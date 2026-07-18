<?php
/**
 * MyWisata Application - Itinerary Timeline Event Model
 * 
 * @package MyWisata
 * @version 1.0.0
 * @since 2026-07-18
 */

class ItineraryTimelineEvent extends Model {
    
    protected $table = 'itinerary_timeline_events';
    
    /**
     * Get by itinerary ID
     */
    public function getByItineraryId($itineraryId) {
        $sql = "SELECT * FROM {$this->table} WHERE itinerary_id = :itinerary_id ORDER BY day_number, event_order";
        return $this->query($sql, ['itinerary_id' => $itineraryId]);
    }
    
    /**
     * Create event
     */
    public function create($data) {
        $sql = "INSERT INTO {$this->table} (itinerary_id, day_number, event_order, event_type, event_title, event_description, start_time, end_time, duration_minutes, is_mandatory) 
                VALUES (:itinerary_id, :day_number, :event_order, :event_type, :event_title, :event_description, :start_time, :end_time, :duration_minutes, :is_mandatory)";
        return $this->execute($sql, $data);
    }
}
