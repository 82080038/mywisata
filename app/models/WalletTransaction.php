<?php
namespace App\Models;

use App\Core\Model;

class WalletTransaction extends Model {
    protected $table = 'wallet_transactions';
    protected $primaryKey = 'id';
    
    /**
     * Get transactions by wallet
     */
    public function getByWalletId($walletId, $limit = 50) {
        return $this->db->query(
            "SELECT * FROM {$this->table} 
             WHERE wallet_id = ? 
             ORDER BY created_at DESC 
             LIMIT ?",
            [$walletId, $limit]
        )->fetchAll();
    }
    
    /**
     * Create transaction
     */
    public function create($data) {
        $data['created_at'] = date('Y-m-d H:i:s');
        return $this->db->insert($this->table, $data);
    }
    
    /**
     * Get transactions by type
     */
    public function getByType($walletId, $transactionType) {
        return $this->db->query(
            "SELECT * FROM {$this->table} 
             WHERE wallet_id = ? AND transaction_type = ? 
             ORDER BY created_at DESC",
            [$walletId, $transactionType]
        )->fetchAll();
    }
}
