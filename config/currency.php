<?php

/**
 * Currency Configuration
 * 
 * Configuration file for multi-currency support
 * 
 * @author MyWisata Team
 * @version 1.0
 */

return [
    // Base currency for the platform
    'base_currency' => 'IDR',
    
    // Default currency for new users
    'default_currency' => 'IDR',
    
    // Exchange rate API keys
    'api_keys' => [
        'open_exchange_rates' => env('OPEN_EXCHANGE_RATES_API_KEY', ''),
        'fixer' => env('FIXER_API_KEY', ''),
        'ecb' => '', // ECB doesn't require API key
    ],
    
    // Exchange rate update schedule (cron job)
    'update_schedule' => [
        'enabled' => true,
        'frequency' => 'daily', // daily, hourly
        'preferred_api' => 'open_exchange_rates', // fallback to other APIs if this fails
        'update_time' => '00:00', // Time to run daily update
    ],
    
    // Currency buffer settings (for margin protection)
    'buffer' => [
        'enabled' => true,
        'default_percentage' => 2.0, // 2% buffer by default
        'apply_to_display' => true, // Apply buffer to display prices
        'apply_to_settlement' => false, // Don't apply buffer to actual settlement
    ],
    
    // Currency formatting defaults
    'formatting' => [
        'default_decimal_places' => 2,
        'default_decimal_separator' => ',',
        'default_thousands_separator' => '.',
        'default_symbol_position' => 'before',
    ],
    
    // Supported currencies for each region
    'regional_currencies' => [
        'ID' => ['IDR'],
        'SG' => ['SGD'],
        'MY' => ['MYR'],
        'TH' => ['THB'],
        'VN' => ['VND'],
        'PH' => ['PHP'],
        'US' => ['USD'],
        'GB' => ['GBP'],
        'EU' => ['EUR'],
        'AU' => ['AUD'],
        'JP' => ['JPY'],
        'CN' => ['CNY'],
    ],
    
    // Auto-detection settings
    'auto_detection' => [
        'enabled' => true,
        'method' => 'ip_geolocation', // ip_geolocation, browser_language
        'fallback_currency' => 'IDR',
    ],
    
    // Currency conversion logging
    'logging' => [
        'enabled' => true,
        'log_all_conversions' => true,
        'log_contexts' => ['booking', 'payment', 'refund', 'display'],
    ],
    
    // Cache settings for exchange rates
    'cache' => [
        'enabled' => true,
        'ttl' => 3600, // Cache for 1 hour
        'redis_key_prefix' => 'exchange_rate:',
    ],
];
