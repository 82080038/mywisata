<?php
/**
 * MyWisata Application - WhatsApp Booking Session Model
 * 
 * @package MyWisata
 * @version 1.0.0
 * @since 2026-07-18
 */

class WhatsAppBookingSession extends Model {
    
    protected $table = 'whatsapp_booking_sessions';
    
    /**
     * Create session
     */
    public function create($data) {
        $sql = "INSERT INTO {$this->table} (session_id, phone_number, session_state, booking_type, last_message_time, last_message_content) 
                VALUES (:session_id, :phone_number, :session_state, :booking_type, :last_message_time, :last_message_content)";
        return $this->execute($sql, $data);
    }
    
    /**
     * Get active by phone
     */
    public function getActiveByPhone($phoneNumber) {
        $sql = "SELECT * FROM {$this->table} WHERE phone_number = :phone_number AND session_state != 'completed' ORDER BY last_message_time DESC LIMIT 1";
        return $this->query($sql, ['phone_number' => $phoneNumber])[0] ?? null;
    }
    
    /**
     * Update last message
     */
    public function updateLastMessage($id, $content) {
        $sql = "UPDATE {$this->table} SET last_message_content = :content, last_message_time = NOW() WHERE id = :id";
        return $this->execute($sql, ['id' => $id, 'content' => $content]);
    }
    
    /**
     * Update state
     */
    public function updateState($id, $state) {
        $sql = "UPDATE {$this->table} SET session_state = :state WHERE id = :id";
        return $this->execute($sql, ['id' => $id, 'state' => $state]);
    }
    
    /**
     * Update booking type
     */
    public function updateBookingType($id, $bookingType) {
        $sql = "UPDATE {$this->table} SET booking_type = :booking_type WHERE id = :id";
        return $this->execute($sql, ['id' => $id, 'booking_type' => $bookingType]);
    }
    
    /**
     * Update travel date
     */
    public function updateTravelDate($id, $date) {
        $sql = "UPDATE {$this->table} SET travel_date = :date WHERE id = :id";
        return $this->execute($sql, ['id' => $id, 'date' => $date]);
    }
    
    /**
     * Update number of people
     */
    public function updateNumberOfPeople($id, $number) {
        $sql = "UPDATE {$this->table} SET number_of_people = :number WHERE id = :id";
        return $this->execute($sql, ['id' => $id, 'number' => $number]);
    }
    
    /**
     * Update booking ID
     */
    public function updateBookingId($id, $bookingId) {
        $sql = "UPDATE {$this->table} SET booking_id = :booking_id WHERE id = :id";
        return $this->execute($sql, ['id' => $id, 'booking_id' => $bookingId]);
    }
}
