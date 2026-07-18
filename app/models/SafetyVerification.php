<?php
/**
 * MyWisata Application - Safety Verification Model
 * 
 * @package MyWisata
 * @version 1.0.0
 * @since 2026-07-18
 */

class SafetyVerification extends Model {
    
    protected $table = 'safety_verifications';
    
    /**
     * Create verification
     */
    public function create($data) {
        $sql = "INSERT INTO {$this->table} (activity_id, user_id, verification_date, health_declaration, emergency_contact, waiver_signed, status) 
                VALUES (:activity_id, :user_id, :verification_date, :health_declaration, :emergency_contact, :waiver_signed, :status)";
        return $this->execute($sql, $data);
    }
    
    /**
     * Find by activity and user
     */
    public function findByActivityAndUser($activityId, $userId) {
        $sql = "SELECT * FROM {$this->table} WHERE activity_id = :activity_id AND user_id = :user_id LIMIT 1";
        return $this->query($sql, ['activity_id' => $activityId, 'user_id' => $userId])[0] ?? null;
    }
}
