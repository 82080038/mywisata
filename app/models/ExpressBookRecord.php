<?php
namespace App\Models;

use App\Core\Model;

class ExpressBookRecord extends Model {
    protected $table = 'express_book_records';
    protected $primaryKey = 'id';
    
    /**
     * Get records by guide
     */
    public function getByGuideId($guideId, $limit = 50) {
        return $this->db->query(
            "SELECT * FROM {$this->table} 
             WHERE guide_id = ? 
             ORDER BY start_datetime DESC 
             LIMIT ?",
            [$guideId, $limit]
        )->fetchAll();
    }
    
    /**
     * Create express book record
     */
    public function create($data) {
        $data['created_at'] = date('Y-m-d H:i:s');
        return $this->db->insert($this->table, $data);
    }
    
    /**
     * Update payment status
     */
    public function updatePaymentStatus($id, $status) {
        $data = ['payment_status' => $status];
        return $this->db->update($this->table, $data, "id = ?", [$id]);
    }
    
    /**
     * Get records by date range
     */
    public function getByDateRange($guideId, $startDate, $endDate) {
        return $this->db->query(
            "SELECT * FROM {$this->table} 
             WHERE guide_id = ? AND start_datetime BETWEEN ? AND ? 
             ORDER BY start_datetime ASC",
            [$guideId, $startDate, $endDate]
        )->fetchAll();
    }
}
