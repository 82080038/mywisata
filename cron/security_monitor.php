<?php
/**
 * MyWisata Application - Security Monitoring Script
 * 
 * Monitors security events and generates alerts.
 * Should be run hourly via cron job.
 * 
 * @package MyWisata
 * @version 1.0.0
 * @since 2026-07-18
 */

define('APP_ROOT', dirname(__DIR__));
require_once APP_ROOT . '/app/helpers/Logger.php';

// Security thresholds
$thresholds = [
    'failed_logins_per_hour' => 20,
    'suspicious_activity_per_hour' => 50,
    'rate_limit_violations_per_hour' => 10
];

// Check for security events
checkFailedLogins($thresholds['failed_logins_per_hour']);
checkSuspiciousActivity($thresholds['suspicious_activity_per_hour']);
checkRateLimitViolations($thresholds['rate_limit_violations_per_hour']);

/**
 * Check for excessive failed login attempts
 */
function checkFailedLogins($threshold) {
    $auditLog = APP_ROOT . '/logs/audit.log';
    
    if (!file_exists($auditLog)) {
        return;
    }
    
    $oneHourAgo = time() - 3600;
    $failedLogins = 0;
    $ips = [];
    
    $handle = fopen($auditLog, 'r');
    while (($line = fgets($handle)) !== false) {
        if (strpos($line, 'Failed login attempt') !== false) {
            $failedLogins++;
            // Extract IP if possible
            if (preg_match('/"ip":"([^"]+)"/', $line, $matches)) {
                $ips[] = $matches[1];
            }
        }
    }
    fclose($handle);
    
    if ($failedLogins >= $threshold) {
        $message = "SECURITY ALERT: {$failedLogins} failed login attempts in the last hour";
        Logger::security($message, [
            'threshold_exceeded' => true,
            'count' => $failedLogins,
            'unique_ips' => array_unique($ips)
        ]);
        echo "ALERT: {$message}\n";
    }
}

/**
 * Check for suspicious activity
 */
function checkSuspiciousActivity($threshold) {
    $auditLog = APP_ROOT . '/logs/audit.log';
    
    if (!file_exists($auditLog)) {
        return;
    }
    
    $oneHourAgo = time() - 3600;
    $suspiciousCount = 0;
    
    $handle = fopen($auditLog, 'r');
    while (($line = fgets($handle)) !== false) {
        if (strpos($line, 'Suspicious activity detected') !== false) {
            $suspiciousCount++;
        }
    }
    fclose($handle);
    
    if ($suspiciousCount >= $threshold) {
        $message = "SECURITY ALERT: {$suspiciousCount} suspicious activities detected in the last hour";
        Logger::security($message, [
            'threshold_exceeded' => true,
            'count' => $suspiciousCount
        ]);
        echo "ALERT: {$message}\n";
    }
}

/**
 * Check for rate limit violations
 */
function checkRateLimitViolations($threshold) {
    $auditLog = APP_ROOT . '/logs/audit.log';
    
    if (!file_exists($auditLog)) {
        return;
    }
    
    $oneHourAgo = time() - 3600;
    $violations = 0;
    
    $handle = fopen($auditLog, 'r');
    while (($line = fgets($handle)) !== false) {
        if (strpos($line, 'Rate limit exceeded') !== false) {
            $violations++;
        }
    }
    fclose($handle);
    
    if ($violations >= $threshold) {
        $message = "SECURITY ALERT: {$violations} rate limit violations in the last hour";
        Logger::security($message, [
            'threshold_exceeded' => true,
            'count' => $violations
        ]);
        echo "ALERT: {$message}\n";
    }
}

echo "Security monitoring completed: " . date('Y-m-d H:i:s') . "\n";
