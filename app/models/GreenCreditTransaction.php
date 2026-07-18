<?php
/**
 * MyWisata Application - Green Credit Transaction Model
 * 
 * @package MyWisata
 * @version 1.0.0
 * @since 2026-07-18
 */

class GreenCreditTransaction extends Model {
    
    protected $table = 'green_credit_transactions';
    
    /**
     * Get by user ID
     */
    public function getByUserId($userId, $page = 1, $limit = 20) {
        $offset = ($page - 1) * $limit;
        $sql = "SELECT * FROM {$this->table} WHERE user_id = :user_id ORDER BY created_at DESC LIMIT {$limit} OFFSET {$offset}";
        return $this->query($sql, ['user_id' => $userId]);
    }
    
    /**
     * Create transaction
     */
    public function create($data) {
        $sql = "INSERT INTO {$this->table} (user_id, transaction_type, amount, reason, related_booking_id, carbon_offset_kg) 
                VALUES (:user_id, :transaction_type, :amount, :reason, :related_booking_id, :carbon_offset_kg)";
        return $this->execute($sql, $data);
    }
}
