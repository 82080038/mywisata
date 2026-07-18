<?php
namespace App\Models;

use App\Core\Model;

class WhatsAppContact extends Model {
    protected $table = 'whatsapp_contacts';
    protected $primaryKey = 'id';
    
    /**
     * Get contact by user
     */
    public function getByUserId($userId) {
        return $this->db->query(
            "SELECT * FROM {$this->table} 
             WHERE user_id = ? AND is_primary = 1",
            [$userId]
        )->fetch();
    }
    
    /**
     * Get all contacts by user
     */
    public function getAllByUserId($userId) {
        return $this->db->query(
            "SELECT * FROM {$this->table} 
             WHERE user_id = ? 
             ORDER BY is_primary DESC, created_at DESC",
            [$userId]
        )->fetchAll();
    }
    
    /**
     * Create contact
     */
    public function create($data) {
        $data['created_at'] = date('Y-m-d H:i:s');
        $data['updated_at'] = date('Y-m-d H:i:s');
        return $this->db->insert($this->table, $data);
    }
    
    /**
     * Update contact
     */
    public function update($id, $data) {
        $data['updated_at'] = date('Y-m-d H:i:s');
        return $this->db->update($this->table, $data, "id = ?", [$id]);
    }
    
    /**
     * Get contact by phone
     */
    public function getByPhone($phoneNumber) {
        return $this->db->query(
            "SELECT * FROM {$this->table} 
             WHERE phone_number = ?",
            [$phoneNumber]
        )->fetch();
    }
}
