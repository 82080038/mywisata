<?php
namespace App\Models;

use App\Core\Model;

class WhatsAppMessage extends Model {
    protected $table = 'whatsapp_messages';
    protected $primaryKey = 'id';
    
    /**
     * Get messages by contact
     */
    public function getByContactId($contactId, $limit = 50) {
        return $this->db->query(
            "SELECT * FROM {$this->table} 
             WHERE contact_id = ? 
             ORDER BY created_at DESC 
             LIMIT ?",
            [$contactId, $limit]
        )->fetchAll();
    }
    
    /**
     * Create message
     */
    public function create($data) {
        $data['created_at'] = date('Y-m-d H:i:s');
        return $this->db->insert($this->table, $data);
    }
    
    /**
     * Update message status
     */
    public function updateStatus($id, $status, $timestampField = null) {
        $data = ['status' => $status];
        if ($timestampField) {
            $data[$timestampField] = date('Y-m-d H:i:s');
        }
        return $this->db->update($this->table, $data, "id = ?", [$id]);
    }
    
    /**
     * Get messages by type
     */
    public function getByType($messageType, $limit = 100) {
        return $this->db->query(
            "SELECT * FROM {$this->table} 
             WHERE message_type = ? 
             ORDER BY created_at DESC 
             LIMIT ?",
            [$messageType, $limit]
        )->fetchAll();
    }
    
    /**
     * Get message statistics
     */
    public function getStatistics($contactId = null) {
        $where = $contactId ? "WHERE contact_id = ?" : "";
        $params = $contactId ? [$contactId] : [];
        
        $total = $this->db->query("SELECT COUNT(*) as count FROM {$this->table} {$where}", $params)->fetch()['count'];
        $sent = $this->db->query("SELECT COUNT(*) as count FROM {$this->table} WHERE status = 'sent' {$where}", $params)->fetch()['count'];
        $delivered = $this->db->query("SELECT COUNT(*) as count FROM {$this->table} WHERE status = 'delivered' {$where}", $params)->fetch()['count'];
        $read = $this->db->query("SELECT COUNT(*) as count FROM {$this->table} WHERE status = 'read' {$where}", $params)->fetch()['count'];
        $failed = $this->db->query("SELECT COUNT(*) as count FROM {$this->table} WHERE status = 'failed' {$where}", $params)->fetch()['count'];
        
        return [
            'total' => $total,
            'sent' => $sent,
            'delivered' => $delivered,
            'read' => $read,
            'failed' => $failed,
            'pending' => $total - $sent - $delivered - $read - $failed
        ];
    }
}
