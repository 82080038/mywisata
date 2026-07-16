<?php
/**
 * MyWisata Application - Itinerary Model
 * 
 * Handles itinerary builder database operations.
 * 
 * @package MyWisata
 * @version 1.0.0
 * @since 2026-07-16
 */

class Itinerary extends Model {
    
    /**
     * Table name
     */
    protected $table = 'itineraries';
    
    /**
     * Create itinerary
     * 
     * @param array $data Itinerary data
     * @return int Itinerary ID
     */
    public function create($data) {
        $sql = "INSERT INTO {$this->table} 
                (user_id, title, description, start_date, end_date, budget, participants, is_public, created_at)
                VALUES 
                (:user_id, :title, :description, :start_date, :end_date, :budget, :participants, :is_public, NOW())";
        
        $this->db->query($sql, $data);
        return $this->db->lastInsertId();
    }
    
    /**
     * Get itinerary by ID
     * 
     * @param int $id Itinerary ID
     * @return array|false
     */
    public function findById($id) {
        $sql = "SELECT i.*, u.name as user_name, u.email as user_email
                FROM {$this->table} i 
                LEFT JOIN users u ON i.user_id = u.id 
                WHERE i.id = :id";
        
        return $this->db->query($sql, ['id' => $id])->fetch();
    }
    
    /**
     * Get itineraries by user ID
     * 
     * @param int $userId User ID
     * @return array
     */
    public function getByUserId($userId) {
        $sql = "SELECT i.*, 
                (SELECT COUNT(*) FROM itinerary_items WHERE itinerary_id = i.id) as item_count
                FROM {$this->table} i 
                WHERE i.user_id = :user_id 
                ORDER BY i.created_at DESC";
        
        return $this->db->query($sql, ['user_id' => $userId])->fetchAll();
    }
    
    /**
     * Update itinerary
     * 
     * @param int $id Itinerary ID
     * @param array $data Data to update
     * @return bool
     */
    public function update($id, $data) {
        $sql = "UPDATE {$this->table} 
                SET title = :title,
                    description = :description,
                    start_date = :start_date,
                    end_date = :end_date,
                    budget = :budget,
                    participants = :participants,
                    is_public = :is_public,
                    updated_at = NOW()
                WHERE id = :id";
        
        $data['id'] = $id;
        return $this->db->query($sql, $data);
    }
    
    /**
     * Delete itinerary
     * 
     * @param int $id Itinerary ID
     * @return bool
     */
    public function delete($id) {
        $sql = "DELETE FROM {$this->table} WHERE id = :id";
        return $this->db->query($sql, ['id' => $id]);
    }
    
    /**
     * Add item to itinerary
     * 
     * @param array $data Item data
     * @return int Item ID
     */
    public function addItem($data) {
        $sql = "INSERT INTO itinerary_items 
                (itinerary_id, item_type, item_id, day_number, start_time, end_time, notes, order_index, created_at)
                VALUES 
                (:itinerary_id, :item_type, :item_id, :day_number, :start_time, :end_time, :notes, :order_index, NOW())";
        
        $this->db->query($sql, $data);
        return $this->db->lastInsertId();
    }
    
    /**
     * Get itinerary items
     * 
     * @param int $itineraryId Itinerary ID
     * @return array
     */
    public function getItems($itineraryId) {
        $sql = "SELECT ii.*,
                CASE ii.item_type
                    WHEN 'destination' THEN (SELECT name FROM destinations WHERE id = ii.item_id)
                    WHEN 'hotel' THEN (SELECT name FROM hotels WHERE id = ii.item_id)
                    WHEN 'restaurant' THEN (SELECT name FROM restaurants WHERE id = ii.item_id)
                    WHEN 'event' THEN (SELECT title FROM events WHERE id = ii.item_id)
                    WHEN 'tour_guide' THEN (SELECT name FROM tour_guides WHERE id = ii.item_id)
                END as item_name,
                CASE ii.item_type
                    WHEN 'destination' THEN (SELECT image FROM destinations WHERE id = ii.item_id)
                    WHEN 'hotel' THEN (SELECT image FROM hotels WHERE id = ii.item_id)
                    WHEN 'restaurant' THEN (SELECT image FROM restaurants WHERE id = ii.item_id)
                    WHEN 'event' THEN (SELECT image FROM events WHERE id = ii.item_id)
                    WHEN 'tour_guide' THEN (SELECT avatar FROM tour_guides WHERE id = ii.item_id)
                END as item_image
                FROM itinerary_items ii 
                WHERE ii.itinerary_id = :itinerary_id 
                ORDER BY ii.day_number ASC, ii.order_index ASC";
        
        return $this->db->query($sql, ['itinerary_id' => $itineraryId])->fetchAll();
    }
    
    /**
     * Get item by ID
     * 
     * @param int $itemId Item ID
     * @return array|false
     */
    public function getItemById($itemId) {
        $sql = "SELECT * FROM itinerary_items WHERE id = :id";
        return $this->db->query($sql, ['id' => $itemId])->fetch();
    }
    
    /**
     * Update item
     * 
     * @param int $itemId Item ID
     * @param array $data Data to update
     * @return bool
     */
    public function updateItem($itemId, $data) {
        $sql = "UPDATE itinerary_items 
                SET day_number = :day_number,
                    start_time = :start_time,
                    end_time = :end_time,
                    notes = :notes,
                    order_index = :order_index,
                    updated_at = NOW()
                WHERE id = :id";
        
        $data['id'] = $itemId;
        return $this->db->query($sql, $data);
    }
    
    /**
     * Remove item
     * 
     * @param int $itemId Item ID
     * @return bool
     */
    public function removeItem($itemId) {
        $sql = "DELETE FROM itinerary_items WHERE id = :id";
        return $this->db->query($sql, ['id' => $itemId]);
    }
    
    /**
     * Generate share token
     * 
     * @param int $itineraryId Itinerary ID
     * @return string Share token
     */
    public function generateShareToken($itineraryId) {
        $token = bin2hex(random_bytes(32));
        $expiry = date('Y-m-d H:i:s', strtotime('+30 days'));
        
        $sql = "INSERT INTO itinerary_shares (itinerary_id, share_token, expires_at, created_at)
                VALUES (:itinerary_id, :share_token, :expires_at, NOW())
                ON DUPLICATE KEY UPDATE 
                share_token = :share_token, 
                expires_at = :expires_at, 
                created_at = NOW()";
        
        $this->db->query($sql, [
            'itinerary_id' => $itineraryId,
            'share_token' => $token,
            'expires_at' => $expiry
        ]);
        
        return $token;
    }
    
    /**
     * Get itinerary by share token
     * 
     * @param string $shareToken Share token
     * @return array|false
     */
    public function getByShareToken($shareToken) {
        $sql = "SELECT i.*, u.name as user_name
                FROM itinerary_shares s
                LEFT JOIN {$this->table} i ON s.itinerary_id = i.id
                LEFT JOIN users u ON i.user_id = u.id
                WHERE s.share_token = :share_token 
                AND s.expires_at > NOW()";
        
        return $this->db->query($sql, ['share_token' => $shareToken])->fetch();
    }
    
    /**
     * Get itinerary summary
     * 
     * @param int $itineraryId Itinerary ID
     * @return array
     */
    public function getSummary($itineraryId) {
        $sql = "SELECT 
                COUNT(*) as total_items,
                COUNT(DISTINCT day_number) as total_days,
                SUM(CASE WHEN item_type = 'destination' THEN 1 ELSE 0 END) as destinations,
                SUM(CASE WHEN item_type = 'hotel' THEN 1 ELSE 0 END) as hotels,
                SUM(CASE WHEN item_type = 'restaurant' THEN 1 ELSE 0 END) as restaurants,
                SUM(CASE WHEN item_type = 'tour_guide' THEN 1 ELSE 0 END) as tour_guides
                FROM itinerary_items 
                WHERE itinerary_id = :itinerary_id";
        
        return $this->db->query($sql, ['itinerary_id' => $itineraryId])->fetch();
    }
}
