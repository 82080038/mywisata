<?php
namespace App\Models;

use App\Core\Model;

class LowCarbonRoute extends Model {
    protected $table = 'low_carbon_routes';
    protected $primaryKey = 'id';
    
    /**
     * Get routes between destinations
     */
    public function getRoutes($originId, $destinationId) {
        return $this->db->query(
            "SELECT * FROM {$this->table} 
             WHERE origin_id = ? AND destination_id = ? 
             ORDER BY co2_kg ASC",
            [$originId, $destinationId]
        )->fetchAll();
    }
    
    /**
     * Get recommended routes
     */
    public function getRecommendedRoutes($originId, $destinationId) {
        return $this->db->query(
            "SELECT * FROM {$this->table} 
             WHERE origin_id = ? AND destination_id = ? AND is_recommended = 1 
             ORDER BY co2_kg ASC",
            [$originId, $destinationId]
        )->fetchAll();
    }
    
    /**
     * Create route
     */
    public function create($data) {
        $data['created_at'] = date('Y-m-d H:i:s');
        return $this->db->insert($this->table, $data);
    }
    
    /**
     * Get routes by transport mode
     */
    public function getByTransportMode($transportMode) {
        return $this->db->query(
            "SELECT * FROM {$this->table} 
             WHERE transport_mode = ? 
             ORDER BY created_at DESC",
            [$transportMode]
        )->fetchAll();
    }
}
