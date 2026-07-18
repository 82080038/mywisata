<?php
namespace App\Services;

use App\Models\CarbonEmission;
use App\Models\EcoScore;
use App\Models\EcoAction;
use App\Models\LowCarbonRoute;

/**
 * Sustainability Service
 * 
 * Service for carbon tracking, eco-scoring, and sustainability features
 * 
 * @package App\Services
 */
class SustainabilityService {
    private $carbonEmission;
    private $ecoScore;
    private $ecoAction;
    private $lowCarbonRoute;
    
    public function __construct() {
        $this->carbonEmission = new CarbonEmission();
        $this->ecoScore = new EcoScore();
        $this->ecoAction = new EcoAction();
        $this->lowCarbonRoute = new LowCarbonRoute();
    }
    
    /**
     * Calculate CO2 emissions for transport
     * 
     * @param string $transportMode car, bus, train, flight, etc.
     * @param float $distanceKm Distance in kilometers
     * @return float CO2 in kg
     */
    public function calculateTransportCO2($transportMode, $distanceKm) {
        $emissionFactors = [
            'car' => 0.21,      // kg CO2 per km
            'bus' => 0.089,     // kg CO2 per km
            'train' => 0.041,   // kg CO2 per km
            'flight' => 0.255,  // kg CO2 per km
            'motorcycle' => 0.113,
            'walking' => 0,
            'cycling' => 0
        ];
        
        $factor = $emissionFactors[$transportMode] ?? 0.21;
        return round($factor * $distanceKm, 2);
    }
    
    /**
     * Record carbon emission
     * 
     * @param array $data Emission data
     * @return array Result
     */
    public function recordEmission($data) {
        $required = ['user_id', 'emission_type', 'co2_kg'];
        foreach ($required as $field) {
            if (!isset($data[$field])) {
                return ['success' => false, 'error' => "Missing required field: {$field}"];
            }
        }
        
        $id = $this->carbonEmission->create($data);
        
        if ($id) {
            // Update eco score
            $this->updateEcoScore($data['user_id']);
            return ['success' => true, 'id' => $id];
        }
        
        return ['success' => false, 'error' => 'Failed to record emission'];
    }
    
    /**
     * Update eco score for user
     * 
     * @param int $userId User ID
     */
    private function updateEcoScore($userId) {
        $totalCO2 = $this->carbonEmission->getTotalCO2ByUser($userId);
        $totalCO2Saved = $this->ecoAction->getTotalCO2Saved($userId);
        $ecoActionsCount = $this->ecoAction->getTotalPoints($userId);
        
        // Calculate score (0-100)
        // Base score: 50
        // Bonus for CO2 saved: up to 30 points
        // Bonus for eco actions: up to 20 points
        $baseScore = 50;
        $co2Bonus = min(30, $totalCO2Saved * 2); // 2 points per kg saved
        $actionsBonus = min(20, $ecoActionsCount / 10); // 1 point per 10 actions
        
        $score = min(100, $baseScore + $co2Bonus + $actionsBonus);
        $level = $this->ecoScore->calculateLevel($score);
        
        $this->ecoScore->updateScore($userId, [
            'score' => $score,
            'level' => $level,
            'total_co2_saved' => $totalCO2Saved,
            'eco_actions_count' => $ecoActionsCount
        ]);
    }
    
    /**
     * Record eco action
     * 
     * @param array $data Action data
     * @return array Result
     */
    public function recordEcoAction($data) {
        $required = ['user_id', 'action_type', 'description'];
        foreach ($required as $field) {
            if (!isset($data[$field])) {
                return ['success' => false, 'error' => "Missing required field: {$field}"];
            }
        }
        
        // Calculate CO2 saved and points based on action type
        $actionBenefits = [
            'public_transport' => ['co2' => 0.5, 'points' => 10],
            'eco_accommodation' => ['co2' => 1.0, 'points' => 15],
            'local_food' => ['co2' => 0.3, 'points' => 5],
            'carbon_offset' => ['co2' => 5.0, 'points' => 50],
            'waste_reduction' => ['co2' => 0.2, 'points' => 5]
        ];
        
        $benefits = $actionBenefits[$data['action_type']] ?? ['co2' => 0, 'points' => 0];
        $data['co2_saved_kg'] = $benefits['co2'];
        $data['points_earned'] = $benefits['points'];
        
        $id = $this->ecoAction->create($data);
        
        if ($id) {
            // Update eco score
            $this->updateEcoScore($data['user_id']);
            return ['success' => true, 'id' => $id, 'points_earned' => $benefits['points']];
        }
        
        return ['success' => false, 'error' => 'Failed to record action'];
    }
    
    /**
     * Get low-carbon route recommendations
     * 
     * @param int $originId Origin destination ID
     * @param int $destinationId Destination ID
     * @return array Routes
     */
    public function getLowCarbonRoutes($originId, $destinationId) {
        $routes = $this->lowCarbonRoute->getRoutes($originId, $destinationId);
        
        // Sort by CO2 emissions
        usort($routes, function($a, $b) {
            return $a['co2_kg'] <=> $b['co2_kg'];
        });
        
        return $routes;
    }
    
    /**
     * Get user sustainability statistics
     * 
     * @param int $userId User ID
     * @return array Statistics
     */
    public function getUserStatistics($userId) {
        $ecoScore = $this->ecoScore->getByUserId($userId);
        $totalCO2 = $this->carbonEmission->getTotalCO2ByUser($userId);
        $totalCO2Saved = $this->ecoAction->getTotalCO2Saved($userId);
        $totalPoints = $this->ecoAction->getTotalPoints($userId);
        $emissionsByType = $this->carbonEmission->getByType('transport', $userId);
        $ecoActions = $this->ecoAction->getByUserId($userId, 10);
        
        return [
            'eco_score' => $ecoScore,
            'total_co2_emitted' => $totalCO2,
            'total_co2_saved' => $totalCO2Saved,
            'net_co2' => $totalCO2 - $totalCO2Saved,
            'total_points' => $totalPoints,
            'emissions_by_type' => $emissionsByType,
            'recent_actions' => $ecoActions
        ];
    }
    
    /**
     * Get eco leaderboard
     * 
     * @param int $limit Number of users
     * @return array Leaderboard
     */
    public function getLeaderboard($limit = 10) {
        return $this->ecoScore->getLeaderboard($limit);
    }
}
