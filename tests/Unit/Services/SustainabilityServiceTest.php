<?php
namespace Tests\Unit\Services;

use PHPUnit\Framework\TestCase;
use App\Services\SustainabilityService;

class SustainabilityServiceTest extends TestCase {
    private $sustainabilityService;
    
    protected function setUp(): void {
        $this->sustainabilityService = new SustainabilityService();
    }
    
    public function testGetUserStatistics() {
        $userId = 1;
        $stats = $this->sustainabilityService->getUserStatistics($userId);
        
        $this->assertIsArray($stats);
        $this->assertArrayHasKey('eco_score', $stats);
        $this->assertArrayHasKey('total_co2_saved', $stats);
        $this->assertArrayHasKey('total_points', $stats);
    }
    
    public function testRecordEmission() {
        $data = [
            'user_id' => 1,
            'emission_type' => 'transport',
            'co2_kg' => 10.5,
            'transport_mode' => 'car',
            'distance_km' => 50
        ];
        
        $result = $this->sustainabilityService->recordEmission($data);
        
        $this->assertIsArray($result);
        $this->assertArrayHasKey('success', $result);
    }
    
    public function testRecordEcoAction() {
        $data = [
            'user_id' => 1,
            'action_type' => 'public_transport',
            'description' => 'Used bus instead of car'
        ];
        
        $result = $this->sustainabilityService->recordEcoAction($data);
        
        $this->assertIsArray($result);
        $this->assertArrayHasKey('success', $result);
        $this->assertArrayHasKey('points_earned', $result);
    }
    
    public function testGetLowCarbonRoutes() {
        $originId = 1;
        $destinationId = 2;
        $routes = $this->sustainabilityService->getLowCarbonRoutes($originId, $destinationId);
        
        $this->assertIsArray($routes);
    }
    
    public function testGetLeaderboard() {
        $limit = 10;
        $leaderboard = $this->sustainabilityService->getLeaderboard($limit);
        
        $this->assertIsArray($leaderboard);
        $this->assertLessThanOrEqual($limit, count($leaderboard));
    }
}
