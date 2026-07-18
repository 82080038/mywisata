<?php
/**
 * MyWisata Application - Farm Product Model
 * 
 * @package MyWisata
 * @version 1.0.0
 * @since 2026-07-18
 */

class FarmProduct extends Model {
    
    protected $table = 'farm_products';
    
    /**
     * Get available products
     */
    public function getAvailable($page = 1, $limit = 12, $farmId = null, $productType = 'all') {
        $offset = ($page - 1) * $limit;
        $sql = "SELECT * FROM {$this->table} WHERE is_active = 1 AND available_quantity > 0";
        $params = [];
        
        if ($farmId) {
            $sql .= " AND farm_id = :farm_id";
            $params['farm_id'] = $farmId;
        }
        
        if ($productType !== 'all') {
            $sql .= " AND product_type = :product_type";
            $params['product_type'] = $productType;
        }
        
        $sql .= " ORDER BY name LIMIT {$limit} OFFSET {$offset}";
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
