<?php
namespace App\Models;

use App\Core\Model;

class CarbonEmission extends Model {
    protected $table = 'carbon_emissions';
    protected $primaryKey = 'id';
    
    /**
     * Get emissions by user
     */
    public function getByUserId($userId, $limit = 100) {
        return $this->db->query(
            "SELECT * FROM {$this->table} 
             WHERE user_id = ? 
             ORDER BY created_at DESC 
             LIMIT ?",
            [$userId, $limit]
        )->fetchAll();
    }
    
    /**
     * Get emissions by booking
     */
    public function getByBookingId($bookingId) {
        return $this->db->query(
            "SELECT * FROM {$this->table} 
             WHERE booking_id = ? 
             ORDER BY created_at ASC",
            [$bookingId]
        )->fetchAll();
    }
    
    /**
     * Calculate total CO2 for user
     */
    public function getTotalCO2ByUser($userId) {
        $result = $this->db->query(
            "SELECT SUM(co2_kg) as total FROM {$this->table} 
             WHERE user_id = ?",
            [$userId]
        )->fetch();
        return $result['total'] ?? 0;
    }
    
    /**
     * Create emission record
     */
    public function create($data) {
        $data['created_at'] = date('Y-m-d H:i:s');
        return $this->db->insert($this->table, $data);
    }
    
    /**
     * Get emissions by type
     */
    public function getByType($type, $userId = null) {
        $where = "emission_type = ?";
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
