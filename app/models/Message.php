<?php
/**
 * MyWisata Application - Message Model
 * 
 * Handles messaging related database operations.
 * 
 * @package MyWisata
 * @version 1.0.0
 * @since 2026-07-16
 */

class Message extends Model {
    
    /**
     * Table name
     */
    protected $table = 'messages';
    
    /**
     * Send a message
     * 
     * @param int $senderId Sender user ID
     * @param int $receiverId Receiver user ID
     * @param string $message Message content
     * @return int Message ID
     */
    public function send($senderId, $receiverId, $message) {
        $sql = "INSERT INTO {$this->table} 
                (sender_id, receiver_id, message, is_read, created_at)
                VALUES 
                (:sender_id, :receiver_id, :message, 0, NOW())";
        
        $this->db->query($sql, [
            'sender_id' => $senderId,
            'receiver_id' => $receiverId,
            'message' => $message
        ]);
        
        return $this->db->lastInsertId();
    }
    
    /**
     * Get message by ID
     * 
     * @param int $id Message ID
     * @return array|false
     */
    public function findById($id) {
        $sql = "SELECT m.*, 
                s.name as sender_name, s.email as sender_email,
                r.name as receiver_name, r.email as receiver_email
                FROM {$this->table} m 
                LEFT JOIN users s ON m.sender_id = s.id 
                LEFT JOIN users r ON m.receiver_id = r.id 
                WHERE m.id = :id";
        
        return $this->db->query($sql, ['id' => $id])->fetch();
    }
    
    /**
     * Get conversation between two users
     * 
     * @param int $userId Current user ID
     * @param int $otherUserId Other user ID
     * @return array
     */
    public function getConversation($userId, $otherUserId) {
        $sql = "SELECT m.*, 
                s.name as sender_name, s.email as sender_email,
                r.name as receiver_name, r.email as receiver_email
                FROM {$this->table} m 
                LEFT JOIN users s ON m.sender_id = s.id 
                LEFT JOIN users r ON m.receiver_id = r.id 
                WHERE (m.sender_id = :user_id AND m.receiver_id = :other_user_id)
                OR (m.sender_id = :other_user_id AND m.receiver_id = :user_id)
                ORDER BY m.created_at ASC";
        
        return $this->db->query($sql, [
            'user_id' => $userId,
            'other_user_id' => $otherUserId
        ])->fetchAll();
    }
    
    /**
     * Get conversations for a user
     * 
     * @param int $userId User ID
     * @return array
     */
    public function getConversations($userId) {
        $sql = "SELECT DISTINCT 
                CASE 
                    WHEN m.sender_id = :user_id THEN m.receiver_id 
                    ELSE m.sender_id 
                END as other_user_id,
                u.name as other_user_name,
                u.email as other_user_email,
                u.avatar as other_user_avatar,
                (SELECT message FROM {$this->table} 
                 WHERE (sender_id = :user_id AND receiver_id = other_user_id)
                 OR (sender_id = other_user_id AND receiver_id = :user_id)
                 ORDER BY created_at DESC LIMIT 1) as last_message,
                (SELECT created_at FROM {$this->table} 
                 WHERE (sender_id = :user_id AND receiver_id = other_user_id)
                 OR (sender_id = other_user_id AND receiver_id = :user_id)
                 ORDER BY created_at DESC LIMIT 1) as last_message_time,
                (SELECT COUNT(*) FROM {$this->table} 
                 WHERE receiver_id = :user_id AND sender_id = other_user_id AND is_read = 0) as unread_count
                FROM {$this->table} m
                LEFT JOIN users u ON (
                    CASE 
                        WHEN m.sender_id = :user_id THEN m.receiver_id 
                        ELSE m.sender_id 
                    END = u.id
                )
                WHERE m.sender_id = :user_id OR m.receiver_id = :user_id
                GROUP BY other_user_id, u.name, u.email, u.avatar
                ORDER BY last_message_time DESC";
        
        return $this->db->query($sql, ['user_id' => $userId])->fetchAll();
    }
    
    /**
     * Get new messages since last message ID
     * 
     * @param int $userId Current user ID
     * @param int $otherUserId Other user ID
     * @param int $lastMessageId Last message ID
     * @return array
     */
    public function getNewMessages($userId, $otherUserId, $lastMessageId) {
        $sql = "SELECT m.*, 
                s.name as sender_name, s.email as sender_email
                FROM {$this->table} m 
                LEFT JOIN users s ON m.sender_id = s.id 
                WHERE ((m.sender_id = :user_id AND m.receiver_id = :other_user_id)
                OR (m.sender_id = :other_user_id AND m.receiver_id = :user_id))
                AND m.id > :last_message_id
                ORDER BY m.created_at ASC";
        
        return $this->db->query($sql, [
            'user_id' => $userId,
            'other_user_id' => $otherUserId,
            'last_message_id' => $lastMessageId
        ])->fetchAll();
    }
    
    /**
     * Mark messages as read
     * 
     * @param int $userId Current user ID
     * @param int $otherUserId Other user ID
     * @return bool
     */
    public function markAsRead($userId, $otherUserId) {
        $sql = "UPDATE {$this->table} 
                SET is_read = 1, read_at = NOW() 
                WHERE receiver_id = :user_id AND sender_id = :other_user_id AND is_read = 0";
        
        return $this->db->query($sql, [
            'user_id' => $userId,
            'other_user_id' => $otherUserId
        ]);
    }
    
    /**
     * Delete a message
     * 
     * @param int $id Message ID
     * @return bool
     */
    public function delete($id) {
        $sql = "DELETE FROM {$this->table} WHERE id = :id";
        return $this->db->query($sql, ['id' => $id]);
    }
    
    /**
     * Get unread message count for a user
     * 
     * @param int $userId User ID
     * @return int
     */
    public function getUnreadCount($userId) {
        $sql = "SELECT COUNT(*) as count
                FROM {$this->table} 
                WHERE receiver_id = :user_id AND is_read = 0";
        
        $result = $this->db->query($sql, ['user_id' => $userId])->fetch();
        
        return $result['count'];
    }
    
    /**
     * Get user info
     * 
     * @param int $userId User ID
     * @return array|false
     */
    public function getUserInfo($userId) {
        $sql = "SELECT id, name, email, avatar, role FROM users WHERE id = :id";
        return $this->db->query($sql, ['id' => $userId])->fetch();
    }
    
    /**
     * Block a user
     * 
     * @param int $userId User ID who is blocking
     * @param int $blockedUserId User ID being blocked
     * @return bool
     */
    public function blockUser($userId, $blockedUserId) {
        $sql = "INSERT INTO message_blocks (user_id, blocked_user_id, created_at)
                VALUES (:user_id, :blocked_user_id, NOW())
                ON DUPLICATE KEY UPDATE created_at = NOW()";
        
        return $this->db->query($sql, [
            'user_id' => $userId,
            'blocked_user_id' => $blockedUserId
        ]);
    }
    
    /**
     * Unblock a user
     * 
     * @param int $userId User ID who is unblocking
     * @param int $blockedUserId User ID being unblocked
     * @return bool
     */
    public function unblockUser($userId, $blockedUserId) {
        $sql = "DELETE FROM message_blocks 
                WHERE user_id = :user_id AND blocked_user_id = :blocked_user_id";
        
        return $this->db->query($sql, [
            'user_id' => $userId,
            'blocked_user_id' => $blockedUserId
        ]);
    }
    
    /**
     * Check if user is blocked by another user
     * 
     * @param int $userId User ID who might be blocking
     * @param int $blockedUserId User ID who might be blocked
     * @return bool
     */
    public function isBlocked($userId, $blockedUserId) {
        $sql = "SELECT COUNT(*) as count
                FROM message_blocks 
                WHERE user_id = :user_id AND blocked_user_id = :blocked_user_id";
        
        $result = $this->db->query($sql, [
            'user_id' => $userId,
            'blocked_user_id' => $blockedUserId
        ])->fetch();
        
        return $result['count'] > 0;
    }
    
    /**
     * Get blocked users for a user
     * 
     * @param int $userId User ID
     * @return array
     */
    public function getBlockedUsers($userId) {
        $sql = "SELECT mb.*, u.name as blocked_user_name, u.email as blocked_user_email
                FROM message_blocks mb
                LEFT JOIN users u ON mb.blocked_user_id = u.id
                WHERE mb.user_id = :user_id";
        
        return $this->db->query($sql, ['user_id' => $userId])->fetchAll();
    }
}
