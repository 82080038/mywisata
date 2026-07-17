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
     * Get all restaurants with filters
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
        
        if (!empty($filters['type'])) {
            $where[] = "type = :type";
            $params['type'] = $filters['type'];
        }
        
        if (!empty($filters['is_approved'])) {
            $where[] = "is_approved = :is_approved";
            $params['is_approved'] = $filters['is_approved'];
        }
        
        if (!empty($filters['is_halal'])) {
            $where[] = "is_halal = :is_halal";
            $params['is_halal'] = $filters['is_halal'];
        }
        
        if (!empty($filters['is_kosher'])) {
            $where[] = "is_kosher = :is_kosher";
            $params['is_kosher'] = $filters['is_kosher'];
        }
        
        if (!empty($filters['is_vegan_friendly'])) {
            $where[] = "is_vegan_friendly = :is_vegan_friendly";
            $params['is_vegan_friendly'] = $filters['is_vegan_friendly'];
        }
        
        if (!empty($filters['is_vegetarian_friendly'])) {
            $where[] = "is_vegetarian_friendly = :is_vegetarian_friendly";
            $params['is_vegetarian_friendly'] = $filters['is_vegetarian_friendly'];
        }
        
        if (!empty($filters['is_gluten_free_friendly'])) {
            $where[] = "is_gluten_free_friendly = :is_gluten_free_friendly";
            $params['is_gluten_free_friendly'] = $filters['is_gluten_free_friendly'];
        }
        
        if (!empty($filters['has_prayer_space'])) {
            $where[] = "has_prayer_space = :has_prayer_space";
            $params['has_prayer_space'] = $filters['has_prayer_space'];
        }
        
        if (!empty($filters['is_alcohol_free'])) {
            $where[] = "is_alcohol_free = :is_alcohol_free";
            $params['is_alcohol_free'] = $filters['is_alcohol_free'];
        }
        
        if (!empty($filters['search'])) {
            $where[] = "(r.name LIKE :search_name OR r.description LIKE :search_desc)";
            $params['search_name'] = "%{$filters['search']}%";
            $params['search_desc'] = "%{$filters['search']}%";
        }
        
        $whereClause = implode(' AND ', $where);
        
        $sql = "SELECT r.*, 
                (SELECT AVG(rating) FROM reviews WHERE reviewable_type = 'restaurant' AND reviewable_id = r.id) as rating_avg,
                (SELECT COUNT(*) FROM reviews WHERE reviewable_type = 'restaurant' AND reviewable_id = r.id) as review_count
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
                (SELECT AVG(rating) FROM reviews WHERE reviewable_type = 'restaurant' AND reviewable_id = r.id) as rating_avg,
                (SELECT COUNT(*) FROM reviews WHERE reviewable_type = 'restaurant' AND reviewable_id = r.id) as review_count
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
        $sql = "SELECT r.*, u.name as user_name 
                FROM reviews r 
                LEFT JOIN users u ON r.user_id = u.id 
                WHERE r.reviewable_type = 'restaurant' AND r.reviewable_id = :restaurant_id 
                ORDER BY r.created_at DESC";
        
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
        $sql = "INSERT INTO reviews 
                (reviewable_type, reviewable_id, user_id, rating, comment, created_at)
                VALUES 
                ('restaurant', :restaurant_id, :user_id, :rating, :comment, NOW())";
        
        return $this->db->query($sql, $data);
    }
    
    /**
     * Update rating
     * 
     * @param int $restaurantId Restaurant ID
     * @return bool
     */
    public function updateRating($restaurantId) {
        $sql = "UPDATE {$this->table} 
                SET rating_avg = (
                    SELECT COALESCE(AVG(rating), 0) 
                    FROM reviews 
                    WHERE reviewable_type = 'restaurant' AND reviewable_id = :restaurant_id
                )
                WHERE id = :restaurant_id";
        
        return $this->db->query($sql, ['restaurant_id' => $restaurantId]);
    }
}
