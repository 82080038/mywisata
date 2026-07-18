<?php
namespace App\Services;

class PaymentService
{
    private $serverKey;
    private $clientKey;
    private $isProduction;
    private $apiUrl;

    public function __construct()
    {
        $config = require __DIR__ . '/../config/payment.php';
        $this->serverKey = $config['midtrans']['server_key'];
        $this->clientKey = $config['midtrans']['client_key'];
        $this->isProduction = $config['midtrans']['is_production'];
        $this->apiUrl = $config['midtrans']['api_url'];
    }

    /**
     * Get client key for frontend
     */
    public function getClientKey()
    {
        return $this->clientKey;
    }

    /**
     * Create Snap token for payment
     */
    public function createSnapToken($transactionDetails, $customerDetails, $itemDetails)
    {
        $payload = [
            'transaction_details' => $transactionDetails,
            'customer_details' => $customerDetails,
            'item_details' => $itemDetails,
            'enabled_payments' => [
                'credit_card',
                'bca_va',
                'bni_va',
                'bri_va',
                'gopay',
                'ovo',
                'dana',
                'qris',
                'alfamart'
            ]
        ];

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->apiUrl . '/transactions');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Accept: application/json',
            'Authorization: Basic ' . base64_encode($this->serverKey . ':')
        ]);

        $response = curl_exec($ch);
        $error = curl_error($ch);
        curl_close($ch);

        if ($error) {
            return [
                'success' => false,
                'error' => $error
            ];
        }

        $result = json_decode($response, true);

        if (isset($result['token'])) {
            return [
                'success' => true,
                'token' => $result['token'],
                'redirect_url' => $result['redirect_url'] ?? null
            ];
        }

        return [
            'success' => false,
            'error' => $result
        ];
    }

    /**
     * Get transaction status
     */
    public function getTransactionStatus($orderId)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->apiUrl . '/' . $orderId . '/status');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Accept: application/json',
            'Authorization: Basic ' . base64_encode($this->serverKey . ':')
        ]);

        $response = curl_exec($ch);
        curl_close($ch);

        return json_decode($response, true);
    }

    /**
     * Cancel transaction
     */
    public function cancelTransaction($orderId)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->apiUrl . '/' . $orderId . '/cancel');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Accept: application/json',
            'Authorization: Basic ' . base64_encode($this->serverKey . ':')
        ]);

        $response = curl_exec($ch);
        curl_close($ch);

        return json_decode($response, true);
    }

    /**
     * Handle webhook notification
     */
    public function handleNotification($notification)
    {
        $orderId = $notification['order_id'];
        $transactionStatus = $notification['transaction_status'];
        $fraudStatus = $notification['fraud_status'] ?? null;

        // Validate signature
        $signatureKey = hash('sha512', 
            $orderId . $notification['status_code'] . 
            $notification['gross_amount'] . $this->serverKey
        );

        if ($signatureKey !== $notification['signature_key']) {
            return ['success' => false, 'error' => 'Invalid signature'];
        }

        // Process based on status
        switch ($transactionStatus) {
            case 'capture':
                if ($fraudStatus === 'challenge') {
                    return $this->handleChallenge($orderId);
                } else if ($fraudStatus === 'accept') {
                    return $this->handleSuccess($orderId);
                }
                break;
            case 'settlement':
                return $this->handleSuccess($orderId);
            case 'pending':
                return $this->handlePending($orderId);
            case 'deny':
                return $this->handleDeny($orderId);
            case 'expire':
                return $this->handleExpire($orderId);
            case 'cancel':
                return $this->handleCancel($orderId);
        }

        return ['success' => true];
    }

    private function handleSuccess($orderId)
    {
        // Update booking status to paid
        $bookingModel = new \App\Models\Booking();
        $booking = $bookingModel->findByOrderId($orderId);
        
        if ($booking) {
            $bookingModel->updateStatus($booking['id'], 'paid');
            
            // Update transaction
            $transactionModel = new \App\Models\Transaction();
            $transactionModel->updateByOrderId($orderId, [
                'status' => 'paid',
                'payment_date' => date('Y-m-d H:i:s')
            ]);
        }

        return ['success' => true];
    }

    private function handlePending($orderId)
    {
        $bookingModel = new \App\Models\Booking();
        $booking = $bookingModel->findByOrderId($orderId);
        
        if ($booking) {
            $bookingModel->updateStatus($booking['id'], 'pending_payment');
        }

        return ['success' => true];
    }

    private function handleDeny($orderId)
    {
        $bookingModel = new \App\Models\Booking();
        $booking = $bookingModel->findByOrderId($orderId);
        
        if ($booking) {
            $bookingModel->updateStatus($booking['id'], 'payment_failed');
        }

        return ['success' => true];
    }

    private function handleExpire($orderId)
    {
        $bookingModel = new \App\Models\Booking();
        $booking = $bookingModel->findByOrderId($orderId);
        
        if ($booking) {
            $bookingModel->updateStatus($booking['id'], 'expired');
        }

        return ['success' => true];
    }

    private function handleCancel($orderId)
    {
        $bookingModel = new \App\Models\Booking();
        $booking = $bookingModel->findByOrderId($orderId);
        
        if ($booking) {
            $bookingModel->updateStatus($booking['id'], 'cancelled');
        }

        return ['success' => true];
    }

    private function handleChallenge($orderId)
    {
        $bookingModel = new \App\Models\Booking();
        $booking = $bookingModel->findByOrderId($orderId);
        
        if ($booking) {
            $bookingModel->updateStatus($booking['id'], 'challenge');
        }

        return ['success' => true];
    }
}
