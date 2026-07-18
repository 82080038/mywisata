<?php
/**
 * MyWisata Application - Green Credit Claim Model
 * 
 * @package MyWisata
 * @version 1.0.0
 * @since 2026-07-18
 */

class GreenCreditClaim extends Model {
    
    protected $table = 'green_credit_claims';
    
    /**
     * Create claim
     */
    public function create($data) {
        $sql = "INSERT INTO {$this->table} (user_id, reward_id, credits_spent, claim_date, status) 
                VALUES (:user_id, :reward_id, :credits_spent, :claim_date, :status)";
        return $this->execute($sql, $data);
    }
}
