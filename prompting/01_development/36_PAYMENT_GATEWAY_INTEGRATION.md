# PAYMENT GATEWAY INTEGRATION
# Module 36 - Payment Gateway Integration for Tour Guide Application

## OVERVIEW

This prompting template guides the AI through integrating payment gateway functionality into the Tour Guide Application to enable secure online payments for bookings and tickets.

## PAYMENT GATEWAY OPTIONS

### Recommended Gateways
1. **Midtrans** - Popular in Indonesia, supports various payment methods
2. **Stripe** - International, robust API
3. **PayPal** - Widely accepted
4. **Xendit** - Indonesia-focused, comprehensive
5. **Doku** - Indonesian payment gateway

### Recommended for This Project
**Midtrans** - Best for Indonesian market with:
- Credit card processing
- Bank transfer (VA)
- E-wallets (GoPay, OVO, Dana)
- QRIS payments
- Convenience stores (Alfamart, Indomaret)

## MIDTRANS INTEGRATION

### Server Key Configuration
```php
// config/payment.php
<?php
return [
    'midtrans' => [
        'server_key' => env('MIDTRANS_SERVER_KEY', ''),
        'client_key' => env('MIDTRANS_CLIENT_KEY', ''),
        'is_production' => env('MIDTRANS_IS_PRODUCTION', false),
        'is_sanitized' => true,
        'is_3ds' => true,
        'api_url' => env('MIDTRANS_IS_PRODUCTION', false) 
            ? 'https://app.midtrans.com/snap/v1' 
            : 'https://app.sandbox.midtrans.com/snap/v1',
    ]
];
```

### Payment Service Class
```php
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
        curl_close($ch);

        $result = json_decode($response, true);

        if (isset($result['token'])) {
            return [
                'success' => true,
                'token' => $result['token'],
                'redirect_url' => $result['redirect_url']
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
        $fraudStatus = $notification['fraud_status'];

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
                    // Challenge
                    return $this->handleChallenge($orderId);
                } else if ($fraudStatus === 'accept') {
                    // Success
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
            
            // Send notification
            $notificationService = new \App\Services\NotificationService();
            $notificationService->sendPaymentSuccess($booking['user_id'], $booking);
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
```

### Payment Controller
```php
<?php
namespace App\Controllers;

use App\Services\PaymentService;
use App\Models\Booking;
use App\Models\Transaction;

class PaymentController extends Controller
{
    private $paymentService;

    public function __construct()
    {
        $this->paymentService = new PaymentService();
    }

    /**
     * Show payment page
     */
    public function index($bookingId)
    {
        $bookingModel = new Booking();
        $booking = $bookingModel->find($bookingId);

        if (!$booking || $booking['user_id'] != $_SESSION['user_id']) {
            $_SESSION['error'] = 'Booking not found';
            return $this->redirect('/bookings');
        }

        return $this->view('payment/index', [
            'booking' => $booking,
            'clientKey' => $this->paymentService->clientKey
        ]);
    }

    /**
     * Create payment token
     */
    public function createToken()
    {
        $bookingId = $_POST['booking_id'];
        
        $bookingModel = new Booking();
        $booking = $bookingModel->find($bookingId);

        if (!$booking) {
            return $this->json(['success' => false, 'error' => 'Booking not found']);
        }

        // Transaction details
        $transactionDetails = [
            'order_id' => 'BOOK-' . $booking['id'] . '-' . time(),
            'gross_amount' => $booking['total_price']
        ];

        // Customer details
        $userModel = new \App\Models\User();
        $user = $userModel->find($booking['user_id']);

        $customerDetails = [
            'first_name' => explode(' ', $user['name'])[0],
            'last_name' => implode(' ', array_slice(explode(' ', $user['name']), 1)),
            'email' => $user['email'],
            'phone' => $user['phone'] ?? ''
        ];

        // Item details
        $itemDetails = [
            [
                'id' => 'BOOKING-' . $booking['id'],
                'price' => $booking['total_price'],
                'quantity' => 1,
                'name' => 'Tour Guide Booking - ' . $booking['tour_guide_name']
            ]
        ];

        // Create Snap token
        $result = $this->paymentService->createSnapToken(
            $transactionDetails,
            $customerDetails,
            $itemDetails
        );

        if ($result['success']) {
            // Update booking with order ID
            $bookingModel->updateOrderId($booking['id'], $transactionDetails['order_id']);
            
            // Create transaction record
            $transactionModel = new Transaction();
            $transactionModel->create([
                'booking_id' => $booking['id'],
                'order_id' => $transactionDetails['order_id'],
                'amount' => $booking['total_price'],
                'status' => 'pending',
                'payment_method' => null
            ]);

            return $this->json($result);
        }

        return $this->json(['success' => false, 'error' => 'Failed to create payment token']);
    }

    /**
     * Handle webhook notification
     */
    public function notification()
    {
        $notification = json_decode(file_get_contents('php://input'), true);

        if (!$notification) {
            http_response_code(400);
            exit;
        }

        $result = $this->paymentService->handleNotification($notification);

        if ($result['success']) {
            http_response_code(200);
            echo json_encode(['status' => 'ok']);
        } else {
            http_response_code(400);
            echo json_encode(['error' => $result['error']]);
        }
    }

    /**
     * Payment success page
     */
    public function success()
    {
        $orderId = $_GET['order_id'];
        
        $transactionModel = new Transaction();
        $transaction = $transactionModel->findByOrderId($orderId);

        if (!$transaction) {
            $_SESSION['error'] = 'Transaction not found';
            return $this->redirect('/bookings');
        }

        return $this->view('payment/success', [
            'transaction' => $transaction
        ]);
    }

    /**
     * Payment failed page
     */
    public function failed()
    {
        $orderId = $_GET['order_id'];
        
        $transactionModel = new Transaction();
        $transaction = $transactionModel->findByOrderId($orderId);

        return $this->view('payment/failed', [
            'transaction' => $transaction
        ]);
    }

    /**
     * Payment pending page
     */
    public function pending()
    {
        $orderId = $_GET['order_id'];
        
        $transactionModel = new Transaction();
        $transaction = $transactionModel->findByOrderId($orderId);

        return $this->view('payment/pending', [
            'transaction' => $transaction
        ]);
    }
}
```

### Payment View (payment/index.php)
```php
<?php $this->layout('layouts/header'); ?>

<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h4>Payment for Booking #<?= $booking['id'] ?></h4>
                </div>
                <div class="card-body">
                    <div class="booking-summary mb-4">
                        <h5>Booking Details</h5>
                        <table class="table">
                            <tr>
                                <td>Tour Guide:</td>
                                <td><?= htmlspecialchars($booking['tour_guide_name']) ?></td>
                            </tr>
                            <tr>
                                <td>Date:</td>
                                <td><?= date('F j, Y', strtotime($booking['date'])) ?></td>
                            </tr>
                            <tr>
                                <td>Duration:</td>
                                <td><?= $booking['duration'] ?> hours</td>
                            </tr>
                            <tr>
                                <td>Total Amount:</td>
                                <td><strong>IDR <?= number_format($booking['total_price'], 0, ',', '.') ?></strong></td>
                            </tr>
                        </table>
                    </div>

                    <button id="pay-button" class="btn btn-primary btn-lg btn-block">
                        Pay Now
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://app.sandbox.midtrans.com/snap/snap.js" 
        data-client-key="<?= $clientKey ?>"></script>
<script>
document.getElementById('pay-button').onclick = function() {
    fetch('/payment/create-token', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            booking_id: <?= $booking['id'] ?>
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            window.snap.pay(data.token, {
                onSuccess: function(result) {
                    window.location.href = '/payment/success?order_id=' + result.order_id;
                },
                onPending: function(result) {
                    window.location.href = '/payment/pending?order_id=' + result.order_id;
                },
                onError: function(result) {
                    window.location.href = '/payment/failed?order_id=' + result.order_id;
                }
            });
        } else {
            alert('Payment failed: ' + data.error);
        }
    });
};
</script>

<?php $this->layout('layouts/footer'); ?>
```

## DATABASE UPDATES

### Add to transactions table
```sql
ALTER TABLE transactions ADD COLUMN order_id VARCHAR(100) UNIQUE AFTER id;
ALTER TABLE transactions ADD COLUMN payment_method VARCHAR(50) AFTER status;
ALTER TABLE transactions ADD COLUMN payment_date DATETIME NULL AFTER payment_method;
ALTER TABLE transactions ADD COLUMN transaction_id VARCHAR(100) NULL AFTER payment_date;
```

## ROUTE CONFIGURATION

### Add to routes
```php
// Payment routes
$router->get('/payment/{id}', [PaymentController::class, 'index']);
$router->post('/payment/create-token', [PaymentController::class, 'createToken']);
$router->post('/payment/notification', [PaymentController::class, 'notification']);
$router->get('/payment/success', [PaymentController::class, 'success']);
$router->get('/payment/failed', [PaymentController::class, 'failed']);
$router->get('/payment/pending', [PaymentController::class, 'pending']);
```

## ENVIRONMENT VARIABLES

### Add to .env
```env
MIDTRANS_SERVER_KEY=your_server_key_here
MIDTRANS_CLIENT_KEY=your_client_key_here
MIDTRANS_IS_PRODUCTION=false
```

## SECURITY CONSIDERATIONS

1. **Server Key Protection**
   - Never expose server key in frontend
   - Use environment variables
   - Restrict API access

2. **Webhook Security**
   - Validate signature
   - Use HTTPS
   - Verify notification source

3. **Transaction Security**
   - Use unique order IDs
   - Implement idempotency
   - Log all transactions

4. **Data Protection**
   - Encrypt sensitive data
   - Comply with PCI DSS
   - Secure customer data

## TESTING

### Test Scenarios
1. Successful payment
2. Failed payment
3. Pending payment
4. Cancelled payment
5. Expired payment
6. Webhook handling
7. Refund processing

### Test Cards (Sandbox)
- Visa: 4911 1111 1111 1113
- MasterCard: 5111 1111 1111 1118
- 3D Secure: 4000 0012 3456 7890

## IMPLEMENTATION TASKS

### Phase 1: Setup
1. Register Midtrans account
2. Get API keys
3. Configure environment variables
4. Create payment config file
5. Update database schema

### Phase 2: Backend
1. Create PaymentService class
2. Create PaymentController
3. Implement token creation
4. Implement webhook handler
5. Update Booking model
6. Create Transaction model

### Phase 3: Frontend
1. Create payment views
2. Integrate Snap.js
3. Implement payment flow
4. Add success/failure pages
5. Add payment history

### Phase 4: Integration
1. Connect booking to payment
2. Implement status updates
3. Add notifications
4. Update booking flow
5. Test complete flow

### Phase 5: Testing
1. Test payment scenarios
2. Test webhook handling
3. Test error cases
4. Test security
5. Load testing

## DELIVERABLES

1. PaymentService class
2. PaymentController
3. Payment views
4. Database migration
5. Webhook handler
6. Payment configuration
7. Testing documentation
8. Integration guide

## ACCEPTANCE CRITERIA

- Payment gateway integrated
- Multiple payment methods supported
- Webhook handling working
- Transaction tracking complete
- Security measures implemented
- Error handling robust
- User-friendly payment flow
- Payment history accessible
- Testing complete
- Documentation updated

## NOTES

- Use sandbox for testing
- Never hardcode API keys
- Log all transactions
- Implement retry logic
- Monitor payment failures
- Keep payment records
- Regular security audits
- Comply with regulations

---

**Module:** 36_PAYMENT_GATEWAY_INTEGRATION  
**Priority:** MEDIUM  
**Status:** READY FOR DEVELOPMENT  
**Last Updated:** 2026-07-18
