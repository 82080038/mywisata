<?php
/**
 * MyWisata Application - Availability Controller
 * 
 * Handles real-time availability checking for tour guides.
 * 
 * @package MyWisata
 * @version 1.0.0
 * @since 2026-07-16
 */

class AvailabilityController extends Controller {
    
    private $tourGuideModel;
    
    public function __construct() {
        parent::__construct();
        $this->tourGuideModel = $this->model('TourGuide');
    }
    
    /**
     * Check availability for a guide on specific date/time
     */
    public function check() {
        if (!$this->isAjax()) {
            $this->redirect('home');
        }
        
        $guideId = $this->get('guide_id');
        $date = $this->get('date');
        $startTime = $this->get('start_time');
        $endTime = $this->get('end_time');
        
        if (empty($guideId) || empty($date) || empty($startTime) || empty($endTime)) {
            $this->json(['status' => 'error', 'message' => 'Parameter tidak lengkap'], 400);
        }
        
        $isAvailable = $this->tourGuideModel->checkAvailability($guideId, $date, $startTime, $endTime);
        
        $this->json([
            'status' => 'success',
            'data' => [
                'available' => $isAvailable,
                'guide_id' => $guideId,
                'date' => $date,
                'start_time' => $startTime,
                'end_time' => $endTime
            ]
        ]);
    }
    
    /**
     * Get available time slots for a guide on a specific date
     */
    public function getSlots() {
        if (!$this->isAjax()) {
            $this->redirect('home');
        }
        
        $guideId = $this->get('guide_id');
        $date = $this->get('date');
        
        if (empty($guideId) || empty($date)) {
            $this->json(['status' => 'error', 'message' => 'Parameter tidak lengkap'], 400);
        }
        
        $slots = $this->tourGuideModel->getAvailableSlots($guideId, $date);
        
        $this->json([
            'status' => 'success',
            'data' => [
                'slots' => $slots,
                'guide_id' => $guideId,
                'date' => $date,
                'count' => count($slots)
            ]
        ]);
    }
    
    /**
     * Get availability for multiple guides (batch check)
     */
    public function batchCheck() {
        if (!$this->isAjax()) {
            $this->redirect('home');
        }
        
        $guideIds = $this->get('guide_ids'); // comma-separated or array
        $date = $this->get('date');
        $startTime = $this->get('start_time');
        $endTime = $this->get('end_time');
        
        if (empty($guideIds) || empty($date) || empty($startTime) || empty($endTime)) {
            $this->json(['status' => 'error', 'message' => 'Parameter tidak lengkap'], 400);
        }
        
        // Parse guide IDs
        if (is_string($guideIds)) {
            $guideIds = explode(',', $guideIds);
        }
        
        $results = [];
        foreach ($guideIds as $guideId) {
            $isAvailable = $this->tourGuideModel->checkAvailability($guideId, $date, $startTime, $endTime);
            $results[] = [
                'guide_id' => $guideId,
                'available' => $isAvailable
            ];
        }
        
        $this->json([
            'status' => 'success',
            'data' => [
                'results' => $results,
                'date' => $date,
                'start_time' => $startTime,
                'end_time' => $endTime
            ]
        ]);
    }
    
    /**
     * Get availability calendar for a guide (date range)
     */
    public function getCalendar() {
        if (!$this->isAjax()) {
            $this->redirect('home');
        }
        
        $guideId = $this->get('guide_id');
        $startDate = $this->get('start_date');
        $endDate = $this->get('end_date');
        
        if (empty($guideId) || empty($startDate) || empty($endDate)) {
            $this->json(['status' => 'error', 'message' => 'Parameter tidak lengkap'], 400);
        }
        
        $schedules = $this->tourGuideModel->getSchedulesInRange($guideId, $startDate, $endDate);
        
        // Format for calendar display
        $calendarData = [];
        foreach ($schedules as $schedule) {
            $calendarData[] = [
                'date' => $schedule['available_date'],
                'start_time' => $schedule['start_time'],
                'end_time' => $schedule['end_time'],
                'is_booked' => $schedule['is_booked']
            ];
        }
        
        $this->json([
            'status' => 'success',
            'data' => [
                'calendar' => $calendarData,
                'guide_id' => $guideId,
                'start_date' => $startDate,
                'end_date' => $endDate
            ]
        ]);
    }
    
    /**
     * Reserve availability slot (for booking)
     */
    public function reserve() {
        Middleware::requireAuth();
        
        if (!$this->isAjax()) {
            $this->redirect('home');
        }
        
        // Verify CSRF
        if (!$this->validateCsrf()) {
            $this->json(['status' => 'error', 'message' => 'CSRF token mismatch'], 419);
        }
        
        $guideId = $this->post('guide_id');
        $date = $this->post('date');
        $startTime = $this->post('start_time');
        $endTime = $this->post('end_time');
        
        if (empty($guideId) || empty($date) || empty($startTime) || empty($endTime)) {
            $this->json(['status' => 'error', 'message' => 'Parameter tidak lengkap'], 400);
        }
        
        // Reserve the slot
        $reserved = $this->tourGuideModel->reserveAvailability($guideId, $date, $startTime, $endTime);
        
        if ($reserved) {
            Logger::audit('RESERVE_AVAILABILITY', 'guide_schedules', "Reserved availability for guide ID: {$guideId}", [], [
                'date' => $date,
                'start_time' => $startTime,
                'end_time' => $endTime
            ]);
            
            $this->json([
                'status' => 'success',
                'message' => 'Slot berhasil direservasi'
            ]);
        } else {
            $this->json(['status' => 'error', 'message' => 'Slot tidak tersedia atau sudah direservasi'], 400);
        }
    }
    
    /**
     * Release availability slot (for booking cancellation)
     */
    public function release() {
        Middleware::requireAuth();
        
        if (!$this->isAjax()) {
            $this->redirect('home');
        }
        
        // Verify CSRF
        if (!$this->validateCsrf()) {
            $this->json(['status' => 'error', 'message' => 'CSRF token mismatch'], 419);
        }
        
        $guideId = $this->post('guide_id');
        $date = $this->post('date');
        $startTime = $this->post('start_time');
        
        if (empty($guideId) || empty($date) || empty($startTime)) {
            $this->json(['status' => 'error', 'message' => 'Parameter tidak lengkap'], 400);
        }
        
        // Release the slot
        $released = $this->tourGuideModel->releaseAvailability($guideId, $date, $startTime);
        
        if ($released) {
            Logger::audit('RELEASE_AVAILABILITY', 'guide_schedules', "Released availability for guide ID: {$guideId}", [], [
                'date' => $date,
                'start_time' => $startTime
            ]);
            
            $this->json([
                'status' => 'success',
                'message' => 'Slot berhasil dilepaskan'
            ]);
        } else {
            $this->json(['status' => 'error', 'message' => 'Gagal melepas slot'], 500);
        }
    }
    
    /**
     * Get guide availability summary
     */
    public function getSummary() {
        if (!$this->isAjax()) {
            $this->redirect('home');
        }
        
        $guideId = $this->get('guide_id');
        $startDate = $this->get('start_date');
        $endDate = $this->get('end_date');
        
        if (empty($guideId)) {
            $this->json(['status' => 'error', 'message' => 'Guide ID wajib diisi'], 400);
        }
        
        // Default to next 30 days if no date range specified
        if (empty($startDate)) {
            $startDate = date('Y-m-d');
        }
        if (empty($endDate)) {
            $endDate = date('Y-m-d', strtotime('+30 days'));
        }
        
        $schedules = $this->tourGuideModel->getSchedulesInRange($guideId, $startDate, $endDate);
        
        // Calculate summary
        $totalSlots = count($schedules);
        $bookedSlots = 0;
        $availableSlots = 0;
        
        foreach ($schedules as $schedule) {
            if ($schedule['is_booked']) {
                $bookedSlots++;
            } else {
                $availableSlots++;
            }
        }
        
        $this->json([
            'status' => 'success',
            'data' => [
                'guide_id' => $guideId,
                'start_date' => $startDate,
                'end_date' => $endDate,
                'total_slots' => $totalSlots,
                'booked_slots' => $bookedSlots,
                'available_slots' => $availableSlots,
                'availability_percentage' => $totalSlots > 0 ? round(($availableSlots / $totalSlots) * 100, 1) : 0
            ]
        ]);
    }
}
