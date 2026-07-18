<?php
namespace Tests\Unit\Models;

use PHPUnit\Framework\TestCase;
use App\Models\CarbonEmission;

class CarbonEmissionTest extends TestCase {
    private $carbonEmission;
    
    protected function setUp(): void {
        $this->carbonEmission = new CarbonEmission();
    }
    
    public function testGetEmissionsByUser() {
        $userId = 1;
        $emissions = $this->carbonEmission->getByUser($userId);
        
        $this->assertIsArray($emissions);
        $this->assertNotEmpty($emissions);
    }
    
    public function testCalculateTotalCO2() {
        $userId = 1;
        $total = $this->carbonEmission->calculateTotalCO2($userId);
        
        $this->assertIsNumeric($total);
        $this->assertGreaterThanOrEqual(0, $total);
    }
    
    public function testCreateEmission() {
        $data = [
            'user_id' => 1,
            'emission_type' => 'transport',
            'co2_kg' => 10.5,
            'transport_mode' => 'car',
            'distance_km' => 50
        ];
        
        $result = $this->carbonEmission->create($data);
        
        $this->assertTrue($result);
    }
    
    public function testGetEmissionsByType() {
        $userId = 1;
        $type = 'transport';
        $emissions = $this->carbonEmission->getByType($userId, $type);
        
        $this->assertIsArray($emissions);
    }
}
