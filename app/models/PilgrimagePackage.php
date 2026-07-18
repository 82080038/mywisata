<?php
/**
 * MyWisata Application - Pilgrimage Package Model
 * 
 * @package MyWisata
 * @version 1.0.0
 * @since 2026-07-18
 */

class PilgrimagePackage extends Model {
    
    protected $table = 'pilgrimage_packages';
    
    /**
     * Get active packages
     */
    public function getActive($page = 1, $limit = 12, $destinationType = 'all') {
        $offset = ($page - 1) * $limit;
        $sql = "SELECT * FROM {$this->table} WHERE is_active = 1";
        
        if ($destinationType !== 'all') {
            $sql .= " AND destination_type = :destination_type";
        }
        
        $sql .= " ORDER BY created_at DESC LIMIT {$limit} OFFSET {$offset}";
        
        $params = $destinationType !== 'all' ? ['destination_type' => $destinationType] : [];
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
    
    /**
     * Create package
     */
    public function create($data) {
        $sql = "INSERT INTO {$this->table} (name, slug, description, destination_type, duration_days, price_per_person, currency, includes, excludes, is_active, is_featured, image_url) 
                VALUES (:name, :slug, :description, :destination_type, :duration_days, :price_per_person, :currency, :includes, :excludes, :is_active, :is_featured, :image_url)";
        return $this->execute($sql, $data);
    }
}
