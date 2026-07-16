<?php
/**
 * MyWisata Application - Gamification Controller
 * 
 * Handles gamification features including points, badges, and achievements.
 * 
 * @package MyWisata
 * @version 1.0.0
 * @since 2026-07-16
 */

class GamificationController extends Controller {
    
    private $gamificationModel;
    
    public function __construct() {
        parent::__construct();
        $this->gamificationModel = $this->model('Gamification');
    }
    
    /**
     * Get user gamification profile
     */
    public function profile() {
        Middleware::requireAuth();
        
        $userId = Session::get('user_id');
        $profile = $this->gamificationModel->getUserProfile($userId);
        $badges = $this->gamificationModel->getUserBadges($userId);
        $achievements = $this->gamificationModel->getUserAchievements($userId);
        $leaderboard = $this->gamificationModel->getLeaderboard(10);
        
        $data = [
            'title' => 'Profil Gamifikasi - MyWisata',
            'profile' => $profile,
            'badges' => $badges,
            'achievements' => $achievements,
            'leaderboard' => $leaderboard,
            'csrf_token' => Middleware::csrfToken()
        ];
        
        $this->view('gamification/profile', $data);
    }
    
    /**
     * Get user points (AJAX)
     */
    public function getPoints() {
        Middleware::requireAuth();
        
        if (!$this->isAjax()) {
            $this->redirect('home');
        }
        
        $userId = Session::get('user_id');
        $points = $this->gamificationModel->getUserPoints($userId);
        
        $this->json([
            'status' => 'success',
            'data' => [
                'points' => $points
            ]
        ]);
    }
    
    /**
     * Award points to user
     */
    public function awardPoints() {
        Middleware::requireAuth();
        
        if (!$this->isAjax()) {
            $this->redirect('home');
        }
        
        // Verify CSRF
        if (!$this->validateCsrf()) {
            $this->json(['status' => 'error', 'message' => 'CSRF token mismatch'], 419);
        }
        
        $userId = Session::get('user_id');
        $action = $this->post('action');
        $itemId = $this->post('item_id', null);
        
        $points = $this->gamificationModel->awardPoints($userId, $action, $itemId);
        
        if ($points) {
            Logger::audit('AWARD_POINTS', 'user_points', "Awarded points for action: {$action}", [], [
                'user_id' => $userId,
                'points' => $points
            ]);
            
            // Check for new badges
            $newBadges = $this->gamificationModel->checkAndAwardBadges($userId);
            
            $this->json([
                'status' => 'success',
                'message' => 'Poin berhasil ditambahkan',
                'data' => [
                    'points_awarded' => $points,
                    'new_badges' => $newBadges
                ]
            ]);
        } else {
            $this->json(['status' => 'error', 'message' => 'Gagal menambahkan poin'], 500);
        }
    }
    
    /**
     * Get available badges
     */
    public function getBadges() {
        Middleware::requireAuth();
        
        if (!$this->isAjax()) {
            $this->redirect('home');
        }
        
        $userId = Session::get('user_id');
        $badges = $this->gamificationModel->getUserBadges($userId);
        $allBadges = $this->gamificationModel->getAllBadges();
        
        $this->json([
            'status' => 'success',
            'data' => [
                'user_badges' => $badges,
                'all_badges' => $allBadges
            ]
        ]);
    }
    
    /**
     * Get leaderboard
     */
    public function getLeaderboard() {
        if (!$this->isAjax()) {
            $this->redirect('home');
        }
        
        $limit = $this->get('limit', 10);
        $leaderboard = $this->gamificationModel->getLeaderboard($limit);
        
        $this->json([
            'status' => 'success',
            'data' => [
                'leaderboard' => $leaderboard
            ]
        ]);
    }
    
    /**
     * Redeem points for reward
     */
    public function redeem() {
        Middleware::requireAuth();
        
        if (!$this->isAjax()) {
            $this->redirect('home');
        }
        
        // Verify CSRF
        if (!$this->validateCsrf()) {
            $this->json(['status' => 'error', 'message' => 'CSRF token mismatch'], 419);
        }
        
        $userId = Session::get('user_id');
        $rewardId = $this->post('reward_id');
        
        $redeemed = $this->gamificationModel->redeemReward($userId, $rewardId);
        
        if ($redeemed) {
            Logger::audit('REDEEM_REWARD', 'user_rewards', "Redeemed reward ID: {$rewardId}", [], [
                'user_id' => $userId
            ]);
            
            $this->json(['status' => 'success', 'message' => 'Reward berhasil ditukar']);
        } else {
            $this->json(['status' => 'error', 'message' => 'Gagal menukar reward'], 500);
        }
    }
    
    /**
     * Get available rewards
     */
    public function getRewards() {
        Middleware::requireAuth();
        
        if (!$this->isAjax()) {
            $this->redirect('home');
        }
        
        $userId = Session::get('user_id');
        $userPoints = $this->gamificationModel->getUserPoints($userId);
        $rewards = $this->gamificationModel->getAvailableRewards();
        
        $this->json([
            'status' => 'success',
            'data' => [
                'user_points' => $userPoints,
                'rewards' => $rewards
            ]
        ]);
    }
    
    /**
     * Get user achievements
     */
    public function getAchievements() {
        Middleware::requireAuth();
        
        if (!$this->isAjax()) {
            $this->redirect('home');
        }
        
        $userId = Session::get('user_id');
        $achievements = $this->gamificationModel->getUserAchievements($userId);
        
        $this->json([
            'status' => 'success',
            'data' => [
                'achievements' => $achievements
            ]
        ]);
    }
}
