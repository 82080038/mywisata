<?php
/**
 * MyWisata Application - Walk-in Booking Controller
 * 
 * Handles express booking for walk-in customers.
 * 
 * @package MyWisata
 * @version 1.0.0
 * @since 2026-07-18
 */

// Require CurrencyController if it exists
if (file_exists(APP_ROOT . '/app/controllers/CurrencyController.php')) {
    require_once APP_ROOT . '/app/controllers/CurrencyController.php';
}

class WalkInBookingController extends Controller {
    
    private $currencyController;
    
    /**
     * Constructor
     */
    public function __construct() {
        parent::__construct();
        // Initialize currency controller only if needed
        try {
            if (class_exists('CurrencyController')) {
                $this->currencyController = new CurrencyController();
            } else {
                $this->currencyController = null;
            }
        } catch (Exception $e) {
            // Fall back to default currency if currency controller fails
            $this->currencyController = null;
        }
    }
    
    /**
     * Index - Show walk-in booking form
     */
    public function index() {
        Middleware::requireRole(['admin', 'staff']);
        
        $templateModel = $this->model('QuickBookingTemplate');
        $templates = $templateModel->getActive();
        
        $data = [
            'title' => 'Express Book - MyWisata',
            'templates' => $templates,
            'csrf_token' => Middleware::csrfToken()
        ];
        
        $this->view('walk_in_booking/index', $data);
    }
    
    /**
     * Create - Create walk-in booking
     */
    public function create() {
        if (!$this->isAjax()) {
            $this->redirect('home');
        }
        
        if (!$this->validateCsrf()) {
            $this->json(['status' => 'error', 'message' => 'CSRF token mismatch'], 419);
        }
        
        $staffId = Session::get('user_id');
        $currency = $this->currencyController->getUserCurrency($staffId);
        
        $bookingCode = 'WI' . date('YmdHis') . rand(1000, 9999);
        
        $bookingData = [
            'booking_code' => $bookingCode,
            'booking_type' => $this->post('booking_type'),
            'customer_name' => $this->post('customer_name'),
            'customer_phone' => $this->post('customer_phone'),
            'customer_email' => $this->post('customer_email'),
            'number_of_people' => $this->post('number_of_people'),
            'booking_date' => date('Y-m-d'),
            'booking_time' => date('H:i:s'),
            'duration_hours' => $this->post('duration_hours'),
            'total_price' => $this->post('total_price'),
            'currency' => $currency,
            'payment_method' => $this->post('payment_method'),
            'payment_status' => $this->post('payment_status', 'pending'),
            'payment_amount' => $this->post('payment_amount', 0),
            'special_requests' => $this->post('special_requests'),
            'notes' => $this->post('notes'),
            'staff_id' => $staffId,
            'processing_device' => 'desktop',
            'processing_location' => $this->post('processing_location', 'front_desk'),
            'status' => 'confirmed'
        ];
        
        // Add booking item based on type
        $bookingType = $bookingData['booking_type'];
        switch ($bookingType) {
            case 'destination':
                $bookingData['destination_id'] = $this->post('destination_id');
                break;
            case 'hotel':
                $bookingData['hotel_id'] = $this->post('hotel_id');
                break;
            case 'restaurant':
                $bookingData['restaurant_id'] = $this->post('restaurant_id');
                break;
            case 'tour_guide':
                $bookingData['tour_guide_id'] = $this->post('tour_guide_id');
                break;
        }
        
        $walkInBookingModel = $this->model('WalkInBooking');
        $bookingId = $walkInBookingModel->create($bookingData);
        
        if ($bookingId) {
            // Add booking items if provided
            $items = $this->post('items', []);
            if (!empty($items)) {
                $walkInBookingItemModel = $this->model('WalkInBookingItem');
                foreach ($items as $item) {
                    $walkInBookingItemModel->create([
                        'walk_in_booking_id' => $bookingId,
                        'item_type' => $item['item_type'],
                        'item_id' => $item['item_id'],
                        'item_name' => $item['item_name'],
                        'quantity' => $item['quantity'],
                        'unit_price' => $item['unit_price'],
                        'total_price' => $item['quantity'] * $item['unit_price'],
                        'currency' => $currency,
                        'start_time' => $item['start_time'] ?? null,
                        'end_time' => $item['end_time'] ?? null,
                        'notes' => $item['notes'] ?? null
                    ]);
                }
            }
            
            // Update analytics
            $this->updateAnalytics($bookingType, $bookingData['total_price'], $currency);
            
            Logger::audit('CREATE_WALK_IN_BOOKING', 'walk_in_bookings', "Created walk-in booking ID: {$bookingId}", [], $bookingData);
            
            $this->json([
                'status' => 'success',
                'message' => 'Booking berhasil dibuat',
                'booking_id' => $bookingId,
                'booking_code' => $bookingCode
            ]);
        } else {
            $this->json(['status' => 'error', 'message' => 'Gagal membuat booking'], 500);
        }
    }
    
    /**
     * Get quick booking template
     */
    public function getTemplate() {
        if (!$this->isAjax()) {
            $this->redirect('home');
        }
        
        $templateId = $this->get('template_id');
        
        $templateModel = $this->model('QuickBookingTemplate');
        $template = $templateModel->findById($templateId);
        
        if ($template) {
            $this->json([
                'status' => 'success',
                'data' => $template
            ]);
        } else {
            $this->json(['status' => 'error', 'message' => 'Template tidak ditemukan'], 404);
        }
    }
    
    /**
     * Update analytics
     */
    private function updateAnalytics($bookingType, $totalPrice, $currency) {
        $analyticsModel = $this->model('WalkInAnalytics');
        $today = date('Y-m-d');
        
        $analytics = $analyticsModel->getByDateAndType($today, $bookingType);
        
        if ($analytics) {
            $analyticsModel->increment($analytics['id'], $totalPrice);
        } else {
            $analyticsModel->create([
                'date' => $today,
                'booking_type' => $bookingType,
                'total_bookings' => 1,
                'total_revenue' => $totalPrice,
                'currency' => $currency,
                'average_booking_value' => $totalPrice
            ]);
        }
    }
    
    /**
     * List - List walk-in bookings
     */
    public function list() {
        Middleware::requireRole(['admin', 'staff']);
        
        $page = $this->get('page', 1);
        $limit = $this->get('limit', 20);
        $status = $this->get('status', 'all');
        $date = $this->get('date', date('Y-m-d'));
        
        $walkInBookingModel = $this->model('WalkInBooking');
        $bookings = $walkInBookingModel->getList($page, $limit, $status, $date);
        
        $data = [
            'title' => 'Daftar Walk-in Booking - MyWisata',
            'bookings' => $bookings,
            'status_filter' => $status,
            'date_filter' => $date,
            'csrf_token' => Middleware::csrfToken()
        ];
        
        $this->view('walk_in_booking/list', $data);
    }
    
    /**
     * Update walk-in booking status
     */
    public function updateStatus() {
        if (!$this->isAjax()) {
            $this->redirect('home');
        }
        
        if (!$this->validateCsrf()) {
            $this->json(['status' => 'error', 'message' => 'CSRF token mismatch'], 419);
        }
        
        $bookingId = $this->post('booking_id');
        $newStatus = $this->post('status');
        
        $walkInBookingModel = $this->model('WalkInBooking');
        $updated = $walkInBookingModel->updateStatus($bookingId, $newStatus);
        
        if ($updated) {
            Logger::audit('UPDATE_WALK_IN_STATUS', 'walk_in_bookings', "Updated status for booking ID: {$bookingId} to {$newStatus}", [], []);
            
            $this->json(['status' => 'success', 'message' => 'Status berhasil diperbarui']);
        } else {
            $this->json(['status' => 'error', 'message' => 'Gagal memperbarui status'], 500);
        }
    }
}
