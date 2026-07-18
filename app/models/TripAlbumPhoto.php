<?php
namespace App\Models;

use App\Core\Model;

class TripAlbumPhoto extends Model {
    protected $table = 'trip_album_photos';
    protected $primaryKey = 'id';
    
    /**
     * Get photos by album
     */
    public function getByAlbumId($albumId, $limit = 50) {
        return $this->db->query(
            "SELECT tap.*, u.name as uploader_name 
             FROM {$this->table} tap
             JOIN users u ON tap.uploaded_by = u.id
             WHERE tap.album_id = ? 
             ORDER BY tap.created_at DESC 
             LIMIT ?",
            [$albumId, $limit]
        )->fetchAll();
    }
    
    /**
     * Create photo
     */
    public function create($data) {
        $data['created_at'] = date('Y-m-d H:i:s');
        return $this->db->insert($this->table, $data);
    }
    
    /**
     * Delete photo
     */
    public function delete($id) {
        return $this->db->query(
            "DELETE FROM {$this->table} 
             WHERE id = ?",
            [$id]
        );
    }
}
