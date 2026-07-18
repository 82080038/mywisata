<?php
/**
 * MyWisata Application - Green Credits Controller
 * 
 * Handles green credits system for sustainability.
 * 
 * @package MyWisata
 * @version 1.0.0
 * @since 2026-07-18
 */

// Require CurrencyController if it exists
if (file_exists(APP_ROOT . '/app/controllers/CurrencyController.php')) {
    require_once APP_ROOT . '/app/controllers/CurrencyController.php';
}

class GreenCreditsController extends Controller {
    
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
     * Index - View user's green credits
     */
    public function index() {
        Middleware::requireAuth();
        
        $userId = Session::get('user_id');
        
        $greenCreditsModel = $this->model('GreenCredit');
        $credits = $greenCreditsModel->getByUserId($userId);
        
        $transactionModel = $this->model('GreenCreditTransaction');
        $transactions = $transactionModel->getByUserId($userId, 1, 20);
        
        $rewardModel = $this->model('GreenCreditReward');
        $rewards = $rewardModel->getActive();
        
        $data = [
            'title' => 'Green Credits - MyWisata',
            'credits' => $credits,
            'transactions' => $transactions,
            'rewards' => $rewards,
            'csrf_token' => Middleware::csrfToken()
        ];
        
        $this->view('green_credits/index', $data);
    }
    
    /**
     * Award credits for eco-friendly booking
     */
    public function awardCredits() {
        if (!$this->isAjax()) {
            $this->redirect('home');
        }
        
        $userId = Session::get('user_id');
        $bookingId = $this->post('booking_id');
        $carbonOffsetKg = $this->post('carbon_offset_kg', 0);
        
        $greenCreditsModel = $this->model('GreenCredit');
        $credits = $greenCreditsModel->getByUserId($userId);
        
        // Calculate credits to award (1 credit per kg CO2 offset)
        $creditsToAward = (int) $carbonOffsetKg;
        
        if ($creditsToAward > 0) {
            // Update credits balance
            $greenCreditsModel->addCredits($userId, $creditsToAward);
            
            // Log transaction
            $transactionModel = $this->model('GreenCreditTransaction');
            $transactionModel->create([
                'user_id' => $userId,
                'transaction_type' => 'earned',
                'amount' => $creditsToAward,
                'reason' => 'Eco-friendly booking',
                'related_booking_id' => $bookingId,
                'carbon_offset_kg' => $carbonOffsetKg
            ]);
            
            // Update tier if needed
            $this->updateTier($userId);
            
            Logger::audit('AWARD_GREEN_CREDITS', 'green_credits', "Awarded {$creditsToAward} credits to user ID: {$userId}", [], [
                'booking_id' => $bookingId,
                'carbon_offset_kg' => $carbonOffsetKg
            ]);
            
            $this->json([
                'status' => 'success',
                'message' => "Anda mendapatkan {$creditsToAward} green credits!",
                'credits_awarded' => $creditsToAward
            ]);
        } else {
            $this->json(['status' => 'error', 'message' => 'Tidak ada credits yang diberikan'], 400);
        }
    }
    
    /**
     * Claim reward
     */
    public function claimReward() {
        if (!$this->isAjax()) {
            $this->redirect('home');
        }
        
        if (!$this->validateCsrf()) {
            $this->json(['status' => 'error', 'message' => 'CSRF token mismatch'], 419);
        }
        
        $userId = Session::get('user_id');
        $rewardId = $this->post('reward_id');
        
        $greenCreditsModel = $this->model('GreenCredit');
        $credits = $greenCreditsModel->getByUserId($userId);
        
        $rewardModel = $this->model('GreenCreditReward');
        $reward = $rewardModel->findById($rewardId);
        
        if (!$reward) {
            $this->json(['status' => 'error', 'message' => 'Reward tidak ditemukan'], 404);
        }
        
        if (!$reward['is_active']) {
            $this->json(['status' => 'error', 'message' => 'Reward tidak tersedia'], 400);
        }
        
        if ($credits['credits_balance'] < $reward['credits_required']) {
            $this->json(['status' => 'error', 'message' => 'Credits tidak cukup'], 400);
        }
        
        // Check if limited and available
        if ($reward['is_limited'] && $reward['total_claimed'] >= $reward['total_available']) {
            $this->json(['status' => 'error', 'message' => 'Reward sudah habis'], 400);
        }
        
        // Deduct credits
        $greenCreditsModel->deductCredits($userId, $reward['credits_required']);
        
        // Log transaction
        $transactionModel = $this->model('GreenCreditTransaction');
        $transactionModel->create([
            'user_id' => $userId,
            'transaction_type' => 'spent',
            'amount' => $reward['credits_required'],
            'reason' => 'Claimed reward: ' . $reward['reward_name']
        ]);
        
        // Create claim record
        $claimModel = $this->model('GreenCreditClaim');
        $claimModel->create([
            'user_id' => $userId,
            'reward_id' => $rewardId,
            'credits_spent' => $reward['credits_required'],
            'claim_date' => date('Y-m-d'),
            'status' => 'approved'
        ]);
        
        // Update reward claimed count
        $rewardModel->incrementClaimed($rewardId);
        
        // Update tier if needed
        $this->updateTier($userId);
        
        Logger::audit('CLAIM_GREEN_CREDIT_REWARD', 'green_credit_claims', "Claimed reward ID: {$rewardId} for user ID: {$userId}", [], [
            'credits_spent' => $reward['credits_required']
        ]);
        
        $this->json([
            'status' => 'success',
            'message' => 'Reward berhasil diklaim!',
            'credits_remaining' => $credits['credits_balance'] - $reward['credits_required']
        ]);
    }
    
    /**
     * Update user tier based on credits
     */
    private function updateTier($userId) {
        $greenCreditsModel = $this->model('GreenCredit');
        $credits = $greenCreditsModel->getByUserId($userId);
        
        $totalCredits = $credits['credits_earned'];
        $newTier = 'bronze';
        
        if ($totalCredits >= 1000) {
            $newTier = 'diamond';
        } elseif ($totalCredits >= 500) {
            $newTier = 'platinum';
        } elseif ($totalCredits >= 250) {
            $newTier = 'gold';
        } elseif ($totalCredits >= 100) {
            $newTier = 'silver';
        }
        
        if ($credits['tier'] !== $newTier) {
            $greenCreditsModel->updateTier($userId, $newTier);
        }
    }
    
    /**
     * Get eco-certified destinations
     */
    public function ecoDestinations() {
        $page = $this->get('page', 1);
        $limit = $this->get('limit', 12);
        $currency = $this->currencyController->getUserCurrency(Session::get('user_id'));
        
        $ecoDestinationModel = $this->model('EcoCertifiedDestination');
        $destinations = $ecoDestinationModel->getActive($page, $limit);
        
        $data = [
            'title' => 'Destinasi Eco-Certified - MyWisata',
            'destinations' => $destinations,
            'currency' => $currency,
            'csrf_token' => Middleware::csrfToken()
        ];
        
        $this->view('green_credits/eco_destinations', $data);
    }
    
    /**
     * Get low-carbon routes
     */
    public function lowCarbonRoutes() {
        $fromDestinationId = $this->get('from_id');
        $toDestinationId = $this->get('to_id');
        
        $lowCarbonRouteModel = $this->model('LowCarbonRoute');
        $routes = $lowCarbonRouteModel->getByDestinations($fromDestinationId, $toDestinationId);
        
        $this->json([
            'status' => 'success',
            'data' => $routes
        ]);
    }
}
