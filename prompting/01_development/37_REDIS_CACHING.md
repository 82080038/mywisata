# REDIS CACHING
# Module 37 - Redis Caching Implementation for Tour Guide Application

## OVERVIEW

This prompting template guides the AI through implementing Redis caching for the Tour Guide Application to improve performance, reduce database load, and enhance scalability.

## REDIS BENEFITS

### Performance Improvements
- **Faster response times** - In-memory data access
- **Reduced database load** - Fewer queries
- **Better scalability** - Handle more concurrent users
- **Lower latency** - Sub-millisecond response times

### Use Cases
- Session storage
- Query result caching
- API response caching
- Rate limiting
- Real-time analytics
- Pub/sub messaging

## REDIS INSTALLATION

### Linux Installation
```bash
# Ubuntu/Debian
sudo apt update
sudo apt install redis-server

# Start Redis
sudo systemctl start redis-server
sudo systemctl enable redis-server

# Check status
sudo systemctl status redis-server
```

### Windows Installation
```bash
# Using WSL or Docker
docker run -d -p 6379:6379 --name redis redis:latest
```

### PHP Redis Extension
```bash
# Install PHP Redis extension
sudo apt install php-redis

# Or using PECL
pecl install redis

# Add to php.ini
extension=redis.so
```

## REDIS CONFIGURATION

### config/redis.php
```php
<?php
return [
    'host' => env('REDIS_HOST', '127.0.0.1'),
    'port' => env('REDIS_PORT', 6379),
    'password' => env('REDIS_PASSWORD', null),
    'database' => env('REDIS_DB', 0),
    'ttl' => env('REDIS_TTL', 3600), // Default TTL: 1 hour
    'prefix' => env('REDIS_PREFIX', 'mywisata:')
];
```

### Environment Variables
```env
REDIS_HOST=127.0.0.1
REDIS_PORT=6379
REDIS_PASSWORD=
REDIS_DB=0
REDIS_TTL=3600
REDIS_PREFIX=mywisata:
```

## REDIS SERVICE CLASS

### RedisService.php
```php
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
        return $this->redis !== null && $this->redis->ping() === '+PONG';
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
```

## CACHING STRATEGIES

### 1. Query Result Caching
```php
// In Model class
public function findWithCache($id)
{
    $redis = new \App\Services\RedisService();
    $key = 'destination:' . $id;
    
    return $redis->remember($key, function() use ($id) {
        return $this->find($id);
    }, 3600); // Cache for 1 hour
}

public function getAllWithCache()
{
    $redis = new \App\Services\RedisService();
    $key = 'destinations:all';
    
    return $redis->remember($key, function() {
        return $this->all();
    }, 1800); // Cache for 30 minutes
}
```

### 2. Session Storage
```php
// SessionService.php
class SessionService
{
    private $redis;
    
    public function __construct()
    {
        $this->redis = new \App\Services\RedisService();
    }
    
    public function set($sessionId, $data)
    {
        $key = 'session:' . $sessionId;
        $this->redis->set($key, $data, 1800); // 30 minutes
    }
    
    public function get($sessionId)
    {
        $key = 'session:' . $sessionId;
        return $this->redis->get($key);
    }
    
    public function destroy($sessionId)
    {
        $key = 'session:' . $sessionId;
        $this->redis->delete($key);
    }
}
```

### 3. API Response Caching
```php
// In Controller
public function index()
{
    $redis = new \App\Services\RedisService();
    $key = 'api:destinations:' . $_GET['page'] ?? 1;
    
    $data = $redis->remember($key, function() {
        $destinationModel = new \App\Models\Destination();
        return $destinationModel->paginate(10);
    }, 300); // Cache for 5 minutes
    
    return $this->json($data);
}
```

### 4. Rate Limiting
```php
// RateLimiterService.php
class RateLimiterService
{
    private $redis;
    
    public function __construct()
    {
        $this->redis = new \App\Services\RedisService();
    }
    
    public function attempt($identifier, $maxAttempts, $decaySeconds)
    {
        $key = 'ratelimit:' . $identifier;
        
        if (!$this->redis->exists($key)) {
            $this->redis->set($key, 1, $decaySeconds);
            return true;
        }
        
        $attempts = $this->redis->increment($key);
        
        if ($attempts > $maxAttempts) {
            return false;
        }
        
        return true;
    }
    
    public function remaining($identifier, $maxAttempts)
    {
        $key = 'ratelimit:' . $identifier;
        $attempts = $this->redis->get($key) ?? 0;
        return max(0, $maxAttempts - $attempts);
    }
}
```

### 5. Cache Tags (Manual Implementation)
```php
// CacheTagService.php
class CacheTagService
{
    private $redis;
    
    public function __construct()
    {
        $this->redis = new \App\Services\RedisService();
    }
    
    public function putWithTag($key, $value, $tag, $ttl = null)
    {
        $this->redis->set($key, $value, $ttl);
        
        // Add key to tag set
        $tagKey = 'tag:' . $tag;
        $this->redis->getRedis()->sAdd($tagKey, $key);
    }
    
    public function flushTag($tag)
    {
        $tagKey = 'tag:' . $tag;
        $keys = $this->redis->getRedis()->sMembers($tagKey);
        
        foreach ($keys as $key) {
            $this->redis->delete($key);
        }
        
        $this->redis->delete($tagKey);
    }
}
```

## CACHE INVALIDATION

### Automatic Invalidation
```php
// In Model class after update
public function update($id, $data)
{
    $result = parent::update($id, $data);
    
    if ($result) {
        $redis = new \App\Services\RedisService();
        
        // Invalidate specific cache
        $redis->delete('destination:' . $id);
        
        // Invalidate list cache
        $redis->delete('destinations:all');
        
        // Invalidate API cache
        $redis->delete('api:destinations:*');
    }
    
    return $result;
}
```

### Manual Invalidation
```php
// CacheController.php
class CacheController extends Controller
{
    public function clear()
    {
        $redis = new \App\Services\RedisService();
        $redis->clear();
        
        $_SESSION['success'] = 'Cache cleared successfully';
        return $this->redirect('/admin/cache');
    }
    
    public function clearTag($tag)
    {
        $cacheTag = new \App\Services\CacheTagService();
        $cacheTag->flushTag($tag);
        
        $_SESSION['success'] = "Cache tag '{$tag}' cleared";
        return $this->redirect('/admin/cache');
    }
}
```

## MONITORING REDIS

### Redis Info Command
```php
// In admin dashboard
public function redisStats()
{
    $redis = new \App\Services\RedisService();
    
    if (!$redis->isConnected()) {
        return ['status' => 'disconnected'];
    }
    
    $info = $redis->getRedis()->info();
    
    return [
        'status' => 'connected',
        'memory_used' => $info['used_memory_human'],
        'memory_peak' => $info['used_memory_peak_human'],
        'total_keys' => $redis->getRedis()->dbSize(),
        'hits' => $info['keyspace_hits'],
        'misses' => $info['keyspace_misses'],
        'hit_rate' => $info['keyspace_hits'] / ($info['keyspace_hits'] + $info['keyspace_misses']) * 100
    ];
}
```

## IMPLEMENTATION TASKS

### Phase 1: Setup
1. Install Redis server
2. Install PHP Redis extension
3. Configure Redis connection
4. Create RedisService class
5. Test connection

### Phase 2: Basic Caching
1. Implement query result caching
2. Implement session storage
3. Implement API response caching
4. Add cache helpers to models
5. Update controllers to use cache

### Phase 3: Advanced Features
1. Implement rate limiting
2. Implement cache tags
3. Implement pub/sub (optional)
4. Implement cache warming
5. Implement cache invalidation

### Phase 4: Integration
1. Update existing models
2. Update existing controllers
3. Add cache to critical paths
4. Implement cache invalidation hooks
5. Add cache monitoring

### Phase 5: Optimization
1. Tune Redis configuration
2. Optimize cache keys
3. Set appropriate TTL values
4. Implement cache warming
5. Monitor cache performance

## DELIVERABLES

1. RedisService class
2. SessionService class
3. RateLimiterService class
4. CacheTagService class
5. Updated models with caching
6. Updated controllers with caching
7. Cache management interface
8. Redis monitoring dashboard
9. Configuration files
10. Documentation

## ACCEPTANCE CRITERIA

- Redis installed and configured
- RedisService class implemented
- Query result caching working
- Session storage in Redis
- API response caching
- Rate limiting implemented
- Cache invalidation working
- Cache monitoring dashboard
- Performance improved
- Documentation complete

## NOTES

- Monitor Redis memory usage
- Set appropriate TTL values
- Implement graceful fallback
- Use cache tags for related data
- Regular cache cleanup
- Monitor cache hit rates
- Test cache expiration
- Document cache strategies

---

**Module:** 37_REDIS_CACHING  
**Priority:** MEDIUM  
**Status:** READY FOR DEVELOPMENT  
**Last Updated:** 2026-07-18
