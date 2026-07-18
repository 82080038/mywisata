<?php
namespace App\Core;

use App\Helpers\Cache;

/**
 * Base Service Class
 * 
 * Implements Service Layer for business logic
 * Provides caching, validation, and common operations
 * 
 * @package App\Core
 */
abstract class Service {
    protected $repository;
    protected $cache;
    protected $cacheEnabled = true;
    protected $cacheTTL = 3600; // 1 hour default
    
    /**
     * Constructor
     * 
     * @param Repository $repository Repository instance
     */
    public function __construct($repository = null) {
        $this->repository = $repository;
        $this->cache = new Cache();
    }
    
    /**
     * Get cached value
     * 
     * @param string $key Cache key
     * @return mixed Cached value or null
     */
    protected function cacheGet($key) {
        if (!$this->cacheEnabled) {
            return null;
        }
        return $this->cache->get($key);
    }
    
    /**
     * Set cached value
     * 
     * @param string $key Cache key
     * @param mixed $value Value to cache
     * @param int $ttl Time to live in seconds
     * @return bool Success status
     */
    protected function cacheSet($key, $value, $ttl = null) {
        if (!$this->cacheEnabled) {
            return false;
        }
        $ttl = $ttl ?? $this->cacheTTL;
        return $this->cache->set($key, $value, $ttl);
    }
    
    /**
     * Delete cached value
     * 
     * @param string $key Cache key
     * @return bool Success status
     */
    protected function cacheDelete($key) {
        if (!$this->cacheEnabled) {
            return false;
        }
        return $this->cache->delete($key);
    }
    
    /**
     * Clear all cache for this service
     * 
     * @param string $prefix Cache key prefix
     * @return bool Success status
     */
    protected function cacheClear($prefix = null) {
        if (!$this->cacheEnabled) {
            return false;
        }
        return $this->cache->clear($prefix);
    }
    
    /**
     * Generate cache key
     * 
     * @param string $method Method name
     * @param array $params Method parameters
     * @return string Cache key
     */
    protected function cacheKey($method, $params = []) {
        $key = get_class($this) . '::' . $method;
        if (!empty($params)) {
            $key .= '::' . md5(json_encode($params));
        }
        return $key;
    }
    
    /**
     * Execute with caching
     * 
     * @param string $key Cache key
     * @param callable $callback Function to execute if cache miss
     * @param int $ttl Time to live
     * @return mixed Result
     */
    protected function cacheRemember($key, callable $callback, $ttl = null) {
        if (!$this->cacheEnabled) {
            return $callback();
        }
        
        $cached = $this->cacheGet($key);
        if ($cached !== null) {
            return $cached;
        }
        
        $result = $callback();
        $this->cacheSet($key, $result, $ttl);
        
        return $result;
    }
    
    /**
     * Validate data
     * 
     * @param array $data Data to validate
     * @param array $rules Validation rules
     * @return array Validation result ['valid' => bool, 'errors' => array]
     */
    protected function validate($data, $rules) {
        $validator = new \App\Helpers\Validator();
        return $validator->validate($data, $rules);
    }
    
    /**
     * Log error
     * 
     * @param string $message Error message
     * @param array $context Additional context
     */
    protected function logError($message, $context = []) {
        $logger = new \App\Helpers\Logger();
        $logger->error($message, $context);
    }
    
    /**
     * Log info
     * 
     * @param string $message Info message
     * @param array $context Additional context
     */
    protected function logInfo($message, $context = []) {
        $logger = new \App\Helpers\Logger();
        $logger->info($message, $context);
    }
    
    /**
     * Transform data for response
     * 
     * @param array $data Raw data
     * @param array $transformations Transformations to apply
     * @return array Transformed data
     */
    protected function transform($data, $transformations = []) {
        foreach ($transformations as $field => $transform) {
            if (isset($data[$field])) {
                if (is_callable($transform)) {
                    $data[$field] = $transform($data[$field]);
                } elseif (is_string($transform)) {
                    $data[$field] = $transform;
                }
            }
        }
        return $data;
    }
    
    /**
     * Paginate results
     * 
     * @param array $items Items to paginate
     * @param int $page Current page
     * @param int $perPage Items per page
     * @return array Paginated data
     */
    protected function paginate($items, $page = 1, $perPage = 10) {
        $total = count($items);
        $offset = ($page - 1) * $perPage;
        $paginatedItems = array_slice($items, $offset, $perPage);
        
        return [
            'data' => $paginatedItems,
            'pagination' => [
                'total' => $total,
                'per_page' => $perPage,
                'current_page' => $page,
                'last_page' => ceil($total / $perPage),
                'from' => $offset + 1,
                'to' => min($offset + $perPage, $total)
            ]
        ];
    }
    
    /**
     * Format date
     * 
     * @param string $date Date string
     * @param string $format Date format
     * @return string Formatted date
     */
    protected function formatDate($date, $format = 'Y-m-d H:i:s') {
        if (empty($date)) {
            return null;
        }
        return date($format, strtotime($date));
    }
    
    /**
     * Sanitize input
     * 
     * @param mixed $input Input to sanitize
     * @return mixed Sanitized input
     */
    protected function sanitize($input) {
        if (is_array($input)) {
            return array_map([$this, 'sanitize'], $input);
        }
        return htmlspecialchars(strip_tags(trim($input)), ENT_QUOTES, 'UTF-8');
    }
    
    /**
     * Enable caching
     */
    public function enableCache() {
        $this->cacheEnabled = true;
    }
    
    /**
     * Disable caching
     */
    public function disableCache() {
        $this->cacheEnabled = false;
    }
    
    /**
     * Set cache TTL
     * 
     * @param int $ttl Time to live in seconds
     */
    public function setCacheTTL($ttl) {
        $this->cacheTTL = $ttl;
    }
}
