<?php
/**
 * MyWisata Application - Currency Converter Helper
 * 
 * Handles currency conversion using exchange rate API.
 * 
 * @package MyWisata
 * @version 1.0.0
 * @since 2026-07-16
 */

class CurrencyConverter {
    
    private $apiKey;
    private $baseUrl = 'https://api.exchangerate-api.com/v4/latest';
    private $cacheFile;
    private $cacheExpiry = 3600; // 1 hour
    
    public function __construct() {
        $this->apiKey = getenv('EXCHANGE_RATE_API_KEY') ?: '';
        $this->cacheFile = ROOT_PATH . '/storage/cache/currency_rates.json';
    }
    
    /**
     * Convert amount from one currency to another
     * 
     * @param float $amount Amount to convert
     * @param string $from Source currency code (e.g., 'USD', 'IDR')
     * @param string $to Target currency code
     * @return float|false
     */
    public function convert($amount, $from, $to) {
        if ($from === $to) {
            return $amount;
        }
        
        $rates = $this->getRates($from);
        
        if ($rates && isset($rates[$to])) {
            return $amount * $rates[$to];
        }
        
        return false;
    }
    
    /**
     * Get exchange rates for a base currency
     * 
     * @param string $base Base currency code
     * @return array|false
     */
    public function getRates($base = 'USD') {
        // Try cache first
        $cached = $this->getCachedRates($base);
        if ($cached) {
            return $cached;
        }
        
        // Fetch from API
        $rates = $this->fetchRates($base);
        
        if ($rates) {
            $this->cacheRates($base, $rates);
            return $rates;
        }
        
        // Return mock rates if API fails
        return $this->getMockRates($base);
    }
    
    /**
     * Fetch rates from API
     * 
     * @param string $base Base currency code
     * @return array|false
     */
    private function fetchRates($base) {
        $url = "{$this->baseUrl}/{$base}";
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        if ($httpCode === 200 && $response) {
            $data = json_decode($response, true);
            return $data['rates'] ?? false;
        }
        
        return false;
    }
    
    /**
     * Get cached rates
     * 
     * @param string $base Base currency code
     * @return array|false
     */
    private function getCachedRates($base) {
        if (!file_exists($this->cacheFile)) {
            return false;
        }
        
        $cache = json_decode(file_get_contents($this->cacheFile), true);
        
        if (!$cache || $cache['base'] !== $base) {
            return false;
        }
        
        if (time() - $cache['timestamp'] > $this->cacheExpiry) {
            return false;
        }
        
        return $cache['rates'];
    }
    
    /**
     * Cache rates
     * 
     * @param string $base Base currency code
     * @param array $rates Exchange rates
     * @return bool
     */
    private function cacheRates($base, $rates) {
        $cache = [
            'base' => $base,
            'rates' => $rates,
            'timestamp' => time()
        ];
        
        $cacheDir = dirname($this->cacheFile);
        if (!is_dir($cacheDir)) {
            mkdir($cacheDir, 0755, true);
        }
        
        return file_put_contents($this->cacheFile, json_encode($cache)) !== false;
    }
    
    /**
     * Get mock rates (for testing when API fails)
     * 
     * @param string $base Base currency code
     * @return array
     */
    private function getMockRates($base) {
        $mockRates = [
            'USD' => [
                'IDR' => 15000,
                'EUR' => 0.85,
                'GBP' => 0.73,
                'JPY' => 110,
                'SGD' => 1.35,
                'MYR' => 4.5,
                'AUD' => 1.5,
                'CNY' => 7.2,
                'KRW' => 1200,
                'THB' => 35
            ],
            'IDR' => [
                'USD' => 0.000067,
                'EUR' => 0.000057,
                'GBP' => 0.000049,
                'JPY' => 0.0073,
                'SGD' => 0.00009,
                'MYR' => 0.0003,
                'AUD' => 0.0001,
                'CNY' => 0.00048,
                'KRW' => 0.08,
                'THB' => 0.0023
            ]
        ];
        
        return $mockRates[$base] ?? $mockRates['USD'];
    }
    
    /**
     * Format currency amount
     * 
     * @param float $amount Amount
     * @param string $currency Currency code
     * @return string
     */
    public function format($amount, $currency = 'IDR') {
        $symbols = [
            'USD' => '$',
            'IDR' => 'Rp',
            'EUR' => '€',
            'GBP' => '£',
            'JPY' => '¥',
            'SGD' => 'S$',
            'MYR' => 'RM',
            'AUD' => 'A$',
            'CNY' => '¥',
            'KRW' => '₩',
            'THB' => '฿'
        ];
        
        $symbol = $symbols[$currency] ?? $currency;
        
        if ($currency === 'IDR') {
            return $symbol . number_format($amount, 0, ',', '.');
        }
        
        return $symbol . number_format($amount, 2);
    }
    
    /**
     * Get supported currencies
     * 
     * @return array
     */
    public function getSupportedCurrencies() {
        return [
            'USD' => ['name' => 'US Dollar', 'symbol' => '$', 'flag' => '🇺🇸'],
            'IDR' => ['name' => 'Indonesian Rupiah', 'symbol' => 'Rp', 'flag' => '🇮🇩'],
            'EUR' => ['name' => 'Euro', 'symbol' => '€', 'flag' => '🇪🇺'],
            'GBP' => ['name' => 'British Pound', 'symbol' => '£', 'flag' => '🇬🇧'],
            'JPY' => ['name' => 'Japanese Yen', 'symbol' => '¥', 'flag' => '🇯🇵'],
            'SGD' => ['name' => 'Singapore Dollar', 'symbol' => 'S$', 'flag' => '🇸🇬'],
            'MYR' => ['name' => 'Malaysian Ringgit', 'symbol' => 'RM', 'flag' => '🇲🇾'],
            'AUD' => ['name' => 'Australian Dollar', 'symbol' => 'A$', 'flag' => '🇦🇺'],
            'CNY' => ['name' => 'Chinese Yuan', 'symbol' => '¥', 'flag' => '🇨🇳'],
            'KRW' => ['name' => 'South Korean Won', 'symbol' => '₩', 'flag' => '🇰🇷'],
            'THB' => ['name' => 'Thai Baht', 'symbol' => '฿', 'flag' => '🇹🇭']
        ];
    }
    
    /**
     * Convert and format
     * 
     * @param float $amount Amount
     * @param string $from Source currency
     * @param string $to Target currency
     * @return string
     */
    public function convertAndFormat($amount, $from, $to) {
        $converted = $this->convert($amount, $from, $to);
        if ($converted === false) {
            return false;
        }
        
        return $this->format($converted, $to);
    }
}
