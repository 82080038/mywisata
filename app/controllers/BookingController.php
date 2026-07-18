<?php
/**
 * MyWisata Application - Booking Controller
 * 
 * Handles tour guide booking operations.
 * 
 * @package MyWisata
 * @version 1.0.0
 * @since 2026-07-01
 */

class BookingController extends Controller {
    
    private $bookingModel;
    private $currencyController;
    
    /**
     * Constructor - Require wisatawan role
     */
    public function __construct() {
        parent::__construct();
        Middleware::requireRole('wisatawan');
        $this->bookingModel = $this->model('Booking');
        $this->currencyController = new CurrencyController();
    }
    
    /**
     * Index - List user bookings
     */
    public function index() {
        $userId = Session::get('user_id');
        
        $status = $this->get('status', 'all');
        $page = $this->get('page', 1);
        $limit = $this->get('limit', 10);
        
        $bookings = $this->bookingModel->getByUserId($userId, $status === 'all' ? null : $status, $page, $limit);
        $total = $this->bookingModel->countByUserId($userId, $status === 'all' ? null : $status);
        
        $data = [
            'title' => 'Booking Saya - MyWisata',
            'bookings' => $bookings,
            'total' => $total,
            'status_filter' => $status,
            'page' => $page,
            'limit' => $limit,
            'csrf_token' => Middleware::csrfToken()
        ];
        
        $this->view('bookings/index', $data);
    }
    
    /**
     * Create - Show booking form
     */
    public function create() {
        $guideId = $this->get('guide_id');
        $tourGuideModel = $this->model('TourGuide');
        $guide = $tourGuideModel->findById($guideId);
        
        if (!$guide) {
            Session::flash('error', 'Tour guide tidak ditemukan');
            $this->redirect('home');
        }
        
        $data = [
            'title' => 'Booking Tour Guide - MyWisata',
            'guide' => $guide,
            'csrf_token' => Middleware::csrfToken()
        ];
        
        $this->view('bookings/create', $data);
    }
    
    /**
     * Store - Create new booking
     */
    public function store() {
        try {
            if (!$this->isAjax()) {
                $this->redirect('home');
            }
            
            // Verify CSRF
            if (!$this->validateCsrf()) {
                $this->json(['status' => 'error', 'message' => 'CSRF token mismatch'], 419);
            }
            
            $userId = Session::get('user_id');
            
            // Get user's preferred currency
            $currency = $this->currencyController->getUserCurrency($userId);
            $totalAmount = $this->post('total_amount');
            
            // Convert to base currency (IDR) if needed
            $baseAmount = $currency === 'IDR' ? $totalAmount : $this->currencyController->convert($totalAmount, $currency, 'IDR');
            $exchangeRate = $currency === 'IDR' ? 1.0 : $this->currencyController->getExchangeRate($currency, 'IDR');
            
            $data = [
                'booking_code' => 'BK' . date('YmdHis') . rand(1000, 9999),
                'user_id' => $userId,
                'guide_id' => $this->post('guide_id'),
                'booking_date' => $this->post('booking_date'),
                'booking_time' => $this->post('booking_time'),
                'duration_hours' => $this->post('duration_hours'),
                'participants' => $this->post('participants'),
                'special_requests' => $this->post('special_requests'),
                'total_amount' => $totalAmount,
                'currency' => $currency,
                'original_amount' => $totalAmount,
                'base_amount' => $baseAmount,
                'exchange_rate' => $exchangeRate,
                'exchange_rate_date' => date('Y-m-d H:i:s')
            ];
            
            $validator = new Validator($_POST);
            $validator->required(['guide_id', 'booking_date', 'booking_time', 'duration_hours', 'participants'])
                      ->numeric(['duration_hours', 'participants', 'total_amount'])
                      ->date('booking_date');
            
            if ($validator->fails()) {
                $this->json(['status' => 'error', 'message' => $validator->firstError()], 400);
            }
            
            // Check availability
            $tourGuideModel = $this->model('TourGuide');
            $isAvailable = $tourGuideModel->checkAvailability(
                $data['guide_id'], 
                $data['booking_date'], 
                $data['booking_time'], 
                date('H:i:s', strtotime($data['booking_time']) + ($data['duration_hours'] * 3600))
            );
            
            if (!$isAvailable) {
                $this->json(['status' => 'error', 'message' => 'Tour guide tidak tersedia pada waktu tersebut'], 400);
            }
            
            $bookingId = $this->bookingModel->create($data);
            
            if ($bookingId) {
                // Reserve availability
                $tourGuideModel->reserveAvailability($data['guide_id'], $data['booking_date'], $data['booking_time']);
                
                // Create transaction
                $transactionModel = $this->model('Transaction');
                $transactionData = [
                    'transaction_code' => 'TX' . date('YmdHis') . rand(1000, 9999),
                    'user_id' => $userId,
                    'booking_id' => $bookingId,
                    'type' => 'booking_guide',
                    'gross_amount' => $data['total_amount'],
                    'discount_amount' => 0,
                    'tax_amount' => 0,
                    'net_amount' => $data['total_amount'],
                    'currency' => $currency,
                    'original_amount' => $data['total_amount'],
                    'base_amount' => $baseAmount,
                    'exchange_rate' => $exchangeRate,
                    'exchange_rate_date' => date('Y-m-d H:i:s'),
                    'payment_method' => 'pending'
                ];
                $transactionModel->create($transactionData);
                
                // Send notification to guide
                $notificationModel = $this->model('Notification');
                $guide = $tourGuideModel->findById($data['guide_id']);
                $notificationModel->notify(
                    $guide['user_id'],
                    'new_booking',
                    'Booking Baru',
                    'Anda mendapat booking baru. Silakan cek dashboard Anda.',
                    'tourguide/bookings'
                );
                
                Logger::audit('CREATE_BOOKING', 'bookings', "Created booking ID: {$bookingId}", [], $data);
                
                $this->json([
                    'status' => 'success',
                    'message' => 'Booking berhasil dibuat. Silakan lanjutkan pembayaran.',
                    'booking_id' => $bookingId,
                    'booking_code' => $data['booking_code']
                ]);
            } else {
                $this->json(['status' => 'error', 'message' => 'Gagal membuat booking'], 500);
            }
        } catch (Exception $e) {
            Logger::error('Booking store error', ['error' => $e->getMessage()]);
            $this->json(['status' => 'error', 'message' => 'Terjadi kesalahan saat membuat booking'], 500);
        }
    }
    
    /**
     * Show booking details
     */
    public function show() {
        $bookingId = $this->get('id');
        $userId = Session::get('user_id');
        
        $booking = $this->bookingModel->findById($bookingId);
        
        if (!$booking || $booking['user_id'] != $userId) {
            Session::flash('error', 'Booking tidak ditemukan');
            $this->redirect('bookings');
        }
        
        $tourGuideModel = $this->model('TourGuide');
        $guide = $tourGuideModel->findById($booking['guide_id']);
        
        $transactionModel = $this->model('Transaction');
        $transaction = $transactionModel->findByBookingId($bookingId);
        
        $data = [
            'title' => 'Detail Booking - MyWisata',
            'booking' => $booking,
            'guide' => $guide,
            'transaction' => $transaction,
            'csrf_token' => Middleware::csrfToken()
        ];
        
        $this->view('bookings/show', $data);
    }
    
    /**
     * Cancel booking
     */
    public function cancel() {
        if (!$this->isAjax()) {
            $this->redirect('home');
        }
        
        // Verify CSRF
        if (!$this->validateCsrf()) {
            $this->json(['status' => 'error', 'message' => 'CSRF token mismatch'], 419);
        }
        
        $bookingId = $this->post('booking_id');
        $reason = $this->post('reason');
        $userId = Session::get('user_id');
        
        $booking = $this->bookingModel->findById($bookingId);
        
        if (!$booking || $booking['user_id'] != $userId) {
            $this->json(['status' => 'error', 'message' => 'Unauthorized'], 403);
        }
        
        // Check if booking can be cancelled
        if (!in_array($booking['status'], ['pending', 'confirmed'])) {
            $this->json(['status' => 'error', 'message' => 'Booking tidak dapat dibatalkan'], 400);
        }
        
        $cancelled = $this->bookingModel->cancel($bookingId, $reason);
        
        if ($cancelled) {
            // Release availability
            $tourGuideModel = $this->model('TourGuide');
            $tourGuideModel->releaseAvailability($booking['guide_id'], $booking['booking_date'], $booking['booking_time']);
            
            // Send notification to guide
            $notificationModel = $this->model('Notification');
            $guide = $tourGuideModel->findById($booking['guide_id']);
            $notificationModel->notify(
                $guide['user_id'],
                'booking_cancelled',
                'Booking Dibatalkan',
                'Booking ' . $booking['booking_code'] . ' telah dibatalkan oleh wisatawan.',
                'tourguide/bookings'
            );
            
            Logger::audit('CANCEL_BOOKING', 'bookings', "Cancelled booking ID: {$bookingId}", [], ['reason' => $reason]);
            
            $this->json(['status' => 'success', 'message' => 'Booking berhasil dibatalkan']);
        } else {
            $this->json(['status' => 'error', 'message' => 'Gagal membatalkan booking'], 500);
        }
    }
    
    /**
     * Reschedule booking
     */
    public function reschedule() {
        if (!$this->isAjax()) {
            $this->redirect('home');
        }
        
        // Verify CSRF
        if (!$this->validateCsrf()) {
            $this->json(['status' => 'error', 'message' => 'CSRF token mismatch'], 419);
        }
        
        $bookingId = $this->post('booking_id');
        $newDate = $this->post('new_date');
        $newTime = $this->post('new_time');
        $userId = Session::get('user_id');
        
        $booking = $this->bookingModel->findById($bookingId);
        
        if (!$booking || $booking['user_id'] != $userId) {
            $this->json(['status' => 'error', 'message' => 'Unauthorized'], 403);
        }
        
        // Check if booking can be rescheduled
        if (!in_array($booking['status'], ['pending', 'confirmed'])) {
            $this->json(['status' => 'error', 'message' => 'Booking tidak dapat dijadwalkan ulang'], 400);
        }
        
        // Check new availability
        $tourGuideModel = $this->model('TourGuide');
        $isAvailable = $tourGuideModel->checkAvailability(
            $booking['guide_id'], 
            $newDate, 
            $newTime, 
            date('H:i:s', strtotime($newTime) + ($booking['duration_hours'] * 3600))
        );
        
        if (!$isAvailable) {
            $this->json(['status' => 'error', 'message' => 'Tour guide tidak tersedia pada waktu baru'], 400);
        }
        
        $rescheduled = $this->bookingModel->reschedule($bookingId, $newDate, $newTime);
        
        if ($rescheduled) {
            // Release old availability and reserve new
            $tourGuideModel->releaseAvailability($booking['guide_id'], $booking['booking_date'], $booking['booking_time']);
            $tourGuideModel->reserveAvailability($booking['guide_id'], $newDate, $newTime);
            
            // Send notification to guide
            $notificationModel = $this->model('Notification');
            $guide = $tourGuideModel->findById($booking['guide_id']);
            $notificationModel->notify(
                $guide['user_id'],
                'booking_rescheduled',
                'Booking Dijadwalkan Ulang',
                'Booking ' . $booking['booking_code'] . ' telah dijadwalkan ulang.',
                'tourguide/bookings'
            );
            
            Logger::audit('RESCHEDULE_BOOKING', 'bookings', "Rescheduled booking ID: {$bookingId}", [], [
                'old_date' => $booking['booking_date'],
                'new_date' => $newDate
            ]);
            
            $this->json(['status' => 'success', 'message' => 'Booking berhasil dijadwalkan ulang']);
        } else {
            $this->json(['status' => 'error', 'message' => 'Gagal menjadwalkan ulang booking'], 500);
        }
    }
    
    /**
     * Add review to booking
     */
    public function addReview() {
        if (!$this->isAjax()) {
            $this->redirect('home');
        }
        
        // Verify CSRF
        if (!$this->validateCsrf()) {
            $this->json(['status' => 'error', 'message' => 'CSRF token mismatch'], 419);
        }
        
        $bookingId = $this->post('booking_id');
        $rating = $this->post('rating');
        $comment = $this->post('comment');
        $userId = Session::get('user_id');
        
        $booking = $this->bookingModel->findById($bookingId);
        
        if (!$booking || $booking['user_id'] != $userId) {
            $this->json(['status' => 'error', 'message' => 'Unauthorized'], 403);
        }
        
        // Check if booking is completed
        if ($booking['status'] !== 'completed') {
            $this->json(['status' => 'error', 'message' => 'Hanya booking yang sudah selesai yang bisa direview'], 400);
        }
        
        // Check if already reviewed
        if ($booking['review_id']) {
            $this->json(['status' => 'error', 'message' => 'Booking sudah direview'], 400);
        }
        
        $reviewModel = $this->model('Review');
        $reviewId = $reviewModel->create([
            'user_id' => $userId,
            'reviewable_type' => 'tour_guide',
            'reviewable_id' => $booking['guide_id'],
            'rating' => $rating,
            'comment' => $comment
        ]);
        
        if ($reviewId) {
            // Update booking with review ID
            $this->bookingModel->updateReviewId($bookingId, $reviewId);
            
            Logger::audit('ADD_BOOKING_REVIEW', 'bookings', "Added review to booking ID: {$bookingId}", [], [
                'rating' => $rating
            ]);
            
            $this->json(['status' => 'success', 'message' => 'Review berhasil ditambahkan']);
        } else {
            $this->json(['status' => 'error', 'message' => 'Gagal menambahkan review'], 500);
        }
    }
    
    /**
     * Get booking statistics
     */
    public function getStatistics() {
        if (!$this->isAjax()) {
            $this->redirect('home');
        }
        
        $userId = Session::get('user_id');
        $stats = $this->bookingModel->getUserStatistics($userId);
        
        $this->json([
            'status' => 'success',
            'data' => $stats
        ]);
    }
}
