<?php
/**
 * MyWisata Application - Farm Activity Model
 * 
 * @package MyWisata
 * @version 1.0.0
 * @since 2026-07-18
 */

class FarmActivity extends Model {
    
    protected $table = 'farm_activities';
    
    /**
     * Get by farm ID
     */
    public function getByFarmId($farmId) {
        $sql = "SELECT * FROM {$this->table} WHERE farm_id = :farm_id AND is_active = 1 ORDER BY name";
        return $this->query($sql, ['farm_id' => $farmId]);
    }
    
    /**
     * Find by ID
     */
    public function findById($id) {
        $sql = "SELECT * FROM {$this->table} WHERE id = :id LIMIT 1";
        return $this->query($sql, ['id' => $id])[0] ?? null;
    }
}
