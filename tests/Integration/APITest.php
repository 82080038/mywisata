<?php
namespace Tests\Integration;

use PHPUnit\Framework\TestCase;

class APITest extends TestCase
{
    private $baseUrl = 'http://localhost/mywisata/api';
    
    public function testDestinationsEndpoint()
    {
        $response = @file_get_contents($this->baseUrl . '/destinations');
        
        $this->assertNotFalse($response, 'API endpoint should be accessible');
        
        $data = json_decode($response, true);
        
        $this->assertIsArray($data);
        $this->assertArrayHasKey('destinations', $data);
    }
    
    public function testTourGuidesEndpoint()
    {
        $response = @file_get_contents($this->baseUrl . '/tourguides');
        
        $this->assertNotFalse($response, 'API endpoint should be accessible');
        
        $data = json_decode($response, true);
        
        $this->assertIsArray($data);
        $this->assertArrayHasKey('tourguides', $data);
    }
    
    public function testBookingEndpoint()
    {
        $postData = [
            'tour_guide_id' => 1,
            'date' => '2026-07-20',
            'duration' => 4
        ];
        
        $context = stream_context_create([
            'http' => [
                'method' => 'POST',
                'header' => 'Content-Type: application/json',
                'content' => json_encode($postData)
            ]
        ]);
        
        $response = @file_get_contents($this->baseUrl . '/bookings', false, $context);
        
        if ($response !== false) {
            $data = json_decode($response, true);
            $this->assertArrayHasKey('status', $data);
        }
    }
    
    public function testAPIResponseFormat()
    {
        $response = @file_get_contents($this->baseUrl . '/destinations');
        
        if ($response !== false) {
            $data = json_decode($response, true);
            
            // Check for standard response format
            $this->assertTrue(
                isset($data['status']) || isset($data['destinations']),
                'API response should have status or data key'
            );
        }
    }
}
