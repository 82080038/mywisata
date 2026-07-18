<?php
namespace App\Models;

use App\Core\Model;

class DigitalWallet extends Model {
    protected $table = 'digital_wallet';
    protected $primaryKey = 'id';
    
    /**
     * Get wallet by user
     */
    public function getByUserId($userId) {
        return $this->db->query(
            "SELECT * FROM {$this->table} 
             WHERE user_id = ?",
            [$userId]
        )->fetch();
    }
    
    /**
     * Create wallet
     */
    public function create($data) {
        $data['created_at'] = date('Y-m-d H:i:s');
        $data['updated_at'] = date('Y-m-d H:i:s');
        return $this->db->insert($this->table, $data);
    }
    
    /**
     * Update balance
     */
    public function updateBalance($userId, $amount) {
        return $this->db->query(
            "UPDATE {$this->table} 
             SET balance = balance + ?, updated_at = ? 
             WHERE user_id = ?",
            [$amount, date('Y-m-d H:i:s'), $userId]
        );
    }
    
    /**
     * Get balance
     */
    public function getBalance($userId) {
        $result = $this->db->query(
            "SELECT balance FROM {$this->table} 
             WHERE user_id = ?",
            [$userId]
        )->fetch();
        return $result['balance'] ?? 0;
    }
}
