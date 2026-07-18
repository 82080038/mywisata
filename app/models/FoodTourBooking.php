<?php
/**
 * MyWisata Application - Food Tour Booking Model
 * 
 * @package MyWisata
 * @version 1.0.0
 * @since 2026-07-18
 */

class FoodTourBooking extends Model {
    
    protected $table = 'food_tour_bookings';
    
    /**
     * Create booking
     */
    public function create($data) {
        $sql = "INSERT INTO {$this->table} (food_tour_id, user_id, booking_date, tour_date, tour_time, number_of_participants, total_price, currency, dietary_restrictions, contact_person_name, contact_person_phone, contact_person_email, status, payment_status) 
                VALUES (:food_tour_id, :user_id, :booking_date, :tour_date, :tour_time, :number_of_participants, :total_price, :currency, :dietary_restrictions, :contact_person_name, :contact_person_phone, :contact_person_email, :status, :payment_status)";
        return $this->execute($sql, $data);
    }
    
    /**
     * Find by ID
     */
    public function findById($id) {
        $sql = "SELECT * FROM {$this->table} WHERE id = :id LIMIT 1";
        return $this->query($sql, ['id' => $id])[0] ?? null;
    }
    
    /**
     * Update status
     */
    public function updateStatus($id, $status) {
        $sql = "UPDATE {$this->table} SET status = :status WHERE id = :id";
        return $this->execute($sql, ['id' => $id, 'status' => $status]);
    }
}
