<?php
/**
 * MyWisata Application - Popular Route Model
 * 
 * @package MyWisata
 * @version 1.0.0
 * @since 2026-07-18
 */

class PopularRoute extends Model {
    
    protected $table = 'popular_routes';
    
    /**
     * Get active routes
     */
    public function getActive($page = 1, $limit = 10, $routeType = 'all') {
        $offset = ($page - 1) * $limit;
        $sql = "SELECT * FROM {$this->table} WHERE is_active = 1";
        $params = [];
        
        if ($routeType !== 'all') {
            $sql .= " AND route_type = :route_type";
            $params['route_type'] = $routeType;
        }
        
        $sql .= " ORDER BY popularity_score DESC LIMIT {$limit} OFFSET {$offset}";
        return $this->query($sql, $params);
    }
    
    /**
     * Find by ID
     */
    public function findById($id) {
        $sql = "SELECT * FROM {$this->table} WHERE id = :id LIMIT 1";
        return $this->query($sql, ['id' => $id])[0] ?? null;
    }
}
