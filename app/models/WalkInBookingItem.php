<?php
/**
 * MyWisata Application - Walk-in Booking Item Model
 * 
 * @package MyWisata
 * @version 1.0.0
 * @since 2026-07-18
 */

class WalkInBookingItem extends Model {
    
    protected $table = 'walk_in_booking_items';
    
    /**
     * Create item
     */
    public function create($data) {
        $sql = "INSERT INTO {$this->table} (walk_in_booking_id, item_type, item_id, item_name, quantity, unit_price, total_price, currency, start_time, end_time, notes) 
                VALUES (:walk_in_booking_id, :item_type, :item_id, :item_name, :quantity, :unit_price, :total_price, :currency, :start_time, :end_time, :notes)";
        return $this->execute($sql, $data);
    }
}
