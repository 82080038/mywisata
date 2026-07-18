<?php

/**
 * PaymentGatewayController
 * 
 * Handles multi-payment gateway operations including:
 * - Payment gateway routing
 * - Payment processing
 * - Webhook handling
 * - Dispute management
 * - Settlement reconciliation
 * 
 * @author MyWisata Team
 * @version 1.0
 */

class PaymentGatewayController extends Controller
{
    private $db;
    
    public function __construct()
    {
        $this->db = Database::getInstance();
    }
    
    /**
     * Get appropriate payment gateway for a transaction
     * 
     * @param array $transactionData Transaction data (currency, country, amount, etc.)
     * @return array Gateway information
     */
    public function getGatewayForTransaction($transactionData)
    {
        $currency = $transactionData['currency'] ?? 'IDR';
        $country = $transactionData['country'] ?? 'ID';
        $amount = $transactionData['amount'] ?? 0;
        $channel = $transactionData['channel'] ?? 'web';
        
        // Get routing rules
        $rules = $this->getRoutingRules($currency, $country, $amount, $channel);
        
        // Sort by priority (highest first)
        usort($rules, function($a, $b) {
            return $b['priority'] - $a['priority'];
        });
        
        // Find matching rule
        foreach ($rules as $rule) {
            if ($this->evaluateRuleCondition($rule['rule_condition'], $transactionData)) {
                $gateway = $this->getGatewayById($rule['gateway_id']);
                if ($gateway && $gateway['is_active']) {
                    return $gateway;
                }
            }
        }
        
        // Default to Midtrans for Indonesia
        if ($country === 'ID' || $currency === 'IDR') {
            return $this->getGatewayByCode('midtrans');
        }
        
        // Default to Stripe for international
        return $this->getGatewayByCode('stripe');
    }
    
    /**
     * Get routing rules for a transaction
     * 
     * @param string $currency Currency code
     * @param string $country Country code
     * @param float $amount Transaction amount
     * @param string $channel Channel type
     * @return array Routing rules
     */
    private function getRoutingRules($currency, $country, $amount, $channel)
    {
        $sql = "SELECT * FROM payment_gateway_routing_rules WHERE is_active = 1";
        
        $result = $this->db->query($sql);
        
        $rules = [];
        if ($result) {
            while ($row = $result->fetch_assoc()) {
                $rules[] = $row;
            }
        }
        
        return $rules;
    }
    
    /**
     * Evaluate rule condition
     * 
     * @param string $condition Condition JSON
     * @param array $transactionData Transaction data
     * @return bool Whether condition matches
     */
    private function evaluateRuleCondition($condition, $transactionData)
    {
        $conditionData = json_decode($condition, true);
        
        foreach ($conditionData as $key => $value) {
            if (isset($transactionData[$key])) {
                if ($transactionData[$key] != $value) {
                    return false;
                }
            }
        }
        
        return true;
    }
    
    /**
     * Get gateway by ID
     * 
     * @param int $gatewayId Gateway ID
     * @return array|null Gateway information
     */
    private function getGatewayById($gatewayId)
    {
        $sql = "SELECT * FROM payment_gateways WHERE id = ? LIMIT 1";
        
        $result = $this->db->query($sql, [$gatewayId]);
        
        if ($result && $result->num_rows > 0) {
            return $result->fetch_assoc();
        }
        
        return null;
    }
    
    /**
     * Get gateway by code
     * 
     * @param string $gatewayCode Gateway code
     * @return array|null Gateway information
     */
    private function getGatewayByCode($gatewayCode)
    {
        $sql = "SELECT * FROM payment_gateways WHERE gateway_code = ? LIMIT 1";
        
        $result = $this->db->query($sql, [$gatewayCode]);
        
        if ($result && $result->num_rows > 0) {
            return $result->fetch_assoc();
        }
        
        return null;
    }
    
    /**
     * Create payment intent
     * 
     * @param array $paymentData Payment data
     * @return array Payment intent response
     */
    public function createPaymentIntent($paymentData)
    {
        $gateway = $this->getGatewayForTransaction($paymentData);
        
        if (!$gateway) {
            return [
                'success' => false,
                'error' => 'No suitable payment gateway found'
            ];
        }
        
        // Route to specific gateway handler
        switch ($gateway['gateway_code']) {
            case 'stripe':
                return $this->createStripePaymentIntent($paymentData, $gateway);
            case 'paypal':
                return $this->createPayPalOrder($paymentData, $gateway);
            case 'midtrans':
                return $this->createMidtransPayment($paymentData, $gateway);
            default:
                return [
                    'success' => false,
                    'error' => 'Unsupported payment gateway'
                ];
        }
    }
    
    /**
     * Create Stripe payment intent
     * 
     * @param array $paymentData Payment data
     * @param array $gateway Gateway information
     * @return array Payment intent response
     */
    private function createStripePaymentIntent($paymentData, $gateway)
    {
        $apiConfig = json_decode($gateway['api_config'], true);
        $apiKey = $apiConfig['api_key'] ?? '';
        
        if (empty($apiKey)) {
            return [
                'success' => false,
                'error' => 'Stripe API key not configured'
            ];
        }
        
        $amount = $paymentData['amount'];
        $currency = strtolower($paymentData['currency']);
        
        // Create payment intent via Stripe API
        $url = 'https://api.stripe.com/v1/payment_intents';
        
        $data = [
            'amount' => $amount * 100, // Stripe uses cents
            'currency' => $currency,
            'metadata' => [
                'booking_id' => $paymentData['booking_id'] ?? '',
                'user_id' => $paymentData['user_id'] ?? ''
            ]
        ];
        
        // Add 3DS requirement for high-risk transactions
        if ($paymentData['risk_score'] > 50) {
            $data['payment_method_types'] = ['card'];
            $data['payment_method_options'] = [
                'card' => [
                    'request_three_d_secure' => 'any'
                ]
            ];
        }
        
        $response = $this->makeStripeRequest($url, $apiKey, $data);
        
        if ($response === null) {
            return [
                'success' => false,
                'error' => 'Failed to create Stripe payment intent'
            ];
        }
        
        $responseData = json_decode($response, true);
        
        // Log payment transaction
        $this->logPaymentTransaction([
            'gateway_id' => $gateway['id'],
            'gateway_payment_id' => $responseData['id'],
            'amount' => $amount,
            'currency' => strtoupper($currency),
            'status' => 'pending',
            'gateway_status' => $responseData['status'],
            '3ds_required' => isset($responseData['next_action']),
            'payment_method_type' => 'card'
        ]);
        
        return [
            'success' => true,
            'client_secret' => $responseData['client_secret'],
            'payment_intent_id' => $responseData['id'],
            'gateway' => 'stripe'
        ];
    }
    
    /**
     * Create PayPal order
     * 
     * @param array $paymentData Payment data
     * @param array $gateway Gateway information
     * @return array Order response
     */
    private function createPayPalOrder($paymentData, $gateway)
    {
        $apiConfig = json_decode($gateway['api_config'], true);
        $clientId = $apiConfig['client_id'] ?? '';
        $clientSecret = $apiConfig['client_secret'] ?? '';
        
        if (empty($clientId) || empty($clientSecret)) {
            return [
                'success' => false,
                'error' => 'PayPal API credentials not configured'
            ];
        }
        
        $amount = $paymentData['amount'];
        $currency = strtoupper($paymentData['currency']);
        
        // Get access token
        $token = $this->getPayPalAccessToken($clientId, $clientSecret);
        
        if ($token === null) {
            return [
                'success' => false,
                'error' => 'Failed to get PayPal access token'
            ];
        }
        
        // Create order
        $url = 'https://api-m.paypal.com/v2/checkout/orders';
        
        $data = [
            'intent' => 'CAPTURE',
            'purchase_units' => [
                [
                    'amount' => [
                        'currency_code' => $currency,
                        'value' => number_format($amount, 2, '.', '')
                    ]
                ]
            ]
        ];
        
        $response = $this->makePayPalRequest($url, $token, $data);
        
        if ($response === null) {
            return [
                'success' => false,
                'error' => 'Failed to create PayPal order'
            ];
        }
        
        $responseData = json_decode($response, true);
        
        // Log payment transaction
        $this->logPaymentTransaction([
            'gateway_id' => $gateway['id'],
            'gateway_payment_id' => $responseData['id'],
            'amount' => $amount,
            'currency' => $currency,
            'status' => 'pending',
            'gateway_status' => 'CREATED',
            'payment_method_type' => 'wallet',
            'wallet_type' => 'paypal'
        ]);
        
        return [
            'success' => true,
            'order_id' => $responseData['id'],
            'gateway' => 'paypal'
        ];
    }
    
    /**
     * Create Midtrans payment
     * 
     * @param array $paymentData Payment data
     * @param array $gateway Gateway information
     * @return array Payment response
     */
    private function createMidtransPayment($paymentData, $gateway)
    {
        $apiConfig = json_decode($gateway['api_config'], true);
        $serverKey = $apiConfig['server_key'] ?? '';
        
        if (empty($serverKey)) {
            return [
                'success' => false,
                'error' => 'Midtrans server key not configured'
            ];
        }
        
        $amount = $paymentData['amount'];
        $orderId = $paymentData['order_id'] ?? 'ORDER-' . time();
        
        // Midtrans uses Snap API for frontend integration
        $transactionDetails = [
            'order_id' => $orderId,
            'gross_amount' => $amount
        ];
        
        $customerDetails = [
            'first_name' => $paymentData['first_name'] ?? '',
            'last_name' => $paymentData['last_name'] ?? '',
            'email' => $paymentData['email'] ?? '',
            'phone' => $paymentData['phone'] ?? ''
        ];
        
        // Generate Snap token
        $snapToken = $this->generateMidtransSnapToken($transactionDetails, $customerDetails, $serverKey);
        
        if ($snapToken === null) {
            return [
                'success' => false,
                'error' => 'Failed to generate Midtrans Snap token'
            ];
        }
        
        // Log payment transaction
        $this->logPaymentTransaction([
            'gateway_id' => $gateway['id'],
            'gateway_payment_id' => $orderId,
            'amount' => $amount,
            'currency' => 'IDR',
            'status' => 'pending',
            'gateway_status' => 'pending',
            'payment_method_type' => 'card'
        ]);
        
        return [
            'success' => true,
            'snap_token' => $snapToken,
            'order_id' => $orderId,
            'gateway' => 'midtrans'
        ];
    }
    
    /**
     * Make Stripe API request
     * 
     * @param string $url API URL
     * @param string $apiKey API key
     * @param array $data Request data
     * @return string|null Response or null if failed
     */
    private function makeStripeRequest($url, $apiKey, $data)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Authorization: Bearer ' . $apiKey,
            'Content-Type: application/x-www-form-urlencoded'
        ]);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        if ($httpCode !== 200) {
            return null;
        }
        
        return $response;
    }
    
    /**
     * Get PayPal access token
     * 
     * @param string $clientId Client ID
     * @param string $clientSecret Client secret
     * @return string|null Access token or null if failed
     */
    private function getPayPalAccessToken($clientId, $clientSecret)
    {
        $url = 'https://api-m.paypal.com/v1/oauth2/token';
        
        $auth = base64_encode($clientId . ':' . $clientSecret);
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, 'grant_type=client_credentials');
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Authorization: Basic ' . $auth,
            'Content-Type: application/x-www-form-urlencoded'
        ]);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        if ($httpCode !== 200) {
            return null;
        }
        
        $data = json_decode($response, true);
        
        return $data['access_token'] ?? null;
    }
    
    /**
     * Make PayPal API request
     * 
     * @param string $url API URL
     * @param string $token Access token
     * @param array $data Request data
     * @return string|null Response or null if failed
     */
    private function makePayPalRequest($url, $token, $data)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Authorization: Bearer ' . $token,
            'Content-Type: application/json'
        ]);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        if ($httpCode !== 200 && $httpCode !== 201) {
            return null;
        }
        
        return $response;
    }
    
    /**
     * Generate Midtrans Snap token
     * 
     * @param array $transactionDetails Transaction details
     * @param array $customerDetails Customer details
     * @param string $serverKey Server key
     * @return string|null Snap token or null if failed
     */
    private function generateMidtransSnapToken($transactionDetails, $customerDetails, $serverKey)
    {
        // This would integrate with Midtrans Snap API
        // For now, return a placeholder
        return 'snap_token_placeholder_' . time();
    }
    
    /**
     * Log payment transaction
     * 
     * @param array $transactionData Transaction data
     * @return int Transaction ID
     */
    private function logPaymentTransaction($transactionData)
    {
        $sql = "INSERT INTO payment_transactions 
                (gateway_id, gateway_payment_id, amount, currency, status, gateway_status, 3ds_required, payment_method_type, wallet_type, card_last4, card_brand, risk_score) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        
        $params = [
            $transactionData['gateway_id'],
            $transactionData['gateway_payment_id'],
            $transactionData['amount'],
            $transactionData['currency'],
            $transactionData['status'],
            $transactionData['gateway_status'],
            $transactionData['3ds_required'] ?? 0,
            $transactionData['payment_method_type'],
            $transactionData['wallet_type'] ?? null,
            $transactionData['card_last4'] ?? null,
            $transactionData['card_brand'] ?? null,
            $transactionData['risk_score'] ?? null
        ];
        
        $this->db->query($sql, $params);
        
        return $this->db->insert_id;
    }
    
    /**
     * Handle webhook from payment gateway
     * 
     * @param string $gatewayCode Gateway code
     * @param array $webhookData Webhook data
     * @return array Handling result
     */
    public function handleWebhook($gatewayCode, $webhookData)
    {
        $gateway = $this->getGatewayByCode($gatewayCode);
        
        if (!$gateway) {
            return [
                'success' => false,
                'error' => 'Unknown gateway'
            ];
        }
        
        // Log webhook
        $this->logWebhook($gateway['id'], $webhookData);
        
        // Route to specific webhook handler
        switch ($gatewayCode) {
            case 'stripe':
                return $this->handleStripeWebhook($webhookData, $gateway);
            case 'paypal':
                return $this->handlePayPalWebhook($webhookData, $gateway);
            case 'midtrans':
                return $this->handleMidtransWebhook($webhookData, $gateway);
            default:
                return [
                    'success' => false,
                    'error' => 'Unsupported gateway webhook'
                ];
        }
    }
    
    /**
     * Handle Stripe webhook
     * 
     * @param array $webhookData Webhook data
     * @param array $gateway Gateway information
     * @return array Handling result
     */
    private function handleStripeWebhook($webhookData, $gateway)
    {
        $eventType = $webhookData['type'] ?? '';
        $paymentIntentId = $webhookData['data']['object']['id'] ?? '';
        
        // Log webhook
        $this->logWebhook($gateway['id'], $webhookData, $eventType, $paymentIntentId);
        
        switch ($eventType) {
            case 'payment_intent.succeeded':
                return $this->handlePaymentSuccess($paymentIntentId, $gateway['id'], 'stripe');
            case 'payment_intent.payment_failed':
                return $this->handlePaymentFailure($paymentIntentId, $gateway['id'], 'stripe');
            case 'charge.dispute.created':
                return $this->handleDisputeCreated($webhookData, $gateway['id']);
            default:
                return [
                    'success' => true,
                    'message' => 'Webhook received but not processed'
                ];
        }
    }
    
    /**
     * Handle PayPal webhook
     * 
     * @param array $webhookData Webhook data
     * @param array $gateway Gateway information
     * @return array Handling result
     */
    private function handlePayPalWebhook($webhookData, $gateway)
    {
        $eventType = $webhookData['event_type'] ?? '';
        $orderId = $webhookData['resource']['id'] ?? '';
        
        // Log webhook
        $this->logWebhook($gateway['id'], $webhookData, $eventType, $orderId);
        
        switch ($eventType) {
            case 'PAYMENT.CAPTURE.COMPLETED':
                return $this->handlePaymentSuccess($orderId, $gateway['id'], 'paypal');
            case 'PAYMENT.CAPTURE.DENIED':
                return $this->handlePaymentFailure($orderId, $gateway['id'], 'paypal');
            default:
                return [
                    'success' => true,
                    'message' => 'Webhook received but not processed'
                ];
        }
    }
    
    /**
     * Handle Midtrans webhook
     * 
     * @param array $webhookData Webhook data
     * @param array $gateway Gateway information
     * @return array Handling result
     */
    private function handleMidtransWebhook($webhookData, $gateway)
    {
        $transactionStatus = $webhookData['transaction_status'] ?? '';
        $orderId = $webhookData['order_id'] ?? '';
        
        // Log webhook
        $this->logWebhook($gateway['id'], $webhookData, $transactionStatus, $orderId);
        
        switch ($transactionStatus) {
            case 'settlement':
                return $this->handlePaymentSuccess($orderId, $gateway['id'], 'midtrans');
            case 'deny':
            case 'expire':
            case 'cancel':
                return $this->handlePaymentFailure($orderId, $gateway['id'], 'midtrans');
            default:
                return [
                    'success' => true,
                    'message' => 'Webhook received but not processed'
                ];
        }
    }
    
    /**
     * Handle payment success
     * 
     * @param string $gatewayPaymentId Gateway payment ID
     * @param int $gatewayId Gateway ID
     * @param string $gatewayCode Gateway code
     * @return array Handling result
     */
    private function handlePaymentSuccess($gatewayPaymentId, $gatewayId, $gatewayCode)
    {
        // Update payment transaction status
        $sql = "UPDATE payment_transactions 
                SET status = 'completed', gateway_status = 'succeeded', updated_at = NOW() 
                WHERE gateway_payment_id = ? AND gateway_id = ?";
        
        $this->db->query($sql, [$gatewayPaymentId, $gatewayId]);
        
        // Get transaction details
        $sql = "SELECT * FROM payment_transactions WHERE gateway_payment_id = ? AND gateway_id = ? LIMIT 1";
        $result = $this->db->query($sql, [$gatewayPaymentId, $gatewayId]);
        
        if ($result && $result->num_rows > 0) {
            $transaction = $result->fetch_assoc();
            
            // Update booking status if linked
            if (isset($transaction['booking_id'])) {
                $this->updateBookingStatus($transaction['booking_id'], 'confirmed');
            }
            
            return [
                'success' => true,
                'message' => 'Payment processed successfully',
                'transaction_id' => $transaction['id']
            ];
        }
        
        return [
            'success' => false,
            'error' => 'Transaction not found'
        ];
    }
    
    /**
     * Handle payment failure
     * 
     * @param string $gatewayPaymentId Gateway payment ID
     * @param int $gatewayId Gateway ID
     * @param string $gatewayCode Gateway code
     * @return array Handling result
     */
    private function handlePaymentFailure($gatewayPaymentId, $gatewayId, $gatewayCode)
    {
        // Update payment transaction status
        $sql = "UPDATE payment_transactions 
                SET status = 'failed', gateway_status = 'failed', updated_at = NOW() 
                WHERE gateway_payment_id = ? AND gateway_id = ?";
        
        $this->db->query($sql, [$gatewayPaymentId, $gatewayId]);
        
        // Get transaction details
        $sql = "SELECT * FROM payment_transactions WHERE gateway_payment_id = ? AND gateway_id = ? LIMIT 1";
        $result = $this->db->query($sql, [$gatewayPaymentId, $gatewayId]);
        
        if ($result && $result->num_rows > 0) {
            $transaction = $result->fetch_assoc();
            
            // Update booking status if linked
            if (isset($transaction['booking_id'])) {
                $this->updateBookingStatus($transaction['booking_id'], 'payment_failed');
            }
            
            return [
                'success' => true,
                'message' => 'Payment failed',
                'transaction_id' => $transaction['id']
            ];
        }
        
        return [
            'success' => false,
            'error' => 'Transaction not found'
        ];
    }
    
    /**
     * Handle dispute created
     * 
     * @param array $webhookData Webhook data
     * @param int $gatewayId Gateway ID
     * @return array Handling result
     */
    private function handleDisputeCreated($webhookData, $gatewayId)
    {
        $disputeId = $webhookData['data']['object']['id'] ?? '';
        $amount = $webhookData['data']['object']['amount'] ?? 0;
        $currency = $webhookData['data']['object']['currency'] ?? 'USD';
        $reason = $webhookData['data']['object']['reason'] ?? '';
        
        // Get payment transaction ID
        $paymentIntentId = $webhookData['data']['object']['payment_intent'] ?? '';
        
        $sql = "SELECT id FROM payment_transactions WHERE gateway_payment_id = ? AND gateway_id = ? LIMIT 1";
        $result = $this->db->query($sql, [$paymentIntentId, $gatewayId]);
        
        $transactionId = null;
        if ($result && $result->num_rows > 0) {
            $row = $result->fetch_assoc();
            $transactionId = $row['id'];
        }
        
        // Log dispute
        $sql = "INSERT INTO payment_disputes 
                (payment_transaction_id, gateway_id, gateway_dispute_id, dispute_reason, dispute_status, dispute_amount, currency) 
                VALUES (?, ?, ?, ?, 'needs_response', ?, ?)";
        
        $this->db->query($sql, [
            $transactionId,
            $gatewayId,
            $disputeId,
            $reason,
            $amount / 100, // Stripe uses cents
            strtoupper($currency)
        ]);
        
        return [
            'success' => true,
            'message' => 'Dispute logged successfully'
        ];
    }
    
    /**
     * Log webhook
     * 
     * @param int $gatewayId Gateway ID
     * @param array $webhookData Webhook data
     * @param string $eventType Event type
     * @param string $webhookId Webhook ID
     * @return bool Success status
     */
    private function logWebhook($gatewayId, $webhookData, $eventType = '', $webhookId = '')
    {
        $sql = "INSERT INTO payment_gateway_webhook_logs 
                (gateway_id, webhook_id, event_type, payload) 
                VALUES (?, ?, ?, ?)";
        
        return $this->db->query($sql, [
            $gatewayId,
            $webhookId,
            $eventType,
            json_encode($webhookData)
        ]);
    }
    
    /**
     * Update booking status
     * 
     * @param int $bookingId Booking ID
     * @param string $status New status
     * @return bool Success status
     */
    private function updateBookingStatus($bookingId, $status)
    {
        $sql = "UPDATE bookings SET status = ?, updated_at = NOW() WHERE id = ?";
        
        return $this->db->query($sql, [$status, $bookingId]);
    }
    
    /**
     * Get all active payment gateways
     * 
     * @return array List of active gateways
     */
    public function getActiveGateways()
    {
        $sql = "SELECT * FROM payment_gateways WHERE is_active = 1 ORDER BY priority";
        
        $result = $this->db->query($sql);
        
        $gateways = [];
        if ($result) {
            while ($row = $result->fetch_assoc()) {
                $gateways[] = $row;
            }
        }
        
        return $gateways;
    }
    
    /**
     * Get supported payment methods for a gateway
     * 
     * @param string $gatewayCode Gateway code
     * @return array Supported payment methods
     */
    public function getSupportedPaymentMethods($gatewayCode)
    {
        $gateway = $this->getGatewayByCode($gatewayCode);
        
        if (!$gateway) {
            return [];
        }
        
        $supportedCurrencies = json_decode($gateway['supported_currencies'], true);
        
        // Return payment methods based on gateway capabilities
        $methods = [];
        
        if ($gateway['supports_3ds']) {
            $methods[] = [
                'type' => 'card',
                'name' => 'Credit/Debit Card',
                'supports_3ds' => true
            ];
        }
        
        if ($gateway['gateway_code'] === 'paypal') {
            $methods[] = [
                'type' => 'wallet',
                'name' => 'PayPal',
                'wallet_type' => 'paypal'
            ];
        }
        
        if ($gateway['gateway_code'] === 'stripe') {
            $methods[] = [
                'type' => 'wallet',
                'name' => 'Apple Pay',
                'wallet_type' => 'apple_pay'
            ];
            $methods[] = [
                'type' => 'wallet',
                'name' => 'Google Pay',
                'wallet_type' => 'google_pay'
            ];
        }
        
        return $methods;
    }
}
