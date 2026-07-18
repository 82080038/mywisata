<?php
/**
 * MyWisata Application - Equipment Rental Model
 * 
 * @package MyWisata
 * @version 1.0.0
 * @since 2026-07-18
 */

class EquipmentRental extends Model {
    
    protected $table = 'equipment_rentals';
    
    /**
     * Get available equipment
     */
    public function getAvailable($page = 1, $limit = 12, $equipmentType = 'all') {
        $offset = ($page - 1) * $limit;
        $sql = "SELECT * FROM {$this->table} WHERE is_active = 1 AND available_quantity > 0";
        $params = [];
        
        if ($equipmentType !== 'all') {
            $sql .= " AND equipment_type = :equipment_type";
            $params['equipment_type'] = $equipmentType;
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
