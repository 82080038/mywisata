<?php
namespace App\Models;

use App\Core\Model;

class SplitPaymentMember extends Model {
    protected $table = 'split_payment_members';
    protected $primaryKey = 'id';
    
    /**
     * Get members by group
     */
    public function getByGroupId($groupId) {
        return $this->db->query(
            "SELECT spm.*, u.name, u.email 
             FROM {$this->table} spm
             JOIN users u ON spm.user_id = u.id
             WHERE spm.payment_group_id = ? 
             ORDER BY spm.created_at ASC",
            [$groupId]
        )->fetchAll();
    }
    
    /**
     * Get groups by user
     */
    public function getGroupsByUserId($userId) {
        return $this->db->query(
            "SELECT spm.*, spg.group_name, spg.total_amount, spg.status
             FROM {$this->table} spm
             JOIN split_payment_groups spg ON spm.payment_group_id = spg.id
             WHERE spm.user_id = ? 
             ORDER BY spg.created_at DESC",
            [$userId]
        )->fetchAll();
    }
    
    /**
     * Add member
     */
    public function create($data) {
        $data['created_at'] = date('Y-m-d H:i:s');
        $data['updated_at'] = date('Y-m-d H:i:s');
        return $this->db->insert($this->table, $data);
    }
    
    /**
     * Update payment
     */
    public function updatePayment($id, $amount) {
        $data = [
            'paid_amount' => $amount,
            'updated_at' => date('Y-m-d H:i:s')
        ];
        
        if ($amount >= $this->getShareAmount($id)) {
            $data['status'] = 'settled';
        } else {
            $data['status'] = 'partial';
        }
        
        return $this->db->update($this->table, $data, "id = ?", [$id]);
    }
    
    /**
     * Get share amount
     */
    public function getShareAmount($id) {
        $result = $this->db->query(
            "SELECT share_amount FROM {$this->table} 
             WHERE id = ?",
            [$id]
        )->fetch();
        return $result['share_amount'] ?? 0;
    }
}
