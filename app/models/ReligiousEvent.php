<?php
/**
 * MyWisata Application - Religious Event Model
 * 
 * @package MyWisata
 * @version 1.0.0
 * @since 2026-07-18
 */

class ReligiousEvent extends Model {
    
    protected $table = 'religious_events';
    
    /**
     * Get active events
     */
    public function getActive($page = 1, $limit = 12, $eventType = 'all') {
        $offset = ($page - 1) * $limit;
        $sql = "SELECT * FROM {$this->table} WHERE is_active = 1";
        
        if ($eventType !== 'all') {
            $sql .= " AND event_type = :event_type";
        }
        
        $sql .= " ORDER BY event_date ASC LIMIT {$limit} OFFSET {$offset}";
        
        $params = $eventType !== 'all' ? ['event_type' => $eventType] : [];
        return $this->query($sql, $params);
    }
    
    /**
     * Find by ID
     */
    public function findById($id) {
        $sql = "SELECT * FROM {$this->table} WHERE id = :id LIMIT 1";
        return $this->query($sql, ['id' => $id])[0] ?? null;
    }
    
    /**
     * Create event
     */
    public function create($data) {
        $sql = "INSERT INTO {$this->table} (event_name, event_type, description, event_date, event_time, location, latitude, longitude, max_participants, registration_fee, currency, is_active, is_featured, image_url) 
                VALUES (:event_name, :event_type, :description, :event_date, :event_time, :location, :latitude, :longitude, :max_participants, :registration_fee, :currency, :is_active, :is_featured, :image_url)";
        return $this->execute($sql, $data);
    }
}
