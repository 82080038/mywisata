<?php
namespace App\Models;

use App\Core\Model;

class TripAlbum extends Model {
    protected $table = 'trip_albums';
    protected $primaryKey = 'id';
    
    /**
     * Get albums by trip
     */
    public function getByTripId($tripId) {
        return $this->db->query(
            "SELECT ta.*, 
                    (SELECT COUNT(*) FROM trip_album_photos WHERE album_id = ta.id) as photo_count
             FROM {$this->table} ta
             WHERE ta.group_trip_id = ? 
             ORDER BY ta.created_at DESC",
            [$tripId]
        )->fetchAll();
    }
    
    /**
     * Get albums by user
     */
    public function getByUserId($userId, $limit = 20) {
        return $this->db->query(
            "SELECT ta.*, 
                    (SELECT COUNT(*) FROM trip_album_photos WHERE album_id = ta.id) as photo_count
             FROM {$this->table} ta
             WHERE ta.created_by = ? 
             ORDER BY ta.created_at DESC 
             LIMIT ?",
            [$userId, $limit]
        )->fetchAll();
    }
    
    /**
     * Create album
     */
    public function create($data) {
        $data['created_at'] = date('Y-m-d H:i:s');
        $data['updated_at'] = date('Y-m-d H:i:s');
        return $this->db->insert($this->table, $data);
    }
    
    /**
     * Update album
     */
    public function update($id, $data) {
        $data['updated_at'] = date('Y-m-d H:i:s');
        return $this->db->update($this->table, $data, "id = ?", [$id]);
    }
}
