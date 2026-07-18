<?php
/**
 * MyWisata Application - Payment Controller
 * 
 * Handles payment processing with Midtrans integration.
 * 
 * @package MyWisata
 * @version 1.0.0
 * @since 2026-07-01
 */

class PaymentController extends Controller {
    
    private $paymentGatewayController;
    private $currencyController;
    
    /**
     * Constructor - Require login
     */
    public function __construct() {
        parent::__construct();
        if (!Session::get('user_id')) {
            $this->redirect('auth/login');
        }
        $this->paymentGatewayController = new PaymentGatewayController();
        $this->currencyController = new CurrencyController();
    }
    
    /**
     * Index - Show payment form
     */
    public function index() {
        $transactionId = $this->get('transaction_id');
        $transactionModel = $this->model('Transaction');
        $transaction = $transactionModel->findById($transactionId);
        
        if (!$transaction || $transaction['user_id'] != Session::get('user_id')) {
            Session::flash('error', 'Transaksi tidak ditemukan');
            $this->redirect('home');
        }
        
        $data = [
            'title' => 'Pembayaran - MyWisata',
            'transaction' => $transaction,
            'midtrans_client_key' => MIDTRANS_CLIENT_KEY
        ];
        
        $this->view('payment/index', $data);
    }
    
    /**
     * Create payment token with intelligent gateway routing
     */
    public function createToken() {
        $transactionId = $this->post('transaction_id');
        $userId = Session::get('user_id');
        $preferredGateway = $this->post('gateway', 'auto'); // auto, midtrans, stripe, paypal
        
        $transactionModel = $this->model('Transaction');
        $transaction = $transactionModel->findById($transactionId);
        
        if (!$transaction || $transaction['user_id'] != $userId) {
            $this->json(['status' => 'error', 'message' => 'Transaksi tidak ditemukan'], 404);
        }
        
        if ($transaction['payment_status'] === 'paid') {
            $this->json(['status' => 'error', 'message' => 'Transaksi sudah dibayar'], 400);
        }
        
        // Get user details
        $userModel = $this->model('User');
        $user = $userModel->findById($userId);
        
        // Get transaction items
        $transactionItemModel = $this->model('TransactionItem');
        $items = $transactionItemModel->getByTransactionId($transactionId);
        
        // Determine currency
        $currency = $transaction['currency'] ?? 'IDR';
        $amount = $transaction['net_amount'];
        
        // Route to appropriate gateway
        $gatewayRouting = $this->paymentGatewayController->routeTransaction($currency, $amount, $preferredGateway, $user['country'] ?? 'ID');
        
        if (!$gatewayRouting['success']) {
            $this->json(['status' => 'error', 'message' => $gatewayRouting['message']], 400);
        }
        
        $gateway = $gatewayRouting['gateway'];
        
        // Format items for gateway
        $gatewayItems = [];
        foreach ($items as $item) {
            $gatewayItems[] = [
                'id' => $item['item_id'],
                'price' => (float) $item['unit_price'],
                'quantity' => (int) $item['quantity'],
                'name' => $item['item_type'],
                'category' => $item['item_type']
            ];
        }
        
        // Create payment intent based on gateway
        $paymentIntent = null;
        switch ($gateway) {
            case 'midtrans':
                $paymentIntent = $this->createMidtransIntent($transaction, $user, $gatewayItems);
                break;
            case 'stripe':
                $paymentIntent = $this->paymentGatewayController->createStripeIntent($transaction['transaction_code'], $amount, $currency, $user);
                break;
            case 'paypal':
                $paymentIntent = $this->paymentGatewayController->createPayPalOrder($transaction['transaction_code'], $amount, $currency, $user);
                break;
            default:
                $paymentIntent = $this->createMidtransIntent($transaction, $user, $gatewayItems);
        }
        
        if ($paymentIntent && $paymentIntent['success']) {
            // Update transaction with gateway info
            $transactionModel->updateGatewayInfo($transactionId, $gateway, $paymentIntent['gateway_payment_id'], $paymentIntent['client_secret'] ?? null);
            
            Logger::info('Payment token created', [
                'transaction_id' => $transactionId,
                'order_id' => $transaction['transaction_code'],
                'gateway' => $gateway
            ]);
            
            $this->json([
                'status' => 'success',
                'message' => 'Token pembayaran berhasil dibuat',
                'data' => [
                    'gateway' => $gateway,
                    'token' => $paymentIntent['token'] ?? $paymentIntent['client_secret'],
                    'redirect_url' => $paymentIntent['redirect_url'] ?? null,
                    'payment_intent_id' => $paymentIntent['payment_intent_id'] ?? null
                ]
            ]);
        } else {
            Logger::error('Failed to create payment token', [
                'transaction_id' => $transactionId,
                'gateway' => $gateway
            ]);
            
            $this->json([
                'status' => 'error',
                'message' => 'Gagal membuat token pembayaran. Silakan coba lagi.'
            ], 500);
        }
    }
    
    /**
     * Create Midtrans payment intent (legacy fallback)
     */
    private function createMidtransIntent($transaction, $user, $items) {
        $midtransData = [
            'order_id' => $transaction['transaction_code'],
            'amount' => (int) $transaction['net_amount'],
            'customer_name' => $user['name'],
            'customer_email' => $user['email'],
            'customer_phone' => $user['phone'] ?? '',
            'items' => $items,
            'type' => $transaction['type'],
            'reference_id' => $transaction['reference_id'],
            'expiry' => PAYMENT_TIMEOUT_HOURS
        ];
        
        $midtransResponse = Midtrans::createTransaction($midtransData);
        
        if ($midtransResponse && isset($midtransResponse['token'])) {
            return [
                'success' => true,
                'token' => $midtransResponse['token'],
                'redirect_url' => $midtransResponse['redirect_url'] ?? null,
                'gateway_payment_id' => $midtransResponse['token']
            ];
        }
        
        return ['success' => false, 'message' => 'Midtrans failed'];
    }
    
    /**
     * Process manual payment (fallback)
     */
    public function processManual() {
        $transactionId = $this->post('transaction_id');
        $paymentMethod = $this->post('payment_method');
        $paymentProof = $this->post('payment_proof');
        $userId = Session::get('user_id');
        
        $transactionModel = $this->model('Transaction');
        $transaction = $transactionModel->findById($transactionId);
        
        if (!$transaction || $transaction['user_id'] != $userId) {
            $this->json(['status' => 'error', 'message' => 'Transaksi tidak ditemukan'], 404);
        }
        
        if ($transaction['payment_status'] === 'paid') {
            $this->json(['status' => 'error', 'message' => 'Transaksi sudah dibayar'], 400);
        }
        
        // Update transaction with manual payment info
        $transactionModel->updatePaymentMethod($transactionId, $paymentMethod);
        $transactionModel->updatePaymentProof($transactionId, $paymentProof);
        $transactionModel->updatePaymentStatus($transactionId, 'pending'); // Waiting for admin approval
        
        Logger::audit('MANUAL_PAYMENT_SUBMITTED', 'transactions', "Manual payment submitted for transaction ID: {$transactionId}", [], ['payment_method' => $paymentMethod]);
        
        $this->json(['status' => 'success', 'message' => 'Bukti pembayaran berhasil diupload. Menunggu verifikasi admin.']);
    }
    
    /**
     * Handle Midtrans callback - Payment finished
     */
    public function callbackFinish() {
        $this->handleCallback('finish');
    }
    
    /**
     * Handle Midtrans callback - Payment unfinished
     */
    public function callbackUnfinish() {
        $this->handleCallback('unfinish');
    }
    
    /**
     * Handle Midtrans callback - Payment error
     */
    public function callbackError() {
        $this->handleCallback('error');
    }
    
    /**
     * Handle payment notification webhook (multi-gateway)
     */
    public function notification() {
        // Get raw POST data
        $rawNotification = file_get_contents('php://input');
        $notification = json_decode($rawNotification, true);
        
        if (!$notification) {
            http_response_code(400);
            echo 'Invalid notification';
            exit;
        }
        
        // Detect gateway from notification
        $gateway = $this->detectGatewayFromNotification($notification);
        
        // Route to appropriate handler
        $result = false;
        switch ($gateway) {
            case 'midtrans':
                $result = $this->handleMidtransNotification($notification);
                break;
            case 'stripe':
                $result = $this->paymentGatewayController->handleStripeWebhook($notification);
                break;
            case 'paypal':
                $result = $this->paymentGatewayController->handlePayPalWebhook($notification);
                break;
            default:
                // Default to Midtrans for backward compatibility
                $result = $this->handleMidtransNotification($notification);
        }
        
        if ($result) {
            http_response_code(200);
            echo 'OK';
        } else {
            http_response_code(500);
            echo 'Failed to process notification';
        }
        exit;
    }
    
    /**
     * Detect gateway from notification payload
     */
    private function detectGatewayFromNotification($notification) {
        if (isset($notification['order_id']) && isset($notification['signature_key'])) {
            return 'midtrans';
        }
        if (isset($notification['type']) && isset($notification['data']['object'])) {
            return 'stripe';
        }
        if (isset($notification['event_type']) || isset($notification['resource_type'])) {
            return 'paypal';
        }
        return 'midtrans'; // Default
    }
    
    /**
     * Handle Midtrans notification (legacy)
     */
    private function handleMidtransNotification($notification) {
        $orderId = $notification['order_id'];
        $statusCode = $notification['status_code'];
        $grossAmount = $notification['gross_amount'];
        $signatureKey = $notification['signature_key'];
        
        // Verify signature
        if (!Midtrans::verifySignature($orderId, $statusCode, $grossAmount, $signatureKey)) {
            http_response_code(403);
            echo 'Invalid signature';
            return false;
        }
        
        // Get transaction status from Midtrans
        $transactionStatus = Midtrans::getTransactionStatus($orderId);
        
        if (!$transactionStatus) {
            return false;
        }
        
        // Map Midtrans status to application status
        $appStatus = Midtrans::mapStatus($transactionStatus['transaction_status']);
        
        // Update transaction in database
        $transactionModel = $this->model('Transaction');
        $transaction = $transactionModel->findByCode($orderId);
        
        if ($transaction) {
            $transactionModel->updatePaymentStatus($transaction['id'], $appStatus);
            
            // Update related booking/ticket status based on payment status
            if ($appStatus === 'paid') {
                $this->updateRelatedStatus($transaction);
            }
            
            Logger::info('Midtrans notification processed', [
                'order_id' => $orderId,
                'status' => $appStatus
            ]);
            return true;
        }
        
        return false;
    }
    
    /**
     * Handle callback from Midtrans
     */
    private function handleCallback($type) {
        $orderId = $this->get('order_id');
        
        if (!$orderId) {
            Session::flash('error', 'Order ID tidak ditemukan');
            $this->redirect('home');
        }
        
        // Get transaction status from Midtrans
        $transactionStatus = Midtrans::getTransactionStatus($orderId);
        
        if (!$transactionStatus) {
            Session::flash('error', 'Gagal mendapatkan status transaksi');
            $this->redirect('home');
        }
        
        // Map status
        $appStatus = Midtrans::mapStatus($transactionStatus['transaction_status']);
        
        // Update transaction
        $transactionModel = $this->model('Transaction');
        $transaction = $transactionModel->findByCode($orderId);
        
        if ($transaction) {
            $transactionModel->updatePaymentStatus($transaction['id'], $appStatus);
            
            if ($appStatus === 'paid') {
                $this->updateRelatedStatus($transaction);
                Session::flash('success', 'Pembayaran berhasil! Transaksi Anda telah dikonfirmasi.');
            } elseif ($appStatus === 'pending') {
                Session::flash('warning', 'Pembayaran belum selesai. Silakan selesaikan pembayaran Anda.');
            } elseif ($appStatus === 'failed') {
                Session::flash('error', 'Pembayaran gagal. Silakan coba lagi.');
            } elseif ($appStatus === 'cancelled') {
                Session::flash('info', 'Pembayaran dibatalkan.');
            }
        }
        
        $this->redirect('booking/history');
    }
    
    /**
     * Update related booking/ticket status after successful payment
     */
    private function updateRelatedStatus($transaction) {
        $type = $transaction['type'];
        $referenceId = $transaction['reference_id'];
        
        switch ($type) {
            case 'booking_guide':
                $bookingModel = $this->model('Booking');
                $bookingModel->updateStatus($referenceId, 'confirmed');
                break;
            case 'ticket':
                $ticketOrderModel = $this->model('TicketOrder');
                $ticketOrderModel->updateStatus($referenceId, 'paid');
                break;
            case 'hotel':
                $hotelBookingModel = $this->model('HotelBooking');
                $hotelBookingModel->updateStatus($referenceId, 'confirmed');
                break;
            case 'restaurant':
                $restaurantOrderModel = $this->model('RestaurantOrder');
                $restaurantOrderModel->updateStatus($referenceId, 'confirmed');
                break;
            case 'event':
                $eventRegistrationModel = $this->model('EventRegistration');
                $eventRegistrationModel->updateStatus($referenceId, 'registered');
                break;
        }
    }
}
