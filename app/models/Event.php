<?php
/**
 * MyWisata Application - Event Model
 * 
 * Handles event related database operations.
 * 
 * @package MyWisata
 * @version 1.0.0
 * @since 2026-07-01
 */

class Event extends Model {
    
    /**
     * Table name
     */
    protected $table = 'events';
    
    /**
     * Get all events with filters
     * 
     * @param array $filters Optional filters
     * @return array
     */
    public function getAllWithFilters($filters = []) {
        $where = ['1=1'];
        $params = [];
        
        if (!empty($filters['city'])) {
            $where[] = "city LIKE :city";
            $params['city'] = "%{$filters['city']}%";
        }
        
        if (!empty($filters['is_approved'])) {
            $where[] = "is_active = :is_active";
            $params['is_active'] = $filters['is_approved'];
        }
        
        if (!empty($filters['search'])) {
            $where[] = "(e.title LIKE :search_name OR e.description LIKE :search_desc)";
            $params['search_name'] = "%{$filters['search']}%";
            $params['search_desc'] = "%{$filters['search']}%";
        }
        
        if (!empty($filters['upcoming'])) {
            $where[] = "start_date >= CURDATE()";
        }
        
        $whereClause = implode(' AND ', $where);
        
        $sql = "SELECT e.*, 
                (SELECT AVG(rating) FROM event_reviews WHERE event_id = e.id) as rating_avg,
                (SELECT COUNT(*) FROM event_reviews WHERE event_id = e.id) as review_count
                FROM {$this->table} e 
                WHERE {$whereClause} 
                ORDER BY e.start_date ASC";
        
        return $this->db->query($sql, $params)->fetchAll();
    }
    
    /**
     * Get event by ID
     * 
     * @param int $id Event ID
     * @return array|false
     */
    public function findById($id) {
        $sql = "SELECT e.*, 
                (SELECT AVG(rating) FROM event_reviews WHERE event_id = e.id) as rating_avg,
                (SELECT COUNT(*) FROM event_reviews WHERE event_id = e.id) as review_count
                FROM {$this->table} e 
                WHERE e.id = :id";
        
        return $this->db->query($sql, ['id' => $id])->fetch();
    }
    
    /**
     * Get upcoming events
     * 
     * @param int $limit Optional limit
     * @return array
     */
    public function getUpcoming($limit = 6) {
        $sql = "SELECT e.*, 
                (SELECT AVG(rating) FROM event_reviews WHERE event_id = e.id) as rating_avg
                FROM {$this->table} e 
                WHERE e.is_approved = 1 AND e.event_date >= CURDATE()
                ORDER BY e.event_date ASC
                LIMIT :limit";
        
        return $this->db->query($sql, ['limit' => $limit])->fetchAll();
    }
    
    /**
     * Get event reviews
     * 
     * @param int $eventId Event ID
     * @param int $limit Optional limit
     * @return array
     */
    public function getReviews($eventId, $limit = null) {
        $sql = "SELECT er.*, u.name as user_name 
                FROM event_reviews er 
                LEFT JOIN users u ON er.user_id = u.id 
                WHERE er.event_id = :event_id 
                ORDER BY er.created_at DESC";
        
        if ($limit) {
            $sql .= " LIMIT {$limit}";
        }
        
        return $this->db->query($sql, ['event_id' => $eventId])->fetchAll();
    }
    
    /**
     * Add review
     * 
     * @param array $data Review data
     * @return bool
     */
    public function addReview($data) {
        $sql = "INSERT INTO event_reviews 
                (event_id, user_id, rating, comment, created_at)
                VALUES 
                (:event_id, :user_id, :rating, :comment, NOW())";
        
        return $this->db->query($sql, $data);
    }
}
