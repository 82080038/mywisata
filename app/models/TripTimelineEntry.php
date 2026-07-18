<?php
namespace App\Models;

use App\Core\Model;

class TripTimelineEntry extends Model {
    protected $table = 'trip_timeline_entries';
    protected $primaryKey = 'id';
    
    /**
     * Get timeline by itinerary
     */
    public function getByItineraryId($itineraryId) {
        return $this->db->query(
            "SELECT * FROM {$this->table} 
             WHERE itinerary_id = ? 
             ORDER BY day_number ASC, time ASC",
            [$itineraryId]
        )->fetchAll();
    }
    
    /**
     * Create timeline entry
     */
    public function create($data) {
        $data['created_at'] = date('Y-m-d H:i:s');
        return $this->db->insert($this->table, $data);
    }
    
    /**
     * Update completion status
     */
    public function updateCompletion($id, $isCompleted) {
        $data = ['is_completed' => $isCompleted];
        return $this->db->update($this->table, $data, "id = ?", [$id]);
    }
    
    /**
     * Get timeline by day
     */
    public function getByDay($itineraryId, $dayNumber) {
        return $this->db->query(
            "SELECT * FROM {$this->table} 
             WHERE itinerary_id = ? AND day_number = ? 
             ORDER BY time ASC",
            [$itineraryId, $dayNumber]
        )->fetchAll();
    }
}
