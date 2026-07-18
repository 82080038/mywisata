<?php
return [
    'midtrans' => [
        'server_key' => getenv('MIDTRANS_SERVER_KEY') ?: 'SB-Mid-server-xxxxx',
        'client_key' => getenv('MIDTRANS_CLIENT_KEY') ?: 'SB-Mid-client-xxxxx',
        'merchant_id' => getenv('MIDTRANS_MERCHANT_ID') ?: '',
        'is_production' => getenv('MIDTRANS_IS_PRODUCTION') ?: false,
        'is_sanitized' => true,
        'is_3ds' => true,
        'api_url' => (getenv('MIDTRANS_IS_PRODUCTION') ?: false) 
            ? 'https://app.midtrans.com/snap/v1' 
            : 'https://app.sandbox.midtrans.com/snap/v1',
    ],
    
    'stripe' => [
        'secret_key' => getenv('STRIPE_SECRET_KEY') ?: '',
        'publishable_key' => getenv('STRIPE_PUBLISHABLE_KEY') ?: '',
        'webhook_secret' => getenv('STRIPE_WEBHOOK_SECRET') ?: '',
        'api_url' => 'https://api.stripe.com/v1',
    ],
    
    'paypal' => [
        'client_id' => getenv('PAYPAL_CLIENT_ID') ?: '',
        'client_secret' => getenv('PAYPAL_CLIENT_SECRET') ?: '',
        'mode' => getenv('PAYPAL_MODE') ?: 'sandbox',
        'api_url' => (getenv('PAYPAL_MODE') === 'live') 
            ? 'https://api-m.paypal.com' 
            : 'https://api-m.sandbox.paypal.com',
    ],
    
    'timeout_hours' => getenv('PAYMENT_TIMEOUT_HOURS') ?: 24
];
