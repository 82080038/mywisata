<?php
namespace App\Models;

use App\Core\Model;

class AIConversation extends Model {
    protected $table = 'ai_conversations';
    protected $primaryKey = 'id';
    
    /**
     * Get conversations by user
     */
    public function getByUserId($userId, $limit = 50) {
        return $this->db->query(
            "SELECT * FROM {$this->table} 
             WHERE user_id = ? 
             ORDER BY created_at DESC 
             LIMIT ?",
            [$userId, $limit]
        )->fetchAll();
    }
    
    /**
     * Get conversation by session
     */
    public function getBySessionId($sessionId) {
        return $this->db->query(
            "SELECT * FROM {$this->table} 
             WHERE session_id = ? 
             ORDER BY created_at ASC",
            [$sessionId]
        )->fetchAll();
    }
    
    /**
     * Create conversation entry
     */
    public function create($data) {
        $data['created_at'] = date('Y-m-d H:i:s');
        return $this->db->insert($this->table, $data);
    }
    
    /**
     * Update feedback
     */
    public function updateFeedback($id, $feedback, $comment = null) {
        $data = [
            'feedback' => $feedback,
            'feedback_comment' => $comment
        ];
        return $this->db->update($this->table, $data, "id = ?", [$id]);
    }
    
    /**
     * Get conversation statistics
     */
    public function getStatistics($userId = null) {
        $where = $userId ? "WHERE user_id = ?" : "";
        $params = $userId ? [$userId] : [];
        
        $total = $this->db->query("SELECT COUNT(*) as count FROM {$this->table} {$where}", $params)->fetch()['count'];
        $positive = $this->db->query("SELECT COUNT(*) as count FROM {$this->table} WHERE feedback = 'positive' {$where}", $params)->fetch()['count'];
        $negative = $this->db->query("SELECT COUNT(*) as count FROM {$this->table} WHERE feedback = 'negative' {$where}", $params)->fetch()['count'];
        
        return [
            'total' => $total,
            'positive' => $positive,
            'negative' => $negative,
            'neutral' => $total - $positive - $negative
        ];
    }
}
