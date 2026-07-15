<?php
/**
 * MyWisata Application - Midtrans Payment Gateway Helper
 * 
 * Handles integration with Midtrans payment gateway for Indonesian market.
 * 
 * @package MyWisata
 * @version 1.0.0
 * @since 2026-07-15
 */

class Midtrans {
    
    private static $serverKey;
    private static $clientKey;
    private static $merchantId;
    private static $isProduction;
    private static $apiUrl;
    
    /**
     * Initialize Midtrans configuration
     */
    public static function init() {
        self::$serverKey = MIDTRANS_SERVER_KEY;
        self::$clientKey = MIDTRANS_CLIENT_KEY;
        self::$merchantId = MIDTRANS_MERCHANT_ID;
        self::$isProduction = MIDTRANS_IS_PRODUCTION;
        
        // Set API URL based on environment
        if (self::$isProduction) {
            self::$apiUrl = 'https://app.midtrans.com/snap/v1';
        } else {
            self::$apiUrl = 'https://app.sandbox.midtrans.com/snap/v1';
        }
    }
    
    /**
     * Create payment transaction
     * 
     * @param array $transactionData Transaction details
     * @return array Response from Midtrans
     */
    public static function createTransaction($transactionData) {
        self::init();
        
        $payload = [
            'transaction_details' => [
                'order_id' => $transactionData['order_id'],
                'gross_amount' => (int) $transactionData['amount']
            ],
            'customer_details' => [
                'first_name' => $transactionData['customer_name'],
                'email' => $transactionData['customer_email'],
                'phone' => $transactionData['customer_phone'] ?? ''
            ],
            'item_details' => $transactionData['items'] ?? [],
            'enabled_payments' => [
                'credit_card',
                'bca_va',
                'bri_va',
                'bni_va',
                'permata_va',
                'cimb_va',
                'mandiri_va',
                'gopay',
                'qris',
                'shopeepay'
            ],
            'callbacks' => [
                'finish' => BASE_URL . 'payment/callback/finish',
                'unfinish' => BASE_URL . 'payment/callback/unfinish',
                'error' => BASE_URL . 'payment/callback/error'
            ]
        ];
        
        // Add expiry if provided
        if (isset($transactionData['expiry'])) {
            $payload['expiry'] = [
                'unit' => 'hours',
                'duration' => (int) $transactionData['expiry']
            ];
        }
        
        // Add custom fields for tracking
        $payload['custom_field1'] = $transactionData['type'] ?? '';
        $payload['custom_field2'] = $transactionData['reference_id'] ?? '';
        
        $response = self::request('POST', '/transactions', $payload);
        
        if ($response && isset($response['token'])) {
            Logger::info('Midtrans transaction created', [
                'order_id' => $transactionData['order_id'],
                'token' => $response['token']
            ]);
        }
        
        return $response;
    }
    
    /**
     * Get transaction status
     * 
     * @param string $orderId Order ID
     * @return array Transaction status
     */
    public static function getTransactionStatus($orderId) {
        self::init();
        
        $response = self::request('GET', '/transactions/' . $orderId . '/status');
        
        return $response;
    }
    
    /**
     * Cancel transaction
     * 
     * @param string $orderId Order ID
     * @return array Response
     */
    public static function cancelTransaction($orderId) {
        self::init();
        
        $response = self::request('POST', '/transactions/' . $orderId . '/cancel');
        
        if ($response) {
            Logger::info('Midtrans transaction cancelled', ['order_id' => $orderId]);
        }
        
        return $response;
    }
    
    /**
     * Refund transaction
     * 
     * @param string $orderId Order ID
     * @param int $amount Refund amount
     * @return array Response
     */
    public static function refundTransaction($orderId, $amount = null) {
        self::init();
        
        $payload = [];
        if ($amount) {
            $payload['amount'] = (int) $amount;
        }
        
        $response = self::request('POST', '/transactions/' . $orderId . '/refund', $payload);
        
        if ($response) {
            Logger::info('Midtrans transaction refunded', [
                'order_id' => $orderId,
                'amount' => $amount
            ]);
        }
        
        return $response;
    }
    
    /**
     * Verify notification signature
     * 
     * @param string $orderId Order ID
     * @param string $statusCode Status code
     * @param string $grossAmount Gross amount
     * @param string $signatureKey Signature key from notification
     * @return bool Valid or not
     */
    public static function verifySignature($orderId, $statusCode, $grossAmount, $signatureKey) {
        self::init();
        
        $expectedSignature = hash('sha512', $orderId . $statusCode . $grossAmount . self::$serverKey);
        
        return $signatureKey === $expectedSignature;
    }
    
    /**
     * Make HTTP request to Midtrans API
     * 
     * @param string $method HTTP method
     * @param string $endpoint API endpoint
     * @param array $data Request data
     * @return array Response
     */
    private static function request($method, $endpoint, $data = []) {
        $url = self::$apiUrl . $endpoint;
        
        $ch = curl_init();
        
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Accept: application/json',
            'Content-Type: application/json',
            'Authorization: Basic ' . base64_encode(self::$serverKey . ':')
        ]);
        
        if ($method === 'POST') {
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        }
        
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        
        curl_close($ch);
        
        if ($error) {
            Logger::error('Midtrans API request failed', [
                'error' => $error,
                'url' => $url
            ]);
            return null;
        }
        
        $decoded = json_decode($response, true);
        
        if ($httpCode >= 400) {
            Logger::error('Midtrans API error', [
                'http_code' => $httpCode,
                'response' => $decoded
            ]);
            return null;
        }
        
        return $decoded;
    }
    
    /**
     * Map Midtrans status to application status
     * 
     * @param string $midtransStatus Midtrans status
     * @return string Application status
     */
    public static function mapStatus($midtransStatus) {
        $statusMap = [
            'pending' => 'pending',
            'capture' => 'paid',
            'settlement' => 'paid',
            'cancel' => 'cancelled',
            'expire' => 'expired',
            'deny' => 'failed',
            'refund' => 'refunded',
            'partial_refund' => 'refunded'
        ];
        
        return $statusMap[$midtransStatus] ?? 'pending';
    }
    
    /**
     * Format item details for Midtrans
     * 
     * @param array $items Items array
     * @return array Formatted items
     */
    public static function formatItems($items) {
        $formatted = [];
        
        foreach ($items as $item) {
            $formatted[] = [
                'id' => $item['id'] ?? '',
                'price' => (int) $item['price'],
                'quantity' => (int) $item['quantity'],
                'name' => $item['name'] ?? 'Item',
                'category' => $item['category'] ?? ''
            ];
        }
        
        return $formatted;
    }
}
