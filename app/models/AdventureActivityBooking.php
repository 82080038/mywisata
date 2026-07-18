<?php
/**
 * MyWisata Application - Adventure Activity Booking Model
 * 
 * @package MyWisata
 * @version 1.0.0
 * @since 2026-07-18
 */

class AdventureActivityBooking extends Model {
    
    protected $table = 'adventure_activity_bookings';
    
    /**
     * Create booking
     */
    public function create($data) {
        $sql = "INSERT INTO {$this->table} (activity_id, user_id, booking_date, activity_date, activity_time, number_of_participants, total_price, currency, equipment_rental, equipment_rental_items, dietary_requirements, medical_conditions, emergency_contact_name, emergency_contact_phone, emergency_contact_relationship, special_requests, status, payment_status) 
                VALUES (:activity_id, :user_id, :booking_date, :activity_date, :activity_time, :number_of_participants, :total_price, :currency, :equipment_rental, :equipment_rental_items, :dietary_requirements, :medical_conditions, :emergency_contact_name, :emergency_contact_phone, :emergency_contact_relationship, :special_requests, :status, :payment_status)";
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
