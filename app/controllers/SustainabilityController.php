<?php
namespace App\Controllers;

use App\Services\SustainabilityService;

class SustainabilityController extends Controller {
    private $sustainabilityService;
    
    public function __construct() {
        $this->sustainabilityService = new SustainabilityService();
    }
    
    /**
     * Get user sustainability statistics
     */
    public function index() {
        $userId = $_SESSION['user_id'] ?? null;
        
        if (!$userId) {
            return $this->json(['success' => false, 'error' => 'Not logged in']);
        }
        
        $stats = $this->sustainabilityService->getUserStatistics($userId);
        return $this->json(['success' => true, 'data' => $stats]);
    }
    
    /**
     * Record carbon emission
     */
    public function recordEmission() {
        $userId = $_SESSION['user_id'] ?? null;
        
        if (!$userId) {
            return $this->json(['success' => false, 'error' => 'Not logged in']);
        }
        
        $data = [
            'user_id' => $userId,
            'emission_type' => $_POST['emission_type'] ?? '',
            'co2_kg' => $_POST['co2_kg'] ?? 0,
            'transport_mode' => $_POST['transport_mode'] ?? null,
            'distance_km' => $_POST['distance_km'] ?? null,
            'booking_id' => $_POST['booking_id'] ?? null
        ];
        
        $result = $this->sustainabilityService->recordEmission($data);
        return $this->json($result);
    }
    
    /**
     * Record eco action
     */
    public function recordAction() {
        $userId = $_SESSION['user_id'] ?? null;
        
        if (!$userId) {
            return $this->json(['success' => false, 'error' => 'Not logged in']);
        }
        
        $data = [
            'user_id' => $userId,
            'action_type' => $_POST['action_type'] ?? '',
            'description' => $_POST['description'] ?? ''
        ];
        
        $result = $this->sustainabilityService->recordEcoAction($data);
        return $this->json($result);
    }
    
    /**
     * Get low-carbon routes
     */
    public function getRoutes() {
        $originId = $_GET['origin_id'] ?? 0;
        $destinationId = $_GET['destination_id'] ?? 0;
        
        $routes = $this->sustainabilityService->getLowCarbonRoutes($originId, $destinationId);
        return $this->json(['success' => true, 'data' => $routes]);
    }
    
    /**
     * Get eco leaderboard
     */
    public function leaderboard() {
        $limit = $_GET['limit'] ?? 10;
        $leaderboard = $this->sustainabilityService->getLeaderboard($limit);
        return $this->json(['success' => true, 'data' => $leaderboard]);
    }
}
