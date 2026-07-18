<?php
namespace Tests\Performance;

use PHPUnit\Framework\TestCase;

class PerformanceTest extends TestCase
{
    private $baseUrl = 'http://localhost/mywisata';
    
    public function testPageLoadTime()
    {
        $pages = [
            '/destinations',
            '/tourguides',
            '/auth/login'
        ];
        
        foreach ($pages as $page) {
            $startTime = microtime(true);
            
            $url = $this->baseUrl . $page;
            $response = @file_get_contents($url);
            
            $loadTime = (microtime(true) - $startTime) * 1000;
            
            $this->assertLessThan(500, $loadTime, "Page {$page} load time should be less than 500ms");
        }
    }
    
    public function testAPIResponseTime()
    {
        $apiEndpoints = [
            '/api/destinations',
            '/api/tourguides'
        ];
        
        foreach ($apiEndpoints as $endpoint) {
            $startTime = microtime(true);
            
            $url = $this->baseUrl . $endpoint;
            $response = @file_get_contents($url);
            
            $responseTime = (microtime(true) - $startTime) * 1000;
            
            $this->assertLessThan(200, $responseTime, "API {$endpoint} response time should be less than 200ms");
        }
    }
    
    public function testDatabaseQueryPerformance()
    {
        require_once APP_ROOT . '/app/core/Database.php';
        
        $db = new Database();
        
        $startTime = microtime(true);
        
        $results = $db->query("SELECT * FROM destinations LIMIT 100");
        
        $queryTime = (microtime(true) - $startTime) * 1000;
        
        $this->assertLessThan(100, $queryTime, 'Query time should be less than 100ms');
    }
    
    public function testMemoryUsage()
    {
        $initialMemory = memory_get_usage();
        
        // Simulate a typical operation
        require_once APP_ROOT . '/app/core/Database.php';
        $db = new Database();
        $results = $db->query("SELECT * FROM destinations LIMIT 100");
        
        $finalMemory = memory_get_usage();
        $memoryUsed = ($finalMemory - $initialMemory) / 1024 / 1024; // Convert to MB
        
        $this->assertLessThan(50, $memoryUsed, 'Memory usage should be less than 50MB');
    }
}
