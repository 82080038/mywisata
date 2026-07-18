<?php
/**
 * MyWisata Application - Farm Model
 * 
 * @package MyWisata
 * @version 1.0.0
 * @since 2026-07-18
 */

class Farm extends Model {
    
    protected $table = 'farms';
    
    /**
     * Get active farms
     */
    public function getActive($page = 1, $limit = 12, $farmType = 'all') {
        $offset = ($page - 1) * $limit;
        $sql = "SELECT * FROM {$this->table} WHERE is_active = 1";
        $params = [];
        
        if ($farmType !== 'all') {
            $sql .= " AND farm_type = :farm_type";
            $params['farm_type'] = $farmType;
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
