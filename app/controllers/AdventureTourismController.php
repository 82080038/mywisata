<?php
/**
 * MyWisata Application - Adventure Tourism Controller
 * 
 * Handles adventure activities and equipment rentals.
 * 
 * @package MyWisata
 * @version 1.0.0
 * @since 2026-07-18
 */

// Require CurrencyController if it exists
if (file_exists(APP_ROOT . '/app/controllers/CurrencyController.php')) {
    require_once APP_ROOT . '/app/controllers/CurrencyController.php';
}

class AdventureTourismController extends Controller {
    
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
     * Index - List adventure activities
     */
    public function index() {
        $page = $this->get('page', 1);
        $limit = $this->get('limit', 12);
        $activityType = $this->get('activity_type', 'all');
        $difficultyLevel = $this->get('difficulty_level', 'all');
        $currency = 'IDR'; // Default currency
        
        if ($this->currencyController) {
            $currency = $this->currencyController->getUserPreferredCurrency(Session::get('user_id'));
        }
        
        $adventureActivityModel = $this->model('AdventureActivity');
        $activities = $adventureActivityModel->getActive($page, $limit, $activityType, $difficultyLevel);
        
        // Convert prices
        $activitiesWithPrices = [];
        foreach ($activities as $activity) {
            if ($this->currencyController) {
                $activity['display_price'] = $this->currencyController->formatCurrency(
                    $this->currencyController->convertAmount($activity['price_per_person'], $activity['currency'], $currency),
                    $currency
                );
            } else {
                // Simple formatting without conversion
                $activity['display_price'] = 'Rp ' . number_format($activity['price_per_person'], 0, ',', '.');
            }
            $activitiesWithPrices[] = $activity;
        }
        $activities = $activitiesWithPrices;
        
        $data = [
            'title' => 'Aktivitas Petualangan - MyWisata',
            'activities' => $activities,
            'activity_type' => $activityType,
            'difficulty_level' => $difficultyLevel,
            'currency' => $currency,
            'csrf_token' => Middleware::csrfToken()
        ];
        
        $this->view('adventure_tourism/index', $data);
    }
    
    /**
     * Show - View adventure activity details
     */
    public function show() {
        $slug = $this->get('slug');
        $currency = $this->currencyController->getUserCurrency(Session::get('user_id'));
        
        $adventureActivityModel = $this->model('AdventureActivity');
        $activity = $adventureActivityModel->findBySlug($slug);
        
        if (!$activity) {
            Session::flash('error', 'Aktivitas tidak ditemukan');
            $this->redirect('adventure-tourism');
        }
        
        // Convert price
        $activity['display_price'] = $this->currencyController->format(
            $this->currencyController->convert($activity['price_per_person'], $activity['currency'], $currency),
            $currency
        );
        
        $data = [
            'title' => $activity['name'] . ' - MyWisata',
            'activity' => $activity,
            'currency' => $currency,
            'csrf_token' => Middleware::csrfToken()
        ];
        
        $this->view('adventure_tourism/show', $data);
    }
    
    /**
     * Book adventure activity
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
        
        $activityId = $this->post('activity_id');
        $activityDate = $this->post('activity_date');
        $activityTime = $this->post('activity_time');
        $numberOfParticipants = $this->post('number_of_participants');
        $equipmentRental = $this->post('equipment_rental', false);
        $equipmentRentalItems = $this->post('equipment_rental_items', []);
        $dietaryRequirements = $this->post('dietary_requirements');
        $medicalConditions = $this->post('medical_conditions');
        
        $adventureActivityModel = $this->model('AdventureActivity');
        $activity = $adventureActivityModel->findById($activityId);
        
        if (!$activity) {
            $this->json(['status' => 'error', 'message' => 'Aktivitas tidak ditemukan'], 404);
        }
        
        // Calculate total price
        $totalPrice = $activity['price_per_person'] * $numberOfParticipants;
        
        // Add equipment rental cost
        if ($equipmentRental && !empty($equipmentRentalItems)) {
            $equipmentRentalModel = $this->model('EquipmentRental');
            foreach ($equipmentRentalItems as $item) {
                $equipment = $equipmentRentalModel->findById($item['equipment_id']);
                if ($equipment) {
                    $totalPrice += $equipment['daily_price'] * $item['quantity'];
                }
            }
        }
        
        $basePrice = $currency === 'IDR' ? $totalPrice : $this->currencyController->convert($totalPrice, $currency, 'IDR');
        $exchangeRate = $currency === 'IDR' ? 1.0 : $this->currencyController->getExchangeRate($currency, 'IDR');
        
        $bookingData = [
            'activity_id' => $activityId,
            'user_id' => $userId,
            'booking_date' => date('Y-m-d'),
            'activity_date' => $activityDate,
            'activity_time' => $activityTime,
            'number_of_participants' => $numberOfParticipants,
            'total_price' => $totalPrice,
            'currency' => $currency,
            'equipment_rental' => $equipmentRental,
            'equipment_rental_items' => json_encode($equipmentRentalItems),
            'dietary_requirements' => json_encode($dietaryRequirements),
            'medical_conditions' => json_encode($medicalConditions),
            'emergency_contact_name' => $this->post('emergency_contact_name'),
            'emergency_contact_phone' => $this->post('emergency_contact_phone'),
            'emergency_contact_relationship' => $this->post('emergency_contact_relationship'),
            'special_requests' => $this->post('special_requests'),
            'status' => 'pending',
            'payment_status' => 'unpaid'
        ];
        
        $adventureBookingModel = $this->model('AdventureActivityBooking');
        $bookingId = $adventureBookingModel->create($bookingData);
        
        if ($bookingId) {
            // Create equipment rental bookings if needed
            if ($equipmentRental && !empty($equipmentRentalItems)) {
                $equipmentRentalBookingModel = $this->model('EquipmentRentalBooking');
                foreach ($equipmentRentalItems as $item) {
                    $equipment = $equipmentRentalModel->findById($item['equipment_id']);
                    if ($equipment) {
                        $equipmentRentalBookingModel->create([
                            'equipment_id' => $item['equipment_id'],
                            'user_id' => $userId,
                            'booking_date' => date('Y-m-d'),
                            'rental_start_date' => $activityDate,
                            'rental_end_date' => $activityDate,
                            'quantity' => $item['quantity'],
                            'total_price' => $equipment['daily_price'] * $item['quantity'],
                            'currency' => $currency,
                            'status' => 'confirmed'
                        ]);
                    }
                }
            }
            
            // Create transaction
            $transactionModel = $this->model('Transaction');
            $transactionData = [
                'transaction_code' => 'AD' . date('YmdHis') . rand(1000, 9999),
                'user_id' => $userId,
                'type' => 'adventure_activity',
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
            
            Logger::audit('BOOK_ADVENTURE_ACTIVITY', 'adventure_activity_bookings', "Booked adventure activity ID: {$bookingId}", [], $bookingData);
            
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
     * Equipment rentals - List available equipment
     */
    public function equipmentRentals() {
        $page = $this->get('page', 1);
        $limit = $this->get('limit', 12);
        $equipmentType = $this->get('equipment_type', 'all');
        $currency = $this->currencyController->getUserCurrency(Session::get('user_id'));
        
        $equipmentRentalModel = $this->model('EquipmentRental');
        $equipment = $equipmentRentalModel->getAvailable($page, $limit, $equipmentType);
        
        // Convert prices
        foreach ($equipment as &$item) {
            $item['display_price'] = $this->currencyController->format(
                $this->currencyController->convert($item['daily_price'], $item['currency'], $currency),
                $currency
            );
        }
        
        $data = [
            'title' => 'Sewa Peralatan - MyWisata',
            'equipment' => $equipment,
            'equipment_type' => $equipmentType,
            'currency' => $currency,
            'csrf_token' => Middleware::csrfToken()
        ];
        
        $this->view('adventure_tourism/equipment_rentals', $data);
    }
    
    /**
     * Book equipment rental
     */
    public function bookEquipment() {
        if (!$this->isAjax()) {
            $this->redirect('home');
        }
        
        if (!$this->validateCsrf()) {
            $this->json(['status' => 'error', 'message' => 'CSRF token mismatch'], 419);
        }
        
        $userId = Session::get('user_id');
        $currency = $this->currencyController->getUserCurrency($userId);
        
        $equipmentId = $this->post('equipment_id');
        $rentalStartDate = $this->post('rental_start_date');
        $rentalEndDate = $this->post('rental_end_date');
        $quantity = $this->post('quantity');
        $size = $this->post('size');
        
        $equipmentRentalModel = $this->model('EquipmentRental');
        $equipment = $equipmentRentalModel->findById($equipmentId);
        
        if (!$equipment) {
            $this->json(['status' => 'error', 'message' => 'Peralatan tidak ditemukan'], 404);
        }
        
        // Calculate rental days
        $startDate = new DateTime($rentalStartDate);
        $endDate = new DateTime($rentalEndDate);
        $days = $startDate->diff($endDate)->days + 1;
        
        // Calculate total price
        $totalPrice = $equipment['daily_price'] * $days * $quantity;
        
        $bookingData = [
            'equipment_id' => $equipmentId,
            'user_id' => $userId,
            'booking_date' => date('Y-m-d'),
            'rental_start_date' => $rentalStartDate,
            'rental_end_date' => $rentalEndDate,
            'quantity' => $quantity,
            'total_price' => $totalPrice,
            'currency' => $currency,
            'size' => $size,
            'status' => 'confirmed',
            'payment_status' => 'unpaid'
        ];
        
        $equipmentRentalBookingModel = $this->model('EquipmentRentalBooking');
        $bookingId = $equipmentRentalBookingModel->create($bookingData);
        
        if ($bookingId) {
            // Create transaction
            $transactionModel = $this->model('Transaction');
            $transactionData = [
                'transaction_code' => 'ER' . date('YmdHis') . rand(1000, 9999),
                'user_id' => $userId,
                'type' => 'equipment_rental',
                'reference_id' => $bookingId,
                'gross_amount' => $totalPrice,
                'discount_amount' => 0,
                'tax_amount' => 0,
                'net_amount' => $totalPrice,
                'currency' => $currency,
                'payment_method' => 'pending'
            ];
            $transactionModel->create($transactionData);
            
            Logger::audit('BOOK_EQUIPMENT_RENTAL', 'equipment_rental_bookings', "Booked equipment rental ID: {$bookingId}", [], $bookingData);
            
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
}
