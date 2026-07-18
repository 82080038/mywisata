<?php

/**
 * MyWisata Application - Message Model
 *
 * Handles conversations and messages between users.
 */
class Message extends Model
{
    protected $table = 'conversations';

    /**
     * Get or create a conversation between two users
     */
    public function getOrCreateConversation($user1Id, $user2Id, $contextType = 'general', $contextId = null, $subject = null)
    {
        // Check existing conversation
        $sql = "SELECT * FROM {$this->table} 
                WHERE ((user1_id = :u1a AND user2_id = :u2a) OR (user1_id = :u1b AND user2_id = :u2b))
                AND context_type = :ctx_type";
        $params = [
            'u1a' => $user1Id, 'u2a' => $user2Id,
            'u1b' => $user2Id, 'u2b' => $user1Id,
            'ctx_type' => $contextType,
        ];
        if ($contextId) {
            $sql .= " AND context_id = :ctx_id";
            $params['ctx_id'] = $contextId;
        }
        $existing = $this->db->query($sql, $params)->fetch();
        if ($existing) {
            return $existing['id'];
        }

        // Create new
        $this->db->query(
            "INSERT INTO {$this->table} (user1_id, user2_id, subject, context_type, context_id, created_at, last_message_at) 
             VALUES (:u1, :u2, :subject, :ctx_type, :ctx_id, NOW(), NOW())",
            [
                'u1' => $user1Id,
                'u2' => $user2Id,
                'subject' => $subject,
                'ctx_type' => $contextType,
                'ctx_id' => $contextId,
            ]
        );
        return $this->db->lastInsertId();
    }

    /**
     * Send a message
     */
    public function sendMessage($conversationId, $senderId, $message)
    {
        $this->db->query(
            "INSERT INTO conversation_messages (conversation_id, sender_id, message, created_at) 
             VALUES (:cid, :sid, :msg, NOW())",
            ['cid' => $conversationId, 'sid' => $senderId, 'msg' => $message]
        );

        $this->db->query(
            "UPDATE {$this->table} SET last_message_at = NOW() WHERE id = :id",
            ['id' => $conversationId]
        );

        return $this->db->lastInsertId();
    }

    /**
     * Get messages in a conversation
     */
    public function getMessages($conversationId, $limit = 100)
    {
        $sql = "SELECT cm.*, u.name as sender_name, u.role as sender_role
                FROM conversation_messages cm
                INNER JOIN users u ON cm.sender_id = u.id
                WHERE cm.conversation_id = :cid
                ORDER BY cm.created_at ASC
                LIMIT {$limit}";
        return $this->db->query($sql, ['cid' => $conversationId])->fetchAll();
    }

    /**
     * Get user's inbox (all conversations)
     */
    public function getInbox($userId)
    {
        $sql = "SELECT c.*,
                       u1.name as user1_name, u1.role as user1_role,
                       u2.name as user2_name, u2.role as user2_role,
                       last_msg.message as last_message,
                       last_msg.sender_id as last_sender_id,
                       last_msg.created_at as last_message_time,
                       unread.unread_count
                FROM {$this->table} c
                INNER JOIN users u1 ON c.user1_id = u1.id
                INNER JOIN users u2 ON c.user2_id = u2.id
                LEFT JOIN (
                    SELECT conversation_id, message, sender_id, created_at
                    FROM conversation_messages cm1
                    WHERE id = (SELECT MAX(id) FROM conversation_messages cm2 WHERE cm2.conversation_id = cm1.conversation_id)
                ) last_msg ON c.id = last_msg.conversation_id
                LEFT JOIN (
                    SELECT conversation_id, COUNT(*) as unread_count
                    FROM conversation_messages
                    WHERE is_read = 0 AND sender_id != :uid1
                    GROUP BY conversation_id
                ) unread ON c.id = unread.conversation_id
                WHERE c.user1_id = :uid2 OR c.user2_id = :uid3
                ORDER BY c.last_message_at DESC";
        return $this->db->query($sql, ['uid1' => $userId, 'uid2' => $userId, 'uid3' => $userId])->fetchAll();
    }

    /**
     * Get a single conversation with both users' info
     */
    public function getConversation($conversationId, $userId)
    {
        $sql = "SELECT c.*,
                       u1.name as user1_name, u1.role as user1_role, u1.avatar as user1_avatar,
                       u2.name as user2_name, u2.role as user2_role, u2.avatar as user2_avatar
                FROM {$this->table} c
                INNER JOIN users u1 ON c.user1_id = u1.id
                INNER JOIN users u2 ON c.user2_id = u2.id
                WHERE c.id = :cid AND (c.user1_id = :uid1 OR c.user2_id = :uid2)";
        return $this->db->query($sql, ['cid' => $conversationId, 'uid1' => $userId, 'uid2' => $userId])->fetch();
    }

    /**
     * Mark messages as read
     */
    public function markAsRead($conversationId, $userId)
    {
        $this->db->query(
            "UPDATE conversation_messages SET is_read = 1 
             WHERE conversation_id = :cid AND sender_id != :uid AND is_read = 0",
            ['cid' => $conversationId, 'uid' => $userId]
        );
    }

    /**
     * Get unread message count for a user
     */
    public function getUnreadCount($userId)
    {
        $sql = "SELECT COUNT(*) as count 
                FROM conversation_messages cm
                INNER JOIN conversations c ON cm.conversation_id = c.id
                WHERE cm.is_read = 0 AND cm.sender_id != :uid
                AND (c.user1_id = :uid1 OR c.user2_id = :uid2)";
        $result = $this->db->query($sql, ['uid' => $userId, 'uid1' => $userId, 'uid2' => $userId])->fetch();
        return $result['count'] ?? 0;
    }

    /**
     * Get new messages since a specific message ID (for polling)
     */
    public function getNewMessages($conversationId, $lastMessageId)
    {
        $sql = "SELECT cm.*, u.name as sender_name, u.role as sender_role
                FROM conversation_messages cm
                INNER JOIN users u ON cm.sender_id = u.id
                WHERE cm.conversation_id = :cid AND cm.id > :last_id
                ORDER BY cm.created_at ASC";
        return $this->db->query($sql, ['cid' => $conversationId, 'last_id' => $lastMessageId])->fetchAll();
    }
}
