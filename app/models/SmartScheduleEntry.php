<?php
namespace App\Models;

use App\Core\Model;

class SmartScheduleEntry extends Model {
    protected $table = 'smart_schedule_entries';
    protected $primaryKey = 'id';
    
    /**
     * Get schedule by guide
     */
    public function getByGuideId($guideId, $startDate = null, $endDate = null) {
        $where = "guide_id = ?";
        $params = [$guideId];
        
        if ($startDate && $endDate) {
            $where .= " AND start_datetime BETWEEN ? AND ?";
            $params[] = $startDate;
            $params[] = $endDate;
        }
        
        return $this->db->query(
            "SELECT * FROM {$this->table} 
             WHERE {$where} 
             ORDER BY start_datetime ASC",
            $params
        )->fetchAll();
    }
    
    /**
     * Create schedule entry
     */
    public function create($data) {
        $data['created_at'] = date('Y-m-d H:i:s');
        $data['updated_at'] = date('Y-m-d H:i:s');
        return $this->db->insert($this->table, $data);
    }
    
    /**
     * Update schedule entry
     */
    public function update($id, $data) {
        $data['updated_at'] = date('Y-m-d H:i:s');
        return $this->db->update($this->table, $data, "id = ?", [$id]);
    }
    
    /**
     * Get conflicts for guide
     */
    public function getConflicts($guideId, $startDateTime, $endDateTime, $excludeId = null) {
        $where = "guide_id = ? AND status != 'cancelled' AND 
                  ((start_datetime < ? AND end_datetime > ?) OR 
                   (start_datetime < ? AND end_datetime > ?) OR 
                   (start_datetime >= ? AND end_datetime <= ?))";
        $params = [$guideId, $endDateTime, $startDateTime, $endDateTime, $startDateTime, $startDateTime, $endDateTime];
        
        if ($excludeId) {
            $where .= " AND id != ?";
            $params[] = $excludeId;
        }
        
        return $this->db->query(
            "SELECT * FROM {$this->table} 
             WHERE {$where}",
            $params
        )->fetchAll();
    }
}
