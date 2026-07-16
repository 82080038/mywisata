<?php
/**
 * MyWisata Application - Payment Gateway Helper
 * 
 * Handles payment gateway integration (Midtrans/Xendit).
 * 
 * @package MyWisata
 * @version 1.0.0
 * @since 2026-07-16
 */

class PaymentGateway {
    
    private $provider;
    private $apiKey;
    private $apiSecret;
    private $isSandbox;
    private $db;
    
    public function __construct($provider = 'midtrans') {
        $this->provider = $provider;
        $this->db = Database::getInstance();
        
        if ($provider === 'midtrans') {
            $this->apiKey = getenv('MIDTRANS_SERVER_KEY') ?: '';
            $this->isSandbox = getenv('MIDTRANS_SANDBOX') !== 'false';
        } elseif ($provider === 'xendit') {
            $this->apiKey = getenv('XENDIT_API_KEY') ?: '';
            $this->apiSecret = getenv('XENDIT_SECRET_KEY') ?: '';
            $this->isSandbox = getenv('XENDIT_SANDBOX') !== 'false';
        }
    }
    
    /**
     * Create payment transaction
     * 
     * @param array $paymentData Payment data
     * @return array|false
     */
    public function createTransaction($paymentData) {
        if ($this->provider === 'midtrans') {
            return $this->createMidtransTransaction($paymentData);
        } elseif ($this->provider === 'xendit') {
            return $this->createXenditTransaction($paymentData);
        }
        
        return false;
    }
    
    /**
     * Create Midtrans transaction
     * 
     * @param array $paymentData Payment data
     * @return array|false
     */
    private function createMidtransTransaction($paymentData) {
        $endpoint = $this->isSandbox 
            ? 'https://app.sandbox.midtrans.com/snap/v1/transactions'
            : 'https://app.midtrans.com/snap/v1/transactions';
        
        $payload = [
            'transaction_details' => [
                'order_id' => $paymentData['order_id'],
                'gross_amount' => $paymentData['amount']
            ],
            'customer_details' => [
                'first_name' => $paymentData['customer_name'],
                'email' => $paymentData['customer_email'],
                'phone' => $paymentData['customer_phone'] ?? ''
            ],
            'item_details' => $paymentData['items'] ?? [],
            'enabled_payments' => $paymentData['payment_methods'] ?? [
                'credit_card', 'bca_va', 'bni_va', 'bri_va', 'permata_va',
                'gopay', 'shopeepay', 'indomaret', 'alfamart'
            ]
        ];
        
        $response = $this->makeRequest($endpoint, 'POST', $payload, true);
        
        if ($response && isset($response['token'])) {
            // Save transaction to database
            $this->saveTransaction($paymentData['order_id'], $response['token'], $response['redirect_url'] ?? null);
            
            return [
                'token' => $response['token'],
                'redirect_url' => $response['redirect_url'] ?? null
            ];
        }
        
        return false;
    }
    
    /**
     * Create Xendit transaction
     * 
     * @param array $paymentData Payment data
     * @return array|false
     */
    private function createXenditTransaction($paymentData) {
        $endpoint = $this->isSandbox 
            ? 'https://api.xendit.co/v2/invoices'
            : 'https://api.xendit.co/v2/invoices';
        
        $payload = [
            'external_id' => $paymentData['order_id'],
            'amount' => $paymentData['amount'],
            'payer_email' => $paymentData['customer_email'],
            'description' => $paymentData['description'] ?? 'Payment for MyWisata',
            'success_redirect_url' => $paymentData['success_url'] ?? BASE_URL . 'payment/success',
            'failure_redirect_url' => $paymentData['failure_url'] ?? BASE_URL . 'payment/failure',
            'payment_methods' => $paymentData['payment_methods'] ?? null
        ];
        
        $response = $this->makeRequest($endpoint, 'POST', $payload, true);
        
        if ($response && isset($response['invoice_url'])) {
            // Save transaction to database
            $this->saveTransaction($paymentData['order_id'], $response['id'], $response['invoice_url']);
            
            return [
                'token' => $response['id'],
                'redirect_url' => $response['invoice_url']
            ];
        }
        
        return false;
    }
    
    /**
     * Make HTTP request to payment gateway
     * 
     * @param string $endpoint API endpoint
     * @param string $method HTTP method
     * @param array $payload Request payload
     * @param bool $auth Whether to use authentication
     * @return array|false
     */
    private function makeRequest($endpoint, $method = 'GET', $payload = [], $auth = false) {
        $ch = curl_init();
        
        curl_setopt($ch, CURLOPT_URL, $endpoint);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        
        if ($method === 'POST') {
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
            curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
        }
        
        if ($auth) {
            if ($this->provider === 'midtrans') {
                $authString = base64_encode($this->apiKey . ':');
                curl_setopt($ch, CURLOPT_HTTPHEADER, [
                    'Content-Type: application/json',
                    'Authorization: Basic ' . $authString
                ]);
            } elseif ($this->provider === 'xendit') {
                curl_setopt($ch, CURLOPT_HTTPHEADER, [
                    'Content-Type: application/json',
                    'Authorization: Basic ' . base64_encode($this->apiKey . ':')
                ]);
            }
        }
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        if ($httpCode === 200 && $response) {
            return json_decode($response, true);
        }
        
        Logger::error('Payment gateway request failed', [
            'provider' => $this->provider,
            'endpoint' => $endpoint,
            'http_code' => $httpCode,
            'response' => $response
        ]);
        
        return false;
    }
    
    /**
     * Save transaction to database
     * 
     * @param string $orderId Order ID
     * @param string $token Payment token
     * @param string $redirectUrl Redirect URL
     * @return bool
     */
    private function saveTransaction($orderId, $token, $redirectUrl = null) {
        $sql = "INSERT INTO payment_transactions 
                (order_id, provider, token, redirect_url, status, created_at)
                VALUES (:order_id, :provider, :token, :redirect_url, 'pending', NOW())
                ON DUPLICATE KEY UPDATE 
                token = :token,
                redirect_url = :redirect_url,
                updated_at = NOW()";
        
        return $this->db->query($sql, [
            'order_id' => $orderId,
            'provider' => $this->provider,
            'token' => $token,
            'redirect_url' => $redirectUrl
        ]);
    }
    
    /**
     * Verify payment notification
     * 
     * @param array $notification Notification data
     * @return bool
     */
    public function verifyNotification($notification) {
        if ($this->provider === 'midtrans') {
            return $this->verifyMidtransNotification($notification);
        } elseif ($this->provider === 'xendit') {
            return $this->verifyXenditNotification($notification);
        }
        
        return false;
    }
    
    /**
     * Verify Midtrans notification
     * 
     * @param array $notification Notification data
     * @return bool
     */
    private function verifyMidtransNotification($notification) {
        $orderId = $notification['order_id'] ?? null;
        $status = $notification['transaction_status'] ?? null;
        $fraudStatus = $notification['fraud_status'] ?? null;
        
        if (!$orderId || !$status) {
            return false;
        }
        
        // Update transaction status
        $paymentStatus = $this->mapMidtransStatus($status, $fraudStatus);
        
        $sql = "UPDATE payment_transactions 
                SET status = :status, 
                    payment_status = :payment_status,
                    raw_response = :raw_response,
                    updated_at = NOW()
                WHERE order_id = :order_id";
        
        return $this->db->query($sql, [
            'status' => $paymentStatus,
            'payment_status' => $status,
            'raw_response' => json_encode($notification),
            'order_id' => $orderId
        ]);
    }
    
    /**
     * Verify Xendit notification
     * 
     * @param array $notification Notification data
     * @return bool
     */
    private function verifyXenditNotification($notification) {
        $externalId = $notification['external_id'] ?? null;
        $status = $notification['status'] ?? null;
        
        if (!$externalId || !$status) {
            return false;
        }
        
        // Update transaction status
        $paymentStatus = $this->mapXenditStatus($status);
        
        $sql = "UPDATE payment_transactions 
                SET status = :status, 
                    payment_status = :payment_status,
                    raw_response = :raw_response,
                    updated_at = NOW()
                WHERE order_id = :order_id";
        
        return $this->db->query($sql, [
            'status' => $paymentStatus,
            'payment_status' => $status,
            'raw_response' => json_encode($notification),
            'order_id' => $externalId
        ]);
    }
    
    /**
     * Map Midtrans status to internal status
     * 
     * @param string $status Transaction status
     * @param string $fraudStatus Fraud status
     * @return string
     */
    private function mapMidtransStatus($status, $fraudStatus) {
        if ($status === 'capture') {
            if ($fraudStatus === 'accept') {
                return 'success';
            }
            return 'pending';
        }
        
        if ($status === 'settlement') {
            return 'success';
        }
        
        if ($status === 'cancel' || $status === 'deny' || $status === 'expire') {
            return 'failed';
        }
        
        if ($status === 'pending') {
            return 'pending';
        }
        
        return 'unknown';
    }
    
    /**
     * Map Xendit status to internal status
     * 
     * @param string $status Payment status
     * @return string
     */
    private function mapXenditStatus($status) {
        if ($status === 'PAID' || $status === 'SETTLED') {
            return 'success';
        }
        
        if ($status === 'CANCELLED' || $status === 'EXPIRED') {
            return 'failed';
        }
        
        if ($status === 'PENDING') {
            return 'pending';
        }
        
        return 'unknown';
    }
    
    /**
     * Get transaction status
     * 
     * @param string $orderId Order ID
     * @return array|false
     */
    public function getTransactionStatus($orderId) {
        $sql = "SELECT * FROM payment_transactions WHERE order_id = :order_id";
        return $this->db->query($sql, ['order_id' => $orderId])->fetch();
    }
    
    /**
     * Get available payment methods
     * 
     * @return array
     */
    public function getAvailablePaymentMethods() {
        if ($this->provider === 'midtrans') {
            return [
                'credit_card' => 'Credit Card',
                'bca_va' => 'BCA Virtual Account',
                'bni_va' => 'BNI Virtual Account',
                'bri_va' => 'BRI Virtual Account',
                'permata_va' => 'Permata Virtual Account',
                'gopay' => 'GoPay',
                'shopeepay' => 'ShopeePay',
                'indomaret' => 'Indomaret',
                'alfamart' => 'Alfamart'
            ];
        } elseif ($this->provider === 'xendit') {
            return [
                'VIRTUAL_ACCOUNT' => 'Virtual Account',
                'EWALLET' => 'E-Wallet',
                'RETAIL_OUTLET' => 'Retail Outlet',
                'CREDIT_CARD' => 'Credit Card',
                'QR_CODE' => 'QR Code'
            ];
        }
        
        return [];
    }
}
