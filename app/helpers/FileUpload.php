<?php
/**
 * MyWisata Application - FileUpload Class
 * 
 * Handles secure file uploads with validation.
 * 
 * @package MyWisata
 * @version 1.0.0
 * @since 2026-06-30
 */

class FileUpload {
    
    /**
     * Upload file with security checks
     * 
     * @param array $file $_FILES array element
     * @param string $targetDir Target directory
     * @param array $allowedTypes Allowed MIME types
     * @param int $maxSize Maximum file size in bytes
     * @param array $options Additional options (maxWidth, maxHeight, etc.)
     * @return string|false File path on success, false on failure
     */
    public static function upload($file, $targetDir, $allowedTypes = null, $maxSize = null, $options = []) {
        // Use defaults if not specified
        if ($allowedTypes === null) {
            $allowedTypes = defined('ALLOWED_IMAGE_TYPES') ? ALLOWED_IMAGE_TYPES : 
                           ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
        }
        if ($maxSize === null) {
            $maxSize = defined('MAX_UPLOAD_SIZE') ? MAX_UPLOAD_SIZE : 5242880; // 5MB
        }
        
        // Check for upload errors
        if ($file['error'] !== UPLOAD_ERR_OK) {
            $errorMessages = [
                UPLOAD_ERR_INI_SIZE => 'File terlalu besar (melebihi upload_max_filesize)',
                UPLOAD_ERR_FORM_SIZE => 'File terlalu besar (melebihi MAX_FILE_SIZE)',
                UPLOAD_ERR_PARTIAL => 'File hanya terupload sebagian',
                UPLOAD_ERR_NO_FILE => 'Tidak ada file yang diupload',
                UPLOAD_ERR_NO_TMP_DIR => 'Temporary folder tidak ditemukan',
                UPLOAD_ERR_CANT_WRITE => 'Gagal menulis ke disk',
                UPLOAD_ERR_EXTENSION => 'File upload dihentikan oleh ekstensi PHP'
            ];
            throw new Exception($errorMessages[$file['error']] ?? 'Upload error: ' . $file['error']);
        }
        
        // Check file size
        if ($file['size'] > $maxSize) {
            throw new Exception('File terlalu besar (maksimum: ' . self::formatSize($maxSize) . ')');
        }
        
        // Check file type using MIME type
        $finfo = new finfo(FILEINFO_MIME_TYPE);
        $mimeType = $finfo->file($file['tmp_name']);
        
        if (!in_array($mimeType, $allowedTypes)) {
            throw new Exception('Tipe file tidak diizinkan. Tipe yang diizinkan: ' . implode(', ', $allowedTypes));
        }
        
        // Additional check using file extension
        $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'pdf', 'mp3', 'wav', 'ogg'];
        if (!in_array($extension, $allowedExtensions)) {
            throw new Exception('Ekstensi file tidak diizinkan');
        }
        
        // Validate image dimensions if it's an image
        if (strpos($mimeType, 'image/') === 0) {
            $imageInfo = getimagesize($file['tmp_name']);
            if ($imageInfo === false) {
                throw new Exception('File bukan gambar yang valid');
            }
            
            // Check max dimensions if specified
            if (isset($options['maxWidth']) && $imageInfo[0] > $options['maxWidth']) {
                throw new Exception('Lebar gambar terlalu besar (maksimum: ' . $options['maxWidth'] . 'px)');
            }
            if (isset($options['maxHeight']) && $imageInfo[1] > $options['maxHeight']) {
                throw new Exception('Tinggi gambar terlalu besar (maksimum: ' . $options['maxHeight'] . 'px)');
            }
            
            // Check min dimensions if specified
            if (isset($options['minWidth']) && $imageInfo[0] < $options['minWidth']) {
                throw new Exception('Lebar gambar terlalu kecil (minimum: ' . $options['minWidth'] . 'px)');
            }
            if (isset($options['minHeight']) && $imageInfo[1] < $options['minHeight']) {
                throw new Exception('Tinggi gambar terlalu kecil (minimum: ' . $options['minHeight'] . 'px)');
            }
        }
        
        // Validate file content (double-check for embedded scripts)
        if (self::containsMaliciousContent($file['tmp_name'])) {
            throw new Exception('File mengandung konten yang mencurigakan');
        }
        
        // Generate safe filename
        $filename = bin2hex(random_bytes(16)) . '.' . $extension;
        $targetPath = rtrim($targetDir, '/') . '/' . $filename;
        
        // Ensure target directory exists
        if (!is_dir($targetDir)) {
            mkdir($targetDir, 0777, true);
        }
        
        // Move uploaded file
        if (!move_uploaded_file($file['tmp_name'], $targetPath)) {
            throw new Exception('Gagal menyimpan file');
        }
        
        // Set proper permissions
        chmod($targetPath, 0644);
        
        // Log the upload
        Logger::audit('FILE_UPLOAD', 'uploads', "File uploaded: {$filename}", [], [
            'original_name' => $file['name'],
            'size' => $file['size'],
            'type' => $mimeType,
            'target_path' => $targetPath
        ]);
        
        return $filename;
    }
    
    /**
     * Delete file
     * 
     * @param string $filePath File path
     * @return bool
     */
    public static function delete($filePath) {
        $fullPath = APP_ROOT . '/public/uploads/' . $filePath;
        
        if (file_exists($fullPath)) {
            if (unlink($fullPath)) {
                Logger::info('File deleted', ['file_path' => $filePath]);
                return true;
            }
        }
        
        return false;
    }
    
    /**
     * Format file size for display
     * 
     * @param int $bytes Size in bytes
     * @return string Formatted size
     */
    private static function formatSize($bytes) {
        $units = ['B', 'KB', 'MB', 'GB'];
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        $bytes /= pow(1024, $pow);
        return round($bytes, 2) . ' ' . $units[$pow];
    }
    
    /**
     * Check if file contains malicious content
     * 
     * @param string $filePath Path to file
     * @return bool True if malicious content found
     */
    private static function containsMaliciousContent($filePath) {
        $content = file_get_contents($filePath);
        
        // Check for common script patterns
        $maliciousPatterns = [
            '/<\?php/i',
            '/<script/i',
            '/javascript:/i',
            '/on\w+\s*=/i',  // Event handlers like onclick=
            '/data:text\/html/i',
            '/vbscript:/i',
            '/eval\s*\(/i',
            '/base64_decode/i'
        ];
        
        foreach ($maliciousPatterns as $pattern) {
            if (preg_match($pattern, $content)) {
                Logger::warning('Malicious content detected in file', ['file' => $filePath, 'pattern' => $pattern]);
                return true;
            }
        }
        
        return false;
    }
}
