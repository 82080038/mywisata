<?php
/**
 * MyWisata Application - Location Data Helper
 * 
 * Handles integration with external location-based data sources for database enrichment.
 * 
 * @package MyWisata
 * @version 1.0.0
 * @since 2026-07-16
 */

class LocationData {
    
    private $db;
    private $cacheDir;
    
    // API Keys (set via environment variables)
    private $googlePlacesApiKey;
    private $openTripMapApiKey;
    
    public function __construct() {
        $this->db = Database::getInstance();
        $this->cacheDir = ROOT_PATH . '/storage/cache/location_data';
        
        if (!is_dir($this->cacheDir)) {
            mkdir($this->cacheDir, 0755, true);
        }
        
        $this->googlePlacesApiKey = getenv('GOOGLE_PLACES_API_KEY') ?: '';
        $this->openTripMapApiKey = getenv('OPENTRIPMAP_API_KEY') ?: '';
    }
    
    /**
     * Fetch data from SISPARNAS (Indonesia Tourism Information System)
     * 
     * @param string $province Province name
     * @param string $category Category (wisata_alam, wisata_budaya, wisata_buatan)
     * @return array
     */
    public function fetchSisparnasData($province = null, $category = null) {
        $url = 'https://sisparnas.kemenpar.go.id/api/v1/destinations';
        
        $params = [];
        if ($province) {
            $params['province'] = $province;
        }
        if ($category) {
            $params['category'] = $category;
        }
        
        if (!empty($params)) {
            $url .= '?' . http_build_query($params);
        }
        
        return $this->fetchFromAPI($url, 'sisparnas');
    }
    
    /**
     * Fetch data from Kemenpar API (Ministry of Tourism)
     * 
     * @param string $endpoint API endpoint
     * @param array $params Query parameters
     * @return array
     */
    public function fetchKemenparData($endpoint, $params = []) {
        $baseUrl = 'https://api-industri.kemenpar.go.id/api/v1';
        $url = $baseUrl . '/' . $endpoint;
        
        if (!empty($params)) {
            $url .= '?' . http_build_query($params);
        }
        
        return $this->fetchFromAPI($url, 'kemenpar');
    }
    
    /**
     * Search places using Google Places API
     * 
     * @param string $query Search query
     * @param string $location Location (lat,lng)
     * @param int $radius Search radius in meters
     * @return array
     */
    public function searchGooglePlaces($query, $location = null, $radius = 50000) {
        if (!$this->googlePlacesApiKey) {
            return $this->getMockGooglePlaces($query);
        }
        
        $url = 'https://maps.googleapis.com/maps/api/place/textsearch/json';
        $params = [
            'query' => $query,
            'key' => $this->googlePlacesApiKey,
            'language' => 'id'
        ];
        
        if ($location) {
            $params['location'] = $location;
            $params['radius'] = $radius;
        }
        
        $url .= '?' . http_build_query($params);
        
        $response = $this->fetchFromAPI($url, 'google_places');
        
        if (isset($response['results'])) {
            return $this->normalizeGooglePlacesData($response['results']);
        }
        
        return [];
    }
    
    /**
     * Get place details from Google Places API
     * 
     * @param string $placeId Place ID
     * @return array
     */
    public function getGooglePlaceDetails($placeId) {
        if (!$this->googlePlacesApiKey) {
            return [];
        }
        
        $url = 'https://maps.googleapis.com/maps/api/place/details/json';
        $params = [
            'place_id' => $placeId,
            'key' => $this->googlePlacesApiKey,
            'language' => 'id'
        ];
        
        $url .= '?' . http_build_query($params);
        
        $response = $this->fetchFromAPI($url, 'google_places');
        
        if (isset($response['result'])) {
            return $this->normalizeGooglePlaceDetails($response['result']);
        }
        
        return [];
    }
    
    /**
     * Search OpenStreetMap for locations
     * 
     * @param string $query Search query
     * @param string $bbox Bounding box (min_lat,min_lon,max_lat,max_lon)
     * @return array
     */
    public function searchOpenStreetMap($query, $bbox = null) {
        $url = 'https://nominatim.openstreetmap.org/search';
        $params = [
            'q' => $query,
            'format' => 'json',
            'limit' => 20,
            'addressdetails' => 1,
            'countrycodes' => 'id'
        ];
        
        if ($bbox) {
            $params['viewbox'] = $bbox;
            $params['bounded'] = 1;
        }
        
        $url .= '?' . http_build_query($params);
        
        $response = $this->fetchFromAPI($url, 'osm');
        
        return $this->normalizeOSMData($response);
    }
    
    /**
     * Search POINDT API (Indonesian address API)
     * 
     * @param string $query Search query
     * @return array
     */
    public function searchPoindt($query) {
        $url = 'https://api.poindt.com/v1/search';
        $params = [
            'q' => $query,
            'limit' => 20
        ];
        
        $url .= '?' . http_build_query($params);
        
        return $this->fetchFromAPI($url, 'poindt');
    }
    
    /**
     * Fetch from OpenTripMap API
     * 
     * @param string $method API method
     * @param array $params Parameters
     * @return array
     */
    public function fetchOpenTripMap($method, $params = []) {
        if (!$this->openTripMapApiKey) {
            return [];
        }
        
        $baseUrl = 'https://api.opentripmap.com/0.1/en';
        $url = $baseUrl . '/' . $method;
        
        $params['apikey'] = $this->openTripMapApiKey;
        $url .= '?' . http_build_query($params);
        
        return $this->fetchFromAPI($url, 'opentripmap');
    }
    
    /**
     * Import tourism destinations from external sources
     * 
     * @param string $source Data source (sisparnas, kemenpar, google, osm)
     * @param array $filters Import filters
     * @return array Import results
     */
    public function importDestinations($source, $filters = []) {
        $results = [
            'success' => 0,
            'failed' => 0,
            'duplicates' => 0,
            'total' => 0
        ];
        
        switch ($source) {
            case 'sisparnas':
                $data = $this->fetchSisparnasData(
                    $filters['province'] ?? null,
                    $filters['category'] ?? null
                );
                break;
                
            case 'kemenpar':
                $data = $this->fetchKemenparData(
                    $filters['endpoint'] ?? 'destinations',
                    $filters['params'] ?? []
                );
                break;
                
            case 'google':
                $data = $this->searchGooglePlaces(
                    $filters['query'] ?? 'wisata Indonesia',
                    $filters['location'] ?? null,
                    $filters['radius'] ?? 50000
                );
                break;
                
            case 'osm':
                $data = $this->searchOpenStreetMap(
                    $filters['query'] ?? 'wisata',
                    $filters['bbox'] ?? null
                );
                break;
                
            default:
                return $results;
        }
        
        if (empty($data)) {
            return $results;
        }
        
        foreach ($data as $item) {
            $results['total']++;
            
            if ($this->importDestination($item, $source)) {
                $results['success']++;
            } else {
                $results['failed']++;
            }
        }
        
        return $results;
    }
    
    /**
     * Import single destination
     * 
     * @param array $data Destination data
     * @param string $source Data source
     * @return bool
     */
    private function importDestination($data, $source) {
        // Check for duplicates
        if ($this->isDuplicateDestination($data)) {
            return false;
        }
        
        $normalizedData = $this->normalizeDestinationData($data, $source);
        
        $sql = "INSERT INTO destinations 
                (name, description, category, province, city, latitude, longitude, 
                 address, phone, website, opening_hours, entrance_fee, 
                 rating, reviews_count, images, source, source_id, created_at)
                VALUES (:name, :description, :category, :province, :city, :latitude, :longitude,
                        :address, :phone, :website, :opening_hours, :entrance_fee,
                        :rating, :reviews_count, :images, :source, :source_id, NOW())";
        
        return $this->db->query($sql, $normalizedData);
    }
    
    /**
     * Check if destination already exists
     * 
     * @param array $data Destination data
     * @return bool
     */
    private function isDuplicateDestination($data) {
        $name = $data['name'] ?? '';
        $latitude = $data['latitude'] ?? null;
        $longitude = $data['longitude'] ?? null;
        
        if ($latitude && $longitude) {
            $sql = "SELECT COUNT(*) as count FROM destinations 
                    WHERE latitude = :latitude AND longitude = :longitude";
            $result = $this->db->query($sql, [
                'latitude' => $latitude,
                'longitude' => $longitude
            ])->fetch();
            
            if ($result['count'] > 0) {
                return true;
            }
        }
        
        if ($name) {
            $sql = "SELECT COUNT(*) as count FROM destinations WHERE name = :name";
            $result = $this->db->query($sql, ['name' => $name])->fetch();
            
            if ($result['count'] > 0) {
                return true;
            }
        }
        
        return false;
    }
    
    /**
     * Normalize destination data for database
     * 
     * @param array $data Raw data
     * @param string $source Data source
     * @return array Normalized data
     */
    private function normalizeDestinationData($data, $source) {
        return [
            'name' => $data['name'] ?? '',
            'description' => $data['description'] ?? '',
            'category' => $this->mapCategory($data['category'] ?? 'wisata'),
            'province' => $data['province'] ?? '',
            'city' => $data['city'] ?? '',
            'latitude' => $data['latitude'] ?? null,
            'longitude' => $data['longitude'] ?? null,
            'address' => $data['address'] ?? '',
            'phone' => $data['phone'] ?? '',
            'website' => $data['website'] ?? '',
            'opening_hours' => $data['opening_hours'] ?? '',
            'entrance_fee' => $data['entrance_fee'] ?? 0,
            'rating' => $data['rating'] ?? 0,
            'reviews_count' => $data['reviews_count'] ?? 0,
            'images' => json_encode($data['images'] ?? []),
            'source' => $source,
            'source_id' => $data['id'] ?? null
        ];
    }
    
    /**
     * Map category to internal category
     * 
     * @param string $category External category
     * @return string
     */
    private function mapCategory($category) {
        $categoryMap = [
            'wisata_alam' => 'nature',
            'wisata_budaya' => 'culture',
            'wisata_buatan' => 'man_made',
            'wisata_sejarah' => 'history',
            'wisata_kuliner' => 'culinary',
            'wisata_belanja' => 'shopping',
            'nature' => 'nature',
            'culture' => 'culture',
            'museum' => 'culture',
            'park' => 'nature',
            'beach' => 'nature',
            'temple' => 'culture',
            'restaurant' => 'culinary',
            'hotel' => 'accommodation'
        ];
        
        return $categoryMap[strtolower($category)] ?? 'general';
    }
    
    /**
     * Normalize Google Places data
     * 
     * @param array $results Google Places results
     * @return array
     */
    private function normalizeGooglePlacesData($results) {
        $normalized = [];
        
        foreach ($results as $place) {
            $normalized[] = [
                'id' => $place['place_id'] ?? null,
                'name' => $place['name'] ?? '',
                'description' => $this->extractDescription($place),
                'category' => $this->extractCategory($place),
                'latitude' => $place['geometry']['location']['lat'] ?? null,
                'longitude' => $place['geometry']['location']['lng'] ?? null,
                'address' => $place['formatted_address'] ?? '',
                'rating' => $place['rating'] ?? 0,
                'reviews_count' => $place['user_ratings_total'] ?? 0,
                'images' => $this->extractPhotos($place),
                'opening_hours' => $this->extractOpeningHours($place)
            ];
        }
        
        return $normalized;
    }
    
    /**
     * Normalize Google Place details
     * 
     * @param array $result Place details result
     * @return array
     */
    private function normalizeGooglePlaceDetails($result) {
        return [
            'id' => $result['place_id'] ?? null,
            'name' => $result['name'] ?? '',
            'description' => $result['editorial_summary']['overview'] ?? '',
            'category' => $this->extractCategory($result),
            'latitude' => $result['geometry']['location']['lat'] ?? null,
            'longitude' => $result['geometry']['location']['lng'] ?? null,
            'address' => $result['formatted_address'] ?? '',
            'phone' => $result['international_phone_number'] ?? '',
            'website' => $result['website'] ?? '',
            'rating' => $result['rating'] ?? 0,
            'reviews_count' => $result['user_ratings_total'] ?? 0,
            'images' => $this->extractPhotos($result),
            'opening_hours' => $this->extractOpeningHours($result)
        ];
    }
    
    /**
     * Normalize OpenStreetMap data
     * 
     * @param array $results OSM results
     * @return array
     */
    private function normalizeOSMData($results) {
        $normalized = [];
        
        foreach ($results as $place) {
            $normalized[] = [
                'id' => $place['place_id'] ?? null,
                'name' => $place['display_name'] ?? '',
                'description' => $place['display_name'] ?? '',
                'category' => $place['type'] ?? 'general',
                'latitude' => $place['lat'] ?? null,
                'longitude' => $place['lon'] ?? null,
                'address' => $place['display_address'] ?? '',
                'province' => $place['address']['state'] ?? '',
                'city' => $place['address']['city'] ?? $place['address']['town'] ?? ''
            ];
        }
        
        return $normalized;
    }
    
    /**
     * Extract description from place data
     * 
     * @param array $place Place data
     * @return string
     */
    private function extractDescription($place) {
        if (isset($place['editorial_summary']['overview'])) {
            return $place['editorial_summary']['overview'];
        }
        
        if (isset($place['types'])) {
            $types = array_filter($place['types'], function($type) {
                return !in_array($type, ['establishment', 'point_of_interest']);
            });
            return implode(', ', array_slice($types, 0, 3));
        }
        
        return '';
    }
    
    /**
     * Extract category from place data
     * 
     * @param array $place Place data
     * @return string
     */
    private function extractCategory($place) {
        if (isset($place['types'])) {
            $typeMap = [
                'tourist_attraction' => 'culture',
                'museum' => 'culture',
                'park' => 'nature',
                'natural_feature' => 'nature',
                'beach' => 'nature',
                'temple' => 'culture',
                'restaurant' => 'culinary',
                'hotel' => 'accommodation',
                'shopping_mall' => 'shopping'
            ];
            
            foreach ($place['types'] as $type) {
                if (isset($typeMap[$type])) {
                    return $typeMap[$type];
                }
            }
        }
        
        return 'general';
    }
    
    /**
     * Extract photos from place data
     * 
     * @param array $place Place data
     * @return array
     */
    private function extractPhotos($place) {
        $photos = [];
        
        if (isset($place['photos'])) {
            foreach ($place['photos'] as $photo) {
                if (isset($photo['photo_reference'])) {
                    $photos[] = [
                        'reference' => $photo['photo_reference'],
                        'width' => $photo['width'] ?? 0,
                        'height' => $photo['height'] ?? 0
                    ];
                }
            }
        }
        
        return $photos;
    }
    
    /**
     * Extract opening hours from place data
     * 
     * @param array $place Place data
     * @return string
     */
    private function extractOpeningHours($place) {
        if (isset($place['opening_hours']['periods'])) {
            $hours = [];
            foreach ($place['opening_hours']['periods'] as $period) {
                $day = $period['open']['day'] ?? '';
                $open = $period['open']['time'] ?? '';
                $close = $period['close']['time'] ?? '';
                $hours[] = "{$day}: {$open} - {$close}";
            }
            return implode('; ', $hours);
        }
        
        return '';
    }
    
    /**
     * Fetch from API with caching
     * 
     * @param string $url API URL
     * @param string $source Source identifier
     * @return array
     */
    private function fetchFromAPI($url, $source) {
        $cacheKey = md5($url);
        $cacheFile = $this->cacheDir . '/' . $source . '_' . $cacheKey . '.json';
        
        // Check cache (1 hour TTL)
        if (file_exists($cacheFile) && (time() - filemtime($cacheFile)) < 3600) {
            return json_decode(file_get_contents($cacheFile), true);
        }
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_USERAGENT, 'MyWisata/1.0');
        
        $data = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        if ($httpCode === 200 && $data) {
            $decoded = json_decode($data, true);
            
            // Cache the response
            file_put_contents($cacheFile, json_encode($decoded));
            
            return $decoded;
        }
        
        Logger::error('API request failed', [
            'source' => $source,
            'url' => $url,
            'http_code' => $httpCode
        ]);
        
        return [];
    }
    
    /**
     * Get mock Google Places data (for testing without API key)
     * 
     * @param string $query Search query
     * @return array
     */
    private function getMockGooglePlaces($query) {
        return [
            [
                'id' => 'mock_1',
                'name' => 'Mock Destination - ' . $query,
                'description' => 'This is a mock destination for testing',
                'category' => 'general',
                'latitude' => -6.2088,
                'longitude' => 106.8456,
                'address' => 'Jakarta, Indonesia',
                'rating' => 4.5,
                'reviews_count' => 100,
                'images' => [],
                'opening_hours' => '09:00 - 17:00'
            ]
        ];
    }
    
    /**
     * Get available data sources
     * 
     * @return array
     */
    public function getAvailableSources() {
        return [
            'sisparnas' => [
                'name' => 'SISPARNAS',
                'description' => 'Sistem Informasi Kepariwisataan Nasional - Official Indonesia tourism data',
                'url' => 'https://sisparnas.kemenpar.go.id',
                'requires_api_key' => false
            ],
            'kemenpar' => [
                'name' => 'API Kemenpar',
                'description' => 'Ministry of Tourism Official API',
                'url' => 'https://api-industri.kemenpar.go.id',
                'requires_api_key' => true
            ],
            'google' => [
                'name' => 'Google Places API',
                'description' => 'Google Places comprehensive location data',
                'url' => 'https://developers.google.com/maps/documentation/places',
                'requires_api_key' => true
            ],
            'osm' => [
                'name' => 'OpenStreetMap',
                'description' => 'Open-source map data',
                'url' => 'https://www.openstreetmap.org',
                'requires_api_key' => false
            ],
            'poindt' => [
                'name' => 'POINDT API',
                'description' => 'Indonesian address and location API',
                'url' => 'https://api.poindt.com',
                'requires_api_key' => true
            ],
            'opentripmap' => [
                'name' => 'OpenTripMap',
                'description' => 'Open tourism database API',
                'url' => 'https://opentripmap.io',
                'requires_api_key' => true
            ]
        ];
    }
    
    /**
     * Get import statistics
     * 
     * @return array
     */
    public function getImportStats() {
        $sql = "SELECT 
                source,
                COUNT(*) as total_imported,
                COUNT(DISTINCT province) as provinces_covered,
                COUNT(DISTINCT category) as categories_imported
                FROM destinations 
                WHERE source IS NOT NULL
                GROUP BY source";
        
        return $this->db->query($sql)->fetchAll();
    }
}
