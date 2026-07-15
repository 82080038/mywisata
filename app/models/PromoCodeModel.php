<?php
/**
 * MyWisata Application - Promo Code Model
 * 
 * Handles promo code database operations.
 * 
 * @package MyWisata
 * @version 1.0.0
 * @since 2026-07-15
 */

class PromoCodeModel extends Model {
    
    /**
     * Table name
     */
    protected $table = 'promo_codes';
    
    /**
     * Find promo code by code
     * 
     * @param string $code Promo code
     * @return array|false
     */
    public function findByCode($code) {
        $sql = "SELECT * FROM {$this->table} WHERE code = :code";
        return $this->db->query($sql, ['code' => $code])->fetch();
    }
    
    /**
     * Find promo code by ID
     * 
     * @param int $id Promo ID
     * @return array|false
     */
    public function findById($id) {
        $sql = "SELECT * FROM {$this->table} WHERE id = :id";
        return $this->db->query($sql, ['id' => $id])->fetch();
    }
    
    /**
     * Get all active promo codes
     * 
     * @param string $type Optional type filter
     * @return array
     */
    public function getActive($type = null) {
        $now = date('Y-m-d H:i:s');
        $where = "is_active = 1 AND start_date <= :now AND end_date >= :now";
        $params = ['now' => $now];
        
        if ($type) {
            $where .= " AND (applicable_types IS NULL OR applicable_types LIKE :type)";
            $params['type'] = "%\"$type\"%";
        }
        
        $sql = "SELECT * FROM {$this->table} WHERE {$where} ORDER BY created_at DESC";
        return $this->db->query($sql, $params)->fetchAll();
    }
    
    /**
     * Get all promo codes
     * 
     * @param array $filters Optional filters
     * @return array
     */
    public function getAll($filters = []) {
        $where = ['1=1'];
        $params = [];
        
        if (!empty($filters['is_active'])) {
            $where[] = "is_active = :is_active";
            $params['is_active'] = $filters['is_active'];
        }
        
        if (!empty($filters['search'])) {
            $where[] = "(code LIKE :search OR name LIKE :search)";
            $params['search'] = "%{$filters['search']}%";
        }
        
        $whereClause = implode(' AND ', $where);
        
        $sql = "SELECT * FROM {$this->table} WHERE {$whereClause} ORDER BY created_at DESC";
        return $this->db->query($sql, $params)->fetchAll();
    }
    
    /**
     * Create promo code
     * 
     * @param array $data Promo code data
     * @return int Promo ID
     */
    public function create($data) {
        $sql = "INSERT INTO {$this->table} 
                (code, name, description, discount_type, discount_value, 
                 max_discount_amount, min_order_amount, max_uses, max_uses_per_user,
                 start_date, end_date, applicable_types, is_active, created_at, updated_at)
                VALUES 
                (:code, :name, :description, :discount_type, :discount_value,
                 :max_discount_amount, :min_order_amount, :max_uses, :max_uses_per_user,
                 :start_date, :end_date, :applicable_types, :is_active, NOW(), NOW())";
        
        $this->db->query($sql, $data);
        return $this->db->lastInsertId();
    }
    
    /**
     * Update promo code
     * 
     * @param int $id Promo ID
     * @param array $data Promo code data
     * @return bool
     */
    public function update($id, $data) {
        $data['id'] = $id;
        $sql = "UPDATE {$this->table} 
                SET name = :name, description = :description, 
                    discount_type = :discount_type, discount_value = :discount_value,
                    max_discount_amount = :max_discount_amount, min_order_amount = :min_order_amount,
                    max_uses = :max_uses, max_uses_per_user = :max_uses_per_user,
                    start_date = :start_date, end_date = :end_date, 
                    applicable_types = :applicable_types, is_active = :is_active, updated_at = NOW()
                WHERE id = :id";
        
        return $this->db->query($sql, $data);
    }
    
    /**
     * Delete promo code
     * 
     * @param int $id Promo ID
     * @return bool
     */
    public function delete($id) {
        $sql = "DELETE FROM {$this->table} WHERE id = :id";
        return $this->db->query($sql, ['id' => $id]);
    }
    
    /**
     * Increment usage count
     * 
     * @param int $id Promo ID
     * @return bool
     */
    public function incrementUsage($id) {
        $sql = "UPDATE {$this->table} 
                SET used_count = used_count + 1, updated_at = NOW() 
                WHERE id = :id";
        
        return $this->db->query($sql, ['id' => $id]);
    }
    
    /**
     * Get user usage count for promo
     * 
     * @param int $promoId Promo ID
     * @param int $userId User ID
     * @return int Usage count
     */
    public function getUserUsage($promoId, $userId) {
        $sql = "SELECT COUNT(*) as count 
                FROM promo_code_usage 
                WHERE promo_id = :promo_id AND user_id = :user_id";
        
        $result = $this->db->query($sql, [
            'promo_id' => $promoId,
            'user_id' => $userId
        ])->fetch();
        
        return $result['count'] ?? 0;
    }
    
    /**
     * Record promo code usage
     * 
     * @param int $promoId Promo ID
     * @param int $userId User ID
     * @param int $transactionId Transaction ID
     * @param float $discountAmount Discount amount
     * @return bool
     */
    public function recordUsage($promoId, $userId, $transactionId, $discountAmount) {
        $sql = "INSERT INTO promo_code_usage 
                (promo_id, user_id, transaction_id, discount_amount, used_at)
                VALUES (:promo_id, :user_id, :transaction_id, :discount_amount, NOW())";
        
        return $this->db->query($sql, [
            'promo_id' => $promoId,
            'user_id' => $userId,
            'transaction_id' => $transactionId,
            'discount_amount' => $discountAmount
        ]);
    }
    
    /**
     * Get promo usage statistics
     * 
     * @param int $promoId Promo ID
     * @return array Statistics
     */
    public function getUsageStats($promoId) {
        $sql = "SELECT 
                    COUNT(*) as total_uses,
                    COUNT(DISTINCT user_id) as unique_users,
                    SUM(discount_amount) as total_discount
                FROM promo_code_usage 
                WHERE promo_id = :promo_id";
        
        return $this->db->query($sql, ['promo_id' => $promoId])->fetch();
    }
}
