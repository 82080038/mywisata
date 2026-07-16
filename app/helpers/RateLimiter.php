<?php
/**
 * MyWisata Application - Rate Limiter Helper
 * 
 * Handles rate limiting for API endpoints and sensitive operations.
 * 
 * @package MyWisata
 * @version 1.0.0
 * @since 2026-07-01
 */

class RateLimiter {
    
    private static $cacheDir;
    private static $defaultLimit = 60; // requests per minute
    private static $defaultWindow = 60; // seconds
    
    /**
     * Initialize rate limiter
     */
    public static function init() {
        self::$cacheDir = APP_ROOT . '/cache/ratelimit';
        
        if (!is_dir(self::$cacheDir)) {
            try {
                mkdir(self::$cacheDir, 0777, true);
            } catch (Exception $e) {
                // Fall back to session-based rate limiting if file system fails
                self::$cacheDir = null;
            }
        }
    }
    
    /**
     * Check if request is allowed
     * 
     * @param string $identifier Unique identifier (IP, user ID, etc.)
     * @param int $limit Request limit
     * @param int $window Time window in seconds
     * @return bool
     */
    public static function allow($identifier, $limit = null, $window = null) {
        self::init();
        
        if ($limit === null) {
            $limit = self::$defaultLimit;
        }
        
        if ($window === null) {
            $window = self::$defaultWindow;
        }
        
        // Use session-based rate limiting if file system unavailable
        if (self::$cacheDir === null) {
            return self::allowSession($identifier, $limit, $window);
        }
        
        $cacheFile = self::$cacheDir . '/' . md5($identifier) . '.ratelimit';
        
        $data = [
            'count' => 0,
            'reset_time' => time() + $window
        ];
        
        if (file_exists($cacheFile)) {
            $data = json_decode(file_get_contents($cacheFile), true);
            
            // Reset if window expired
            if ($data['reset_time'] < time()) {
                $data['count'] = 0;
                $data['reset_time'] = time() + $window;
            }
        }
        
        // Check if limit exceeded
        if ($data['count'] >= $limit) {
            Logger::warning('Rate limit exceeded', ['identifier' => $identifier, 'limit' => $limit]);
            return false;
        }
        
        // Increment count
        $data['count']++;
        try {
            file_put_contents($cacheFile, json_encode($data));
        } catch (Exception $e) {
            // Fall back to session if file write fails
            return self::allowSession($identifier, $limit, $window);
        }
        
        return true;
    }
    
    /**
     * Session-based rate limiting fallback
     * 
     * @param string $identifier Unique identifier
     * @param int $limit Request limit
     * @param int $window Time window in seconds
     * @return bool
     */
    private static function allowSession($identifier, $limit, $window) {
        $sessionKey = 'ratelimit_' . md5($identifier);
        
        $data = [
            'count' => 0,
            'reset_time' => time() + $window
        ];
        
        if (isset($_SESSION[$sessionKey])) {
            $data = $_SESSION[$sessionKey];
            
            // Reset if window expired
            if ($data['reset_time'] < time()) {
                $data['count'] = 0;
                $data['reset_time'] = time() + $window;
            }
        }
        
        // Check if limit exceeded
        if ($data['count'] >= $limit) {
            Logger::warning('Rate limit exceeded (session)', ['identifier' => $identifier, 'limit' => $limit]);
            return false;
        }
        
        // Increment count
        $data['count']++;
        $_SESSION[$sessionKey] = $data;
        
        return true;
    }
    
    /**
     * Get remaining requests
     * 
     * @param string $identifier Unique identifier
     * @return array
     */
    public static function getRemaining($identifier) {
        self::init();
        
        $cacheFile = self::$cacheDir . '/' . md5($identifier) . '.ratelimit';
        
        if (!file_exists($cacheFile)) {
            return [
                'remaining' => self::$defaultLimit,
                'reset_time' => time() + self::$defaultWindow
            ];
        }
        
        $data = json_decode(file_get_contents($cacheFile), true);
        
        // Reset if window expired
        if ($data['reset_time'] < time()) {
            return [
                'remaining' => self::$defaultLimit,
                'reset_time' => time() + self::$defaultWindow
            ];
        }
        
        return [
            'remaining' => max(0, self::$defaultLimit - $data['count']),
            'reset_time' => $data['reset_time']
        ];
    }
    
    /**
     * Clear rate limit for identifier
     * 
     * @param string $identifier Unique identifier
     * @return bool
     */
    public static function clear($identifier) {
        self::init();
        
        // Use session-based if file system unavailable
        if (self::$cacheDir === null) {
            $sessionKey = 'ratelimit_' . md5($identifier);
            if (isset($_SESSION[$sessionKey])) {
                unset($_SESSION[$sessionKey]);
                return true;
            }
            return false;
        }
        
        $cacheFile = self::$cacheDir . '/' . md5($identifier) . '.ratelimit';
        
        if (file_exists($cacheFile)) {
            unlink($cacheFile);
            return true;
        }
        
        return false;
    }
    
    /**
     * Get client IP address
     * 
     * @return string
     */
    public static function getClientIP() {
        $ip = '';
        
        if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
            $ip = $_SERVER['HTTP_CLIENT_IP'];
        } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
        } else {
            $ip = $_SERVER['REMOTE_ADDR'];
        }
        
        return $ip;
    }
}
