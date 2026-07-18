<?php
namespace App\Models;

use App\Core\Model;

class WebsocketSubscription extends Model {
    protected $table = 'websocket_subscriptions';
    protected $primaryKey = 'id';
    
    /**
     * Get subscriptions by user
     */
    public function getByUserId($userId) {
        return $this->db->query(
            "SELECT * FROM {$this->table} 
             WHERE user_id = ? AND is_active = 1 
             ORDER BY last_activity DESC",
            [$userId]
        )->fetchAll();
    }
    
    /**
     * Create subscription
     */
    public function create($data) {
        $data['created_at'] = date('Y-m-d H:i:s');
        return $this->db->insert($this->table, $data);
    }
    
    /**
     * Update activity
     */
    public function updateActivity($id) {
        return $this->db->query(
            "UPDATE {$this->table} 
             SET last_activity = ? 
             WHERE id = ?",
            [date('Y-m-d H:i:s'), $id]
        );
    }
    
    /**
     * Deactivate subscription
     */
    public function deactivate($id) {
        return $this->db->query(
            "UPDATE {$this->table} 
             SET is_active = 0 
             WHERE id = ?",
            [$id]
        );
    }
}
