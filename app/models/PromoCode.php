<?php
/**
 * MyWisata Application - Promo Code Model
 * 
 * Handles promo code and voucher related database operations.
 * 
 * @package MyWisata
 * @version 1.0.0
 * @since 2026-07-16
 */

class PromoCode extends Model {
    
    /**
     * Table name
     */
    protected $table = 'promo_codes';
    
    /**
     * Create a promo code
     * 
     * @param array $data Promo code data
     * @return int Promo code ID
     */
    public function create($data) {
        $sql = "INSERT INTO {$this->table} 
                (code, description, discount_type, discount_value, max_discount, 
                 min_purchase, usage_limit, usage_count, valid_from, valid_until, 
                 applicable_to, is_active, created_at)
                VALUES 
                (:code, :description, :discount_type, :discount_value, :max_discount,
                 :min_purchase, :usage_limit, :usage_count, :valid_from, :valid_until,
                 :applicable_to, :is_active, NOW())";
        
        $this->db->query($sql, $data);
        return $this->db->lastInsertId();
    }
    
    /**
     * Get promo code by ID
     * 
     * @param int $id Promo code ID
     * @return array|false
     */
    public function findById($id) {
        $sql = "SELECT * FROM {$this->table} WHERE id = :id";
        return $this->db->query($sql, ['id' => $id])->fetch();
    }
    
    /**
     * Get promo code by code
     * 
     * @param string $code Promo code
     * @return array|false
     */
    public function findByCode($code) {
        $sql = "SELECT * FROM {$this->table} WHERE code = :code";
        return $this->db->query($sql, ['code' => $code])->fetch();
    }
    
    /**
     * Get all promo codes with optional status filter
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
        
        if ($status === 'active') {
            $where = "is_active = 1 AND (valid_from <= NOW() OR valid_from IS NULL) AND (valid_until >= NOW() OR valid_until IS NULL)";
        } elseif ($status === 'inactive') {
            $where = "is_active = 0";
        } elseif ($status === 'expired') {
            $where = "valid_until < NOW()";
        }
        
        $sql = "SELECT * FROM {$this->table} WHERE {$where} ORDER BY created_at DESC LIMIT :limit OFFSET :offset";
        $params['limit'] = $limit;
        $params['offset'] = $offset;
        
        return $this->db->query($sql, $params)->fetchAll();
    }
    
    /**
     * Count promo codes by status
     * 
     * @param string $status Optional status filter
     * @return int
     */
    public function countByStatus($status = null) {
        $where = "1=1";
        $params = [];
        
        if ($status === 'active') {
            $where = "is_active = 1 AND (valid_from <= NOW() OR valid_from IS NULL) AND (valid_until >= NOW() OR valid_until IS NULL)";
        } elseif ($status === 'inactive') {
            $where = "is_active = 0";
        } elseif ($status === 'expired') {
            $where = "valid_until < NOW()";
        }
        
        $sql = "SELECT COUNT(*) as count FROM {$this->table} WHERE {$where}";
        $result = $this->db->query($sql, $params)->fetch();
        
        return $result['count'];
    }
    
    /**
     * Validate promo code
     * 
     * @param string $code Promo code
     * @param float $totalAmount Total purchase amount
     * @param int $userId User ID
     * @return array|false
     */
    public function validateCode($code, $totalAmount, $userId = null) {
        $promo = $this->findByCode($code);
        
        if (!$promo) {
            return false;
        }
        
        // Check if active
        if (!$promo['is_active']) {
            return false;
        }
        
        // Check validity period
        $now = date('Y-m-d H:i:s');
        if ($promo['valid_from'] && $promo['valid_from'] > $now) {
            return false;
        }
        if ($promo['valid_until'] && $promo['valid_until'] < $now) {
            return false;
        }
        
        // Check usage limit
        if ($promo['usage_limit'] && $promo['usage_count'] >= $promo['usage_limit']) {
            return false;
        }
        
        // Check minimum purchase
        if ($promo['min_purchase'] && $totalAmount < $promo['min_purchase']) {
            return false;
        }
        
        // Check user-specific usage if user ID provided
        if ($userId) {
            $userUsage = $this->getUserUsageCount($code, $userId);
            if ($userUsage >= 1) { // Limit to 1 use per user by default
                return false;
            }
        }
        
        return $promo;
    }
    
    /**
     * Update promo code
     * 
     * @param int $id Promo code ID
     * @param array $data Data to update
     * @return bool
     */
    public function update($id, $data) {
        $sql = "UPDATE {$this->table} 
                SET description = :description,
                    discount_type = :discount_type,
                    discount_value = :discount_value,
                    max_discount = :max_discount,
                    min_purchase = :min_purchase,
                    usage_limit = :usage_limit,
                    valid_from = :valid_from,
                    valid_until = :valid_until,
                    applicable_to = :applicable_to,
                    is_active = :is_active,
                    updated_at = NOW()
                WHERE id = :id";
        
        $data['id'] = $id;
        return $this->db->query($sql, $data);
    }
    
    /**
     * Delete promo code
     * 
     * @param int $id Promo code ID
     * @return bool
     */
    public function delete($id) {
        $sql = "DELETE FROM {$this->table} WHERE id = :id";
        return $this->db->query($sql, ['id' => $id]);
    }
    
    /**
     * Record promo code usage
     * 
     * @param string $code Promo code
     * @param int $userId User ID
     * @param int $orderId Order ID
     * @param float $discountAmount Discount amount
     * @return bool
     */
    public function recordUsage($code, $userId, $orderId, $discountAmount) {
        try {
            $this->db->beginTransaction();
            
            // Increment usage count
            $sql = "UPDATE {$this->table} 
                    SET usage_count = usage_count + 1, updated_at = NOW() 
                    WHERE code = :code";
            $this->db->query($sql, ['code' => $code]);
            
            // Record usage
            $sql = "INSERT INTO promo_code_usage 
                    (promo_code, user_id, order_id, discount_amount, used_at)
                    VALUES (:promo_code, :user_id, :order_id, :discount_amount, NOW())";
            $this->db->query($sql, [
                'promo_code' => $code,
                'user_id' => $userId,
                'order_id' => $orderId,
                'discount_amount' => $discountAmount
            ]);
            
            $this->db->commit();
            return true;
        } catch (Exception $e) {
            $this->db->rollBack();
            Logger::error('Failed to record promo code usage', [
                'code' => $code,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }
    
    /**
     * Get user usage count for a promo code
     * 
     * @param string $code Promo code
     * @param int $userId User ID
     * @return int
     */
    public function getUserUsageCount($code, $userId) {
        $sql = "SELECT COUNT(*) as count 
                FROM promo_code_usage 
                WHERE promo_code = :code AND user_id = :user_id";
        
        $result = $this->db->query($sql, [
            'code' => $code,
            'user_id' => $userId
        ])->fetch();
        
        return $result['count'];
    }
    
    /**
     * Get promo code statistics
     * 
     * @return array
     */
    public function getStatistics() {
        $sql = "SELECT 
                COUNT(*) as total_promos,
                SUM(CASE WHEN is_active = 1 AND (valid_from <= NOW() OR valid_from IS NULL) AND (valid_until >= NOW() OR valid_until IS NULL) THEN 1 ELSE 0 END) as active_promos,
                SUM(CASE WHEN valid_until < NOW() THEN 1 ELSE 0 END) as expired_promos,
                SUM(usage_count) as total_usage,
                SUM(discount_amount) as total_discount_given
                FROM {$this->table}";
        
        return $this->db->query($sql)->fetch();
    }
    
    /**
     * Get promo code usage history
     * 
     * @param int $promoId Promo code ID
     * @param int $page Page number
     * @param int $limit Items per page
     * @return array
     */
    public function getUsageHistory($promoId, $page = 1, $limit = 20) {
        $offset = ($page - 1) * $limit;
        
        $sql = "SELECT pcu.*, u.name as user_name, u.email as user_email
                FROM promo_code_usage pcu
                LEFT JOIN users u ON pcu.user_id = u.id
                WHERE pcu.promo_code = (SELECT code FROM {$this->table} WHERE id = :id)
                ORDER BY pcu.used_at DESC
                LIMIT :limit OFFSET :offset";
        
        return $this->db->query($sql, ['id' => $promoId, 'limit' => $limit, 'offset' => $offset])->fetchAll();
    }
}
