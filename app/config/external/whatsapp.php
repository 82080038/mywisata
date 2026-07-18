<?php
/**
 * MyWisata Application - WhatsApp Business API Configuration
 * 
 * @package MyWisata
 * @version 1.0.0
 * @since 2026-07-18
 */

return [
    'access_token' => getenv('WHATSAPP_ACCESS_TOKEN') ?: '',
    'phone_number_id' => getenv('WHATSAPP_PHONE_NUMBER_ID') ?: '',
    'webhook_verify_token' => getenv('WHATSAPP_WEBHOOK_VERIFY_TOKEN') ?: '',
    'api_version' => getenv('WHATSAPP_API_VERSION') ?: 'v18.0',
    'api_url' => 'https://graph.facebook.com',
    
    'message_templates' => [
        'welcome' => [
            'id' => '',
            'name' => 'welcome_message',
            'language' => 'id'
        ],
        'booking_confirmation' => [
            'id' => '',
            'name' => 'booking_confirmation',
            'language' => 'id'
        ],
        'payment_reminder' => [
            'id' => '',
            'name' => 'payment_reminder',
            'language' => 'id'
        ]
    ],
    
    'rate_limit' => [
        'messages_per_minute' => 60,
        'messages_per_hour' => 1000
    ]
];
