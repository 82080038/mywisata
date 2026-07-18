<?php
/**
 * MyWisata Application - Pilgrimage Booking Model
 * 
 * @package MyWisata
 * @version 1.0.0
 * @since 2026-07-18
 */

class PilgrimageBooking extends Model {
    
    protected $table = 'pilgrimage_bookings';
    
    /**
     * Create booking
     */
    public function create($data) {
        $sql = "INSERT INTO {$this->table} (package_id, user_id, booking_date, departure_date, return_date, number_of_pilgrims, total_price, currency, medical_requirements, dietary_requirements, room_preference, gender_group, group_leader_name, group_leader_phone, group_leader_email, emergency_contact_name, emergency_contact_phone, emergency_contact_relationship, status, payment_status) 
                VALUES (:package_id, :user_id, :booking_date, :departure_date, :return_date, :number_of_pilgrims, :total_price, :currency, :medical_requirements, :dietary_requirements, :room_preference, :gender_group, :group_leader_name, :group_leader_phone, :group_leader_email, :emergency_contact_name, :emergency_contact_phone, :emergency_contact_relationship, :status, :payment_status)";
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
