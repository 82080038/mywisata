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
            return $this->redirect('auth/login');
        }
        
        $stats = $this->sustainabilityService->getUserStatistics($userId);
        $data = [
            'eco_score' => $stats['eco_score'],
            'total_co2_saved' => $stats['total_co2_saved'],
            'total_points' => $stats['total_points'],
            'emissions_by_type' => $stats['emissions_by_type'],
            'recent_actions' => $stats['recent_actions']
        ];
        $this->view('sustainability/index', $data);
    }
    
    /**
     * Record carbon emission
     */
    public function recordEmission() {
        $userId = $_SESSION['user_id'] ?? null;
        
        if (!$userId) {
            return $this->redirect('auth/login');
        }
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = [
                'user_id' => $userId,
                'emission_type' => $_POST['emission_type'] ?? '',
                'co2_kg' => $_POST['co2_kg'] ?? 0,
                'transport_mode' => $_POST['transport_mode'] ?? null,
                'distance_km' => $_POST['distance_km'] ?? null,
                'booking_id' => $_POST['booking_id'] ?? null
            ];
            
            $result = $this->sustainabilityService->recordEmission($data);
            
            if ($result['success']) {
                Session::flash('success', 'Carbon emission recorded successfully');
                return $this->redirect('sustainability');
            } else {
                Session::flash('error', $result['error']);
            }
        }
        
        $this->view('sustainability/record_emission');
    }
    
    /**
     * Record eco action
     */
    public function recordAction() {
        $userId = $_SESSION['user_id'] ?? null;
        
        if (!$userId) {
            return $this->redirect('auth/login');
        }
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = [
                'user_id' => $userId,
                'action_type' => $_POST['action_type'] ?? '',
                'description' => $_POST['description'] ?? ''
            ];
            
            $result = $this->sustainabilityService->recordEcoAction($data);
            
            if ($result['success']) {
                Session::flash('success', 'Eco action recorded! You earned ' . $result['points_earned'] . ' points');
                return $this->redirect('sustainability');
            } else {
                Session::flash('error', $result['error']);
            }
        }
        
        $this->view('sustainability/record_action');
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
