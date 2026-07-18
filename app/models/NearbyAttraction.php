<?php
/**
 * MyWisata Application - Nearby Attraction Model
 * 
 * @package MyWisata
 * @version 1.0.0
 * @since 2026-07-18
 */

class NearbyAttraction extends Model {
    
    protected $table = 'nearby_attractions';
    
    /**
     * Get by user location
     */
    public function getByUserLocation($userId, $limit = 20) {
        $sql = "SELECT * FROM {$this->table} WHERE user_id = :user_id ORDER BY distance_km ASC LIMIT {$limit}";
        return $this->query($sql, ['user_id' => $userId]);
    }
    
    /**
     * Create attraction
     */
    public function create($data) {
        $sql = "INSERT INTO {$this->table} (user_id, destination_id, distance_km, calculated_at) 
                VALUES (:user_id, :destination_id, :distance_km, :calculated_at)";
        return $this->execute($sql, $data);
    }
}
