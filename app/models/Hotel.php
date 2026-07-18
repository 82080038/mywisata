<?php

/**
 * MyWisata Application - Hotel Model
 *
 * Handles hotel related database operations.
 *
 * @version 1.0.0
 *
 * @since 2026-07-01
 */
class Hotel extends Model
{
    /**
     * Table name
     */
    protected $table = 'hotels';

    /**
     * Get all hotels with filters
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
            $where[] = "h.city LIKE :city";
            $params['city'] = "%{$filters['city']}%";
        }

        if (!empty($filters['is_approved'])) {
            $where[] = "h.is_approved = :is_approved";
            $params['is_approved'] = $filters['is_approved'];
        }

        if (!empty($filters['search'])) {
            $where[] = "(h.name LIKE :search_name OR h.description LIKE :search_desc OR h.city LIKE :search_city)";
            $params['search_name'] = "%{$filters['search']}%";
            $params['search_desc'] = "%{$filters['search']}%";
            $params['search_city'] = "%{$filters['search']}%";
        }

        if (!empty($filters['type'])) {
            $where[] = "(h.type = :type OR h.hotel_type_id = (SELECT id FROM hotel_types WHERE slug = :type_slug))";
            $params['type'] = $filters['type'];
            $params['type_slug'] = $filters['type'];
        }

        if (isset($filters['min_price']) && $filters['min_price'] !== '') {
            $where[] = "h.price_range_min >= :min_price";
            $params['min_price'] = $filters['min_price'];
        }

        if (isset($filters['max_price']) && $filters['max_price'] !== '') {
            $where[] = "h.price_range_max <= :max_price";
            $params['max_price'] = $filters['max_price'];
        }

        if (!empty($filters['star_rating'])) {
            $where[] = "h.star_rating >= :star_rating";
            $params['star_rating'] = $filters['star_rating'];
        }

        if (!empty($filters['facility'])) {
            $where[] = "EXISTS (SELECT 1 FROM entity_facilities ef JOIN facilities f ON ef.facility_id = f.id WHERE ef.entity_type = 'hotel' AND ef.entity_id = h.id AND f.slug = :facility)";
            $params['facility'] = $filters['facility'];
        }

        $whereClause = implode(' AND ', $where);

        $orderBy = 'h.name';
        if (!empty($filters['sort'])) {
            switch ($filters['sort']) {
                case 'price_low':
                    $orderBy = 'h.price_range_min ASC';
                    break;
                case 'price_high':
                    $orderBy = 'h.price_range_max DESC';
                    break;
                case 'rating':
                    $orderBy = 'rating_avg DESC';
                    break;
                case 'newest':
                    $orderBy = 'h.created_at DESC';
                    break;
            }
        }

        $sql = "SELECT h.*, ht.name as type_name, ht.slug as type_slug, ht.icon as type_icon,
                COALESCE(AVG(hotrev.rating), 0) as rating_avg,
                COUNT(hotrev.id) as review_count
                FROM {$this->table} h
                LEFT JOIN hotel_types ht ON h.hotel_type_id = ht.id
                LEFT JOIN reviews hotrev ON h.id = hotrev.reviewable_id AND hotrev.reviewable_type = 'hotel'
                WHERE {$whereClause}
                GROUP BY h.id
                ORDER BY {$orderBy}";

        return $this->db->query($sql, $params)->fetchAll();
    }

    /**
     * Get hotel by ID
     *
     * @param int $id Hotel ID
     *
     * @return array|false
     */
    public function findById($id)
    {
        $sql = "SELECT h.*, ht.name as type_name, ht.slug as type_slug, ht.icon as type_icon,
                COALESCE(AVG(hotrev.rating), 0) as rating_avg,
                COUNT(hotrev.id) as review_count
                FROM {$this->table} h
                LEFT JOIN hotel_types ht ON h.hotel_type_id = ht.id
                LEFT JOIN reviews hotrev ON h.id = hotrev.reviewable_id AND hotrev.reviewable_type = 'hotel'
                WHERE h.id = :id
                GROUP BY h.id";

        return $this->db->query($sql, ['id' => $id])->fetch();
    }

    /**
     * Get hotel rooms
     *
     * @param int $hotelId Hotel ID
     *
     * @return array
     */
    public function getRooms($hotelId)
    {
        $sql = "SELECT * FROM hotel_rooms WHERE hotel_id = :hotel_id AND is_active = 1";

        return $this->db->query($sql, ['hotel_id' => $hotelId])->fetchAll();
    }

    /**
     * Get hotel gallery images
     *
     * @param int $hotelId Hotel ID
     *
     * @return array
     */
    public function getImages($hotelId)
    {
        $sql = "SELECT * FROM hotel_images WHERE hotel_id = :hotel_id ORDER BY is_primary DESC, sort_order ASC";
        return $this->db->query($sql, ['hotel_id' => $hotelId])->fetchAll();
    }

    /**
     * Get hotel reviews
     *
     * @param int $hotelId Hotel ID
     * @param int $limit Optional limit
     *
     * @return array
     */
    public function getReviews($hotelId, $limit = null)
    {
        $sql = "SELECT r.*, u.name as user_name, u.avatar as user_avatar
                FROM reviews r
                LEFT JOIN users u ON r.user_id = u.id
                WHERE r.reviewable_type = 'hotel' AND r.reviewable_id = :hotel_id AND r.is_published = 1
                ORDER BY r.created_at DESC";

        if ($limit) {
            $sql .= " LIMIT {$limit}";
        }

        return $this->db->query($sql, ['hotel_id' => $hotelId])->fetchAll();
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
                (:user_id, 'hotel', :hotel_id, :rating, :comment, 1, NOW(), NOW())";

        $reviewData = [
            'user_id' => $data['user_id'],
            'hotel_id' => $data['hotel_id'],
            'rating' => $data['rating'],
            'comment' => $data['comment'],
        ];

        return $this->db->query($sql, $reviewData);
    }

    /**
     * Get all accommodation types with counts
     *
     * @return array
     */
    public function getAccommodationTypes()
    {
        $sql = "SELECT ht.slug as type, ht.name, ht.icon, COUNT(h.id) as count 
                FROM hotel_types ht 
                LEFT JOIN {$this->table} h ON h.hotel_type_id = ht.id AND h.is_approved = 1 AND h.is_active = 1
                WHERE ht.is_active = 1
                GROUP BY ht.id
                HAVING count > 0
                ORDER BY count DESC";
        return $this->db->query($sql)->fetchAll();
    }

    /**
     * Get all unique facilities across all hotels
     *
     * @return array
     */
    public function getAllFacilities()
    {
        $sql = "SELECT DISTINCT f.slug, f.name, f.icon
                FROM facilities f
                INNER JOIN entity_facilities ef ON f.id = ef.facility_id
                WHERE ef.entity_type = 'hotel' AND f.is_active = 1
                ORDER BY f.name";
        return $this->db->query($sql)->fetchAll();
    }

    /**
     * Parse facilities JSON to array
     *
     * @param string|null $facilitiesJson
     * @return array
     */
    public static function parseFacilities($facilitiesJson)
    {
        if (empty($facilitiesJson)) {
            return [];
        }
        $facilities = json_decode($facilitiesJson, true);
        return is_array($facilities) ? $facilities : [];
    }

    /**
     * Get facility label in Indonesian
     *
     * @param string $key
     * @return string
     */
    public static function facilityLabel($key)
    {
        $labels = [
            'wifi' => 'WiFi',
            'pool' => 'Kolam Renang',
            'private_pool' => 'Kolam Renang Pribadi',
            'restaurant' => 'Restoran',
            'parking' => 'Parkir',
            'ac' => 'AC',
            'spa' => 'Spa',
            'gym' => 'Fitness Center',
            'beach_access' => 'Akses Pantai',
            'beach_view' => 'Pemandangan Pantai',
            'seaview' => 'Pemandangan Laut',
            'mountain_view' => 'Pemandangan Gunung',
            'lake_view' => 'Pemandangan Danau',
            'river_view' => 'Pemandangan Sungai',
            'rice_field_view' => 'Pemandangan Sawah',
            'forest_view' => 'Pemandangan Hutan',
            'hill_view' => 'Pemandangan Bukit',
            'sunset_view' => 'Pemandangan Sunset',
            'city_view' => 'Pemandangan Kota',
            'breakfast' => 'Sarapan',
            'hot_water' => 'Air Panas',
            'kitchen' => 'Dapur',
            'shared_kitchen' => 'Dapur Bersama',
            'garden' => 'Taman',
            'lounge' => 'Ruang Santai',
            'laundry' => 'Laundry',
            'bike_rental' => 'Sewa Sepeda',
            'bonfire' => 'Api Unggun',
            'bbq' => 'BBQ',
            'bar' => 'Bar',
            'elevator' => 'Lift',
            'concierge' => 'Concierge',
            'diving_center' => 'Pusat Selam',
            'surf_access' => 'Akses Surfing',
            'snorkeling' => 'Snorkeling',
            'cultural_tour' => 'Tur Budaya',
            'traditional_food' => 'Makanan Tradisional',
            'traditional_architecture' => 'Arsitektur Tradisional',
        ];

        return $labels[$key] ?? ucfirst(str_replace('_', ' ', $key));
    }

    /**
     * Get accommodation type label in Indonesian
     *
     * @param string $type
     * @return string
     */
    public static function typeLabel($type)
    {
        $labels = [
            'hotel' => 'Hotel',
            'resort' => 'Resort',
            'homestay' => 'Homestay',
            'villa' => 'Villa',
            'guesthouse' => 'Guest House',
            'hostel' => 'Hostel',
            'apartment' => 'Apartemen',
            'bungalow' => 'Bungalow',
            'cottage' => 'Cottage',
            'glamping' => 'Glamping',
            'cabin' => 'Cabin',
            'lodging' => 'Penginapan',
            'inn' => 'Inn / Wisma',
            'camping' => 'Camping Ground',
        ];

        return $labels[$type] ?? ucfirst($type);
    }

    /**
     * Get accommodation type icon
     *
     * @param string $type
     * @return string
     */
    public static function typeIcon($type)
    {
        $icons = [
            'hotel' => 'fa-hotel',
            'resort' => 'fa-umbrella-beach',
            'homestay' => 'fa-house',
            'villa' => 'fa-house-chimney',
            'guesthouse' => 'fa-door-open',
            'hostel' => 'fa-bed',
            'apartment' => 'fa-building',
            'bungalow' => 'fa-house-circle',
            'cottage' => 'fa-tree',
            'glamping' => 'fa-campground',
            'cabin' => 'fa-house-chimney-window',
            'lodging' => 'fa-bed',
            'inn' => 'fa-mug-saucer',
            'camping' => 'fa-tent',
        ];

        return $icons[$type] ?? 'fa-hotel';
    }
}
