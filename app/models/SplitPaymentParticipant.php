<?php
/**
 * MyWisata Application - Split Payment Participant Model
 * 
 * @package MyWisata
 * @version 1.0.0
 * @since 2026-07-18
 */

class SplitPaymentParticipant extends Model {
    
    protected $table = 'split_payment_participants';
    
    /**
     * Create participant
     */
    public function create($data) {
        $sql = "INSERT INTO {$this->table} (split_payment_group_id, user_id, participant_name, participant_email, participant_phone, share_amount, amount_paid, amount_remaining, payment_status, invite_sent, invite_method, invite_token) 
                VALUES (:split_payment_group_id, :user_id, :participant_name, :participant_email, :participant_phone, :share_amount, :amount_paid, :amount_remaining, :payment_status, :invite_sent, :invite_method, :invite_token)";
        return $this->execute($sql, $data);
    }
    
    /**
     * Find by ID
     */
    public function findById($id) {
        $sql = "SELECT * FROM {$this->table} WHERE id = :id LIMIT 1";
        return $this->query($sql, ['id' => $id])[0] ?? null;
    }
    
    /**
     * Find by invite token
     */
    public function getByInviteToken($token) {
        $sql = "SELECT * FROM {$this->table} WHERE invite_token = :token LIMIT 1";
        return $this->query($sql, ['token' => $token])[0] ?? null;
    }
    
    /**
     * Update payment
     */
    public function updatePayment($id, $amountPaid, $amountRemaining, $paymentStatus) {
        $sql = "UPDATE {$this->table} SET amount_paid = :amount_paid, amount_remaining = :amount_remaining, payment_status = :payment_status WHERE id = :id";
        return $this->execute($sql, ['id' => $id, 'amount_paid' => $amountPaid, 'amount_remaining' => $amountRemaining, 'payment_status' => $paymentStatus]);
    }
    
    /**
     * Mark invite as sent
     */
    public function markInviteSent($id) {
        $sql = "UPDATE {$this->table} SET invite_sent = 1 WHERE id = :id";
        return $this->execute($sql, ['id' => $id]);
    }
    
    /**
     * Get by group ID
     */
    public function getByGroupId($groupId) {
        $sql = "SELECT * FROM {$this->table} WHERE split_payment_group_id = :split_payment_group_id ORDER BY created_at";
        return $this->query($sql, ['split_payment_group_id' => $groupId]);
    }
}
