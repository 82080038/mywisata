<?php
namespace App\Models;

use App\Core\Model;

class GroupTripParticipant extends Model {
    protected $table = 'group_trip_participants';
    protected $primaryKey = 'id';
    
    /**
     * Get participants by trip
     */
    public function getByTripId($tripId) {
        return $this->db->query(
            "SELECT gtp.*, u.name, u.email, u.avatar 
             FROM {$this->table} gtp
             JOIN users u ON gtp.user_id = u.id
             WHERE gtp.group_trip_id = ? 
             ORDER BY gtp.role DESC, gtp.joined_at ASC",
            [$tripId]
        )->fetchAll();
    }
    
    /**
     * Get trips by user
     */
    public function getTripsByUserId($userId) {
        return $this->db->query(
            "SELECT gtp.*, gt.trip_name, gt.start_date, gt.end_date, gt.status
             FROM {$this->table} gtp
             JOIN group_trips gt ON gtp.group_trip_id = gt.id
             WHERE gtp.user_id = ? AND gtp.status = 'accepted'
             ORDER BY gt.start_date DESC",
            [$userId]
        )->fetchAll();
    }
    
    /**
     * Add participant
     */
    public function create($data) {
        $data['created_at'] = date('Y-m-d H:i:s');
        return $this->db->insert($this->table, $data);
    }
    
    /**
     * Update status
     */
    public function updateStatus($tripId, $userId, $status) {
        $data = ['status' => $status];
        if ($status === 'accepted') {
            $data['joined_at'] = date('Y-m-d H:i:s');
        }
        return $this->db->update($this->table, $data, "group_trip_id = ? AND user_id = ?", [$tripId, $userId]);
    }
    
    /**
     * Remove participant
     */
    public function remove($tripId, $userId) {
        return $this->db->query(
            "DELETE FROM {$this->table} 
             WHERE group_trip_id = ? AND user_id = ?",
            [$tripId, $userId]
        );
    }
}
