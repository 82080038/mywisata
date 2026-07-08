<?php

/**
 * MyWisata Application - Access Log Middleware
 *
 * Middleware to log all HTTP requests for monitoring and analytics.
 *
 * @package MyWisata
 * @version 1.0.0
 * @since 2026-07-01
 */

class AccessLogMiddleware
{
    /**
     * Handle incoming request
     *
     * @param callable $next Next middleware
     * @return void
     */
    public static function handle($next)
    {
        $startTime = microtime(true);

        // Execute request
        $next();

        // Calculate response time
        $responseTime = microtime(true) - $startTime;

        // Log access
        $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
        $url = $_SERVER['REQUEST_URI'] ?? '/';
        $statusCode = http_response_code();

        // Skip logging for static assets
        if (self::shouldSkipLogging($url)) {
            return;
        }

        Logger::access($method, $url, $statusCode, $responseTime);
    }

    /**
     * Determine if request should be skipped from logging
     *
     * @param string $url Request URL
     * @return bool
     */
    private static function shouldSkipLogging($url)
    {
        // Skip static assets
        $skipPatterns = [
            '/assets/',
            '/css/',
            '/js/',
            '/images/',
            '/fonts/',
            '/favicon.',
            '/robots.txt',
        ];

        foreach ($skipPatterns as $pattern) {
            if (strpos($url, $pattern) === 0) {
                return true;
            }
        }

        // Skip health checks
        if ($url === '/health' || $url === '/ping') {
            return true;
        }

        return false;
    }
}
