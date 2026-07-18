<?php
namespace App\Services;

class AssetService
{
    private $cdnUrl;
    private $version;
    private $enabled;

    public function __construct()
    {
        $config = require __DIR__ . '/../config/external/cdn.php';
        $this->enabled = $config['enabled'];
        $this->cdnUrl = $config['url'];
        $this->version = getenv('APP_VERSION') ?: '1.0.0';
    }

    /**
     * Get CSS URL with version
     */
    public function css($path)
    {
        if ($this->enabled && $this->cdnUrl) {
            return $this->cdnUrl . '/public/css/' . $path . '?v=' . $this->version;
        }
        return BASE_URL . 'public/css/' . $path . '?v=' . $this->version;
    }

    /**
     * Get JS URL with version
     */
    public function js($path)
    {
        if ($this->enabled && $this->cdnUrl) {
            return $this->cdnUrl . '/public/js/' . $path . '?v=' . $this->version;
        }
        return BASE_URL . 'public/js/' . $path . '?v=' . $this->version;
    }

    /**
     * Get image URL
     */
    public function image($path)
    {
        if ($this->enabled && $this->cdnUrl) {
            return $this->cdnUrl . '/public/images/' . $path;
        }
        return BASE_URL . 'public/images/' . $path;
    }

    /**
     * Get asset URL
     */
    public function asset($path)
    {
        if ($this->enabled && $this->cdnUrl) {
            return $this->cdnUrl . '/public/' . $path;
        }
        return BASE_URL . 'public/' . $path;
    }

    /**
     * Get font URL
     */
    public function font($path)
    {
        if ($this->enabled && $this->cdnUrl) {
            return $this->cdnUrl . '/public/fonts/' . $path;
        }
        return BASE_URL . 'public/fonts/' . $path;
    }

    /**
     * Check if CDN is enabled
     */
    public function isEnabled()
    {
        return $this->enabled && !empty($this->cdnUrl);
    }

    /**
     * Get CDN URL
     */
    public function getCdnUrl()
    {
        return $this->cdnUrl;
    }
}
