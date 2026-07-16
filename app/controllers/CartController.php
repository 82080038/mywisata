<?php
/**
 * MyWisata Application - Cart Controller
 * 
 * Handles multi-item shopping cart functionality.
 * 
 * @package MyWisata
 * @version 1.0.0
 * @since 2026-07-16
 */

class CartController extends Controller {
    
    private $cartModel;
    
    public function __construct() {
        parent::__construct();
        $this->cartModel = $this->model('Cart');
    }
    
    /**
     * Get cart contents
     */
    public function index() {
        Middleware::requireAuth();
        
        $userId = Session::get('user_id');
        $cart = $this->cartModel->getCart($userId);
        $summary = $this->cartModel->getCartSummary($userId);
        
        $data = [
            'title' => 'Keranjang Belanja - MyWisata',
            'cart' => $cart,
            'summary' => $summary,
            'csrf_token' => Middleware::csrfToken()
        ];
        
        $this->view('cart/index', $data);
    }
    
    /**
     * Add item to cart
     */
    public function add() {
        Middleware::requireAuth();
        
        if (!$this->isAjax()) {
            $this->redirect('home');
        }
        
        // Verify CSRF
        if (!$this->validateCsrf()) {
            $this->json(['status' => 'error', 'message' => 'CSRF token mismatch'], 419);
        }
        
        $userId = Session::get('user_id');
        
        $itemType = $this->post('item_type');
        $itemId = $this->post('item_id');
        $quantity = $this->post('quantity', 1);
        $options = $this->post('options', null);
        
        // Validate input
        if (empty($itemType) || empty($itemId)) {
            $this->json(['status' => 'error', 'message' => 'Data tidak valid'], 400);
        }
        
        if (!in_array($itemType, ['tour_guide', 'ticket', 'hotel', 'restaurant'])) {
            $this->json(['status' => 'error', 'message' => 'Tipe item tidak valid'], 400);
        }
        
        if ($quantity < 1) {
            $this->json(['status' => 'error', 'message' => 'Quantity minimal 1'], 400);
        }
        
        $cartItemId = $this->cartModel->addItem($userId, $itemType, $itemId, $quantity, $options);
        
        if ($cartItemId) {
            Logger::audit('ADD_CART_ITEM', 'cart_items', "Added item to cart: {$itemType} ID: {$itemId}", [], [
                'quantity' => $quantity,
                'options' => $options
            ]);
            
            $summary = $this->cartModel->getCartSummary($userId);
            
            $this->json([
                'status' => 'success',
                'message' => 'Item berhasil ditambahkan ke keranjang',
                'cart_item_id' => $cartItemId,
                'cart_count' => $summary['total_items']
            ]);
        } else {
            $this->json(['status' => 'error', 'message' => 'Gagal menambahkan item ke keranjang'], 500);
        }
    }
    
    /**
     * Update cart item
     */
    public function update() {
        Middleware::requireAuth();
        
        if (!$this->isAjax()) {
            $this->redirect('home');
        }
        
        // Verify CSRF
        if (!$this->validateCsrf()) {
            $this->json(['status' => 'error', 'message' => 'CSRF token mismatch'], 419);
        }
        
        $userId = Session::get('user_id');
        $cartItemId = $this->post('cart_item_id');
        $quantity = $this->post('quantity');
        $options = $this->post('options', null);
        
        if ($quantity < 1) {
            $this->json(['status' => 'error', 'message' => 'Quantity minimal 1'], 400);
        }
        
        $updated = $this->cartModel->updateItem($cartItemId, $userId, $quantity, $options);
        
        if ($updated) {
            Logger::audit('UPDATE_CART_ITEM', 'cart_items', "Updated cart item ID: {$cartItemId}", [], [
                'quantity' => $quantity
            ]);
            
            $summary = $this->cartModel->getCartSummary($userId);
            
            $this->json([
                'status' => 'success',
                'message' => 'Item berhasil diperbarui',
                'cart_summary' => $summary
            ]);
        } else {
            $this->json(['status' => 'error', 'message' => 'Gagal memperbarui item'], 500);
        }
    }
    
    /**
     * Remove item from cart
     */
    public function remove() {
        Middleware::requireAuth();
        
        if (!$this->isAjax()) {
            $this->redirect('home');
        }
        
        // Verify CSRF
        if (!$this->validateCsrf()) {
            $this->json(['status' => 'error', 'message' => 'CSRF token mismatch'], 419);
        }
        
        $userId = Session::get('user_id');
        $cartItemId = $this->post('cart_item_id');
        
        $removed = $this->cartModel->removeItem($cartItemId, $userId);
        
        if ($removed) {
            Logger::audit('REMOVE_CART_ITEM', 'cart_items', "Removed cart item ID: {$cartItemId}", [], []);
            
            $summary = $this->cartModel->getCartSummary($userId);
            
            $this->json([
                'status' => 'success',
                'message' => 'Item berhasil dihapus dari keranjang',
                'cart_summary' => $summary
            ]);
        } else {
            $this->json(['status' => 'error', 'message' => 'Gagal menghapus item'], 500);
        }
    }
    
    /**
     * Clear cart
     */
    public function clear() {
        Middleware::requireAuth();
        
        if (!$this->isAjax()) {
            $this->redirect('home');
        }
        
        // Verify CSRF
        if (!$this->validateCsrf()) {
            $this->json(['status' => 'error', 'message' => 'CSRF token mismatch'], 419);
        }
        
        $userId = Session::get('user_id');
        
        $cleared = $this->cartModel->clearCart($userId);
        
        if ($cleared) {
            Logger::audit('CLEAR_CART', 'cart_items', "Cleared cart for user ID: {$userId}", [], []);
            
            $this->json(['status' => 'success', 'message' => 'Keranjang berhasil dikosongkan']);
        } else {
            $this->json(['status' => 'error', 'message' => 'Gagal mengosongkan keranjang'], 500);
        }
    }
    
    /**
     * Apply promo code to cart
     */
    public function applyPromo() {
        Middleware::requireAuth();
        
        if (!$this->isAjax()) {
            $this->redirect('home');
        }
        
        // Verify CSRF
        if (!$this->validateCsrf()) {
            $this->json(['status' => 'error', 'message' => 'CSRF token mismatch'], 419);
        }
        
        $userId = Session::get('user_id');
        $promoCode = $this->post('promo_code');
        
        $summary = $this->cartModel->getCartSummary($userId);
        
        if ($summary['total_amount'] == 0) {
            $this->json(['status' => 'error', 'message' => 'Keranjang kosong'], 400);
        }
        
        $promoCodeModel = $this->model('PromoCode');
        $promo = $promoCodeModel->validateCode($promoCode, $summary['total_amount'], $userId);
        
        if ($promo) {
            $discountAmount = $this->calculateDiscount($summary['total_amount'], $promo);
            
            $this->cartModel->applyPromoCode($userId, $promoCode, $discountAmount);
            
            $updatedSummary = $this->cartModel->getCartSummary($userId);
            
            $this->json([
                'status' => 'success',
                'message' => 'Kode promo berhasil diterapkan',
                'cart_summary' => $updatedSummary
            ]);
        } else {
            $this->json(['status' => 'error', 'message' => 'Kode promo tidak valid'], 400);
        }
    }
    
    /**
     * Remove promo code from cart
     */
    public function removePromo() {
        Middleware::requireAuth();
        
        if (!$this->isAjax()) {
            $this->redirect('home');
        }
        
        // Verify CSRF
        if (!$this->validateCsrf()) {
            $this->json(['status' => 'error', 'message' => 'CSRF token mismatch'], 419);
        }
        
        $userId = Session::get('user_id');
        
        $this->cartModel->removePromoCode($userId);
        
        $summary = $this->cartModel->getCartSummary($userId);
        
        $this->json([
            'status' => 'success',
            'message' => 'Kode promo berhasil dihapus',
            'cart_summary' => $summary
        ]);
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
        
        if ($promo['max_discount'] && $discount > $promo['max_discount']) {
            $discount = $promo['max_discount'];
        }
        
        if ($discount > $totalAmount) {
            $discount = $totalAmount;
        }
        
        return $discount;
    }
    
    /**
     * Get cart count (for header display)
     */
    public function getCount() {
        Middleware::requireAuth();
        
        if (!$this->isAjax()) {
            $this->redirect('home');
        }
        
        $userId = Session::get('user_id');
        $summary = $this->cartModel->getCartSummary($userId);
        
        $this->json([
            'status' => 'success',
            'data' => [
                'count' => $summary['total_items'],
                'total' => $summary['total_amount']
            ]
        ]);
    }
    
    /**
     * Checkout cart
     */
    public function checkout() {
        Middleware::requireAuth();
        
        $userId = Session::get('user_id');
        $cart = $this->cartModel->getCart($userId);
        $summary = $this->cartModel->getCartSummary($userId);
        
        if (empty($cart)) {
            Session::flash('error', 'Keranjang kosong');
            $this->redirect('cart');
        }
        
        $data = [
            'title' => 'Checkout - MyWisata',
            'cart' => $cart,
            'summary' => $summary,
            'csrf_token' => Middleware::csrfToken()
        ];
        
        $this->view('cart/checkout', $data);
    }
}
