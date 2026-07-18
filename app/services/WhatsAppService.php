<?php
namespace App\Services;

use App\Models\WhatsAppContact;
use App\Models\WhatsAppMessage;
use App\Models\WhatsAppTemplate;
use App\Models\WhatsAppCampaign;

/**
 * WhatsApp Service
 * 
 * Service for WhatsApp messaging and notifications (self-hosted)
 * 
 * @package App\Services
 */
class WhatsAppService {
    private $contact;
    private $message;
    private $template;
    private $campaign;
    private $baseUrl;
    private $apiKey;
    
    public function __construct() {
        $this->contact = new WhatsAppContact();
        $this->message = new WhatsAppMessage();
        $this->template = new WhatsAppTemplate();
        $this->campaign = new WhatsAppCampaign();
        
        $this->baseUrl = getenv('WHATSAPP_BASE_URL') ?: 'http://localhost:3000';
        $this->apiKey = getenv('WHATSAPP_API_KEY') ?: '';
    }
    
    /**
     * Register contact
     * 
     * @param int $userId User ID
     * @param string $phoneNumber Phone number
     * @return array Result
     */
    public function registerContact($userId, $phoneNumber) {
        $existing = $this->contact->getByPhone($phoneNumber);
        
        if ($existing) {
            return ['success' => false, 'error' => 'Phone number already registered'];
        }
        
        $data = [
            'user_id' => $userId,
            'phone_number' => $phoneNumber,
            'is_verified' => 0,
            'is_primary' => 1,
            'opt_in' => 1
        ];
        
        $id = $this->contact->create($data);
        
        if ($id) {
            return ['success' => true, 'id' => $id];
        }
        
        return ['success' => false, 'error' => 'Failed to register contact'];
    }
    
    /**
     * Send message
     * 
     * @param int $contactId Contact ID
     * @param string $messageType Message type
     * @param string $content Message content
     * @param array $variables Template variables
     * @return array Result
     */
    public function sendMessage($contactId, $messageType, $content, $variables = []) {
        $data = [
            'contact_id' => $contactId,
            'message_type' => $messageType,
            'direction' => 'outbound',
            'content' => $content,
            'status' => 'pending'
        ];
        
        $messageId = $this->message->create($data);
        
        if (!$messageId) {
            return ['success' => false, 'error' => 'Failed to create message'];
        }
        
        // Send via WhatsApp API (if configured)
        if ($this->baseUrl && $this->apiKey) {
            $result = $this->sendViaAPI($contactId, $content, $variables);
            
            if ($result['success']) {
                $this->message->updateStatus($messageId, 'sent', 'sent_at');
                $this->message->update($messageId, ['external_message_id' => $result['message_id']]);
                return ['success' => true, 'message_id' => $messageId];
            }
        }
        
        // Fallback: mark as sent without actual API call
        $this->message->updateStatus($messageId, 'sent', 'sent_at');
        return ['success' => true, 'message_id' => $messageId, 'warning' => 'API not configured, message saved locally only'];
    }
    
    /**
     * Send via WhatsApp API
     * 
     * @param int $contactId Contact ID
     * @param string $content Message content
     * @param array $variables Template variables
     * @return array Result
     */
    private function sendViaAPI($contactId, $content, $variables = []) {
        $contact = $this->contact->getById($contactId);
        
        if (!$contact) {
            return ['success' => false, 'error' => 'Contact not found'];
        }
        
        try {
            $ch = curl_init($this->baseUrl . '/api/send');
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode([
                'phone' => $contact['phone_number'],
                'message' => $content,
                'variables' => $variables
            ]));
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'Content-Type: application/json',
                'Authorization: Bearer ' . $this->apiKey
            ]);
            curl_setopt($ch, CURLOPT_TIMEOUT, 30);
            
            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);
            
            if ($httpCode === 200) {
                $result = json_decode($response, true);
                return ['success' => true, 'message_id' => $result['id'] ?? null];
            }
            
            return ['success' => false, 'error' => 'API request failed'];
        } catch (\Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }
    
    /**
     * Send booking confirmation
     * 
     * @param int $userId User ID
     * @param array $bookingData Booking data
     * @return array Result
     */
    public function sendBookingConfirmation($userId, $bookingData) {
        $contact = $this->contact->getByUserId($userId);
        
        if (!$contact) {
            return ['success' => false, 'error' => 'No WhatsApp contact found'];
        }
        
        $template = $this->template->getByName('booking_confirmation');
        
        if ($template) {
            $content = $this->renderTemplate($template['content'], $bookingData);
        } else {
            $content = "Booking confirmed! Reference: {$bookingData['reference']}. Date: {$bookingData['date']}. Thank you for using MyWisata!";
        }
        
        return $this->sendMessage($contact['id'], 'booking_confirmation', $content, $bookingData);
    }
    
    /**
     * Send payment reminder
     * 
     * @param int $userId User ID
     * @param array $paymentData Payment data
     * @return array Result
     */
    public function sendPaymentReminder($userId, $paymentData) {
        $contact = $this->contact->getByUserId($userId);
        
        if (!$contact) {
            return ['success' => false, 'error' => 'No WhatsApp contact found'];
        }
        
        $template = $this->template->getByName('payment_reminder');
        
        if ($template) {
            $content = $this->renderTemplate($template['content'], $paymentData);
        } else {
            $content = "Payment reminder: Amount: {$paymentData['amount']}. Due date: {$paymentData['due_date']}. Please complete your payment.";
        }
        
        return $this->sendMessage($contact['id'], 'payment_reminder', $content, $paymentData);
    }
    
    /**
     * Render template with variables
     * 
     * @param string $template Template content
     * @param array $variables Variables
     * @return string Rendered content
     */
    private function renderTemplate($template, $variables) {
        foreach ($variables as $key => $value) {
            $template = str_replace("{{$key}}", $value, $template);
        }
        return $template;
    }
    
    /**
     * Create campaign
     * 
     * @param array $data Campaign data
     * @return array Result
     */
    public function createCampaign($data) {
        $id = $this->campaign->create($data);
        
        if ($id) {
            return ['success' => true, 'id' => $id];
        }
        
        return ['success' => false, 'error' => 'Failed to create campaign'];
    }
    
    /**
     * Get message statistics
     * 
     * @param int $contactId Contact ID (optional)
     * @return array Statistics
     */
    public function getStatistics($contactId = null) {
        return $this->message->getStatistics($contactId);
    }
}
