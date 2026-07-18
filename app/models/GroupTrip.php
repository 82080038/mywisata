<?php
namespace App\Models;

use App\Core\Model;

class GroupTrip extends Model {
    protected $table = 'group_trips';
    protected $primaryKey = 'id';
    
    /**
     * Get trips by user
     */
    public function getByUserId($userId, $limit = 20) {
        return $this->db->query(
            "SELECT gt.*, 
                    (SELECT COUNT(*) FROM group_trip_participants WHERE group_trip_id = gt.id) as participant_count
             FROM {$this->table} gt
             WHERE gt.created_by = ? 
             ORDER BY gt.created_at DESC 
             LIMIT ?",
            [$userId, $limit]
        )->fetchAll();
    }
    
    /**
     * Get trip by ID
     */
    public function getById($tripId) {
        return $this->db->query(
            "SELECT * FROM {$this->table} 
             WHERE id = ?",
            [$tripId]
        )->fetch();
    }
    
    /**
     * Create group trip
     */
    public function create($data) {
        $data['created_at'] = date('Y-m-d H:i:s');
        $data['updated_at'] = date('Y-m-d H:i:s');
        return $this->db->insert($this->table, $data);
    }
    
    /**
     * Update trip
     */
    public function update($id, $data) {
        $data['updated_at'] = date('Y-m-d H:i:s');
        return $this->db->update($this->table, $data, "id = ?", [$id]);
    }
    
    /**
     * Update status
     */
    public function updateStatus($id, $status) {
        $data = ['status' => $status, 'updated_at' => date('Y-m-d H:i:s')];
        return $this->db->update($this->table, $data, "id = ?", [$id]);
    }
}
