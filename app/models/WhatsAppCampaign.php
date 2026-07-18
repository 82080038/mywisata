<?php
namespace App\Models;

use App\Core\Model;

class WhatsAppCampaign extends Model {
    protected $table = 'whatsapp_campaigns';
    protected $primaryKey = 'id';
    
    /**
     * Get all campaigns
     */
    public function getAll($limit = 50) {
        return $this->db->query(
            "SELECT * FROM {$this->table} 
             ORDER BY created_at DESC 
             LIMIT ?",
            [$limit]
        )->fetchAll();
    }
    
    /**
     * Get campaign by status
     */
    public function getByStatus($status) {
        return $this->db->query(
            "SELECT * FROM {$this->table} 
             WHERE status = ? 
             ORDER BY created_at DESC",
            [$status]
        )->fetchAll();
    }
    
    /**
     * Create campaign
     */
    public function create($data) {
        $data['created_at'] = date('Y-m-d H:i:s');
        $data['updated_at'] = date('Y-m-d H:i:s');
        return $this->db->insert($this->table, $data);
    }
    
    /**
     * Update campaign
     */
    public function update($id, $data) {
        $data['updated_at'] = date('Y-m-d H:i:s');
        return $this->db->update($this->table, $data, "id = ?", [$id]);
    }
    
    /**
     * Update campaign statistics
     */
    public function updateStats($id, $stats) {
        $data = [
            'sent_count' => $stats['sent_count'] ?? 0,
            'delivered_count' => $stats['delivered_count'] ?? 0,
            'read_count' => $stats['read_count'] ?? 0,
            'updated_at' => date('Y-m-d H:i:s')
        ];
        return $this->db->update($this->table, $data, "id = ?", [$id]);
    }
}
