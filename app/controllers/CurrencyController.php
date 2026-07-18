<?php

/**
 * CurrencyController
 * 
 * Handles multi-currency operations including:
 * - Currency conversion
 * - Exchange rate management
 * - User currency preferences
 * - Currency formatting
 * 
 * @author MyWisata Team
 * @version 1.0
 */

class CurrencyController extends Controller
{
    protected $db;
    private $baseCurrency = 'IDR';
    
    // Exchange rate API endpoints
    private $exchangeRateApis = [
        'open_exchange_rates' => [
            'url' => 'https://openexchangerates.org/api/latest.json',
            'requires_api_key' => true
        ],
        'fixer' => [
            'url' => 'https://api.fixer.io/latest',
            'requires_api_key' => true
        ],
        'ecb' => [
            'url' => 'https://api.exchangeratesapi.io/latest',
            'requires_api_key' => false
        ]
    ];
    
    public function __construct()
    {
        $this->db = Database::getInstance();
    }
    
    /**
     * Get exchange rate between two currencies
     * 
     * @param string $fromCurrency Source currency code
     * @param string $toCurrency Target currency code
     * @param bool $useBuffer Whether to apply currency buffer
     * @return float Exchange rate
     */
    public function getExchangeRate($fromCurrency, $toCurrency, $useBuffer = true)
    {
        // If same currency, return 1
        if ($fromCurrency === $toCurrency) {
            return 1.0;
        }
        
        // Try to get from database first
        $rate = $this->getExchangeRateFromDB($fromCurrency, $toCurrency);
        
        if ($rate !== null) {
            // Apply buffer if enabled
            if ($useBuffer) {
                $buffer = $this->getCurrencyBuffer($fromCurrency, $toCurrency);
                $rate = $rate * (1 + ($buffer / 100));
            }
            
            return $rate;
        }
        
        // If not in database, try to fetch from API
        $rate = $this->fetchExchangeRateFromAPI($fromCurrency, $toCurrency);
        
        if ($rate !== null) {
            // Store in database
            $this->storeExchangeRate($fromCurrency, $toCurrency, $rate);
            
            // Apply buffer if enabled
            if ($useBuffer) {
                $buffer = $this->getCurrencyBuffer($fromCurrency, $toCurrency);
                $rate = $rate * (1 + ($buffer / 100));
            }
            
            return $rate;
        }
        
        // Return null if rate not found
        return null;
    }
    
    /**
     * Get exchange rate from database
     * 
     * @param string $fromCurrency Source currency code
     * @param string $toCurrency Target currency code
     * @return float|null Exchange rate or null if not found
     */
    private function getExchangeRateFromDB($fromCurrency, $toCurrency)
    {
        $sql = "SELECT rate FROM exchange_rates 
                WHERE from_currency = ? 
                AND to_currency = ? 
                AND effective_date <= NOW() 
                AND expires_at > NOW() 
                ORDER BY effective_date DESC 
                LIMIT 1";
        
        $result = $this->db->query($sql, [$fromCurrency, $toCurrency]);
        
        if ($result) {
            $rows = $result->fetchAll();
            if (!empty($rows)) {
                return (float) $rows[0]['rate'];
            }
        }
        
        return null;
    }
    
    /**
     * Fetch exchange rate from external API
     * 
     * @param string $fromCurrency Source currency code
     * @param string $toCurrency Target currency code
     * @return float|null Exchange rate or null if failed
     */
    private function fetchExchangeRateFromAPI($fromCurrency, $toCurrency)
    {
        // Try each API source
        foreach ($this->exchangeRateApis as $apiName => $apiConfig) {
            try {
                $rate = $this->fetchFromAPI($apiName, $fromCurrency, $toCurrency);
                if ($rate !== null) {
                    return $rate;
                }
            } catch (Exception $e) {
                // Log error and try next API
                error_log("Failed to fetch from {$apiName}: " . $e->getMessage());
                continue;
            }
        }
        
        return null;
    }
    
    /**
     * Fetch from specific API
     * 
     * @param string $apiName API name
     * @param string $fromCurrency Source currency code
     * @param string $toCurrency Target currency code
     * @return float|null Exchange rate or null if failed
     */
    private function fetchFromAPI($apiName, $fromCurrency, $toCurrency)
    {
        $apiConfig = $this->exchangeRateApis[$apiName];
        
        // Check if API key is required and available
        if ($apiConfig['requires_api_key']) {
            $apiKey = $this->getAPIKey($apiName);
            if (empty($apiKey)) {
                return null;
            }
        }
        
        // Build URL
        $url = $apiConfig['url'];
        if ($apiName === 'open_exchange_rates') {
            $url .= '?app_id=' . $apiKey . '&base=' . $fromCurrency;
        } elseif ($apiName === 'fixer') {
            $url .= '?access_key=' . $apiKey . '&base=' . $fromCurrency;
        } elseif ($apiName === 'ecb') {
            $url .= '?base=' . $fromCurrency;
        }
        
        // Make API request
        $response = $this->makeAPIRequest($url);
        
        if ($response === null) {
            return null;
        }
        
        $data = json_decode($response, true);
        
        // Extract rate based on API response format
        if (isset($data['rates'][$toCurrency])) {
            return (float) $data['rates'][$toCurrency];
        }
        
        return null;
    }
    
    /**
     * Make API request
     * 
     * @param string $url API URL
     * @return string|null Response or null if failed
     */
    private function makeAPIRequest($url)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
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
     * Get API key from configuration
     * 
     * @param string $apiName API name
     * @return string API key
     */
    private function getAPIKey($apiName)
    {
        // Get from config file or environment
        $configPath = __DIR__ . '/../../config/external/currency.php';
        if (file_exists($configPath)) {
            $config = require $configPath;
            return isset($config['api_keys'][$apiName]) ? $config['api_keys'][$apiName] : '';
        }
        
        return '';
    }
    
    /**
     * Store exchange rate in database
     * 
     * @param string $fromCurrency Source currency code
     * @param string $toCurrency Target currency code
     * @param float $rate Exchange rate
     * @return bool Success status
     */
    private function storeExchangeRate($fromCurrency, $toCurrency, $rate)
    {
        $effectiveDate = date('Y-m-d H:i:s');
        $expiresAt = date('Y-m-d H:i:s', strtotime('+24 hours'));
        
        $sql = "INSERT INTO exchange_rates 
                (from_currency, to_currency, rate, source, effective_date, expires_at) 
                VALUES (?, ?, ?, 'api', ?, ?)";
        
        return $this->db->query($sql, [$fromCurrency, $toCurrency, $rate, $effectiveDate, $expiresAt]);
    }
    
    /**
     * Get currency buffer percentage
     * 
     * @param string $fromCurrency Source currency code
     * @param string $toCurrency Target currency code
     * @return float Buffer percentage
     */
    private function getCurrencyBuffer($fromCurrency, $toCurrency)
    {
        $sql = "SELECT buffer_percentage FROM currency_buffer_settings 
                WHERE from_currency = ? 
                AND to_currency = ? 
                AND is_active = 1 
                LIMIT 1";
        
        $result = $this->db->query($sql, [$fromCurrency, $toCurrency]);
        
        if ($result) {
            $rows = $result->fetchAll();
            if (!empty($rows)) {
                return (float) $rows[0]['buffer_percentage'];
            }
        }
        
        // Default buffer 2%
        return 2.0;
    }
    
    /**
     * Convert amount from one currency to another
     * 
     * @param float $amount Amount to convert
     * @param string $fromCurrency Source currency code
     * @param string $toCurrency Target currency code
     * @param bool $useBuffer Whether to apply currency buffer
     * @return float Converted amount
     */
    public function convertAmount($amount, $fromCurrency, $toCurrency, $useBuffer = true)
    {
        $rate = $this->getExchangeRate($fromCurrency, $toCurrency, $useBuffer);
        
        if ($rate === null) {
            return null;
        }
        
        $convertedAmount = $amount * $rate;
        
        // Log conversion
        $this->logConversion($fromCurrency, $toCurrency, $amount, $convertedAmount, $rate);
        
        return $convertedAmount;
    }
    
    /**
     * Log currency conversion
     * 
     * @param string $fromCurrency Source currency code
     * @param string $toCurrency Target currency code
     * @param float $fromAmount Source amount
     * @param float $toAmount Target amount
     * @param float $rate Exchange rate used
     * @return bool Success status
     */
    private function logConversion($fromCurrency, $toCurrency, $fromAmount, $toAmount, $rate)
    {
        $context = 'booking'; // Default context
        $userId = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;
        $ipAddress = $_SERVER['REMOTE_ADDR'] ?? null;
        
        $sql = "INSERT INTO currency_conversion_log 
                (from_currency, to_currency, from_amount, to_amount, exchange_rate, conversion_context, user_id, ip_address) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
        
        return $this->db->query($sql, [
            $fromCurrency,
            $toCurrency,
            $fromAmount,
            $toAmount,
            $rate,
            $context,
            $userId,
            $ipAddress
        ]);
    }
    
    /**
     * Format currency amount for display
     * 
     * @param float $amount Amount to format
     * @param string $currencyCode Currency code
     * @return string Formatted amount
     */
    public function formatCurrency($amount, $currencyCode)
    {
        $config = $this->getCurrencyConfig($currencyCode);
        
        if ($config === null) {
            // Default formatting
            return number_format($amount, 2, '.', ',');
        }
        
        // Format based on currency config
        $decimalPlaces = $config['decimal_places'];
        $decimalSeparator = $config['decimal_separator'];
        $thousandsSeparator = $config['thousands_separator'];
        $symbol = $config['currency_symbol'];
        $symbolPosition = $config['symbol_position'];
        
        $formattedAmount = number_format(
            $amount,
            $decimalPlaces,
            $decimalSeparator,
            $thousandsSeparator
        );
        
        if ($symbolPosition === 'before') {
            return $symbol . $formattedAmount;
        } else {
            return $formattedAmount . ' ' . $symbol;
        }
    }
    
    /**
     * Get currency configuration
     * 
     * @param string $currencyCode Currency code
     * @return array|null Currency configuration or null if not found
     */
    public function getCurrencyConfig($currencyCode)
    {
        $sql = "SELECT * FROM currency_config WHERE currency_code = ? AND is_active = 1 LIMIT 1";
        
        $result = $this->db->query($sql, [$currencyCode]);
        
        if ($result) {
            $rows = $result->fetchAll();
            if (!empty($rows)) {
                return $rows[0];
            }
        }
        
        return null;
    }
    
    /**
     * Get all active currencies
     * 
     * @return array List of active currencies
     */
    public function getActiveCurrencies()
    {
        $sql = "SELECT * FROM currency_config WHERE is_active = 1 ORDER BY is_base_currency DESC, currency_code";
        
        $result = $this->db->query($sql);
        
        $currencies = [];
        if ($result) {
            $currencies = $result->fetchAll();
        }
        
        return $currencies;
    }
    
    /**
     * Get user's preferred currency
     * 
     * @param int $userId User ID
     * @return string Currency code
     */
    public function getUserPreferredCurrency($userId)
    {
        $sql = "SELECT preferred_currency FROM user_currency_preferences WHERE user_id = ? LIMIT 1";
        
        $result = $this->db->query($sql, [$userId]);
        
        if ($result) {
            // For PDO, check if we got any rows
            $rows = $result->fetchAll();
            if (!empty($rows)) {
                return $rows[0]['preferred_currency'];
            }
        }
        
        // Default to base currency
        return $this->baseCurrency;
    }
    
    /**
     * Set user's preferred currency
     * 
     * @param int $userId User ID
     * @param string $currencyCode Currency code
     * @return bool Success status
     */
    public function setUserPreferredCurrency($userId, $currencyCode)
    {
        // Check if currency is active
        $config = $this->getCurrencyConfig($currencyCode);
        if ($config === null) {
            return false;
        }
        
        $sql = "INSERT INTO user_currency_preferences (user_id, preferred_currency) 
                VALUES (?, ?) 
                ON DUPLICATE KEY UPDATE preferred_currency = ?, last_updated = NOW()";
        
        return $this->db->query($sql, [$userId, $currencyCode, $currencyCode]);
    }
    
    /**
     * Auto-detect user's currency based on location
     * 
     * @return string Detected currency code
     */
    public function autoDetectCurrency()
    {
        // Use IP geolocation to detect country
        $ipAddress = $_SERVER['REMOTE_ADDR'] ?? null;
        
        if ($ipAddress) {
            $countryCode = $this->getCountryFromIP($ipAddress);
            
            if ($countryCode) {
                $currencyCode = $this->getCurrencyFromCountry($countryCode);
                if ($currencyCode) {
                    return $currencyCode;
                }
            }
        }
        
        // Default to base currency
        return $this->baseCurrency;
    }
    
    /**
     * Get country code from IP address
     * 
     * @param string $ipAddress IP address
     * @return string|null Country code or null if not found
     */
    private function getCountryFromIP($ipAddress)
    {
        // Use IP geolocation API (e.g., ip-api.com)
        $url = 'http://ip-api.com/json/' . $ipAddress;
        
        $response = $this->makeAPIRequest($url);
        
        if ($response === null) {
            return null;
        }
        
        $data = json_decode($response, true);
        
        return isset($data['countryCode']) ? $data['countryCode'] : null;
    }
    
    /**
     * Get currency code from country code
     * 
     * @param string $countryCode Country code
     * @return string|null Currency code or null if not found
     */
    private function getCurrencyFromCountry($countryCode)
    {
        $sql = "SELECT currency_code FROM currency_config 
                WHERE JSON_CONTAINS(supported_regions, ?) 
                AND is_active = 1 
                LIMIT 1";
        
        $result = $this->db->query($sql, ['"' . $countryCode . '"']);
        
        if ($result && $result->num_rows > 0) {
            $row = $result->fetch_assoc();
            return $row['currency_code'];
        }
        
        return null;
    }
    
    /**
     * Update exchange rates from API
     * 
     * @param string $apiName API name to use
     * @return array Update results
     */
    public function updateExchangeRates($apiName = null)
    {
        $startTime = microtime(true);
        $success = false;
        $currenciesUpdated = [];
        $errorMessage = null;
        
        try {
            // Determine which API to use
            if ($apiName === null) {
                // Try each API until one succeeds
                foreach (array_keys($this->exchangeRateApis) as $api) {
                    $result = $this->updateFromAPI($api);
                    if ($result['success']) {
                        $apiName = $api;
                        $success = true;
                        $currenciesUpdated = $result['currencies'];
                        break;
                    }
                }
            } else {
                $result = $this->updateFromAPI($apiName);
                $success = $result['success'];
                $currenciesUpdated = $result['currencies'];
                $errorMessage = $result['error'];
            }
        } catch (Exception $e) {
            $success = false;
            $errorMessage = $e->getMessage();
        }
        
        $executionTime = round((microtime(true) - $startTime) * 1000);
        
        // Log update
        $this->logRateUpdate($apiName, $currenciesUpdated, $success, $errorMessage, $executionTime);
        
        return [
            'success' => $success,
            'currencies_updated' => $currenciesUpdated,
            'error' => $errorMessage,
            'execution_time_ms' => $executionTime
        ];
    }
    
    /**
     * Update exchange rates from specific API
     * 
     * @param string $apiName API name
     * @return array Update results
     */
    private function updateFromAPI($apiName)
    {
        $apiConfig = $this->exchangeRateApis[$apiName];
        
        // Check if API key is required and available
        if ($apiConfig['requires_api_key']) {
            $apiKey = $this->getAPIKey($apiName);
            if (empty($apiKey)) {
                return [
                    'success' => false,
                    'currencies' => [],
                    'error' => 'API key not configured'
                ];
            }
        }
        
        // Build URL
        $url = $apiConfig['url'];
        if ($apiName === 'open_exchange_rates') {
            $url .= '?app_id=' . $apiKey;
        } elseif ($apiName === 'fixer') {
            $url .= '?access_key=' . $apiKey;
        }
        
        // Make API request
        $response = $this->makeAPIRequest($url);
        
        if ($response === null) {
            return [
                'success' => false,
                'currencies' => [],
                'error' => 'API request failed'
            ];
        }
        
        $data = json_decode($response, true);
        
        if (!isset($data['rates'])) {
            return [
                'success' => false,
                'currencies' => [],
                'error' => 'Invalid API response format'
            ];
        }
        
        // Get base currency from API response
        $baseCurrency = isset($data['base']) ? $data['base'] : 'USD';
        
        // Update rates for all active currencies
        $currenciesUpdated = [];
        $activeCurrencies = $this->getActiveCurrencies();
        
        foreach ($activeCurrencies as $currency) {
            $currencyCode = $currency['currency_code'];
            
            if ($currencyCode === $baseCurrency) {
                continue; // Skip base currency
            }
            
            if (isset($data['rates'][$currencyCode])) {
                $rate = $data['rates'][$currencyCode];
                
                // Store rate
                $this->storeExchangeRate($baseCurrency, $currencyCode, $rate);
                
                // Also store inverse rate
                $inverseRate = 1 / $rate;
                $this->storeExchangeRate($currencyCode, $baseCurrency, $inverseRate);
                
                $currenciesUpdated[] = $currencyCode;
            }
        }
        
        return [
            'success' => true,
            'currencies' => $currenciesUpdated,
            'error' => null
        ];
    }
    
    /**
     * Log exchange rate update
     * 
     * @param string $source API source
     * @param array $currenciesUpdated List of currencies updated
     * @param bool $success Success status
     * @param string $errorMessage Error message if failed
     * @param int $executionTime Execution time in milliseconds
     * @return bool Success status
     */
    private function logRateUpdate($source, $currenciesUpdated, $success, $errorMessage, $executionTime)
    {
        $sql = "INSERT INTO currency_rate_update_log 
                (source, currencies_updated, success, error_message, execution_time_ms) 
                VALUES (?, ?, ?, ?, ?)";
        
        return $this->db->query($sql, [
            $source,
            json_encode($currenciesUpdated),
            $success,
            $errorMessage,
            $executionTime
        ]);
    }
    
    /**
     * Get supported currencies for a country
     * 
     * @param string $countryCode Country code
     * @return array List of supported currencies
     */
    public function getSupportedCurrenciesForCountry($countryCode)
    {
        $sql = "SELECT * FROM currency_config 
                WHERE JSON_CONTAINS(supported_regions, ?) 
                AND is_active = 1 
                ORDER BY is_base_currency DESC, currency_code";
        
        $result = $this->db->query($sql, ['"' . $countryCode . '"']);
        
        $currencies = [];
        if ($result) {
            $currencies = $result->fetchAll();
        }
        
        return $currencies;
    }
}
