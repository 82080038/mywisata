<?php
/**
 * MyWisata Application - Currency API Configuration
 * 
 * @package MyWisata
 * @version 1.0.0
 * @since 2026-07-18
 */

return [
    'provider' => getenv('CURRENCY_API_PROVIDER') ?: 'openexchangerates',
    
    'openexchangerates' => [
        'api_key' => getenv('OPENEXCHANGERATES_API_KEY') ?: '',
        'base_url' => 'https://api.openexchangerates.com',
        'endpoint' => '/latest.json',
        'cache_ttl' => getenv('CURRENCY_API_CACHE_TTL') ?: 3600
    ],
    
    'fixer' => [
        'api_key' => getenv('FIXER_API_KEY') ?: '',
        'base_url' => 'https://api.fixer.io',
        'endpoint' => '/latest',
        'cache_ttl' => getenv('CURRENCY_API_CACHE_TTL') ?: 3600
    ],
    
    'default_currency' => 'IDR',
    'supported_currencies' => ['IDR', 'USD', 'SGD', 'MYR', 'THB', 'EUR', 'GBP', 'JPY', 'CNY', 'AUD']
];
