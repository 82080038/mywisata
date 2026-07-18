<?php
/**
 * MyWisata Application - Green Credit Reward Model
 * 
 * @package MyWisata
 * @version 1.0.0
 * @since 2026-07-18
 */

class GreenCreditReward extends Model {
    
    protected $table = 'green_credit_rewards';
    
    /**
     * Get active rewards
     */
    public function getActive() {
        $sql = "SELECT * FROM {$this->table} WHERE is_active = 1 ORDER BY credits_required ASC";
        return $this->query($sql);
    }
    
    /**
     * Find by ID
     */
    public function findById($id) {
        $sql = "SELECT * FROM {$this->table} WHERE id = :id LIMIT 1";
        return $this->query($sql, ['id' => $id])[0] ?? null;
    }
    
    /**
     * Increment claimed count
     */
    public function incrementClaimed($id) {
        $sql = "UPDATE {$this->table} SET total_claimed = total_claimed + 1 WHERE id = :id";
        return $this->execute($sql, ['id' => $id]);
    }
}
