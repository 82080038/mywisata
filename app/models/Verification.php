<?php
/**
 * MyWisata Application - Verification Model
 * 
 * Handles tour guide verification related database operations.
 * 
 * @package MyWisata
 * @version 1.0.0
 * @since 2026-07-16
 */

class Verification extends Model {
    
    /**
     * Table name
     */
    protected $table = 'guide_verifications';
    
    /**
     * Create a verification record
     * 
     * @param array $data Verification data
     * @return int Verification ID
     */
    public function create($data) {
        $sql = "INSERT INTO {$this->table} 
                (guide_id, identity_type, identity_number, identity_document, 
                 certification_type, certification_number, certification_document, 
                 portfolio_document, experience_years, languages, specializations, 
                 status, submitted_at)
                VALUES 
                (:guide_id, :identity_type, :identity_number, :identity_document, 
                 :certification_type, :certification_number, :certification_document, 
                 :portfolio_document, :experience_years, :languages, :specializations, 
                 :status, NOW())";
        
        $this->db->query($sql, $data);
        return $this->db->lastInsertId();
    }
    
    /**
     * Get verification by ID
     * 
     * @param int $id Verification ID
     * @return array|false
     */
    public function findById($id) {
        $sql = "SELECT v.*, 
                tg.name as guide_name, tg.user_id as guide_user_id,
                u.name as user_name, u.email as user_email
                FROM {$this->table} v 
                LEFT JOIN tour_guides tg ON v.guide_id = tg.id
                LEFT JOIN users u ON tg.user_id = u.id
                WHERE v.id = :id";
        
        return $this->db->query($sql, ['id' => $id])->fetch();
    }
    
    /**
     * Get verification by guide ID
     * 
     * @param int $guideId Guide ID
     * @return array|false
     */
    public function getByGuideId($guideId) {
        $sql = "SELECT v.* 
                FROM {$this->table} v 
                WHERE v.guide_id = :guide_id 
                ORDER BY v.submitted_at DESC 
                LIMIT 1";
        
        return $this->db->query($sql, ['guide_id' => $guideId])->fetch();
    }
    
    /**
     * Get all verifications with optional status filter
     * 
     * @param string $status Optional status filter
     * @param int $page Page number
     * @param int $limit Items per page
     * @return array
     */
    public function getAll($status = null, $page = 1, $limit = 20) {
        $offset = ($page - 1) * $limit;
        
        $where = "1=1";
        $params = [];
        
        if ($status) {
            $where = "v.status = :status";
            $params['status'] = $status;
        }
        
        $sql = "SELECT v.*, 
                tg.name as guide_name, tg.user_id as guide_user_id,
                u.name as user_name, u.email as user_email
                FROM {$this->table} v 
                LEFT JOIN tour_guides tg ON v.guide_id = tg.id
                LEFT JOIN users u ON tg.user_id = u.id
                WHERE {$where}
                ORDER BY v.submitted_at DESC
                LIMIT :limit OFFSET :offset";
        
        $params['limit'] = $limit;
        $params['offset'] = $offset;
        
        return $this->db->query($sql, $params)->fetchAll();
    }
    
    /**
     * Count verifications by status
     * 
     * @param string $status Status
     * @return int
     */
    public function countByStatus($status = null) {
        $where = "1=1";
        $params = [];
        
        if ($status) {
            $where = "status = :status";
            $params['status'] = $status;
        }
        
        $sql = "SELECT COUNT(*) as count FROM {$this->table} WHERE {$where}";
        $result = $this->db->query($sql, $params)->fetch();
        
        return $result['count'];
    }
    
    /**
     * Update verification
     * 
     * @param int $id Verification ID
     * @param array $data Data to update
     * @return bool
     */
    public function update($id, $data) {
        $sql = "UPDATE {$this->table} 
                SET identity_type = :identity_type,
                    identity_number = :identity_number,
                    identity_document = :identity_document,
                    certification_type = :certification_type,
                    certification_number = :certification_number,
                    certification_document = :certification_document,
                    portfolio_document = :portfolio_document,
                    experience_years = :experience_years,
                    languages = :languages,
                    specializations = :specializations,
                    status = :status,
                    submitted_at = NOW()
                WHERE id = :id";
        
        $data['id'] = $id;
        return $this->db->query($sql, $data);
    }
    
    /**
     * Approve verification
     * 
     * @param int $id Verification ID
     * @param string $notes Admin notes
     * @return bool
     */
    public function approve($id, $notes = null) {
        $sql = "UPDATE {$this->table} 
                SET status = 'approved',
                    admin_notes = :admin_notes,
                    reviewed_at = NOW(),
                    reviewed_by = :admin_id
                WHERE id = :id";
        
        return $this->db->query($sql, [
            'id' => $id,
            'admin_notes' => $notes,
            'admin_id' => Session::get('user_id')
        ]);
    }
    
    /**
     * Reject verification
     * 
     * @param int $id Verification ID
     * @param string $reason Rejection reason
     * @return bool
     */
    public function reject($id, $reason) {
        $sql = "UPDATE {$this->table} 
                SET status = 'rejected',
                    rejection_reason = :rejection_reason,
                    reviewed_at = NOW(),
                    reviewed_by = :admin_id
                WHERE id = :id";
        
        return $this->db->query($sql, [
            'id' => $id,
            'rejection_reason' => $reason,
            'admin_id' => Session::get('user_id')
        ]);
    }
    
    /**
     * Reset verification to pending (for re-verification)
     * 
     * @param int $id Verification ID
     * @return bool
     */
    public function resetToPending($id) {
        $sql = "UPDATE {$this->table} 
                SET status = 'pending',
                    rejection_reason = NULL,
                    reviewed_at = NULL,
                    reviewed_by = NULL,
                    submitted_at = NOW()
                WHERE id = :id";
        
        return $this->db->query($sql, ['id' => $id]);
    }
    
    /**
     * Get verification statistics
     * 
     * @return array
     */
    public function getStatistics() {
        $sql = "SELECT 
                COUNT(*) as total,
                SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending,
                SUM(CASE WHEN status = 'approved' THEN 1 ELSE 0 END) as approved,
                SUM(CASE WHEN status = 'rejected' THEN 1 ELSE 0 END) as rejected
                FROM {$this->table}";
        
        return $this->db->query($sql)->fetch();
    }
    
    /**
     * Get recent verifications
     * 
     * @param int $limit Number of recent verifications
     * @return array
     */
    public function getRecent($limit = 10) {
        $sql = "SELECT v.*, 
                tg.name as guide_name,
                u.name as user_name
                FROM {$this->table} v 
                LEFT JOIN tour_guides tg ON v.guide_id = tg.id
                LEFT JOIN users u ON tg.user_id = u.id
                ORDER BY v.submitted_at DESC
                LIMIT :limit";
        
        return $this->db->query($sql, ['limit' => $limit])->fetchAll();
    }
}
