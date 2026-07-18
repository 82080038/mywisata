<?php
/**
 * MyWisata Application - Location Discovery Controller
 * 
 * Handles location-based discovery and nearby attractions.
 * 
 * @package MyWisata
 * @version 1.0.0
 * @since 2026-07-18
 */

// Require CurrencyController if it exists
if (file_exists(APP_ROOT . '/app/controllers/CurrencyController.php')) {
    require_once APP_ROOT . '/app/controllers/CurrencyController.php';
}

class LocationDiscoveryController extends Controller {
    
    private $currencyController;
    
    /**
     * Constructor
     */
    public function __construct() {
        parent::__construct();
        // Initialize currency controller only if needed
        try {
            if (class_exists('CurrencyController')) {
                $this->currencyController = new CurrencyController();
            } else {
                $this->currencyController = null;
            }
        } catch (Exception $e) {
            // Fall back to default currency if currency controller fails
            $this->currencyController = null;
        }
    }
    
    /**
     * Index - Show location discovery page
     */
    public function index() {
        $data = [
            'title' => 'Location Discovery - MyWisata',
            'csrf_token' => Middleware::csrfToken()
        ];
        
        $this->view('location_discovery/index', $data);
    }
    
    /**
     * Nearby - Show nearby attractions page
     */
    public function nearby() {
        $data = [
            'title' => 'Nearby Attractions - MyWisata',
            'csrf_token' => Middleware::csrfToken()
        ];
        
        $this->view('location_discovery/nearby', $data);
    }
    
    /**
     * Get nearby attractions (AJAX)
     */
    public function nearbyAttractions() {
        if (!$this->isAjax()) {
            $this->redirect('home');
        }
        
        $lat = $this->get('lat');
        $lng = $this->get('lng');
        $radius = $this->get('radius', 5); // km
        $limit = $this->get('limit', 20);
        $currency = $this->currencyController->getUserCurrency(Session::get('user_id'));
        
        if (!$lat || !$lng) {
            $this->json(['status' => 'error', 'message' => 'Latitude dan longitude diperlukan'], 400);
        }
        
        $destinationModel = $this->model('Destination');
        $destinations = $destinationModel->getNearby($lat, $lng, $radius, $limit);
        
        // Convert prices
        foreach ($destinations as &$destination) {
            $destination['display_price'] = $this->currencyController->format(
                $this->currencyController->convert($destination['entry_fee'], 'IDR', $currency),
                $currency
            );
            $destination['distance_km'] = $this->calculateDistance($lat, $lng, $destination['latitude'], $destination['longitude']);
        }
        
        // Sort by distance
        usort($destinations, function($a, $b) {
            return $a['distance_km'] <=> $b['distance_km'];
        });
        
        $this->json([
            'status' => 'success',
            'data' => $destinations
        ]);
    }
    
    /**
     * Get location-based recommendations
     */
    public function recommendations() {
        if (!$this->isAjax()) {
            $this->redirect('home');
        }
        
        $lat = $this->get('lat');
        $lng = $this->get('lng');
        $radius = $this->get('radius', 10); // km
        $limit = $this->get('limit', 10);
        $userId = Session::get('user_id');
        $sessionId = Session::get('session_id');
        
        if (!$lat || !$lng) {
            $this->json(['status' => 'error', 'message' => 'Latitude dan longitude diperlukan'], 400);
        }
        
        $destinationModel = $this->model('Destination');
        $destinations = $destinationModel->getNearby($lat, $lng, $radius, $limit * 2); // Get more for scoring
        
        // Score and rank destinations
        $recommendations = [];
        foreach ($destinations as $destination) {
            $distance = $this->calculateDistance($lat, $lng, $destination['latitude'], $destination['longitude']);
            
            if ($distance > $radius) {
                continue;
            }
            
            $score = $this->calculateRecommendationScore($destination, $distance, $userId);
            
            $recommendations[] = [
                'destination_id' => $destination['id'],
                'destination_name' => $destination['name'],
                'distance_km' => $distance,
                'recommendation_score' => $score,
                'recommendation_reason' => $this->getRecommendationReasons($destination, $score),
                'entry_fee' => $destination['entry_fee'],
                'rating_avg' => $destination['rating_avg'],
                'eco_rating' => $destination['eco_rating']
            ];
        }
        
        // Sort by recommendation score
        usort($recommendations, function($a, $b) {
            return $b['recommendation_score'] <=> $a['recommendation_score'];
        });
        
        // Limit results
        $recommendations = array_slice($recommendations, 0, $limit);
        
        // Log recommendation
        $locationRecommendationModel = $this->model('LocationRecommendation');
        foreach ($recommendations as $recommendation) {
            $locationRecommendationModel->create([
                'user_id' => $userId,
                'session_id' => $sessionId,
                'user_lat' => $lat,
                'user_lng' => $lng,
                'search_radius_km' => $radius,
                'recommended_destination_id' => $recommendation['destination_id'],
                'recommendation_score' => $recommendation['recommendation_score'],
                'distance_km' => $recommendation['distance_km']
            ]);
        }
        
        $this->json([
            'status' => 'success',
            'data' => $recommendations
        ]);
    }
    
    /**
     * Calculate recommendation score
     */
    private function calculateRecommendationScore($destination, $distance, $userId) {
        $score = 0;
        
        // Distance score (closer is better)
        $distanceScore = max(0, 1 - ($distance / 10)); // 0 to 1
        $score += $distanceScore * 0.3;
        
        // Rating score
        $ratingScore = $destination['rating_avg'] / 5; // 0 to 1
        $score += $ratingScore * 0.3;
        
        // Eco score
        if ($destination['eco_rating']) {
            $ecoScore = $destination['eco_rating'] / 100; // 0 to 1
            $score += $ecoScore * 0.2;
        }
        
        // Popularity score
        $popularityScore = min(1, $destination['total_visitors'] / 1000); // 0 to 1
        $score += $popularityScore * 0.2;
        
        return round($score, 2);
    }
    
    /**
     * Get recommendation reasons
     */
    private function getRecommendationReasons($destination, $score) {
        $reasons = [];
        
        if ($destination['rating_avg'] >= 4.5) {
            $reasons[] = 'high_rating';
        }
        
        if ($destination['eco_rating'] >= 80) {
            $reasons[] = 'eco_friendly';
        }
        
        if ($destination['is_featured']) {
            $reasons[] = 'popular';
        }
        
        if ($destination['total_visitors'] > 500) {
            $reasons[] = 'popular';
        }
        
        return $reasons;
    }
    
    /**
     * Calculate distance between two points (Haversine formula)
     */
    private function calculateDistance($lat1, $lng1, $lat2, $lng2) {
        $earthRadius = 6371; // km
        
        $dLat = deg2rad($lat2 - $lat1);
        $dLng = deg2rad($lng2 - $lng1);
        
        $a = sin($dLat / 2) * sin($dLat / 2) +
             cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
             sin($dLng / 2) * sin($dLng / 2);
        
        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));
        
        return $earthRadius * $c;
    }
    
    /**
     * Get popular routes
     */
    public function popularRoutes() {
        if (!$this->isAjax()) {
            $this->redirect('home');
        }
        
        $page = $this->get('page', 1);
        $limit = $this->get('limit', 10);
        $routeType = $this->get('route_type', 'all');
        
        $popularRouteModel = $this->model('PopularRoute');
        $routes = $popularRouteModel->getActive($page, $limit, $routeType);
        
        $this->json([
            'status' => 'success',
            'data' => $routes
        ]);
    }
    
    /**
     * Get route details
     */
    public function routeDetails() {
        $routeId = $this->get('id');
        
        $popularRouteModel = $this->model('PopularRoute');
        $route = $popularRouteModel->findById($routeId);
        
        if (!$route) {
            $this->json(['status' => 'error', 'message' => 'Rute tidak ditemukan'], 404);
        }
        
        // Get waypoints
        $waypoints = json_decode($route['waypoints'], true);
        
        // Get destination details for each waypoint
        $destinationModel = $this->model('Destination');
        $waypointDetails = [];
        foreach ($waypoints as $waypoint) {
            $destination = $destinationModel->findById($waypoint['item_id']);
            if ($destination) {
                $waypointDetails[] = [
                    'destination' => $destination,
                    'order' => $waypoint['order']
                ];
            }
        }
        
        $this->json([
            'status' => 'success',
            'data' => [
                'route' => $route,
                'waypoints' => $waypointDetails
            ]
        ]);
    }
    
    /**
     * Get geofence zones
     */
    public function geofenceZones() {
        if (!$this->isAjax()) {
            $this->redirect('home');
        }
        
        $lat = $this->get('lat');
        $lng = $this->get('lng');
        
        $geofenceZoneModel = $this->model('GeofenceZone');
        $zones = $geofenceZoneModel->getActive();
        
        // Filter zones that contain the point
        $activeZones = [];
        foreach ($zones as $zone) {
            if ($this->isPointInGeofence($lat, $lng, $zone)) {
                $activeZones[] = $zone;
            }
        }
        
        $this->json([
            'status' => 'success',
            'data' => $activeZones
        ]);
    }
    
    /**
     * Check if point is in geofence
     */
    private function isPointInGeofence($lat, $lng, $zone) {
        $distance = $this->calculateDistance($lat, $lng, $zone['center_lat'], $zone['center_lng']);
        return $distance <= $zone['radius_km'];
    }
    
    /**
     * Search location
     */
    public function search() {
        if (!$this->isAjax()) {
            $this->redirect('home');
        }
        
        $query = $this->get('query');
        $lat = $this->get('lat');
        $lng = $this->get('lng');
        $radius = $this->get('radius', 50); // km
        $limit = $this->get('limit', 20);
        
        if (empty($query)) {
            $this->json(['status' => 'error', 'message' => 'Query diperlukan'], 400);
        }
        
        $destinationModel = $this->model('Destination');
        $destinations = $destinationModel->searchByName($query, $limit);
        
        // Filter by location if provided
        if ($lat && $lng) {
            $destinations = array_filter($destinations, function($dest) use ($lat, $lng, $radius) {
                $distance = $this->calculateDistance($lat, $lng, $dest['latitude'], $dest['longitude']);
                return $distance <= $radius;
            });
        }
        
        // Log search
        $locationSearchHistoryModel = $this->model('LocationSearchHistory');
        $locationSearchHistoryModel->create([
            'user_id' => Session::get('user_id'),
            'session_id' => Session::get('session_id'),
            'search_lat' => $lat,
            'search_lng' => $lng,
            'search_radius_km' => $radius,
            'search_query' => $query,
            'results_count' => count($destinations)
        ]);
        
        $this->json([
            'status' => 'success',
            'data' => array_values($destinations)
        ]);
    }
}
