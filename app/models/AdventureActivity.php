<?php
/**
 * MyWisata Application - Adventure Activity Model
 * 
 * @package MyWisata
 * @version 1.0.0
 * @since 2026-07-18
 */

class AdventureActivity extends Model {
    
    protected $table = 'adventure_activities';
    
    /**
     * Get active activities
     */
    public function getActive($page = 1, $limit = 12, $activityType = 'all', $difficultyLevel = 'all') {
        $offset = ($page - 1) * $limit;
        $sql = "SELECT * FROM {$this->table} WHERE is_active = 1";
        $params = [];
        
        if ($activityType !== 'all') {
            $sql .= " AND activity_type = :activity_type";
            $params['activity_type'] = $activityType;
        }
        
        if ($difficultyLevel !== 'all') {
            $sql .= " AND difficulty_level = :difficulty_level";
            $params['difficulty_level'] = $difficultyLevel;
        }
        
        $sql .= " ORDER BY created_at DESC LIMIT {$limit} OFFSET {$offset}";
        return $this->query($sql, $params);
    }
    
    /**
     * Find by slug
     */
    public function findBySlug($slug) {
        $sql = "SELECT * FROM {$this->table} WHERE slug = :slug AND is_active = 1 LIMIT 1";
        return $this->query($sql, ['slug' => $slug])[0] ?? null;
    }
    
    /**
     * Find by ID
     */
    public function findById($id) {
        $sql = "SELECT * FROM {$this->table} WHERE id = :id LIMIT 1";
        return $this->query($sql, ['id' => $id])[0] ?? null;
    }
}
