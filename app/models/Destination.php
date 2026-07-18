<?php

/**
 * MyWisata Application - Destination Model
 *
 * Handles destination related database operations.
 *
 * @version 1.0.0
 *
 * @since 2026-07-01
 */
class Destination extends Model
{
    /**
     * Table name
     */
    protected $table = 'destinations';

    /**
     * Get all destinations with filters
     *
     * @param array $filters Optional filters
     *
     * @return array
     */
    public function getAllWithFilters($filters = [])
    {
        $where = ['1=1'];
        $params = [];

        if (!empty($filters['category_id'])) {
            $where[] = "category_id = :category_id";
            $params['category_id'] = $filters['category_id'];
        }

        if (!empty($filters['city'])) {
            $where[] = "city LIKE :city";
            $params['city'] = "%{$filters['city']}%";
        }

        if (!empty($filters['is_active'])) {
            $where[] = "is_active = :is_active";
            $params['is_active'] = $filters['is_active'];
        }

        if (!empty($filters['search'])) {
            $where[] = "(d.name LIKE :search_name OR d.description LIKE :search_desc)";
            $params['search_name'] = "%{$filters['search']}%";
            $params['search_desc'] = "%{$filters['search']}%";
        }

        $whereClause = implode(' AND ', $where);

        $sql = "SELECT d.*, c.name as category_name,
                COALESCE(AVG(dr.rating), 0) as rating_avg,
                COUNT(dr.id) as review_count
                FROM {$this->table} d
                LEFT JOIN destination_categories c ON d.category_id = c.id
                LEFT JOIN reviews dr ON d.id = dr.reviewable_id AND dr.reviewable_type = 'destination'
                WHERE {$whereClause}
                GROUP BY d.id
                ORDER BY d.name";

        return $this->db->query($sql, $params)->fetchAll();
    }

    /**
     * Get destination by ID
     *
     * @param int $id Destination ID
     *
     * @return array|false
     */
    public function findById($id)
    {
        $sql = "SELECT d.*, c.name as category_name,
                COALESCE(AVG(dr.rating), 0) as rating_avg,
                COUNT(dr.id) as review_count
                FROM {$this->table} d
                LEFT JOIN destination_categories c ON d.category_id = c.id
                LEFT JOIN reviews dr ON d.id = dr.reviewable_id AND dr.reviewable_type = 'destination'
                WHERE d.id = :id
                GROUP BY d.id";

        return $this->db->query($sql, ['id' => $id])->fetch();
    }

    /**
     * Get destination images
     *
     * @param int $destinationId Destination ID
     *
     * @return array
     */
    public function getImages($destinationId)
    {
        $sql = "SELECT * FROM destination_images WHERE destination_id = :destination_id ORDER BY is_primary DESC";

        return $this->db->query($sql, ['destination_id' => $destinationId])->fetchAll();
    }

    /**
     * Get destination reviews
     *
     * @param int $destinationId Destination ID
     * @param int $limit Optional limit
     *
     * @return array
     */
    public function getReviews($destinationId, $limit = null)
    {
        $sql = "SELECT r.*, u.name as user_name, u.avatar as user_avatar
                FROM reviews r
                LEFT JOIN users u ON r.user_id = u.id
                WHERE r.reviewable_type = 'destination' AND r.reviewable_id = :destination_id AND r.is_published = 1
                ORDER BY r.created_at DESC";

        if ($limit) {
            $sql .= " LIMIT {$limit}";
        }

        return $this->db->query($sql, ['destination_id' => $destinationId])->fetchAll();
    }

    /**
     * Get nearby destinations
     *
     * @param float $latitude Latitude
     * @param float $longitude Longitude
     * @param float $radius Radius in km
     *
     * @return array
     */
    public function getNearby($latitude, $longitude, $radius = 10)
    {
        $sql = "SELECT d.*, c.name as category_name,
                (6371 * ACOS(COS(RADIANS(:lat1)) * COS(RADIANS(d.latitude)) 
                * COS(RADIANS(d.longitude) - RADIANS(:lng)) 
                + SIN(RADIANS(:lat2)) * SIN(RADIANS(d.latitude)))) AS distance
                FROM {$this->table} d 
                LEFT JOIN destination_categories c ON d.category_id = c.id 
                WHERE d.is_active = 1
                HAVING distance < :radius
                ORDER BY distance ASC";

        return $this->db->query($sql, [
            'lat1' => $latitude,
            'lat2' => $latitude,
            'lng' => $longitude,
            'radius' => $radius,
        ])->fetchAll();
    }

    /**
     * Get featured destinations
     *
     * @param int $limit Optional limit
     *
     * @return array
     */
    public function getFeatured($limit = 6)
    {
        $sql = "SELECT d.*, c.name as category_name,
                (SELECT AVG(rating) FROM reviews WHERE reviewable_type = 'destination' AND reviewable_id = d.id) as rating_avg
                FROM {$this->table} d 
                LEFT JOIN destination_categories c ON d.category_id = c.id 
                WHERE d.is_active = 1 AND d.is_featured = 1
                ORDER BY d.rating_avg DESC
                LIMIT :limit";

        return $this->db->query($sql, ['limit' => $limit])->fetchAll();
    }

    /**
     * Get popular destinations
     *
     * @param int $limit Optional limit
     *
     * @return array
     */
    public function getPopular($limit = 6)
    {
        $sql = "SELECT d.*, c.name as category_name,
                COALESCE(AVG(dr.rating), 0) as rating_avg,
                COUNT(dr.id) as review_count
                FROM {$this->table} d
                LEFT JOIN destination_categories c ON d.category_id = c.id
                LEFT JOIN reviews dr ON d.id = dr.reviewable_id AND dr.reviewable_type = 'destination'
                WHERE d.is_active = 1
                GROUP BY d.id
                ORDER BY review_count DESC, rating_avg DESC
                LIMIT :limit";

        return $this->db->query($sql, ['limit' => $limit])->fetchAll();
    }

    /**
     * Add review
     *
     * @param array $data Review data
     *
     * @return bool
     */
    public function addReview($data)
    {
        $sql = "INSERT INTO reviews
                (user_id, reviewable_type, reviewable_id, rating, comment, is_published, created_at, updated_at)
                VALUES
                (:user_id, 'destination', :destination_id, :rating, :comment, 1, NOW(), NOW())";

        $reviewData = [
            'user_id' => $data['user_id'],
            'destination_id' => $data['destination_id'],
            'rating' => $data['rating'],
            'comment' => $data['comment'],
        ];

        return $this->db->query($sql, $reviewData);
    }

    /**
     * Update rating
     *
     * @param int $destinationId Destination ID
     *
     * @return bool
     */
    public function updateRating($destinationId)
    {
        $sql = "UPDATE {$this->table} 
                SET rating_avg = (
                    SELECT COALESCE(AVG(rating), 0) 
                    FROM reviews 
                    WHERE reviewable_type = 'destination' AND reviewable_id = :dest_id
                )
                WHERE id = :dest_id_main";

        return $this->db->query($sql, ['dest_id' => $destinationId, 'dest_id_main' => $destinationId]);
    }
}
