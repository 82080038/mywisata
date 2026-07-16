<?php
/**
 * MyWisata Application - Push Notification Helper
 * 
 * Handles push notification functionality using Web Push API.
 * 
 * @package MyWisata
 * @version 1.0.0
 * @since 2026-07-16
 */

class PushNotification {
    
    private $vapidPublicKey;
    private $vapidPrivateKey;
    private $db;
    
    public function __construct() {
        $this->vapidPublicKey = getenv('VAPID_PUBLIC_KEY') ?: '';
        $this->vapidPrivateKey = getenv('VAPID_PRIVATE_KEY') ?: '';
        $this->db = Database::getInstance();
    }
    
    /**
     * Subscribe user to push notifications
     * 
     * @param int $userId User ID
     * @param string $endpoint Push endpoint
     * @param string $p256dh Key
     * @param string $auth Auth secret
     * @return bool
     */
    public function subscribe($userId, $endpoint, $p256dh, $auth) {
        $sql = "INSERT INTO push_subscriptions 
                (user_id, endpoint, p256dh, auth, created_at)
                VALUES (:user_id, :endpoint, :p256dh, :auth, NOW())
                ON DUPLICATE KEY UPDATE 
                endpoint = :endpoint,
                p256dh = :p256dh,
                auth = :auth,
                updated_at = NOW()";
        
        return $this->db->query($sql, [
            'user_id' => $userId,
            'endpoint' => $endpoint,
            'p256dh' => $p256dh,
            'auth' => $auth
        ]);
    }
    
    /**
     * Unsubscribe user from push notifications
     * 
     * @param int $userId User ID
     * @param string $endpoint Push endpoint
     * @return bool
     */
    public function unsubscribe($userId, $endpoint) {
        $sql = "DELETE FROM push_subscriptions 
                WHERE user_id = :user_id AND endpoint = :endpoint";
        
        return $this->db->query($sql, [
            'user_id' => $userId,
            'endpoint' => $endpoint
        ]);
    }
    
    /**
     * Send push notification to user
     * 
     * @param int $userId User ID
     * @param string $title Notification title
     * @param string $body Notification body
     * @param array $data Additional data
     * @param array $actions Notification actions
     * @return bool
     */
    public function sendToUser($userId, $title, $body, $data = [], $actions = []) {
        $subscriptions = $this->getUserSubscriptions($userId);
        
        if (empty($subscriptions)) {
            return false;
        }
        
        $payload = $this->createPayload($title, $body, $data, $actions);
        $success = 0;
        
        foreach ($subscriptions as $subscription) {
            if ($this->sendPush($subscription, $payload)) {
                $success++;
            }
        }
        
        return $success > 0;
    }
    
    /**
     * Send push notification to multiple users
     * 
     * @param array $userIds User IDs
     * @param string $title Notification title
     * @param string $body Notification body
     * @param array $data Additional data
     * @param array $actions Notification actions
     * @return int Number of successful sends
     */
    public function sendToUsers($userIds, $title, $body, $data = [], $actions = []) {
        $success = 0;
        
        foreach ($userIds as $userId) {
            if ($this->sendToUser($userId, $title, $body, $data, $actions)) {
                $success++;
            }
        }
        
        return $success;
    }
    
    /**
     * Send push notification to all subscribers
     * 
     * @param string $title Notification title
     * @param string $body Notification body
     * @param array $data Additional data
     * @param array $actions Notification actions
     * @return int Number of successful sends
     */
    public function sendToAll($title, $body, $data = [], $actions = []) {
        $subscriptions = $this->getAllSubscriptions();
        
        if (empty($subscriptions)) {
            return 0;
        }
        
        $payload = $this->createPayload($title, $body, $data, $actions);
        $success = 0;
        
        foreach ($subscriptions as $subscription) {
            if ($this->sendPush($subscription, $payload)) {
                $success++;
            }
        }
        
        return $success;
    }
    
    /**
     * Get user subscriptions
     * 
     * @param int $userId User ID
     * @return array
     */
    private function getUserSubscriptions($userId) {
        $sql = "SELECT * FROM push_subscriptions WHERE user_id = :user_id";
        return $this->db->query($sql, ['user_id' => $userId])->fetchAll();
    }
    
    /**
     * Get all subscriptions
     * 
     * @return array
     */
    private function getAllSubscriptions() {
        $sql = "SELECT * FROM push_subscriptions";
        return $this->db->query($sql)->fetchAll();
    }
    
    /**
     * Create notification payload
     * 
     * @param string $title Title
     * @param string $body Body
     * @param array $data Data
     * @param array $actions Actions
     * @return string
     */
    private function createPayload($title, $body, $data = [], $actions = []) {
        $payload = [
            'title' => $title,
            'body' => $body,
            'data' => $data,
            'actions' => $actions,
            'icon' => BASE_URL . '/assets/icons/icon-192x192.png',
            'badge' => BASE_URL . '/assets/icons/badge-72x72.png',
            'timestamp' => time()
        ];
        
        return json_encode($payload);
    }
    
    /**
     * Send push notification
     * 
     * @param array $subscription Subscription data
     * @param string $payload Payload
     * @return bool
     */
    private function sendPush($subscription, $payload) {
        try {
            $auth = [
                'VAPID' => [
                    'subject' => 'mailto:contact@mywisata.com',
                    'publicKey' => $this->vapidPublicKey,
                    'privateKey' => $this->vapidPrivateKey
                ]
            ];
            
            $webPush = new \Minishlink\WebPush\WebPush($auth);
            $subscription = new \Minishlink\WebPush\Subscription(
                $subscription['endpoint'],
                $subscription['p256dh'],
                $subscription['auth']
            );
            
            $report = $webPush->sendOne($subscription, $payload);
            
            return $report->isSuccess();
        } catch (Exception $e) {
            Logger::error('Push notification failed', [
                'error' => $e->getMessage(),
                'endpoint' => $subscription['endpoint']
            ]);
            return false;
        }
    }
    
    /**
     * Get VAPID public key for frontend
     * 
     * @return string
     */
    public function getVapidPublicKey() {
        return $this->vapidPublicKey;
    }
    
    /**
     * Send booking confirmation notification
     * 
     * @param int $userId User ID
     * @param int $bookingId Booking ID
     * @return bool
     */
    public function sendBookingConfirmation($userId, $bookingId) {
        return $this->sendToUser(
            $userId,
            'Booking Confirmed',
            'Your booking has been confirmed! Check your booking details.',
            ['type' => 'booking', 'booking_id' => $bookingId],
            [
                ['action' => 'view', 'title' => 'View Booking'],
                ['action' => 'dismiss', 'title' => 'Dismiss']
            ]
        );
    }
    
    /**
     * Send booking reminder notification
     * 
     * @param int $userId User ID
     * @param int $bookingId Booking ID
     * @param string $date Date
     * @return bool
     */
    public function sendBookingReminder($userId, $bookingId, $date) {
        return $this->sendToUser(
            $userId,
            'Upcoming Booking Reminder',
            "You have a booking scheduled for {$date}. Don't forget!",
            ['type' => 'reminder', 'booking_id' => $bookingId],
            [
                ['action' => 'view', 'title' => 'View Booking'],
                ['action' => 'dismiss', 'title' => 'Dismiss']
            ]
        );
    }
    
    /**
     * Send promo notification
     * 
     * @param array $userIds User IDs
     * @param string $promoCode Promo code
     * @param string $description Description
     * @return int
     */
    public function sendPromoNotification($userIds, $promoCode, $description) {
        return $this->sendToUsers(
            $userIds,
            'Special Offer!',
            "Use promo code {$promoCode} for {$description}",
            ['type' => 'promo', 'promo_code' => $promoCode],
            [
                ['action' => 'use', 'title' => 'Use Promo'],
                ['action' => 'dismiss', 'title' => 'Dismiss']
            ]
        );
    }
    
    /**
     * Send new destination notification
     * 
     * @param array $userIds User IDs
     * @param string $destinationName Destination name
     * @param int $destinationId Destination ID
     * @return int
     */
    public function sendNewDestinationNotification($userIds, $destinationName, $destinationId) {
        return $this->sendToUsers(
            $userIds,
            'New Destination Added!',
            "Check out {$destinationName}, our newest destination!",
            ['type' => 'destination', 'destination_id' => $destinationId],
            [
                ['action' => 'view', 'title' => 'View Destination'],
                ['action' => 'dismiss', 'title' => 'Dismiss']
            ]
        );
    }
}
