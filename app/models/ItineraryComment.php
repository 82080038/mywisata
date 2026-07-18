<?php
/**
 * MyWisata Application - Itinerary Comment Model
 * 
 * @package MyWisata
 * @version 1.0.0
 * @since 2026-07-18
 */

class ItineraryComment extends Model {
    
    protected $table = 'itinerary_comments';
    
    /**
     * Create comment
     */
    public function create($data) {
        $sql = "INSERT INTO {$this->table} (itinerary_id, user_id, comment_text, parent_comment_id) 
                VALUES (:itinerary_id, :user_id, :comment_text, :parent_comment_id)";
        return $this->execute($sql, $data);
    }
    
    /**
     * Get by itinerary ID
     */
    public function getByItineraryId($itineraryId) {
        $sql = "SELECT * FROM {$this->table} WHERE itinerary_id = :itinerary_id AND parent_comment_id IS NULL ORDER BY created_at DESC";
        return $this->query($sql, ['itinerary_id' => $itineraryId]);
    }
}
