<?php
namespace App\Services;

class ImageService
{
    private $cdnUrl;
    private $enabled;

    public function __construct()
    {
        $config = require __DIR__ . '/../config/cdn.php';
        $this->enabled = $config['enabled'];
        $this->cdnUrl = $config['url'];
    }

    /**
     * Get CDN URL for image
     */
    public function getImageUrl($path, $options = [])
    {
        $baseUrl = $this->enabled && $this->cdnUrl 
            ? $this->cdnUrl . '/public/images/' . $path
            : BASE_URL . 'public/images/' . $path;
        
        // Add Cloudflare Image Resizing parameters
        if (!empty($options)) {
            $params = [];
            
            if (isset($options['width'])) {
                $params['width'] = $options['width'];
            }
            
            if (isset($options['height'])) {
                $params['height'] = $options['height'];
            }
            
            if (isset($options['quality'])) {
                $params['quality'] = $options['quality'];
            }
            
            if (isset($options['format'])) {
                $params['format'] = $options['format'];
            }
            
            if (!empty($params)) {
                $baseUrl .= '?' . http_build_query($params);
            }
        }
        
        return $baseUrl;
    }

    /**
     * Generate responsive image sources
     */
    public function getResponsiveImage($path, $sizes = [320, 640, 1024, 1920])
    {
        $sources = [];
        
        foreach ($sizes as $size) {
            $sources[] = [
                'url' => $this->getImageUrl($path, ['width' => $size]),
                'width' => $size
            ];
        }
        
        return $sources;
    }

    /**
     * Generate srcset attribute
     */
    public function getSrcset($path, $sizes = [320, 640, 1024, 1920])
    {
        $sources = $this->getResponsiveImage($path, $sizes);
        $srcset = [];
        
        foreach ($sources as $source) {
            $srcset[] = $source['url'] . ' ' . $source['width'] . 'w';
        }
        
        return implode(', ', $srcset);
    }

    /**
     * Check if CDN is enabled
     */
    public function isEnabled()
    {
        return $this->enabled && !empty($this->cdnUrl);
    }
}
