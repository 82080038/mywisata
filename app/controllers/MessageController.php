<?php
/**
 * MyWisata Application - Message Controller
 * 
 * Handles messaging between users (tourists and tour guides).
 * 
 * @package MyWisata
 * @version 1.0.0
 * @since 2026-07-16
 */

class MessageController extends Controller {
    
    private $messageModel;
    
    public function __construct() {
        parent::__construct();
        $this->messageModel = $this->model('Message');
    }
    
    /**
     * Display conversation list
     */
    public function index() {
        Middleware::requireAuth();
        
        $userId = Session::get('user_id');
        $conversations = $this->messageModel->getConversations($userId);
        
        $data = [
            'title' => 'Pesan - MyWisata',
            'conversations' => $conversations,
            'csrf_token' => Middleware::csrfToken()
        ];
        
        $this->view('message/index', $data);
    }
    
    /**
     * Display conversation with specific user
     */
    public function conversation() {
        Middleware::requireAuth();
        
        $userId = Session::get('user_id');
        $otherUserId = $this->get('user_id');
        
        if (empty($otherUserId)) {
            $this->redirect('message');
        }
        
        // Mark messages as read
        $this->messageModel->markAsRead($userId, $otherUserId);
        
        $messages = $this->messageModel->getConversation($userId, $otherUserId);
        $otherUser = $this->messageModel->getUserInfo($otherUserId);
        
        $data = [
            'title' => 'Pesan - MyWisata',
            'messages' => $messages,
            'other_user' => $otherUser,
            'other_user_id' => $otherUserId,
            'csrf_token' => Middleware::csrfToken()
        ];
        
        $this->view('message/conversation', $data);
    }
    
    /**
     * Send a message
     */
    public function send() {
        Middleware::requireAuth();
        
        if (!$this->isAjax()) {
            $this->redirect('message');
        }
        
        // Verify CSRF
        if (!$this->validateCsrf()) {
            $this->json(['status' => 'error', 'message' => 'CSRF token mismatch'], 419);
        }
        
        $senderId = Session::get('user_id');
        $receiverId = $this->post('receiver_id');
        $message = $this->post('message');
        
        // Validate input
        if (empty($receiverId)) {
            $this->json(['status' => 'error', 'message' => 'Penerima tidak valid'], 400);
        }
        
        if (empty($message) || strlen($message) < 1) {
            $this->json(['status' => 'error', 'message' => 'Pesan tidak boleh kosong'], 400);
        }
        
        if (strlen($message) > 2000) {
            $this->json(['status' => 'error', 'message' => 'Pesan maksimal 2000 karakter'], 400);
        }
        
        // Check if sender is blocked by receiver
        if ($this->messageModel->isBlocked($receiverId, $senderId)) {
            $this->json(['status' => 'error', 'message' => 'Anda tidak dapat mengirim pesan ke pengguna ini'], 403);
        }
        
        // Send message
        $messageId = $this->messageModel->send($senderId, $receiverId, $message);
        
        if ($messageId) {
            Logger::audit('SEND_MESSAGE', 'messages', "Sent message to user ID: {$receiverId}", [], [
                'message_id' => $messageId
            ]);
            
            $this->json([
                'status' => 'success',
                'message' => 'Pesan terkirim',
                'message_id' => $messageId
            ]);
        } else {
            $this->json(['status' => 'error', 'message' => 'Gagal mengirim pesan'], 500);
        }
    }
    
    /**
     * Get new messages (for real-time updates)
     */
    public function getNewMessages() {
        Middleware::requireAuth();
        
        if (!$this->isAjax()) {
            $this->redirect('message');
        }
        
        $userId = Session::get('user_id');
        $otherUserId = $this->get('user_id');
        $lastMessageId = $this->get('last_message_id', 0);
        
        if (empty($otherUserId)) {
            $this->json(['status' => 'error', 'message' => 'User ID tidak valid'], 400);
        }
        
        $messages = $this->messageModel->getNewMessages($userId, $otherUserId, $lastMessageId);
        
        $this->json([
            'status' => 'success',
            'data' => [
                'messages' => $messages,
                'count' => count($messages)
            ]
        ]);
    }
    
    /**
     * Delete a message
     */
    public function delete() {
        Middleware::requireAuth();
        
        if (!$this->isAjax()) {
            $this->redirect('message');
        }
        
        // Verify CSRF
        if (!$this->validateCsrf()) {
            $this->json(['status' => 'error', 'message' => 'CSRF token mismatch'], 419);
        }
        
        $userId = Session::get('user_id');
        $messageId = $this->post('message_id');
        
        // Get message
        $message = $this->messageModel->findById($messageId);
        
        if (!$message) {
            $this->json(['status' => 'error', 'message' => 'Pesan tidak ditemukan'], 404);
        }
        
        // Check ownership
        if ($message['sender_id'] != $userId) {
            $this->json(['status' => 'error', 'message' => 'Anda tidak memiliki akses'], 403);
        }
        
        // Delete message
        $deleted = $this->messageModel->delete($messageId);
        
        if ($deleted) {
            Logger::audit('DELETE_MESSAGE', 'messages', "Deleted message ID: {$messageId}", [], []);
            
            $this->json([
                'status' => 'success',
                'message' => 'Pesan dihapus'
            ]);
        } else {
            $this->json(['status' => 'error', 'message' => 'Gagal menghapus pesan'], 500);
        }
    }
    
    /**
     * Block a user
     */
    public function block() {
        Middleware::requireAuth();
        
        if (!$this->isAjax()) {
            $this->redirect('message');
        }
        
        // Verify CSRF
        if (!$this->validateCsrf()) {
            $this->json(['status' => 'error', 'message' => 'CSRF token mismatch'], 419);
        }
        
        $userId = Session::get('user_id');
        $blockedUserId = $this->post('user_id');
        
        if (empty($blockedUserId)) {
            $this->json(['status' => 'error', 'message' => 'User ID tidak valid'], 400);
        }
        
        // Block user
        $blocked = $this->messageModel->blockUser($userId, $blockedUserId);
        
        if ($blocked) {
            Logger::audit('BLOCK_USER', 'message_blocks', "Blocked user ID: {$blockedUserId}", [], []);
            
            $this->json([
                'status' => 'success',
                'message' => 'Pengguna diblokir'
            ]);
        } else {
            $this->json(['status' => 'error', 'message' => 'Gagal memblokir pengguna'], 500);
        }
    }
    
    /**
     * Unblock a user
     */
    public function unblock() {
        Middleware::requireAuth();
        
        if (!$this->isAjax()) {
            $this->redirect('message');
        }
        
        // Verify CSRF
        if (!$this->validateCsrf()) {
            $this->json(['status' => 'error', 'message' => 'CSRF token mismatch'], 419);
        }
        
        $userId = Session::get('user_id');
        $blockedUserId = $this->post('user_id');
        
        if (empty($blockedUserId)) {
            $this->json(['status' => 'error', 'message' => 'User ID tidak valid'], 400);
        }
        
        // Unblock user
        $unblocked = $this->messageModel->unblockUser($userId, $blockedUserId);
        
        if ($unblocked) {
            Logger::audit('UNBLOCK_USER', 'message_blocks', "Unblocked user ID: {$blockedUserId}", [], []);
            
            $this->json([
                'status' => 'success',
                'message' => 'Pengguna tidak diblokir'
            ]);
        } else {
            $this->json(['status' => 'error', 'message' => 'Gagal membuka blokir'], 500);
        }
    }
    
    /**
     * Get unread message count
     */
    public function getUnreadCount() {
        Middleware::requireAuth();
        
        if (!$this->isAjax()) {
            $this->redirect('message');
        }
        
        $userId = Session::get('user_id');
        $count = $this->messageModel->getUnreadCount($userId);
        
        $this->json([
            'status' => 'success',
            'data' => [
                'unread_count' => $count
            ]
        ]);
    }
}
