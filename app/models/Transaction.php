<?php
/**
 * MyWisata Application - Transaction Model
 * 
 * Handles transaction related database operations.
 * 
 * @package MyWisata
 * @version 1.0.0
 * @since 2026-07-01
 */

class Transaction extends Model {
    
    /**
     * Table name
     */
    protected $table = 'transactions';
    
    /**
     * Create transaction
     * 
     * @param array $data Transaction data
     * @return int Transaction ID
     */
    public function create($data) {
        $sql = "INSERT INTO {$this->table} 
                (transaction_code, user_id, booking_id, type, gross_amount, discount_amount, 
                 tax_amount, net_amount, payment_method, payment_status, created_at)
                VALUES 
                (:transaction_code, :user_id, :booking_id, :type, :gross_amount, :discount_amount,
                 :tax_amount, :net_amount, :payment_method, 'pending', NOW())";
        
        $this->db->query($sql, $data);
        return $this->db->lastInsertId();
    }
    
    /**
     * Get transaction by ID
     * 
     * @param int $id Transaction ID
     * @return array|false
     */
    public function findById($id) {
        $sql = "SELECT t.*, u.name as user_name 
                FROM {$this->table} t 
                LEFT JOIN users u ON t.user_id = u.id 
                WHERE t.id = :id";
        
        return $this->db->query($sql, ['id' => $id])->fetch();
    }
    
    /**
     * Get transactions by user ID
     * 
     * @param int $userId User ID
     * @return array
     */
    public function getByUserId($userId) {
        $sql = "SELECT t.*, b.booking_code 
                FROM {$this->table} t 
                LEFT JOIN bookings b ON t.booking_id = b.id 
                WHERE t.user_id = :user_id 
                ORDER BY t.created_at DESC";
        
        return $this->db->query($sql, ['user_id' => $userId])->fetchAll();
    }
    
    /**
     * Update payment status
     * 
     * @param int $id Transaction ID
     * @param string $status Payment status
     * @return bool
     */
    public function updatePaymentStatus($id, $status) {
        $sql = "UPDATE {$this->table} SET payment_status = :status, updated_at = NOW() WHERE id = :id";
        return $this->db->query($sql, ['id' => $id, 'status' => $status]);
    }
    
    /**
     * Update payment method
     * 
     * @param int $id Transaction ID
     * @param string $method Payment method
     * @return bool
     */
    public function updatePaymentMethod($id, $method) {
        $sql = "UPDATE {$this->table} SET payment_method = :method, updated_at = NOW() WHERE id = :id";
        return $this->db->query($sql, ['id' => $id, 'method' => $method]);
    }
    
    /**
     * Update payment proof
     * 
     * @param int $id Transaction ID
     * @param string $proof Payment proof file path
     * @return bool
     */
    public function updatePaymentProof($id, $proof) {
        $sql = "UPDATE {$this->table} SET payment_proof = :proof, updated_at = NOW() WHERE id = :id";
        return $this->db->query($sql, ['id' => $id, 'proof' => $proof]);
    }
    
    /**
     * Update Midtrans token
     * 
     * @param int $id Transaction ID
     * @param string $token Midtrans token
     * @param string $redirectUrl Redirect URL
     * @return bool
     */
    public function updateMidtransToken($id, $token, $redirectUrl = null) {
        $sql = "UPDATE {$this->table} SET midtrans_token = :token, midtrans_redirect_url = :redirect_url, updated_at = NOW() WHERE id = :id";
        return $this->db->query($sql, [
            'id' => $id,
            'token' => $token,
            'redirect_url' => $redirectUrl
        ]);
    }
    
    /**
     * Get transaction by code
     * 
     * @param string $code Transaction code
     * @return array|false
     */
    public function findByCode($code) {
        $sql = "SELECT t.*, u.name as user_name 
                FROM {$this->table} t 
                LEFT JOIN users u ON t.user_id = u.id 
                WHERE t.transaction_code = :code";
        
        return $this->db->query($sql, ['code' => $code])->fetch();
    }
    
    /**
     * Get transactions by guide ID
     * 
     * @param int $guideId Guide ID
     * @return array
     */
    public function getByGuideId($guideId) {
        $sql = "SELECT t.*, b.booking_code 
                FROM {$this->table} t 
                LEFT JOIN bookings b ON t.booking_id = b.id 
                WHERE t.guide_id = :guide_id 
                ORDER BY t.created_at DESC";
        
        return $this->db->query($sql, ['guide_id' => $guideId])->fetchAll();
    }
}
