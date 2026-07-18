<?php
namespace App\Models;

use App\Core\Model;

class GPSClockInRecord extends Model {
    protected $table = 'gps_clock_in_records';
    protected $primaryKey = 'id';
    
    /**
     * Get records by guide
     */
    public function getByGuideId($guideId, $limit = 50) {
        return $this->db->query(
            "SELECT * FROM {$this->table} 
             WHERE guide_id = ? 
             ORDER BY clock_in_time DESC 
             LIMIT ?",
            [$guideId, $limit]
        )->fetchAll();
    }
    
    /**
     * Get records by booking
     */
    public function getByBookingId($bookingId) {
        return $this->db->query(
            "SELECT * FROM {$this->table} 
             WHERE booking_id = ? 
             ORDER BY clock_in_time ASC",
            [$bookingId]
        )->fetchAll();
    }
    
    /**
     * Create clock-in record
     */
    public function create($data) {
        $data['created_at'] = date('Y-m-d H:i:s');
        return $this->db->insert($this->table, $data);
    }
    
    /**
     * Update clock-out
     */
    public function clockOut($id, $data) {
        $data['clock_out_time'] = date('Y-m-d H:i:s');
        $data['status'] = 'clocked_out';
        
        // Calculate hours worked
        if (isset($data['clock_in_time'])) {
            $clockIn = strtotime($data['clock_in_time']);
            $clockOut = strtotime($data['clock_out_time']);
            $data['hours_worked'] = round(($clockOut - $clockIn) / 3600, 2);
        }
        
        return $this->db->update($this->table, $data, "id = ?", [$id]);
    }
    
    /**
     * Get total hours by guide in period
     */
    public function getTotalHours($guideId, $startDate, $endDate) {
        $result = $this->db->query(
            "SELECT SUM(hours_worked) as total FROM {$this->table} 
             WHERE guide_id = ? AND clock_in_time BETWEEN ? AND ?",
            [$guideId, $startDate, $endDate]
        )->fetch();
        return $result['total'] ?? 0;
    }
}
