<?php

/**
 * MyWisata Application - Dashboard Controller
 *
 * Handles wisatawan (tourist) dashboard.
 *
 * @version 1.0.0
 *
 * @since 2026-07-18
 */
class DashboardController extends Controller
{
    public function __construct()
    {
        parent::__construct();
        Middleware::requireAuth();
    }

    /**
     * Dashboard - Main wisatawan dashboard
     */
    public function index()
    {
        $userId = Session::get('user_id');

        $bookingModel = new Booking();
        $ticketModel = new Ticket();
        $favoriteModel = new Favorite();
        $transactionModel = new Transaction();

        $recentBookings = $bookingModel->getByUserId($userId);
        $recentTickets = $ticketModel->getByUserId($userId);
        $favorites = $favoriteModel->getUserFavorites($userId);
        $transactions = $transactionModel->getByUserId($userId);

        $pendingPayments = array_filter($transactions, function ($t) {
            return $t['payment_status'] === 'pending';
        });

        $data = [
            'title' => 'Dashboard - MyWisata',
            'recentBookings' => array_slice($recentBookings, 0, 5),
            'recentTickets' => array_slice($recentTickets, 0, 5),
            'favorites' => array_slice($favorites, 0, 5),
            'pendingPayments' => $pendingPayments,
            'totalBookings' => count($recentBookings),
            'totalTickets' => count($recentTickets),
            'totalFavorites' => count($favorites),
        ];

        $this->view('wisatawan/dashboard', $data);
    }

    /**
     * Food preferences - manage allergies and dietary restrictions
     */
    public function foodPreferences()
    {
        $userId = Session::get('user_id');
        $userModel = new User();
        $prefs = $userModel->getFoodPreferences($userId);

        $data = [
            'title' => 'Preferensi Makanan - MyWisata',
            'prefs' => $prefs,
            'allergyOptions' => User::getAllergyOptions(),
            'preferenceOptions' => User::getPreferenceOptions(),
            'csrf_token' => Middleware::csrfToken(),
        ];

        $this->view('wisatawan/food_preferences', $data);
    }

    /**
     * Update food preferences (AJAX)
     */
    public function updateFoodPreferences()
    {
        if (!$this->isAjax()) {
            $this->redirect('dashboard');
        }

        if (!$this->validateCsrf()) {
            $this->json(['status' => 'error', 'message' => 'CSRF token mismatch'], 419);
        }

        $userId = Session::get('user_id');
        $allergies = $this->post('allergies', []);
        $preferences = $this->post('preferences', []);
        $notes = $this->post('notes', '');

        if (!is_array($allergies)) {
            $allergies = [];
        }
        if (!is_array($preferences)) {
            $preferences = [];
        }

        $userModel = new User();
        $userModel->updateFoodPreferences($userId, $allergies, $preferences, $notes);

        Logger::audit('UPDATE_FOOD_PREFS', 'users', "Updated food preferences for user ID: {$userId}", [], [
            'allergies' => $allergies,
            'preferences' => $preferences,
        ]);

        $this->json(['status' => 'success', 'message' => 'Preferensi makanan berhasil disimpan']);
    }
}
