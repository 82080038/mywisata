<?php
/**
 * MyWisata Application - Geofence Zone Model
 * 
 * @package MyWisata
 * @version 1.0.0
 * @since 2026-07-18
 */

class GeofenceZone extends Model {
    
    protected $table = 'geofence_zones';
    
    /**
     * Get active zones
     */
    public function getActive() {
        $sql = "SELECT * FROM {$this->table} WHERE is_active = 1";
        return $this->query($sql);
    }
    
    /**
     * Find by ID
     */
    public function findById($id) {
        $sql = "SELECT * FROM {$this->table} WHERE id = :id LIMIT 1";
        return $this->query($sql, ['id' => $id])[0] ?? null;
    }
}
