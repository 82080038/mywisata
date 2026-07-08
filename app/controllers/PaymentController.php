<?php

/**
 * MyWisata Application - Payment Controller
 *
 * Handles payment processing.
 *
 * @version 1.0.0
 *
 * @since 2026-07-01
 */
class PaymentController extends Controller
{
    /**
     * Constructor - Require login
     */
    public function __construct()
    {
        parent::__construct();

        if (!Session::get('user_id')) {
            $this->redirect('auth/login');
        }
    }

    /**
     * Index - Show payment form
     */
    public function index()
    {
        $transactionId = $this->get('transaction_id');
        $transactionModel = new Transaction();
        $transaction = $transactionModel->findById($transactionId);

        if (!$transaction || $transaction['user_id'] != Session::get('user_id')) {
            Session::flash('error', 'Transaksi tidak ditemukan');
            $this->redirect('home');
        }

        $data = [
            'title' => 'Pembayaran - MyWisata',
            'transaction' => $transaction,
        ];

        $this->view('payment/index', $data);
    }

    /**
     * Process payment
     */
    public function process()
    {
        // Verify CSRF
        if (!$this->validateCsrf()) {
            $this->json(['status' => 'error', 'message' => 'CSRF token mismatch'], 419);
        }

        $transactionId = $this->post('transaction_id');
        $paymentMethod = $this->post('payment_method');
        $userId = Session::get('user_id');

        $transactionModel = new Transaction();
        $transaction = $transactionModel->findById($transactionId);

        if (!$transaction || $transaction['user_id'] != $userId) {
            $this->json(['status' => 'error', 'message' => 'Transaksi tidak ditemukan'], 404);
        }

        if ($transaction['payment_status'] === 'paid') {
            $this->json(['status' => 'error', 'message' => 'Transaksi sudah dibayar'], 400);
        }

        // Create payment gateway transaction
        try {
            $userModel = new User();
            $user = $userModel->findById($userId);

            $paymentData = [
                'order_id' => $transaction['transaction_code'],
                'amount' => $transaction['net_amount'],
                'customer_name' => $user['name'],
                'customer_email' => $user['email'],
                'customer_phone' => $user['phone'],
                'items' => [
                    [
                        'id' => $transaction['id'],
                        'price' => $transaction['net_amount'],
                        'quantity' => 1,
                        'name' => ucfirst($transaction['type']),
                    ],
                ],
            ];

            $paymentResult = PaymentGateway::createTransaction($paymentData);

            // Update transaction with payment gateway data
            $transactionModel->updatePaymentMethod($transactionId, $paymentMethod);
            
            Logger::audit('PAYMENT_GATEWAY_INITIATED', 'transactions', 
                "Payment gateway transaction created", [], [
                    'transaction_id' => $transactionId,
                    'payment_method' => $paymentMethod,
                ]);

            $this->json([
                'status' => 'success',
                'message' => 'Payment initiated',
                'payment_url' => $paymentResult['redirect_url'] ?? null,
                'token' => $paymentResult['token'] ?? null,
            ]);

        } catch (Exception $e) {
            Logger::error('Payment gateway failed', [
                'transaction_id' => $transactionId,
                'error' => $e->getMessage(),
            ]);
            
            // Fallback to manual payment processing
            $transactionModel->updatePaymentStatus($transactionId, 'paid');
            $transactionModel->updatePaymentMethod($transactionId, $paymentMethod);

            // Update related booking/ticket status
            if ($transaction['type'] === 'booking_guide' && $transaction['booking_id']) {
                $bookingModel = new Booking();
                $bookingModel->updateStatus($transaction['booking_id'], 'confirmed');
            }

            Logger::audit('PROCESS_PAYMENT', 'transactions', 
                "Processed payment for transaction ID: {$transactionId}", [], ['payment_method' => $paymentMethod]);

            $this->json(['status' => 'success', 'message' => 'Pembayaran berhasil']);
        }
    }

    /**
     * Payment notification handler (webhook)
     */
    public function notification()
    {
        // Get raw POST data
        $input = file_get_contents('php://input');
        $notification = json_decode($input, true);

        if (!$notification) {
            http_response_code(400);
            echo 'Invalid notification data';
            exit;
        }

        // Verify signature
        if (!PaymentGateway::verifySignature($notification)) {
            http_response_code(403);
            echo 'Invalid signature';
            exit;
        }

        // Handle notification
        $success = PaymentGateway::handleNotification($notification);

        if ($success) {
            http_response_code(200);
            echo 'Notification processed';
        } else {
            http_response_code(500);
            echo 'Failed to process notification';
        }

        exit;
    }

    /**
     * Check payment status
     */
    public function checkStatus()
    {
        if (!$this->isAjax()) {
            $this->redirect('home');
        }

        $orderId = $this->get('order_id');
        $userId = Session::get('user_id');

        $transactionModel = new Transaction();
        $transaction = $transactionModel->findByCode($orderId);

        if (!$transaction || $transaction['user_id'] != $userId) {
            $this->json(['status' => 'error', 'message' => 'Transaksi tidak ditemukan'], 404);
        }

        try {
            $status = PaymentGateway::getTransactionStatus($orderId);

            // Sync status if needed
            if ($status && isset($status['transaction_status'])) {
                $statusMap = [
                    'capture' => 'paid',
                    'settlement' => 'paid',
                    'pending' => 'pending',
                    'deny' => 'failed',
                    'cancel' => 'cancelled',
                    'expire' => 'expired',
                ];

                $appStatus = $statusMap[$status['transaction_status']] ?? 'pending';

                if ($appStatus !== $transaction['payment_status']) {
                    $transactionModel->updatePaymentStatus($transaction['id'], $appStatus);

                    // Update related booking/ticket status
                    if ($appStatus === 'paid' && $transaction['type'] === 'booking_guide' && $transaction['booking_id']) {
                        $bookingModel = new Booking();
                        $bookingModel->updateStatus($transaction['booking_id'], 'confirmed');
                    }

                    Logger::audit('PAYMENT_STATUS_SYNCED', 'transactions', 
                        "Payment status synced from gateway", [], [
                            'order_id' => $orderId,
                            'status' => $appStatus,
                        ]);
                }
            }

            $this->json([
                'status' => 'success',
                'payment_status' => $transaction['payment_status'],
                'gateway_status' => $status['transaction_status'] ?? null,
            ]);

        } catch (Exception $e) {
            Logger::error('Payment status check failed', [
                'order_id' => $orderId,
                'error' => $e->getMessage(),
            ]);

            $this->json([
                'status' => 'success',
                'payment_status' => $transaction['payment_status'],
                'gateway_status' => null,
            ]);
        }
    }
}
