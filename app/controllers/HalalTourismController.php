<?php
/**
 * MyWisata Application - Halal Tourism Controller
 * 
 * Handles halal tourism packages and prayer room operations.
 * 
 * @package MyWisata
 * @version 1.0.0
 * @since 2026-07-18
 */

// Require CurrencyController if it exists
if (file_exists(APP_ROOT . '/app/controllers/CurrencyController.php')) {
    require_once APP_ROOT . '/app/controllers/CurrencyController.php';
}

class HalalTourismController extends Controller {
    
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
     * Index - List halal packages
     */
    public function index() {
        $page = $this->get('page', 1);
        $limit = $this->get('limit', 12);
        $currency = 'IDR'; // Default currency
        
        if ($this->currencyController) {
            $currency = $this->currencyController->getUserPreferredCurrency(Session::get('user_id'));
        }
        
        $halalPackageModel = $this->model('HalalPackage');
        $packages = $halalPackageModel->getActive($page, $limit);
        
        // Convert prices to user's currency
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
            'title' => 'Paket Wisata Halal - MyWisata',
            'packages' => $packages,
            'currency' => $currency,
            'csrf_token' => Middleware::csrfToken()
        ];
        
        $this->view('halal_tourism/index', $data);
    }
    
    /**
     * Show - View halal package details
     */
    public function show() {
        $slug = $this->get('slug');
        $currency = $this->currencyController->getUserCurrency(Session::get('user_id'));
        
        $halalPackageModel = $this->model('HalalPackage');
        $package = $halalPackageModel->findBySlug($slug);
        
        if (!$package) {
            Session::flash('error', 'Paket tidak ditemukan');
            $this->redirect('halal-tourism');
        }
        
        // Get package itinerary
        $itineraryModel = $this->model('HalalPackageItinerary');
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
        
        $this->view('halal_tourism/show', $data);
    }
    
    /**
     * Book halal package
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
        $travelDate = $this->post('travel_date');
        $numberOfTravelers = $this->post('number_of_travelers');
        $specialRequests = $this->post('special_requests');
        $dietaryRequirements = $this->post('dietary_requirements');
        $genderPreference = $this->post('gender_preference', 'mixed');
        
        $halalPackageModel = $this->model('HalalPackage');
        $package = $halalPackageModel->findById($packageId);
        
        if (!$package) {
            $this->json(['status' => 'error', 'message' => 'Paket tidak ditemukan'], 404);
        }
        
        // Calculate total price
        $totalPrice = $package['price_per_person'] * $numberOfTravelers;
        $basePrice = $currency === 'IDR' ? $totalPrice : $this->currencyController->convert($totalPrice, $currency, 'IDR');
        $exchangeRate = $currency === 'IDR' ? 1.0 : $this->currencyController->getExchangeRate($currency, 'IDR');
        
        $bookingData = [
            'package_id' => $packageId,
            'user_id' => $userId,
            'booking_date' => date('Y-m-d'),
            'travel_date' => $travelDate,
            'number_of_travelers' => $numberOfTravelers,
            'total_price' => $totalPrice,
            'currency' => $currency,
            'special_requests' => $specialRequests,
            'dietary_requirements' => json_encode($dietaryRequirements),
            'gender_preference' => $genderPreference,
            'contact_person_name' => $this->post('contact_person_name'),
            'contact_person_phone' => $this->post('contact_person_phone'),
            'contact_person_email' => $this->post('contact_person_email'),
            'status' => 'pending',
            'payment_status' => 'unpaid'
        ];
        
        $halalBookingModel = $this->model('HalalPackageBooking');
        $bookingId = $halalBookingModel->create($bookingData);
        
        if ($bookingId) {
            // Create transaction
            $transactionModel = $this->model('Transaction');
            $transactionData = [
                'transaction_code' => 'HT' . date('YmdHis') . rand(1000, 9999),
                'user_id' => $userId,
                'type' => 'halal_package',
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
            
            Logger::audit('BOOK_HALAL_PACKAGE', 'halal_package_bookings', "Booked halal package ID: {$bookingId}", [], $bookingData);
            
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
     * Prayer rooms - List nearby prayer rooms
     */
    public function prayerRooms() {
        $lat = $this->get('lat');
        $lng = $this->get('lng');
        $radius = $this->get('radius', 5); // km
        
        $prayerRoomModel = $this->model('PrayerRoom');
        
        if ($lat && $lng) {
            $prayerRooms = $prayerRoomModel->getNearby($lat, $lng, $radius);
        } else {
            $prayerRooms = $prayerRoomModel->getAll();
        }
        
        $data = [
            'title' => 'Musholla Terdekat - MyWisata',
            'prayer_rooms' => $prayerRooms,
            'csrf_token' => Middleware::csrfToken()
        ];
        
        $this->view('halal_tourism/prayer_rooms', $data);
    }
    
    /**
     * Get prayer times
     */
    public function prayerTimes() {
        if (!$this->isAjax()) {
            $this->redirect('home');
        }
        
        $lat = $this->get('lat');
        $lng = $this->get('lng');
        $date = $this->get('date', date('Y-m-d'));
        
        if (!$lat || !$lng) {
            $this->json(['status' => 'error', 'message' => 'Latitude dan longitude diperlukan'], 400);
        }
        
        $prayerTimesModel = $this->model('PrayerTimesCache');
        $prayerTimes = $prayerTimesModel->getByLocationAndDate($lat, $lng, $date);
        
        if (!$prayerTimes) {
            // Fetch from API
            $prayerTimes = $this->fetchPrayerTimesFromAPI($lat, $lng, $date);
            
            if ($prayerTimes) {
                // Cache the result
                $prayerTimesModel->create([
                    'location_lat' => $lat,
                    'location_lng' => $lng,
                    'city_name' => 'Unknown',
                    'country_code' => 'ID',
                    'date' => $date,
                    'fajr' => $prayerTimes['fajr'],
                    'dhuhr' => $prayerTimes['dhuhr'],
                    'asr' => $prayerTimes['asr'],
                    'maghrib' => $prayerTimes['maghrib'],
                    'isha' => $prayerTimes['isha']
                ]);
            }
        }
        
        if ($prayerTimes) {
            $this->json([
                'status' => 'success',
                'data' => $prayerTimes
            ]);
        } else {
            $this->json(['status' => 'error', 'message' => 'Gagal mendapatkan jadwal sholat'], 500);
        }
    }
    
    /**
     * Fetch prayer times from API
     */
    private function fetchPrayerTimesFromAPI($lat, $lng, $date) {
        // Using Aladhan API
        $url = "http://api.aladhan.com/v1/timings/{$date}?latitude={$lat}&longitude={$lng}&method=20";
        
        $response = @file_get_contents($url);
        if ($response) {
            $data = json_decode($response, true);
            if (isset($data['data']['timings'])) {
                return [
                    'fajr' => $data['data']['timings']['Fajr'],
                    'dhuhr' => $data['data']['timings']['Dhuhr'],
                    'asr' => $data['data']['timings']['Asr'],
                    'maghrib' => $data['data']['timings']['Maghrib'],
                    'isha' => $data['data']['timings']['Isha']
                ];
            }
        }
        
        return null;
    }
}
