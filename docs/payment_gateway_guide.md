# PAYMENT GATEWAY INTEGRATION GUIDE
# Tour Guide Application

## OVERVIEW

This guide provides comprehensive instructions for integrating and using the Midtrans payment gateway in the Tour Guide Application.

## PAYMENT GATEWAY

### Midtrans
Midtrans is a popular payment gateway in Indonesia that supports multiple payment methods:
- Credit cards
- Bank transfers (BCA, BNI, BRI VA)
- E-wallets (GoPay, OVO, Dana)
- QRIS payments
- Convenience stores (Alfamart, Indomaret)

## SETUP

### 1. Register Midtrans Account
1. Go to [Midtrans Dashboard](https://dashboard.midtrans.com/)
2. Register for a sandbox account
3. Get your Server Key and Client Key from the dashboard
4. Configure webhook URL in Midtrans settings

### 2. Configure Environment Variables
Add the following to your `.env` file:
```env
MIDTRANS_SERVER_KEY=SB-Mid-server-xxxxx
MIDTRANS_CLIENT_KEY=SB-Mid-client-xxxxx
MIDTRANS_IS_PRODUCTION=false
```

### 3. Run Database Migration
Execute the migration script to add payment fields:
```bash
mysql -u root -p mywisata < database/migrations/add_payment_fields.sql
```

## PAYMENT FLOW

### Step-by-Step Process
1. User creates booking/ticket
2. Transaction record created in database
3. User redirected to payment page
4. Payment token created via Midtrans API
5. Midtrans Snap popup opens
6. User selects payment method and completes payment
7. Midtrans sends webhook notification
8. Application updates transaction status
9. Related booking/ticket status updated
10. User redirected to success/failed page

## PAYMENT SERVICE

### PaymentService Class
Location: `app/services/PaymentService.php`

#### Methods
- `createSnapToken()` - Create payment token for Midtrans Snap
- `getTransactionStatus()` - Get transaction status from Midtrans
- `cancelTransaction()` - Cancel a transaction
- `handleNotification()` - Handle webhook notifications

### Usage Example
```php
$paymentService = new PaymentService();

// Create payment token
$result = $paymentService->createSnapToken(
    $transactionDetails,
    $customerDetails,
    $itemDetails
);

if ($result['success']) {
    $token = $result['token'];
    // Use token to open Midtrans Snap
}
```

## PAYMENT CONTROLLER

### PaymentController Class
Location: `app/controllers/PaymentController.php`

#### Endpoints
- `GET /payment/{id}` - Show payment page
- `POST /payment/create-token` - Create payment token
- `POST /payment/process-manual` - Process manual payment
- `POST /payment/notification` - Handle webhook notification
- `GET /payment/callback-finish` - Handle payment finish callback
- `GET /payment/callback-unfinish` - Handle payment unfinish callback
- `GET /payment/callback-error` - Handle payment error callback

## WEBHOOK SETUP

### Configure Webhook URL
1. Go to Midtrans Dashboard
2. Navigate to Settings > Notification
3. Add webhook URL: `https://yourdomain.com/payment/notification`
4. Save settings

### Webhook Security
The webhook handler validates the signature to ensure the notification is from Midtrans:
```php
$signatureKey = hash('sha512', 
    $orderId . $statusCode . 
    $grossAmount . $serverKey
);
```

## PAYMENT STATUS

### Status Mapping
- `capture` - Payment captured (check fraud status)
- `settlement` - Payment settled (success)
- `pending` - Payment pending
- `deny` - Payment denied
- `expire` - Payment expired
- `cancel` - Payment cancelled
- `challenge` - Payment under fraud review

### Application Status
- `paid` - Payment successful
- `pending_payment` - Payment pending
- `payment_failed` - Payment failed
- `expired` - Payment expired
- `cancelled` - Payment cancelled
- `challenge` - Payment under review

## TESTING

### Sandbox Environment
Use sandbox environment for testing:
```env
MIDTRANS_IS_PRODUCTION=false
```

### Test Cards
- Visa: 4911 1111 1111 1113
- MasterCard: 5111 1111 1111 1118
- 3D Secure: 4000 0012 3456 7890

### Test Scenarios
1. Successful payment
2. Failed payment
3. Pending payment
4. Cancelled payment
5. Expired payment
6. Webhook handling
7. Status updates

## SECURITY CONSIDERATIONS

### Server Key Protection
- Never expose server key in frontend
- Use environment variables
- Restrict API access

### Webhook Security
- Validate signature
- Use HTTPS
- Verify notification source

### Transaction Security
- Use unique order IDs
- Implement idempotency
- Log all transactions

### Data Protection
- Encrypt sensitive data
- Comply with PCI DSS
- Secure customer data

## TROUBLESHOOTING

### Payment Token Creation Failed
- Check server key is correct
- Verify API URL (sandbox vs production)
- Check network connectivity
- Review Midtrans dashboard for errors

### Webhook Not Received
- Verify webhook URL is correct
- Check firewall settings
- Ensure HTTPS is enabled
- Review Midtrans dashboard logs

### Status Not Updated
- Check webhook handler logs
- Verify signature validation
- Review database connection
- Check transaction ID mapping

## PRODUCTION DEPLOYMENT

### Pre-Deployment Checklist
- [ ] Update environment variables with production keys
- [ ] Set `MIDTRANS_IS_PRODUCTION=true`
- [ ] Configure production webhook URL
- [ ] Test production environment
- [ ] Enable HTTPS
- [ ] Review security settings
- [ ] Set up monitoring
- [ ] Configure error alerts

### Monitoring
- Monitor payment success rate
- Track webhook failures
- Monitor transaction status
- Set up error alerts
- Review payment logs regularly

## RESOURCES

- [Midtrans Documentation](https://docs.midtrans.com/)
- [Midtrans API Reference](https://api-docs.midtrans.com/)
- [Midtrans Dashboard](https://dashboard.midtrans.com/)
- [PCI DSS Compliance](https://www.pcisecuritystandards.org/)

---

**Version:** 1.0  
**Last Updated:** 2026-07-18  
**Status:** Active
