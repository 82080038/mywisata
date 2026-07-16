<?php
/**
 * MyWisata Application - Review Model
 * 
 * Handles review and rating related database operations.
 * 
 * @package MyWisata
 * @version 1.0.0
 * @since 2026-07-16
 */

class Review extends Model {
    
    /**
     * Table name
     */
    protected $table = 'reviews';
    
    /**
     * Create a new review
     * 
     * @param array $data Review data
     * @return int Review ID
     */
    public function create($data) {
        $sql = "INSERT INTO {$this->table} 
                (user_id, reviewable_type, reviewable_id, rating, comment, created_at)
                VALUES 
                (:user_id, :reviewable_type, :reviewable_id, :rating, :comment, NOW())";
        
        $this->db->query($sql, $data);
        return $this->db->lastInsertId();
    }
    
    /**
     * Get review by ID
     * 
     * @param int $id Review ID
     * @return array|false
     */
    public function findById($id) {
        $sql = "SELECT r.*, u.name as user_name, u.email as user_email
                FROM {$this->table} r 
                LEFT JOIN users u ON r.user_id = u.id 
                WHERE r.id = :id";
        
        return $this->db->query($sql, ['id' => $id])->fetch();
    }
    
    /**
     * Get reviews by reviewable type and ID
     * 
     * @param string $reviewableType Type (destination, hotel, restaurant, event, tour_guide)
     * @param int $reviewableId ID of the item
     * @param int $page Page number
     * @param int $limit Items per page
     * @return array
     */
    public function getByReviewable($reviewableType, $reviewableId, $page = 1, $limit = 10) {
        $offset = ($page - 1) * $limit;
        
        $sql = "SELECT r.*, u.name as user_name, u.email as user_email
                FROM {$this->table} r 
                LEFT JOIN users u ON r.user_id = u.id 
                WHERE r.reviewable_type = :reviewable_type 
                AND r.reviewable_id = :reviewable_id
                AND r.is_approved = 1
                ORDER BY r.created_at DESC
                LIMIT :limit OFFSET :offset";
        
        return $this->db->query($sql, [
            'reviewable_type' => $reviewableType,
            'reviewable_id' => $reviewableId,
            'limit' => $limit,
            'offset' => $offset
        ])->fetchAll();
    }
    
    /**
     * Get reviews by user
     * 
     * @param int $userId User ID
     * @param int $page Page number
     * @param int $limit Items per page
     * @return array
     */
    public function getByUser($userId, $page = 1, $limit = 10) {
        $offset = ($page - 1) * $limit;
        
        $sql = "SELECT r.*, 
                CASE r.reviewable_type
                    WHEN 'destination' THEN (SELECT name FROM destinations WHERE id = r.reviewable_id)
                    WHEN 'hotel' THEN (SELECT name FROM hotels WHERE id = r.reviewable_id)
                    WHEN 'restaurant' THEN (SELECT name FROM restaurants WHERE id = r.reviewable_id)
                    WHEN 'event' THEN (SELECT title FROM events WHERE id = r.reviewable_id)
                    WHEN 'tour_guide' THEN (SELECT name FROM tour_guides WHERE id = r.reviewable_id)
                END as item_name
                FROM {$this->table} r 
                WHERE r.user_id = :user_id
                ORDER BY r.created_at DESC
                LIMIT :limit OFFSET :offset";
        
        return $this->db->query($sql, [
            'user_id' => $userId,
            'limit' => $limit,
            'offset' => $offset
        ])->fetchAll();
    }
    
    /**
     * Count reviews by reviewable type and ID
     * 
     * @param string $reviewableType Type
     * @param int $reviewableId ID
     * @return int
     */
    public function countByReviewable($reviewableType, $reviewableId) {
        $sql = "SELECT COUNT(*) as count
                FROM {$this->table} 
                WHERE reviewable_type = :reviewable_type 
                AND reviewable_id = :reviewable_id
                AND is_approved = 1";
        
        $result = $this->db->query($sql, [
            'reviewable_type' => $reviewableType,
            'reviewable_id' => $reviewableId
        ])->fetch();
        
        return $result['count'];
    }
    
    /**
     * Count reviews by user
     * 
     * @param int $userId User ID
     * @return int
     */
    public function countByUser($userId) {
        $sql = "SELECT COUNT(*) as count
                FROM {$this->table} 
                WHERE user_id = :user_id";
        
        $result = $this->db->query($sql, ['user_id' => $userId])->fetch();
        
        return $result['count'];
    }
    
    /**
     * Get average rating for an item
     * 
     * @param string $reviewableType Type
     * @param int $reviewableId ID
     * @return float
     */
    public function getAverageRating($reviewableType, $reviewableId) {
        $sql = "SELECT COALESCE(AVG(rating), 0) as average
                FROM {$this->table} 
                WHERE reviewable_type = :reviewable_type 
                AND reviewable_id = :reviewable_id
                AND is_approved = 1";
        
        $result = $this->db->query($sql, [
            'reviewable_type' => $reviewableType,
            'reviewable_id' => $reviewableId
        ])->fetch();
        
        return round($result['average'], 1);
    }
    
    /**
     * Check if user has reviewed an item
     * 
     * @param int $userId User ID
     * @param string $reviewableType Type
     * @param int $reviewableId ID
     * @return bool
     */
    public function hasUserReviewed($userId, $reviewableType, $reviewableId) {
        $sql = "SELECT COUNT(*) as count
                FROM {$this->table} 
                WHERE user_id = :user_id 
                AND reviewable_type = :reviewable_type 
                AND reviewable_id = :reviewable_id";
        
        $result = $this->db->query($sql, [
            'user_id' => $userId,
            'reviewable_type' => $reviewableType,
            'reviewable_id' => $reviewableId
        ])->fetch();
        
        return $result['count'] > 0;
    }
    
    /**
     * Update a review
     * 
     * @param int $id Review ID
     * @param array $data Data to update
     * @return bool
     */
    public function update($id, $data) {
        $sql = "UPDATE {$this->table} 
                SET rating = :rating, comment = :comment, updated_at = NOW() 
                WHERE id = :id";
        
        $data['id'] = $id;
        return $this->db->query($sql, $data);
    }
    
    /**
     * Delete a review
     * 
     * @param int $id Review ID
     * @return bool
     */
    public function delete($id) {
        $sql = "DELETE FROM {$this->table} WHERE id = :id";
        return $this->db->query($sql, ['id' => $id]);
    }
    
    /**
     * Approve a review
     * 
     * @param int $id Review ID
     * @return bool
     */
    public function approve($id) {
        $sql = "UPDATE {$this->table} SET is_approved = 1, updated_at = NOW() WHERE id = :id";
        return $this->db->query($sql, ['id' => $id]);
    }
    
    /**
     * Reject a review
     * 
     * @param int $id Review ID
     * @return bool
     */
    public function reject($id) {
        $sql = "UPDATE {$this->table} SET is_approved = 0, updated_at = NOW() WHERE id = :id";
        return $this->db->query($sql, ['id' => $id]);
    }
    
    /**
     * Flag a review for moderation
     * 
     * @param int $reviewId Review ID
     * @param int $userId User ID who flagged
     * @param string $reason Reason for flagging
     * @return bool
     */
    public function flag($reviewId, $userId, $reason = null) {
        $sql = "INSERT INTO review_flags (review_id, user_id, reason, created_at)
                VALUES (:review_id, :user_id, :reason, NOW())";
        
        return $this->db->query($sql, [
            'review_id' => $reviewId,
            'user_id' => $userId,
            'reason' => $reason
        ]);
    }
    
    /**
     * Check if user has flagged a review
     * 
     * @param int $userId User ID
     * @param int $reviewId Review ID
     * @return bool
     */
    public function hasUserFlagged($userId, $reviewId) {
        $sql = "SELECT COUNT(*) as count
                FROM review_flags 
                WHERE user_id = :user_id AND review_id = :review_id";
        
        $result = $this->db->query($sql, [
            'user_id' => $userId,
            'review_id' => $reviewId
        ])->fetch();
        
        return $result['count'] > 0;
    }
    
    /**
     * Get flagged reviews
     * 
     * @param int $page Page number
     * @param int $limit Items per page
     * @return array
     */
    public function getFlaggedReviews($page = 1, $limit = 10) {
        $offset = ($page - 1) * $limit;
        
        $sql = "SELECT r.*, u.name as user_name, 
                (SELECT COUNT(*) FROM review_flags WHERE review_id = r.id) as flag_count
                FROM {$this->table} r 
                LEFT JOIN users u ON r.user_id = u.id 
                WHERE r.id IN (SELECT DISTINCT review_id FROM review_flags)
                ORDER BY r.created_at DESC
                LIMIT :limit OFFSET :offset";
        
        return $this->db->query($sql, [
            'limit' => $limit,
            'offset' => $offset
        ])->fetchAll();
    }
    
    /**
     * Get pending reviews (not yet approved)
     * 
     * @param int $page Page number
     * @param int $limit Items per page
     * @return array
     */
    public function getPendingReviews($page = 1, $limit = 10) {
        $offset = ($page - 1) * $limit;
        
        $sql = "SELECT r.*, u.name as user_name
                FROM {$this->table} r 
                LEFT JOIN users u ON r.user_id = u.id 
                WHERE r.is_approved = 0
                ORDER BY r.created_at DESC
                LIMIT :limit OFFSET :offset";
        
        return $this->db->query($sql, [
            'limit' => $limit,
            'offset' => $offset
        ])->fetchAll();
    }
}
