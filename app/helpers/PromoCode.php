<?php
/**
 * MyWisata Application - Promo Code Helper
 * 
 * Handles promo code generation, validation, and application.
 * 
 * @package MyWisata
 * @version 1.0.0
 * @since 2026-07-15
 */

class PromoCode {
    
    /**
     * Generate random promo code
     * 
     * @param int $length Code length
     * @return string Generated code
     */
    public static function generate($length = null) {
        $length = $length ?? PROMO_CODE_LENGTH;
        $characters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
        $code = '';
        
        for ($i = 0; $i < $length; $i++) {
            $code .= $characters[rand(0, strlen($characters) - 1)];
        }
        
        return $code;
    }
    
    /**
     * Validate promo code
     * 
     * @param string $code Promo code
     * @param int $userId User ID (optional)
     * @param string $type Type (booking, ticket, hotel, restaurant, event)
     * @return array Validation result
     */
    public static function validate($code, $userId = null, $type = null) {
        if (!PROMO_ENABLED) {
            return [
                'valid' => false,
                'message' => 'Promo codes are currently disabled'
            ];
        }
        
        $promoModel = new PromoCodeModel();
        $promo = $promoModel->findByCode($code);
        
        if (!$promo) {
            return [
                'valid' => false,
                'message' => 'Invalid promo code'
            ];
        }
        
        // Check if promo is active
        if (!$promo['is_active']) {
            return [
                'valid' => false,
                'message' => 'This promo code is not active'
            ];
        }
        
        // Check dates
        $now = date('Y-m-d H:i:s');
        if ($promo['start_date'] > $now) {
            return [
                'valid' => false,
                'message' => 'This promo code is not yet valid'
            ];
        }
        
        if ($promo['end_date'] < $now) {
            return [
                'valid' => false,
                'message' => 'This promo code has expired'
            ];
        }
        
        // Check usage limit
        if ($promo['max_uses'] > 0 && $promo['used_count'] >= $promo['max_uses']) {
            return [
                'valid' => false,
                'message' => 'This promo code has reached its usage limit'
            ];
        }
        
        // Check user limit
        if ($userId && $promo['max_uses_per_user'] > 0) {
            $userUsage = $promoModel->getUserUsage($promo['id'], $userId);
            if ($userUsage >= $promo['max_uses_per_user']) {
                return [
                    'valid' => false,
                    'message' => 'You have reached the maximum usage limit for this promo code'
                ];
            }
        }
        
        // Check applicable types
        if ($type && !empty($promo['applicable_types'])) {
            $applicableTypes = json_decode($promo['applicable_types'], true);
            if (!in_array($type, $applicableTypes)) {
                return [
                    'valid' => false,
                    'message' => 'This promo code is not applicable for this type of booking'
                ];
            }
        }
        
        // Check minimum order amount
        // This would be checked during application with the actual amount
        
        return [
            'valid' => true,
            'promo' => $promo,
            'message' => 'Promo code is valid'
        ];
    }
    
    /**
     * Calculate discount amount
     * 
     * @param array $promo Promo data
     * @param float $amount Original amount
     * @return float Discount amount
     */
    public static function calculateDiscount($promo, $amount) {
        if ($promo['discount_type'] === 'percentage') {
            $discount = ($amount * $promo['discount_value']) / 100;
            
            // Apply maximum discount if set
            if ($promo['max_discount_amount'] > 0) {
                $discount = min($discount, $promo['max_discount_amount']);
            }
        } else {
            // Fixed amount discount
            $discount = $promo['discount_value'];
        }
        
        // Ensure discount doesn't exceed amount
        $discount = min($discount, $amount);
        
        return $discount;
    }
    
    /**
     * Apply promo code to transaction
     * 
     * @param string $code Promo code
     * @param int $userId User ID
     * @param float $amount Original amount
     * @param string $type Type
     * @return array Result
     */
    public static function apply($code, $userId, $amount, $type = null) {
        $validation = self::validate($code, $userId, $type);
        
        if (!$validation['valid']) {
            return [
                'success' => false,
                'message' => $validation['message'],
                'discount' => 0,
                'final_amount' => $amount
            ];
        }
        
        $promo = $validation['promo'];
        
        // Check minimum order amount
        if ($promo['min_order_amount'] > 0 && $amount < $promo['min_order_amount']) {
            return [
                'success' => false,
                'message' => 'Minimum order amount of ' . number_format($promo['min_order_amount']) . ' not met',
                'discount' => 0,
                'final_amount' => $amount
            ];
        }
        
        $discount = self::calculateDiscount($promo, $amount);
        $finalAmount = $amount - $discount;
        
        return [
            'success' => true,
            'message' => 'Promo code applied successfully',
            'discount' => $discount,
            'final_amount' => $finalAmount,
            'promo_id' => $promo['id']
        ];
    }
    
    /**
     * Record promo code usage
     * 
     * @param int $promoId Promo ID
     * @param int $userId User ID
     * @param int $transactionId Transaction ID
     * @param float $discountAmount Discount amount
     * @return bool Success
     */
    public static function recordUsage($promoId, $userId, $transactionId, $discountAmount) {
        $promoModel = new PromoCodeModel();
        
        // Increment usage count
        $promoModel->incrementUsage($promoId);
        
        // Record usage log
        return $promoModel->recordUsage($promoId, $userId, $transactionId, $discountAmount);
    }
    
    /**
     * Get active promo codes
     * 
     * @param string $type Optional type filter
     * @return array Active promo codes
     */
    public static function getActivePromos($type = null) {
        $promoModel = new PromoCodeModel();
        return $promoModel->getActive($type);
    }
}
