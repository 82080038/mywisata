<?php
namespace App\Services;

use App\Models\DigitalWallet;
use App\Models\WalletTransaction;
use App\Models\ImportedItinerary;
use App\Models\TripTimelineEntry;
use App\Models\PDFItinerary;
use App\Models\WebsocketSubscription;

/**
 * Document & Trip Management Service
 * 
 * Service for digital wallet, itinerary import, trip timeline, and PDF itineraries
 * 
 * @package App\Services
 */
class DocumentTripService {
    private $digitalWallet;
    private $walletTransaction;
    private $importedItinerary;
    private $tripTimelineEntry;
    private $pdfItinerary;
    private $websocketSubscription;
    
    public function __construct() {
        $this->digitalWallet = new DigitalWallet();
        $this->walletTransaction = new WalletTransaction();
        $this->importedItinerary = new ImportedItinerary();
        $this->tripTimelineEntry = new TripTimelineEntry();
        $this->pdfItinerary = new PDFItinerary();
        $this->websocketSubscription = new WebsocketSubscription();
    }
    
    /**
     * Get or create wallet for user
     * 
     * @param int $userId User ID
     * @return array Wallet data
     */
    public function getWallet($userId) {
        $wallet = $this->digitalWallet->getByUserId($userId);
        
        if (!$wallet) {
            $this->digitalWallet->create([
                'user_id' => $userId,
                'balance' => 0,
                'currency' => 'IDR',
                'is_active' => 1
            ]);
            $wallet = $this->digitalWallet->getByUserId($userId);
        }
        
        return $wallet;
    }
    
    /**
     * Add funds to wallet
     * 
     * @param int $userId User ID
     * @param float $amount Amount to add
     * @param string $description Description
     * @return array Result
     */
    public function addFunds($userId, $amount, $description = null) {
        $wallet = $this->getWallet($userId);
        
        $this->digitalWallet->updateBalance($userId, $amount);
        
        $this->walletTransaction->create([
            'wallet_id' => $wallet['id'],
            'transaction_type' => 'credit',
            'amount' => $amount,
            'description' => $description,
            'balance_after' => $wallet['balance'] + $amount
        ]);
        
        return ['success' => true, 'new_balance' => $wallet['balance'] + $amount];
    }
    
    /**
     * Deduct funds from wallet
     * 
     * @param int $userId User ID
     * @param float $amount Amount to deduct
     * @param string $description Description
     * @param int $referenceId Reference ID
     * @return array Result
     */
    public function deductFunds($userId, $amount, $description = null, $referenceId = null) {
        $wallet = $this->getWallet($userId);
        
        if ($wallet['balance'] < $amount) {
            return ['success' => false, 'error' => 'Insufficient balance'];
        }
        
        $this->digitalWallet->updateBalance($userId, -$amount);
        
        $this->walletTransaction->create([
            'wallet_id' => $wallet['id'],
            'transaction_type' => 'debit',
            'amount' => $amount,
            'description' => $description,
            'reference_id' => $referenceId,
            'reference_type' => 'booking',
            'balance_after' => $wallet['balance'] - $amount
        ]);
        
        return ['success' => true, 'new_balance' => $wallet['balance'] - $amount];
    }
    
    /**
     * Get wallet transactions
     * 
     * @param int $userId User ID
     * @param int $limit Limit
     * @return array Transactions
     */
    public function getTransactions($userId, $limit = 50) {
        $wallet = $this->getWallet($userId);
        return $this->walletTransaction->getByWalletId($wallet['id'], $limit);
    }
    
    /**
     * Import itinerary from PDF
     * 
     * @param int $userId User ID
     * @param string $filename Original filename
     * @param string $filePath File path
     * @return array Result
     */
    public function importItinerary($userId, $filename, $filePath) {
        $importId = $this->importedItinerary->create([
            'user_id' => $userId,
            'original_filename' => $filename,
            'file_path' => $filePath,
            'import_status' => 'pending'
        ]);
        
        if (!$importId) {
            return ['success' => false, 'error' => 'Failed to create import record'];
        }
        
        // Parse PDF (simplified - would need actual PDF parsing library)
        $parsedData = $this->parsePDF($filePath);
        
        if ($parsedData) {
            $this->importedItinerary->updateStatus($importId, 'completed', json_encode($parsedData));
            return ['success' => true, 'import_id' => $importId, 'parsed_data' => $parsedData];
        } else {
            $this->importedItinerary->updateStatus($importId, 'failed', null, 'Failed to parse PDF');
            return ['success' => false, 'error' => 'Failed to parse PDF'];
        }
    }
    
    /**
     * Parse PDF (simplified placeholder)
     * 
     * @param string $filePath File path
     * @return array Parsed data
     */
    private function parsePDF($filePath) {
        // This would use a PDF parsing library like TCPDF or FPDI
        // For now, return placeholder data
        return [
            'title' => 'Imported Itinerary',
            'days' => [
                [
                    'day' => 1,
                    'activities' => [
                        ['time' => '09:00', 'activity' => 'Breakfast', 'location' => 'Hotel'],
                        ['time' => '10:00', 'activity' => 'City Tour', 'location' => 'Downtown']
                    ]
                ]
            ]
        ];
    }
    
    /**
     * Create trip timeline from itinerary
     * 
     * @param int $itineraryId Itinerary ID
     * @param array $timelineData Timeline data
     * @return array Result
     */
    public function createTimeline($itineraryId, $timelineData) {
        $created = 0;
        
        foreach ($timelineData as $entry) {
            $id = $this->tripTimelineEntry->create([
                'itinerary_id' => $itineraryId,
                'day_number' => $entry['day_number'],
                'time' => $entry['time'],
                'activity_type' => $entry['activity_type'],
                'title' => $entry['title'],
                'description' => $entry['description'] ?? null,
                'location' => $entry['location'] ?? null,
                'duration_minutes' => $entry['duration_minutes'] ?? null
            ]);
            
            if ($id) {
                $created++;
            }
        }
        
        return ['success' => true, 'created' => $created];
    }
    
    /**
     * Get trip timeline
     * 
     * @param int $itineraryId Itinerary ID
     * @return array Timeline
     */
    public function getTimeline($itineraryId) {
        return $this->tripTimelineEntry->getByItineraryId($itineraryId);
    }
    
    /**
     * Generate PDF itinerary
     * 
     * @param int $itineraryId Itinerary ID
     * @param string $fileName File name
     * @return array Result
     */
    public function generatePDF($itineraryId, $fileName) {
        $timeline = $this->tripTimelineEntry->getByItineraryId($itineraryId);
        
        if (empty($timeline)) {
            return ['success' => false, 'error' => 'No timeline data found'];
        }
        
        // Generate PDF (simplified - would use TCPDF or similar)
        $filePath = '/opt/lampp/htdocs/mywisata/public/uploads/itineraries/' . $fileName;
        
        $pdfId = $this->pdfItinerary->create([
            'itinerary_id' => $itineraryId,
            'file_path' => $filePath,
            'file_name' => $fileName,
            'file_size' => 0 // Would be actual file size
        ]);
        
        if ($pdfId) {
            return ['success' => true, 'pdf_id' => $pdfId, 'file_path' => $filePath];
        }
        
        return ['success' => false, 'error' => 'Failed to generate PDF'];
    }
    
    /**
     * Subscribe to real-time updates
     * 
     * @param int $userId User ID
     * @param string $channel Channel
     * @param string $subscriptionType Subscription type
     * @param int $referenceId Reference ID
     * @return array Result
     */
    public function subscribe($userId, $channel, $subscriptionType, $referenceId = null) {
        $id = $this->websocketSubscription->create([
            'user_id' => $userId,
            'channel' => $channel,
            'subscription_type' => $subscriptionType,
            'reference_id' => $referenceId
        ]);
        
        if ($id) {
            return ['success' => true, 'subscription_id' => $id];
        }
        
        return ['success' => false, 'error' => 'Failed to create subscription'];
    }
    
    /**
     * Unsubscribe from updates
     * 
     * @param int $subscriptionId Subscription ID
     * @return array Result
     */
    public function unsubscribe($subscriptionId) {
        $this->websocketSubscription->deactivate($subscriptionId);
        return ['success' => true];
    }
}
