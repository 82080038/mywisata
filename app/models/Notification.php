<?php
/**
 * MyWisata Application - Notification Model
 * 
 * Handles notification related database operations.
 * 
 * @package MyWisata
 * @version 1.0.0
 * @since 2026-07-16
 */

class Notification extends Model {
    
    /**
     * Table name
     */
    protected $table = 'notifications';
    
    /**
     * Create a notification
     * 
     * @param array $data Notification data
     * @return int Notification ID
     */
    public function create($data) {
        $sql = "INSERT INTO {$this->table} 
                (user_id, type, title, message, link, is_read, created_at)
                VALUES 
                (:user_id, :type, :title, :message, :link, 0, NOW())";
        
        $this->db->query($sql, $data);
        return $this->db->lastInsertId();
    }
    
    /**
     * Get notification by ID
     * 
     * @param int $id Notification ID
     * @return array|false
     */
    public function findById($id) {
        $sql = "SELECT n.*, u.name as user_name 
                FROM {$this->table} n 
                LEFT JOIN users u ON n.user_id = u.id 
                WHERE n.id = :id";
        
        return $this->db->query($sql, ['id' => $id])->fetch();
    }
    
    /**
     * Get notifications by user
     * 
     * @param int $userId User ID
     * @param int $page Page number
     * @param int $limit Items per page
     * @return array
     */
    public function getByUser($userId, $page = 1, $limit = 20) {
        $offset = ($page - 1) * $limit;
        
        $sql = "SELECT n.* 
                FROM {$this->table} n 
                WHERE n.user_id = :user_id 
                ORDER BY n.is_read ASC, n.created_at DESC 
                LIMIT :limit OFFSET :offset";
        
        return $this->db->query($sql, [
            'user_id' => $userId,
            'limit' => $limit,
            'offset' => $offset
        ])->fetchAll();
    }
    
    /**
     * Count notifications by user
     * 
     * @param int $userId User ID
     * @return int
     */
    public function countByUser($userId) {
        $sql = "SELECT COUNT(*) as count
                FROM {$this->table} 
                WHERE user_id = :user_id";
        
        $result = $this->db->query($sql, ['user_id' => $userId])->fetch();
        
        return $result['count'];
    }
    
    /**
     * Get unread notification count
     * 
     * @param int $userId User ID
     * @return int
     */
    public function getUnreadCount($userId) {
        $sql = "SELECT COUNT(*) as count
                FROM {$this->table} 
                WHERE user_id = :user_id AND is_read = 0";
        
        $result = $this->db->query($sql, ['user_id' => $userId])->fetch();
        
        return $result['count'];
    }
    
    /**
     * Get new notifications since last notification ID
     * 
     * @param int $userId User ID
     * @param int $lastNotificationId Last notification ID
     * @return array
     */
    public function getNew($userId, $lastNotificationId) {
        $sql = "SELECT n.* 
                FROM {$this->table} n 
                WHERE n.user_id = :user_id 
                AND n.id > :last_notification_id
                ORDER BY n.created_at DESC";
        
        return $this->db->query($sql, [
            'user_id' => $userId,
            'last_notification_id' => $lastNotificationId
        ])->fetchAll();
    }
    
    /**
     * Mark notification as read
     * 
     * @param int $id Notification ID
     * @param int $userId User ID (for ownership check)
     * @return bool
     */
    public function markAsRead($id, $userId) {
        $sql = "UPDATE {$this->table} 
                SET is_read = 1, read_at = NOW() 
                WHERE id = :id AND user_id = :user_id";
        
        return $this->db->query($sql, [
            'id' => $id,
            'user_id' => $userId
        ]);
    }
    
    /**
     * Mark all notifications as read for a user
     * 
     * @param int $userId User ID
     * @return bool
     */
    public function markAllAsRead($userId) {
        $sql = "UPDATE {$this->table} 
                SET is_read = 1, read_at = NOW() 
                WHERE user_id = :user_id AND is_read = 0";
        
        return $this->db->query($sql, ['user_id' => $userId]);
    }
    
    /**
     * Delete a notification
     * 
     * @param int $id Notification ID
     * @param int $userId User ID (for ownership check)
     * @return bool
     */
    public function delete($id, $userId) {
        $sql = "DELETE FROM {$this->table} 
                WHERE id = :id AND user_id = :user_id";
        
        return $this->db->query($sql, [
            'id' => $id,
            'user_id' => $userId
        ]);
    }
    
    /**
     * Delete all read notifications for a user
     * 
     * @param int $userId User ID
     * @return bool
     */
    public function deleteAllRead($userId) {
        $sql = "DELETE FROM {$this->table} 
                WHERE user_id = :user_id AND is_read = 1";
        
        return $this->db->query($sql, ['user_id' => $userId]);
    }
    
    /**
     * Get notification settings for a user
     * 
     * @param int $userId User ID
     * @return array
     */
    public function getSettings($userId) {
        $sql = "SELECT * FROM notification_settings WHERE user_id = :user_id";
        $result = $this->db->query($sql, ['user_id' => $userId])->fetch();
        
        if (!$result) {
            // Return default settings
            return [
                'email_booking' => 1,
                'email_message' => 1,
                'email_review' => 0,
                'email_promo' => 0,
                'push_booking' => 1,
                'push_message' => 1,
                'push_review' => 1,
                'push_promo' => 0
            ];
        }
        
        return $result;
    }
    
    /**
     * Update notification settings for a user
     * 
     * @param int $userId User ID
     * @param array $settings Settings array
     * @return bool
     */
    public function updateSettings($userId, $settings) {
        $sql = "INSERT INTO notification_settings 
                (user_id, email_booking, email_message, email_review, email_promo, 
                 push_booking, push_message, push_review, push_promo, updated_at)
                VALUES 
                (:user_id, :email_booking, :email_message, :email_review, :email_promo,
                 :push_booking, :push_message, :push_review, :push_promo, NOW())
                ON DUPLICATE KEY UPDATE 
                email_booking = :email_booking,
                email_message = :email_message,
                email_review = :email_review,
                email_promo = :email_promo,
                push_booking = :push_booking,
                push_message = :push_message,
                push_review = :push_review,
                push_promo = :push_promo,
                updated_at = NOW()";
        
        $params = array_merge(['user_id' => $userId], $settings);
        return $this->db->query($sql, $params);
    }
    
    /**
     * Create notification helper method
     * 
     * @param int $userId User ID
     * @param string $type Notification type
     * @param string $title Title
     * @param string $message Message
     * @param string $link Optional link
     * @return int Notification ID
     */
    public function notify($userId, $type, $title, $message, $link = null) {
        return $this->create([
            'user_id' => $userId,
            'type' => $type,
            'title' => $title,
            'message' => $message,
            'link' => $link
        ]);
    }
    
    /**
     * Notify multiple users
     * 
     * @param array $userIds Array of user IDs
     * @param string $type Notification type
     * @param string $title Title
     * @param string $message Message
     * @param string $link Optional link
     * @return array Array of notification IDs
     */
    public function notifyMultiple($userIds, $type, $title, $message, $link = null) {
        $notificationIds = [];
        
        foreach ($userIds as $userId) {
            $notificationIds[] = $this->notify($userId, $type, $title, $message, $link);
        }
        
        return $notificationIds;
    }
}
