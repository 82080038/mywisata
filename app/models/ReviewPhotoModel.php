<?php
/**
 * MyWisata Application - Review Photo Model
 * 
 * Handles review photo database operations.
 * 
 * @package MyWisata
 * @version 1.0.0
 * @since 2026-07-15
 */

class ReviewPhotoModel extends Model {
    
    /**
     * Table name
     */
    protected $table = 'review_photos';
    
    /**
     * Find photo by ID
     * 
     * @param int $id Photo ID
     * @return array|false
     */
    public function findById($id) {
        $sql = "SELECT * FROM {$this->table} WHERE id = :id";
        return $this->db->query($sql, ['id' => $id])->fetch();
    }
    
    /**
     * Get photos by review ID
     * 
     * @param int $reviewId Review ID
     * @return array
     */
    public function getByReviewId($reviewId) {
        $sql = "SELECT * FROM {$this->table} 
                WHERE review_id = :review_id 
                ORDER BY is_primary DESC, sort_order ASC, created_at ASC";
        
        return $this->db->query($sql, ['review_id' => $reviewId])->fetchAll();
    }
    
    /**
     * Create review photo
     * 
     * @param array $data Photo data
     * @return int Photo ID
     */
    public function create($data) {
        $sql = "INSERT INTO {$this->table} 
                (review_id, file_path, thumbnail_path, caption, sort_order, is_primary, created_at)
                VALUES (:review_id, :file_path, :thumbnail_path, :caption, :sort_order, :is_primary, NOW())";
        
        $this->db->query($sql, $data);
        return $this->db->lastInsertId();
    }
    
    /**
     * Update photo
     * 
     * @param int $id Photo ID
     * @param array $data Photo data
     * @return bool
     */
    public function update($id, $data) {
        $data['id'] = $id;
        $sql = "UPDATE {$this->table} 
                SET caption = :caption, sort_order = :sort_order 
                WHERE id = :id";
        
        return $this->db->query($sql, $data);
    }
    
    /**
     * Delete photo
     * 
     * @param int $id Photo ID
     * @return bool
     */
    public function delete($id) {
        $sql = "DELETE FROM {$this->table} WHERE id = :id";
        return $this->db->query($sql, ['id' => $id]);
    }
    
    /**
     * Set photo as primary
     * 
     * @param int $id Photo ID
     * @return bool
     */
    public function setPrimary($id) {
        $sql = "UPDATE {$this->table} 
                SET is_primary = 1 
                WHERE id = :id";
        
        return $this->db->query($sql, ['id' => $id]);
    }
    
    /**
     * Remove primary flag from all photos in review
     * 
     * @param int $reviewId Review ID
     * @return bool
     */
    public function removePrimaryFromReview($reviewId) {
        $sql = "UPDATE {$this->table} 
                SET is_primary = 0 
                WHERE review_id = :review_id";
        
        return $this->db->query($sql, ['review_id' => $reviewId]);
    }
    
    /**
     * Update sort order
     * 
     * @param int $id Photo ID
     * @param int $sortOrder Sort order
     * @return bool
     */
    public function updateSortOrder($id, $sortOrder) {
        $sql = "UPDATE {$this->table} 
                SET sort_order = :sort_order 
                WHERE id = :id";
        
        return $this->db->query($sql, [
            'id' => $id,
            'sort_order' => $sortOrder
        ]);
    }
}
