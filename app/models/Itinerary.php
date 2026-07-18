<?php

/**
 * MyWisata Application - Itinerary Model
 *
 * Handles AI-powered itinerary builder data operations.
 *
 * @version 1.0.0
 * @since 2026-07-19
 */
class Itinerary extends Model
{
    protected $table = 'itineraries';

    /**
     * Get itinerary by ID with items
     *
     * @param int $id
     * @return array|false
     */
    public function findById($id)
    {
        $itinerary = $this->db->query(
            "SELECT i.*, u.name as user_name FROM {$this->table} i LEFT JOIN users u ON i.user_id = u.id WHERE i.id = :id",
            ['id' => $id]
        )->fetch();

        if (!$itinerary) {
            return false;
        }

        $itinerary['items'] = $this->getItems($id);
        $itinerary['items_by_day'] = $this->getItemsByDay($id);

        return $itinerary;
    }

    /**
     * Get itineraries for a user
     *
     * @param int $userId
     * @param string|null $status
     * @return array
     */
    public function getByUserId($userId, $status = null)
    {
        $sql = "SELECT * FROM {$this->table} WHERE user_id = :user_id";
        $params = ['user_id' => $userId];

        if ($status) {
            $sql .= " AND status = :status";
            $params['status'] = $status;
        }

        $sql .= " ORDER BY created_at DESC";

        return $this->db->query($sql, $params)->fetchAll();
    }

    /**
     * Get itinerary items
     *
     * @param int $itineraryId
     * @return array
     */
    public function getItems($itineraryId)
    {
        $sql = "SELECT * FROM itinerary_items WHERE itinerary_id = :id ORDER BY day_number, sequence";
        return $this->db->query($sql, ['id' => $itineraryId])->fetchAll();
    }

    /**
     * Get itinerary items grouped by day
     *
     * @param int $itineraryId
     * @return array
     */
    public function getItemsByDay($itineraryId)
    {
        $items = $this->getItems($itineraryId);
        $byDay = [];

        foreach ($items as $item) {
            $day = $item['day_number'];
            if (!isset($byDay[$day])) {
                $byDay[$day] = [];
            }
            $byDay[$day][] = $item;
        }

        return $byDay;
    }

    /**
     * Create a new itinerary
     *
     * @param array $data
     * @return int Itinerary ID
     */
    public function create($data)
    {
        $sql = "INSERT INTO {$this->table}
                (user_id, title, description, start_date, end_date, num_days, num_travelers, budget_min, budget_max, status, preferences)
                VALUES
                (:user_id, :title, :description, :start_date, :end_date, :num_days, :num_travelers, :budget_min, :budget_max, :status, :preferences)";

        $params = [
            'user_id' => $data['user_id'],
            'title' => $data['title'],
            'description' => $data['description'] ?? null,
            'start_date' => $data['start_date'],
            'end_date' => $data['end_date'],
            'num_days' => $data['num_days'],
            'num_travelers' => $data['num_travelers'] ?? 1,
            'budget_min' => $data['budget_min'] ?? null,
            'budget_max' => $data['budget_max'] ?? null,
            'status' => $data['status'] ?? 'draft',
            'preferences' => $data['preferences'] ?? null,
        ];

        $this->db->query($sql, $params);
        return $this->db->lastInsertId();
    }

    /**
     * Add an item to an itinerary
     *
     * @param array $data
     * @return bool
     */
    public function addItem($data)
    {
        $sql = "INSERT INTO itinerary_items
                (itinerary_id, day_number, sequence, item_type, item_id, item_name, start_time, end_time, location, estimated_cost, notes)
                VALUES
                (:itinerary_id, :day_number, :sequence, :item_type, :item_id, :item_name, :start_time, :end_time, :location, :estimated_cost, :notes)";

        return $this->db->query($sql, $data);
    }

    /**
     * Update itinerary
     *
     * @param int $id
     * @param array $data
     * @return bool
     */
    public function update($id, $data)
    {
        $fields = [];
        $params = ['id' => $id];

        $allowed = ['title', 'description', 'start_date', 'end_date', 'num_days', 'num_travelers', 'budget_min', 'budget_max', 'status', 'total_estimated_cost'];

        foreach ($allowed as $field) {
            if (isset($data[$field])) {
                $fields[] = "{$field} = :{$field}";
                $params[$field] = $data[$field];
            }
        }

        if (empty($fields)) {
            return false;
        }

        $sql = "UPDATE {$this->table} SET " . implode(', ', $fields) . " WHERE id = :id";
        return $this->db->query($sql, $params);
    }

    /**
     * Delete an itinerary item
     *
     * @param int $itemId
     * @return bool
     */
    public function deleteItem($itemId)
    {
        return $this->db->query("DELETE FROM itinerary_items WHERE id = :id", ['id' => $itemId]);
    }

    /**
     * Delete itinerary
     *
     * @param int $id
     * @return bool
     */
    public function delete($id)
    {
        $this->db->query("DELETE FROM itinerary_items WHERE itinerary_id = :id", ['id' => $id]);
        return $this->db->query("DELETE FROM {$this->table} WHERE id = :id", ['id' => $id]);
    }

    /**
     * Calculate total estimated cost
     *
     * @param int $itineraryId
     * @return float
     */
    public function calculateTotalCost($itineraryId)
    {
        $result = $this->db->query(
            "SELECT COALESCE(SUM(estimated_cost), 0) as total FROM itinerary_items WHERE itinerary_id = :id",
            ['id' => $itineraryId]
        )->fetch();

        return (float)$result['total'];
    }

    /**
     * Generate AI-suggested itinerary based on preferences
     *
     * @param array $prefs [city, days, budget, interests, travelers]
     * @return array Suggested items grouped by day
     */
    public function generateSuggestion($prefs)
    {
        $db = Database::getInstance();
        $days = min(max((int)($prefs['days'] ?? 3), 1), 7);
        $city = $prefs['city'] ?? '';
        $interests = $prefs['interests'] ?? [];
        $budget = (float)($prefs['budget'] ?? 5000000);

        $suggestion = [];

        // Fetch destinations matching city/interests
        $destWhere = ["d.is_active = 1"];
        $destParams = [];

        if (!empty($city)) {
            $destWhere[] = "(d.city LIKE :city OR d.address LIKE :city2)";
            $destParams['city'] = "%{$city}%";
            $destParams['city2'] = "%{$city}%";
        }

        if (!empty($interests)) {
            $placeholders = [];
            foreach ($interests as $i => $interest) {
                $key = "interest_{$i}";
                $placeholders[] = ":{$key}";
                $destParams[$key] = $interest;
            }
            $destWhere[] = "c.slug IN (" . implode(',', $placeholders) . ")";
        }

        $destSql = "SELECT d.*, c.name as category_name, c.slug as category_slug
                    FROM destinations d
                    LEFT JOIN destination_categories c ON d.category_id = c.id
                    WHERE " . implode(' AND ', $destWhere) . "
                    ORDER BY d.rating_avg DESC, d.is_featured DESC
                    LIMIT :limit";
        $destParams['limit'] = $days * 2;

        $destinations = $db->query($destSql, $destParams)->fetchAll();

        // Fetch hotels matching city
        $hotels = [];
        if (!empty($city)) {
            $hotels = $db->query(
                "SELECT * FROM hotels WHERE is_approved = 1 AND is_active = 1 AND city LIKE :city ORDER BY star_rating DESC, rating_avg DESC LIMIT 3",
                ['city' => "%{$city}%"]
            )->fetchAll();
        }

        // Fetch restaurants matching city
        $restaurants = [];
        if (!empty($city)) {
            $restaurants = $db->query(
                "SELECT * FROM restaurants WHERE is_approved = 1 AND is_active = 1 AND city LIKE :city ORDER BY rating_avg DESC LIMIT 3",
                ['city' => "%{$city}%"]
            )->fetchAll();
        }

        // Build day-by-day suggestion
        $destIdx = 0;
        $restIdx = 0;

        for ($day = 1; $day <= $days; $day++) {
            $dayItems = [];

            // Morning: destination
            if (isset($destinations[$destIdx])) {
                $dest = $destinations[$destIdx];
                $dayItems[] = [
                    'item_type' => 'destination',
                    'item_id' => $dest['id'],
                    'item_name' => $dest['name'],
                    'start_time' => '09:00',
                    'end_time' => '12:00',
                    'location' => $dest['city'] ?? '',
                    'estimated_cost' => ($dest['entry_fee'] ?? 0) * ($prefs['travelers'] ?? 1),
                    'notes' => $dest['category_name'] ? "Kategori: {$dest['category_name']}" : '',
                ];
                $destIdx++;
            }

            // Lunch: restaurant
            if (isset($restaurants[$restIdx])) {
                $rest = $restaurants[$restIdx];
                $dayItems[] = [
                    'item_type' => 'restaurant',
                    'item_id' => $rest['id'],
                    'item_name' => $rest['name'],
                    'start_time' => '12:30',
                    'end_time' => '14:00',
                    'location' => $rest['city'] ?? '',
                    'estimated_cost' => 50000 * ($prefs['travelers'] ?? 1),
                    'notes' => '',
                ];
                $restIdx = ($restIdx + 1) % max(count($restaurants), 1);
            }

            // Afternoon: another destination
            if (isset($destinations[$destIdx])) {
                $dest = $destinations[$destIdx];
                $dayItems[] = [
                    'item_type' => 'destination',
                    'item_id' => $dest['id'],
                    'item_name' => $dest['name'],
                    'start_time' => '14:30',
                    'end_time' => '17:00',
                    'location' => $dest['city'] ?? '',
                    'estimated_cost' => ($dest['entry_fee'] ?? 0) * ($prefs['travelers'] ?? 1),
                    'notes' => $dest['category_name'] ? "Kategori: {$dest['category_name']}" : '',
                ];
                $destIdx++;
            }

            // Evening: hotel (only on first day or as accommodation)
            if ($day === 1 && !empty($hotels)) {
                $hotel = $hotels[0];
                $dayItems[] = [
                    'item_type' => 'hotel',
                    'item_id' => $hotel['id'],
                    'item_name' => $hotel['name'],
                    'start_time' => '18:00',
                    'end_time' => null,
                    'location' => $hotel['city'] ?? '',
                    'estimated_cost' => ($hotel['price_range_min'] ?? 300000) * ($prefs['travelers'] ?? 1),
                    'notes' => "Penginapan malam hari ke-{$day}",
                ];
            } elseif (!empty($hotels)) {
                $hotel = $hotels[0];
                $dayItems[] = [
                    'item_type' => 'hotel',
                    'item_id' => $hotel['id'],
                    'item_name' => $hotel['name'],
                    'start_time' => '18:00',
                    'end_time' => null,
                    'location' => $hotel['city'] ?? '',
                    'estimated_cost' => ($hotel['price_range_min'] ?? 300000) * ($prefs['travelers'] ?? 1),
                    'notes' => "Penginapan malam hari ke-{$day}",
                ];
            }

            $suggestion[$day] = $dayItems;
        }

        return $suggestion;
    }
}
