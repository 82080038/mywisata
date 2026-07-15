<?php
/**
 * MyWisata Application - Redis Cache Helper
 * 
 * Handles Redis caching for improved performance.
 * Falls back to file-based caching if Redis is not available.
 * 
 * @package MyWisata
 * @version 1.0.0
 * @since 2026-07-15
 */

class RedisCache {
    
    private static $redis = null;
    private static $enabled = false;
    private static $fallbackEnabled = true;
    private static $fallbackDir;
    
    /**
     * Initialize Redis connection
     */
    public static function init() {
        self::$enabled = REDIS_ENABLED;
        self::$fallbackDir = APP_ROOT . '/cache/';
        
        if (self::$enabled) {
            try {
                self::$redis = new Redis();
                self::$redis->connect(REDIS_HOST, REDIS_PORT);
                
                if (!empty(REDIS_PASSWORD)) {
                    self::$redis->auth(REDIS_PASSWORD);
                }
                
                self::$redis->select(REDIS_DATABASE);
                
                // Test connection
                self::$redis->ping();
                
                Logger::info('Redis connected successfully');
            } catch (Exception $e) {
                Logger::error('Redis connection failed', ['error' => $e->getMessage()]);
                self::$enabled = false;
                self::$fallbackEnabled = true;
            }
        }
        
        // Create fallback cache directory
        if (!is_dir(self::$fallbackDir)) {
            mkdir(self::$fallbackDir, 0755, true);
        }
    }
    
    /**
     * Get value from cache
     * 
     * @param string $key Cache key
     * @param mixed $default Default value if not found
     * @return mixed Cached value or default
     */
    public static function get($key, $default = null) {
        if (self::$enabled && self::$redis) {
            try {
                $value = self::$redis->get($key);
                if ($value !== false) {
                    return json_decode($value, true);
                }
            } catch (Exception $e) {
                Logger::error('Redis get failed', ['error' => $e->getMessage(), 'key' => $key]);
            }
        }
        
        // Fallback to file cache
        if (self::$fallbackEnabled) {
            return self::getFallback($key, $default);
        }
        
        return $default;
    }
    
    /**
     * Set value in cache
     * 
     * @param string $key Cache key
     * @param mixed $value Value to cache
     * @param int $ttl Time to live in seconds
     * @return bool Success status
     */
    public static function set($key, $value, $ttl = null) {
        $ttl = $ttl ?? CACHE_TTL;
        
        if (self::$enabled && self::$redis) {
            try {
                $serialized = json_encode($value);
                if ($ttl > 0) {
                    return self::$redis->setex($key, $ttl, $serialized);
                } else {
                    return self::$redis->set($key, $serialized);
                }
            } catch (Exception $e) {
                Logger::error('Redis set failed', ['error' => $e->getMessage(), 'key' => $key]);
            }
        }
        
        // Fallback to file cache
        if (self::$fallbackEnabled) {
            return self::setFallback($key, $value, $ttl);
        }
        
        return false;
    }
    
    /**
     * Delete value from cache
     * 
     * @param string $key Cache key
     * @return bool Success status
     */
    public static function delete($key) {
        if (self::$enabled && self::$redis) {
            try {
                return self::$redis->del($key) > 0;
            } catch (Exception $e) {
                Logger::error('Redis delete failed', ['error' => $e->getMessage(), 'key' => $key]);
            }
        }
        
        // Fallback to file cache
        if (self::$fallbackEnabled) {
            return self::deleteFallback($key);
        }
        
        return false;
    }
    
    /**
     * Clear all cache
     * 
     * @return bool Success status
     */
    public static function clear() {
        if (self::$enabled && self::$redis) {
            try {
                return self::$redis->flushDB();
            } catch (Exception $e) {
                Logger::error('Redis clear failed', ['error' => $e->getMessage()]);
            }
        }
        
        // Fallback to file cache
        if (self::$fallbackEnabled) {
            return self::clearFallback();
        }
        
        return false;
    }
    
    /**
     * Check if key exists in cache
     * 
     * @param string $key Cache key
     * @return bool Exists status
     */
    public static function exists($key) {
        if (self::$enabled && self::$redis) {
            try {
                return self::$redis->exists($key) > 0;
            } catch (Exception $e) {
                Logger::error('Redis exists failed', ['error' => $e->getMessage(), 'key' => $key]);
            }
        }
        
        // Fallback to file cache
        if (self::$fallbackEnabled) {
            return self::existsFallback($key);
        }
        
        return false;
    }
    
    /**
     * Get or set cache (memoization pattern)
     * 
     * @param string $key Cache key
     * @param callable $callback Callback to generate value if not cached
     * @param int $ttl Time to live in seconds
     * @return mixed Cached or generated value
     */
    public static function remember($key, $callback, $ttl = null) {
        $value = self::get($key);
        
        if ($value !== null) {
            return $value;
        }
        
        $value = $callback();
        self::set($key, $value, $ttl);
        
        return $value;
    }
    
    /**
     * Increment value in cache
     * 
     * @param string $key Cache key
     * @param int $value Increment value
     * @return int New value
     */
    public static function increment($key, $value = 1) {
        if (self::$enabled && self::$redis) {
            try {
                return self::$redis->incrBy($key, $value);
            } catch (Exception $e) {
                Logger::error('Redis increment failed', ['error' => $e->getMessage(), 'key' => $key]);
            }
        }
        
        // Fallback to file cache
        if (self::$fallbackEnabled) {
            $current = self::get($key, 0);
            $new = $current + $value;
            self::set($key, $new);
            return $new;
        }
        
        return false;
    }
    
    /**
     * Decrement value in cache
     * 
     * @param string $key Cache key
     * @param int $value Decrement value
     * @return int New value
     */
    public static function decrement($key, $value = 1) {
        if (self::$enabled && self::$redis) {
            try {
                return self::$redis->decrBy($key, $value);
            } catch (Exception $e) {
                Logger::error('Redis decrement failed', ['error' => $e->getMessage(), 'key' => $key]);
            }
        }
        
        // Fallback to file cache
        if (self::$fallbackEnabled) {
            $current = self::get($key, 0);
            $new = $current - $value;
            self::set($key, $new);
            return $new;
        }
        
        return false;
    }
    
    /**
     * Get multiple values from cache
     * 
     * @param array $keys Array of cache keys
     * @return array Array of cached values
     */
    public static function getMultiple($keys) {
        if (self::$enabled && self::$redis) {
            try {
                $values = self::$redis->mget($keys);
                $result = [];
                foreach ($keys as $i => $key) {
                    $result[$key] = $values[$i] !== false ? json_decode($values[$i], true) : null;
                }
                return $result;
            } catch (Exception $e) {
                Logger::error('Redis getMultiple failed', ['error' => $e->getMessage()]);
            }
        }
        
        // Fallback to file cache
        if (self::$fallbackEnabled) {
            $result = [];
            foreach ($keys as $key) {
                $result[$key] = self::get($key);
            }
            return $result;
        }
        
        return [];
    }
    
    /**
     * Set multiple values in cache
     * 
     * @param array $data Associative array of key => value pairs
     * @param int $ttl Time to live in seconds
     * @return bool Success status
     */
    public static function setMultiple($data, $ttl = null) {
        $ttl = $ttl ?? CACHE_TTL;
        
        if (self::$enabled && self::$redis) {
            try {
                $pipeline = self::$redis->multi();
                foreach ($data as $key => $value) {
                    $serialized = json_encode($value);
                    if ($ttl > 0) {
                        $pipeline->setex($key, $ttl, $serialized);
                    } else {
                        $pipeline->set($key, $serialized);
                    }
                }
                $pipeline->exec();
                return true;
            } catch (Exception $e) {
                Logger::error('Redis setMultiple failed', ['error' => $e->getMessage()]);
            }
        }
        
        // Fallback to file cache
        if (self::$fallbackEnabled) {
            $success = true;
            foreach ($data as $key => $value) {
                if (!self::set($key, $value, $ttl)) {
                    $success = false;
                }
            }
            return $success;
        }
        
        return false;
    }
    
    // ============================================
    // FALLBACK FILE CACHE METHODS
    // ============================================
    
    private static function getFallbackPath($key) {
        $safeKey = md5($key);
        return self::$fallbackDir . $safeKey . '.cache';
    }
    
    private static function getFallback($key, $default = null) {
        $path = self::getFallbackPath($key);
        
        if (!file_exists($path)) {
            return $default;
        }
        
        $content = file_get_contents($path);
        $data = json_decode($content, true);
        
        // Check expiry
        if (isset($data['expiry']) && $data['expiry'] < time()) {
            @unlink($path);
            return $default;
        }
        
        return $data['value'] ?? $default;
    }
    
    private static function setFallback($key, $value, $ttl = null) {
        $path = self::getFallbackPath($key);
        $ttl = $ttl ?? CACHE_TTL;
        
        $data = [
            'value' => $value,
            'expiry' => $ttl > 0 ? time() + $ttl : 0,
            'created' => time()
        ];
        
        return file_put_contents($path, json_encode($data)) !== false;
    }
    
    private static function deleteFallback($key) {
        $path = self::getFallbackPath($key);
        return @unlink($path);
    }
    
    private static function clearFallback() {
        $files = glob(self::$fallbackDir . '*.cache');
        $success = true;
        
        foreach ($files as $file) {
            if (!@unlink($file)) {
                $success = false;
            }
        }
        
        return $success;
    }
    
    private static function existsFallback($key) {
        $path = self::getFallbackPath($key);
        
        if (!file_exists($path)) {
            return false;
        }
        
        $content = file_get_contents($path);
        $data = json_decode($content, true);
        
        // Check expiry
        if (isset($data['expiry']) && $data['expiry'] < time()) {
            @unlink($path);
            return false;
        }
        
        return true;
    }
    
    /**
     * Generate cache key with prefix
     * 
     * @param string $prefix Key prefix
     * @param string $identifier Unique identifier
     * @return string Generated cache key
     */
    public static function key($prefix, $identifier) {
        return $prefix . ':' . $identifier;
    }
    
    /**
     * Clear cache by pattern
     * 
     * @param string $pattern Key pattern (e.g., "user:*")
     * @return int Number of keys deleted
     */
    public static function clearByPattern($pattern) {
        if (self::$enabled && self::$redis) {
            try {
                $keys = self::$redis->keys($pattern);
                if (!empty($keys)) {
                    return self::$redis->del($keys);
                }
                return 0;
            } catch (Exception $e) {
                Logger::error('Redis clearByPattern failed', ['error' => $e->getMessage()]);
            }
        }
        
        // Fallback: clear all if pattern is wildcard
        if (self::$fallbackEnabled && $pattern === '*') {
            return self::clearFallback() ? 1 : 0;
        }
        
        return 0;
    }
}

// Initialize on load
RedisCache::init();
