<?php
namespace App\Models;

use App\Core\Model;

class TripAlbumComment extends Model {
    protected $table = 'trip_album_comments';
    protected $primaryKey = 'id';
    
    /**
     * Get comments by photo
     */
    public function getByPhotoId($photoId) {
        return $this->db->query(
            "SELECT tac.*, u.name, u.avatar 
             FROM {$this->table} tac
             JOIN users u ON tac.user_id = u.id
             WHERE tac.photo_id = ? 
             ORDER BY tac.created_at ASC",
            [$photoId]
        )->fetchAll();
    }
    
    /**
     * Create comment
     */
    public function create($data) {
        $data['created_at'] = date('Y-m-d H:i:s');
        return $this->db->insert($this->table, $data);
    }
    
    /**
     * Delete comment
     */
    public function delete($id) {
        return $this->db->query(
            "DELETE FROM {$this->table} 
             WHERE id = ?",
            [$id]
        );
    }
}
