<?php
/**
 * MyWisata Application - Favorite Model
 * 
 * Handles user favorites with enhanced features (folders, notes, sharing).
 * 
 * @package MyWisata
 * @version 1.0.0
 * @since 2026-07-01
 */

class Favorite extends Model {
    
    /**
     * Table name
     */
    protected $table = 'user_favorites';
    
    /**
     * Add to favorites
     * 
     * @param int $userId User ID
     * @param string $itemType Item type (destination, hotel, restaurant, event, tour_guide)
     * @param int $itemId Item ID
     * @param string $folder Optional folder name
     * @param string $notes Optional notes
     * @return int Favorite ID
     */
    public function add($userId, $itemType, $itemId, $folder = null, $notes = null) {
        $sql = "INSERT INTO {$this->table} (user_id, item_type, item_id, folder, notes, created_at)
                VALUES (:user_id, :item_type, :item_id, :folder, :notes, NOW())";
        
        $this->db->query($sql, [
            'user_id' => $userId,
            'item_type' => $itemType,
            'item_id' => $itemId,
            'folder' => $folder,
            'notes' => $notes
        ]);
        
        return $this->db->lastInsertId();
    }
    
    /**
     * Remove from favorites
     * 
     * @param int $userId User ID
     * @param string $itemType Item type
     * @param int $itemId Item ID
     * @return bool
     */
    public function remove($userId, $itemType, $itemId) {
        $sql = "DELETE FROM {$this->table} 
                WHERE user_id = :user_id AND item_type = :item_type AND item_id = :item_id";
        
        return $this->db->query($sql, [
            'user_id' => $userId,
            'item_type' => $itemType,
            'item_id' => $itemId
        ]);
    }
    
    /**
     * Get user favorites
     * 
     * @param int $userId User ID
     * @param string $itemType Optional item type filter
     * @param string $folder Optional folder filter
     * @param int $page Page number
     * @param int $limit Items per page
     * @return array
     */
    public function getUserFavorites($userId, $itemType = null, $folder = null, $page = 1, $limit = 12) {
        $offset = ($page - 1) * $limit;
        $where = "f.user_id = :user_id";
        $params = ['user_id' => $userId];
        
        if ($itemType) {
            $where .= " AND f.item_type = :item_type";
            $params['item_type'] = $itemType;
        }
        
        if ($folder) {
            $where .= " AND f.folder = :folder";
            $params['folder'] = $folder;
        }
        
        $sql = "SELECT f.*, 
                CASE f.item_type
                    WHEN 'destination' THEN (SELECT name FROM destinations WHERE id = f.item_id)
                    WHEN 'hotel' THEN (SELECT name FROM hotels WHERE id = f.item_id)
                    WHEN 'restaurant' THEN (SELECT name FROM restaurants WHERE id = f.item_id)
                    WHEN 'event' THEN (SELECT title FROM events WHERE id = f.item_id)
                    WHEN 'tour_guide' THEN (SELECT name FROM tour_guides WHERE id = f.item_id)
                END as item_name,
                CASE f.item_type
                    WHEN 'destination' THEN (SELECT image FROM destinations WHERE id = f.item_id)
                    WHEN 'hotel' THEN (SELECT image FROM hotels WHERE id = f.item_id)
                    WHEN 'restaurant' THEN (SELECT image FROM restaurants WHERE id = f.item_id)
                    WHEN 'event' THEN (SELECT image FROM events WHERE id = f.item_id)
                    WHEN 'tour_guide' THEN (SELECT avatar FROM tour_guides WHERE id = f.item_id)
                END as item_image
                FROM {$this->table} f 
                WHERE {$where} 
                ORDER BY f.created_at DESC
                LIMIT :limit OFFSET :offset";
        
        $params['limit'] = $limit;
        $params['offset'] = $offset;
        
        return $this->db->query($sql, $params)->fetchAll();
    }
    
    /**
     * Count user favorites
     * 
     * @param int $userId User ID
     * @param string $itemType Optional item type filter
     * @param string $folder Optional folder filter
     * @return int
     */
    public function countUserFavorites($userId, $itemType = null, $folder = null) {
        $where = "user_id = :user_id";
        $params = ['user_id' => $userId];
        
        if ($itemType) {
            $where .= " AND item_type = :item_type";
            $params['item_type'] = $itemType;
        }
        
        if ($folder) {
            $where .= " AND folder = :folder";
            $params['folder'] = $folder;
        }
        
        $sql = "SELECT COUNT(*) as count FROM {$this->table} WHERE {$where}";
        $result = $this->db->query($sql, $params)->fetch();
        
        return $result['count'];
    }
    
    /**
     * Check if item is favorited
     * 
     * @param int $userId User ID
     * @param string $itemType Item type
     * @param int $itemId Item ID
     * @return bool
     */
    public function isFavorited($userId, $itemType, $itemId) {
        $sql = "SELECT COUNT(*) as count FROM {$this->table} 
                WHERE user_id = :user_id AND item_type = :item_type AND item_id = :item_id";
        
        $result = $this->db->query($sql, [
            'user_id' => $userId,
            'item_type' => $itemType,
            'item_id' => $itemId
        ])->fetch();
        
        return $result['count'] > 0;
    }
    
    /**
     * Create folder
     * 
     * @param int $userId User ID
     * @param string $name Folder name
     * @param string $description Optional description
     * @return int Folder ID
     */
    public function createFolder($userId, $name, $description = null) {
        $sql = "INSERT INTO favorite_folders (user_id, name, description, created_at)
                VALUES (:user_id, :name, :description, NOW())";
        
        $this->db->query($sql, [
            'user_id' => $userId,
            'name' => $name,
            'description' => $description
        ]);
        
        return $this->db->lastInsertId();
    }
    
    /**
     * Update folder
     * 
     * @param int $folderId Folder ID
     * @param int $userId User ID (for ownership check)
     * @param string $name Folder name
     * @param string $description Optional description
     * @return bool
     */
    public function updateFolder($folderId, $userId, $name, $description = null) {
        $sql = "UPDATE favorite_folders 
                SET name = :name, description = :description, updated_at = NOW() 
                WHERE id = :id AND user_id = :user_id";
        
        return $this->db->query($sql, [
            'id' => $folderId,
            'user_id' => $userId,
            'name' => $name,
            'description' => $description
        ]);
    }
    
    /**
     * Delete folder
     * 
     * @param int $folderId Folder ID
     * @param int $userId User ID (for ownership check)
     * @return bool
     */
    public function deleteFolder($folderId, $userId) {
        $sql = "DELETE FROM favorite_folders WHERE id = :id AND user_id = :user_id";
        return $this->db->query($sql, ['id' => $folderId, 'user_id' => $userId]);
    }
    
    /**
     * Get user folders
     * 
     * @param int $userId User ID
     * @return array
     */
    public function getUserFolders($userId) {
        $sql = "SELECT f.*, 
                (SELECT COUNT(*) FROM user_favorites WHERE folder = f.name AND user_id = :user_id) as item_count
                FROM favorite_folders f 
                WHERE f.user_id = :user_id 
                ORDER BY f.created_at DESC";
        
        return $this->db->query($sql, ['user_id' => $userId])->fetchAll();
    }
    
    /**
     * Move item to folder
     * 
     * @param int $userId User ID
     * @param string $itemType Item type
     * @param int $itemId Item ID
     * @param string $folder Folder name
     * @return bool
     */
    public function moveToFolder($userId, $itemType, $itemId, $folder) {
        $sql = "UPDATE {$this->table} 
                SET folder = :folder, updated_at = NOW() 
                WHERE user_id = :user_id AND item_type = :item_type AND item_id = :item_id";
        
        return $this->db->query($sql, [
            'user_id' => $userId,
            'item_type' => $itemType,
            'item_id' => $itemId,
            'folder' => $folder
        ]);
    }
    
    /**
     * Update notes
     * 
     * @param int $userId User ID
     * @param string $itemType Item type
     * @param int $itemId Item ID
     * @param string $notes Notes
     * @return bool
     */
    public function updateNotes($userId, $itemType, $itemId, $notes) {
        $sql = "UPDATE {$this->table} 
                SET notes = :notes, updated_at = NOW() 
                WHERE user_id = :user_id AND item_type = :item_type AND item_id = :item_id";
        
        return $this->db->query($sql, [
            'user_id' => $userId,
            'item_type' => $itemType,
            'item_id' => $itemId,
            'notes' => $notes
        ]);
    }
    
    /**
     * Generate share token
     * 
     * @param int $userId User ID
     * @param string $folder Optional folder
     * @return string Share token
     */
    public function generateShareToken($userId, $folder = null) {
        $token = bin2hex(random_bytes(32));
        $expiry = date('Y-m-d H:i:s', strtotime('+7 days'));
        
        $sql = "INSERT INTO favorite_shares (user_id, folder, share_token, expires_at, created_at)
                VALUES (:user_id, :folder, :share_token, :expires_at, NOW())
                ON DUPLICATE KEY UPDATE 
                share_token = :share_token, 
                expires_at = :expires_at, 
                created_at = NOW()";
        
        $this->db->query($sql, [
            'user_id' => $userId,
            'folder' => $folder,
            'share_token' => $token,
            'expires_at' => $expiry
        ]);
        
        return $token;
    }
    
    /**
     * Get shared favorites
     * 
     * @param string $shareToken Share token
     * @return array|false
     */
    public function getSharedFavorites($shareToken) {
        $sql = "SELECT s.*, u.name as owner_name,
                f.item_type, f.item_id, f.folder, f.notes,
                CASE f.item_type
                    WHEN 'destination' THEN (SELECT name FROM destinations WHERE id = f.item_id)
                    WHEN 'hotel' THEN (SELECT name FROM hotels WHERE id = f.item_id)
                    WHEN 'restaurant' THEN (SELECT name FROM restaurants WHERE id = f.item_id)
                    WHEN 'event' THEN (SELECT title FROM events WHERE id = f.item_id)
                    WHEN 'tour_guide' THEN (SELECT name FROM tour_guides WHERE id = f.item_id)
                END as item_name
                FROM favorite_shares s
                LEFT JOIN users u ON s.user_id = u.id
                LEFT JOIN user_favorites f ON s.user_id = f.user_id 
                    AND (s.folder IS NULL OR f.folder = s.folder)
                WHERE s.share_token = :share_token 
                AND s.expires_at > NOW()";
        
        return $this->db->query($sql, ['share_token' => $shareToken])->fetchAll();
    }
}
