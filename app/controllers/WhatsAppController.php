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
            return $this->redirect('auth/login');
        }
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $phoneNumber = $_POST['phone_number'] ?? '';
            
            $result = $this->whatsappService->registerContact($userId, $phoneNumber);
            
            if ($result['success']) {
                Session::flash('success', 'WhatsApp contact registered successfully');
                return $this->redirect('whatsapp');
            } else {
                Session::flash('error', $result['error']);
            }
        }
        
        $this->view('whatsapp/index');
    }
    
    /**
     * Send message
     */
    public function send() {
        $userId = $_SESSION['user_id'] ?? null;
        
        if (!$userId) {
            return $this->redirect('auth/login');
        }
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $contactId = $_POST['contact_id'] ?? 0;
            $messageType = $_POST['message_type'] ?? 'custom';
            $content = $_POST['content'] ?? '';
            $variables = json_decode($_POST['variables'] ?? '[]', true);
            
            $result = $this->whatsappService->sendMessage($contactId, $messageType, $content, $variables);
            
            if ($result['success']) {
                Session::flash('success', 'Message sent successfully');
                return $this->redirect('whatsapp');
            } else {
                Session::flash('error', $result['error']);
            }
        }
        
        $this->view('whatsapp/index');
    }
    
    /**
     * Get message statistics
     */
    public function statistics() {
        $contactId = $_GET['contact_id'] ?? null;
        $stats = $this->whatsappService->getStatistics($contactId);
        return $this->json(['success' => true, 'data' => $stats]);
    }
    
    /**
     * Index page
     */
    public function index() {
        $userId = $_SESSION['user_id'] ?? null;
        
        if (!$userId) {
            return $this->redirect('auth/login');
        }
        
        $stats = $this->whatsappService->getStatistics();
        $data = [
            'stats' => $stats,
            'contacts' => [] // Would need to fetch from model
        ];
        $this->view('whatsapp/index', $data);
    }
}
