<?php
/**
 * MyWisata Application - Eco Certified Destination Model
 * 
 * @package MyWisata
 * @version 1.0.0
 * @since 2026-07-18
 */

class EcoCertifiedDestination extends Model {
    
    protected $table = 'eco_certified_destinations';
    
    /**
     * Get active destinations
     */
    public function getActive($page = 1, $limit = 12) {
        $offset = ($page - 1) * $limit;
        $sql = "SELECT * FROM {$this->table} WHERE is_active = 1 ORDER BY eco_score DESC LIMIT {$limit} OFFSET {$offset}";
        return $this->query($sql);
    }
    
    /**
     * Find by ID
     */
    public function findById($id) {
        $sql = "SELECT * FROM {$this->table} WHERE id = :id LIMIT 1";
        return $this->query($sql, ['id' => $id])[0] ?? null;
    }
}
