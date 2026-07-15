<?php
/**
 * MyWisata Application - Review Model
 * 
 * Handles review database operations.
 * 
 * @package MyWisata
 * @version 1.0.0
 * @since 2026-07-15
 */

class ReviewModel extends Model {
    
    /**
     * Table name
     */
    protected $table = 'reviews';
    
    /**
     * Find review by ID
     * 
     * @param int $id Review ID
     * @return array|false
     */
    public function findById($id) {
        $sql = "SELECT r.*, u.name as user_name, u.avatar as user_avatar 
                FROM {$this->table} r 
                LEFT JOIN users u ON r.user_id = u.id 
                WHERE r.id = :id";
        
        return $this->db->query($sql, ['id' => $id])->fetch();
    }
    
    /**
     * Get reviews by entity
     * 
     * @param string $type Entity type
     * @param int $entityId Entity ID
     * @return array
     */
    public function getByEntity($type, $entityId) {
        $sql = "SELECT r.*, u.name as user_name, u.avatar as user_avatar 
                FROM {$this->table} r 
                LEFT JOIN users u ON r.user_id = u.id 
                WHERE r.reviewable_type = :type 
                AND r.reviewable_id = :entity_id 
                AND r.is_published = 1 
                ORDER BY r.created_at DESC";
        
        return $this->db->query($sql, [
            'type' => $type,
            'entity_id' => $entityId
        ])->fetchAll();
    }
    
    /**
     * Create review
     * 
     * @param array $data Review data
     * @return int Review ID
     */
    public function create($data) {
        $sql = "INSERT INTO {$this->table} 
                (user_id, reviewable_type, reviewable_id, rating, comment, photos, photo_count, is_published, created_at, updated_at)
                VALUES (:user_id, :reviewable_type, :reviewable_id, :rating, :comment, :photos, :photo_count, :is_published, NOW(), NOW())";
        
        $this->db->query($sql, $data);
        return $this->db->lastInsertId();
    }
    
    /**
     * Update review
     * 
     * @param int $id Review ID
     * @param array $data Review data
     * @return bool
     */
    public function update($id, $data) {
        $data['id'] = $id;
        $sql = "UPDATE {$this->table} 
                SET rating = :rating, comment = :comment, photos = :photos, photo_count = :photo_count, 
                    is_published = :is_published, updated_at = NOW() 
                WHERE id = :id";
        
        return $this->db->query($sql, $data);
    }
    
    /**
     * Delete review
     * 
     * @param int $id Review ID
     * @return bool
     */
    public function delete($id) {
        $sql = "DELETE FROM {$this->table} WHERE id = :id";
        return $this->db->query($sql, ['id' => $id]);
    }
    
    /**
     * Increment photo count
     * 
     * @param int $reviewId Review ID
     * @return bool
     */
    public function incrementPhotoCount($reviewId) {
        $sql = "UPDATE {$this->table} 
                SET photo_count = photo_count + 1 
                WHERE id = :review_id";
        
        return $this->db->query($sql, ['review_id' => $reviewId]);
    }
    
    /**
     * Decrement photo count
     * 
     * @param int $reviewId Review ID
     * @return bool
     */
    public function decrementPhotoCount($reviewId) {
        $sql = "UPDATE {$this->table} 
                SET photo_count = GREATEST(photo_count - 1, 0) 
                WHERE id = :review_id";
        
        return $this->db->query($sql, ['review_id' => $reviewId]);
    }
    
    /**
     * Update rating for entity
     * 
     * @param string $type Entity type
     * @param int $entityId Entity ID
     * @return bool
     */
    public function updateEntityRating($type, $entityId) {
        $tableMap = [
            'guide' => 'tour_guides',
            'destination' => 'destinations',
            'hotel' => 'hotels',
            'restaurant' => 'restaurants',
            'event' => 'events'
        ];
        
        $table = $tableMap[$type] ?? null;
        if (!$table) {
            return false;
        }
        
        $sql = "UPDATE {$table} t 
                SET rating_avg = (
                    SELECT COALESCE(AVG(r.rating), 0) 
                    FROM reviews r 
                    WHERE r.reviewable_type = :type 
                    AND r.reviewable_id = :entity_id 
                    AND r.is_published = 1
                ),
                total_reviews = (
                    SELECT COUNT(*) 
                    FROM reviews r 
                    WHERE r.reviewable_type = :type 
                    AND r.reviewable_id = :entity_id 
                    AND r.is_published = 1
                )
                WHERE t.id = :entity_id";
        
        return $this->db->query($sql, [
            'type' => $type,
            'entity_id' => $entityId
        ]);
    }
}
