<?php

/**
 * MyWisata Application - Event Model
 *
 * Handles event related database operations.
 *
 * @version 1.0.0
 *
 * @since 2026-07-01
 */
class Event extends Model
{
    /**
     * Table name
     */
    protected $table = 'events';

    /**
     * Get all events with filters
     *
     * @param array $filters Optional filters
     *
     * @return array
     */
    public function getAllWithFilters($filters = [])
    {
        $where = ['1=1'];
        $params = [];

        if (!empty($filters['city'])) {
            $where[] = "(e.location_name LIKE :city OR e.address LIKE :city)";
            $params['city'] = "%{$filters['city']}%";
        }

        if (!empty($filters['is_approved'])) {
            $where[] = "is_active = :is_active";
            $params['is_active'] = $filters['is_approved'];
        }

        if (!empty($filters['is_active'])) {
            $where[] = "is_active = :is_active2";
            $params['is_active2'] = $filters['is_active'];
        }

        if (!empty($filters['search'])) {
            $where[] = "(e.title LIKE :search_name OR e.description LIKE :search_desc)";
            $params['search_name'] = "%{$filters['search']}%";
            $params['search_desc'] = "%{$filters['search']}%";
        }

        if (!empty($filters['upcoming'])) {
            $where[] = "start_date >= CURDATE()";
        }

        if (!empty($filters['registration_type'])) {
            $where[] = "e.registration_type = :reg_type";
            $params['reg_type'] = $filters['registration_type'];
        }

        $whereClause = implode(' AND ', $where);

        $sql = "SELECT e.*, ec.name as category_name, ec.slug as category_slug, ec.icon as category_icon,
                COALESCE(AVG(evtrev.rating), 0) as rating_avg,
                COUNT(evtrev.id) as review_count
                FROM {$this->table} e
                LEFT JOIN event_categories ec ON e.event_category_id = ec.id
                LEFT JOIN reviews evtrev ON e.id = evtrev.reviewable_id AND evtrev.reviewable_type = 'event'
                WHERE {$whereClause}
                GROUP BY e.id
                ORDER BY e.start_date ASC";

        return $this->db->query($sql, $params)->fetchAll();
    }

    /**
     * Get event by ID
     *
     * @param int $id Event ID
     *
     * @return array|false
     */
    public function findById($id)
    {
        $sql = "SELECT e.*, ec.name as category_name, ec.slug as category_slug, ec.icon as category_icon,
                COALESCE(AVG(evtrev.rating), 0) as rating_avg,
                COUNT(evtrev.id) as review_count
                FROM {$this->table} e
                LEFT JOIN event_categories ec ON e.event_category_id = ec.id
                LEFT JOIN reviews evtrev ON e.id = evtrev.reviewable_id AND evtrev.reviewable_type = 'event'
                WHERE e.id = :id
                GROUP BY e.id";

        return $this->db->query($sql, ['id' => $id])->fetch();
    }

    /**
     * Get upcoming events
     *
     * @param int $limit Optional limit
     *
     * @return array
     */
    public function getUpcoming($limit = 6)
    {
        $sql = "SELECT e.*, ec.name as category_name, ec.slug as category_slug, ec.icon as category_icon,
                COALESCE(AVG(evtrev.rating), 0) as rating_avg
                FROM {$this->table} e
                LEFT JOIN event_categories ec ON e.event_category_id = ec.id
                LEFT JOIN reviews evtrev ON e.id = evtrev.reviewable_id AND evtrev.reviewable_type = 'event'
                WHERE e.is_active = 1 AND e.start_date >= CURDATE()
                GROUP BY e.id
                ORDER BY e.start_date ASC
                LIMIT :limit";

        return $this->db->query($sql, ['limit' => $limit])->fetchAll();
    }

    /**
     * Get event reviews
     *
     * @param int $eventId Event ID
     * @param int $limit Optional limit
     *
     * @return array
     */
    public function getReviews($eventId, $limit = null)
    {
        $sql = "SELECT r.*, u.name as user_name, u.avatar as user_avatar
                FROM reviews r
                LEFT JOIN users u ON r.user_id = u.id
                WHERE r.reviewable_type = 'event' AND r.reviewable_id = :event_id AND r.is_published = 1
                ORDER BY r.created_at DESC";

        if ($limit) {
            $sql .= " LIMIT {$limit}";
        }

        return $this->db->query($sql, ['event_id' => $eventId])->fetchAll();
    }

    /**
     * Get events for a given month/year for calendar display
     *
     * @param int $month
     * @param int $year
     * @param string|null $city
     * @return array
     */
    public function getEventsForCalendar($month, $year, $city = null)
    {
        $params = [
            'start_month' => "{$year}-{$month}-01",
            'end_month' => date('Y-m-t', strtotime("{$year}-{$month}-01")),
        ];

        $sql = "SELECT e.id, e.title, e.start_date, e.end_date, e.location_name, e.main_image,
                       e.category, e.registration_type, e.price, e.event_status,
                       ec.name as category_name, ec.icon as category_icon
                FROM {$this->table} e
                LEFT JOIN event_categories ec ON e.event_category_id = ec.id
                WHERE e.is_active = 1
                  AND e.start_date >= :start_month
                  AND e.start_date <= :end_month";

        if ($city) {
            $sql .= " AND (e.location_name LIKE :city OR e.address LIKE :city2)";
            $params['city'] = "%{$city}%";
            $params['city2'] = "%{$city}%";
        }

        $sql .= " ORDER BY e.start_date ASC";

        return $this->db->query($sql, $params)->fetchAll();
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
                (:user_id, 'event', :event_id, :rating, :comment, 1, NOW(), NOW())";

        $reviewData = [
            'user_id' => $data['user_id'],
            'event_id' => $data['event_id'],
            'rating' => $data['rating'],
            'comment' => $data['comment'],
        ];

        return $this->db->query($sql, $reviewData);
    }
}
