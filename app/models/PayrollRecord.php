<?php
namespace App\Models;

use App\Core\Model;

class PayrollRecord extends Model {
    protected $table = 'payroll_records';
    protected $primaryKey = 'id';
    
    /**
     * Get payroll by guide
     */
    public function getByGuideId($guideId, $limit = 20) {
        return $this->db->query(
            "SELECT * FROM {$this->table} 
             WHERE guide_id = ? 
             ORDER BY period_start DESC 
             LIMIT ?",
            [$guideId, $limit]
        )->fetchAll();
    }
    
    /**
     * Get payroll by period
     */
    public function getByPeriod($startDate, $endDate) {
        return $this->db->query(
            "SELECT * FROM {$this->table} 
             WHERE period_start = ? AND period_end = ? 
             ORDER BY guide_id ASC",
            [$startDate, $endDate]
        )->fetchAll();
    }
    
    /**
     * Create payroll record
     */
    public function create($data) {
        $data['created_at'] = date('Y-m-d H:i:s');
        $data['updated_at'] = date('Y-m-d H:i:s');
        return $this->db->insert($this->table, $data);
    }
    
    /**
     * Update payroll record
     */
    public function update($id, $data) {
        $data['updated_at'] = date('Y-m-d H:i:s');
        return $this->db->update($this->table, $data, "id = ?", [$id]);
    }
    
    /**
     * Approve payroll
     */
    public function approve($id, $approvedBy) {
        $data = [
            'status' => 'approved',
            'approved_by' => $approvedBy,
            'approved_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')
        ];
        return $this->db->update($this->table, $data, "id = ?", [$id]);
    }
    
    /**
     * Mark as paid
     */
    public function markPaid($id) {
        $data = [
            'status' => 'paid',
            'paid_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')
        ];
        return $this->db->update($this->table, $data, "id = ?", [$id]);
    }
}
