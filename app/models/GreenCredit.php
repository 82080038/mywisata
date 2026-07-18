<?php
/**
 * MyWisata Application - Green Credit Model
 * 
 * @package MyWisata
 * @version 1.0.0
 * @since 2026-07-18
 */

class GreenCredit extends Model {
    
    protected $table = 'green_credits';
    
    /**
     * Get by user ID
     */
    public function getByUserId($userId) {
        $sql = "SELECT * FROM {$this->table} WHERE user_id = :user_id LIMIT 1";
        $result = $this->query($sql, ['user_id' => $userId]);
        return $result[0] ?? [
            'user_id' => $userId,
            'credits_balance' => 0,
            'credits_earned' => 0,
            'credits_spent' => 0,
            'tier' => 'bronze'
        ];
    }
    
    /**
     * Add credits
     */
    public function addCredits($userId, $amount) {
        $sql = "INSERT INTO {$this->table} (user_id, credits_balance, credits_earned, tier) 
                VALUES (:user_id, :amount, :amount, 'bronze')
                ON DUPLICATE KEY UPDATE 
                credits_balance = credits_balance + :amount,
                credits_earned = credits_earned + :amount";
        return $this->execute($sql, ['user_id' => $userId, 'amount' => $amount]);
    }
    
    /**
     * Deduct credits
     */
    public function deductCredits($userId, $amount) {
        $sql = "UPDATE {$this->table} 
                SET credits_balance = credits_balance - :amount,
                    credits_spent = credits_spent + :amount
                WHERE user_id = :user_id AND credits_balance >= :amount";
        return $this->execute($sql, ['user_id' => $userId, 'amount' => $amount]);
    }
    
    /**
     * Update tier
     */
    public function updateTier($userId, $tier) {
        $sql = "UPDATE {$this->table} SET tier = :tier WHERE user_id = :user_id";
        return $this->execute($sql, ['user_id' => $userId, 'tier' => $tier]);
    }
}
