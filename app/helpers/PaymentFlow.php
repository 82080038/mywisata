<?php
/**
 * MyWisata Application - Payment Flow Helper
 * 
 * Handles payment flow including deposit, balance, and refund.
 * 
 * @package MyWisata
 * @version 1.0.0
 * @since 2026-07-16
 */

class PaymentFlow {
    
    private $db;
    private $paymentGateway;
    
    public function __construct() {
        $this->db = Database::getInstance();
        $this->paymentGateway = new PaymentGateway();
    }
    
    /**
     * Create deposit payment
     * 
     * @param int $bookingId Booking ID
     * @param float $depositAmount Deposit amount
     * @param float $totalAmount Total amount
     * @return array|false
     */
    public function createDepositPayment($bookingId, $depositAmount, $totalAmount) {
        try {
            $this->db->beginTransaction();
            
            // Get booking details
            $booking = $this->getBookingById($bookingId);
            
            if (!$booking) {
                $this->db->rollBack();
                return false;
            }
            
            // Create payment record
            $orderId = 'DEP-' . $bookingId . '-' . time();
            
            $sql = "INSERT INTO payment_records 
                    (booking_id, order_id, payment_type, amount, total_amount, status, created_at)
                    VALUES (:booking_id, :order_id, 'deposit', :amount, :total_amount, 'pending', NOW())";
            
            $this->db->query($sql, [
                'booking_id' => $bookingId,
                'order_id' => $orderId,
                'amount' => $depositAmount,
                'total_amount' => $totalAmount
            ]);
            
            $paymentId = $this->db->lastInsertId();
            
            // Create payment gateway transaction
            $paymentData = [
                'order_id' => $orderId,
                'amount' => $depositAmount,
                'customer_name' => $booking['user_name'],
                'customer_email' => $booking['user_email'],
                'customer_phone' => $booking['user_phone'] ?? '',
                'description' => "Deposit payment for booking #{$bookingId}",
                'success_url' => BASE_URL . 'payment/success?type=deposit&booking_id=' . $bookingId,
                'failure_url' => BASE_URL . 'payment/failure?type=deposit&booking_id=' . $bookingId
            ];
            
            $transaction = $this->paymentGateway->createTransaction($paymentData);
            
            if (!$transaction) {
                $this->db->rollBack();
                return false;
            }
            
            $this->db->commit();
            
            return [
                'payment_id' => $paymentId,
                'order_id' => $orderId,
                'token' => $transaction['token'],
                'redirect_url' => $transaction['redirect_url'],
                'amount' => $depositAmount,
                'balance_due' => $totalAmount - $depositAmount
            ];
        } catch (Exception $e) {
            $this->db->rollBack();
            Logger::error('Failed to create deposit payment', [
                'booking_id' => $bookingId,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }
    
    /**
     * Create balance payment
     * 
     * @param int $bookingId Booking ID
     * @param float $balanceAmount Balance amount
     * @return array|false
     */
    public function createBalancePayment($bookingId, $balanceAmount) {
        try {
            $this->db->beginTransaction();
            
            // Get booking details
            $booking = $this->getBookingById($bookingId);
            
            if (!$booking) {
                $this->db->rollBack();
                return false;
            }
            
            // Check if deposit is paid
            $depositPaid = $this->isDepositPaid($bookingId);
            
            if (!$depositPaid) {
                $this->db->rollBack();
                return false;
            }
            
            // Create payment record
            $orderId = 'BAL-' . $bookingId . '-' . time();
            
            $sql = "INSERT INTO payment_records 
                    (booking_id, order_id, payment_type, amount, status, created_at)
                    VALUES (:booking_id, :order_id, 'balance', :amount, 'pending', NOW())";
            
            $this->db->query($sql, [
                'booking_id' => $bookingId,
                'order_id' => $orderId,
                'amount' => $balanceAmount
            ]);
            
            $paymentId = $this->db->lastInsertId();
            
            // Create payment gateway transaction
            $paymentData = [
                'order_id' => $orderId,
                'amount' => $balanceAmount,
                'customer_name' => $booking['user_name'],
                'customer_email' => $booking['user_email'],
                'customer_phone' => $booking['user_phone'] ?? '',
                'description' => "Balance payment for booking #{$bookingId}",
                'success_url' => BASE_URL . 'payment/success?type=balance&booking_id=' . $bookingId,
                'failure_url' => BASE_URL . 'payment/failure?type=balance&booking_id=' . $bookingId
            ];
            
            $transaction = $this->paymentGateway->createTransaction($paymentData);
            
            if (!$transaction) {
                $this->db->rollBack();
                return false;
            }
            
            $this->db->commit();
            
            return [
                'payment_id' => $paymentId,
                'order_id' => $orderId,
                'token' => $transaction['token'],
                'redirect_url' => $transaction['redirect_url'],
                'amount' => $balanceAmount
            ];
        } catch (Exception $e) {
            $this->db->rollBack();
            Logger::error('Failed to create balance payment', [
                'booking_id' => $bookingId,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }
    
    /**
     * Process refund
     * 
     * @param int $bookingId Booking ID
     * @param float $refundAmount Refund amount
     * @param string $reason Refund reason
     * @return bool
     */
    public function processRefund($bookingId, $refundAmount, $reason = 'Customer request') {
        try {
            $this->db->beginTransaction();
            
            // Get booking details
            $booking = $this->getBookingById($bookingId);
            
            if (!$booking) {
                $this->db->rollBack();
                return false;
            }
            
            // Check refundable amount
            $refundableAmount = $this->getRefundableAmount($bookingId);
            
            if ($refundAmount > $refundableAmount) {
                $this->db->rollBack();
                return false;
            }
            
            // Create refund record
            $sql = "INSERT INTO payment_records 
                    (booking_id, payment_type, amount, status, refund_reason, created_at)
                    VALUES (:booking_id, 'refund', :amount, 'processing', :reason, NOW())";
            
            $this->db->query($sql, [
                'booking_id' => $bookingId,
                'amount' => $refundAmount,
                'reason' => $reason
            ]);
            
            $refundId = $this->db->lastInsertId();
            
            // Process refund through payment gateway
            $refundSuccess = $this->processGatewayRefund($bookingId, $refundAmount);
            
            if ($refundSuccess) {
                // Update refund status
                $sql = "UPDATE payment_records 
                        SET status = 'completed',
                            completed_at = NOW()
                        WHERE id = :id";
                
                $this->db->query($sql, ['id' => $refundId]);
                
                // Update booking status if full refund
                $totalPaid = $this->getTotalPaid($bookingId);
                if ($totalPaid - $refundAmount <= 0) {
                    $sql = "UPDATE bookings SET status = 'refunded' WHERE id = :id";
                    $this->db->query($sql, ['id' => $bookingId]);
                }
            } else {
                // Update refund status to failed
                $sql = "UPDATE payment_records 
                        SET status = 'failed',
                            failed_at = NOW()
                        WHERE id = :id";
                
                $this->db->query($sql, ['id' => $refundId]);
            }
            
            $this->db->commit();
            
            Logger::audit('PROCESS_REFUND', 'payment_records', "Processed refund for booking ID: {$bookingId}", [], [
                'booking_id' => $bookingId,
                'amount' => $refundAmount,
                'status' => $refundSuccess ? 'completed' : 'failed'
            ]);
            
            return $refundSuccess;
        } catch (Exception $e) {
            $this->db->rollBack();
            Logger::error('Failed to process refund', [
                'booking_id' => $bookingId,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }
    
    /**
     * Get booking by ID
     * 
     * @param int $bookingId Booking ID
     * @return array|false
     */
    private function getBookingById($bookingId) {
        $sql = "SELECT b.*, u.name as user_name, u.email as user_email, u.phone as user_phone
                FROM bookings b
                LEFT JOIN users u ON b.user_id = u.id
                WHERE b.id = :id";
        
        return $this->db->query($sql, ['id' => $bookingId])->fetch();
    }
    
    /**
     * Check if deposit is paid
     * 
     * @param int $bookingId Booking ID
     * @return bool
     */
    private function isDepositPaid($bookingId) {
        $sql = "SELECT COUNT(*) as count 
                FROM payment_records 
                WHERE booking_id = :booking_id 
                AND payment_type = 'deposit' 
                AND status = 'completed'";
        
        $result = $this->db->query($sql, ['booking_id' => $bookingId])->fetch();
        
        return $result['count'] > 0;
    }
    
    /**
     * Get refundable amount
     * 
     * @param int $bookingId Booking ID
     * @return float
     */
    private function getRefundableAmount($bookingId) {
        $sql = "SELECT SUM(CASE WHEN status = 'completed' THEN amount ELSE 0 END) as total_paid,
                SUM(CASE WHEN payment_type = 'refund' AND status = 'completed' THEN amount ELSE 0 END) as total_refunded
                FROM payment_records 
                WHERE booking_id = :booking_id";
        
        $result = $this->db->query($sql, ['booking_id' => $bookingId])->fetch();
        
        $totalPaid = $result['total_paid'] ?? 0;
        $totalRefunded = $result['total_refunded'] ?? 0;
        
        return $totalPaid - $totalRefunded;
    }
    
    /**
     * Get total paid amount
     * 
     * @param int $bookingId Booking ID
     * @return float
     */
    private function getTotalPaid($bookingId) {
        $sql = "SELECT SUM(CASE WHEN status = 'completed' THEN amount ELSE 0 END) as total_paid
                FROM payment_records 
                WHERE booking_id = :booking_id";
        
        $result = $this->db->query($sql, ['booking_id' => $bookingId])->fetch();
        
        return $result['total_paid'] ?? 0;
    }
    
    /**
     * Process refund through payment gateway
     * 
     * @param int $bookingId Booking ID
     * @param float $amount Amount to refund
     * @return bool
     */
    private function processGatewayRefund($bookingId, $amount) {
        // In production, this would call the payment gateway API
        // For now, simulate success
        Logger::info('Processing gateway refund', [
            'booking_id' => $bookingId,
            'amount' => $amount
        ]);
        
        return true;
    }
    
    /**
     * Get payment summary for booking
     * 
     * @param int $bookingId Booking ID
     * @return array
     */
    public function getPaymentSummary($bookingId) {
        $sql = "SELECT 
                payment_type,
                SUM(CASE WHEN status = 'completed' THEN amount ELSE 0 END) as paid,
                SUM(CASE WHEN status = 'pending' THEN amount ELSE 0 END) as pending,
                SUM(CASE WHEN status = 'failed' THEN amount ELSE 0 END) as failed
                FROM payment_records 
                WHERE booking_id = :booking_id
                GROUP BY payment_type";
        
        $results = $this->db->query($sql, ['booking_id' => $bookingId])->fetchAll();
        
        $summary = [
            'deposit' => ['paid' => 0, 'pending' => 0, 'failed' => 0],
            'balance' => ['paid' => 0, 'pending' => 0, 'failed' => 0],
            'refund' => ['paid' => 0, 'pending' => 0, 'failed' => 0]
        ];
        
        foreach ($results as $row) {
            $type = $row['payment_type'];
            if (isset($summary[$type])) {
                $summary[$type]['paid'] = $row['paid'];
                $summary[$type]['pending'] = $row['pending'];
                $summary[$type]['failed'] = $row['failed'];
            }
        }
        
        $totalPaid = $summary['deposit']['paid'] + $summary['balance']['paid'];
        $totalRefunded = $summary['refund']['paid'];
        $balanceDue = $summary['balance']['pending'];
        
        return [
            'by_type' => $summary,
            'total_paid' => $totalPaid,
            'total_refunded' => $totalRefunded,
            'balance_due' => $balanceDue,
            'net_paid' => $totalPaid - $totalRefunded
        ];
    }
    
    /**
     * Update payment status from webhook
     * 
     * @param string $orderId Order ID
     * @param string $status Status
     * @return bool
     */
    public function updatePaymentStatus($orderId, $status) {
        $sql = "UPDATE payment_records 
                SET status = :status,
                    updated_at = NOW()
                WHERE order_id = :order_id";
        
        return $this->db->query($sql, [
            'status' => $status,
            'order_id' => $orderId
        ]);
    }
}
