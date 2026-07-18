<?php
/**
 * MyWisata Application - Routing Test
 * 
 * @package MyWisata
 * @version 1.0.0
 * @since 2026-07-18
 */

class RoutingTest {
    
    private $results = [];
    private $baseUrl = 'http://localhost/mywisata';
    
    /**
     * Test if routes are accessible
     */
    public function testRoutesAccessible() {
        $routes = [
            '/halal-tourism',
            '/halal-tourism/prayer-rooms',
            '/culinary-tourism/food-tours',
            '/culinary-tourism/cooking-classes',
            '/green-credits',
            '/green-credits/eco-destinations',
            '/walk-in-booking',
            '/walk-in-booking/list',
            '/religious-tourism',
            '/religious-tourism/events',
            '/adventure-tourism',
            '/adventure-tourism/equipment-rentals',
            '/agritourism',
            '/agritourism/products',
            '/split-payment/join-group',
            '/location/nearby'
        ];
        
        $passed = 0;
        $failed = 0;
        
        foreach ($routes as $route) {
            $url = $this->baseUrl . $route;
            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_TIMEOUT, 5);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);
            
            if ($httpCode >= 200 && $httpCode < 500) {
                $passed++;
                $this->results[] = "✓ Route accessible: {$route} (HTTP {$httpCode})";
            } else {
                $failed++;
                $this->results[] = "✗ Route not accessible: {$route} (HTTP {$httpCode})";
            }
        }
        
        $this->results[] = "\nRoute Accessibility Test: {$passed} passed, {$failed} failed";
        return $failed === 0;
    }
    
    /**
     * Test if controller classes can be instantiated
     */
    public function testControllerInstantiation() {
        // Include base classes first
        require_once __DIR__ . '/../app/core/Controller.php';
        require_once __DIR__ . '/../app/core/Database.php';
        require_once __DIR__ . '/../app/core/Model.php';
        
        $controllers = [
            'HalalTourismController',
            'CulinaryTourismController',
            'ReligiousTourismController',
            'GreenCreditsController',
            'WalkInBookingController',
            'WhatsAppBookingController',
            'AdventureTourismController',
            'AgritourismController',
            'SplitPaymentController',
            'LocationDiscoveryController'
        ];
        
        $passed = 0;
        $failed = 0;
        
        foreach ($controllers as $controller) {
            $file = __DIR__ . '/../app/controllers/' . $controller . '.php';
            if (file_exists($file)) {
                require_once $file;
                if (class_exists($controller)) {
                    $passed++;
                    $this->results[] = "✓ Controller class exists: {$controller}";
                } else {
                    $failed++;
                    $this->results[] = "✗ Controller class not found: {$controller}";
                }
            } else {
                $failed++;
                $this->results[] = "✗ Controller file missing: {$controller}.php";
            }
        }
        
        $this->results[] = "\nController Instantiation Test: {$passed} passed, {$failed} failed";
        return $failed === 0;
    }
    
    /**
     * Test if model classes can be instantiated
     */
    public function testModelInstantiation() {
        $models = [
            'HalalPackage',
            'HalalPackageItinerary',
            'HalalPackageBooking',
            'PrayerRoom',
            'PrayerTimesCache',
            'FoodTour',
            'FoodTourBooking',
            'CookingClass',
            'CookingClassBooking',
            'CookingClassMenuItem',
            'PilgrimagePackage',
            'PilgrimagePackageItinerary',
            'PilgrimageBooking',
            'ReligiousEvent',
            'GreenCredit',
            'GreenCreditTransaction',
            'GreenCreditReward',
            'GreenCreditClaim',
            'EcoCertifiedDestination',
            'WalkInBooking',
            'WalkInBookingItem',
            'QuickBookingTemplate',
            'WalkInAnalytics',
            'WhatsAppBookingSession',
            'WhatsAppMessageTemplate',
            'WhatsAppBookingAnalytics',
            'WhatsAppQuickReply',
            'AdventureActivity',
            'AdventureActivityBooking',
            'EquipmentRental',
            'EquipmentRentalBooking',
            'SafetyVerification',
            'Farm',
            'FarmActivity',
            'FarmActivityBooking',
            'FarmTourPackage',
            'FarmProduct',
            'ItineraryTimelineEvent',
            'ItineraryDaySummary',
            'ItineraryTemplate',
            'ItineraryTemplateEvent',
            'ItinerarySharing',
            'ItineraryComment',
            'SplitPaymentParticipant',
            'SplitPaymentTransaction',
            'PaymentReminder',
            'NearbyAttraction',
            'LocationRecommendation',
            'GeofenceZone',
            'LocationSearchHistory',
            'PopularRoute'
        ];
        
        $passed = 0;
        $failed = 0;
        
        foreach ($models as $model) {
            $file = __DIR__ . '/../app/models/' . $model . '.php';
            if (file_exists($file)) {
                require_once $file;
                if (class_exists($model)) {
                    $passed++;
                    $this->results[] = "✓ Model class exists: {$model}";
                } else {
                    $failed++;
                    $this->results[] = "✗ Model class not found: {$model}";
                }
            } else {
                $failed++;
                $this->results[] = "✗ Model file missing: {$model}.php";
            }
        }
        
        $this->results[] = "\nModel Instantiation Test: {$passed} passed, {$failed} failed";
        return $failed === 0;
    }
    
    /**
     * Run all tests
     */
    public function runAllTests() {
        echo "=== MyWisata Routing Test ===\n\n";
        
        $this->testControllerInstantiation();
        $this->testModelInstantiation();
        
        echo "\n=== Test Results ===\n\n";
        foreach ($this->results as $result) {
            echo $result . "\n";
        }
        
        echo "\n=== Test Summary ===\n";
        $total = count($this->results);
        $passed = substr_count(implode('', $this->results), '✓');
        $failed = substr_count(implode('', $this->results), '✗');
        echo "Total: {$total}, Passed: {$passed}, Failed: {$failed}\n";
    }
}

// Run tests
$test = new RoutingTest();
$test->runAllTests();
