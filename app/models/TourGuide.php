<?php
/**
 * MyWisata Application - TourGuide Model
 * 
 * Handles tour guide related database operations.
 * 
 * @package MyWisata
 * @version 1.0.0
 * @since 2026-06-30
 */

class TourGuide extends Model {
    
    /**
     * Table name
     */
    protected $table = 'tour_guides';
    
    /**
     * Get tour guide by user ID
     * 
     * @param int $userId User ID
     * @return array|false
     */
    public function findByUserId($userId) {
        $sql = "SELECT tg.*, u.name, u.email, u.phone 
                FROM {$this->table} tg 
                LEFT JOIN users u ON tg.user_id = u.id 
                WHERE tg.user_id = :user_id";
        return $this->db->query($sql, ['user_id' => $userId])->fetch();
    }
    
    /**
     * Get tour guide by ID
     * 
     * @param int $id Tour Guide ID
     * @return array|false
     */
    public function findById($id) {
        $sql = "SELECT tg.*, u.name, u.email, u.phone 
                FROM {$this->table} tg 
                LEFT JOIN users u ON tg.user_id = u.id 
                WHERE tg.id = :id";
        return $this->db->query($sql, ['id' => $id])->fetch();
    }
    
    /**
     * Get all tour guides with filters
     * 
     * @param array $filters Optional filters
     * @return array
     */
    public function getAllWithFilters($filters = []) {
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
        
        $sql = "SELECT tg.*, u.name, u.email, u.phone, u.avatar 
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
     * @return int Tour guide ID
     */
    public function save($data) {
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
     * @return bool
     */
    public function updateAvailability($userId, $isAvailable) {
        $sql = "UPDATE {$this->table} 
                SET is_available = :is_available, updated_at = NOW() 
                WHERE user_id = :user_id";
        
        return $this->db->query($sql, [
            'is_available' => $isAvailable ? 1 : 0,
            'user_id' => $userId
        ]);
    }
    
    /**
     * Update rating
     * 
     * @param int $guideId Tour Guide ID
     * @return bool
     */
    public function updateRating($guideId) {
        $sql = "UPDATE {$this->table} tg 
                SET rating_avg = (
                    SELECT COALESCE(AVG(rating), 0) 
                    FROM reviews 
                    WHERE reviewable_type = 'guide' AND reviewable_id = :guide_id
                ),
                total_tours = (
                    SELECT COUNT(*) 
                    FROM bookings 
                    WHERE guide_id = :guide_id AND status = 'completed'
                )
                WHERE id = :guide_id";
        
        return $this->db->query($sql, ['guide_id' => $guideId]);
    }
    
    /**
     * Get guide languages
     * 
     * @param int $guideId Tour Guide ID
     * @return array
     */
    public function getLanguages($guideId) {
        $sql = "SELECT gl.*, gl.language as language_name, gl.language as native_name 
                FROM guide_languages gl 
                WHERE gl.guide_id = :guide_id";
        
        return $this->db->query($sql, ['guide_id' => $guideId])->fetchAll();
    }
    
    /**
     * Add language to guide
     * 
     * @param int $guideId Tour Guide ID
     * @param int $languageId Language ID
     * @param string $proficiency Proficiency level
     * @return bool
     */
    public function addLanguage($guideId, $languageId, $proficiency) {
        $sql = "INSERT INTO guide_languages (guide_id, language_id, proficiency, created_at)
                VALUES (:guide_id, :language_id, :proficiency, NOW())
                ON DUPLICATE KEY UPDATE proficiency = :proficiency";
        
        return $this->db->query($sql, [
            'guide_id' => $guideId,
            'language_id' => $languageId,
            'proficiency' => $proficiency
        ]);
    }
    
    /**
     * Remove language from guide
     * 
     * @param int $guideId Tour Guide ID
     * @param int $languageId Language ID
     * @return bool
     */
    public function removeLanguage($guideId, $languageId) {
        $sql = "DELETE FROM guide_languages 
                WHERE guide_id = :guide_id AND language_id = :language_id";
        
        return $this->db->query($sql, [
            'guide_id' => $guideId,
            'language_id' => $languageId
        ]);
    }
    
    /**
     * Get guide specializations
     * 
     * @param int $guideId Tour Guide ID
     * @return array
     */
    public function getSpecializations($guideId) {
        $sql = "SELECT gs.*, gs.specialization as specialization_name 
                FROM guide_specializations gs 
                WHERE gs.guide_id = :guide_id";
        
        return $this->db->query($sql, ['guide_id' => $guideId])->fetchAll();
    }
    
    /**
     * Add specialization to guide
     * 
     * @param int $guideId Tour Guide ID
     * @param int $specializationId Specialization ID
     * @return bool
     */
    public function addSpecialization($guideId, $specializationId) {
        $sql = "INSERT INTO guide_specializations (guide_id, specialization_id, created_at)
                VALUES (:guide_id, :specialization_id, NOW())
                ON DUPLICATE KEY UPDATE created_at = NOW()";
        
        return $this->db->query($sql, [
            'guide_id' => $guideId,
            'specialization_id' => $specializationId
        ]);
    }
    
    /**
     * Remove specialization from guide
     * 
     * @param int $guideId Tour Guide ID
     * @param int $specializationId Specialization ID
     * @return bool
     */
    public function removeSpecialization($guideId, $specializationId) {
        $sql = "DELETE FROM guide_specializations 
                WHERE guide_id = :guide_id AND specialization_id = :specialization_id";
        
        return $this->db->query($sql, [
            'guide_id' => $guideId,
            'specialization_id' => $specializationId
        ]);
    }
    
    /**
     * Get guide bookings
     * 
     * @param int $guideId Tour Guide ID
     * @param string $status Optional status filter
     * @return array
     */
    public function getBookings($guideId, $status = null) {
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
     * @return array
     */
    public function getEarnings($guideId, $period = 'all') {
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
    
    /**
     * Check availability for specific date and time
     * 
     * @param int $guideId Tour Guide ID
     * @param string $date Date (Y-m-d)
     * @param string $startTime Start time (H:i:s)
     * @param string $endTime End time (H:i:s)
     * @return bool Available or not
     */
    public function checkAvailability($guideId, $date, $startTime, $endTime) {
        // Check if guide is available
        $guide = $this->findById($guideId);
        if (!$guide || !$guide['is_available']) {
            return false;
        }
        
        // Check if date is in schedule
        $sql = "SELECT COUNT(*) as count FROM guide_schedules 
                WHERE guide_id = :guide_id 
                AND available_date = :date 
                AND is_booked = 0";
        
        $result = $this->db->query($sql, [
            'guide_id' => $guideId,
            'date' => $date
        ])->fetch();
        
        if ($result['count'] == 0) {
            return false;
        }
        
        // Check for overlapping bookings
        $sql = "SELECT COUNT(*) as count FROM bookings 
                WHERE guide_id = :guide_id 
                AND booking_date = :date 
                AND status IN ('pending', 'confirmed')
                AND (
                    (start_time <= :end_time AND end_time >= :start_time)
                )";
        
        $result = $this->db->query($sql, [
            'guide_id' => $guideId,
            'date' => $date,
            'start_time' => $startTime,
            'end_time' => $endTime
        ])->fetch();
        
        return $result['count'] == 0;
    }
    
    /**
     * Reserve availability slot
     * 
     * @param int $guideId Tour Guide ID
     * @param string $date Date (Y-m-d)
     * @param string $startTime Start time (H:i:s)
     * @param string $endTime End time (H:i:s)
     * @return bool Success or not
     */
    public function reserveAvailability($guideId, $date, $startTime, $endTime) {
        // Use transaction to prevent race conditions
        try {
            $this->db->beginTransaction();
            
            // Check availability again within transaction
            if (!$this->checkAvailability($guideId, $date, $startTime, $endTime)) {
                $this->db->rollBack();
                return false;
            }
            
            // Mark schedule as booked
            $sql = "UPDATE guide_schedules 
                    SET is_booked = 1 
                    WHERE guide_id = :guide_id 
                    AND available_date = :date 
                    AND start_time <= :start_time 
                    AND end_time >= :end_time 
                    AND is_booked = 0 
                    LIMIT 1";
            
            $result = $this->db->query($sql, [
                'guide_id' => $guideId,
                'date' => $date,
                'start_time' => $startTime,
                'end_time' => $endTime
            ]);
            
            if ($result) {
                $this->db->commit();
                return true;
            } else {
                $this->db->rollBack();
                return false;
            }
        } catch (Exception $e) {
            $this->db->rollBack();
            Logger::error('Failed to reserve availability', [
                'guide_id' => $guideId,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }
    
    /**
     * Release availability slot
     * 
     * @param int $guideId Tour Guide ID
     * @param string $date Date (Y-m-d)
     * @param string $startTime Start time (H:i:s)
     * @return bool Success or not
     */
    public function releaseAvailability($guideId, $date, $startTime) {
        $sql = "UPDATE guide_schedules 
                SET is_booked = 0 
                WHERE guide_id = :guide_id 
                AND available_date = :date 
                AND start_time = :start_time";
        
        return $this->db->query($sql, [
            'guide_id' => $guideId,
            'date' => $date,
            'start_time' => $startTime
        ]);
    }
    
    /**
     * Get available time slots for a date
     * 
     * @param int $guideId Tour Guide ID
     * @param string $date Date (Y-m-d)
     * @return array Available time slots
     */
    public function getAvailableSlots($guideId, $date) {
        $sql = "SELECT * FROM guide_schedules 
                WHERE guide_id = :guide_id 
                AND available_date = :date 
                AND is_booked = 0 
                ORDER BY start_time ASC";
        
        return $this->db->query($sql, [
            'guide_id' => $guideId,
            'date' => $date
        ])->fetchAll();
    }
    
    /**
     * Add schedule availability
     * 
     * @param int $guideId Tour Guide ID
     * @param string $date Date (Y-m-d)
     * @param string $startTime Start time (H:i:s)
     * @param string $endTime End time (H:i:s)
     * @return bool Success or not
     */
    public function addSchedule($guideId, $date, $startTime, $endTime) {
        $sql = "INSERT INTO guide_schedules 
                (guide_id, available_date, start_time, end_time, is_booked, created_at)
                VALUES (:guide_id, :date, :start_time, :end_time, 0, NOW())
                ON DUPLICATE KEY UPDATE 
                is_booked = 0, 
                start_time = :start_time, 
                end_time = :end_time";
        
        return $this->db->query($sql, [
            'guide_id' => $guideId,
            'date' => $date,
            'start_time' => $startTime,
            'end_time' => $endTime
        ]);
    }
    
    /**
     * Update verification status
     * 
     * @param int $guideId Guide ID
     * @param bool $isVerified Verification status
     * @return bool
     */
    public function updateVerificationStatus($guideId, $isVerified) {
        $sql = "UPDATE tour_guides 
                SET is_verified = :is_verified, 
                    verified_at = " . ($isVerified ? "NOW()" : "NULL") . "
                WHERE id = :guide_id";
        
        return $this->db->query($sql, [
            'guide_id' => $guideId,
            'is_verified' => $isVerified ? 1 : 0
        ]);
    }
    
    /**
     * Bulk add schedules
     * 
     * @param int $guideId Tour Guide ID
     * @param array $schedules Array of schedule arrays
     * @return bool
     */
    public function bulkAddSchedules($guideId, $schedules) {
        try {
            $this->db->beginTransaction();
            
            foreach ($schedules as $schedule) {
                $sql = "INSERT INTO guide_schedules 
                        (guide_id, available_date, start_time, end_time, is_booked, created_at)
                        VALUES (:guide_id, :date, :start_time, :end_time, 0, NOW())
                        ON DUPLICATE KEY UPDATE 
                        is_booked = 0, 
                        start_time = :start_time, 
                        end_time = :end_time";
                
                $this->db->query($sql, [
                    'guide_id' => $guideId,
                    'date' => $schedule['date'],
                    'start_time' => $schedule['start_time'],
                    'end_time' => $schedule['end_time']
                ]);
            }
            
            $this->db->commit();
            return true;
        } catch (Exception $e) {
            $this->db->rollBack();
            Logger::error('Failed to bulk add schedules', [
                'guide_id' => $guideId,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }
    
    /**
     * Delete schedule
     * 
     * @param int $guideId Tour Guide ID
     * @param string $date Date (Y-m-d)
     * @param string $startTime Start time (H:i:s)
     * @return bool
     */
    public function deleteSchedule($guideId, $date, $startTime) {
        $sql = "DELETE FROM guide_schedules 
                WHERE guide_id = :guide_id 
                AND available_date = :date 
                AND start_time = :start_time";
        
        return $this->db->query($sql, [
            'guide_id' => $guideId,
            'date' => $date,
            'start_time' => $startTime
        ]);
    }
    
    /**
     * Get schedules for date range
     * 
     * @param int $guideId Tour Guide ID
     * @param string $startDate Start date (Y-m-d)
     * @param string $endDate End date (Y-m-d)
     * @return array
     */
    public function getSchedulesInRange($guideId, $startDate, $endDate) {
        $sql = "SELECT * FROM guide_schedules 
                WHERE guide_id = :guide_id 
                AND available_date BETWEEN :start_date AND :end_date
                ORDER BY available_date ASC, start_time ASC";
        
        return $this->db->query($sql, [
            'guide_id' => $guideId,
            'start_date' => $startDate,
            'end_date' => $endDate
        ])->fetchAll();
    }
    
    /**
     * Validate tour guide data
     * 
     * @param array $data Tour guide data to validate
     * @return array Validation errors
     */
    public function validate($data) {
        $errors = [];
        
        if (empty($data['name'])) {
            $errors['name'] = 'Nama wajib diisi';
        } elseif (strlen($data['name']) < 3) {
            $errors['name'] = 'Nama minimal 3 karakter';
        }
        
        if (empty($data['phone'])) {
            $errors['phone'] = 'Nomor telepon wajib diisi';
        } elseif (!preg_match('/^[0-9\+\-\(\)\s]+$/', $data['phone'])) {
            $errors['phone'] = 'Format nomor telepon tidak valid';
        }
        
        if (isset($data['hourly_rate']) && !is_numeric($data['hourly_rate'])) {
            $errors['hourly_rate'] = 'Tarif per jam harus berupa angka';
        }
        
        if (isset($data['daily_rate']) && !is_numeric($data['daily_rate'])) {
            $errors['daily_rate'] = 'Tarif per hari harus berupa angka';
        }
        
        if (isset($data['experience_years']) && !is_numeric($data['experience_years'])) {
            $errors['experience_years'] = 'Pengalaman harus berupa angka';
        }
        
        if (isset($data['experience_years']) && $data['experience_years'] < 0) {
            $errors['experience_years'] = 'Pengalaman tidak boleh negatif';
        }
        
        return $errors;
    }
}
