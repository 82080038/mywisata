<?php
/**
 * MyWisata Application - Agritourism Controller
 * 
 * Handles farm tours and agricultural activities.
 * 
 * @package MyWisata
 * @version 1.0.0
 * @since 2026-07-18
 */

// Require CurrencyController if it exists
if (file_exists(APP_ROOT . '/app/controllers/CurrencyController.php')) {
    require_once APP_ROOT . '/app/controllers/CurrencyController.php';
}

class AgritourismController extends Controller {
    
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
     * Index - List farms
     */
    public function index() {
        $page = $this->get('page', 1);
        $limit = $this->get('limit', 12);
        $farmType = $this->get('farm_type', 'all');
        $currency = 'IDR'; // Default currency
        
        if ($this->currencyController) {
            $currency = $this->currencyController->getUserPreferredCurrency(Session::get('user_id'));
        }
        
        $farmModel = $this->model('Farm');
        $farms = $farmModel->getActive($page, $limit, $farmType);
        
        $data = [
            'title' => 'Wisata Pertanian - MyWisata',
            'farms' => $farms,
            'farm_type' => $farmType,
            'currency' => $currency,
            'csrf_token' => Middleware::csrfToken()
        ];
        
        $this->view('agritourism/index', $data);
    }
    
    /**
     * Show - View farm details
     */
    public function show() {
        $slug = $this->get('slug');
        $currency = $this->currencyController->getUserCurrency(Session::get('user_id'));
        
        $farmModel = $this->model('Farm');
        $farm = $farmModel->findBySlug($slug);
        
        if (!$farm) {
            Session::flash('error', 'Kebun tidak ditemukan');
            $this->redirect('agritourism');
        }
        
        // Get farm activities
        $farmActivityModel = $this->model('FarmActivity');
        $activities = $farmActivityModel->getByFarmId($farm['id']);
        
        // Get farm tour packages
        $farmTourPackageModel = $this->model('FarmTourPackage');
        $packages = $farmTourPackageModel->getByFarmId($farm['id']);
        
        // Convert prices
        foreach ($activities as &$activity) {
            $activity['display_price'] = $this->currencyController->format(
                $this->currencyController->convert($activity['price_per_person'], $activity['currency'], $currency),
                $currency
            );
        }
        
        foreach ($packages as &$package) {
            $package['display_price'] = $this->currencyController->format(
                $this->currencyController->convert($package['price_per_person'], $package['currency'], $currency),
                $currency
            );
        }
        
        $data = [
            'title' => $farm['name'] . ' - MyWisata',
            'farm' => $farm,
            'activities' => $activities,
            'packages' => $packages,
            'currency' => $currency,
            'csrf_token' => Middleware::csrfToken()
        ];
        
        $this->view('agritourism/show', $data);
    }
    
    /**
     * Book farm activity
     */
    public function bookActivity() {
        if (!$this->isAjax()) {
            $this->redirect('home');
        }
        
        if (!$this->validateCsrf()) {
            $this->json(['status' => 'error', 'message' => 'CSRF token mismatch'], 419);
        }
        
        $userId = Session::get('user_id');
        $currency = $this->currencyController->getUserCurrency($userId);
        
        $farmId = $this->post('farm_id');
        $activityId = $this->post('activity_id');
        $activityDate = $this->post('activity_date');
        $activityTime = $this->post('activity_time');
        $numberOfParticipants = $this->post('number_of_participants');
        $groupType = $this->post('group_type', 'tourist');
        $ageRange = $this->post('age_range');
        $specialRequirements = $this->post('special_requirements');
        
        $farmModel = $this->model('Farm');
        $farm = $farmModel->findById($farmId);
        
        if (!$farm) {
            $this->json(['status' => 'error', 'message' => 'Kebun tidak ditemukan'], 404);
        }
        
        $farmActivityModel = $this->model('FarmActivity');
        $activity = $farmActivityModel->findById($activityId);
        
        if (!$activity) {
            $this->json(['status' => 'error', 'message' => 'Aktivitas tidak ditemukan'], 404);
        }
        
        // Calculate total price
        $totalPrice = $activity['price_per_person'] * $numberOfParticipants;
        $basePrice = $currency === 'IDR' ? $totalPrice : $this->currencyController->convert($totalPrice, $currency, 'IDR');
        $exchangeRate = $currency === 'IDR' ? 1.0 : $this->currencyController->getExchangeRate($currency, 'IDR');
        
        $bookingData = [
            'farm_id' => $farmId,
            'activity_id' => $activityId,
            'user_id' => $userId,
            'booking_date' => date('Y-m-d'),
            'activity_date' => $activityDate,
            'activity_time' => $activityTime,
            'number_of_participants' => $numberOfParticipants,
            'total_price' => $totalPrice,
            'currency' => $currency,
            'group_type' => $groupType,
            'age_range' => $ageRange,
            'special_requirements' => $specialRequirements,
            'contact_person_name' => $this->post('contact_person_name'),
            'contact_person_phone' => $this->post('contact_person_phone'),
            'contact_person_email' => $this->post('contact_person_email'),
            'status' => 'pending',
            'payment_status' => 'unpaid'
        ];
        
        $farmActivityBookingModel = $this->model('FarmActivityBooking');
        $bookingId = $farmActivityBookingModel->create($bookingData);
        
        if ($bookingId) {
            // Create transaction
            $transactionModel = $this->model('Transaction');
            $transactionData = [
                'transaction_code' => 'FA' . date('YmdHis') . rand(1000, 9999),
                'user_id' => $userId,
                'type' => 'farm_activity',
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
            
            Logger::audit('BOOK_FARM_ACTIVITY', 'farm_activity_bookings', "Booked farm activity ID: {$bookingId}", [], $bookingData);
            
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
     * Book farm tour package
     */
    public function bookPackage() {
        if (!$this->isAjax()) {
            $this->redirect('home');
        }
        
        if (!$this->validateCsrf()) {
            $this->json(['status' => 'error', 'message' => 'CSRF token mismatch'], 419);
        }
        
        $userId = Session::get('user_id');
        $currency = $this->currencyController->getUserCurrency($userId);
        
        $farmId = $this->post('farm_id');
        $packageId = $this->post('package_id');
        $tourDate = $this->post('tour_date');
        $numberOfParticipants = $this->post('number_of_participants');
        $groupType = $this->post('group_type', 'tourist');
        
        $farmModel = $this->model('Farm');
        $farm = $farmModel->findById($farmId);
        
        if (!$farm) {
            $this->json(['status' => 'error', 'message' => 'Kebun tidak ditemukan'], 404);
        }
        
        $farmTourPackageModel = $this->model('FarmTourPackage');
        $package = $farmTourPackageModel->findById($packageId);
        
        if (!$package) {
            $this->json(['status' => 'error', 'message' => 'Paket tidak ditemukan'], 404);
        }
        
        // Calculate total price
        $totalPrice = $package['price_per_person'] * $numberOfParticipants;
        $basePrice = $currency === 'IDR' ? $totalPrice : $this->currencyController->convert($totalPrice, $currency, 'IDR');
        $exchangeRate = $currency === 'IDR' ? 1.0 : $this->currencyController->getExchangeRate($currency, 'IDR');
        
        $bookingData = [
            'farm_id' => $farmId,
            'package_id' => $packageId,
            'user_id' => $userId,
            'booking_date' => date('Y-m-d'),
            'activity_date' => $tourDate,
            'activity_time' => '09:00:00',
            'number_of_participants' => $numberOfParticipants,
            'total_price' => $totalPrice,
            'currency' => $currency,
            'group_type' => $groupType,
            'contact_person_name' => $this->post('contact_person_name'),
            'contact_person_phone' => $this->post('contact_person_phone'),
            'contact_person_email' => $this->post('contact_person_email'),
            'status' => 'pending',
            'payment_status' => 'unpaid'
        ];
        
        $farmActivityBookingModel = $this->model('FarmActivityBooking');
        $bookingId = $farmActivityBookingModel->create($bookingData);
        
        if ($bookingId) {
            // Create transaction
            $transactionModel = $this->model('Transaction');
            $transactionData = [
                'transaction_code' => 'FP' . date('YmdHis') . rand(1000, 9999),
                'user_id' => $userId,
                'type' => 'farm_package',
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
            
            Logger::audit('BOOK_FARM_PACKAGE', 'farm_activity_bookings', "Booked farm package ID: {$bookingId}", [], $bookingData);
            
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
     * Farm products - List farm products
     */
    public function products() {
        $page = $this->get('page', 1);
        $limit = $this->get('limit', 12);
        $farmId = $this->get('farm_id');
        $productType = $this->get('product_type', 'all');
        $currency = $this->currencyController->getUserCurrency(Session::get('user_id'));
        
        $farmProductModel = $this->model('FarmProduct');
        $products = $farmProductModel->getAvailable($page, $limit, $farmId, $productType);
        
        // Convert prices
        foreach ($products as &$product) {
            if ($product['price_per_kg']) {
                $product['display_price_kg'] = $this->currencyController->format(
                    $this->currencyController->convert($product['price_per_kg'], $product['currency'], $currency),
                    $currency
                );
            }
            if ($product['price_per_unit']) {
                $product['display_price_unit'] = $this->currencyController->format(
                    $this->currencyController->convert($product['price_per_unit'], $product['currency'], $currency),
                    $currency
                );
            }
        }
        
        $data = [
            'title' => 'Produk Pertanian - MyWisata',
            'products' => $products,
            'farm_id' => $farmId,
            'product_type' => $productType,
            'currency' => $currency,
            'csrf_token' => Middleware::csrfToken()
        ];
        
        $this->view('agritourism/products', $data);
    }
}
