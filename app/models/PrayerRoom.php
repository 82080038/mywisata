<?php
/**
 * MyWisata Application - Prayer Room Model
 * 
 * @package MyWisata
 * @version 1.0.0
 * @since 2026-07-18
 */

class PrayerRoom extends Model {
    
    protected $table = 'prayer_rooms';
    
    /**
     * Get all active
     */
    public function getAllActive() {
        $sql = "SELECT * FROM {$this->table} WHERE is_active = 1 ORDER BY name";
        return $this->query($sql);
    }
    
    /**
     * Get nearby
     */
    public function getNearby($lat, $lng, $radiusKm) {
        // Using Haversine formula for distance calculation
        $sql = "SELECT *, (6371 * ACOS(COS(RADIANS(:lat)) * COS(RADIANS(latitude)) * COS(RADIANS(longitude) - RADIANS(:lng)) + SIN(RADIANS(:lat)) * SIN(RADIANS(latitude)))) AS distance 
                FROM {$this->table} 
                WHERE is_active = 1 
                HAVING distance <= :radius 
                ORDER BY distance ASC";
        return $this->query($sql, ['lat' => $lat, 'lng' => $lng, 'radius' => $radiusKm]);
    }
    
    /**
     * Find by ID
     */
    public function findById($id) {
        $sql = "SELECT * FROM {$this->table} WHERE id = :id LIMIT 1";
        return $this->query($sql, ['id' => $id])[0] ?? null;
    }
}
