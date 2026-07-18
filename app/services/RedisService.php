<?php
namespace App\Services;

use Redis;
use RedisException;

class RedisService
{
    private $redis;
    private $config;
    private $prefix;

    public function __construct()
    {
        $this->config = require __DIR__ . '/../config/redis.php';
        $this->prefix = $this->config['prefix'];
        
        try {
            $this->redis = new Redis();
            $this->redis->connect(
                $this->config['host'],
                $this->config['port']
            );
            
            if ($this->config['password']) {
                $this->redis->auth($this->config['password']);
            }
            
            $this->redis->select($this->config['database']);
        } catch (RedisException $e) {
            error_log("Redis connection failed: " . $e->getMessage());
            $this->redis = null;
        }
    }

    /**
     * Check if Redis is connected
     */
    public function isConnected()
    {
        if ($this->redis === null) {
            return false;
        }
        
        try {
            return $this->redis->ping() === '+PONG';
        } catch (RedisException $e) {
            return false;
        }
    }

    /**
     * Get value from cache
     */
    public function get($key)
    {
        if (!$this->isConnected()) {
            return null;
        }

        $value = $this->redis->get($this->prefix . $key);
        
        if ($value === false) {
            return null;
        }

        return json_decode($value, true);
    }

    /**
     * Set value in cache
     */
    public function set($key, $value, $ttl = null)
    {
        if (!$this->isConnected()) {
            return false;
        }

        $ttl = $ttl ?? $this->config['ttl'];
        $serialized = json_encode($value);
        
        return $this->redis->setex($this->prefix . $key, $ttl, $serialized);
    }

    /**
     * Delete value from cache
     */
    public function delete($key)
    {
        if (!$this->isConnected()) {
            return false;
        }

        return $this->redis->del($this->prefix . $key) > 0;
    }

    /**
     * Check if key exists
     */
    public function exists($key)
    {
        if (!$this->isConnected()) {
            return false;
        }

        return $this->redis->exists($this->prefix . $key) > 0;
    }

    /**
     * Clear all cache with prefix
     */
    public function clear()
    {
        if (!$this->isConnected()) {
            return false;
        }

        $keys = $this->redis->keys($this->prefix . '*');
        
        if (!empty($keys)) {
            return $this->redis->del($keys) > 0;
        }

        return true;
    }

    /**
     * Get multiple values
     */
    public function getMultiple($keys)
    {
        if (!$this->isConnected()) {
            return array_fill(0, count($keys), null);
        }

        $prefixedKeys = array_map(function($key) {
            return $this->prefix . $key;
        }, $keys);

        $values = $this->redis->mget($prefixedKeys);
        
        return array_map(function($value) {
            return $value === false ? null : json_decode($value, true);
        }, $values);
    }

    /**
     * Set multiple values
     */
    public function setMultiple($items, $ttl = null)
    {
        if (!$this->isConnected()) {
            return false;
        }

        $ttl = $ttl ?? $this->config['ttl'];
        
        foreach ($items as $key => $value) {
            $this->set($key, $value, $ttl);
        }

        return true;
    }

    /**
     * Increment value
     */
    public function increment($key, $value = 1)
    {
        if (!$this->isConnected()) {
            return false;
        }

        return $this->redis->incrBy($this->prefix . $key, $value);
    }

    /**
     * Decrement value
     */
    public function decrement($key, $value = 1)
    {
        if (!$this->isConnected()) {
            return false;
        }

        return $this->redis->decrBy($this->prefix . $key, $value);
    }

    /**
     * Set with expiration
     */
    public function setEx($key, $ttl, $value)
    {
        if (!$this->isConnected()) {
            return false;
        }

        $serialized = json_encode($value);
        return $this->redis->setex($this->prefix . $key, $ttl, $serialized);
    }

    /**
     * Get remaining TTL
     */
    public function ttl($key)
    {
        if (!$this->isConnected()) {
            return -1;
        }

        return $this->redis->ttl($this->prefix . $key);
    }

    /**
     * Cache query result
     */
    public function cacheQuery($key, $callback, $ttl = null)
    {
        $cached = $this->get($key);
        
        if ($cached !== null) {
            return $cached;
        }

        $result = $callback();
        $this->set($key, $result, $ttl);
        
        return $result;
    }

    /**
     * Remember or get
     */
    public function remember($key, $callback, $ttl = null)
    {
        return $this->cacheQuery($key, $callback, $ttl);
    }

    /**
     * Close connection
     */
    public function close()
    {
        if ($this->redis) {
            $this->redis->close();
        }
    }

    /**
     * Get Redis instance for advanced operations
     */
    public function getRedis()
    {
        return $this->redis;
    }
}
