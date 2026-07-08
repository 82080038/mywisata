<?php

/**
 * MyWisata Application - Cache Helper
 *
 * Handles caching functionality with support for file-based and Redis caching.
 *
 * @version 2.0.0
 *
 * @since 2026-07-01
 */
class Cache
{
    private static $cacheDir;
    private static $defaultTTL = 3600; // 1 hour
    private static $redis = null;
    private static $useRedis = false;

    /**
     * Initialize cache
     */
    public static function init()
    {
        self::$cacheDir = APP_ROOT . '/cache';

        if (!is_dir(self::$cacheDir)) {
            mkdir(self::$cacheDir, 0777, true);
        }

        // Try to initialize Redis if available
        if (extension_loaded('redis') && getenv('REDIS_ENABLED') === 'true') {
            try {
                self::$redis = new Redis();
                $redisHost = getenv('REDIS_HOST') ?: '127.0.0.1';
                $redisPort = getenv('REDIS_PORT') ?: 6379;
                
                if (self::$redis->connect($redisHost, $redisPort)) {
                    self::$useRedis = true;
                    Logger::info('Redis cache initialized', ['host' => $redisHost, 'port' => $redisPort]);
                }
            } catch (Exception $e) {
                Logger::warning('Redis connection failed, falling back to file cache', ['error' => $e->getMessage()]);
                self::$useRedis = false;
            }
        }
    }

    /**
     * Get cached value
     *
     * @param string $key Cache key
     *
     * @return mixed|false
     */
    public static function get($key)
    {
        self::init();

        if (self::$useRedis && self::$redis) {
            try {
                $value = self::$redis->get($key);
                if ($value !== false) {
                    return unserialize($value);
                }
            } catch (Exception $e) {
                Logger::warning('Redis get failed, falling back to file cache', ['error' => $e->getMessage()]);
                self::$useRedis = false;
            }
        }

        // File-based cache fallback
        $cacheFile = self::$cacheDir . '/' . md5($key) . '.cache';

        if (!file_exists($cacheFile)) {
            return false;
        }

        $data = unserialize(file_get_contents($cacheFile));

        if ($data['expires'] < time()) {
            self::delete($key);

            return false;
        }

        return $data['value'];
    }

    /**
     * Set cached value
     *
     * @param string $key Cache key
     * @param mixed $value Value to cache
     * @param int $ttl Time to live in seconds
     *
     * @return bool
     */
    public static function set($key, $value, $ttl = null)
    {
        self::init();

        if ($ttl === null) {
            $ttl = self::$defaultTTL;
        }

        if (self::$useRedis && self::$redis) {
            try {
                $result = self::$redis->setex($key, $ttl, serialize($value));
                if ($result) {
                    Logger::info('Redis cache set', ['key' => $key, 'ttl' => $ttl]);
                    return true;
                }
            } catch (Exception $e) {
                Logger::warning('Redis set failed, falling back to file cache', ['error' => $e->getMessage()]);
                self::$useRedis = false;
            }
        }

        // File-based cache fallback
        $cacheFile = self::$cacheDir . '/' . md5($key) . '.cache';

        $data = [
            'value' => $value,
            'expires' => time() + $ttl,
            'created' => time(),
        ];

        $result = file_put_contents($cacheFile, serialize($data));

        if ($result !== false) {
            Logger::info('File cache set', ['key' => $key, 'ttl' => $ttl]);
        }

        return $result !== false;
    }

    /**
     * Delete cached value
     *
     * @param string $key Cache key
     *
     * @return bool
     */
    public static function delete($key)
    {
        self::init();

        if (self::$useRedis && self::$redis) {
            try {
                $result = self::$redis->del($key);
                if ($result > 0) {
                    Logger::info('Redis cache deleted', ['key' => $key]);
                    return true;
                }
            } catch (Exception $e) {
                Logger::warning('Redis delete failed, falling back to file cache', ['error' => $e->getMessage()]);
                self::$useRedis = false;
            }
        }

        // File-based cache fallback
        $cacheFile = self::$cacheDir . '/' . md5($key) . '.cache';

        if (file_exists($cacheFile)) {
            $result = unlink($cacheFile);

            if ($result) {
                Logger::info('File cache deleted', ['key' => $key]);
            }

            return $result;
        }

        return false;
    }

    /**
     * Clear all cache
     *
     * @return bool
     */
    public static function clear()
    {
        self::init();

        if (self::$useRedis && self::$redis) {
            try {
                $result = self::$redis->flushDB();
                if ($result) {
                    Logger::info('Redis cache cleared');
                    return true;
                }
            } catch (Exception $e) {
                Logger::warning('Redis clear failed, falling back to file cache', ['error' => $e->getMessage()]);
                self::$useRedis = false;
            }
        }

        // File-based cache fallback
        $files = glob(self::$cacheDir . '/*.cache');
        $count = 0;

        foreach ($files as $file) {
            if (unlink($file)) {
                $count++;
            }
        }

        Logger::info('File cache cleared', ['files_deleted' => $count]);

        return $count > 0;
    }

    /**
     * Remember - Get from cache or execute callback
     *
     * @param string $key Cache key
     * @param callable $callback Callback to execute if cache miss
     * @param int $ttl Time to live in seconds
     *
     * @return mixed
     */
    public static function remember($key, $callback, $ttl = null)
    {
        $value = self::get($key);

        if ($value !== false) {
            return $value;
        }

        $value = $callback();
        self::set($key, $value, $ttl);

        return $value;
    }

    /**
     * Check if Redis is enabled and connected
     *
     * @return bool
     */
    public static function isRedisEnabled()
    {
        self::init();
        return self::$useRedis && self::$redis;
    }
}
