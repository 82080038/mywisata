<?php
namespace App\Models;

use App\Core\Model;

class WhatsAppTemplate extends Model {
    protected $table = 'whatsapp_templates';
    protected $primaryKey = 'id';
    
    /**
     * Get all templates
     */
    public function getAll($activeOnly = true) {
        $where = $activeOnly ? "WHERE is_active = 1" : "";
        return $this->db->query(
            "SELECT * FROM {$this->table} 
             {$where} 
             ORDER BY template_name ASC"
        )->fetchAll();
    }
    
    /**
     * Get template by name
     */
    public function getByName($templateName) {
        return $this->db->query(
            "SELECT * FROM {$this->table} 
             WHERE template_name = ?",
            [$templateName]
        )->fetch();
    }
    
    /**
     * Get templates by type
     */
    public function getByType($templateType) {
        return $this->db->query(
            "SELECT * FROM {$this->table} 
             WHERE template_type = ? AND is_active = 1 
             ORDER BY template_name ASC",
            [$templateType]
        )->fetchAll();
    }
    
    /**
     * Create template
     */
    public function create($data) {
        $data['created_at'] = date('Y-m-d H:i:s');
        $data['updated_at'] = date('Y-m-d H:i:s');
        return $this->db->insert($this->table, $data);
    }
    
    /**
     * Update template
     */
    public function update($id, $data) {
        $data['updated_at'] = date('Y-m-d H:i:s');
        return $this->db->update($this->table, $data, "id = ?", [$id]);
    }
}
