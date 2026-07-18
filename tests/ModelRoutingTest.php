<?php
/**
 * MyWisata Application - Model and Routing Test
 * 
 * @package MyWisata
 * @version 1.0.0
 * @since 2026-07-18
 */

// Bootstrap the application
require_once __DIR__ . '/../app/core/App.php';
require_once __DIR__ . '/../app/core/Database.php';
require_once __DIR__ . '/../app/core/Model.php';

class ModelRoutingTest {
    
    private $results = [];
    
    /**
     * Test if model files exist
     */
    public function testModelFilesExist() {
        $models = [
            // HalalTourism
            'HalalPackage',
            'HalalPackageItinerary',
            'HalalPackageBooking',
            'PrayerRoom',
            'PrayerTimesCache',
            // CulinaryTourism
            'FoodTour',
            'FoodTourBooking',
            'CookingClass',
            'CookingClassBooking',
            'CookingClassMenuItem',
            // ReligiousTourism
            'PilgrimagePackage',
            'PilgrimagePackageItinerary',
            'PilgrimageBooking',
            'ReligiousEvent',
            // GreenCredits
            'GreenCredit',
            'GreenCreditTransaction',
            'GreenCreditReward',
            'GreenCreditClaim',
            'EcoCertifiedDestination',
            // WalkInBooking
            'WalkInBooking',
            'WalkInBookingItem',
            'QuickBookingTemplate',
            'WalkInAnalytics',
            // WhatsAppBooking
            'WhatsAppBookingSession',
            'WhatsAppMessageTemplate',
            'WhatsAppBookingAnalytics',
            'WhatsAppQuickReply',
            // AdventureTourism
            'AdventureActivity',
            'AdventureActivityBooking',
            'EquipmentRental',
            'EquipmentRentalBooking',
            'SafetyVerification',
            // Agritourism
            'Farm',
            'FarmActivity',
            'FarmActivityBooking',
            'FarmTourPackage',
            'FarmProduct',
            // VisualItinerary
            'ItineraryTimelineEvent',
            'ItineraryDaySummary',
            'ItineraryTemplate',
            'ItineraryTemplateEvent',
            'ItinerarySharing',
            'ItineraryComment',
            // SplitPayment
            'SplitPaymentParticipant',
            'SplitPaymentTransaction',
            'PaymentReminder',
            // LocationDiscovery
            'NearbyAttraction',
            'LocationRecommendation',
            'GeofenceZone',
            'LocationSearchHistory',
            'PopularRoute'
        ];
        
        $modelPath = __DIR__ . '/../app/models/';
        $passed = 0;
        $failed = 0;
        
        foreach ($models as $model) {
            $file = $modelPath . $model . '.php';
            if (file_exists($file)) {
                $passed++;
                $this->results[] = "✓ Model file exists: {$model}.php";
            } else {
                $failed++;
                $this->results[] = "✗ Model file missing: {$model}.php";
            }
        }
        
        $this->results[] = "\nModel Files Test: {$passed} passed, {$failed} failed";
        return $failed === 0;
    }
    
    /**
     * Test if controller files exist
     */
    public function testControllerFilesExist() {
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
        
        $controllerPath = __DIR__ . '/../app/controllers/';
        $passed = 0;
        $failed = 0;
        
        foreach ($controllers as $controller) {
            $file = $controllerPath . $controller . '.php';
            if (file_exists($file)) {
                $passed++;
                $this->results[] = "✓ Controller file exists: {$controller}.php";
            } else {
                $failed++;
                $this->results[] = "✗ Controller file missing: {$controller}.php";
            }
        }
        
        $this->results[] = "\nController Files Test: {$passed} passed, {$failed} failed";
        return $failed === 0;
    }
    
    /**
     * Test if view files exist
     */
    public function testViewFilesExist() {
        $views = [
            'halal_tourism/index.php',
            'halal_tourism/show.php',
            'halal_tourism/prayer_rooms.php',
            'culinary_tourism/food_tours.php',
            'culinary_tourism/cooking_classes.php',
            'green_credits/index.php',
            'green_credits/eco_destinations.php',
            'walk_in_booking/index.php',
            'walk_in_booking/list.php',
            'religious_tourism/index.php',
            'religious_tourism/show.php',
            'religious_tourism/events.php',
            'adventure_tourism/index.php',
            'adventure_tourism/show.php',
            'adventure_tourism/equipment_rentals.php',
            'agritourism/index.php',
            'agritourism/show.php',
            'agritourism/products.php',
            'split_payment/join_group.php',
            'split_payment/group_status.php'
        ];
        
        $viewPath = __DIR__ . '/../app/views/';
        $passed = 0;
        $failed = 0;
        
        foreach ($views as $view) {
            $file = $viewPath . $view;
            if (file_exists($file)) {
                $passed++;
                $this->results[] = "✓ View file exists: {$view}";
            } else {
                $failed++;
                $this->results[] = "✗ View file missing: {$view}";
            }
        }
        
        $this->results[] = "\nView Files Test: {$passed} passed, {$failed} failed";
        return $failed === 0;
    }
    
    /**
     * Test if config files exist
     */
    public function testConfigFilesExist() {
        $configs = [
            'currency.php',
            'whatsapp.php'
        ];
        
        $configPath = __DIR__ . '/../app/config/external/';
        $passed = 0;
        $failed = 0;
        
        foreach ($configs as $config) {
            $file = $configPath . $config;
            if (file_exists($file)) {
                $passed++;
                $this->results[] = "✓ Config file exists: {$config}";
            } else {
                $failed++;
                $this->results[] = "✗ Config file missing: {$config}";
            }
        }
        
        $this->results[] = "\nConfig Files Test: {$passed} passed, {$failed} failed";
        return $failed === 0;
    }
    
    /**
     * Test routing configuration
     */
    public function testRoutingConfiguration() {
        $appFile = __DIR__ . '/../app/core/App.php';
        $content = file_get_contents($appFile);
        
        $routes = [
            'halal-tourism',
            'culinary-tourism',
            'religious-tourism',
            'green-credits',
            'walk-in-booking',
            'whatsapp-booking',
            'adventure-tourism',
            'agritourism',
            'split-payment',
            'location'
        ];
        
        $passed = 0;
        $failed = 0;
        
        foreach ($routes as $route) {
            if (strpos($content, "'{$route}'") !== false) {
                $passed++;
                $this->results[] = "✓ Route configured: {$route}";
            } else {
                $failed++;
                $this->results[] = "✗ Route missing: {$route}";
            }
        }
        
        $this->results[] = "\nRouting Configuration Test: {$passed} passed, {$failed} failed";
        return $failed === 0;
    }
    
    /**
     * Run all tests
     */
    public function runAllTests() {
        echo "=== MyWisata Model and Routing Test ===\n\n";
        
        $this->testModelFilesExist();
        $this->testControllerFilesExist();
        $this->testViewFilesExist();
        $this->testConfigFilesExist();
        $this->testRoutingConfiguration();
        
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
$test = new ModelRoutingTest();
$test->runAllTests();
