<?php

/**
 * MyWisata Application - Payment Gateway Helper
 *
 * Handles payment gateway integration (Midtrans/Xendit).
 *
 * @version 1.0.0
 *
 * @since 2026-07-01
 */
class PaymentGateway
{
    private static $gateway;
    private static $config;

    /**
     * Initialize payment gateway
     */
    public static function init()
    {
        $gatewayType = getenv('PAYMENT_GATEWAY') ?: 'midtrans';
        
        if ($gatewayType === 'midtrans') {
            self::$gateway = new MidtransGateway();
        } elseif ($gatewayType === 'xendit') {
            self::$gateway = new XenditGateway();
        } else {
            throw new Exception("Unsupported payment gateway: {$gatewayType}");
        }

        self::$config = [
            'server_key' => getenv('PAYMENT_SERVER_KEY'),
            'client_key' => getenv('PAYMENT_CLIENT_KEY'),
            'is_production' => getenv('PAYMENT_IS_PRODUCTION') === 'true',
            'merchant_id' => getenv('PAYMENT_MERCHANT_ID'),
        ];
    }

    /**
     * Create payment transaction
     *
     * @param array $data Transaction data
     * @return array Transaction details
     */
    public static function createTransaction($data)
    {
        self::init();

        $transactionData = array_merge([
            'transaction_details' => [
                'order_id' => $data['order_id'],
                'gross_amount' => $data['amount'],
            ],
            'customer_details' => [
                'first_name' => $data['customer_name'] ?? 'Customer',
                'email' => $data['customer_email'] ?? '',
                'phone' => $data['customer_phone'] ?? '',
            ],
            'item_details' => $data['items'] ?? [],
        ], $data['additional_data'] ?? []);

        return self::$gateway->createTransaction($transactionData, self::$config);
    }

    /**
     * Get transaction status
     *
     * @param string $orderId Order ID
     * @return array Transaction status
     */
    public static function getTransactionStatus($orderId)
    {
        self::init();
        return self::$gateway->getTransactionStatus($orderId, self::$config);
    }

    /**
     * Handle payment notification
     *
     * @param array $notification Notification data
     * @return bool
     */
    public static function handleNotification($notification)
    {
        self::init();
        return self::$gateway->handleNotification($notification, self::$config);
    }

    /**
     * Verify payment notification signature
     *
     * @param array $notification Notification data
     * @return bool
     */
    public static function verifySignature($notification)
    {
        self::init();
        return self::$gateway->verifySignature($notification, self::$config);
    }

    /**
     * Cancel transaction
     *
     * @param string $orderId Order ID
     * @return bool
     */
    public static function cancelTransaction($orderId)
    {
        self::init();
        return self::$gateway->cancelTransaction($orderId, self::$config);
    }

    /**
     * Refund transaction
     *
     * @param string $orderId Order ID
     * @param float $amount Refund amount
     * @param string $reason Refund reason
     * @return bool
     */
    public static function refundTransaction($orderId, $amount, $reason = '')
    {
        self::init();
        return self::$gateway->refundTransaction($orderId, $amount, $reason, self::$config);
    }
}

/**
 * Midtrans Gateway Implementation
 */
class MidtransGateway
{
    public function createTransaction($data, $config)
    {
        $isProduction = $config['is_production'];
        $serverKey = $config['server_key'];
        $baseUrl = $isProduction 
            ? 'https://app.midtrans.com/snap/v1/transactions' 
            : 'https://app.sandbox.midtrans.com/snap/v1/transactions';

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $baseUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Accept: application/json',
            'Authorization: Basic ' . base64_encode($serverKey . ':'),
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode !== 201) {
            Logger::error('Midtrans create transaction failed', [
                'http_code' => $httpCode,
                'response' => $response,
            ]);
            throw new Exception('Failed to create transaction');
        }

        $result = json_decode($response, true);
        
        Logger::audit('PAYMENT_TRANSACTION_CREATED', 'transactions', 
            "Created payment transaction", [], [
                'order_id' => $data['transaction_details']['order_id'],
                'amount' => $data['transaction_details']['gross_amount'],
            ]);

        return $result;
    }

    public function getTransactionStatus($orderId, $config)
    {
        $isProduction = $config['is_production'];
        $serverKey = $config['server_key'];
        $baseUrl = $isProduction 
            ? "https://api.midtrans.com/v2/{$orderId}/status" 
            : "https://api.sandbox.midtrans.com/v2/{$orderId}/status";

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $baseUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Accept: application/json',
            'Authorization: Basic ' . base64_encode($serverKey . ':'),
        ]);

        $response = curl_exec($ch);
        curl_close($ch);

        return json_decode($response, true);
    }

    public function handleNotification($notification, $config)
    {
        // Update transaction status in database based on notification
        $orderId = $notification['order_id'];
        $transactionStatus = $notification['transaction_status'];
        $fraudStatus = $notification['fraud_status'] ?? '';

        $db = Database::getInstance();

        // Map Midtrans status to application status
        $statusMap = [
            'capture' => 'paid',
            'settlement' => 'paid',
            'pending' => 'pending',
            'deny' => 'failed',
            'cancel' => 'cancelled',
            'expire' => 'expired',
            'refund' => 'refunded',
        ];

        $appStatus = $statusMap[$transactionStatus] ?? 'pending';

        // Update transaction
        $sql = "UPDATE transactions 
                SET payment_status = :status, 
                    payment_method = :method,
                    updated_at = NOW()
                WHERE transaction_code = :order_id";

        $db->query($sql, [
            'status' => $appStatus,
            'method' => $notification['payment_type'] ?? 'midtrans',
            'order_id' => $orderId,
        ]);

        Logger::audit('PAYMENT_NOTIFICATION', 'transactions', 
            "Payment notification received", [], [
                'order_id' => $orderId,
                'status' => $transactionStatus,
                'app_status' => $appStatus,
            ]);

        return true;
    }

    public function verifySignature($notification, $config)
    {
        $orderId = $notification['order_id'];
        $statusCode = $notification['status_code'];
        $grossAmount = $notification['gross_amount'];
        $serverKey = $config['server_key'];

        $signatureKey = hash('sha512', $orderId . $statusCode . $grossAmount . $serverKey);
        
        return $signatureKey === $notification['signature_key'];
    }

    public function cancelTransaction($orderId, $config)
    {
        $isProduction = $config['is_production'];
        $serverKey = $config['server_key'];
        $baseUrl = $isProduction 
            ? "https://api.midtrans.com/v2/{$orderId}/cancel" 
            : "https://api.sandbox.midtrans.com/v2/{$orderId}/cancel";

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $baseUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Accept: application/json',
            'Authorization: Basic ' . base64_encode($serverKey . ':'),
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        return $httpCode === 200;
    }

    public function refundTransaction($orderId, $amount, $reason, $config)
    {
        $isProduction = $config['is_production'];
        $serverKey = $config['server_key'];
        $baseUrl = $isProduction 
            ? "https://api.midtrans.com/v2/{$orderId}/refund" 
            : "https://api.sandbox.midtrans.com/v2/{$orderId}/refund";

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $baseUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode([
            'amount' => $amount,
            'reason' => $reason,
        ]));
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Accept: application/json',
            'Authorization: Basic ' . base64_encode($serverKey . ':'),
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        return $httpCode === 200;
    }
}

/**
 * Xendit Gateway Implementation (Placeholder)
 */
class XenditGateway
{
    public function createTransaction($data, $config)
    {
        // Xendit implementation
        // This is a placeholder - implement based on Xendit API documentation
        throw new Exception('Xendit gateway not yet implemented');
    }

    public function getTransactionStatus($orderId, $config)
    {
        throw new Exception('Xendit gateway not yet implemented');
    }

    public function handleNotification($notification, $config)
    {
        throw new Exception('Xendit gateway not yet implemented');
    }

    public function verifySignature($notification, $config)
    {
        throw new Exception('Xendit gateway not yet implemented');
    }

    public function cancelTransaction($orderId, $config)
    {
        throw new Exception('Xendit gateway not yet implemented');
    }

    public function refundTransaction($orderId, $amount, $reason, $config)
    {
        throw new Exception('Xendit gateway not yet implemented');
    }
}
