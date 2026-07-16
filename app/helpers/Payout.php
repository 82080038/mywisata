<?php
/**
 * MyWisata Application - Payout Helper
 * 
 * Handles payout system for tour guides.
 * 
 * @package MyWisata
 * @version 1.0.0
 * @since 2026-07-16
 */

class Payout {
    
    private $db;
    private $minimumPayout = 100000; // Minimum payout amount in IDR
    
    public function __construct() {
        $this->db = Database::getInstance();
    }
    
    /**
     * Request payout for guide
     * 
     * @param int $guideId Guide ID
     * @param float $amount Amount to payout
     * @param string $bankName Bank name
     * @param string $accountNumber Account number
     * @param string $accountName Account holder name
     * @return int|false Payout ID
     */
    public function requestPayout($guideId, $amount, $bankName, $accountNumber, $accountName) {
        try {
            $this->db->beginTransaction();
            
            // Check guide balance
            $balance = $this->getGuideBalance($guideId);
            
            if ($balance['available_balance'] < $amount) {
                $this->db->rollBack();
                return false;
            }
            
            if ($amount < $this->minimumPayout) {
                $this->db->rollBack();
                return false;
            }
            
            // Deduct from available balance
            $sql = "UPDATE guide_balances 
                    SET available_balance = available_balance - :amount,
                        pending_balance = pending_balance + :amount,
                        updated_at = NOW()
                    WHERE guide_id = :guide_id";
            
            $this->db->query($sql, [
                'amount' => $amount,
                'guide_id' => $guideId
            ]);
            
            // Create payout request
            $sql = "INSERT INTO guide_payouts 
                    (guide_id, amount, bank_name, account_number, account_name, status, created_at)
                    VALUES (:guide_id, :amount, :bank_name, :account_number, :account_name, 'pending', NOW())";
            
            $this->db->query($sql, [
                'guide_id' => $guideId,
                'amount' => $amount,
                'bank_name' => $bankName,
                'account_number' => $accountNumber,
                'account_name' => $accountName
            ]);
            
            $payoutId = $this->db->lastInsertId();
            
            $this->db->commit();
            
            Logger::audit('REQUEST_PAYOUT', 'guide_payouts', "Requested payout ID: {$payoutId}", [], [
                'guide_id' => $guideId,
                'amount' => $amount
            ]);
            
            return $payoutId;
        } catch (Exception $e) {
            $this->db->rollBack();
            Logger::error('Failed to request payout', [
                'guide_id' => $guideId,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }
    
    /**
     * Process payout (approve and transfer)
     * 
     * @param int $payoutId Payout ID
     * @param string $adminNote Admin notes
     * @return bool
     */
    public function processPayout($payoutId, $adminNote = null) {
        try {
            $this->db->beginTransaction();
            
            // Get payout details
            $payout = $this->getPayoutById($payoutId);
            
            if (!$payout || $payout['status'] !== 'pending') {
                $this->db->rollBack();
                return false;
            }
            
            // Update payout status
            $sql = "UPDATE guide_payouts 
                    SET status = 'processing',
                        processed_at = NOW(),
                        admin_note = :admin_note
                    WHERE id = :id";
            
            $this->db->query($sql, [
                'admin_note' => $adminNote,
                'id' => $payoutId
            ]);
            
            // Process transfer through payment gateway
            $transferSuccess = $this->processTransfer($payout);
            
            if ($transferSuccess) {
                // Update to completed
                $sql = "UPDATE guide_payouts 
                        SET status = 'completed',
                            completed_at = NOW()
                        WHERE id = :id";
                
                $this->db->query($sql, ['id' => $payoutId]);
                
                // Deduct from pending balance
                $sql = "UPDATE guide_balances 
                        SET pending_balance = pending_balance - :amount,
                            updated_at = NOW()
                        WHERE guide_id = :guide_id";
                
                $this->db->query($sql, [
                    'amount' => $payout['amount'],
                    'guide_id' => $payout['guide_id']
                ]);
            } else {
                // Update to failed
                $sql = "UPDATE guide_payouts 
                        SET status = 'failed',
                            failed_at = NOW()
                        WHERE id = :id";
                
                $this->db->query($sql, ['id' => $payoutId]);
                
                // Return to available balance
                $sql = "UPDATE guide_balances 
                        SET pending_balance = pending_balance - :amount,
                            available_balance = available_balance + :amount,
                            updated_at = NOW()
                        WHERE guide_id = :guide_id";
                
                $this->db->query($sql, [
                    'amount' => $payout['amount'],
                    'guide_id' => $payout['guide_id']
                ]);
            }
            
            $this->db->commit();
            
            Logger::audit('PROCESS_PAYOUT', 'guide_payouts', "Processed payout ID: {$payoutId}", [], [
                'payout_id' => $payoutId,
                'status' => $transferSuccess ? 'completed' : 'failed'
            ]);
            
            return $transferSuccess;
        } catch (Exception $e) {
            $this->db->rollBack();
            Logger::error('Failed to process payout', [
                'payout_id' => $payoutId,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }
    
    /**
     * Reject payout request
     * 
     * @param int $payoutId Payout ID
     * @param string $reason Rejection reason
     * @return bool
     */
    public function rejectPayout($payoutId, $reason) {
        try {
            $this->db->beginTransaction();
            
            // Get payout details
            $payout = $this->getPayoutById($payoutId);
            
            if (!$payout || $payout['status'] !== 'pending') {
                $this->db->rollBack();
                return false;
            }
            
            // Update payout status
            $sql = "UPDATE guide_payouts 
                    SET status = 'rejected',
                        rejected_at = NOW(),
                        rejection_reason = :reason
                    WHERE id = :id";
            
            $this->db->query($sql, [
                'reason' => $reason,
                'id' => $payoutId
            ]);
            
            // Return to available balance
            $sql = "UPDATE guide_balances 
                    SET pending_balance = pending_balance - :amount,
                        available_balance = available_balance + :amount,
                        updated_at = NOW()
                    WHERE guide_id = :guide_id";
            
            $this->db->query($sql, [
                'amount' => $payout['amount'],
                'guide_id' => $payout['guide_id']
            ]);
            
            $this->db->commit();
            
            Logger::audit('REJECT_PAYOUT', 'guide_payouts', "Rejected payout ID: {$payoutId}", [], [
                'payout_id' => $payoutId,
                'reason' => $reason
            ]);
            
            return true;
        } catch (Exception $e) {
            $this->db->rollBack();
            Logger::error('Failed to reject payout', [
                'payout_id' => $payoutId,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }
    
    /**
     * Get payout by ID
     * 
     * @param int $payoutId Payout ID
     * @return array|false
     */
    public function getPayoutById($payoutId) {
        $sql = "SELECT p.*, tg.name as guide_name, tg.email as guide_email
                FROM guide_payouts p
                LEFT JOIN tour_guides tg ON p.guide_id = tg.id
                WHERE p.id = :id";
        
        return $this->db->query($sql, ['id' => $payoutId])->fetch();
    }
    
    /**
     * Get guide payouts
     * 
     * @param int $guideId Guide ID
     * @return array
     */
    public function getGuidePayouts($guideId) {
        $sql = "SELECT * FROM guide_payouts WHERE guide_id = :guide_id ORDER BY created_at DESC";
        return $this->db->query($sql, ['guide_id' => $guideId])->fetchAll();
    }
    
    /**
     * Get all pending payouts
     * 
     * @return array
     */
    public function getPendingPayouts() {
        $sql = "SELECT p.*, tg.name as guide_name, tg.email as guide_email
                FROM guide_payouts p
                LEFT JOIN tour_guides tg ON p.guide_id = tg.id
                WHERE p.status = 'pending'
                ORDER BY p.created_at ASC";
        
        return $this->db->query($sql)->fetchAll();
    }
    
    /**
     * Get guide balance
     * 
     * @param int $guideId Guide ID
     * @return array
     */
    public function getGuideBalance($guideId) {
        $sql = "SELECT * FROM guide_balances WHERE guide_id = :guide_id";
        $result = $this->db->query($sql, ['guide_id' => $guideId])->fetch();
        
        if (!$result) {
            return [
                'balance' => 0,
                'available_balance' => 0,
                'pending_balance' => 0
            ];
        }
        
        return $result;
    }
    
    /**
     * Process transfer through payment gateway
     * 
     * @param array $payout Payout data
     * @return bool
     */
    private function processTransfer($payout) {
        // In production, this would call the payment gateway API (e.g., Xendit Disbursement)
        // For now, simulate success
        Logger::info('Processing payout transfer', [
            'payout_id' => $payout['id'],
            'amount' => $payout['amount'],
            'bank_name' => $payout['bank_name'],
            'account_number' => $payout['account_number']
        ]);
        
        return true;
    }
    
    /**
     * Get payout statistics
     * 
     * @param int $guideId Optional guide ID
     * @return array
     */
    public function getPayoutStats($guideId = null) {
        $sql = "SELECT 
                COUNT(*) as total_payouts,
                SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending_count,
                SUM(CASE WHEN status = 'processing' THEN 1 ELSE 0 END) as processing_count,
                SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) as completed_count,
                SUM(CASE WHEN status = 'rejected' THEN 1 ELSE 0 END) as rejected_count,
                SUM(CASE WHEN status = 'pending' THEN amount ELSE 0 END) as pending_amount,
                SUM(CASE WHEN status = 'completed' THEN amount ELSE 0 END) as completed_amount
                FROM guide_payouts";
        
        if ($guideId) {
            $sql .= " WHERE guide_id = :guide_id";
        }
        
        $params = $guideId ? ['guide_id' => $guideId] : [];
        
        return $this->db->query($sql, $params)->fetch();
    }
    
    /**
     * Get minimum payout amount
     * 
     * @return float
     */
    public function getMinimumPayout() {
        return $this->minimumPayout;
    }
    
    /**
     * Set minimum payout amount
     * 
     * @param float $amount Minimum amount
     */
    public function setMinimumPayout($amount) {
        $this->minimumPayout = $amount;
    }
    
    /**
     * Check if guide can request payout
     * 
     * @param int $guideId Guide ID
     * @param float $amount Requested amount
     * @return bool
     */
    public function canRequestPayout($guideId, $amount) {
        $balance = $this->getGuideBalance($guideId);
        
        return $balance['available_balance'] >= $amount && $amount >= $this->minimumPayout;
    }
}
