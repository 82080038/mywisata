<?php
/**
 * MyWisata Application - Farm Activity Booking Model
 * 
 * @package MyWisata
 * @version 1.0.0
 * @since 2026-07-18
 */

class FarmActivityBooking extends Model {
    
    protected $table = 'farm_activity_bookings';
    
    /**
     * Create booking
     */
    public function create($data) {
        $sql = "INSERT INTO {$this->table} (farm_id, activity_id, package_id, user_id, booking_date, activity_date, activity_time, number_of_participants, total_price, currency, group_type, age_range, special_requirements, contact_person_name, contact_person_phone, contact_person_email, status, payment_status) 
                VALUES (:farm_id, :activity_id, :package_id, :user_id, :booking_date, :activity_date, :activity_time, :number_of_participants, :total_price, :currency, :group_type, :age_range, :special_requirements, :contact_person_name, :contact_person_phone, :contact_person_email, :status, :payment_status)";
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
