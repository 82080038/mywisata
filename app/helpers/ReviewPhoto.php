<?php
/**
 * MyWisata Application - Review Photo Helper
 * 
 * Handles review photo uploads and management.
 * 
 * @package MyWisata
 * @version 1.0.0
 * @since 2026-07-15
 */

class ReviewPhoto {
    
    /**
     * Upload review photo
     * 
     * @param array $file Uploaded file
     * @param int $reviewId Review ID
     * @param string $caption Optional caption
     * @return array Result
     */
    public static function upload($file, $reviewId, $caption = null) {
        // Validate file
        if (!isset($file['tmp_name']) || !is_uploaded_file($file['tmp_name'])) {
            return [
                'success' => false,
                'message' => 'Invalid file upload'
            ];
        }
        
        // Check file type
        $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mimeType = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);
        
        if (!in_array($mimeType, $allowedTypes)) {
            return [
                'success' => false,
                'message' => 'Invalid file type. Only JPEG, PNG, GIF, and WebP are allowed.'
            ];
        }
        
        // Check file size (max 5MB)
        $maxSize = 5 * 1024 * 1024; // 5MB
        if ($file['size'] > $maxSize) {
            return [
                'success' => false,
                'message' => 'File size exceeds maximum limit of 5MB'
            ];
        }
        
        // Generate unique filename
        $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
        $filename = uniqid('review_', true) . '_' . time() . '.' . $extension;
        
        // Create upload directory
        $uploadDir = UPLOAD_PATH . 'reviews/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }
        
        // Move uploaded file
        $filepath = $uploadDir . $filename;
        if (!move_uploaded_file($file['tmp_name'], $filepath)) {
            return [
                'success' => false,
                'message' => 'Failed to move uploaded file'
            ];
        }
        
        // Create thumbnail
        $thumbnailPath = self::createThumbnail($filepath, $filename, $uploadDir);
        
        // Save to database
        $reviewPhotoModel = new ReviewPhotoModel();
        $photoId = $reviewPhotoModel->create([
            'review_id' => $reviewId,
            'file_path' => 'reviews/' . $filename,
            'thumbnail_path' => $thumbnailPath ? 'reviews/thumbnails/' . $filename : null,
            'caption' => $caption
        ]);
        
        if ($photoId) {
            // Update review photo count
            $reviewModel = new ReviewModel();
            $reviewModel->incrementPhotoCount($reviewId);
            
            Logger::info('Review photo uploaded', [
                'review_id' => $reviewId,
                'photo_id' => $photoId
            ]);
            
            return [
                'success' => true,
                'message' => 'Photo uploaded successfully',
                'photo_id' => $photoId,
                'file_path' => 'reviews/' . $filename
            ];
        } else {
            // Cleanup file if database insert failed
            @unlink($filepath);
            if ($thumbnailPath) {
                @unlink($uploadDir . 'thumbnails/' . $filename);
            }
            
            return [
                'success' => false,
                'message' => 'Failed to save photo to database'
            ];
        }
    }
    
    /**
     * Create thumbnail for image
     * 
     * @param string $sourcePath Source image path
     * @param string $filename Filename
     * @param string $uploadDir Upload directory
     * @return string|false Thumbnail path or false
     */
    private static function createThumbnail($sourcePath, $filename, $uploadDir) {
        try {
            // Get image info
            $imageInfo = getimagesize($sourcePath);
            if (!$imageInfo) {
                return false;
            }
            
            $sourceWidth = $imageInfo[0];
            $sourceHeight = $imageInfo[1];
            $imageType = $imageInfo[2];
            
            // Thumbnail dimensions
            $thumbWidth = 300;
            $thumbHeight = 200;
            
            // Create thumbnail directory
            $thumbDir = $uploadDir . 'thumbnails/';
            if (!is_dir($thumbDir)) {
                mkdir($thumbDir, 0755, true);
            }
            
            // Create image resource based on type
            switch ($imageType) {
                case IMAGETYPE_JPEG:
                    $source = imagecreatefromjpeg($sourcePath);
                    break;
                case IMAGETYPE_PNG:
                    $source = imagecreatefrompng($sourcePath);
                    break;
                case IMAGETYPE_GIF:
                    $source = imagecreatefromgif($sourcePath);
                    break;
                case IMAGETYPE_WEBP:
                    $source = imagecreatefromwebp($sourcePath);
                    break;
                default:
                    return false;
            }
            
            if (!$source) {
                return false;
            }
            
            // Calculate aspect ratio
            $ratio = min($thumbWidth / $sourceWidth, $thumbHeight / $sourceHeight);
            $newWidth = (int)($sourceWidth * $ratio);
            $newHeight = (int)($sourceHeight * $ratio);
            
            // Create thumbnail
            $thumbnail = imagecreatetruecolor($thumbWidth, $thumbHeight);
            
            // Fill with white background
            $white = imagecolorallocate($thumbnail, 255, 255, 255);
            imagefill($thumbnail, 0, 0, $white);
            
            // Center the image
            $x = (int)(($thumbWidth - $newWidth) / 2);
            $y = (int)(($thumbHeight - $newHeight) / 2);
            
            imagecopyresampled($thumbnail, $source, $x, $y, 0, 0, $newWidth, $newHeight, $sourceWidth, $sourceHeight);
            
            // Save thumbnail
            $thumbPath = $thumbDir . $filename;
            switch ($imageType) {
                case IMAGETYPE_JPEG:
                    imagejpeg($thumbnail, $thumbPath, 85);
                    break;
                case IMAGETYPE_PNG:
                    imagepng($thumbnail, $thumbPath, 9);
                    break;
                case IMAGETYPE_GIF:
                    imagegif($thumbnail, $thumbPath);
                    break;
                case IMAGETYPE_WEBP:
                    imagewebp($thumbnail, $thumbPath, 85);
                    break;
            }
            
            imagedestroy($source);
            imagedestroy($thumbnail);
            
            return 'thumbnails/' . $filename;
        } catch (Exception $e) {
            Logger::error('Failed to create thumbnail', [
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }
    
    /**
     * Delete review photo
     * 
     * @param int $photoId Photo ID
     * @return bool Success
     */
    public static function delete($photoId) {
        $reviewPhotoModel = new ReviewPhotoModel();
        $photo = $reviewPhotoModel->findById($photoId);
        
        if (!$photo) {
            return false;
        }
        
        // Delete files
        $filepath = UPLOAD_PATH . $photo['file_path'];
        if (file_exists($filepath)) {
            @unlink($filepath);
        }
        
        if ($photo['thumbnail_path']) {
            $thumbPath = UPLOAD_PATH . $photo['thumbnail_path'];
            if (file_exists($thumbPath)) {
                @unlink($thumbPath);
            }
        }
        
        // Delete from database
        $result = $reviewPhotoModel->delete($photoId);
        
        if ($result) {
            // Update review photo count
            $reviewModel = new ReviewModel();
            $reviewModel->decrementPhotoCount($photo['review_id']);
            
            Logger::info('Review photo deleted', [
                'photo_id' => $photoId,
                'review_id' => $photo['review_id']
            ]);
        }
        
        return $result;
    }
    
    /**
     * Set primary photo for review
     * 
     * @param int $photoId Photo ID
     * @return bool Success
     */
    public static function setPrimary($photoId) {
        $reviewPhotoModel = new ReviewPhotoModel();
        $photo = $reviewPhotoModel->findById($photoId);
        
        if (!$photo) {
            return false;
        }
        
        // Remove primary from all photos in this review
        $reviewPhotoModel->removePrimaryFromReview($photo['review_id']);
        
        // Set new primary
        return $reviewPhotoModel->setPrimary($photoId);
    }
    
    /**
     * Get photos for review
     * 
     * @param int $reviewId Review ID
     * @return array Photos
     */
    public static function getReviewPhotos($reviewId) {
        $reviewPhotoModel = new ReviewPhotoModel();
        return $reviewPhotoModel->getByReviewId($reviewId);
    }
}
