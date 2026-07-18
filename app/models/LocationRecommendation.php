<?php
/**
 * MyWisata Application - Location Recommendation Model
 * 
 * @package MyWisata
 * @version 1.0.0
 * @since 2026-07-18
 */

class LocationRecommendation extends Model {
    
    protected $table = 'location_recommendations';
    
    /**
     * Create recommendation
     */
    public function create($data) {
        $sql = "INSERT INTO {$this->table} (user_id, session_id, user_lat, user_lng, search_radius_km, recommended_destination_id, recommendation_score, distance_km) 
                VALUES (:user_id, :session_id, :user_lat, :user_lng, :search_radius_km, :recommended_destination_id, :recommendation_score, :distance_km)";
        return $this->execute($sql, $data);
    }
}
