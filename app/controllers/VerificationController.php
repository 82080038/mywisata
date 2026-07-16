<?php
/**
 * MyWisata Application - Verification Controller
 * 
 * Handles tour guide verification and document management.
 * 
 * @package MyWisata
 * @version 1.0.0
 * @since 2026-07-16
 */

class VerificationController extends Controller {
    
    private $verificationModel;
    
    public function __construct() {
        parent::__construct();
        $this->verificationModel = $this->model('Verification');
    }
    
    /**
     * Display verification form
     */
    public function index() {
        Middleware::requireAuth();
        Middleware::requireRole('tour_guide');
        
        $userId = Session::get('user_id');
        $tourGuideModel = $this->model('TourGuide');
        $guide = $tourGuideModel->findByUserId($userId);
        
        if (!$guide) {
            Session::flash('error', 'Profil tour guide belum diisi');
            $this->redirect('tourguide/profile');
        }
        
        $verification = $this->verificationModel->getByGuideId($guide['id']);
        
        $data = [
            'title' => 'Verifikasi Tour Guide - MyWisata',
            'guide' => $guide,
            'verification' => $verification,
            'csrf_token' => Middleware::csrfToken()
        ];
        
        $this->view('verification/index', $data);
    }
    
    /**
     * Submit verification documents
     */
    public function submit() {
        Middleware::requireAuth();
        Middleware::requireRole('tour_guide');
        
        if (!$this->isAjax()) {
            $this->redirect('verification');
        }
        
        // Verify CSRF
        if (!$this->validateCsrf()) {
            $this->json(['status' => 'error', 'message' => 'CSRF token mismatch'], 419);
        }
        
        $userId = Session::get('user_id');
        $tourGuideModel = $this->model('TourGuide');
        $guide = $tourGuideModel->findByUserId($userId);
        
        if (!$guide) {
            $this->json(['status' => 'error', 'message' => 'Profil tour guide tidak ditemukan'], 404);
        }
        
        // Check if already verified
        if ($guide['is_verified']) {
            $this->json(['status' => 'error', 'message' => 'Anda sudah terverifikasi'], 400);
        }
        
        // Check if there's a pending verification
        $existing = $this->verificationModel->getByGuideId($guide['id']);
        if ($existing && $existing['status'] === 'pending') {
            $this->json(['status' => 'error', 'message' => 'Verifikasi Anda sedang dalam proses'], 400);
        }
        
        // Handle file uploads
        $identityDocument = null;
        $certificationDocument = null;
        $portfolioDocument = null;
        
        if (isset($_FILES['identity_document']) && $_FILES['identity_document']['error'] === UPLOAD_ERR_OK) {
            $identityDocument = FileUpload::upload(
                $_FILES['identity_document'],
                APP_ROOT . '/public/uploads/verification/',
                ['application/pdf', 'image/jpeg', 'image/png'],
                5242880 // 5MB
            );
        }
        
        if (isset($_FILES['certification_document']) && $_FILES['certification_document']['error'] === UPLOAD_ERR_OK) {
            $certificationDocument = FileUpload::upload(
                $_FILES['certification_document'],
                APP_ROOT . '/public/uploads/verification/',
                ['application/pdf', 'image/jpeg', 'image/png'],
                5242880 // 5MB
            );
        }
        
        if (isset($_FILES['portfolio_document']) && $_FILES['portfolio_document']['error'] === UPLOAD_ERR_OK) {
            $portfolioDocument = FileUpload::upload(
                $_FILES['portfolio_document'],
                APP_ROOT . '/public/uploads/verification/',
                ['application/pdf', 'image/jpeg', 'image/png'],
                10485760 // 10MB
            );
        }
        
        // Create or update verification
        $verificationData = [
            'guide_id' => $guide['id'],
            'identity_type' => $this->post('identity_type'),
            'identity_number' => $this->post('identity_number'),
            'identity_document' => $identityDocument,
            'certification_type' => $this->post('certification_type'),
            'certification_number' => $this->post('certification_number'),
            'certification_document' => $certificationDocument,
            'portfolio_document' => $portfolioDocument,
            'experience_years' => $this->post('experience_years'),
            'languages' => $this->post('languages'),
            'specializations' => $this->post('specializations'),
            'status' => 'pending'
        ];
        
        if ($existing) {
            $verificationId = $this->verificationModel->update($existing['id'], $verificationData);
        } else {
            $verificationId = $this->verificationModel->create($verificationData);
        }
        
        if ($verificationId) {
            Logger::audit('SUBMIT_VERIFICATION', 'verifications', "Submitted verification for guide ID: {$guide['id']}", [], $verificationData);
            
            $this->json([
                'status' => 'success',
                'message' => 'Dokumen verifikasi berhasil dikirim. Tim kami akan memverifikasi dalam 2-3 hari kerja.'
            ]);
        } else {
            $this->json(['status' => 'error', 'message' => 'Gagal mengirim dokumen verifikasi'], 500);
        }
    }
    
    /**
     * Admin: List pending verifications
     */
    public function adminIndex() {
        Middleware::requireAuth();
        Middleware::requireRole('admin');
        
        $status = $this->get('status', 'pending');
        $page = $this->get('page', 1);
        $limit = $this->get('limit', 20);
        
        $verifications = $this->verificationModel->getAll($status, $page, $limit);
        $total = $this->verificationModel->countByStatus($status);
        
        $data = [
            'title' => 'Verifikasi Tour Guide - Admin',
            'verifications' => $verifications,
            'total' => $total,
            'status' => $status,
            'page' => $page,
            'limit' => $limit,
            'csrf_token' => Middleware::csrfToken()
        ];
        
        $this->view('verification/admin', $data);
    }
    
    /**
     * Admin: View verification details
     */
    public function adminView() {
        Middleware::requireAuth();
        Middleware::requireRole('admin');
        
        $verificationId = $this->get('id');
        
        if (empty($verificationId)) {
            $this->redirect('verification/admin');
        }
        
        $verification = $this->verificationModel->findById($verificationId);
        
        if (!$verification) {
            Session::flash('error', 'Verifikasi tidak ditemukan');
            $this->redirect('verification/admin');
        }
        
        $data = [
            'title' => 'Detail Verifikasi - Admin',
            'verification' => $verification,
            'csrf_token' => Middleware::csrfToken()
        ];
        
        $this->view('verification/admin-view', $data);
    }
    
    /**
     * Admin: Approve verification
     */
    public function approve() {
        Middleware::requireAuth();
        Middleware::requireRole('admin');
        
        if (!$this->isAjax()) {
            $this->redirect('verification/admin');
        }
        
        // Verify CSRF
        if (!$this->validateCsrf()) {
            $this->json(['status' => 'error', 'message' => 'CSRF token mismatch'], 419);
        }
        
        $verificationId = $this->post('verification_id');
        $notes = $this->post('notes');
        
        $verification = $this->verificationModel->findById($verificationId);
        
        if (!$verification) {
            $this->json(['status' => 'error', 'message' => 'Verifikasi tidak ditemukan'], 404);
        }
        
        if ($verification['status'] !== 'pending') {
            $this->json(['status' => 'error', 'message' => 'Verifikasi sudah diproses'], 400);
        }
        
        // Approve verification
        $approved = $this->verificationModel->approve($verificationId, $notes);
        
        if ($approved) {
            // Update guide verification status
            $tourGuideModel = $this->model('TourGuide');
            $tourGuideModel->updateVerificationStatus($verification['guide_id'], true);
            
            // Send notification to guide
            $notificationModel = $this->model('Notification');
            $notificationModel->notify(
                $verification['guide']['user_id'],
                'verification_approved',
                'Verifikasi Diterima',
                'Selamat! Profil tour guide Anda telah diverifikasi.',
                'tourguide/profile'
            );
            
            Logger::audit('APPROVE_VERIFICATION', 'verifications', "Approved verification ID: {$verificationId}", [], [
                'notes' => $notes
            ]);
            
            $this->json([
                'status' => 'success',
                'message' => 'Verifikasi berhasil disetujui'
            ]);
        } else {
            $this->json(['status' => 'error', 'message' => 'Gagal menyetujui verifikasi'], 500);
        }
    }
    
    /**
     * Admin: Reject verification
     */
    public function reject() {
        Middleware::requireAuth();
        Middleware::requireRole('admin');
        
        if (!$this->isAjax()) {
            $this->redirect('verification/admin');
        }
        
        // Verify CSRF
        if (!$this->validateCsrf()) {
            $this->json(['status' => 'error', 'message' => 'CSRF token mismatch'], 419);
        }
        
        $verificationId = $this->post('verification_id');
        $reason = $this->post('reason');
        
        if (empty($reason)) {
            $this->json(['status' => 'error', 'message' => 'Alasan penolakan wajib diisi'], 400);
        }
        
        $verification = $this->verificationModel->findById($verificationId);
        
        if (!$verification) {
            $this->json(['status' => 'error', 'message' => 'Verifikasi tidak ditemukan'], 404);
        }
        
        if ($verification['status'] !== 'pending') {
            $this->json(['status' => 'error', 'message' => 'Verifikasi sudah diproses'], 400);
        }
        
        // Reject verification
        $rejected = $this->verificationModel->reject($verificationId, $reason);
        
        if ($rejected) {
            // Send notification to guide
            $notificationModel = $this->model('Notification');
            $notificationModel->notify(
                $verification['guide']['user_id'],
                'verification_rejected',
                'Verifikasi Ditolak',
                "Verifikasi Anda ditolak. Alasan: {$reason}",
                'verification'
            );
            
            Logger::audit('REJECT_VERIFICATION', 'verifications', "Rejected verification ID: {$verificationId}", [], [
                'reason' => $reason
            ]);
            
            $this->json([
                'status' => 'success',
                'message' => 'Verifikasi berhasil ditolak'
            ]);
        } else {
            $this->json(['status' => 'error', 'message' => 'Gagal menolak verifikasi'], 500);
        }
    }
    
    /**
     * Request re-verification
     */
    public function requestReverification() {
        Middleware::requireAuth();
        Middleware::requireRole('tour_guide');
        
        if (!$this->isAjax()) {
            $this->redirect('verification');
        }
        
        // Verify CSRF
        if (!$this->validateCsrf()) {
            $this->json(['status' => 'error', 'message' => 'CSRF token mismatch'], 419);
        }
        
        $userId = Session::get('user_id');
        $tourGuideModel = $this->model('TourGuide');
        $guide = $tourGuideModel->findByUserId($userId);
        
        if (!$guide) {
            $this->json(['status' => 'error', 'message' => 'Profil tour guide tidak ditemukan'], 404);
        }
        
        $verification = $this->verificationModel->getByGuideId($guide['id']);
        
        if (!$verification || $verification['status'] !== 'rejected') {
            $this->json(['status' => 'error', 'message' => 'Tidak dapat meminta re-verifikasi'], 400);
        }
        
        // Reset status to pending
        $updated = $this->verificationModel->resetToPending($verification['id']);
        
        if ($updated) {
            Logger::audit('REQUEST_REVERIFICATION', 'verifications', "Requested re-verification for guide ID: {$guide['id']}", [], []);
            
            $this->json([
                'status' => 'success',
                'message' => 'Permintaan re-verifikasi berhasil dikirim'
            ]);
        } else {
            $this->json(['status' => 'error', 'message' => 'Gagal meminta re-verifikasi'], 500);
        }
    }
}
