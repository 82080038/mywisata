<?php
/**
 * MyWisata Application - WhatsApp Booking Controller
 * 
 * Handles WhatsApp-based booking integration.
 * 
 * @package MyWisata
 * @version 1.0.0
 * @since 2026-07-18
 */

class WhatsAppBookingController extends Controller {
    
    private $currencyController;
    
    /**
     * Constructor
     */
    public function __construct() {
        parent::__construct();
        $this->currencyController = new CurrencyController();
    }
    
    /**
     * Handle incoming WhatsApp webhook
     */
    public function webhook() {
        // Get raw POST data
        $rawData = file_get_contents('php://input');
        $data = json_decode($rawData, true);
        
        if (!$data) {
            http_response_code(400);
            echo 'Invalid data';
            exit;
        }
        
        // Verify webhook signature if configured
        if (defined('WHATSAPP_WEBHOOK_VERIFY_TOKEN') && isset($_GET['hub_verify_token'])) {
            if ($_GET['hub_verify_token'] === WHATSAPP_WEBHOOK_VERIFY_TOKEN) {
                echo $_GET['hub_challenge'];
                exit;
            } else {
                http_response_code(403);
                exit;
            }
        }
        
        // Process message
        if (isset($data['entry'][0]['changes'][0]['value']['messages'])) {
            $message = $data['entry'][0]['changes'][0]['value']['messages'][0];
            $phoneNumber = $message['from'];
            $messageText = $message['text']['body'] ?? '';
            
            $this->processMessage($phoneNumber, $messageText);
        }
        
        http_response_code(200);
        echo 'OK';
        exit;
    }
    
    /**
     * Process incoming WhatsApp message
     */
    private function processMessage($phoneNumber, $messageText) {
        $whatsappSessionModel = $this->model('WhatsAppBookingSession');
        
        // Check if there's an active session
        $session = $whatsappSessionModel->getActiveByPhone($phoneNumber);
        
        if (!$session) {
            // Create new session
            $sessionId = 'WA' . date('YmdHis') . rand(1000, 9999);
            $session = $whatsappSessionModel->create([
                'session_id' => $sessionId,
                'phone_number' => $phoneNumber,
                'session_state' => 'initiated',
                'booking_type' => 'destination',
                'last_message_time' => date('Y-m-d H:i:s'),
                'last_message_content' => $messageText
            ]);
            
            // Send welcome message
            $this->sendWelcomeMessage($phoneNumber);
        } else {
            // Update session
            $whatsappSessionModel->updateLastMessage($session['id'], $messageText);
            
            // Process based on session state
            $this->processSessionState($session, $messageText);
        }
    }
    
    /**
     * Send welcome message
     */
    private function sendWelcomeMessage($phoneNumber) {
        $templateModel = $this->model('WhatsAppMessageTemplate');
        $template = $templateModel->getByTypeAndLanguage('welcome', 'id');
        
        if ($template) {
            $this->sendWhatsAppMessage($phoneNumber, $template['template_content']);
        } else {
            $this->sendWhatsAppMessage($phoneNumber, 'Selamat datang di MyWisata! Ketik "booking" untuk mulai reservasi atau "help" untuk bantuan.');
        }
    }
    
    /**
     * Process session state
     */
    private function processSessionState($session, $messageText) {
        $messageLower = strtolower(trim($messageText));
        
        switch ($session['session_state']) {
            case 'initiated':
                if ($messageLower === 'booking') {
                    $this->updateSessionState($session['id'], 'destination_selection');
                    $this->sendWhatsAppMessage($session['phone_number'], 'Silakan pilih jenis booking: 1. Destinasi, 2. Hotel, 3. Restoran, 4. Tour Guide');
                } elseif ($messageLower === 'help') {
                    $this->sendWhatsAppMessage($session['phone_number'], 'Perintah yang tersedia: booking, cancel, status, help');
                }
                break;
                
            case 'destination_selection':
                $this->handleDestinationSelection($session, $messageText);
                break;
                
            case 'date_selection':
                $this->handleDateSelection($session, $messageText);
                break;
                
            case 'confirmation':
                $this->handleConfirmation($session, $messageText);
                break;
        }
    }
    
    /**
     * Handle destination selection
     */
    private function handleDestinationSelection($session, $messageText) {
        $bookingTypes = [
            '1' => 'destination',
            '2' => 'hotel',
            '3' => 'restaurant',
            '4' => 'tour_guide'
        ];
        
        if (isset($bookingTypes[$messageText])) {
            $whatsappSessionModel = $this->model('WhatsAppBookingSession');
            $whatsappSessionModel->updateBookingType($session['id'], $bookingTypes[$messageText]);
            $whatsappSessionModel->updateState($session['id'], 'date_selection');
            
            $this->sendWhatsAppMessage($session['phone_number'], 'Silakan masukkan tanggal booking (format: YYYY-MM-DD)');
        } else {
            $this->sendWhatsAppMessage($session['phone_number'], 'Pilihan tidak valid. Silakan pilih 1-4');
        }
    }
    
    /**
     * Handle date selection
     */
    private function handleDateSelection($session, $messageText) {
        $date = date('Y-m-d', strtotime($messageText));
        
        if ($date) {
            $whatsappSessionModel = $this->model('WhatsAppBookingSession');
            $whatsappSessionModel->updateTravelDate($session['id'], $date);
            $whatsappSessionModel->updateState($session['id'], 'confirmation');
            
            $this->sendWhatsAppMessage($session['phone_number'], 'Masukkan jumlah peserta:');
        } else {
            $this->sendWhatsAppMessage($session['phone_number'], 'Format tanggal tidak valid. Gunakan format YYYY-MM-DD');
        }
    }
    
    /**
     * Handle confirmation
     */
    private function handleConfirmation($session, $messageText) {
        $numberOfPeople = (int) $messageText;
        
        if ($numberOfPeople > 0) {
            $whatsappSessionModel = $this->model('WhatsAppBookingSession');
            $whatsappSessionModel->updateNumberOfPeople($session['id'], $numberOfPeople);
            
            // Create booking
            $this->createBookingFromSession($session);
        } else {
            $this->sendWhatsAppMessage($session['phone_number'], 'Jumlah peserta tidak valid');
        }
    }
    
    /**
     * Create booking from WhatsApp session
     */
    private function createBookingFromSession($session) {
        $userId = $session['user_id'];
        $currency = $this->currencyController->getUserCurrency($userId);
        
        // Calculate price based on booking type
        $pricePerPerson = $this->getPricePerBookingType($session['booking_type']);
        $totalPrice = $pricePerPerson * $session['number_of_people'];
        
        $bookingData = [
            'booking_code' => 'WA' . date('YmdHis') . rand(1000, 9999),
            'user_id' => $userId,
            'booking_date' => date('Y-m-d'),
            'travel_date' => $session['travel_date'],
            'number_of_people' => $session['number_of_people'],
            'total_price' => $totalPrice,
            'currency' => $currency,
            'status' => 'pending',
            'payment_status' => 'unpaid'
        ];
        
        // Add booking type specific fields
        switch ($session['booking_type']) {
            case 'destination':
                $bookingData['destination_id'] = $session['selected_destination_id'];
                break;
            case 'hotel':
                $bookingData['hotel_id'] = $session['selected_hotel_id'];
                break;
            case 'restaurant':
                $bookingData['restaurant_id'] = $session['selected_restaurant_id'];
                break;
            case 'tour_guide':
                $bookingData['guide_id'] = $session['selected_tour_guide_id'];
                break;
        }
        
        $bookingModel = $this->model('Booking');
        $bookingId = $bookingModel->create($bookingData);
        
        if ($bookingId) {
            // Update session
            $whatsappSessionModel = $this->model('WhatsAppBookingSession');
            $whatsappSessionModel->updateState($session['id'], 'completed');
            $whatsappSessionModel->updateBookingId($session['id'], $bookingId);
            
            // Send confirmation message
            $this->sendBookingConfirmation($session['phone_number'], $bookingData['booking_code'], $totalPrice, $currency);
            
            Logger::audit('WHATSAPP_BOOKING', 'whatsapp_booking_sessions', "Created booking from WhatsApp session ID: {$session['id']}", [], $bookingData);
        }
    }
    
    /**
     * Get price per booking type
     */
    private function getPricePerBookingType($bookingType) {
        $prices = [
            'destination' => 50000,
            'hotel' => 500000,
            'restaurant' => 100000,
            'tour_guide' => 300000
        ];
        
        return $prices[$bookingType] ?? 50000;
    }
    
    /**
     * Send booking confirmation
     */
    private function sendBookingConfirmation($phoneNumber, $bookingCode, $totalPrice, $currency) {
        $formattedPrice = $this->currencyController->format($totalPrice, $currency);
        $message = "Booking berhasil dikonfirmasi!\n\nKode Booking: {$bookingCode}\nTotal: {$formattedPrice}\n\nSilakan lanjutkan pembayaran melalui aplikasi MyWisata.";
        
        $this->sendWhatsAppMessage($phoneNumber, $message);
    }
    
    /**
     * Send WhatsApp message
     */
    private function sendWhatsAppMessage($phoneNumber, $message) {
        // This would integrate with WhatsApp Business API
        // For now, just log the message
        Logger::info('WhatsApp message sent', [
            'phone' => $phoneNumber,
            'message' => $message
        ]);
        
        // TODO: Implement actual WhatsApp API call
        // $this->callWhatsAppAPI($phoneNumber, $message);
    }
    
    /**
     * Update session state
     */
    private function updateSessionState($sessionId, $newState) {
        $whatsappSessionModel = $this->model('WhatsAppBookingSession');
        $whatsappSessionModel->updateState($sessionId, $newState);
    }
    
    /**
     * Get session analytics
     */
    public function analytics() {
        Middleware::requireRole(['admin']);
        
        $page = $this->get('page', 1);
        $limit = $this->get('limit', 20);
        
        $analyticsModel = $this->model('WhatsAppBookingAnalytics');
        $analytics = $analyticsModel->getList($page, $limit);
        
        $data = [
            'title' => 'WhatsApp Booking Analytics - MyWisata',
            'analytics' => $analytics,
            'csrf_token' => Middleware::csrfToken()
        ];
        
        $this->view('whatsapp_booking/analytics', $data);
    }
}
