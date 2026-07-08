<?php

/**
 * MyWisata Application - Logger Class
 *
 * Handles audit logging, error logging, and access logging with rotation.
 *
 * @version 1.0.0
 *
 * @since 2026-06-30
 */
class Logger
{
    /**
     * Log levels
     */
    const DEBUG = 'DEBUG';
    const INFO = 'INFO';
    const WARNING = 'WARNING';
    const ERROR = 'ERROR';
    const CRITICAL = 'CRITICAL';

    /**
     * Maximum log file size in bytes (10MB)
     */
    const MAX_LOG_SIZE = 10485760;

    /**
     * Number of log files to keep
     */
    const MAX_LOG_FILES = 5;

    /**
     * Log audit action to database
     *
     * @param string $action Action performed
     * @param string $module Module name
     * @param string $description Description
     * @param array $oldData Old data (before change)
     * @param array $newData New data (after change)
     */
    public static function audit($action, $module, $description, $oldData = null, $newData = null)
    {
        try {
            $db = Database::getInstance();

            $sql = "INSERT INTO audit_logs (user_id, action, module, description, ip_address, user_agent, old_data, new_data, created_at)
                    VALUES (:user_id, :action, :module, :description, :ip_address, :user_agent, :old_data, :new_data, NOW())";

            $params = [
                'user_id' => Session::get('user_id'),
                'action' => $action,
                'module' => $module,
                'description' => $description,
                'ip_address' => self::getClientIp(),
                'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? null,
                'old_data' => $oldData ? json_encode($oldData) : null,
                'new_data' => $newData ? json_encode($newData) : null,
            ];

            $db->query($sql, $params);

            // Also log to file for backup
            self::logToFile('audit.log', self::INFO, "AUDIT: {$action} - {$module} - {$description}", [
                'user_id' => Session::get('user_id'),
                'ip_address' => self::getClientIp(),
            ]);
        } catch (Exception $e) {
            self::error('Failed to log audit: ' . $e->getMessage());
        }
    }

    /**
     * Log access request
     *
     * @param string $method HTTP method
     * @param string $url Request URL
     * @param int $statusCode Response status code
     * @param float $responseTime Response time in seconds
     */
    public static function access($method, $url, $statusCode, $responseTime)
    {
        $logData = [
            'method' => $method,
            'url' => $url,
            'status' => $statusCode,
            'response_time' => round($responseTime * 1000, 2) . 'ms',
            'ip' => self::getClientIp(),
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? null,
            'user_id' => Session::get('user_id') ?? 'guest',
        ];

        $logMessage = sprintf(
            '%s %s %s %s - %s - %s',
            $logData['method'],
            $logData['url'],
            $logData['status'],
            $logData['response_time'],
            $logData['ip'],
            $logData['user_id']
        );

        self::logToFile('access.log', self::INFO, $logMessage, $logData);
    }

    /**
     * Log debug message
     *
     * @param string $message Debug message
     * @param array $context Additional context
     */
    public static function debug($message, $context = [])
    {
        self::logToFile('debug.log', self::DEBUG, $message, $context);
    }

    /**
     * Log info message
     *
     * @param string $message Info message
     * @param array $context Additional context
     */
    public static function info($message, $context = [])
    {
        self::logToFile('info.log', self::INFO, $message, $context);
    }

    /**
     * Log warning message
     *
     * @param string $message Warning message
     * @param array $context Additional context
     */
    public static function warning($message, $context = [])
    {
        self::logToFile('warning.log', self::WARNING, $message, $context);
    }

    /**
     * Log error message
     *
     * @param string $message Error message
     * @param array $context Additional context
     */
    public static function error($message, $context = [])
    {
        self::logToFile('error.log', self::ERROR, $message, $context);
    }

    /**
     * Log critical message
     *
     * @param string $message Critical message
     * @param array $context Additional context
     */
    public static function critical($message, $context = [])
    {
        self::logToFile('critical.log', self::CRITICAL, $message, $context);
    }

    /**
     * Log message to file with rotation
     *
     * @param string $filename Log filename
     * @param string $level Log level
     * @param string $message Log message
     * @param array $context Additional context
     */
    private static function logToFile($filename, $level, $message, $context = [])
    {
        $logFile = self::getLogPath($filename);
        $logDir = dirname($logFile);

        if (!is_dir($logDir)) {
            mkdir($logDir, 0755, true);
        }

        // Rotate log if file is too large
        self::rotateLog($logFile);

        $timestamp = date('Y-m-d H:i:s');
        $logMessage = "[{$timestamp}] [{$level}] {$message}";

        if (!empty($context)) {
            $logMessage .= " | " . json_encode($context);
        }

        $logMessage .= "\n";

        error_log($logMessage, 3, $logFile);
    }

    /**
     * Get log file path
     *
     * @param string $filename Log filename
     * @return string Full log file path
     */
    private static function getLogPath($filename)
    {
        $logDir = defined('LOG_PATH') ? LOG_PATH : APP_ROOT . '/logs';
        return $logDir . '/' . $filename;
    }

    /**
     * Rotate log file if it exceeds maximum size
     *
     * @param string $logFile Log file path
     */
    private static function rotateLog($logFile)
    {
        if (!file_exists($logFile)) {
            return;
        }

        if (filesize($logFile) < self::MAX_LOG_SIZE) {
            return;
        }

        // Rotate log files
        for ($i = self::MAX_LOG_FILES - 1; $i >= 1; $i--) {
            $oldFile = $i === 1 ? $logFile : substr($logFile, 0, -4) . '.' . ($i - 1) . '.log';
            $newFile = substr($logFile, 0, -4) . '.' . $i . '.log';

            if (file_exists($oldFile)) {
                rename($oldFile, $newFile);
            }
        }

        // Clear current log file
        file_put_contents($logFile, '');
    }

    /**
     * Get client IP address
     *
     * @return string IP address
     */
    private static function getClientIp()
    {
        $ip = null;

        if (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
        } elseif (isset($_SERVER['HTTP_CLIENT_IP'])) {
            $ip = $_SERVER['HTTP_CLIENT_IP'];
        } elseif (isset($_SERVER['REMOTE_ADDR'])) {
            $ip = $_SERVER['REMOTE_ADDR'];
        }

        return $ip ?: 'unknown';
    }

    /**
     * Clear old log files
     *
     * @param int $days Number of days to keep logs
     */
    public static function clearOldLogs($days = 30)
    {
        $logDir = defined('LOG_PATH') ? LOG_PATH : APP_ROOT . '/logs';
        $cutoffTime = time() - ($days * 86400);

        $files = glob($logDir . '/*.log');

        foreach ($files as $file) {
            if (filemtime($file) < $cutoffTime) {
                unlink($file);
            }
        }
    }

    /**
     * Get log statistics
     *
     * @return array Log statistics
     */
    public static function getLogStats()
    {
        $logDir = defined('LOG_PATH') ? LOG_PATH : APP_ROOT . '/logs';
        $stats = [
            'total_files' => 0,
            'total_size' => 0,
            'files' => [],
        ];

        $files = glob($logDir . '/*.log');

        foreach ($files as $file) {
            $stats['total_files']++;
            $stats['total_size'] += filesize($file);
            $stats['files'][] = [
                'name' => basename($file),
                'size' => filesize($file),
                'modified' => date('Y-m-d H:i:s', filemtime($file)),
            ];
        }

        return $stats;
    }
}
