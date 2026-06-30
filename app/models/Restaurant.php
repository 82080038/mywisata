<?php
/**
 * MyWisata Application - Restaurant Model
 * 
 * Handles restaurant related database operations.
 * 
 * @package MyWisata
 * @version 1.0.0
 * @since 2026-07-01
 */

class Restaurant extends Model {
    
    /**
     * Table name
     */
    protected $table = 'restaurants';
    
    /**
     * Get all restaurants
     * 
     * @param array $filters Optional filters
     * @return array
     */
    public function getAll($filters = []) {
        $where = ['1=1'];
        $params = [];
        
        if (!empty($filters['city'])) {
            $where[] = "city LIKE :city";
            $params['city'] = "%{$filters['city']}%";
        }
        
        if (!empty($filters['is_approved'])) {
            $where[] = "is_approved = :is_approved";
            $params['is_approved'] = $filters['is_approved'];
        }
        
        if (!empty($filters['search'])) {
            $where[] = "(name LIKE :search OR description LIKE :search)";
            $params['search'] = "%{$filters['search']}%";
        }
        
        $whereClause = implode(' AND ', $where);
        
        $sql = "SELECT r.*, 
                (SELECT AVG(rating) FROM restaurant_reviews WHERE restaurant_id = r.id) as rating_avg,
                (SELECT COUNT(*) FROM restaurant_reviews WHERE restaurant_id = r.id) as review_count
                FROM {$this->table} r 
                WHERE {$whereClause} 
                ORDER BY r.name";
        
        return $this->db->query($sql, $params)->fetchAll();
    }
    
    /**
     * Get restaurant by ID
     * 
     * @param int $id Restaurant ID
     * @return array|false
     */
    public function findById($id) {
        $sql = "SELECT r.*, 
                (SELECT AVG(rating) FROM restaurant_reviews WHERE restaurant_id = r.id) as rating_avg,
                (SELECT COUNT(*) FROM restaurant_reviews WHERE restaurant_id = r.id) as review_count
                FROM {$this->table} r 
                WHERE r.id = :id";
        
        return $this->db->query($sql, ['id' => $id])->fetch();
    }
    
    /**
     * Get restaurant menu items
     * 
     * @param int $restaurantId Restaurant ID
     * @return array
     */
    public function getMenuItems($restaurantId) {
        $sql = "SELECT * FROM restaurant_menu WHERE restaurant_id = :restaurant_id AND is_available = 1";
        return $this->db->query($sql, ['restaurant_id' => $restaurantId])->fetchAll();
    }
    
    /**
     * Get restaurant reviews
     * 
     * @param int $restaurantId Restaurant ID
     * @param int $limit Optional limit
     * @return array
     */
    public function getReviews($restaurantId, $limit = null) {
        $sql = "SELECT rr.*, u.name as user_name 
                FROM restaurant_reviews rr 
                LEFT JOIN users u ON rr.user_id = u.id 
                WHERE rr.restaurant_id = :restaurant_id 
                ORDER BY rr.created_at DESC";
        
        if ($limit) {
            $sql .= " LIMIT {$limit}";
        }
        
        return $this->db->query($sql, ['restaurant_id' => $restaurantId])->fetchAll();
    }
    
    /**
     * Add review
     * 
     * @param array $data Review data
     * @return bool
     */
    public function addReview($data) {
        $sql = "INSERT INTO restaurant_reviews 
                (restaurant_id, user_id, rating, comment, created_at)
                VALUES 
                (:restaurant_id, :user_id, :rating, :comment, NOW())";
        
        return $this->db->query($sql, $data);
    }
}
