<?php
/**
 * MyWisata Application - Itinerary Template Event Model
 * 
 * @package MyWisata
 * @version 1.0.0
 * @since 2026-07-18
 */

class ItineraryTemplateEvent extends Model {
    
    protected $table = 'itinerary_template_events';
    
    /**
     * Get by template ID
     */
    public function getByTemplateId($templateId) {
        $sql = "SELECT * FROM {$this->table} WHERE template_id = :template_id ORDER BY day_number, event_order";
        return $this->query($sql, ['template_id' => $templateId]);
    }
    
    /**
     * Create event
     */
    public function create($data) {
        $sql = "INSERT INTO {$this->table} (template_id, day_number, event_order, event_type, event_title, event_description, start_time, end_time, duration_minutes, is_optional) 
                VALUES (:template_id, :day_number, :event_order, :event_type, :event_title, :event_description, :start_time, :end_time, :duration_minutes, :is_optional)";
        return $this->execute($sql, $data);
    }
}
