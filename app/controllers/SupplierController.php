<?php
/**
 * MyWisata Application - Supplier Controller
 * 
 * Handles supplier self-service portal for managing listings, bookings, and profiles.
 * 
 * @package MyWisata
 * @version 1.0.0
 * @since 2026-07-15
 */

class SupplierController extends Controller {
    
    /**
     * Constructor - Require login and supplier role
     */
    public function __construct() {
        parent::__construct();
        if (!Session::get('user_id')) {
            $this->redirect('auth/login');
        }
        
        // Check if user is a supplier (tour guide, hotel owner, restaurant owner, event organizer)
        $userRole = Session::get('role');
        $allowedRoles = ['tour_guide', 'admin'];
        
        if (!in_array($userRole, $allowedRoles)) {
            Session::flash('error', 'Access denied. Supplier access required.');
            $this->redirect('home');
        }
    }
    
    /**
     * Supplier dashboard
     */
    public function index() {
        $userId = Session::get('user_id');
        $userRole = Session::get('role');
        
        $data = [
            'title' => 'Supplier Dashboard',
            'user_role' => $userRole
        ];
        
        // Get role-specific data
        switch ($userRole) {
            case 'tour_guide':
                $tourGuideModel = $this->model('TourGuide');
                $guide = $tourGuideModel->findByUserId($userId);
                $bookings = $tourGuideModel->getBookings($guide['id'] ?? 0, 'pending');
                $earnings = $tourGuideModel->getEarnings($guide['id'] ?? 0, 'month');
                
                $data['guide'] = $guide;
                $data['pending_bookings'] = $bookings;
                $data['monthly_earnings'] = $earnings;
                break;
                
            case 'admin':
                // Admin sees all supplier data
                $data['is_admin'] = true;
                break;
        }
        
        $this->view('supplier/dashboard', $data);
    }
    
    /**
     * Profile management
     */
    public function profile() {
        $userId = Session::get('user_id');
        $userRole = Session::get('role');
        
        $data = [
            'title' => 'Manage Profile'
        ];
        
        switch ($userRole) {
            case 'tour_guide':
                $tourGuideModel = $this->model('TourGuide');
                $guide = $tourGuideModel->findByUserId($userId);
                
                // Get languages and specializations
                if ($guide) {
                    $data['guide'] = $guide;
                    $data['languages'] = $tourGuideModel->getLanguages($guide['id']);
                    $data['specializations'] = $tourGuideModel->getSpecializations($guide['id']);
                }
                break;
        }
        
        $this->view('supplier/profile', $data);
    }
    
    /**
     * Update profile
     */
    public function updateProfile() {
        $userId = Session::get('user_id');
        $userRole = Session::get('role');
        
        $input = $this->post();
        
        switch ($userRole) {
            case 'tour_guide':
                $tourGuideModel = $this->model('TourGuide');
                
                $guideData = [
                    'user_id' => $userId,
                    'name' => $input['name'] ?? '',
                    'phone' => $input['phone'] ?? '',
                    'bio' => $input['bio'] ?? '',
                    'license_number' => $input['license_number'] ?? '',
                    'experience_years' => $input['experience_years'] ?? 0,
                    'hourly_rate' => $input['hourly_rate'] ?? 0,
                    'daily_rate' => $input['daily_rate'] ?? 0,
                    'city' => $input['city'] ?? '',
                    'latitude' => $input['latitude'] ?? null,
                    'longitude' => $input['longitude'] ?? null,
                    'is_available' => isset($input['is_available']) ? 1 : 0
                ];
                
                $tourGuideModel->save($guideData);
                
                // Update languages
                if (isset($input['languages']) && is_array($input['languages'])) {
                    $guide = $tourGuideModel->findByUserId($userId);
                    foreach ($input['languages'] as $lang) {
                        $tourGuideModel->addLanguage($guide['id'], $lang['language'], $lang['proficiency']);
                    }
                }
                
                // Update specializations
                if (isset($input['specializations']) && is_array($input['specializations'])) {
                    $guide = $tourGuideModel->findByUserId($userId);
                    foreach ($input['specializations'] as $spec) {
                        $tourGuideModel->addSpecialization($guide['id'], $spec);
                    }
                }
                
                Logger::audit('UPDATE_PROFILE', 'tour_guides', "Profile updated by user ID: {$userId}");
                break;
        }
        
        Session::flash('success', 'Profile updated successfully');
        $this->redirect('supplier/profile');
    }
    
    /**
     * Schedule management
     */
    public function schedule() {
        $userId = Session::get('user_id');
        $userRole = Session::get('role');
        
        if ($userRole !== 'tour_guide') {
            Session::flash('error', 'Schedule management only available for tour guides');
            $this->redirect('supplier');
        }
        
        $tourGuideModel = $this->model('TourGuide');
        $guide = $tourGuideModel->findByUserId($userId);
        
        $data = [
            'title' => 'Manage Schedule',
            'guide' => $guide
        ];
        
        $this->view('supplier/schedule', $data);
    }
    
    /**
     * Add schedule
     */
    public function addSchedule() {
        $userId = Session::get('user_id');
        
        $input = $this->post();
        
        $tourGuideModel = $this->model('TourGuide');
        $guide = $tourGuideModel->findByUserId($userId);
        
        if (!$guide) {
            $this->json(['status' => 'error', 'message' => 'Guide profile not found'], 404);
        }
        
        $result = $tourGuideModel->addSchedule(
            $guide['id'],
            $input['date'],
            $input['start_time'],
            $input['end_time']
        );
        
        if ($result) {
            Logger::audit('ADD_SCHEDULE', 'guide_schedules', "Schedule added by guide ID: {$guide['id']}");
            $this->json(['status' => 'success', 'message' => 'Schedule added successfully']);
        } else {
            $this->json(['status' => 'error', 'message' => 'Failed to add schedule'], 500);
        }
    }
    
    /**
     * Bookings management
     */
    public function bookings() {
        $userId = Session::get('user_id');
        $userRole = Session::get('role');
        
        $data = [
            'title' => 'Manage Bookings'
        ];
        
        switch ($userRole) {
            case 'tour_guide':
                $tourGuideModel = $this->model('TourGuide');
                $guide = $tourGuideModel->findByUserId($userId);
                
                if ($guide) {
                    $data['bookings'] = $tourGuideModel->getBookings($guide['id']);
                }
                break;
        }
        
        $this->view('supplier/bookings', $data);
    }
    
    /**
     * Update booking status
     */
    public function updateBookingStatus() {
        $input = $this->post();
        $userId = Session::get('user_id');
        $userRole = Session::get('role');
        
        $bookingId = $input['booking_id'];
        $status = $input['status'];
        
        // Validate status
        $validStatuses = ['pending', 'confirmed', 'completed', 'cancelled', 'rejected'];
        if (!in_array($status, $validStatuses)) {
            $this->json(['status' => 'error', 'message' => 'Invalid status'], 400);
        }
        
        switch ($userRole) {
            case 'tour_guide':
                $bookingModel = $this->model('Booking');
                $booking = $bookingModel->findById($bookingId);
                
                // Verify ownership
                $tourGuideModel = $this->model('TourGuide');
                $guide = $tourGuideModel->findByUserId($userId);
                
                if (!$booking || $booking['guide_id'] != $guide['id']) {
                    $this->json(['status' => 'error', 'message' => 'Booking not found'], 404);
                }
                
                $bookingModel->updateStatus($bookingId, $status);
                
                // Release or reserve availability based on status
                if ($status === 'cancelled' || $status === 'rejected') {
                    $tourGuideModel->releaseAvailability($guide['id'], $booking['booking_date'], $booking['start_time']);
                }
                
                Logger::audit('UPDATE_BOOKING_STATUS', 'bookings', "Booking status updated to {$status} by guide ID: {$guide['id']}");
                break;
        }
        
        $this->json(['status' => 'success', 'message' => 'Booking status updated']);
    }
    
    /**
     * Earnings report
     */
    public function earnings() {
        $userId = Session::get('user_id');
        $userRole = Session::get('role');
        
        $data = [
            'title' => 'Earnings Report'
        ];
        
        switch ($userRole) {
            case 'tour_guide':
                $tourGuideModel = $this->model('TourGuide');
                $guide = $tourGuideModel->findByUserId($userId);
                
                if ($guide) {
                    $data['earnings_month'] = $tourGuideModel->getEarnings($guide['id'], 'month');
                    $data['earnings_year'] = $tourGuideModel->getEarnings($guide['id'], 'year');
                    $data['earnings_all'] = $tourGuideModel->getEarnings($guide['id'], 'all');
                }
                break;
        }
        
        $this->view('supplier/earnings', $data);
    }
    
    /**
     * Documents management
     */
    public function documents() {
        $userId = Session::get('user_id');
        $userRole = Session::get('role');
        
        if ($userRole !== 'tour_guide') {
            Session::flash('error', 'Documents management only available for tour guides');
            $this->redirect('supplier');
        }
        
        $tourGuideModel = $this->model('TourGuide');
        $guide = $tourGuideModel->findByUserId($userId);
        
        $data = [
            'title' => 'Manage Documents',
            'guide' => $guide
        ];
        
        $this->view('supplier/documents', $data);
    }
    
    /**
     * Upload document
     */
    public function uploadDocument() {
        $userId = Session::get('user_id');
        
        if (!isset($_FILES['document'])) {
            $this->json(['status' => 'error', 'message' => 'No file uploaded'], 400);
        }
        
        $file = $_FILES['document'];
        $documentType = $this->post('document_type');
        
        // Validate document type
        $validTypes = ['ktp', 'sertifikat', 'lisensi', 'other'];
        if (!in_array($documentType, $validTypes)) {
            $this->json(['status' => 'error', 'message' => 'Invalid document type'], 400);
        }
        
        // Upload file
        $uploadResult = FileUpload::upload($file, 'documents', ['application/pdf', 'image/jpeg', 'image/png']);
        
        if (!$uploadResult['success']) {
            $this->json(['status' => 'error', 'message' => $uploadResult['message']], 400);
        }
        
        // Save to database
        $tourGuideModel = $this->model('TourGuide');
        $guide = $tourGuideModel->findByUserId($userId);
        
        $sql = "INSERT INTO guide_documents (guide_id, document_type, file_path, uploaded_at)
                VALUES (:guide_id, :document_type, :file_path, NOW())";
        
        $this->db->query($sql, [
            'guide_id' => $guide['id'],
            'document_type' => $documentType,
            'file_path' => $uploadResult['filepath']
        ]);
        
        Logger::audit('UPLOAD_DOCUMENT', 'guide_documents', "Document uploaded by guide ID: {$guide['id']}");
        
        $this->json(['status' => 'success', 'message' => 'Document uploaded successfully']);
    }
    
    /**
     * Settings
     */
    public function settings() {
        $data = [
            'title' => 'Supplier Settings'
        ];
        
        $this->view('supplier/settings', $data);
    }
    
    /**
     * Update availability
     */
    public function updateAvailability() {
        $userId = Session::get('user_id');
        $isAvailable = $this->post('is_available') === 'true';
        
        $tourGuideModel = $this->model('TourGuide');
        $tourGuideModel->updateAvailability($userId, $isAvailable);
        
        Logger::audit('UPDATE_AVAILABILITY', 'tour_guides', "Availability updated by user ID: {$userId}");
        
        $this->json(['status' => 'success', 'message' => 'Availability updated']);
    }
}
