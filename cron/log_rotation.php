<?php
/**
 * MyWisata Application - Log Rotation Script
 * 
 * Rotates log files to prevent disk space exhaustion.
 * Should be run daily via cron job.
 * 
 * @package MyWisata
 * @version 1.0.0
 * @since 2026-07-18
 */

// Define paths
define('APP_ROOT', dirname(__DIR__));
define('LOG_PATH', APP_ROOT . '/logs/');
define('ARCHIVE_PATH', LOG_PATH . 'archive/');

// Create archive directory if it doesn't exist
if (!file_exists(ARCHIVE_PATH)) {
    mkdir(ARCHIVE_PATH, 0755, true);
}

// Log files to rotate
$logFiles = [
    'error.log',
    'audit.log',
    'access.log'
];

// Maximum log file size (10MB)
$maxSize = 10 * 1024 * 1024;

// Number of archived logs to keep
$keepArchives = 30;

foreach ($logFiles as $logFile) {
    $logPath = LOG_PATH . $logFile;
    
    if (file_exists($logPath)) {
        $fileSize = filesize($logPath);
        
        // Rotate if file exceeds max size
        if ($fileSize > $maxSize) {
            $timestamp = date('Y-m-d_H-i-s');
            $archiveName = pathinfo($logFile, PATHINFO_FILENAME) . '_' . $timestamp . '.log';
            $archivePath = ARCHIVE_PATH . $archiveName;
            
            // Move current log to archive
            rename($logPath, $archivePath);
            
            // Create new empty log file
            file_put_contents($logPath, '');
            
            // Clean old archives
            cleanOldArchives($logFile, $keepArchives);
            
            echo "Rotated: {$logFile} (Size: " . round($fileSize / 1024 / 1024, 2) . "MB)\n";
        }
    }
}

/**
 * Clean old archived log files
 * 
 * @param string $logFile Original log file name
 * @param int $keep Number of archives to keep
 */
function cleanOldArchives($logFile, $keep) {
    $baseName = pathinfo($logFile, PATHINFO_FILENAME);
    $pattern = ARCHIVE_PATH . $baseName . '_*.log';
    
    $archives = glob($pattern);
    
    // Sort by modification time (oldest first)
    usort($archives, function($a, $b) {
        return filemtime($a) - filemtime($b);
    });
    
    // Delete old archives
    $toDelete = count($archives) - $keep;
    if ($toDelete > 0) {
        for ($i = 0; $i < $toDelete; $i++) {
            if (file_exists($archives[$i])) {
                unlink($archives[$i]);
                echo "Deleted old archive: " . basename($archives[$i]) . "\n";
            }
        }
    }
}

echo "Log rotation completed: " . date('Y-m-d H:i:s') . "\n";
