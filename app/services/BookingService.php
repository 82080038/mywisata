<?php
/**
 * MyWisata Application - Booking Service
 * 
 * Business logic layer for booking operations.
 * 
 * @package MyWisata
 * @version 1.0.0
 * @since 2026-07-16
 */

class BookingService {
    private $bookingModel;
    private $tourGuideModel;
    private $transactionModel;
    private $notificationModel;
    
    /**
     * Constructor
     */
    public function __construct() {
        $this->bookingModel = new Booking();
        $this->tourGuideModel = new TourGuide();
        $this->transactionModel = new Transaction();
        $this->notificationModel = new Notification();
    }
    
    /**
     * Create new booking with full business logic
     * 
     * @param array $data Booking data
     * @return array Result with booking ID and status
     */
    public function createBooking($data) {
        $db = Database::getInstance();
        
        try {
            $db->beginTransaction();
            
            // Validate booking data
            $errors = $this->bookingModel->validate($data);
            if (!empty($errors)) {
                return ['success' => false, 'errors' => $errors];
            }
            
            // Check guide availability
            $guide = $this->tourGuideModel->findById($data['guide_id']);
            if (!$guide) {
                return ['success' => false, 'message' => 'Tour guide tidak ditemukan'];
            }
            
            // Calculate end time
            $startTime = DateTime::createFromFormat('H:i', $data['booking_time']);
            $endTime = clone $startTime;
            $endTime->modify('+' . $data['duration_hours'] . ' hours');
            
            // Check availability
            if (!$this->tourGuideModel->checkAvailability(
                $data['guide_id'], 
                $data['booking_date'], 
                $data['booking_time'], 
                $endTime->format('H:i')
            )) {
                return ['success' => false, 'message' => 'Tour guide tidak tersedia pada waktu tersebut'];
            }
            
            // Calculate total amount
            $totalAmount = $guide['hourly_rate'] * $data['duration_hours'];
            
            // Generate booking code
            $bookingCode = 'BK' . date('YmdHis') . rand(1000, 9999);
            
            // Create booking
            $bookingData = array_merge($data, [
                'booking_code' => $bookingCode,
                'total_amount' => $totalAmount
            ]);
            
            $bookingId = $this->bookingModel->create($bookingData);
            
            if (!$bookingId) {
                throw new Exception('Failed to create booking');
            }
            
            // Reserve availability
            $this->tourGuideModel->reserveAvailability(
                $data['guide_id'], 
                $data['booking_date'], 
                $data['booking_time'], 
                $endTime->format('H:i')
            );
            
            // Create transaction
            $transactionCode = 'TX' . date('YmdHis') . rand(1000, 9999);
            $transactionData = [
                'transaction_code' => $transactionCode,
                'user_id' => $data['user_id'],
                'type' => 'booking_guide',
                'reference_id' => $bookingId,
                'gross_amount' => $totalAmount,
                'discount' => 0,
                'net_amount' => $totalAmount,
                'payment_method' => 'transfer',
                'payment_status' => 'pending'
            ];
            
            $transactionId = $this->transactionModel->insert($transactionData);
            
            if (!$transactionId) {
                throw new Exception('Failed to create transaction');
            }
            
            // Update booking with transaction ID
            $this->bookingModel->update($bookingId, ['transaction_id' => $transactionId]);
            
            // Send notification to guide
            $this->notificationModel->create([
                'user_id' => $guide['user_id'],
                'type' => 'booking',
                'title' => 'Booking Baru',
                'message' => 'Anda mendapat booking baru. Silakan cek dashboard Anda.',
                'link' => 'tourguide/bookings'
            ]);
            
            $db->commit();
            
            return [
                'success' => true,
                'booking_id' => $bookingId,
                'booking_code' => $bookingCode,
                'transaction_id' => $transactionId,
                'transaction_code' => $transactionCode,
                'total_amount' => $totalAmount
            ];
            
        } catch (Exception $e) {
            $db->rollBack();
            Logger::error('Booking service error', ['error' => $e->getMessage()]);
            return ['success' => false, 'message' => 'Terjadi kesalahan saat membuat booking'];
        }
    }
    
    /**
     * Cancel booking with business logic
     * 
     * @param int $bookingId Booking ID
     * @param string $reason Cancellation reason
     * @return array Result
     */
    public function cancelBooking($bookingId, $reason = null) {
        $db = Database::getInstance();
        
        try {
            $db->beginTransaction();
            
            $booking = $this->bookingModel->findById($bookingId);
            if (!$booking) {
                return ['success' => false, 'message' => 'Booking tidak ditemukan'];
            }
            
            // Check if booking can be cancelled
            if (!in_array($booking['status'], ['pending', 'confirmed'])) {
                return ['success' => false, 'message' => 'Booking tidak dapat dibatalkan'];
            }
            
            // Check cancellation policy (24 hours before)
            $bookingDateTime = new DateTime($booking['booking_date'] . ' ' . $booking['booking_time']);
            $now = new DateTime();
            $diff = $now->diff($bookingDateTime);
            
            if ($diff->h < 24 && $diff->d == 0) {
                return ['success' => false, 'message' => 'Pembatalan hanya dapat dilakukan 24 jam sebelum jadwal'];
            }
            
            // Update booking status
            $this->bookingModel->updateStatus($bookingId, 'cancelled');
            
            // Release availability
            $this->tourGuideModel->releaseAvailability(
                $booking['guide_id'],
                $booking['booking_date'],
                $booking['booking_time']
            );
            
            // Process refund if payment was made
            if ($booking['transaction_id']) {
                $transaction = $this->transactionModel->findById($booking['transaction_id']);
                if ($transaction && $transaction['payment_status'] === 'paid') {
                    // Create refund transaction
                    $refundData = [
                        'transaction_code' => 'RF' . date('YmdHis') . rand(1000, 9999),
                        'user_id' => $booking['user_id'],
                        'type' => 'refund',
                        'reference_id' => $booking['transaction_id'],
                        'gross_amount' => $transaction['net_amount'],
                        'discount' => 0,
                        'net_amount' => $transaction['net_amount'],
                        'payment_method' => $transaction['payment_method'],
                        'payment_status' => 'pending'
                    ];
                    $this->transactionModel->insert($refundData);
                }
            }
            
            // Send notification to guide
            $guide = $this->tourGuideModel->findById($booking['guide_id']);
            $this->notificationModel->create([
                'user_id' => $guide['user_id'],
                'type' => 'booking',
                'title' => 'Booking Dibatalkan',
                'message' => 'Booking telah dibatalkan oleh wisatawan.',
                'link' => 'tourguide/bookings'
            ]);
            
            $db->commit();
            
            return ['success' => true, 'message' => 'Booking berhasil dibatalkan'];
            
        } catch (Exception $e) {
            $db->rollBack();
            Logger::error('Cancel booking error', ['booking_id' => $bookingId, 'error' => $e->getMessage()]);
            return ['success' => false, 'message' => 'Terjadi kesalahan saat membatalkan booking'];
        }
    }
}
