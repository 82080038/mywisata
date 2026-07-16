<?php
/**
 * MyWisata Application - Cart Model
 * 
 * Handles multi-item shopping cart operations.
 * 
 * @package MyWisata
 * @version 1.0.0
 * @since 2026-07-16
 */

class Cart extends Model {
    
    /**
     * Table name
     */
    protected $table = 'cart_items';
    
    /**
     * Add item to cart
     * 
     * @param int $userId User ID
     * @param string $itemType Item type (tour_guide, ticket, hotel, restaurant)
     * @param int $itemId Item ID
     * @param int $quantity Quantity
     * @param array $options Additional options (JSON)
     * @return int Cart item ID
     */
    public function addItem($userId, $itemType, $itemId, $quantity = 1, $options = null) {
        $sql = "INSERT INTO {$this->table} 
                (user_id, item_type, item_id, quantity, options, created_at)
                VALUES (:user_id, :item_type, :item_id, :quantity, :options, NOW())
                ON DUPLICATE KEY UPDATE 
                quantity = quantity + :quantity,
                options = :options,
                updated_at = NOW()";
        
        $this->db->query($sql, [
            'user_id' => $userId,
            'item_type' => $itemType,
            'item_id' => $itemId,
            'quantity' => $quantity,
            'options' => $options ? json_encode($options) : null
        ]);
        
        return $this->db->lastInsertId();
    }
    
    /**
     * Get cart contents
     * 
     * @param int $userId User ID
     * @return array
     */
    public function getCart($userId) {
        $sql = "SELECT c.*,
                CASE c.item_type
                    WHEN 'tour_guide' THEN (SELECT name FROM tour_guides WHERE id = c.item_id)
                    WHEN 'ticket' THEN (SELECT title FROM tickets WHERE id = c.item_id)
                    WHEN 'hotel' THEN (SELECT name FROM hotels WHERE id = c.item_id)
                    WHEN 'restaurant' THEN (SELECT name FROM restaurants WHERE id = c.item_id)
                END as item_name,
                CASE c.item_type
                    WHEN 'tour_guide' THEN (SELECT hourly_rate FROM tour_guides WHERE id = c.item_id)
                    WHEN 'ticket' THEN (SELECT price FROM tickets WHERE id = c.item_id)
                    WHEN 'hotel' THEN (SELECT price_per_night FROM hotels WHERE id = c.item_id)
                    WHEN 'restaurant' THEN (SELECT avg_price FROM restaurants WHERE id = c.item_id)
                END as unit_price,
                CASE c.item_type
                    WHEN 'tour_guide' THEN (SELECT avatar FROM tour_guides WHERE id = c.item_id)
                    WHEN 'ticket' THEN (SELECT image FROM tickets WHERE id = c.item_id)
                    WHEN 'hotel' THEN (SELECT image FROM hotels WHERE id = c.item_id)
                    WHEN 'restaurant' THEN (SELECT image FROM restaurants WHERE id = c.item_id)
                END as item_image
                FROM {$this->table} c 
                WHERE c.user_id = :user_id 
                ORDER BY c.created_at DESC";
        
        return $this->db->query($sql, ['user_id' => $userId])->fetchAll();
    }
    
    /**
     * Get cart summary
     * 
     * @param int $userId User ID
     * @return array
     */
    public function getCartSummary($userId) {
        $cart = $this->getCart($userId);
        
        $totalItems = 0;
        $totalAmount = 0;
        $discountAmount = 0;
        
        foreach ($cart as $item) {
            $totalItems += $item['quantity'];
            $totalAmount += $item['unit_price'] * $item['quantity'];
        }
        
        // Get applied promo code
        $promoCode = $this->getAppliedPromoCode($userId);
        if ($promoCode) {
            $discountAmount = $promoCode['discount_amount'];
        }
        
        $finalAmount = $totalAmount - $discountAmount;
        
        return [
            'total_items' => $totalItems,
            'total_amount' => $totalAmount,
            'discount_amount' => $discountAmount,
            'final_amount' => $finalAmount,
            'promo_code' => $promoCode ? $promoCode['code'] : null
        ];
    }
    
    /**
     * Update cart item
     * 
     * @param int $cartItemId Cart item ID
     * @param int $userId User ID (for ownership check)
     * @param int $quantity New quantity
     * @param array $options Options
     * @return bool
     */
    public function updateItem($cartItemId, $userId, $quantity, $options = null) {
        $sql = "UPDATE {$this->table} 
                SET quantity = :quantity, 
                    options = :options,
                    updated_at = NOW() 
                WHERE id = :id AND user_id = :user_id";
        
        return $this->db->query($sql, [
            'id' => $cartItemId,
            'user_id' => $userId,
            'quantity' => $quantity,
            'options' => $options ? json_encode($options) : null
        ]);
    }
    
    /**
     * Remove item from cart
     * 
     * @param int $cartItemId Cart item ID
     * @param int $userId User ID (for ownership check)
     * @return bool
     */
    public function removeItem($cartItemId, $userId) {
        $sql = "DELETE FROM {$this->table} WHERE id = :id AND user_id = :user_id";
        return $this->db->query($sql, ['id' => $cartItemId, 'user_id' => $userId]);
    }
    
    /**
     * Clear cart
     * 
     * @param int $userId User ID
     * @return bool
     */
    public function clearCart($userId) {
        $sql = "DELETE FROM {$this->table} WHERE user_id = :user_id";
        return $this->db->query($sql, ['user_id' => $userId]);
    }
    
    /**
     * Apply promo code to cart
     * 
     * @param int $userId User ID
     * @param string $promoCode Promo code
     * @param float $discountAmount Discount amount
     * @return bool
     */
    public function applyPromoCode($userId, $promoCode, $discountAmount) {
        $sql = "INSERT INTO cart_promo_codes (user_id, promo_code, discount_amount, applied_at)
                VALUES (:user_id, :promo_code, :discount_amount, NOW())
                ON DUPLICATE KEY UPDATE 
                promo_code = :promo_code,
                discount_amount = :discount_amount,
                applied_at = NOW()";
        
        return $this->db->query($sql, [
            'user_id' => $userId,
            'promo_code' => $promoCode,
            'discount_amount' => $discountAmount
        ]);
    }
    
    /**
     * Remove promo code from cart
     * 
     * @param int $userId User ID
     * @return bool
     */
    public function removePromoCode($userId) {
        $sql = "DELETE FROM cart_promo_codes WHERE user_id = :user_id";
        return $this->db->query($sql, ['user_id' => $userId]);
    }
    
    /**
     * Get applied promo code
     * 
     * @param int $userId User ID
     * @return array|false
     */
    public function getAppliedPromoCode($userId) {
        $sql = "SELECT * FROM cart_promo_codes WHERE user_id = :user_id";
        return $this->db->query($sql, ['user_id' => $userId])->fetch();
    }
    
    /**
     * Get cart item by ID
     * 
     * @param int $cartItemId Cart item ID
     * @param int $userId User ID
     * @return array|false
     */
    public function getCartItem($cartItemId, $userId) {
        $sql = "SELECT * FROM {$this->table} WHERE id = :id AND user_id = :user_id";
        return $this->db->query($sql, ['id' => $cartItemId, 'user_id' => $userId])->fetch();
    }
    
    /**
     * Check if item exists in cart
     * 
     * @param int $userId User ID
     * @param string $itemType Item type
     * @param int $itemId Item ID
     * @return bool
     */
    public function itemExists($userId, $itemType, $itemId) {
        $sql = "SELECT COUNT(*) as count FROM {$this->table} 
                WHERE user_id = :user_id AND item_type = :item_type AND item_id = :item_id";
        
        $result = $this->db->query($sql, [
            'user_id' => $userId,
            'item_type' => $itemType,
            'item_id' => $itemId
        ])->fetch();
        
        return $result['count'] > 0;
    }
}
