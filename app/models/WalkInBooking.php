<?php
/**
 * MyWisata Application - Walk-in Booking Model
 * 
 * @package MyWisata
 * @version 1.0.0
 * @since 2026-07-18
 */

class WalkInBooking extends Model {
    
    protected $table = 'walk_in_bookings';
    
    /**
     * Create booking
     */
    public function create($data) {
        $sql = "INSERT INTO {$this->table} (booking_code, booking_type, customer_name, customer_phone, customer_email, number_of_people, booking_date, booking_time, duration_hours, total_price, currency, payment_method, payment_status, payment_amount, special_requests, notes, staff_id, processing_device, processing_location, status) 
                VALUES (:booking_code, :booking_type, :customer_name, :customer_phone, :customer_email, :number_of_people, :booking_date, :booking_time, :duration_hours, :total_price, :currency, :payment_method, :payment_status, :payment_amount, :special_requests, :notes, :staff_id, :processing_device, :processing_location, :status)";
        return $this->execute($sql, $data);
    }
    
    /**
     * Get list
     */
    public function getList($page = 1, $limit = 20, $status = 'all', $date = null) {
        $offset = ($page - 1) * $limit;
        $sql = "SELECT * FROM {$this->table} WHERE 1=1";
        $params = [];
        
        if ($status !== 'all') {
            $sql .= " AND status = :status";
            $params['status'] = $status;
        }
        
        if ($date) {
            $sql .= " AND booking_date = :date";
            $params['date'] = $date;
        }
        
        $sql .= " ORDER BY booking_date DESC, booking_time DESC LIMIT {$limit} OFFSET {$offset}";
        return $this->query($sql, $params);
    }
    
    /**
     * Update status
     */
    public function updateStatus($id, $status) {
        $sql = "UPDATE {$this->table} SET status = :status WHERE id = :id";
        return $this->execute($sql, ['id' => $id, 'status' => $status]);
    }
    
    /**
     * Find by ID
     */
    public function findById($id) {
        $sql = "SELECT * FROM {$this->table} WHERE id = :id LIMIT 1";
        return $this->query($sql, ['id' => $id])[0] ?? null;
    }
}
