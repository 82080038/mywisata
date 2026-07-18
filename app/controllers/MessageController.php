<?php

/**
 * MyWisata Application - Message Controller
 *
 * Handles user-to-user chat/messaging.
 */
class MessageController extends Controller
{
    public function __construct()
    {
        parent::__construct();
        if (!Session::get('user_id')) {
            if ($this->isAjax()) {
                $this->json(['status' => 'error', 'message' => 'Silakan login terlebih dahulu'], 401);
            }
            Session::flash('error', 'Silakan login terlebih dahulu');
            Session::set('redirect_after_login', 'messages');
            $this->redirect('auth/login');
        }
    }

    /**
     * Inbox - list all conversations
     */
    public function index()
    {
        $userId = Session::get('user_id');
        $messageModel = new Message();
        $conversations = $messageModel->getInbox($userId);
        $unreadCount = $messageModel->getUnreadCount($userId);

        $data = [
            'title' => 'Pesan - MyWisata',
            'conversations' => $conversations,
            'unreadCount' => $unreadCount,
        ];

        $this->view('messages/index', $data);
    }

    /**
     * View a conversation
     */
    public function chat()
    {
        $userId = Session::get('user_id');
        $conversationId = $this->get('id');

        if (!$conversationId) {
            Session::flash('error', 'Percakapan tidak ditemukan');
            $this->redirect('messages');
        }

        $messageModel = new Message();
        $conversation = $messageModel->getConversation($conversationId, $userId);

        if (!$conversation) {
            Session::flash('error', 'Akses ditolak');
            $this->redirect('messages');
        }

        $messageModel->markAsRead($conversationId, $userId);
        $messages = $messageModel->getMessages($conversationId);

        $data = [
            'title' => 'Chat - MyWisata',
            'conversation' => $conversation,
            'messages' => $messages,
            'csrf_token' => Middleware::csrfToken(),
        ];

        $this->view('messages/chat', $data);
    }

    /**
     * Start a new conversation with a business owner
     */
    public function compose()
    {
        $userId = Session::get('user_id');
        $recipientId = $this->get('to');
        $contextType = $this->get('context', 'general');
        $contextId = $this->get('context_id');

        if (!$recipientId || $recipientId == $userId) {
            Session::flash('error', 'Penerima tidak valid');
            $this->redirect('messages');
        }

        $db = Database::getInstance();
        $recipient = $db->query("SELECT id, name, role FROM users WHERE id = :id", ['id' => $recipientId])->fetch();

        if (!$recipient) {
            Session::flash('error', 'Penerima tidak ditemukan');
            $this->redirect('messages');
        }

        // Get context info
        $contextInfo = null;
        if ($contextType === 'hotel' && $contextId) {
            $contextInfo = $db->query("SELECT name FROM hotels WHERE id = :id", ['id' => $contextId])->fetch();
        } elseif ($contextType === 'product' && $contextId) {
            $contextInfo = $db->query("SELECT name FROM products WHERE id = :id", ['id' => $contextId])->fetch();
        } elseif ($contextType === 'tour_guide' && $contextId) {
            $contextInfo = $db->query("SELECT u.name as name FROM tour_guides tg INNER JOIN users u ON tg.user_id = u.id WHERE tg.id = :id", ['id' => $contextId])->fetch();
        }

        // Check if conversation already exists
        $messageModel = new Message();
        $existingConv = $messageModel->getOrCreateConversation($userId, $recipientId, $contextType, $contextId, $contextInfo['name'] ?? null);

        $data = [
            'title' => 'Pesan Baru - MyWisata',
            'recipient' => $recipient,
            'contextType' => $contextType,
            'contextId' => $contextId,
            'contextInfo' => $contextInfo,
            'existingConvId' => $existingConv,
            'csrf_token' => Middleware::csrfToken(),
        ];

        $this->view('messages/compose', $data);
    }

    /**
     * Send message (AJAX)
     */
    public function send()
    {
        if (!$this->isAjax()) {
            $this->redirect('messages');
        }

        if (!$this->validateCsrf()) {
            $this->json(['status' => 'error', 'message' => 'CSRF token mismatch'], 419);
        }

        $userId = Session::get('user_id');
        $conversationId = $this->post('conversation_id');
        $message = trim($this->post('message'));

        if (empty($message)) {
            $this->json(['status' => 'error', 'message' => 'Pesan tidak boleh kosong'], 400);
        }

        $messageModel = new Message();
        $conversation = $messageModel->getConversation($conversationId, $userId);

        if (!$conversation) {
            $this->json(['status' => 'error', 'message' => 'Percakapan tidak ditemukan'], 404);
        }

        $messageId = $messageModel->sendMessage($conversationId, $userId, $message);

        $this->json([
            'status' => 'success',
            'message_id' => $messageId,
            'time' => date('H:i'),
        ]);
    }

    /**
     * Start conversation + send first message
     */
    public function start()
    {
        if (!$this->isAjax()) {
            $this->redirect('messages');
        }

        if (!$this->validateCsrf()) {
            $this->json(['status' => 'error', 'message' => 'CSRF token mismatch'], 419);
        }

        $userId = Session::get('user_id');
        $recipientId = $this->post('recipient_id');
        $message = trim($this->post('message'));
        $contextType = $this->post('context_type', 'general');
        $contextId = $this->post('context_id') ?: null;
        $subject = $this->post('subject');

        if (empty($message)) {
            $this->json(['status' => 'error', 'message' => 'Pesan tidak boleh kosong'], 400);
        }

        $messageModel = new Message();
        $conversationId = $messageModel->getOrCreateConversation($userId, $recipientId, $contextType, $contextId, $subject);
        $messageModel->sendMessage($conversationId, $userId, $message);

        $this->json([
            'status' => 'success',
            'conversation_id' => $conversationId,
            'redirect' => View::url('messages/chat?id=' . $conversationId),
        ]);
    }

    /**
     * Poll for new messages (AJAX)
     */
    public function poll()
    {
        if (!$this->isAjax()) {
            $this->redirect('messages');
        }

        $userId = Session::get('user_id');
        $conversationId = $this->get('conversation_id');
        $lastMessageId = $this->get('last_id', 0);

        $messageModel = new Message();
        $conversation = $messageModel->getConversation($conversationId, $userId);

        if (!$conversation) {
            $this->json(['status' => 'error', 'message' => 'Not found'], 404);
        }

        $newMessages = $messageModel->getNewMessages($conversationId, $lastMessageId);

        // Mark new messages as read
        if (!empty($newMessages)) {
            $messageModel->markAsRead($conversationId, $userId);
        }

        $formatted = [];
        foreach ($newMessages as $msg) {
            $formatted[] = [
                'id' => $msg['id'],
                'sender_id' => $msg['sender_id'],
                'sender_name' => $msg['sender_name'],
                'sender_role' => $msg['sender_role'],
                'message' => $msg['message'],
                'time' => date('H:i', strtotime($msg['created_at'])),
                'is_me' => $msg['sender_id'] == $userId,
            ];
        }

        $this->json([
            'status' => 'success',
            'messages' => $formatted,
            'unread_count' => $messageModel->getUnreadCount($userId),
        ]);
    }

    /**
     * Get unread count (AJAX for badge)
     */
    public function unread()
    {
        if (!$this->isAjax()) {
            $this->redirect('messages');
        }

        $messageModel = new Message();
        $count = $messageModel->getUnreadCount(Session::get('user_id'));

        $this->json(['status' => 'success', 'count' => $count]);
    }
}
