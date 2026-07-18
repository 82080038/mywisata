<?php
/**
 * MyWisata Application - WhatsApp Booking Analytics Model
 * 
 * @package MyWisata
 * @version 1.0.0
 * @since 2026-07-18
 */

class WhatsAppBookingAnalytics extends Model {
    
    protected $table = 'whatsapp_booking_analytics';
    
    /**
     * Get list
     */
    public function getList($page = 1, $limit = 20) {
        $offset = ($page - 1) * $limit;
        $sql = "SELECT * FROM {$this->table} ORDER BY date DESC LIMIT {$limit} OFFSET {$offset}";
        return $this->query($sql);
    }
}
