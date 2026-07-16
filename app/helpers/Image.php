<?php
/**
 * MyWisata Application - Image Helper
 * 
 * Handles image upload, processing, and management.
 * 
 * @package MyWisata
 * @version 1.0.0
 * @since 2026-07-16
 */

class Image {
    
    private $uploadDir;
    private $allowedTypes = ['jpg', 'jpeg', 'png', 'webp'];
    private $maxFileSize = 5242880; // 5MB
    
    public function __construct() {
        $this->uploadDir = ROOT_PATH . '/public/assets/images';
    }
    
    /**
     * Upload image
     * 
     * @param array $file $_FILES array
     * @param string $category Image category (destinations, hotels, restaurants, etc.)
     * @return array|false
     */
    public function upload($file, $category) {
        if (!isset($file['tmp_name']) || !is_uploaded_file($file['tmp_name'])) {
            return false;
        }
        
        // Validate file
        $validation = $this->validate($file);
        if (!$validation['valid']) {
            return ['error' => $validation['error']];
        }
        
        // Create category directory if not exists
        $categoryDir = $this->uploadDir . '/' . $category;
        if (!is_dir($categoryDir)) {
            mkdir($categoryDir, 0755, true);
        }
        
        // Generate unique filename
        $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $filename = $this->generateFilename($extension);
        $filepath = $categoryDir . '/' . $filename;
        
        // Move uploaded file
        if (move_uploaded_file($file['tmp_name'], $filepath)) {
            // Process image (resize, optimize)
            $this->processImage($filepath);
            
            return [
                'success' => true,
                'filename' => $filename,
                'path' => '/assets/images/' . $category . '/' . $filename,
                'url' => BASE_URL . '/assets/images/' . $category . '/' . $filename
            ];
        }
        
        return false;
    }
    
    /**
     * Upload multiple images
     * 
     * @param array $files Array of $_FILES
     * @param string $category Image category
     * @return array
     */
    public function uploadMultiple($files, $category) {
        $results = [];
        
        foreach ($files['name'] as $key => $name) {
            $file = [
                'name' => $name,
                'type' => $files['type'][$key],
                'tmp_name' => $files['tmp_name'][$key],
                'error' => $files['error'][$key],
                'size' => $files['size'][$key]
            ];
            
            $result = $this->upload($file, $category);
            $results[] = $result;
        }
        
        return $results;
    }
    
    /**
     * Validate uploaded file
     * 
     * @param array $file File data
     * @return array
     */
    private function validate($file) {
        // Check file size
        if ($file['size'] > $this->maxFileSize) {
            return ['valid' => false, 'error' => 'File size exceeds maximum limit (5MB)'];
        }
        
        // Check file type
        $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if (!in_array($extension, $this->allowedTypes)) {
            return ['valid' => false, 'error' => 'Invalid file type. Allowed: ' . implode(', ', $this->allowedTypes)];
        }
        
        // Check for upload errors
        if ($file['error'] !== UPLOAD_ERR_OK) {
            $errorMessages = [
                UPLOAD_ERR_INI_SIZE => 'File exceeds upload_max_filesize directive',
                UPLOAD_ERR_FORM_SIZE => 'File exceeds MAX_FILE_SIZE directive',
                UPLOAD_ERR_PARTIAL => 'File was only partially uploaded',
                UPLOAD_ERR_NO_FILE => 'No file was uploaded',
                UPLOAD_ERR_NO_TMP_DIR => 'Missing temporary folder',
                UPLOAD_ERR_CANT_WRITE => 'Failed to write file to disk'
            ];
            
            return ['valid' => false, 'error' => $errorMessages[$file['error']] ?? 'Unknown upload error'];
        }
        
        return ['valid' => true];
    }
    
    /**
     * Generate unique filename
     * 
     * @param string $extension File extension
     * @return string
     */
    private function generateFilename($extension) {
        return uniqid('img_', true) . '_' . time() . '.' . $extension;
    }
    
    /**
     * Process image (resize, optimize)
     * 
     * @param string $filepath Image filepath
     */
    private function processImage($filepath) {
        // Get image info
        $imageInfo = getimagesize($filepath);
        if (!$imageInfo) {
            return;
        }
        
        $width = $imageInfo[0];
        $height = $imageInfo[1];
        $type = $imageInfo[2];
        
        // Only process if image is larger than 1920px
        if ($width > 1920 || $height > 1920) {
            $this->resizeImage($filepath, 1920, 1920);
        }
        
        // Create thumbnail
        $this->createThumbnail($filepath);
    }
    
    /**
     * Resize image
     * 
     * @param string $filepath Image filepath
     * @param int $maxWidth Maximum width
     * @param int $maxHeight Maximum height
     */
    private function resizeImage($filepath, $maxWidth, $maxHeight) {
        $imageInfo = getimagesize($filepath);
        if (!$imageInfo) {
            return;
        }
        
        $width = $imageInfo[0];
        $height = $imageInfo[1];
        $type = $imageInfo[2];
        
        // Calculate new dimensions
        $ratio = min($maxWidth / $width, $maxHeight / $height);
        $newWidth = round($width * $ratio);
        $newHeight = round($height * $ratio);
        
        // Create new image
        $newImage = imagecreatetruecolor($newWidth, $newHeight);
        
        // Load original image
        switch ($type) {
            case IMAGETYPE_JPEG:
                $source = imagecreatefromjpeg($filepath);
                break;
            case IMAGETYPE_PNG:
                $source = imagecreatefrompng($filepath);
                imagealphablending($newImage, false);
                imagesavealpha($newImage, true);
                break;
            case IMAGETYPE_WEBP:
                $source = imagecreatefromwebp($filepath);
                break;
            default:
                return;
        }
        
        // Resize
        imagecopyresampled($newImage, $source, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);
        
        // Save
        switch ($type) {
            case IMAGETYPE_JPEG:
                imagejpeg($newImage, $filepath, 85);
                break;
            case IMAGETYPE_PNG:
                imagepng($newImage, $filepath, 9);
                break;
            case IMAGETYPE_WEBP:
                imagewebp($newImage, $filepath, 80);
                break;
        }
        
        imagedestroy($newImage);
        imagedestroy($source);
    }
    
    /**
     * Create thumbnail
     * 
     * @param string $filepath Image filepath
     */
    private function createThumbnail($filepath) {
        $imageInfo = getimagesize($filepath);
        if (!$imageInfo) {
            return;
        }
        
        $pathInfo = pathinfo($filepath);
        $thumbPath = $pathInfo['dirname'] . '/thumb_' . $pathInfo['basename'];
        
        $width = $imageInfo[0];
        $height = $imageInfo[1];
        $type = $imageInfo[2];
        
        // Thumbnail dimensions
        $thumbWidth = 300;
        $thumbHeight = 200;
        
        // Create thumbnail
        $thumbImage = imagecreatetruecolor($thumbWidth, $thumbHeight);
        
        // Load original
        switch ($type) {
            case IMAGETYPE_JPEG:
                $source = imagecreatefromjpeg($filepath);
                break;
            case IMAGETYPE_PNG:
                $source = imagecreatefrompng($filepath);
                imagealphablending($thumbImage, false);
                imagesavealpha($thumbImage, true);
                break;
            case IMAGETYPE_WEBP:
                $source = imagecreatefromwebp($filepath);
                break;
            default:
                return;
        }
        
        // Crop and resize
        $this->smartResize($source, $thumbImage, $width, $height, $thumbWidth, $thumbHeight);
        
        // Save thumbnail
        switch ($type) {
            case IMAGETYPE_JPEG:
                imagejpeg($thumbImage, $thumbPath, 85);
                break;
            case IMAGETYPE_PNG:
                imagepng($thumbImage, $thumbPath, 9);
                break;
            case IMAGETYPE_WEBP:
                imagewebp($thumbImage, $thumbPath, 80);
                break;
        }
        
        imagedestroy($thumbImage);
        imagedestroy($source);
    }
    
    /**
     * Smart resize (crop to center)
     * 
     * @param resource $source Source image
     * @param resource $destination Destination image
     * @param int $srcWidth Source width
     * @param int $srcHeight Source height
     * @param int $destWidth Destination width
     * @param int $destHeight Destination height
     */
    private function smartResize($source, $destination, $srcWidth, $srcHeight, $destWidth, $destHeight) {
        $srcRatio = $srcWidth / $srcHeight;
        $destRatio = $destWidth / $destHeight;
        
        if ($srcRatio > $destRatio) {
            // Crop width
            $newWidth = $srcHeight * $destRatio;
            $x = ($srcWidth - $newWidth) / 2;
            $y = 0;
            $width = $newWidth;
            $height = $srcHeight;
        } else {
            // Crop height
            $newHeight = $srcWidth / $destRatio;
            $x = 0;
            $y = ($srcHeight - $newHeight) / 2;
            $width = $srcWidth;
            $height = $newHeight;
        }
        
        imagecopyresampled($destination, $source, 0, 0, $x, $y, $destWidth, $destHeight, $width, $height);
    }
    
    /**
     * Delete image
     * 
     * @param string $path Image path
     * @return bool
     */
    public function delete($path) {
        $filepath = ROOT_PATH . '/public' . $path;
        
        if (file_exists($filepath)) {
            // Also delete thumbnail
            $pathInfo = pathinfo($filepath);
            $thumbPath = $pathInfo['dirname'] . '/thumb_' . $pathInfo['basename'];
            
            if (file_exists($thumbPath)) {
                unlink($thumbPath);
            }
            
            return unlink($filepath);
        }
        
        return false;
    }
    
    /**
     * Get image URL
     * 
     * @param string $filename Image filename
     * @param string $category Image category
     * @return string
     */
    public function getUrl($filename, $category) {
        if (empty($filename)) {
            return $this->getPlaceholder($category);
        }
        
        $path = '/assets/images/' . $category . '/' . $filename;
        
        if (file_exists(ROOT_PATH . '/public' . $path)) {
            return BASE_URL . $path;
        }
        
        return $this->getPlaceholder($category);
    }
    
    /**
     * Get thumbnail URL
     * 
     * @param string $filename Image filename
     * @param string $category Image category
     * @return string
     */
    public function getThumbnailUrl($filename, $category) {
        if (empty($filename)) {
            return $this->getPlaceholder($category);
        }
        
        $path = '/assets/images/' . $category . '/thumb_' . $filename;
        
        if (file_exists(ROOT_PATH . '/public' . $path)) {
            return BASE_URL . $path;
        }
        
        return $this->getUrl($filename, $category);
    }
    
    /**
     * Get placeholder image URL
     * 
     * @param string $category Image category
     * @return string
     */
    public function getPlaceholder($category) {
        $placeholders = [
            'destinations' => 'https://via.placeholder.com/800x600/3b82f6/ffffff?text=MyWisata+Destination',
            'hotels' => 'https://via.placeholder.com/800x600/10b981/ffffff?text=MyWisata+Hotel',
            'restaurants' => 'https://via.placeholder.com/800x600/f59e0b/ffffff?text=MyWisata+Restaurant',
            'tour_guides' => 'https://via.placeholder.com/400x400/8b5cf6/ffffff?text=Guide',
            'users' => 'https://via.placeholder.com/200x200/6366f1/ffffff?text=User',
            'avatars' => 'https://via.placeholder.com/200x200/6366f1/ffffff?text=Avatar',
            'badges' => 'https://via.placeholder.com/100x100/ec4899/ffffff?text=Badge'
        ];
        
        return $placeholders[$category] ?? $placeholders['destinations'];
    }
    
    /**
     * Get image dimensions
     * 
     * @param string $path Image path
     * @return array|false
     */
    public function getDimensions($path) {
        $filepath = ROOT_PATH . '/public' . $path;
        
        if (file_exists($filepath)) {
            $info = getimagesize($filepath);
            if ($info) {
                return [
                    'width' => $info[0],
                    'height' => $info[1],
                    'type' => $info[2],
                    'mime' => $info['mime']
                ];
            }
        }
        
        return false;
    }
    
    /**
     * Get image file size
     * 
     * @param string $path Image path
     * @return int|false
     */
    public function getFileSize($path) {
        $filepath = ROOT_PATH . '/public' . $path;
        
        if (file_exists($filepath)) {
            return filesize($filepath);
        }
        
        return false;
    }
}
