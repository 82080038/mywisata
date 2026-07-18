<?php
/**
 * MyWisata Application - Religious Tourism Controller
 * 
 * Handles pilgrimage packages and religious events.
 * 
 * @package MyWisata
 * @version 1.0.0
 * @since 2026-07-18
 */

// Require CurrencyController if it exists
if (file_exists(APP_ROOT . '/app/controllers/CurrencyController.php')) {
    require_once APP_ROOT . '/app/controllers/CurrencyController.php';
}

class ReligiousTourismController extends Controller {
    
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
     * Index - List pilgrimage packages
     */
    public function index() {
        $page = $this->get('page', 1);
        $limit = $this->get('limit', 12);
        $destinationType = $this->get('destination_type', 'all');
        $currency = 'IDR'; // Default currency
        
        if ($this->currencyController) {
            $currency = $this->currencyController->getUserPreferredCurrency(Session::get('user_id'));
        }
        
        $pilgrimageModel = $this->model('PilgrimagePackage');
        $packages = $pilgrimageModel->getActive($page, $limit, $destinationType);
        
        // Convert prices
        $packagesWithPrices = [];
        foreach ($packages as $package) {
            if ($this->currencyController) {
                $package['display_price'] = $this->currencyController->formatCurrency(
                    $this->currencyController->convertAmount($package['price_per_person'], $package['currency'], $currency),
                    $currency
                );
            } else {
                // Simple formatting without conversion
                $package['display_price'] = 'Rp ' . number_format($package['price_per_person'], 0, ',', '.');
            }
            $packagesWithPrices[] = $package;
        }
        $packages = $packagesWithPrices;
        
        $data = [
            'title' => 'Paket Ziarah & Umrah - MyWisata',
            'packages' => $packages,
            'destination_type' => $destinationType,
            'currency' => $currency,
            'csrf_token' => Middleware::csrfToken()
        ];
        
        $this->view('religious_tourism/index', $data);
    }
    
    /**
     * Show - View pilgrimage package details
     */
    public function show() {
        $slug = $this->get('slug');
        $currency = $this->currencyController->getUserCurrency(Session::get('user_id'));
        
        $pilgrimageModel = $this->model('PilgrimagePackage');
        $package = $pilgrimageModel->findBySlug($slug);
        
        if (!$package) {
            Session::flash('error', 'Paket tidak ditemukan');
            $this->redirect('religious-tourism');
        }
        
        // Get package itinerary
        $itineraryModel = $this->model('PilgrimagePackageItinerary');
        $itinerary = $itineraryModel->getByPackageId($package['id']);
        
        // Convert price
        $package['display_price'] = $this->currencyController->format(
            $this->currencyController->convert($package['price_per_person'], $package['currency'], $currency),
            $currency
        );
        
        $data = [
            'title' => $package['name'] . ' - MyWisata',
            'package' => $package,
            'itinerary' => $itinerary,
            'currency' => $currency,
            'csrf_token' => Middleware::csrfToken()
        ];
        
        $this->view('religious_tourism/show', $data);
    }
    
    /**
     * Book pilgrimage package
     */
    public function book() {
        if (!$this->isAjax()) {
            $this->redirect('home');
        }
        
        if (!$this->validateCsrf()) {
            $this->json(['status' => 'error', 'message' => 'CSRF token mismatch'], 419);
        }
        
        $userId = Session::get('user_id');
        $currency = $this->currencyController->getUserCurrency($userId);
        
        $packageId = $this->post('package_id');
        $departureDate = $this->post('departure_date');
        $returnDate = $this->post('return_date');
        $numberOfPilgrims = $this->post('number_of_pilgrims');
        $medicalRequirements = $this->post('medical_requirements');
        $dietaryRequirements = $this->post('dietary_requirements');
        $roomPreference = $this->post('room_preference', 'shared');
        $genderGroup = $this->post('gender_group', 'mixed');
        
        $pilgrimageModel = $this->model('PilgrimagePackage');
        $package = $pilgrimageModel->findById($packageId);
        
        if (!$package) {
            $this->json(['status' => 'error', 'message' => 'Paket tidak ditemukan'], 404);
        }
        
        // Calculate total price
        $totalPrice = $package['price_per_person'] * $numberOfPilgrims;
        $basePrice = $currency === 'IDR' ? $totalPrice : $this->currencyController->convert($totalPrice, $currency, 'IDR');
        $exchangeRate = $currency === 'IDR' ? 1.0 : $this->currencyController->getExchangeRate($currency, 'IDR');
        
        $bookingData = [
            'package_id' => $packageId,
            'user_id' => $userId,
            'booking_date' => date('Y-m-d'),
            'departure_date' => $departureDate,
            'return_date' => $returnDate,
            'number_of_pilgrims' => $numberOfPilgrims,
            'total_price' => $totalPrice,
            'currency' => $currency,
            'medical_requirements' => json_encode($medicalRequirements),
            'dietary_requirements' => json_encode($dietaryRequirements),
            'room_preference' => $roomPreference,
            'gender_group' => $genderGroup,
            'group_leader_name' => $this->post('group_leader_name'),
            'group_leader_phone' => $this->post('group_leader_phone'),
            'group_leader_email' => $this->post('group_leader_email'),
            'emergency_contact_name' => $this->post('emergency_contact_name'),
            'emergency_contact_phone' => $this->post('emergency_contact_phone'),
            'emergency_contact_relationship' => $this->post('emergency_contact_relationship'),
            'status' => 'pending',
            'payment_status' => 'unpaid'
        ];
        
        $pilgrimageBookingModel = $this->model('PilgrimageBooking');
        $bookingId = $pilgrimageBookingModel->create($bookingData);
        
        if ($bookingId) {
            // Create transaction
            $transactionModel = $this->model('Transaction');
            $transactionData = [
                'transaction_code' => 'PL' . date('YmdHis') . rand(1000, 9999),
                'user_id' => $userId,
                'type' => 'pilgrimage_package',
                'reference_id' => $bookingId,
                'gross_amount' => $totalPrice,
                'discount_amount' => 0,
                'tax_amount' => 0,
                'net_amount' => $totalPrice,
                'currency' => $currency,
                'original_amount' => $totalPrice,
                'base_amount' => $basePrice,
                'exchange_rate' => $exchangeRate,
                'exchange_rate_date' => date('Y-m-d H:i:s'),
                'payment_method' => 'pending'
            ];
            $transactionModel->create($transactionData);
            
            Logger::audit('BOOK_PILGRIMAGE', 'pilgrimage_bookings', "Booked pilgrimage ID: {$bookingId}", [], $bookingData);
            
            $this->json([
                'status' => 'success',
                'message' => 'Booking berhasil dibuat',
                'booking_id' => $bookingId,
                'transaction_code' => $transactionData['transaction_code']
            ]);
        } else {
            $this->json(['status' => 'error', 'message' => 'Gagal membuat booking'], 500);
        }
    }
    
    /**
     * Religious events - List religious events
     */
    public function events() {
        $page = $this->get('page', 1);
        $limit = $this->get('limit', 12);
        $eventType = $this->get('event_type', 'all');
        
        $religiousEventModel = $this->model('ReligiousEvent');
        $events = $religiousEventModel->getActive($page, $limit, $eventType);
        
        $data = [
            'title' => 'Event Keagamaan - MyWisata',
            'events' => $events,
            'event_type' => $eventType,
            'csrf_token' => Middleware::csrfToken()
        ];
        
        $this->view('religious_tourism/events', $data);
    }
    
    /**
     * Show - View religious event details
     */
    public function showEvent() {
        $eventId = $this->get('id');
        
        $religiousEventModel = $this->model('ReligiousEvent');
        $event = $religiousEventModel->findById($eventId);
        
        if (!$event) {
            Session::flash('error', 'Event tidak ditemukan');
            $this->redirect('religious-tourism/events');
        }
        
        $data = [
            'title' => $event['event_name'] . ' - MyWisata',
            'event' => $event,
            'csrf_token' => Middleware::csrfToken()
        ];
        
        $this->view('religious_tourism/show_event', $data);
    }
    
    /**
     * Register for religious event
     */
    public function registerEvent() {
        if (!$this->isAjax()) {
            $this->redirect('home');
        }
        
        if (!$this->validateCsrf()) {
            $this->json(['status' => 'error', 'message' => 'CSRF token mismatch'], 419);
        }
        
        $userId = Session::get('user_id');
        $eventId = $this->post('event_id');
        $numberOfAttendees = $this->post('number_of_attendees');
        
        $religiousEventModel = $this->model('ReligiousEvent');
        $event = $religiousEventModel->findById($eventId);
        
        if (!$event) {
            $this->json(['status' => 'error', 'message' => 'Event tidak ditemukan'], 404);
        }
        
        // Calculate total price if applicable
        $totalPrice = $event['registration_fee'] ? $event['registration_fee'] * $numberOfAttendees : 0;
        
        $registrationData = [
            'event_id' => $eventId,
            'user_id' => $userId,
            'number_of_attendees' => $numberOfAttendees,
            'total_price' => $totalPrice,
            'currency' => $event['currency'] ?? 'IDR',
            'status' => 'registered',
            'payment_status' => $totalPrice > 0 ? 'unpaid' : 'paid'
        ];
        
        // This would need a separate table for event registrations
        // For now, log the registration
        Logger::audit('REGISTER_RELIGIOUS_EVENT', 'religious_events', "Registered for event ID: {$eventId}", [], $registrationData);
        
        $this->json([
            'status' => 'success',
            'message' => 'Pendaftaran berhasil'
        ]);
    }
}
