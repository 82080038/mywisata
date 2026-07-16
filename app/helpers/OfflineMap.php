<?php
/**
 * MyWisata Application - Offline Map Helper
 * 
 * Handles offline map functionality and caching.
 * 
 * @package MyWisata
 * @version 1.0.0
 * @since 2026-07-16
 */

class OfflineMap {
    
    private $cacheDir;
    private $tileCacheDir;
    
    public function __construct() {
        $this->cacheDir = ROOT_PATH . '/storage/cache/maps';
        $this->tileCacheDir = $this->cacheDir . '/tiles';
        
        // Create cache directories if they don't exist
        if (!is_dir($this->cacheDir)) {
            mkdir($this->cacheDir, 0755, true);
        }
        if (!is_dir($this->tileCacheDir)) {
            mkdir($this->tileCacheDir, 0755, true);
        }
    }
    
    /**
     * Get offline map data for a destination
     * 
     * @param int $destinationId Destination ID
     * @return array|false
     */
    public function getDestinationMap($destinationId) {
        $cacheFile = $this->cacheDir . '/destination_' . $destinationId . '.json';
        
        if (file_exists($cacheFile)) {
            return json_decode(file_get_contents($cacheFile), true);
        }
        
        return false;
    }
    
    /**
     * Cache destination map data
     * 
     * @param int $destinationId Destination ID
     * @param array $mapData Map data to cache
     * @return bool
     */
    public function cacheDestinationMap($destinationId, $mapData) {
        $cacheFile = $this->cacheDir . '/destination_' . $destinationId . '.json';
        return file_put_contents($cacheFile, json_encode($mapData)) !== false;
    }
    
    /**
     * Get cached map tile
     * 
     * @param int $x Tile X coordinate
     * @param int $y Tile Y coordinate
     * @param int $zoom Zoom level
     * @param string $provider Map provider (osm, google)
     * @return string|false
     */
    public function getTile($x, $y, $zoom, $provider = 'osm') {
        $tileFile = $this->tileCacheDir . '/' . $provider . '_' . $zoom . '_' . $x . '_' . $y . '.png';
        
        if (file_exists($tileFile)) {
            return file_get_contents($tileFile);
        }
        
        return false;
    }
    
    /**
     * Cache map tile
     * 
     * @param int $x Tile X coordinate
     * @param int $y Tile Y coordinate
     * @param int $zoom Zoom level
     * @param string $tileData Tile image data
     * @param string $provider Map provider
     * @return bool
     */
    public function cacheTile($x, $y, $zoom, $tileData, $provider = 'osm') {
        $tileFile = $this->tileCacheDir . '/' . $provider . '_' . $zoom . '_' . $x . '_' . $y . '.png';
        return file_put_contents($tileFile, $tileData) !== false;
    }
    
    /**
     * Download and cache tiles for a bounding box
     * 
     * @param float $minLat Minimum latitude
     * @param float $maxLat Maximum latitude
     * @param float $minLon Minimum longitude
     * @param float $maxLon Maximum longitude
     * @param int $minZoom Minimum zoom level
     * @param int $maxZoom Maximum zoom level
     * @param string $provider Map provider
     * @return array Downloaded tiles count
     */
    public function cacheBoundingBox($minLat, $maxLat, $minLon, $maxLon, $minZoom = 10, $maxZoom = 15, $provider = 'osm') {
        $downloaded = 0;
        
        for ($zoom = $minZoom; $zoom <= $maxZoom; $zoom++) {
            $minTileX = $this->lonToTileX($minLon, $zoom);
            $maxTileX = $this->lonToTileX($maxLon, $zoom);
            $minTileY = $this->latToTileY($maxLat, $zoom);
            $maxTileY = $this->latToTileY($minLat, $zoom);
            
            for ($x = $minTileX; $x <= $maxTileX; $x++) {
                for ($y = $minTileY; $y <= $maxTileY; $y++) {
                    $tileUrl = $this->getTileUrl($x, $y, $zoom, $provider);
                    $tileData = $this->downloadTile($tileUrl);
                    
                    if ($tileData) {
                        $this->cacheTile($x, $y, $zoom, $tileData, $provider);
                        $downloaded++;
                    }
                }
            }
        }
        
        return [
            'downloaded' => $downloaded,
            'min_zoom' => $minZoom,
            'max_zoom' => $maxZoom
        ];
    }
    
    /**
     * Get tile URL for provider
     * 
     * @param int $x Tile X coordinate
     * @param int $y Tile Y coordinate
     * @param int $zoom Zoom level
     * @param string $provider Map provider
     * @return string
     */
    private function getTileUrl($x, $y, $zoom, $provider) {
        switch ($provider) {
            case 'osm':
                return "https://tile.openstreetmap.org/{$zoom}/{$x}/{$y}.png";
            case 'google':
                return "https://mt1.google.com/vt/lyrs=m&x={$x}&y={$y}&z={$zoom}";
            default:
                return "https://tile.openstreetmap.org/{$zoom}/{$x}/{$y}.png";
        }
    }
    
    /**
     * Download tile from URL
     * 
     * @param string $url Tile URL
     * @return string|false
     */
    private function downloadTile($url) {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_USERAGENT, 'MyWisata/1.0');
        
        $data = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        if ($httpCode === 200 && $data) {
            return $data;
        }
        
        return false;
    }
    
    /**
     * Convert longitude to tile X coordinate
     * 
     * @param float $lon Longitude
     * @param int $zoom Zoom level
     * @return int
     */
    private function lonToTileX($lon, $zoom) {
        return floor(($lon + 180) / 360 * pow(2, $zoom));
    }
    
    /**
     * Convert latitude to tile Y coordinate
     * 
     * @param float $lat Latitude
     * @param int $zoom Zoom level
     * @return int
     */
    private function latToTileY($lat, $zoom) {
        return floor((1 - log(tan(deg2rad($lat)) + 1 / cos(deg2rad($lat))) / pi()) / 2 * pow(2, $zoom));
    }
    
    /**
     * Get cached map statistics
     * 
     * @return array
     */
    public function getCacheStats() {
        $tileCount = count(glob($this->tileCacheDir . '/*.png'));
        $destinationCount = count(glob($this->cacheDir . '/destination_*.json'));
        $totalSize = $this->getDirectorySize($this->cacheDir);
        
        return [
            'tile_count' => $tileCount,
            'destination_count' => $destinationCount,
            'total_size' => $totalSize,
            'total_size_mb' => round($totalSize / 1024 / 1024, 2)
        ];
    }
    
    /**
     * Get directory size
     * 
     * @param string $directory Directory path
     * @return int Size in bytes
     */
    private function getDirectorySize($directory) {
        $size = 0;
        $files = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($directory),
            RecursiveIteratorIterator::LEAVES_ONLY
        );
        
        foreach ($files as $file) {
            if ($file->isFile()) {
                $size += $file->getSize();
            }
        }
        
        return $size;
    }
    
    /**
     * Clear cached tiles
     * 
     * @param string $provider Optional provider filter
     * @return int Number of files deleted
     */
    public function clearTileCache($provider = null) {
        $pattern = $provider ? $this->tileCacheDir . '/' . $provider . '_*.png' : $this->tileCacheDir . '/*.png';
        $files = glob($pattern);
        $deleted = 0;
        
        foreach ($files as $file) {
            if (unlink($file)) {
                $deleted++;
            }
        }
        
        return $deleted;
    }
    
    /**
     * Clear destination map cache
     * 
     * @param int $destinationId Optional destination ID
     * @return int Number of files deleted
     */
    public function clearDestinationCache($destinationId = null) {
        if ($destinationId) {
            $file = $this->cacheDir . '/destination_' . $destinationId . '.json';
            if (file_exists($file) && unlink($file)) {
                return 1;
            }
            return 0;
        }
        
        $files = glob($this->cacheDir . '/destination_*.json');
        $deleted = 0;
        
        foreach ($files as $file) {
            if (unlink($file)) {
                $deleted++;
            }
        }
        
        return $deleted;
    }
    
    /**
     * Get map providers
     * 
     * @return array
     */
    public function getProviders() {
        return [
            'osm' => [
                'name' => 'OpenStreetMap',
                'url' => 'https://tile.openstreetmap.org/{z}/{x}/{y}.png',
                'attribution' => '© OpenStreetMap contributors'
            ],
            'google' => [
                'name' => 'Google Maps',
                'url' => 'https://mt1.google.com/vt/lyrs=m&x={x}&y={y}&z={z}',
                'attribution' => '© Google Maps'
            ]
        ];
    }
}
