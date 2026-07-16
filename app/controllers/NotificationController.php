<?php
/**
 * MyWisata Application - Notification Controller
 * 
 * Handles notification functionality.
 * 
 * @package MyWisata
 * @version 1.0.0
 * @since 2026-07-01
 */

class NotificationController extends Controller {
    
    private $notificationModel;
    
    /**
     * Constructor - Require login
     */
    public function __construct() {
        parent::__construct();
        if (!Session::get('user_id')) {
            $this->redirect('auth/login');
        }
        $this->notificationModel = $this->model('Notification');
    }
    
    /**
     * Index - List user notifications
     */
    public function index() {
        $userId = Session::get('user_id');
        $page = $this->get('page', 1);
        $limit = $this->get('limit', 20);
        
        $notifications = $this->notificationModel->getByUser($userId, $page, $limit);
        $total = $this->notificationModel->countByUser($userId);
        
        // Mark all as read
        $this->notificationModel->markAllAsRead($userId);
        
        $data = [
            'title' => 'Notifikasi - MyWisata',
            'notifications' => $notifications,
            'total' => $total,
            'page' => $page,
            'limit' => $limit,
            'csrf_token' => Middleware::csrfToken()
        ];
        
        $this->view('notifications/index', $data);
    }
    
    /**
     * Get unread count
     */
    public function getUnreadCount() {
        if (!$this->isAjax()) {
            $this->redirect('home');
        }
        
        $userId = Session::get('user_id');
        $count = $this->notificationModel->getUnreadCount($userId);
        
        $this->json(['status' => 'success', 'count' => $count]);
    }
    
    /**
     * Get new notifications (for real-time updates)
     */
    public function getNew() {
        if (!$this->isAjax()) {
            $this->redirect('home');
        }
        
        $userId = Session::get('user_id');
        $lastNotificationId = $this->get('last_notification_id', 0);
        
        $notifications = $this->notificationModel->getNew($userId, $lastNotificationId);
        
        $this->json([
            'status' => 'success',
            'data' => [
                'notifications' => $notifications,
                'count' => count($notifications)
            ]
        ]);
    }
    
    /**
     * Mark as read
     */
    public function markAsRead() {
        if (!$this->isAjax()) {
            $this->redirect('home');
        }
        
        // Verify CSRF
        if (!$this->validateCsrf()) {
            $this->json(['status' => 'error', 'message' => 'CSRF token mismatch'], 419);
        }
        
        $notificationId = $this->post('notification_id');
        $userId = Session::get('user_id');
        
        $updated = $this->notificationModel->markAsRead($notificationId, $userId);
        
        if ($updated) {
            $this->json(['status' => 'success']);
        } else {
            $this->json(['status' => 'error', 'message' => 'Gagal menandai sebagai dibaca'], 500);
        }
    }
    
    /**
     * Mark all as read
     */
    public function markAllAsRead() {
        if (!$this->isAjax()) {
            $this->redirect('home');
        }
        
        // Verify CSRF
        if (!$this->validateCsrf()) {
            $this->json(['status' => 'error', 'message' => 'CSRF token mismatch'], 419);
        }
        
        $userId = Session::get('user_id');
        
        $updated = $this->notificationModel->markAllAsRead($userId);
        
        if ($updated) {
            $this->json(['status' => 'success']);
        } else {
            $this->json(['status' => 'error', 'message' => 'Gagal menandai semua sebagai dibaca'], 500);
        }
    }
    
    /**
     * Delete notification
     */
    public function delete() {
        if (!$this->isAjax()) {
            $this->redirect('home');
        }
        
        // Verify CSRF
        if (!$this->validateCsrf()) {
            $this->json(['status' => 'error', 'message' => 'CSRF token mismatch'], 419);
        }
        
        $notificationId = $this->post('notification_id');
        $userId = Session::get('user_id');
        
        $deleted = $this->notificationModel->delete($notificationId, $userId);
        
        if ($deleted) {
            $this->json(['status' => 'success']);
        } else {
            $this->json(['status' => 'error', 'message' => 'Gagal menghapus notifikasi'], 500);
        }
    }
    
    /**
     * Delete all read notifications
     */
    public function deleteAllRead() {
        if (!$this->isAjax()) {
            $this->redirect('home');
        }
        
        // Verify CSRF
        if (!$this->validateCsrf()) {
            $this->json(['status' => 'error', 'message' => 'CSRF token mismatch'], 419);
        }
        
        $userId = Session::get('user_id');
        
        $deleted = $this->notificationModel->deleteAllRead($userId);
        
        if ($deleted) {
            $this->json(['status' => 'success']);
        } else {
            $this->json(['status' => 'error', 'message' => 'Gagal menghapus notifikasi yang sudah dibaca'], 500);
        }
    }
    
    /**
     * Update notification settings
     */
    public function updateSettings() {
        if (!$this->isAjax()) {
            $this->redirect('home');
        }
        
        // Verify CSRF
        if (!$this->validateCsrf()) {
            $this->json(['status' => 'error', 'message' => 'CSRF token mismatch'], 419);
        }
        
        $userId = Session::get('user_id');
        $settings = $this->post('settings');
        
        $updated = $this->notificationModel->updateSettings($userId, $settings);
        
        if ($updated) {
            $this->json(['status' => 'success']);
        } else {
            $this->json(['status' => 'error', 'message' => 'Gagal memperbarui pengaturan notifikasi'], 500);
        }
    }
    
    /**
     * Get notification settings
     */
    public function getSettings() {
        if (!$this->isAjax()) {
            $this->redirect('home');
        }
        
        $userId = Session::get('user_id');
        $settings = $this->notificationModel->getSettings($userId);
        
        $this->json([
            'status' => 'success',
            'data' => $settings
        ]);
    }
}
