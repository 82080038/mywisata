<?php
/**
 * MyWisata Application - Equipment Rental Booking Model
 * 
 * @package MyWisata
 * @version 1.0.0
 * @since 2026-07-18
 */

class EquipmentRentalBooking extends Model {
    
    protected $table = 'equipment_rental_bookings';
    
    /**
     * Create booking
     */
    public function create($data) {
        $sql = "INSERT INTO {$this->table} (equipment_id, user_id, booking_date, rental_start_date, rental_end_date, quantity, total_price, currency, size, status, payment_status) 
                VALUES (:equipment_id, :user_id, :booking_date, :rental_start_date, :rental_end_date, :quantity, :total_price, :currency, :size, :status, :payment_status)";
        return $this->execute($sql, $data);
    }
    
    /**
     * Find by ID
     */
    public function findById($id) {
        $sql = "SELECT * FROM {$this->table} WHERE id = :id LIMIT 1";
        return $this->query($sql, ['id' => $id])[0] ?? null;
    }
}
