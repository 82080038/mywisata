<?php
/**
 * MyWisata Application - Review Controller
 * 
 * Handles review and rating operations for all content types.
 * 
 * @package MyWisata
 * @version 1.0.0
 * @since 2026-07-16
 */

class ReviewController extends Controller {
    
    private $reviewModel;
    
    public function __construct() {
        parent::__construct();
        $this->reviewModel = $this->model('Review');
    }
    
    /**
     * Add a new review
     */
    public function add() {
        Middleware::requireAuth();
        
        if (!$this->isAjax()) {
            $this->redirect('home');
        }
        
        // Verify CSRF
        if (!$this->validateCsrf()) {
            $this->json(['status' => 'error', 'message' => 'CSRF token mismatch'], 419);
        }
        
        $userId = Session::get('user_id');
        $reviewableType = $this->post('reviewable_type'); // destination, hotel, restaurant, event, tour_guide
        $reviewableId = $this->post('reviewable_id');
        $rating = $this->post('rating');
        $comment = $this->post('comment');
        
        // Validate input
        if (empty($reviewableType) || empty($reviewableId)) {
            $this->json(['status' => 'error', 'message' => 'Data tidak valid'], 400);
        }
        
        if (!in_array($reviewableType, ['destination', 'hotel', 'restaurant', 'event', 'tour_guide'])) {
            $this->json(['status' => 'error', 'message' => 'Tipe review tidak valid'], 400);
        }
        
        if (empty($rating) || $rating < 1 || $rating > 5) {
            $this->json(['status' => 'error', 'message' => 'Rating harus antara 1-5'], 400);
        }
        
        if (empty($comment) || strlen($comment) < 10) {
            $this->json(['status' => 'error', 'message' => 'Komentar minimal 10 karakter'], 400);
        }
        
        // Check if user already reviewed this item
        if ($this->reviewModel->hasUserReviewed($userId, $reviewableType, $reviewableId)) {
            $this->json(['status' => 'error', 'message' => 'Anda sudah memberikan review untuk item ini'], 400);
        }
        
        // Create review
        $reviewId = $this->reviewModel->create([
            'user_id' => $userId,
            'reviewable_type' => $reviewableType,
            'reviewable_id' => $reviewableId,
            'rating' => $rating,
            'comment' => $comment
        ]);
        
        if ($reviewId) {
            // Update rating for the item
            $this->updateItemRating($reviewableType, $reviewableId);
            
            Logger::audit('ADD_REVIEW', 'reviews', "Added review for {$reviewableType} ID: {$reviewableId}", [], [
                'rating' => $rating,
                'comment' => $comment
            ]);
            
            $this->json([
                'status' => 'success',
                'message' => 'Review berhasil ditambahkan',
                'review_id' => $reviewId
            ]);
        } else {
            $this->json(['status' => 'error', 'message' => 'Gagal menambahkan review'], 500);
        }
    }
    
    /**
     * Update an existing review
     */
    public function update() {
        Middleware::requireAuth();
        
        if (!$this->isAjax()) {
            $this->redirect('home');
        }
        
        // Verify CSRF
        if (!$this->validateCsrf()) {
            $this->json(['status' => 'error', 'message' => 'CSRF token mismatch'], 419);
        }
        
        $userId = Session::get('user_id');
        $reviewId = $this->post('review_id');
        $rating = $this->post('rating');
        $comment = $this->post('comment');
        
        // Get existing review
        $review = $this->reviewModel->findById($reviewId);
        
        if (!$review) {
            $this->json(['status' => 'error', 'message' => 'Review tidak ditemukan'], 404);
        }
        
        // Check ownership
        if ($review['user_id'] != $userId) {
            $this->json(['status' => 'error', 'message' => 'Anda tidak memiliki akses'], 403);
        }
        
        // Validate input
        if (empty($rating) || $rating < 1 || $rating > 5) {
            $this->json(['status' => 'error', 'message' => 'Rating harus antara 1-5'], 400);
        }
        
        if (empty($comment) || strlen($comment) < 10) {
            $this->json(['status' => 'error', 'message' => 'Komentar minimal 10 karakter'], 400);
        }
        
        // Update review
        $updated = $this->reviewModel->update($reviewId, [
            'rating' => $rating,
            'comment' => $comment
        ]);
        
        if ($updated) {
            // Update rating for the item
            $this->updateItemRating($review['reviewable_type'], $review['reviewable_id']);
            
            Logger::audit('UPDATE_REVIEW', 'reviews', "Updated review ID: {$reviewId}", [], [
                'rating' => $rating,
                'comment' => $comment
            ]);
            
            $this->json([
                'status' => 'success',
                'message' => 'Review berhasil diperbarui'
            ]);
        } else {
            $this->json(['status' => 'error', 'message' => 'Gagal memperbarui review'], 500);
        }
    }
    
    /**
     * Delete a review
     */
    public function delete() {
        Middleware::requireAuth();
        
        if (!$this->isAjax()) {
            $this->redirect('home');
        }
        
        // Verify CSRF
        if (!$this->validateCsrf()) {
            $this->json(['status' => 'error', 'message' => 'CSRF token mismatch'], 419);
        }
        
        $userId = Session::get('user_id');
        $reviewId = $this->post('review_id');
        
        // Get existing review
        $review = $this->reviewModel->findById($reviewId);
        
        if (!$review) {
            $this->json(['status' => 'error', 'message' => 'Review tidak ditemukan'], 404);
        }
        
        // Check ownership or admin
        if ($review['user_id'] != $userId && !Middleware::hasRole('admin')) {
            $this->json(['status' => 'error', 'message' => 'Anda tidak memiliki akses'], 403);
        }
        
        // Delete review
        $deleted = $this->reviewModel->delete($reviewId);
        
        if ($deleted) {
            // Update rating for the item
            $this->updateItemRating($review['reviewable_type'], $review['reviewable_id']);
            
            Logger::audit('DELETE_REVIEW', 'reviews', "Deleted review ID: {$reviewId}", [], []);
            
            $this->json([
                'status' => 'success',
                'message' => 'Review berhasil dihapus'
            ]);
        } else {
            $this->json(['status' => 'error', 'message' => 'Gagal menghapus review'], 500);
        }
    }
    
    /**
     * Flag a review for moderation
     */
    public function flag() {
        Middleware::requireAuth();
        
        if (!$this->isAjax()) {
            $this->redirect('home');
        }
        
        // Verify CSRF
        if (!$this->validateCsrf()) {
            $this->json(['status' => 'error', 'message' => 'CSRF token mismatch'], 419);
        }
        
        $userId = Session::get('user_id');
        $reviewId = $this->post('review_id');
        $reason = $this->post('reason');
        
        // Get existing review
        $review = $this->reviewModel->findById($reviewId);
        
        if (!$review) {
            $this->json(['status' => 'error', 'message' => 'Review tidak ditemukan'], 404);
        }
        
        // Check if already flagged by this user
        if ($this->reviewModel->hasUserFlagged($userId, $reviewId)) {
            $this->json(['status' => 'error', 'message' => 'Anda sudah melaporkan review ini'], 400);
        }
        
        // Flag review
        $flagged = $this->reviewModel->flag($reviewId, $userId, $reason);
        
        if ($flagged) {
            Logger::audit('FLAG_REVIEW', 'reviews', "Flagged review ID: {$reviewId}", [], [
                'reason' => $reason
            ]);
            
            $this->json([
                'status' => 'success',
                'message' => 'Review berhasil dilaporkan'
            ]);
        } else {
            $this->json(['status' => 'error', 'message' => 'Gagal melaporkan review'], 500);
        }
    }
    
    /**
     * Get reviews for an item
     */
    public function getReviews() {
        $reviewableType = $this->get('reviewable_type');
        $reviewableId = $this->get('reviewable_id');
        $page = $this->get('page', 1);
        $limit = $this->get('limit', 10);
        
        if (empty($reviewableType) || empty($reviewableId)) {
            $this->json(['status' => 'error', 'message' => 'Data tidak valid'], 400);
        }
        
        $reviews = $this->reviewModel->getByReviewable($reviewableType, $reviewableId, $page, $limit);
        $total = $this->reviewModel->countByReviewable($reviewableType, $reviewableId);
        $average = $this->reviewModel->getAverageRating($reviewableType, $reviewableId);
        
        $this->json([
            'status' => 'success',
            'data' => [
                'reviews' => $reviews,
                'total' => $total,
                'average' => $average,
                'page' => $page,
                'limit' => $limit
            ]
        ]);
    }
    
    /**
     * Get user's reviews
     */
    public function getUserReviews() {
        Middleware::requireAuth();
        
        $userId = Session::get('user_id');
        $page = $this->get('page', 1);
        $limit = $this->get('limit', 10);
        
        $reviews = $this->reviewModel->getByUser($userId, $page, $limit);
        $total = $this->reviewModel->countByUser($userId);
        
        $this->json([
            'status' => 'success',
            'data' => [
                'reviews' => $reviews,
                'total' => $total,
                'page' => $page,
                'limit' => $limit
            ]
        ]);
    }
    
    /**
     * Helper method to update item rating
     */
    private function updateItemRating($reviewableType, $reviewableId) {
        $average = $this->reviewModel->getAverageRating($reviewableType, $reviewableId);
        
        $db = Database::getInstance();
        
        switch ($reviewableType) {
            case 'destination':
                $db->query("UPDATE destinations SET rating_avg = :rating WHERE id = :id", 
                          ['rating' => $average, 'id' => $reviewableId]);
                break;
            case 'hotel':
                $db->query("UPDATE hotels SET rating_avg = :rating WHERE id = :id", 
                          ['rating' => $average, 'id' => $reviewableId]);
                break;
            case 'restaurant':
                $db->query("UPDATE restaurants SET rating_avg = :rating WHERE id = :id", 
                          ['rating' => $average, 'id' => $reviewableId]);
                break;
            case 'event':
                $db->query("UPDATE events SET rating_avg = :rating WHERE id = :id", 
                          ['rating' => $average, 'id' => $reviewableId]);
                break;
            case 'tour_guide':
                $db->query("UPDATE tour_guides SET rating_avg = :rating WHERE id = :id", 
                          ['rating' => $average, 'id' => $reviewableId]);
                break;
        }
    }
}
