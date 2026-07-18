<?php

/**
 * MyWisata Application - Favorite Model
 *
 * Handles user favorites.
 *
 * @version 1.0.0
 *
 * @since 2026-07-01
 */
class Favorite extends Model
{
    /**
     * Table name
     */
    protected $table = 'user_favorites';

    /**
     * Add to favorites
     *
     * @param int $userId User ID
     * @param string $itemType Item type (destination, hotel, restaurant, event)
     * @param int $itemId Item ID
     *
     * @return bool
     */
    public function add($userId, $itemType, $itemId)
    {
        $sql = "INSERT INTO {$this->table} (user_id, item_type, item_id, created_at)
                VALUES (:user_id, :item_type, :item_id, NOW())
                ON DUPLICATE KEY UPDATE created_at = NOW()";

        return $this->db->query($sql, [
            'user_id' => $userId,
            'item_type' => $itemType,
            'item_id' => $itemId,
        ]);
    }

    /**
     * Remove from favorites
     *
     * @param int $userId User ID
     * @param string $itemType Item type
     * @param int $itemId Item ID
     *
     * @return bool
     */
    public function remove($userId, $itemType, $itemId)
    {
        $sql = "DELETE FROM {$this->table} 
                WHERE user_id = :user_id AND item_type = :item_type AND item_id = :item_id";

        return $this->db->query($sql, [
            'user_id' => $userId,
            'item_type' => $itemType,
            'item_id' => $itemId,
        ]);
    }

    /**
     * Get user favorites
     *
     * @param int $userId User ID
     * @param string $itemType Optional item type filter
     *
     * @return array
     */
    public function getUserFavorites($userId, $itemType = null)
    {
        $where = "f.user_id = :user_id";
        $params = ['user_id' => $userId];

        if ($itemType) {
            $where .= " AND f.item_type = :item_type";
            $params['item_type'] = $itemType;
        }

        $sql = "SELECT f.*,
                    COALESCE(d.name, h.name, r.name, e.title, tg.name, 'Item #' || f.item_id) as item_name,
                    COALESCE(d.main_image, h.main_image, r.main_image, e.main_image, NULL) as item_image,
                    COALESCE(d.city, h.city, r.city, NULL) as item_city
                FROM {$this->table} f
                LEFT JOIN destinations d ON f.item_type = 'destination' AND f.item_id = d.id
                LEFT JOIN hotels h ON f.item_type = 'hotel' AND f.item_id = h.id
                LEFT JOIN restaurants r ON f.item_type = 'restaurant' AND f.item_id = r.id
                LEFT JOIN events e ON f.item_type = 'event' AND f.item_id = e.id
                LEFT JOIN tour_guides tg ON f.item_type = 'tour_guide' AND f.item_id = tg.id
                WHERE {$where}
                ORDER BY f.created_at DESC";

        return $this->db->query($sql, $params)->fetchAll();
    }

    /**
     * Check if item is favorited
     *
     * @param int $userId User ID
     * @param string $itemType Item type
     * @param int $itemId Item ID
     *
     * @return bool
     */
    public function isFavorited($userId, $itemType, $itemId)
    {
        $sql = "SELECT COUNT(*) as count FROM {$this->table} 
                WHERE user_id = :user_id AND item_type = :item_type AND item_id = :item_id";

        $result = $this->db->query($sql, [
            'user_id' => $userId,
            'item_type' => $itemType,
            'item_id' => $itemId,
        ])->fetch();

        return $result['count'] > 0;
    }
}
