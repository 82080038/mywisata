<?php
/**
 * MyWisata Application - Itinerary Day Summary Model
 * 
 * @package MyWisata
 * @version 1.0.0
 * @since 2026-07-18
 */

class ItineraryDaySummary extends Model {
    
    protected $table = 'itinerary_day_summaries';
    
    /**
     * Get by itinerary ID
     */
    public function getByItineraryId($itineraryId) {
        $sql = "SELECT * FROM {$this->table} WHERE itinerary_id = :itinerary_id ORDER BY day_number";
        return $this->query($sql, ['itinerary_id' => $itineraryId]);
    }
    
    /**
     * Create summary
     */
    public function create($data) {
        $sql = "INSERT INTO {$this->table} (itinerary_id, day_number, date, summary_text, highlights, total_events) 
                VALUES (:itinerary_id, :day_number, :date, :summary_text, :highlights, :total_events)";
        return $this->execute($sql, $data);
    }
}
