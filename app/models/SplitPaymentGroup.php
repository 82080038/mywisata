<?php
namespace App\Models;

use App\Core\Model;

class SplitPaymentGroup extends Model {
    protected $table = 'split_payment_groups';
    protected $primaryKey = 'id';
    
    /**
     * Get groups by user
     */
    public function getByUserId($userId, $limit = 20) {
        return $this->db->query(
            "SELECT spg.*, 
                    (SELECT COUNT(*) FROM split_payment_members WHERE payment_group_id = spg.id) as member_count,
                    (SELECT SUM(paid_amount) FROM split_payment_members WHERE payment_group_id = spg.id) as total_paid
             FROM {$this->table} spg
             WHERE spg.created_by = ? 
             ORDER BY spg.created_at DESC 
             LIMIT ?",
            [$userId, $limit]
        )->fetchAll();
    }
    
    /**
     * Get group by ID
     */
    public function getById($groupId) {
        return $this->db->query(
            "SELECT * FROM {$this->table} 
             WHERE id = ?",
            [$groupId]
        )->fetch();
    }
    
    /**
     * Create split payment group
     */
    public function create($data) {
        $data['created_at'] = date('Y-m-d H:i:s');
        $data['updated_at'] = date('Y-m-d H:i:s');
        return $this->db->insert($this->table, $data);
    }
    
    /**
     * Update group
     */
    public function update($id, $data) {
        $data['updated_at'] = date('Y-m-d H:i:s');
        return $this->db->update($this->table, $data, "id = ?", [$id]);
    }
    
    /**
     * Update status
     */
    public function updateStatus($id, $status) {
        $data = ['status' => $status, 'updated_at' => date('Y-m-d H:i:s')];
        return $this->db->update($this->table, $data, "id = ?", [$id]);
    }
}
