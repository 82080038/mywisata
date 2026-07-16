<?php
/**
 * MyWisata Application - Booking Model
 * 
 * Handles booking related database operations.
 * 
 * @package MyWisata
 * @version 1.0.0
 * @since 2026-07-01
 */

class Booking extends Model {
    
    /**
     * Table name
     */
    protected $table = 'bookings';
    
    /**
     * Create booking
     * 
     * @param array $data Booking data
     * @return int Booking ID
     */
    public function create($data) {
        $sql = "INSERT INTO {$this->table} 
                (booking_code, user_id, guide_id, booking_date, booking_time, 
                 duration_hours, participants, special_requests, status, total_amount, created_at)
                VALUES 
                (:booking_code, :user_id, :guide_id, :booking_date, :booking_time,
                 :duration_hours, :participants, :special_requests, 'pending', :total_amount, NOW())";
        
        $this->db->query($sql, $data);
        return $this->db->lastInsertId();
    }
    
    /**
     * Get booking by ID
     * 
     * @param int $id Booking ID
     * @return array|false
     */
    public function findById($id) {
        $sql = "SELECT b.*, u.name as user_name, u.email as user_email, u.phone as user_phone,
                tg.name as guide_name, tg.phone as guide_phone, tg.hourly_rate, tg.daily_rate
                FROM {$this->table} b 
                LEFT JOIN users u ON b.user_id = u.id 
                LEFT JOIN tour_guides tg ON b.guide_id = tg.id 
                WHERE b.id = :id";
        
        return $this->db->query($sql, ['id' => $id])->fetch();
    }
    
    /**
     * Get bookings by user ID
     * 
     * @param int $userId User ID
     * @param string $status Optional status filter
     * @param int $page Page number
     * @param int $limit Items per page
     * @return array
     */
    public function getByUserId($userId, $status = null, $page = 1, $limit = 10) {
        $offset = ($page - 1) * $limit;
        $where = "b.user_id = :user_id";
        $params = ['user_id' => $userId];
        
        if ($status) {
            $where .= " AND b.status = :status";
            $params['status'] = $status;
        }
        
        $sql = "SELECT b.*, tg.name as guide_name, tg.avatar, tg.rating_avg
                FROM {$this->table} b 
                LEFT JOIN tour_guides tg ON b.guide_id = tg.id 
                WHERE {$where} 
                ORDER BY b.created_at DESC
                LIMIT :limit OFFSET :offset";
        
        $params['limit'] = $limit;
        $params['offset'] = $offset;
        
        return $this->db->query($sql, $params)->fetchAll();
    }
    
    /**
     * Count bookings by user ID
     * 
     * @param int $userId User ID
     * @param string $status Optional status filter
     * @return int
     */
    public function countByUserId($userId, $status = null) {
        $where = "user_id = :user_id";
        $params = ['user_id' => $userId];
        
        if ($status) {
            $where .= " AND status = :status";
            $params['status'] = $status;
        }
        
        $sql = "SELECT COUNT(*) as count FROM {$this->table} WHERE {$where}";
        $result = $this->db->query($sql, $params)->fetch();
        
        return $result['count'];
    }
    
    /**
     * Update booking status
     * 
     * @param int $id Booking ID
     * @param string $status New status
     * @return bool
     */
    public function updateStatus($id, $status) {
        $sql = "UPDATE {$this->table} SET status = :status, updated_at = NOW() WHERE id = :id";
        return $this->db->query($sql, ['id' => $id, 'status' => $status]);
    }
    
    /**
     * Cancel booking
     * 
     * @param int $id Booking ID
     * @param string $reason Cancellation reason
     * @return bool
     */
    public function cancel($id, $reason = null) {
        $sql = "UPDATE {$this->table} SET status = 'cancelled', cancellation_reason = :reason, updated_at = NOW() WHERE id = :id";
        return $this->db->query($sql, ['id' => $id, 'reason' => $reason]);
    }
    
    /**
     * Reschedule booking
     * 
     * @param int $id Booking ID
     * @param string $newDate New booking date
     * @param string $newTime New booking time
     * @return bool
     */
    public function reschedule($id, $newDate, $newTime) {
        $sql = "UPDATE {$this->table} 
                SET booking_date = :new_date, 
                    booking_time = :new_time, 
                    updated_at = NOW() 
                WHERE id = :id";
        
        return $this->db->query($sql, [
            'id' => $id,
            'new_date' => $newDate,
            'new_time' => $newTime
        ]);
    }
    
    /**
     * Update booking with review ID
     * 
     * @param int $id Booking ID
     * @param int $reviewId Review ID
     * @return bool
     */
    public function updateReviewId($id, $reviewId) {
        $sql = "UPDATE {$this->table} SET review_id = :review_id, updated_at = NOW() WHERE id = :id";
        return $this->db->query($sql, ['id' => $id, 'review_id' => $reviewId]);
    }
    
    /**
     * Get user booking statistics
     * 
     * @param int $userId User ID
     * @return array
     */
    public function getUserStatistics($userId) {
        $sql = "SELECT 
                COUNT(*) as total_bookings,
                SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending_bookings,
                SUM(CASE WHEN status = 'confirmed' THEN 1 ELSE 0 END) as confirmed_bookings,
                SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) as completed_bookings,
                SUM(CASE WHEN status = 'cancelled' THEN 1 ELSE 0 END) as cancelled_bookings,
                SUM(total_amount) as total_spent
                FROM {$this->table} 
                WHERE user_id = :user_id";
        
        return $this->db->query($sql, ['user_id' => $userId])->fetch();
    }
    
    /**
     * Get bookings by guide ID
     * 
     * @param int $guideId Guide ID
     * @param string $status Optional status filter
     * @param int $page Page number
     * @param int $limit Items per page
     * @return array
     */
    public function getByGuideId($guideId, $status = null, $page = 1, $limit = 10) {
        $offset = ($page - 1) * $limit;
        $where = "b.guide_id = :guide_id";
        $params = ['guide_id' => $guideId];
        
        if ($status) {
            $where .= " AND b.status = :status";
            $params['status'] = $status;
        }
        
        $sql = "SELECT b.*, u.name as user_name, u.email as user_email, u.phone as user_phone
                FROM {$this->table} b 
                LEFT JOIN users u ON b.user_id = u.id 
                WHERE {$where} 
                ORDER BY b.booking_date ASC, b.booking_time ASC
                LIMIT :limit OFFSET :offset";
        
        $params['limit'] = $limit;
        $params['offset'] = $offset;
        
        return $this->db->query($sql, $params)->fetchAll();
    }
    
    /**
     * Count bookings by guide ID
     * 
     * @param int $guideId Guide ID
     * @param string $status Optional status filter
     * @return int
     */
    public function countByGuideId($guideId, $status = null) {
        $where = "guide_id = :guide_id";
        $params = ['guide_id' => $guideId];
        
        if ($status) {
            $where .= " AND status = :status";
            $params['status'] = $status;
        }
        
        $sql = "SELECT COUNT(*) as count FROM {$this->table} WHERE {$where}";
        $result = $this->db->query($sql, $params)->fetch();
        
        return $result['count'];
    }
}
