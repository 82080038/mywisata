<?php
/**
 * MyWisata Application - Culinary Tourism Controller
 * 
 * Handles food tours and cooking classes.
 * 
 * @package MyWisata
 * @version 1.0.0
 * @since 2026-07-18
 */

// Require CurrencyController if it exists
if (file_exists(APP_ROOT . '/app/controllers/CurrencyController.php')) {
    require_once APP_ROOT . '/app/controllers/CurrencyController.php';
}

class CulinaryTourismController extends Controller {
    
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
     * Index - List food tours
     */
    public function foodTours() {
        $page = $this->get('page', 1);
        $limit = $this->get('limit', 12);
        $currency = 'IDR'; // Default currency
        
        if ($this->currencyController) {
            $currency = $this->currencyController->getUserPreferredCurrency(Session::get('user_id'));
        }
        
        $foodTourModel = $this->model('FoodTour');
        $tours = $foodTourModel->getActive($page, $limit);
        
        // Convert prices
        $toursWithPrices = [];
        foreach ($tours as $tour) {
            if ($this->currencyController) {
                $tour['display_price'] = $this->currencyController->formatCurrency(
                    $this->currencyController->convertAmount($tour['price_per_person'], $tour['currency'], $currency),
                    $currency
                );
            } else {
                // Simple formatting without conversion
                $tour['display_price'] = 'Rp ' . number_format($tour['price_per_person'], 0, ',', '.');
            }
            $toursWithPrices[] = $tour;
        }
        $tours = $toursWithPrices;
        
        $data = [
            'title' => 'Tur Kuliner - MyWisata',
            'tours' => $tours,
            'currency' => $currency,
            'csrf_token' => Middleware::csrfToken()
        ];
        
        $this->view('culinary_tourism/food_tours', $data);
    }
    
    /**
     * Index - List cooking classes
     */
    public function cookingClasses() {
        $page = $this->get('page', 1);
        $limit = $this->get('limit', 12);
        $currency = $this->currencyController->getUserCurrency(Session::get('user_id'));
        
        $cookingClassModel = $this->model('CookingClass');
        $classes = $cookingClassModel->getActive($page, $limit);
        
        // Convert prices
        foreach ($classes as &$class) {
            $class['display_price'] = $this->currencyController->format(
                $this->currencyController->convert($class['price_per_person'], $class['currency'], $currency),
                $currency
            );
        }
        
        $data = [
            'title' => 'Kelas Memasak - MyWisata',
            'classes' => $classes,
            'currency' => $currency,
            'csrf_token' => Middleware::csrfToken()
        ];
        
        $this->view('culinary_tourism/cooking_classes', $data);
    }
    
    /**
     * Show - View food tour details
     */
    public function showFoodTour() {
        $slug = $this->get('slug');
        $currency = $this->currencyController->getUserCurrency(Session::get('user_id'));
        
        $foodTourModel = $this->model('FoodTour');
        $tour = $foodTourModel->findBySlug($slug);
        
        if (!$tour) {
            Session::flash('error', 'Tur tidak ditemukan');
            $this->redirect('culinary-tourism/food-tours');
        }
        
        // Convert price
        $tour['display_price'] = $this->currencyController->format(
            $this->currencyController->convert($tour['price_per_person'], $tour['currency'], $currency),
            $currency
        );
        
        $data = [
            'title' => $tour['name'] . ' - MyWisata',
            'tour' => $tour,
            'currency' => $currency,
            'csrf_token' => Middleware::csrfToken()
        ];
        
        $this->view('culinary_tourism/show_food_tour', $data);
    }
    
    /**
     * Show - View cooking class details
     */
    public function showCookingClass() {
        $slug = $this->get('slug');
        $currency = $this->currencyController->getUserCurrency(Session::get('user_id'));
        
        $cookingClassModel = $this->model('CookingClass');
        $class = $cookingClassModel->findBySlug($slug);
        
        if (!$class) {
            Session::flash('error', 'Kelas tidak ditemukan');
            $this->redirect('culinary-tourism/cooking-classes');
        }
        
        // Get menu items
        $menuModel = $this->model('CookingClassMenuItem');
        $menuItems = $menuModel->getByClassId($class['id']);
        
        // Convert price
        $class['display_price'] = $this->currencyController->format(
            $this->currencyController->convert($class['price_per_person'], $class['currency'], $currency),
            $currency
        );
        
        $data = [
            'title' => $class['name'] . ' - MyWisata',
            'class' => $class,
            'menu_items' => $menuItems,
            'currency' => $currency,
            'csrf_token' => Middleware::csrfToken()
        ];
        
        $this->view('culinary_tourism/show_cooking_class', $data);
    }
    
    /**
     * Book food tour
     */
    public function bookFoodTour() {
        if (!$this->isAjax()) {
            $this->redirect('home');
        }
        
        if (!$this->validateCsrf()) {
            $this->json(['status' => 'error', 'message' => 'CSRF token mismatch'], 419);
        }
        
        $userId = Session::get('user_id');
        $currency = $this->currencyController->getUserCurrency($userId);
        
        $foodTourId = $this->post('food_tour_id');
        $tourDate = $this->post('tour_date');
        $tourTime = $this->post('tour_time');
        $numberOfParticipants = $this->post('number_of_participants');
        $dietaryRestrictions = $this->post('dietary_restrictions');
        
        $foodTourModel = $this->model('FoodTour');
        $tour = $foodTourModel->findById($foodTourId);
        
        if (!$tour) {
            $this->json(['status' => 'error', 'message' => 'Tur tidak ditemukan'], 404);
        }
        
        // Calculate total price
        $totalPrice = $tour['price_per_person'] * $numberOfParticipants;
        $basePrice = $currency === 'IDR' ? $totalPrice : $this->currencyController->convert($totalPrice, $currency, 'IDR');
        $exchangeRate = $currency === 'IDR' ? 1.0 : $this->currencyController->getExchangeRate($currency, 'IDR');
        
        $bookingData = [
            'food_tour_id' => $foodTourId,
            'user_id' => $userId,
            'booking_date' => date('Y-m-d'),
            'tour_date' => $tourDate,
            'tour_time' => $tourTime,
            'number_of_participants' => $numberOfParticipants,
            'total_price' => $totalPrice,
            'currency' => $currency,
            'dietary_restrictions' => json_encode($dietaryRestrictions),
            'contact_person_name' => $this->post('contact_person_name'),
            'contact_person_phone' => $this->post('contact_person_phone'),
            'contact_person_email' => $this->post('contact_person_email'),
            'status' => 'pending',
            'payment_status' => 'unpaid'
        ];
        
        $foodTourBookingModel = $this->model('FoodTourBooking');
        $bookingId = $foodTourBookingModel->create($bookingData);
        
        if ($bookingId) {
            // Create transaction
            $transactionModel = $this->model('Transaction');
            $transactionData = [
                'transaction_code' => 'FT' . date('YmdHis') . rand(1000, 9999),
                'user_id' => $userId,
                'type' => 'food_tour',
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
            
            Logger::audit('BOOK_FOOD_TOUR', 'food_tour_bookings', "Booked food tour ID: {$bookingId}", [], $bookingData);
            
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
     * Book cooking class
     */
    public function bookCookingClass() {
        if (!$this->isAjax()) {
            $this->redirect('home');
        }
        
        if (!$this->validateCsrf()) {
            $this->json(['status' => 'error', 'message' => 'CSRF token mismatch'], 419);
        }
        
        $userId = Session::get('user_id');
        $currency = $this->currencyController->getUserCurrency($userId);
        
        $cookingClassId = $this->post('cooking_class_id');
        $classDate = $this->post('class_date');
        $classTime = $this->post('class_time');
        $numberOfParticipants = $this->post('number_of_participants');
        $dietaryRestrictions = $this->post('dietary_restrictions');
        $skillLevel = $this->post('skill_level', 'beginner');
        
        $cookingClassModel = $this->model('CookingClass');
        $class = $cookingClassModel->findById($cookingClassId);
        
        if (!$class) {
            $this->json(['status' => 'error', 'message' => 'Kelas tidak ditemukan'], 404);
        }
        
        // Calculate total price
        $totalPrice = $class['price_per_person'] * $numberOfParticipants;
        $basePrice = $currency === 'IDR' ? $totalPrice : $this->currencyController->convert($totalPrice, $currency, 'IDR');
        $exchangeRate = $currency === 'IDR' ? 1.0 : $this->currencyController->getExchangeRate($currency, 'IDR');
        
        $bookingData = [
            'cooking_class_id' => $cookingClassId,
            'user_id' => $userId,
            'booking_date' => date('Y-m-d'),
            'class_date' => $classDate,
            'class_time' => $classTime,
            'number_of_participants' => $numberOfParticipants,
            'total_price' => $totalPrice,
            'currency' => $currency,
            'dietary_restrictions' => json_encode($dietaryRestrictions),
            'skill_level' => $skillLevel,
            'contact_person_name' => $this->post('contact_person_name'),
            'contact_person_phone' => $this->post('contact_person_phone'),
            'contact_person_email' => $this->post('contact_person_email'),
            'status' => 'pending',
            'payment_status' => 'unpaid'
        ];
        
        $cookingClassBookingModel = $this->model('CookingClassBooking');
        $bookingId = $cookingClassBookingModel->create($bookingData);
        
        if ($bookingId) {
            // Create transaction
            $transactionModel = $this->model('Transaction');
            $transactionData = [
                'transaction_code' => 'CC' . date('YmdHis') . rand(1000, 9999),
                'user_id' => $userId,
                'type' => 'cooking_class',
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
            
            Logger::audit('BOOK_COOKING_CLASS', 'cooking_class_bookings', "Booked cooking class ID: {$bookingId}", [], $bookingData);
            
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
