<?php
/**
 * MyWisata Application - Location Search History Model
 * 
 * @package MyWisata
 * @version 1.0.0
 * @since 2026-07-18
 */

class LocationSearchHistory extends Model {
    
    protected $table = 'location_search_history';
    
    /**
     * Create search record
     */
    public function create($data) {
        $sql = "INSERT INTO {$this->table} (user_id, session_id, search_lat, search_lng, search_radius_km, search_query, results_count) 
                VALUES (:user_id, :session_id, :search_lat, :search_lng, :search_radius_km, :search_query, :results_count)";
        return $this->execute($sql, $data);
    }
}
