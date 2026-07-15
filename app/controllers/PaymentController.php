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
    
    /**
     * Constructor - Require login
     */
    public function __construct() {
        parent::__construct();
        if (!Session::get('user_id')) {
            $this->redirect('auth/login');
        }
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
     * Create payment token with Midtrans
     */
    public function createToken() {
        $transactionId = $this->post('transaction_id');
        $userId = Session::get('user_id');
        
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
        
        // Format items for Midtrans
        $midtransItems = [];
        foreach ($items as $item) {
            $midtransItems[] = [
                'id' => $item['item_id'],
                'price' => (int) $item['unit_price'],
                'quantity' => (int) $item['quantity'],
                'name' => $item['item_type'],
                'category' => $item['item_type']
            ];
        }
        
        // Create Midtrans transaction
        $midtransData = [
            'order_id' => $transaction['transaction_code'],
            'amount' => (int) $transaction['net_amount'],
            'customer_name' => $user['name'],
            'customer_email' => $user['email'],
            'customer_phone' => $user['phone'] ?? '',
            'items' => $midtransItems,
            'type' => $transaction['type'],
            'reference_id' => $transaction['reference_id'],
            'expiry' => PAYMENT_TIMEOUT_HOURS
        ];
        
        $midtransResponse = Midtrans::createTransaction($midtransData);
        
        if ($midtransResponse && isset($midtransResponse['token'])) {
            // Update transaction with Midtrans token
            $transactionModel->updateMidtransToken($transactionId, $midtransResponse['token'], $midtransResponse['redirect_url'] ?? null);
            
            Logger::info('Payment token created', [
                'transaction_id' => $transactionId,
                'order_id' => $transaction['transaction_code']
            ]);
            
            $this->json([
                'status' => 'success',
                'message' => 'Token pembayaran berhasil dibuat',
                'data' => [
                    'token' => $midtransResponse['token'],
                    'redirect_url' => $midtransResponse['redirect_url'] ?? null
                ]
            ]);
        } else {
            Logger::error('Failed to create Midtrans token', [
                'transaction_id' => $transactionId
            ]);
            
            $this->json([
                'status' => 'error',
                'message' => 'Gagal membuat token pembayaran. Silakan coba lagi.'
            ], 500);
        }
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
     * Handle Midtrans notification webhook
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
        
        $orderId = $notification['order_id'];
        $statusCode = $notification['status_code'];
        $grossAmount = $notification['gross_amount'];
        $signatureKey = $notification['signature_key'];
        
        // Verify signature
        if (!Midtrans::verifySignature($orderId, $statusCode, $grossAmount, $signatureKey)) {
            http_response_code(403);
            echo 'Invalid signature';
            exit;
        }
        
        // Get transaction status from Midtrans
        $transactionStatus = Midtrans::getTransactionStatus($orderId);
        
        if (!$transactionStatus) {
            http_response_code(500);
            echo 'Failed to get transaction status';
            exit;
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
            
            Logger::info('Payment notification processed', [
                'order_id' => $orderId,
                'status' => $appStatus
            ]);
        }
        
        http_response_code(200);
        echo 'OK';
        exit;
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
