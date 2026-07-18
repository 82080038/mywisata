<?php
namespace App\Models;

use App\Core\Model;

class EcoAction extends Model {
    protected $table = 'eco_actions';
    protected $primaryKey = 'id';
    
    /**
     * Get actions by user
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
     * Create eco action
     */
    public function create($data) {
        $data['created_at'] = date('Y-m-d H:i:s');
        return $this->db->insert($this->table, $data);
    }
    
    /**
     * Get total CO2 saved by user
     */
    public function getTotalCO2Saved($userId) {
        $result = $this->db->query(
            "SELECT SUM(co2_saved_kg) as total FROM {$this->table} 
             WHERE user_id = ?",
            [$userId]
        )->fetch();
        return $result['total'] ?? 0;
    }
    
    /**
     * Get total points earned by user
     */
    public function getTotalPoints($userId) {
        $result = $this->db->query(
            "SELECT SUM(points_earned) as total FROM {$this->table} 
             WHERE user_id = ?",
            [$userId]
        )->fetch();
        return $result['total'] ?? 0;
    }
    
    /**
     * Get actions by type
     */
    public function getByType($type, $userId = null) {
        $where = "action_type = ?";
        $params = [$type];
        
        if ($userId) {
            $where .= " AND user_id = ?";
            $params[] = $userId;
        }
        
        return $this->db->query(
            "SELECT * FROM {$this->table} 
             WHERE {$where} 
             ORDER BY created_at DESC",
            $params
        )->fetchAll();
    }
}
