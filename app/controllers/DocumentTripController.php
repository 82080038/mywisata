<?php
namespace App\Controllers;

use App\Services\DocumentTripService;

class DocumentTripController extends Controller {
    private $documentTripService;
    
    public function __construct() {
        $this->documentTripService = new DocumentTripService();
    }
    
    /**
     * Get wallet
     */
    public function wallet() {
        $userId = $_SESSION['user_id'] ?? null;
        
        if (!$userId) {
            return $this->redirect('auth/login');
        }
        
        $wallet = $this->documentTripService->getWallet($userId);
        $transactions = $this->documentTripService->getTransactions($userId, 20);
        
        $data = [
            'wallet' => $wallet,
            'transactions' => $transactions
        ];
        $this->view('document_trip/wallet', $data);
    }
    
    /**
     * Add funds to wallet
     */
    public function addFunds() {
        $userId = $_SESSION['user_id'] ?? null;
        
        if (!$userId) {
            return $this->redirect('auth/login');
        }
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $amount = $_POST['amount'] ?? 0;
            $description = $_POST['description'] ?? null;
            
            $result = $this->documentTripService->addFunds($userId, $amount, $description);
            
            if ($result['success']) {
                Session::flash('success', 'Funds added successfully');
                return $this->redirect('document-trip/wallet');
            } else {
                Session::flash('error', $result['error']);
            }
        }
        
        return $this->redirect('document-trip/wallet');
    }
    
    /**
     * Get wallet transactions
     */
    public function transactions() {
        $userId = $_SESSION['user_id'] ?? null;
        
        if (!$userId) {
            return $this->redirect('auth/login');
        }
        
        $limit = $_GET['limit'] ?? 50;
        $transactions = $this->documentTripService->getTransactions($userId, $limit);
        return $this->json(['success' => true, 'data' => $transactions]);
    }
    
    /**
     * Import itinerary
     */
    public function importItinerary() {
        $userId = $_SESSION['user_id'] ?? null;
        
        if (!$userId) {
            return $this->json(['success' => false, 'error' => 'Not logged in']);
        }
        
        $filename = $_FILES['file']['name'] ?? '';
        $filePath = $_FILES['file']['tmp_name'] ?? '';
        
        if (!$filePath) {
            return $this->json(['success' => false, 'error' => 'No file uploaded']);
        }
        
        $result = $this->documentTripService->importItinerary($userId, $filename, $filePath);
        return $this->json($result);
    }
    
    /**
     * Get trip timeline
     */
    public function timeline() {
        $itineraryId = $_GET['itinerary_id'] ?? 0;
        $timeline = $this->documentTripService->getTimeline($itineraryId);
        return $this->json(['success' => true, 'data' => $timeline]);
    }
    
    /**
     * Generate PDF itinerary
     */
    public function generatePDF() {
        $itineraryId = $_POST['itinerary_id'] ?? 0;
        $fileName = $_POST['file_name'] ?? 'itinerary.pdf';
        
        $result = $this->documentTripService->generatePDF($itineraryId, $fileName);
        return $this->json($result);
    }
    
    /**
     * Subscribe to updates
     */
    public function subscribe() {
        $userId = $_SESSION['user_id'] ?? null;
        
        if (!$userId) {
            return $this->json(['success' => false, 'error' => 'Not logged in']);
        }
        
        $channel = $_POST['channel'] ?? '';
        $subscriptionType = $_POST['subscription_type'] ?? '';
        $referenceId = $_POST['reference_id'] ?? null;
        
        $result = $this->documentTripService->subscribe($userId, $channel, $subscriptionType, $referenceId);
        return $this->json($result);
    }
}
