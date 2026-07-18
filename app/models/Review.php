<?php

/**
 * MyWisata Application - Review Model
 *
 * Unified polymorphic review model for all entity types.
 * Replaces individual review tables (destination_reviews, hotel_reviews, etc.)
 *
 * @version 1.0.0
 * @since 2026-07-19
 */
class Review extends Model
{
    /**
     * Table name
     */
    protected $table = 'reviews';

    /**
     * Get reviews for a specific entity
     *
     * @param string $type Entity type (destination, hotel, restaurant, event, guide)
     * @param int $id Entity ID
     * @param int|null $limit Optional limit
     * @return array
     */
    public function getForEntity($type, $id, $limit = null)
    {
        $sql = "SELECT r.*, u.name as user_name, u.avatar as user_avatar
                FROM {$this->table} r
                LEFT JOIN users u ON r.user_id = u.id
                WHERE r.reviewable_type = :type AND r.reviewable_id = :id AND r.is_published = 1
                ORDER BY r.created_at DESC";

        $params = ['type' => $type, 'id' => $id];

        if ($limit) {
            $sql .= " LIMIT {$limit}";
        }

        return $this->db->query($sql, $params)->fetchAll();
    }

    /**
     * Add a review
     *
     * @param array $data Review data (user_id, reviewable_type, reviewable_id, rating, comment)
     * @return bool
     */
    public function add($data)
    {
        $sql = "INSERT INTO {$this->table}
                (user_id, reviewable_type, reviewable_id, rating, comment, is_published, created_at, updated_at)
                VALUES
                (:user_id, :reviewable_type, :reviewable_id, :rating, :comment, 1, NOW(), NOW())";

        return $this->db->query($sql, $data);
    }

    /**
     * Get average rating for an entity
     *
     * @param string $type Entity type
     * @param int $id Entity ID
     * @return array ['avg' => float, 'count' => int]
     */
    public function getRating($type, $id)
    {
        $sql = "SELECT COALESCE(AVG(rating), 0) as avg, COUNT(*) as count
                FROM {$this->table}
                WHERE reviewable_type = :type AND reviewable_id = :id AND is_published = 1";

        $result = $this->db->query($sql, ['type' => $type, 'id' => $id])->fetch();
        return [
            'avg' => round($result['avg'], 1),
            'count' => (int)$result['count']
        ];
    }

    /**
     * Check if user has already reviewed an entity
     *
     * @param int $userId
     * @param string $type
     * @param int $id
     * @return bool
     */
    public function hasReviewed($userId, $type, $id)
    {
        $sql = "SELECT COUNT(*) as c FROM {$this->table}
                WHERE user_id = :user_id AND reviewable_type = :type AND reviewable_id = :id";

        return $this->db->query($sql, ['user_id' => $userId, 'type' => $type, 'id' => $id])->fetch()['c'] > 0;
    }

    /**
     * Get reviews by user
     *
     * @param int $userId
     * @param int|null $limit
     * @return array
     */
    public function getByUser($userId, $limit = null)
    {
        $sql = "SELECT r.*, u.name as user_name
                FROM {$this->table} r
                LEFT JOIN users u ON r.user_id = u.id
                WHERE r.user_id = :user_id
                ORDER BY r.created_at DESC";

        if ($limit) {
            $sql .= " LIMIT {$limit}";
        }

        return $this->db->query($sql, ['user_id' => $userId])->fetchAll();
    }
}
