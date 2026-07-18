<?php
return [
    'midtrans' => [
        'server_key' => getenv('MIDTRANS_SERVER_KEY') ?: 'SB-Mid-server-xxxxx',
        'client_key' => getenv('MIDTRANS_CLIENT_KEY') ?: 'SB-Mid-client-xxxxx',
        'is_production' => getenv('MIDTRANS_IS_PRODUCTION') ?: false,
        'is_sanitized' => true,
        'is_3ds' => true,
        'api_url' => (getenv('MIDTRANS_IS_PRODUCTION') ?: false) 
            ? 'https://app.midtrans.com/snap/v1' 
            : 'https://app.sandbox.midtrans.com/snap/v1',
    ]
];
