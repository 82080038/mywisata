<?php
/**
 * MyWisata Application - Halal Package Booking Model
 * 
 * @package MyWisata
 * @version 1.0.0
 * @since 2026-07-18
 */

class HalalPackageBooking extends Model {
    
    protected $table = 'halal_package_bookings';
    
    /**
     * Create booking
     */
    public function create($data) {
        $sql = "INSERT INTO {$this->table} (package_id, user_id, booking_date, travel_date, number_of_travelers, total_price, currency, special_requests, dietary_requirements, gender_preference, contact_person_name, contact_person_phone, contact_person_email, status, payment_status) 
                VALUES (:package_id, :user_id, :booking_date, :travel_date, :number_of_travelers, :total_price, :currency, :special_requests, :dietary_requirements, :gender_preference, :contact_person_name, :contact_person_phone, :contact_person_email, :status, :payment_status)";
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
