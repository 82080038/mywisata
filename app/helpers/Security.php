<?php
/**
 * MyWisata Application - Security Helper Class
 * 
 * Handles security-related functions including password complexity,
 * account lockout, rate limiting, and encryption.
 * 
 * @package MyWisata
 * @version 1.0.0
 * @since 2026-07-18
 */

class Security {
    
    /**
     * Validate password complexity
     * 
     * Requirements:
     * - Minimum 8 characters
     * - At least 1 uppercase letter
     * - At least 1 lowercase letter
     * - At least 1 number
     * - At least 1 special character
     * 
     * @param string $password Password to validate
     * @return array Validation result with 'valid' boolean and 'message' string
     */
    public static function validatePasswordComplexity($password) {
        $errors = [];
        
        // Minimum length
        if (strlen($password) < 8) {
            $errors[] = 'Password must be at least 8 characters long';
        }
        
        // Uppercase letter
        if (!preg_match('/[A-Z]/', $password)) {
            $errors[] = 'Password must contain at least 1 uppercase letter';
        }
        
        // Lowercase letter
        if (!preg_match('/[a-z]/', $password)) {
            $errors[] = 'Password must contain at least 1 lowercase letter';
        }
        
        // Number
        if (!preg_match('/[0-9]/', $password)) {
            $errors[] = 'Password must contain at least 1 number';
        }
        
        // Special character
        if (!preg_match('/[^A-Za-z0-9]/', $password)) {
            $errors[] = 'Password must contain at least 1 special character';
        }
        
        return [
            'valid' => empty($errors),
            'message' => empty($errors) ? 'Password meets complexity requirements' : implode(', ', $errors)
        ];
    }
    
    /**
     * Check account lockout status
     * 
     * @param string $identifier User identifier (email or IP)
     * @return array Lockout status with 'locked' boolean and 'remaining_attempts' integer
     */
    public static function checkAccountLockout($identifier) {
        $lockoutFile = APP_ROOT . '/logs/lockouts.json';
        $maxAttempts = 5;
        $lockoutDuration = 900; // 15 minutes
        
        $lockouts = [];
        if (file_exists($lockoutFile)) {
            $lockouts = json_decode(file_get_contents($lockoutFile), true) ?: [];
        }
        
        // Clean expired lockouts
        $currentTime = time();
        foreach ($lockouts as $key => $lockout) {
            if ($currentTime - $lockout['last_attempt'] > $lockoutDuration) {
                unset($lockouts[$key]);
            }
        }
        
        // Check current identifier
        if (isset($lockouts[$identifier])) {
            $attempts = $lockouts[$identifier]['attempts'];
            
            if ($attempts >= $maxAttempts) {
                $remainingTime = $lockoutDuration - ($currentTime - $lockouts[$identifier]['last_attempt']);
                return [
                    'locked' => true,
                    'remaining_attempts' => 0,
                    'remaining_time' => $remainingTime,
                    'message' => 'Account locked. Try again in ' . ceil($remainingTime / 60) . ' minutes'
                ];
            }
            
            return [
                'locked' => false,
                'remaining_attempts' => $maxAttempts - $attempts,
                'message' => 'Account not locked'
            ];
        }
        
        return [
            'locked' => false,
            'remaining_attempts' => $maxAttempts,
            'message' => 'Account not locked'
        ];
    }
    
    /**
     * Record failed login attempt
     * 
     * @param string $identifier User identifier (email or IP)
     * @return void
     */
    public static function recordFailedAttempt($identifier) {
        $lockoutFile = APP_ROOT . '/logs/lockouts.json';
        $maxAttempts = 5;
        
        $lockouts = [];
        if (file_exists($lockoutFile)) {
            $lockouts = json_decode(file_get_contents($lockoutFile), true) ?: [];
        }
        
        if (!isset($lockouts[$identifier])) {
            $lockouts[$identifier] = [
                'attempts' => 0,
                'last_attempt' => time()
            ];
        }
        
        $lockouts[$identifier]['attempts']++;
        $lockouts[$identifier]['last_attempt'] = time();
        
        // Log the failed attempt
        if (function_exists('Logger') && method_exists('Logger', 'security')) {
            Logger::security('Failed login attempt', [
                'identifier' => $identifier,
                'attempts' => $lockouts[$identifier]['attempts'],
                'ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown'
            ]);
        }
        
        file_put_contents($lockoutFile, json_encode($lockouts, JSON_PRETTY_PRINT));
    }
    
    /**
     * Clear failed login attempts (on successful login)
     * 
     * @param string $identifier User identifier (email or IP)
     * @return void
     */
    public static function clearFailedAttempts($identifier) {
        $lockoutFile = APP_ROOT . '/logs/lockouts.json';
        
        $lockouts = [];
        if (file_exists($lockoutFile)) {
            $lockouts = json_decode(file_get_contents($lockoutFile), true) ?: [];
        }
        
        if (isset($lockouts[$identifier])) {
            unset($lockouts[$identifier]);
            file_put_contents($lockoutFile, json_encode($lockouts, JSON_PRETTY_PRINT));
        }
    }
    
    /**
     * Check rate limit
     * 
     * @param string $identifier Unique identifier (IP or user ID)
     * @param int $limit Maximum requests
     * @param int $window Time window in seconds
     * @return array Rate limit status
     */
    public static function checkRateLimit($identifier, $limit = 60, $window = 60) {
        $rateLimitFile = APP_ROOT . '/logs/ratelimits.json';
        
        $rateLimits = [];
        if (file_exists($rateLimitFile)) {
            $rateLimits = json_decode(file_get_contents($rateLimitFile), true) ?: [];
        }
        
        $currentTime = time();
        
        // Clean old entries
        foreach ($rateLimits as $key => $entries) {
            $rateLimits[$key] = array_filter($entries, function($entry) use ($currentTime, $window) {
                return $currentTime - $entry['timestamp'] < $window;
            });
            
            if (empty($rateLimits[$key])) {
                unset($rateLimits[$key]);
            }
        }
        
        // Check current identifier
        if (!isset($rateLimits[$identifier])) {
            $rateLimits[$identifier] = [];
        }
        
        $requestCount = count($rateLimits[$identifier]);
        
        if ($requestCount >= $limit) {
            return [
                'allowed' => false,
                'remaining' => 0,
                'reset_time' => $rateLimits[$identifier][0]['timestamp'] + $window,
                'message' => 'Rate limit exceeded'
            ];
        }
        
        // Add current request
        $rateLimits[$identifier][] = [
            'timestamp' => $currentTime,
            'ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown'
        ];
        
        file_put_contents($rateLimitFile, json_encode($rateLimits, JSON_PRETTY_PRINT));
        
        return [
            'allowed' => true,
            'remaining' => $limit - $requestCount - 1,
            'reset_time' => $currentTime + $window,
            'message' => 'Request allowed'
        ];
    }
    
    /**
     * Encrypt sensitive data
     * 
     * @param string $data Data to encrypt
     * @param string $key Encryption key
     * @return string Encrypted data (base64 encoded)
     */
    public static function encrypt($data, $key = null) {
        $key = $key ?: (defined('ENCRYPTION_KEY') ? ENCRYPTION_KEY : 'default-encryption-key-change-in-production');
        
        $iv = random_bytes(16);
        $encrypted = openssl_encrypt($data, 'AES-256-CBC', $key, 0, $iv);
        
        return base64_encode($iv . $encrypted);
    }
    
    /**
     * Decrypt sensitive data
     * 
     * @param string $encryptedData Encrypted data (base64 encoded)
     * @param string $key Encryption key
     * @return string|false Decrypted data or false on failure
     */
    public static function decrypt($encryptedData, $key = null) {
        $key = $key ?: (defined('ENCRYPTION_KEY') ? ENCRYPTION_KEY : 'default-encryption-key-change-in-production');
        
        $data = base64_decode($encryptedData);
        $iv = substr($data, 0, 16);
        $encrypted = substr($data, 16);
        
        return openssl_decrypt($encrypted, 'AES-256-CBC', $key, 0, $iv);
    }
    
    /**
     * Generate secure random token
     * 
     * @param int $length Token length
     * @return string Random token
     */
    public static function generateToken($length = 32) {
        return bin2hex(random_bytes($length / 2));
    }
    
    /**
     * Sanitize input to prevent XSS
     * 
     * @param string $input Input to sanitize
     * @return string Sanitized input
     */
    public static function sanitizeXSS($input) {
        if (is_array($input)) {
            return array_map([self::class, 'sanitizeXSS'], $input);
        }
        
        return htmlspecialchars($input, ENT_QUOTES, 'UTF-8');
    }
    
    /**
     * Validate and sanitize file upload
     * 
     * @param array $file $_FILES array element
     * @param array $allowedTypes Allowed MIME types
     * @param int $maxSize Maximum file size in bytes
     * @return array Validation result
     */
    public static function validateFileUpload($file, $allowedTypes = null, $maxSize = null) {
        $allowedTypes = $allowedTypes ?? ALLOWED_IMAGE_TYPES;
        $maxSize = $maxSize ?? MAX_UPLOAD_SIZE;
        
        $errors = [];
        
        // Check if file was uploaded
        if (!isset($file['tmp_name']) || !is_uploaded_file($file['tmp_name'])) {
            return [
                'valid' => false,
                'message' => 'No file uploaded or upload error'
            ];
        }
        
        // Check file size
        if ($file['size'] > $maxSize) {
            $errors[] = 'File size exceeds maximum limit of ' . ($maxSize / 1048576) . 'MB';
        }
        
        // Check file type
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mimeType = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);
        
        if (!in_array($mimeType, $allowedTypes)) {
            $errors[] = 'File type not allowed. Allowed types: ' . implode(', ', $allowedTypes);
        }
        
        // Check file extension
        $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'pdf', 'doc', 'docx'];
        
        if (!in_array($extension, $allowedExtensions)) {
            $errors[] = 'File extension not allowed';
        }
        
        return [
            'valid' => empty($errors),
            'message' => empty($errors) ? 'File is valid' : implode(', ', $errors),
            'mime_type' => $mimeType
        ];
    }
    
    /**
     * Detect suspicious activity
     * 
     * @param string $type Activity type
     * @param array $context Activity context
     * @return bool True if activity is suspicious
     */
    public static function detectSuspiciousActivity($type, $context = []) {
        $suspiciousFile = APP_ROOT . '/logs/suspicious.json';
        
        $suspicious = [];
        if (file_exists($suspiciousFile)) {
            $suspicious = json_decode(file_get_contents($suspiciousFile), true) ?: [];
        }
        
        $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
        $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? 'unknown';
        
        // Check for rapid requests from same IP
        $key = $ip . '_' . $type;
        
        if (!isset($suspicious[$key])) {
            $suspicious[$key] = [
                'count' => 0,
                'first_seen' => time(),
                'last_seen' => time()
            ];
        }
        
        $suspicious[$key]['count']++;
        $suspicious[$key]['last_seen'] = time();
        
        // If more than 100 requests in 1 minute, flag as suspicious
        if ($suspicious[$key]['count'] > 100 && (time() - $suspicious[$key]['first_seen']) < 60) {
            Logger::security('Suspicious activity detected', [
                'type' => $type,
                'ip' => $ip,
                'user_agent' => $userAgent,
                'count' => $suspicious[$key]['count'],
                'context' => $context
            ]);
            
            return true;
        }
        
        file_put_contents($suspiciousFile, json_encode($suspicious, JSON_PRETTY_PRINT));
        
        return false;
    }
}
