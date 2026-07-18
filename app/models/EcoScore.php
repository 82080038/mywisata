<?php
namespace App\Models;

use App\Core\Model;

class EcoScore extends Model {
    protected $table = 'eco_scores';
    protected $primaryKey = 'id';
    
    /**
     * Get eco score by user
     */
    public function getByUserId($userId) {
        return $this->db->query(
            "SELECT * FROM {$this->table} 
             WHERE user_id = ?",
            [$userId]
        )->fetch();
    }
    
    /**
     * Create or update eco score
     */
    public function updateScore($userId, $data) {
        $existing = $this->getByUserId($userId);
        
        if ($existing) {
            $data['updated_at'] = date('Y-m-d H:i:s');
            return $this->db->update($this->table, $data, "user_id = ?", [$userId]);
        } else {
            $data['user_id'] = $userId;
            $data['created_at'] = date('Y-m-d H:i:s');
            $data['updated_at'] = date('Y-m-d H:i:s');
            return $this->db->insert($this->table, $data);
        }
    }
    
    /**
     * Calculate eco level based on score
     */
    public function calculateLevel($score) {
        if ($score >= 90) return 'platinum';
        if ($score >= 70) return 'gold';
        if ($score >= 50) return 'silver';
        return 'bronze';
    }
    
    /**
     * Get leaderboard
     */
    public function getLeaderboard($limit = 10) {
        return $this->db->query(
            "SELECT es.*, u.name, u.email 
             FROM {$this->table} es 
             JOIN users u ON es.user_id = u.id 
             ORDER BY es.score DESC 
             LIMIT ?",
            [$limit]
        )->fetchAll();
    }
}
