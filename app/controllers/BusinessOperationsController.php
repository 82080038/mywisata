<?php
namespace App\Controllers;

use App\Services\BusinessOperationsService;

class BusinessOperationsController extends Controller {
    private $businessOpsService;
    
    public function __construct() {
        $this->businessOpsService = new BusinessOperationsService();
    }
    
    /**
     * Match guide for booking
     */
    public function matchGuide() {
        $bookingId = $_POST['booking_id'] ?? 0;
        $requirements = json_decode($_POST['requirements'] ?? '{}', true);
        
        $result = $this->businessOpsService->matchGuide($bookingId, $requirements);
        return $this->json($result);
    }
    
    /**
     * Create schedule entry
     */
    public function createSchedule() {
        $data = [
            'guide_id' => $_POST['guide_id'] ?? 0,
            'booking_id' => $_POST['booking_id'] ?? null,
            'title' => $_POST['title'] ?? '',
            'description' => $_POST['description'] ?? null,
            'start_datetime' => $_POST['start_datetime'] ?? '',
            'end_datetime' => $_POST['end_datetime'] ?? '',
            'location' => $_POST['location'] ?? null,
            'status' => 'scheduled'
        ];
        
        $result = $this->businessOpsService->createScheduleEntry($data);
        return $this->json($result);
    }
    
    /**
     * Clock in
     */
    public function clockIn() {
        $data = [
            'guide_id' => $_POST['guide_id'] ?? 0,
            'booking_id' => $_POST['booking_id'] ?? 0,
            'clock_in_time' => date('Y-m-d H:i:s'),
            'clock_in_latitude' => $_POST['latitude'] ?? null,
            'clock_in_longitude' => $_POST['longitude'] ?? null,
            'location_name' => $_POST['location_name'] ?? null,
            'status' => 'clocked_in'
        ];
        
        $result = $this->businessOpsService->clockIn($data);
        return $this->json($result);
    }
    
    /**
     * Clock out
     */
    public function clockOut() {
        $recordId = $_POST['record_id'] ?? 0;
        $data = [
            'clock_out_latitude' => $_POST['latitude'] ?? null,
            'clock_out_longitude' => $_POST['longitude'] ?? null,
            'location_name' => $_POST['location_name'] ?? null,
            'notes' => $_POST['notes'] ?? null
        ];
        
        $result = $this->businessOpsService->clockOut($recordId, $data);
        return $this->json($result);
    }
    
    /**
     * Create express book
     */
    public function expressBook() {
        $data = [
            'guide_id' => $_POST['guide_id'] ?? 0,
            'customer_name' => $_POST['customer_name'] ?? '',
            'customer_phone' => $_POST['customer_phone'] ?? '',
            'customer_email' => $_POST['customer_email'] ?? null,
            'service_type' => $_POST['service_type'] ?? '',
            'duration_hours' => $_POST['duration_hours'] ?? 0,
            'price' => $_POST['price'] ?? 0,
            'payment_method' => $_POST['payment_method'] ?? 'cash',
            'start_datetime' => $_POST['start_datetime'] ?? ''
        ];
        
        $result = $this->businessOpsService->createExpressBook($data);
        return $this->json($result);
    }
    
    /**
     * Get guide statistics
     */
    public function guideStats() {
        $guideId = $_GET['guide_id'] ?? 0;
        $stats = $this->businessOpsService->getGuideStatistics($guideId);
        return $this->json(['success' => true, 'data' => $stats]);
    }
    
    /**
     * Index page
     */
    public function index() {
        $userId = $_SESSION['user_id'] ?? null;
        
        if (!$userId) {
            return $this->redirect('auth/login');
        }
        
        $data = [
            'recent_matches' => [], // Would fetch from model
            'recent_clock_ins' => [] // Would fetch from model
        ];
        $this->view('business_operations/index', $data);
    }
}
