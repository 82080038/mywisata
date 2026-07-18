<?php
/**
 * MyWisata Application - Quick Booking Template Model
 * 
 * @package MyWisata
 * @version 1.0.0
 * @since 2026-07-18
 */

class QuickBookingTemplate extends Model {
    
    protected $table = 'quick_booking_templates';
    
    /**
     * Get active templates
     */
    public function getActive() {
        $sql = "SELECT * FROM {$this->table} WHERE is_active = 1 ORDER BY name";
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
