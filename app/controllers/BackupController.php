<?php
/**
 * MyWisata Application - Backup Controller
 * 
 * Handles database backup and recovery operations.
 * 
 * @package MyWisata
 * @version 1.0.0
 * @since 2026-07-15
 */

class BackupController extends Controller {

    public function __construct() {
        Middleware::requireRole('admin');
    }

    /**
     * Show backup management page
     */
    public function index() {
        $data = [
            'title' => 'Backup Database',
            'backups' => Backup::listBackups()
        ];
        $this->view('admin/backup/index', $data);
    }

    /**
     * Create new database backup
     */
    public function create() {
        $backupDir = APP_ROOT . '/database/backup';
        
        // Create backup directory if not exists
        if (!is_dir($backupDir)) {
            mkdir($backupDir, 0755, true);
        }
        
        $filename = 'mywisata_' . date('Y-m-d_H-i-s') . '.sql';
        $outputFile = $backupDir . '/' . $filename;
        
        $config = require APP_ROOT . '/app/config/database.php';
        
        // Build mysqldump command
        $command = sprintf(
            'mysqldump -h%s -u%s -p%s %s > %s 2>&1',
            escapeshellarg($config['host']),
            escapeshellarg($config['username']),
            escapeshellarg($config['password']),
            escapeshellarg($config['database']),
            escapeshellarg($outputFile)
        );
        
        exec($command, $output, $returnCode);
        
        if ($returnCode === 0 && file_exists($outputFile)) {
            // Compress the backup
            $compressedFile = $outputFile . '.gz';
            $compressCommand = sprintf('gzip %s', escapeshellarg($outputFile));
            exec($compressCommand, $compressOutput, $compressReturnCode);
            
            if ($compressReturnCode === 0) {
                $finalFile = $filename . '.gz';
                Logger::info('Database backup created and compressed', ['file' => $finalFile]);
                
                $this->json([
                    'status' => 'success',
                    'message' => 'Backup database berhasil dibuat',
                    'data' => [
                        'file' => $finalFile,
                        'size' => filesize($compressedFile),
                        'created_at' => time()
                    ]
                ]);
            } else {
                // If compression fails, still return the uncompressed file
                Logger::warning('Database backup created but compression failed', ['file' => $filename]);
                
                $this->json([
                    'status' => 'success',
                    'message' => 'Backup database berhasil dibuat (tanpa kompresi)',
                    'data' => [
                        'file' => $filename,
                        'size' => filesize($outputFile),
                        'created_at' => time()
                    ]
                ]);
            }
        } else {
            Logger::error('Database backup failed', ['return_code' => $returnCode, 'output' => $output]);
            
            $this->json([
                'status' => 'error',
                'message' => 'Gagal membuat backup database. Periksa log untuk detail.'
            ], 500);
        }
    }

    /**
     * List all backup files
     */
    public function list() {
        $backups = Backup::listBackups();
        
        $this->json([
            'status' => 'success',
            'data' => $backups
        ]);
    }

    /**
     * Download backup file
     */
    public function download($filename) {
        $backupDir = APP_ROOT . '/database/backup';
        $filepath = $backupDir . '/' . $filename;
        
        if (!file_exists($filepath)) {
            http_response_code(404);
            echo json_encode(['status' => 'error', 'message' => 'File tidak ditemukan']);
            exit;
        }
        
        // Security check - ensure file is in backup directory
        $realPath = realpath($filepath);
        $backupDirReal = realpath($backupDir);
        
        if (strpos($realPath, $backupDirReal) !== 0) {
            http_response_code(403);
            echo json_encode(['status' => 'error', 'message' => 'Akses ditolak']);
            exit;
        }
        
        $mimeType = mime_content_type($filepath);
        
        header('Content-Type: ' . $mimeType);
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Content-Length: ' . filesize($filepath));
        header('Cache-Control: no-cache, must-revalidate');
        header('Pragma: no-cache');
        
        readfile($filepath);
        exit;
    }

    /**
     * Restore database from backup
     */
    public function restore() {
        $input = json_decode(file_get_contents('php://input'), true);
        
        if (!isset($input['filename'])) {
            $this->json(['status' => 'error', 'message' => 'Filename diperlukan'], 400);
        }
        
        $filename = basename($input['filename']);
        $backupDir = APP_ROOT . '/database/backup';
        $filepath = $backupDir . '/' . $filename;
        
        if (!file_exists($filepath)) {
            $this->json(['status' => 'error', 'message' => 'File backup tidak ditemukan'], 404);
        }
        
        // Security check
        $realPath = realpath($filepath);
        $backupDirReal = realpath($backupDir);
        
        if (strpos($realPath, $backupDirReal) !== 0) {
            $this->json(['status' => 'error', 'message' => 'Akses ditolak'], 403);
        }
        
        $config = require APP_ROOT . '/app/config/database.php';
        
        // Handle compressed files
        if (strpos($filename, '.gz') !== false) {
            $command = sprintf(
                'gunzip -c %s | mysql -h%s -u%s -p%s %s 2>&1',
                escapeshellarg($filepath),
                escapeshellarg($config['host']),
                escapeshellarg($config['username']),
                escapeshellarg($config['password']),
                escapeshellarg($config['database'])
            );
        } else {
            $command = sprintf(
                'mysql -h%s -u%s -p%s %s < %s 2>&1',
                escapeshellarg($config['host']),
                escapeshellarg($config['username']),
                escapeshellarg($config['password']),
                escapeshellarg($config['database']),
                escapeshellarg($filepath)
            );
        }
        
        exec($command, $output, $returnCode);
        
        if ($returnCode === 0) {
            Logger::audit('restore', 'database', "Database restored from {$filename}");
            
            $this->json([
                'status' => 'success',
                'message' => 'Database berhasil dipulihkan'
            ]);
        } else {
            Logger::error('Database restore failed', [
                'return_code' => $returnCode,
                'output' => $output,
                'file' => $filename
            ]);
            
            $this->json([
                'status' => 'error',
                'message' => 'Gagal memulihkan database. Periksa log untuk detail.'
            ], 500);
        }
    }

    /**
     * Delete backup file
     */
    public function delete() {
        $input = json_decode(file_get_contents('php://input'), true);
        
        if (!isset($input['filename'])) {
            $this->json(['status' => 'error', 'message' => 'Filename diperlukan'], 400);
        }
        
        $filename = basename($input['filename']);
        $backupDir = APP_ROOT . '/database/backup';
        $filepath = $backupDir . '/' . $filename;
        
        if (!file_exists($filepath)) {
            $this->json(['status' => 'error', 'message' => 'File tidak ditemukan'], 404);
        }
        
        // Security check
        $realPath = realpath($filepath);
        $backupDirReal = realpath($backupDir);
        
        if (strpos($realPath, $backupDirReal) !== 0) {
            $this->json(['status' => 'error', 'message' => 'Akses ditolak'], 403);
        }
        
        if (unlink($filepath)) {
            Logger::info('Backup file deleted', ['file' => $filename]);
            
            $this->json([
                'status' => 'success',
                'message' => 'File backup berhasil dihapus'
            ]);
        } else {
            $this->json([
                'status' => 'error',
                'message' => 'Gagal menghapus file backup'
            ], 500);
        }
    }

    /**
     * Get backup statistics
     */
    public function stats() {
        $backupDir = APP_ROOT . '/database/backup';
        $backups = Backup::listBackups();
        
        $totalSize = array_sum(array_column($backups, 'size'));
        $totalBackups = count($backups);
        
        // Get latest backup
        $latestBackup = !empty($backups) ? $backups[0] : null;
        
        $this->json([
            'status' => 'success',
            'data' => [
                'total_backups' => $totalBackups,
                'total_size' => $totalSize,
                'total_size_formatted' => $this->formatBytes($totalSize),
                'latest_backup' => $latestBackup
            ]
        ]);
    }

    /**
     * Format bytes to human readable format
     */
    private function formatBytes($bytes, $precision = 2) {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        
        for ($i = 0; $bytes > 1024; $i++) {
            $bytes /= 1024;
        }
        
        return round($bytes, $precision) . ' ' . $units[$i];
    }
}
