<?php
/**
 * MyWisata Application - Itinerary Sharing Model
 * 
 * @package MyWisata
 * @version 1.0.0
 * @since 2026-07-18
 */

class ItinerarySharing extends Model {
    
    protected $table = 'itinerary_sharing';
    
    /**
     * Create sharing record
     */
    public function create($data) {
        $sql = "INSERT INTO {$this->table} (itinerary_id, shared_by_user_id, shared_with_user_id, share_type, share_token, share_link, can_edit, can_comment, expires_at) 
                VALUES (:itinerary_id, :shared_by_user_id, :shared_with_user_id, :share_type, :share_token, :share_link, :can_edit, :can_comment, :expires_at)";
        return $this->execute($sql, $data);
    }
    
    /**
     * Find by token
     */
    public function findByToken($token) {
        $sql = "SELECT * FROM {$this->table} WHERE share_token = :token AND (expires_at IS NULL OR expires_at > NOW()) LIMIT 1";
        return $this->query($sql, ['token' => $token])[0] ?? null;
    }
}
