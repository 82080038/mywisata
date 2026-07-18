<?php
/**
 * MyWisata Application - Itinerary Template Model
 * 
 * @package MyWisata
 * @version 1.0.0
 * @since 2026-07-18
 */

class ItineraryTemplate extends Model {
    
    protected $table = 'itinerary_templates';
    
    /**
     * Get active templates
     */
    public function getActive($page = 1, $limit = 12, $destinationId = null, $durationDays = null) {
        $offset = ($page - 1) * $limit;
        $sql = "SELECT * FROM {$this->table} WHERE is_active = 1";
        $params = [];
        
        if ($destinationId) {
            $sql .= " AND destination_id = :destination_id";
            $params['destination_id'] = $destinationId;
        }
        
        if ($durationDays) {
            $sql .= " AND duration_days = :duration_days";
            $params['duration_days'] = $durationDays;
        }
        
        $sql .= " ORDER BY created_at DESC LIMIT {$limit} OFFSET {$offset}";
        return $this->query($sql, $params);
    }
    
    /**
     * Find by ID
     */
    public function findById($id) {
        $sql = "SELECT * FROM {$this->table} WHERE id = :id LIMIT 1";
        return $this->query($sql, ['id' => $id])[0] ?? null;
    }
}
