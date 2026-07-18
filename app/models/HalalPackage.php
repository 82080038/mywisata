<?php
/**
 * MyWisata Application - Halal Package Model
 * 
 * @package MyWisata
 * @version 1.0.0
 * @since 2026-07-18
 */

class HalalPackage extends Model {
    
    protected $table = 'halal_packages';
    
    /**
     * Get active packages
     */
    public function getActive($page = 1, $limit = 12) {
        $offset = ($page - 1) * $limit;
        $sql = "SELECT * FROM {$this->table} WHERE is_active = 1 ORDER BY created_at DESC LIMIT {$limit} OFFSET {$offset}";
        return $this->query($sql);
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
        $sql = "INSERT INTO {$this->table} (name, slug, description, duration_days, duration_nights, price_per_person, currency, includes, excludes, highlights, is_active, is_featured, image_url) 
                VALUES (:name, :slug, :description, :duration_days, :duration_nights, :price_per_person, :currency, :includes, :excludes, :highlights, :is_active, :is_featured, :image_url)";
        return $this->execute($sql, $data);
    }
}
