<?php
namespace App\Controllers;

use App\Services\WhatsAppService;

class WhatsAppController extends Controller {
    private $whatsappService;
    
    public function __construct() {
        $this->whatsappService = new WhatsAppService();
    }
    
    /**
     * Register WhatsApp contact
     */
    public function register() {
        $userId = $_SESSION['user_id'] ?? null;
        
        if (!$userId) {
            return $this->json(['success' => false, 'error' => 'Not logged in']);
        }
        
        $phoneNumber = $_POST['phone_number'] ?? '';
        
        $result = $this->whatsappService->registerContact($userId, $phoneNumber);
        return $this->json($result);
    }
    
    /**
     * Send message
     */
    public function send() {
        $userId = $_SESSION['user_id'] ?? null;
        
        if (!$userId) {
            return $this->json(['success' => false, 'error' => 'Not logged in']);
        }
        
        $contactId = $_POST['contact_id'] ?? 0;
        $messageType = $_POST['message_type'] ?? 'custom';
        $content = $_POST['content'] ?? '';
        $variables = json_decode($_POST['variables'] ?? '[]', true);
        
        $result = $this->whatsappService->sendMessage($contactId, $messageType, $content, $variables);
        return $this->json($result);
    }
    
    /**
     * Get message statistics
     */
    public function statistics() {
        $contactId = $_GET['contact_id'] ?? null;
        $stats = $this->whatsappService->getStatistics($contactId);
        return $this->json(['success' => true, 'data' => $stats]);
    }
}
