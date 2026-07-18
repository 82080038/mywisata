<?php
/**
 * MyWisata Application - Walk-in Analytics Model
 * 
 * @package MyWisata
 * @version 1.0.0
 * @since 2026-07-18
 */

class WalkInAnalytics extends Model {
    
    protected $table = 'walk_in_analytics';
    
    /**
     * Get by date and type
     */
    public function getByDateAndType($date, $bookingType) {
        $sql = "SELECT * FROM {$this->table} WHERE date = :date AND booking_type = :booking_type LIMIT 1";
        return $this->query($sql, ['date' => $date, 'booking_type' => $bookingType])[0] ?? null;
    }
    
    /**
     * Create analytics entry
     */
    public function create($data) {
        $sql = "INSERT INTO {$this->table} (date, booking_type, total_bookings, total_revenue, currency, average_booking_value) 
                VALUES (:date, :booking_type, :total_bookings, :total_revenue, :currency, :average_booking_value)";
        return $this->execute($sql, $data);
    }
    
    /**
     * Increment analytics
     */
    public function increment($id, $revenue) {
        $sql = "UPDATE {$this->table} 
                SET total_bookings = total_bookings + 1,
                    total_revenue = total_revenue + :revenue,
                    average_booking_value = (total_revenue + :revenue) / (total_bookings + 1)
                WHERE id = :id";
        return $this->execute($sql, ['id' => $id, 'revenue' => $revenue]);
    }
}
