<?php
/**
 * MyWisata Application - Escrow Helper
 * 
 * Handles escrow system for secure payments between users and guides.
 * 
 * @package MyWisata
 * @version 1.0.0
 * @since 2026-07-16
 */

class Escrow {
    
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance();
    }
    
    /**
     * Create escrow account for booking
     * 
     * @param int $bookingId Booking ID
     * @param float $amount Amount to hold
     * @param int $userId User ID
     * @param int $guideId Guide ID
     * @return int|false Escrow ID
     */
    public function createEscrow($bookingId, $amount, $userId, $guideId) {
        $sql = "INSERT INTO escrow_accounts 
                (booking_id, amount, user_id, guide_id, status, created_at)
                VALUES (:booking_id, :amount, :user_id, :guide_id, 'held', NOW())";
        
        $this->db->query($sql, [
            'booking_id' => $bookingId,
            'amount' => $amount,
            'user_id' => $userId,
            'guide_id' => $guideId
        ]);
        
        return $this->db->lastInsertId();
    }
    
    /**
     * Release funds to guide
     * 
     * @param int $escrowId Escrow ID
     * @param string $reason Release reason
     * @return bool
     */
    public function releaseFunds($escrowId, $reason = 'Service completed') {
        try {
            $this->db->beginTransaction();
            
            // Get escrow details
            $escrow = $this->getEscrowById($escrowId);
            
            if (!$escrow || $escrow['status'] !== 'held') {
                $this->db->rollBack();
                return false;
            }
            
            // Update escrow status
            $sql = "UPDATE escrow_accounts 
                    SET status = 'released', 
                        released_at = NOW(),
                        release_reason = :reason
                    WHERE id = :id";
            
            $this->db->query($sql, [
                'reason' => $reason,
                'id' => $escrowId
            ]);
            
            // Add to guide balance
            $this->addToGuideBalance($escrow['guide_id'], $escrow['amount'], $escrowId);
            
            // Log transaction
            $this->logEscrowTransaction($escrowId, 'release', $escrow['amount'], $reason);
            
            $this->db->commit();
            
            Logger::audit('RELEASE_ESCROW', 'escrow_accounts', "Released escrow ID: {$escrowId}", [], [
                'escrow_id' => $escrowId,
                'amount' => $escrow['amount'],
                'guide_id' => $escrow['guide_id']
            ]);
            
            return true;
        } catch (Exception $e) {
            $this->db->rollBack();
            Logger::error('Failed to release escrow funds', [
                'escrow_id' => $escrowId,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }
    
    /**
     * Refund funds to user
     * 
     * @param int $escrowId Escrow ID
     * @param string $reason Refund reason
     * @return bool
     */
    public function refundFunds($escrowId, $reason = 'Service cancelled') {
        try {
            $this->db->beginTransaction();
            
            // Get escrow details
            $escrow = $this->getEscrowById($escrowId);
            
            if (!$escrow || $escrow['status'] !== 'held') {
                $this->db->rollBack();
                return false;
            }
            
            // Update escrow status
            $sql = "UPDATE escrow_accounts 
                    SET status = 'refunded', 
                        refunded_at = NOW(),
                        refund_reason = :reason
                    WHERE id = :id";
            
            $this->db->query($sql, [
                'reason' => $reason,
                'id' => $escrowId
            ]);
            
            // Process refund through payment gateway
            $this->processRefund($escrow['booking_id'], $escrow['amount']);
            
            // Log transaction
            $this->logEscrowTransaction($escrowId, 'refund', $escrow['amount'], $reason);
            
            $this->db->commit();
            
            Logger::audit('REFUND_ESCROW', 'escrow_accounts', "Refunded escrow ID: {$escrowId}", [], [
                'escrow_id' => $escrowId,
                'amount' => $escrow['amount'],
                'user_id' => $escrow['user_id']
            ]);
            
            return true;
        } catch (Exception $e) {
            $this->db->rollBack();
            Logger::error('Failed to refund escrow funds', [
                'escrow_id' => $escrowId,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }
    
    /**
     * Partial release of funds
     * 
     * @param int $escrowId Escrow ID
     * @param float $amount Amount to release
     * @param string $reason Release reason
     * @return bool
     */
    public function partialRelease($escrowId, $amount, $reason = 'Partial service completed') {
        try {
            $this->db->beginTransaction();
            
            // Get escrow details
            $escrow = $this->getEscrowById($escrowId);
            
            if (!$escrow || $escrow['status'] !== 'held') {
                $this->db->rollBack();
                return false;
            }
            
            if ($amount > $escrow['amount']) {
                $this->db->rollBack();
                return false;
            }
            
            // Update escrow
            $remainingAmount = $escrow['amount'] - $amount;
            
            if ($remainingAmount <= 0) {
                // Full release
                return $this->releaseFunds($escrowId, $reason);
            }
            
            $sql = "UPDATE escrow_accounts 
                    SET amount = :remaining_amount,
                        released_amount = COALESCE(released_amount, 0) + :amount
                    WHERE id = :id";
            
            $this->db->query($sql, [
                'remaining_amount' => $remainingAmount,
                'amount' => $amount,
                'id' => $escrowId
            ]);
            
            // Add to guide balance
            $this->addToGuideBalance($escrow['guide_id'], $amount, $escrowId);
            
            // Log transaction
            $this->logEscrowTransaction($escrowId, 'partial_release', $amount, $reason);
            
            $this->db->commit();
            
            return true;
        } catch (Exception $e) {
            $this->db->rollBack();
            Logger::error('Failed to partially release escrow funds', [
                'escrow_id' => $escrowId,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }
    
    /**
     * Get escrow by ID
     * 
     * @param int $escrowId Escrow ID
     * @return array|false
     */
    public function getEscrowById($escrowId) {
        $sql = "SELECT * FROM escrow_accounts WHERE id = :id";
        return $this->db->query($sql, ['id' => $escrowId])->fetch();
    }
    
    /**
     * Get escrow by booking ID
     * 
     * @param int $bookingId Booking ID
     * @return array|false
     */
    public function getEscrowByBookingId($bookingId) {
        $sql = "SELECT * FROM escrow_accounts WHERE booking_id = :booking_id";
        return $this->db->query($sql, ['booking_id' => $bookingId])->fetch();
    }
    
    /**
     * Get user escrow accounts
     * 
     * @param int $userId User ID
     * @return array
     */
    public function getUserEscrows($userId) {
        $sql = "SELECT * FROM escrow_accounts WHERE user_id = :user_id ORDER BY created_at DESC";
        return $this->db->query($sql, ['user_id' => $userId])->fetchAll();
    }
    
    /**
     * Get guide escrow accounts
     * 
     * @param int $guideId Guide ID
     * @return array
     */
    public function getGuideEscrows($guideId) {
        $sql = "SELECT * FROM escrow_accounts WHERE guide_id = :guide_id ORDER BY created_at DESC";
        return $this->db->query($sql, ['guide_id' => $guideId])->fetchAll();
    }
    
    /**
     * Add to guide balance
     * 
     * @param int $guideId Guide ID
     * @param float $amount Amount
     * @param int $escrowId Escrow ID
     * @return bool
     */
    private function addToGuideBalance($guideId, $amount, $escrowId) {
        $sql = "INSERT INTO guide_balances 
                (guide_id, balance, available_balance, updated_at)
                VALUES (:guide_id, :amount, :amount, NOW())
                ON DUPLICATE KEY UPDATE 
                balance = balance + :amount,
                available_balance = available_balance + :amount,
                updated_at = NOW()";
        
        return $this->db->query($sql, [
            'guide_id' => $guideId,
            'amount' => $amount
        ]);
    }
    
    /**
     * Process refund through payment gateway
     * 
     * @param int $bookingId Booking ID
     * @param float $amount Amount to refund
     * @return bool
     */
    private function processRefund($bookingId, $amount) {
        // In production, this would call the payment gateway API
        // For now, just log the refund
        Logger::info('Processing refund', [
            'booking_id' => $bookingId,
            'amount' => $amount
        ]);
        
        return true;
    }
    
    /**
     * Log escrow transaction
     * 
     * @param int $escrowId Escrow ID
     * @param string $type Transaction type
     * @param float $amount Amount
     * @param string $reason Reason
     * @return bool
     */
    private function logEscrowTransaction($escrowId, $type, $amount, $reason) {
        $sql = "INSERT INTO escrow_transactions 
                (escrow_id, transaction_type, amount, reason, created_at)
                VALUES (:escrow_id, :type, :amount, :reason, NOW())";
        
        return $this->db->query($sql, [
            'escrow_id' => $escrowId,
            'type' => $type,
            'amount' => $amount,
            'reason' => $reason
        ]);
    }
    
    /**
     * Get escrow statistics
     * 
     * @param int $guideId Optional guide ID
     * @return array
     */
    public function getEscrowStats($guideId = null) {
        $sql = "SELECT 
                COUNT(*) as total_escrows,
                SUM(CASE WHEN status = 'held' THEN 1 ELSE 0 END) as held_count,
                SUM(CASE WHEN status = 'released' THEN 1 ELSE 0 END) as released_count,
                SUM(CASE WHEN status = 'refunded' THEN 1 ELSE 0 END) as refunded_count,
                SUM(CASE WHEN status = 'held' THEN amount ELSE 0 END) as held_amount,
                SUM(CASE WHEN status = 'released' THEN amount ELSE 0 END) as released_amount,
                SUM(CASE WHEN status = 'refunded' THEN amount ELSE 0 END) as refunded_amount
                FROM escrow_accounts";
        
        if ($guideId) {
            $sql .= " WHERE guide_id = :guide_id";
        }
        
        $params = $guideId ? ['guide_id' => $guideId] : [];
        
        return $this->db->query($sql, $params)->fetch();
    }
    
    /**
     * Auto-release escrow after service completion
     * 
     * @param int $bookingId Booking ID
     * @return bool
     */
    public function autoReleaseAfterService($bookingId) {
        $escrow = $this->getEscrowByBookingId($bookingId);
        
        if (!$escrow || $escrow['status'] !== 'held') {
            return false;
        }
        
        return $this->releaseFunds($escrow['id'], 'Service completed automatically');
    }
}
