<?php
/**
 * MyWisata Application - Split Payment Controller
 * 
 * Handles split payment functionality for group bookings.
 * 
 * @package MyWisata
 * @version 1.0.0
 * @since 2026-07-18
 */

// Require CurrencyController if it exists
if (file_exists(APP_ROOT . '/app/controllers/CurrencyController.php')) {
    require_once APP_ROOT . '/app/controllers/CurrencyController.php';
}

class SplitPaymentController extends Controller {
    
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
     * Index - Show split payment dashboard
     */
    public function index() {
        Middleware::requireAuth();
        
        $data = [
            'title' => 'Split Payment - MyWisata',
            'csrf_token' => Middleware::csrfToken()
        ];
        
        $this->view('split_payment/index', $data);
    }
    
    /**
     * Create split payment group
     */
    public function createGroup() {
        if (!$this->isAjax()) {
            $this->redirect('home');
        }
        
        if (!$this->validateCsrf()) {
            $this->json(['status' => 'error', 'message' => 'CSRF token mismatch'], 419);
        }
        
        $userId = Session::get('user_id');
        $bookingId = $this->post('booking_id');
        $currency = $this->currencyController->getUserCurrency($userId);
        
        $bookingModel = $this->model('Booking');
        $booking = $bookingModel->findById($bookingId);
        
        if (!$booking || $booking['user_id'] != $userId) {
            $this->json(['status' => 'error', 'message' => 'Unauthorized'], 403);
        }
        
        $totalAmount = $booking['total_amount'];
        $groupCode = 'SP' . date('YmdHis') . rand(1000, 9999);
        
        $splitPaymentGroupModel = $this->model('SplitPaymentGroup');
        $groupId = $splitPaymentGroupModel->create([
            'booking_id' => $bookingId,
            'group_code' => $groupCode,
            'total_amount' => $totalAmount,
            'currency' => $currency,
            'amount_paid' => 0,
            'amount_remaining' => $totalAmount,
            'created_by_user_id' => $userId,
            'status' => 'active'
        ]);
        
        if ($groupId) {
            // Update booking with split payment info
            $bookingModel->updateSplitPaymentInfo($bookingId, true, $groupId);
            
            Logger::audit('CREATE_SPLIT_PAYMENT_GROUP', 'split_payment_groups', "Created split payment group ID: {$groupId}", [], [
                'booking_id' => $bookingId,
                'total_amount' => $totalAmount
            ]);
            
            $this->json([
                'status' => 'success',
                'message' => 'Split payment group berhasil dibuat',
                'group_id' => $groupId,
                'group_code' => $groupCode
            ]);
        } else {
            $this->json(['status' => 'error', 'message' => 'Gagal membuat split payment group'], 500);
        }
    }
    
    /**
     * Add participant to split payment group
     */
    public function addParticipant() {
        if (!$this->isAjax()) {
            $this->redirect('home');
        }
        
        if (!$this->validateCsrf()) {
            $this->json(['status' => 'error', 'message' => 'CSRF token mismatch'], 419);
        }
        
        $userId = Session::get('user_id');
        $groupId = $this->post('group_id');
        $participantName = $this->post('participant_name');
        $participantEmail = $this->post('participant_email');
        $participantPhone = $this->post('participant_phone');
        $shareAmount = $this->post('share_amount');
        $inviteMethod = $this->post('invite_method', 'email');
        
        $splitPaymentGroupModel = $this->model('SplitPaymentGroup');
        $group = $splitPaymentGroupModel->findById($groupId);
        
        if (!$group) {
            $this->json(['status' => 'error', 'message' => 'Group tidak ditemukan'], 404);
        }
        
        if ($group['created_by_user_id'] != $userId) {
            $this->json(['status' => 'error', 'message' => 'Unauthorized'], 403);
        }
        
        if ($group['status'] !== 'active') {
            $this->json(['status' => 'error', 'message' => 'Group tidak aktif'], 400);
        }
        
        $inviteToken = 'INV' . date('YmdHis') . rand(1000, 9999);
        
        $participantModel = $this->model('SplitPaymentParticipant');
        $participantId = $participantModel->create([
            'split_payment_group_id' => $groupId,
            'user_id' => null, // For non-registered users
            'participant_name' => $participantName,
            'participant_email' => $participantEmail,
            'participant_phone' => $participantPhone,
            'share_amount' => $shareAmount,
            'amount_paid' => 0,
            'amount_remaining' => $shareAmount,
            'payment_status' => 'pending',
            'invite_sent' => false,
            'invite_method' => $inviteMethod,
            'invite_token' => $inviteToken
        ]);
        
        if ($participantId) {
            // Send invite
            $this->sendInvite($participantId, $inviteMethod);
            
            Logger::audit('ADD_SPLIT_PAYMENT_PARTICIPANT', 'split_payment_participants', "Added participant ID: {$participantId} to group ID: {$groupId}", [], [
                'participant_name' => $participantName,
                'share_amount' => $shareAmount
            ]);
            
            $this->json([
                'status' => 'success',
                'message' => 'Peserta berhasil ditambahkan',
                'participant_id' => $participantId
            ]);
        } else {
            $this->json(['status' => 'error', 'message' => 'Gagal menambahkan peserta'], 500);
        }
    }
    
    /**
     * Send invite to participant
     */
    private function sendInvite($participantId, $method) {
        $participantModel = $this->model('SplitPaymentParticipant');
        $participant = $participantModel->findById($participantId);
        
        $splitPaymentGroupModel = $this->model('SplitPaymentGroup');
        $group = $splitPaymentGroupModel->findById($participant['split_payment_group_id']);
        
        $inviteLink = BASE_URL . 'split-payment/join/' . $participant['invite_token'];
        
        switch ($method) {
            case 'email':
                // Send email invite
                $this->sendEmailInvite($participant['participant_email'], $inviteLink, $group['group_code'], $participant['share_amount']);
                break;
            case 'whatsapp':
                // Send WhatsApp invite
                $this->sendWhatsAppInvite($participant['participant_phone'], $inviteLink, $group['group_code'], $participant['share_amount']);
                break;
            case 'sms':
                // Send SMS invite
                $this->sendSMSInvite($participant['participant_phone'], $inviteLink, $group['group_code'], $participant['share_amount']);
                break;
        }
        
        // Mark invite as sent
        $participantModel->markInviteSent($participantId);
    }
    
    /**
     * Send email invite
     */
    private function sendEmailInvite($email, $inviteLink, $groupCode, $shareAmount) {
        // TODO: Implement email sending
        Logger::info('Email invite sent', [
            'email' => $email,
            'invite_link' => $inviteLink,
            'group_code' => $groupCode,
            'share_amount' => $shareAmount
        ]);
    }
    
    /**
     * Send WhatsApp invite
     */
    private function sendWhatsAppInvite($phone, $inviteLink, $groupCode, $shareAmount) {
        // TODO: Implement WhatsApp sending
        Logger::info('WhatsApp invite sent', [
            'phone' => $phone,
            'invite_link' => $inviteLink,
            'group_code' => $groupCode,
            'share_amount' => $shareAmount
        ]);
    }
    
    /**
     * Send SMS invite
     */
    private function sendSMSInvite($phone, $inviteLink, $groupCode, $shareAmount) {
        // TODO: Implement SMS sending
        Logger::info('SMS invite sent', [
            'phone' => $phone,
            'invite_link' => $inviteLink,
            'group_code' => $groupCode,
            'share_amount' => $shareAmount
        ]);
    }
    
    /**
     * Join split payment group
     */
    public function joinGroup() {
        $inviteToken = $this->get('token');
        
        if (empty($inviteToken)) {
            Session::flash('error', 'Token tidak valid');
            $this->redirect('home');
        }
        
        $participantModel = $this->model('SplitPaymentParticipant');
        $participant = $participantModel->getByInviteToken($inviteToken);
        
        if (!$participant) {
            Session::flash('error', 'Invite tidak valid atau sudah kadaluarsa');
            $this->redirect('home');
        }
        
        $splitPaymentGroupModel = $this->model('SplitPaymentGroup');
        $group = $splitPaymentGroupModel->findById($participant['split_payment_group_id']);
        
        if ($group['status'] !== 'active') {
            Session::flash('error', 'Group tidak aktif');
            $this->redirect('home');
        }
        
        $data = [
            'title' => 'Join Split Payment - MyWisata',
            'participant' => $participant,
            'group' => $group,
            'csrf_token' => Middleware::csrfToken()
        ];
        
        $this->view('split_payment/join', $data);
    }
    
    /**
     * Process participant payment
     */
    public function processPayment() {
        if (!$this->isAjax()) {
            $this->redirect('home');
        }
        
        if (!$this->validateCsrf()) {
            $this->json(['status' => 'error', 'message' => 'CSRF token mismatch'], 419);
        }
        
        $participantId = $this->post('participant_id');
        $paymentMethod = $this->post('payment_method');
        $amount = $this->post('amount');
        
        $participantModel = $this->model('SplitPaymentParticipant');
        $participant = $participantModel->findById($participantId);
        
        if (!$participant) {
            $this->json(['status' => 'error', 'message' => 'Peserta tidak ditemukan'], 404);
        }
        
        if ($amount > $participant['amount_remaining']) {
            $this->json(['status' => 'error', 'message' => 'Jumlah pembayaran melebihi sisa'], 400);
        }
        
        // Create payment transaction
        $transactionModel = $this->model('Transaction');
        $transactionData = [
            'transaction_code' => 'SP' . date('YmdHis') . rand(1000, 9999),
            'user_id' => $participant['user_id'],
            'type' => 'split_payment',
            'reference_id' => $participantId,
            'gross_amount' => $amount,
            'discount_amount' => 0,
            'tax_amount' => 0,
            'net_amount' => $amount,
            'currency' => $participantModel->getGroupCurrency($participant['split_payment_group_id']),
            'payment_method' => $payment_method
        ];
        $transactionId = $transactionModel->create($transactionData);
        
        if ($transactionId) {
            // Record split payment transaction
            $splitPaymentTransactionModel = $this->model('SplitPaymentTransaction');
            $splitPaymentTransactionModel->create([
                'split_payment_group_id' => $participant['split_payment_group_id'],
                'participant_id' => $participantId,
                'payment_transaction_id' => $transactionId,
                'amount' => $amount,
                'currency' => $transactionData['currency'],
                'payment_method' => $paymentMethod
            ]);
            
            // Update participant
            $newAmountPaid = $participant['amount_paid'] + $amount;
            $newAmountRemaining = $participant['amount_remaining'] - $amount;
            $newPaymentStatus = $newAmountRemaining <= 0 ? 'paid' : 'partial';
            
            $participantModel->updatePayment($participantId, $newAmountPaid, $newAmountRemaining, $newPaymentStatus);
            
            // Update group
            $splitPaymentGroupModel = $this->model('SplitPaymentGroup');
            $group = $splitPaymentGroupModel->findById($participant['split_payment_group_id']);
            $newGroupPaid = $group['amount_paid'] + $amount;
            $newGroupRemaining = $group['amount_remaining'] - $amount;
            $newGroupStatus = $newGroupRemaining <= 0 ? 'completed' : 'active';
            
            $splitPaymentGroupModel->updatePayment($group['id'], $newGroupPaid, $newGroupRemaining, $newGroupStatus);
            
            Logger::audit('SPLIT_PAYMENT_PROCESS', 'split_payment_transactions', "Processed payment for participant ID: {$participantId}", [], [
                'amount' => $amount,
                'payment_method' => $paymentMethod
            ]);
            
            $this->json([
                'status' => 'success',
                'message' => 'Pembayaran berhasil diproses',
                'amount_paid' => $amount,
                'amount_remaining' => $newAmountRemaining
            ]);
        } else {
            $this->json(['status' => 'error', 'message' => 'Gagal memproses pembayaran'], 500);
        }
    }
    
    /**
     * Get group status
     */
    public function getGroupStatus() {
        if (!$this->isAjax()) {
            $this->redirect('home');
        }
        
        $groupId = $this->get('group_id');
        
        $splitPaymentGroupModel = $this->model('SplitPaymentGroup');
        $group = $splitPaymentGroupModel->findById($groupId);
        
        if (!$group) {
            $this->json(['status' => 'error', 'message' => 'Group tidak ditemukan'], 404);
        }
        
        $participantModel = $this->model('SplitPaymentParticipant');
        $participants = $participantModel->getByGroupId($groupId);
        
        $this->json([
            'status' => 'success',
            'data' => [
                'group' => $group,
                'participants' => $participants
            ]
        ]);
    }
}
