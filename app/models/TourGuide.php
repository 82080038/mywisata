<?php

/**
 * MyWisata Application - TourGuide Model
 *
 * Handles tour guide related database operations.
 *
 * @version 1.0.0
 *
 * @since 2026-06-30
 */
class TourGuide extends Model
{
    /**
     * Table name
     */
    protected $table = 'tour_guides';

    /**
     * Get tour guide by user ID
     *
     * @param int $userId User ID
     *
     * @return array|false
     */
    public function findByUserId($userId)
    {
        $sql = "SELECT tg.*, u.email, u.phone as user_phone
                FROM {$this->table} tg 
                LEFT JOIN users u ON tg.user_id = u.id 
                WHERE tg.user_id = :user_id";

        return $this->db->query($sql, ['user_id' => $userId])->fetch();
    }

    /**
     * Get tour guide by ID
     *
     * @param int $id Tour Guide ID
     *
     * @return array|false
     */
    public function findById($id)
    {
        $sql = "SELECT tg.*, u.email, u.phone as user_phone
                FROM {$this->table} tg 
                LEFT JOIN users u ON tg.user_id = u.id 
                WHERE tg.id = :id";

        return $this->db->query($sql, ['id' => $id])->fetch();
    }

    /**
     * Get all tour guides with filters
     *
     * @param array $filters Optional filters
     *
     * @return array
     */
    public function getAllWithFilters($filters = [])
    {
        $where = ['1=1'];
        $params = [];

        if (!empty($filters['is_verified'])) {
            $where[] = "is_verified = :is_verified";
            $params['is_verified'] = $filters['is_verified'];
        }

        if (!empty($filters['is_available'])) {
            $where[] = "is_available = :is_available";
            $params['is_available'] = $filters['is_available'];
        }

        if (!empty($filters['city'])) {
            $where[] = "city LIKE :city";
            $params['city'] = "%{$filters['city']}%";
        }

        $whereClause = implode(' AND ', $where);

        $sql = "SELECT tg.*, u.email, u.phone as user_phone
                FROM {$this->table} tg 
                LEFT JOIN users u ON tg.user_id = u.id 
                WHERE {$whereClause} 
                ORDER BY tg.rating_avg DESC";

        return $this->db->query($sql, $params)->fetchAll();
    }

    /**
     * Create or update tour guide profile
     *
     * @param array $data Tour guide data
     *
     * @return int Tour guide ID
     */
    public function save($data)
    {
        $existing = $this->findByUserId($data['user_id']);

        if ($existing) {
            // Update
            $sql = "UPDATE {$this->table} 
                    SET name = :name, phone = :phone, bio = :bio, 
                        license_number = :license_number, experience_years = :experience_years,
                        hourly_rate = :hourly_rate, daily_rate = :daily_rate,
                        city = :city, latitude = :latitude, longitude = :longitude,
                        is_available = :is_available, updated_at = NOW()
                    WHERE user_id = :user_id";

            $this->db->query($sql, $data);

            return $existing['id'];
        } else {
            // Insert
            $sql = "INSERT INTO {$this->table} 
                    (user_id, name, phone, bio, license_number, experience_years, 
                     hourly_rate, daily_rate, city, latitude, longitude, is_available, 
                     rating_avg, total_tours, is_verified, created_at, updated_at)
                    VALUES 
                    (:user_id, :name, :phone, :bio, :license_number, :experience_years,
                     :hourly_rate, :daily_rate, :city, :latitude, :longitude, :is_available,
                     0, 0, 0, NOW(), NOW())";

            $this->db->query($sql, $data);

            return $this->db->lastInsertId();
        }
    }

    /**
     * Update availability
     *
     * @param int $userId User ID
     * @param bool $isAvailable Availability status
     *
     * @return bool
     */
    public function updateAvailability($userId, $isAvailable)
    {
        $sql = "UPDATE {$this->table} 
                SET is_available = :is_available, updated_at = NOW() 
                WHERE user_id = :user_id";

        return $this->db->query($sql, [
            'is_available' => $isAvailable ? 1 : 0,
            'user_id' => $userId,
        ]);
    }

    /**
     * Update rating
     *
     * @param int $guideId Tour Guide ID
     *
     * @return bool
     */
    public function updateRating($guideId)
    {
        $sql = "UPDATE {$this->table} tg 
                SET rating_avg = (
                    SELECT COALESCE(AVG(rating), 0) 
                    FROM guide_reviews 
                    WHERE guide_id = :gid1
                ),
                total_tours = (
                    SELECT COUNT(*) 
                    FROM bookings 
                    WHERE guide_id = :gid2 AND status = 'completed'
                )
                WHERE id = :gid3";

        return $this->db->query($sql, ['gid1' => $guideId, 'gid2' => $guideId, 'gid3' => $guideId]);
    }

    /**
     * Get guide languages
     *
     * @param int $guideId Tour Guide ID
     *
     * @return array
     */
    public function getLanguages($guideId)
    {
        $sql = "SELECT gl.*, 
                COALESCE(l.name, gl.language) as language_name, 
                COALESCE(l.native_name, gl.language) as native_name 
                FROM guide_languages gl 
                LEFT JOIN languages l ON gl.language_id = l.id 
                WHERE gl.guide_id = :guide_id";

        return $this->db->query($sql, ['guide_id' => $guideId])->fetchAll();
    }

    /**
     * Add language to guide
     *
     * @param int $guideId Tour Guide ID
     * @param int $languageId Language ID
     * @param string $proficiency Proficiency level
     *
     * @return bool
     */
    public function addLanguage($guideId, $languageId, $proficiency)
    {
        // Look up language name from languages table
        $lang = $this->db->query("SELECT name FROM languages WHERE id = :id", ['id' => $languageId])->fetch();
        $langName = $lang ? $lang['name'] : '';

        $sql = "INSERT INTO guide_languages (guide_id, language_id, language, proficiency)
                VALUES (:guide_id, :language_id, :language, :proficiency)
                ON DUPLICATE KEY UPDATE proficiency = :proficiency, language_id = :language_id, language = :language";

        return $this->db->query($sql, [
            'guide_id' => $guideId,
            'language_id' => $languageId,
            'language' => $langName,
            'proficiency' => $proficiency,
        ]);
    }

    /**
     * Remove language from guide
     *
     * @param int $guideId Tour Guide ID
     * @param int $languageId Language ID
     *
     * @return bool
     */
    public function removeLanguage($guideId, $languageId)
    {
        $sql = "DELETE FROM guide_languages 
                WHERE guide_id = :guide_id AND (language_id = :language_id OR language = :language_id)";

        return $this->db->query($sql, [
            'guide_id' => $guideId,
            'language_id' => $languageId,
        ]);
    }

    /**
     * Get guide specializations
     *
     * @param int $guideId Tour Guide ID
     *
     * @return array
     */
    public function getSpecializations($guideId)
    {
        $sql = "SELECT gs.*, 
                COALESCE(s.name, gs.specialization) as specialization_name 
                FROM guide_specializations gs 
                LEFT JOIN specializations s ON gs.specialization_id = s.id 
                WHERE gs.guide_id = :guide_id";

        return $this->db->query($sql, ['guide_id' => $guideId])->fetchAll();
    }

    /**
     * Add specialization to guide
     *
     * @param int $guideId Tour Guide ID
     * @param int $specializationId Specialization ID
     *
     * @return bool
     */
    public function addSpecialization($guideId, $specializationId)
    {
        // Look up specialization name
        $spec = $this->db->query("SELECT name FROM specializations WHERE id = :id", ['id' => $specializationId])->fetch();
        $specName = $spec ? $spec['name'] : '';

        $sql = "INSERT INTO guide_specializations (guide_id, specialization_id, specialization)
                VALUES (:guide_id, :specialization_id, :specialization)
                ON DUPLICATE KEY UPDATE specialization_id = :specialization_id, specialization = :specialization";

        return $this->db->query($sql, [
            'guide_id' => $guideId,
            'specialization_id' => $specializationId,
            'specialization' => $specName,
        ]);
    }

    /**
     * Remove specialization from guide
     *
     * @param int $guideId Tour Guide ID
     * @param int $specializationId Specialization ID
     *
     * @return bool
     */
    public function removeSpecialization($guideId, $specializationId)
    {
        $sql = "DELETE FROM guide_specializations 
                WHERE guide_id = :guide_id AND (specialization_id = :specialization_id OR specialization = :specialization_id)";

        return $this->db->query($sql, [
            'guide_id' => $guideId,
            'specialization_id' => $specializationId,
        ]);
    }

    /**
     * Get guide bookings
     *
     * @param int $guideId Tour Guide ID
     * @param string $status Optional status filter
     *
     * @return array
     */
    public function getBookings($guideId, $status = null)
    {
        $where = "b.guide_id = :guide_id";
        $params = ['guide_id' => $guideId];

        if ($status) {
            $where .= " AND b.status = :status";
            $params['status'] = $status;
        }

        $sql = "SELECT b.*, u.name as user_name, u.email as user_email 
                FROM bookings b 
                LEFT JOIN users u ON b.user_id = u.id 
                WHERE {$where} 
                ORDER BY b.booking_date DESC";

        return $this->db->query($sql, $params)->fetchAll();
    }

    /**
     * Get guide earnings
     *
     * @param int $guideId Tour Guide ID
     * @param string $period Period (month, year, all)
     *
     * @return array
     */
    public function getEarnings($guideId, $period = 'all')
    {
        $where = "t.guide_id = :guide_id AND t.payment_status = 'paid'";
        $params = ['guide_id' => $guideId];

        if ($period === 'month') {
            $where .= " AND MONTH(t.created_at) = MONTH(CURRENT_DATE) 
                       AND YEAR(t.created_at) = YEAR(CURRENT_DATE)";
        } elseif ($period === 'year') {
            $where .= " AND YEAR(t.created_at) = YEAR(CURRENT_DATE)";
        }

        $sql = "SELECT COALESCE(SUM(net_amount), 0) as total, COUNT(*) as count
                FROM transactions t
                WHERE {$where}";

        return $this->db->query($sql, $params)->fetch();
    }
}
