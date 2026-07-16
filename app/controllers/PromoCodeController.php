<?php
/**
 * MyWisata Application - Promo Code Controller
 * 
 * Handles promo code and voucher management.
 * 
 * @package MyWisata
 * @version 1.0.0
 * @since 2026-07-16
 */

class PromoCodeController extends Controller {
    
    private $promoCodeModel;
    
    public function __construct() {
        parent::__construct();
        $this->promoCodeModel = $this->model('PromoCode');
    }
    
    /**
     * Validate promo code
     */
    public function validate() {
        if (!$this->isAjax()) {
            $this->redirect('home');
        }
        
        $code = $this->post('code');
        $totalAmount = $this->post('total_amount');
        $userId = Session::get('user_id');
        
        if (empty($code)) {
            $this->json(['status' => 'error', 'message' => 'Kode promo wajib diisi'], 400);
        }
        
        $promo = $this->promoCodeModel->validateCode($code, $totalAmount, $userId);
        
        if ($promo) {
            $this->json([
                'status' => 'success',
                'data' => [
                    'valid' => true,
                    'discount_type' => $promo['discount_type'],
                    'discount_value' => $promo['discount_value'],
                    'discount_amount' => $this->calculateDiscount($totalAmount, $promo),
                    'max_discount' => $promo['max_discount'],
                    'description' => $promo['description']
                ]
            ]);
        } else {
            $this->json(['status' => 'error', 'message' => 'Kode promo tidak valid atau sudah kadaluarsa'], 400);
        }
    }
    
    /**
     * Calculate discount amount
     */
    private function calculateDiscount($totalAmount, $promo) {
        $discount = 0;
        
        if ($promo['discount_type'] === 'percentage') {
            $discount = $totalAmount * ($promo['discount_value'] / 100);
        } else {
            $discount = $promo['discount_value'];
        }
        
        // Apply max discount limit
        if ($promo['max_discount'] && $discount > $promo['max_discount']) {
            $discount = $promo['max_discount'];
        }
        
        // Discount cannot exceed total amount
        if ($discount > $totalAmount) {
            $discount = $totalAmount;
        }
        
        return $discount;
    }
    
    /**
     * Admin: List all promo codes
     */
    public function adminIndex() {
        Middleware::requireAuth();
        Middleware::requireRole('admin');
        
        $status = $this->get('status', 'all');
        $page = $this->get('page', 1);
        $limit = $this->get('limit', 20);
        
        $promos = $this->promoCodeModel->getAll($status, $page, $limit);
        $total = $this->promoCodeModel->countByStatus($status);
        
        $data = [
            'title' => 'Kelola Kode Promo - Admin',
            'promos' => $promos,
            'total' => $total,
            'status' => $status,
            'page' => $page,
            'limit' => $limit,
            'csrf_token' => Middleware::csrfToken()
        ];
        
        $this->view('admin/promo-codes', $data);
    }
    
    /**
     * Admin: Create promo code
     */
    public function adminCreate() {
        Middleware::requireAuth();
        Middleware::requireRole('admin');
        
        if (!$this->isAjax()) {
            $this->redirect('admin/promo-codes');
        }
        
        // Verify CSRF
        if (!$this->validateCsrf()) {
            $this->json(['status' => 'error', 'message' => 'CSRF token mismatch'], 419);
        }
        
        $data = [
            'code' => strtoupper($this->post('code')),
            'description' => $this->post('description'),
            'discount_type' => $this->post('discount_type'),
            'discount_value' => $this->post('discount_value'),
            'max_discount' => $this->post('max_discount') ?: null,
            'min_purchase' => $this->post('min_purchase') ?: 0,
            'usage_limit' => $this->post('usage_limit') ?: null,
            'usage_count' => 0,
            'valid_from' => $this->post('valid_from'),
            'valid_until' => $this->post('valid_until'),
            'applicable_to' => $this->post('applicable_to', 'all'),
            'is_active' => 1
        ];
        
        // Validate input
        if (empty($data['code'])) {
            $this->json(['status' => 'error', 'message' => 'Kode promo wajib diisi'], 400);
        }
        
        if (!in_array($data['discount_type'], ['percentage', 'fixed'])) {
            $this->json(['status' => 'error', 'message' => 'Tipe diskon tidak valid'], 400);
        }
        
        if ($data['discount_value'] <= 0) {
            $this->json(['status' => 'error', 'message' => 'Nilai diskon harus lebih dari 0'], 400);
        }
        
        if ($data['discount_type'] === 'percentage' && $data['discount_value'] > 100) {
            $this->json(['status' => 'error', 'message' => 'Persentase diskon maksimal 100%'], 400);
        }
        
        $promoId = $this->promoCodeModel->create($data);
        
        if ($promoId) {
            Logger::audit('CREATE_PROMO_CODE', 'promo_codes', "Created promo code: {$data['code']}", [], $data);
            
            $this->json(['status' => 'success', 'message' => 'Kode promo berhasil dibuat', 'promo_id' => $promoId]);
        } else {
            $this->json(['status' => 'error', 'message' => 'Gagal membuat kode promo'], 500);
        }
    }
    
    /**
     * Admin: Update promo code
     */
    public function adminUpdate() {
        Middleware::requireAuth();
        Middleware::requireRole('admin');
        
        if (!$this->isAjax()) {
            $this->redirect('admin/promo-codes');
        }
        
        // Verify CSRF
        if (!$this->validateCsrf()) {
            $this->json(['status' => 'error', 'message' => 'CSRF token mismatch'], 419);
        }
        
        $promoId = $this->post('promo_id');
        
        $data = [
            'description' => $this->post('description'),
            'discount_type' => $this->post('discount_type'),
            'discount_value' => $this->post('discount_value'),
            'max_discount' => $this->post('max_discount') ?: null,
            'min_purchase' => $this->post('min_purchase') ?: 0,
            'usage_limit' => $this->post('usage_limit') ?: null,
            'valid_from' => $this->post('valid_from'),
            'valid_until' => $this->post('valid_until'),
            'applicable_to' => $this->post('applicable_to', 'all'),
            'is_active' => $this->post('is_active', 1)
        ];
        
        $updated = $this->promoCodeModel->update($promoId, $data);
        
        if ($updated) {
            Logger::audit('UPDATE_PROMO_CODE', 'promo_codes', "Updated promo code ID: {$promoId}", [], $data);
            
            $this->json(['status' => 'success', 'message' => 'Kode promo berhasil diperbarui']);
        } else {
            $this->json(['status' => 'error', 'message' => 'Gagal memperbarui kode promo'], 500);
        }
    }
    
    /**
     * Admin: Delete promo code
     */
    public function adminDelete() {
        Middleware::requireAuth();
        Middleware::requireRole('admin');
        
        if (!$this->isAjax()) {
            $this->redirect('admin/promo-codes');
        }
        
        // Verify CSRF
        if (!$this->validateCsrf()) {
            $this->json(['status' => 'error', 'message' => 'CSRF token mismatch'], 419);
        }
        
        $promoId = $this->post('promo_id');
        
        $deleted = $this->promoCodeModel->delete($promoId);
        
        if ($deleted) {
            Logger::audit('DELETE_PROMO_CODE', 'promo_codes', "Deleted promo code ID: {$promoId}", [], []);
            
            $this->json(['status' => 'success', 'message' => 'Kode promo berhasil dihapus']);
        } else {
            $this->json(['status' => 'error', 'message' => 'Gagal menghapus kode promo'], 500);
        }
    }
    
    /**
     * Admin: Get promo code statistics
     */
    public function adminStats() {
        Middleware::requireAuth();
        Middleware::requireRole('admin');
        
        if (!$this->isAjax()) {
            $this->redirect('admin/promo-codes');
        }
        
        $stats = $this->promoCodeModel->getStatistics();
        
        $this->json([
            'status' => 'success',
            'data' => $stats
        ]);
    }
    
    /**
     * Record promo code usage
     */
    public function recordUsage() {
        if (!$this->isAjax()) {
            $this->redirect('home');
        }
        
        // Verify CSRF
        if (!$this->validateCsrf()) {
            $this->json(['status' => 'error', 'message' => 'CSRF token mismatch'], 419);
        }
        
        $code = $this->post('code');
        $userId = Session::get('user_id');
        $orderId = $this->post('order_id');
        $discountAmount = $this->post('discount_amount');
        
        $recorded = $this->promoCodeModel->recordUsage($code, $userId, $orderId, $discountAmount);
        
        if ($recorded) {
            Logger::audit('USE_PROMO_CODE', 'promo_code_usage', "Used promo code: {$code}", [], [
                'user_id' => $userId,
                'order_id' => $orderId,
                'discount_amount' => $discountAmount
            ]);
            
            $this->json(['status' => 'success', 'message' => 'Penggunaan promo code berhasil dicatat']);
        } else {
            $this->json(['status' => 'error', 'message' => 'Gagal mencatat penggunaan promo code'], 500);
        }
    }
}
