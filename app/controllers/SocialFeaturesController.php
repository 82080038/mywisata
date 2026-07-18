<?php
namespace App\Controllers;

use App\Services\SocialFeaturesService;

class SocialFeaturesController extends Controller {
    private $socialFeaturesService;
    
    public function __construct() {
        $this->socialFeaturesService = new SocialFeaturesService();
    }
    
    /**
     * Create group trip
     */
    public function createGroupTrip() {
        $userId = $_SESSION['user_id'] ?? null;
        
        if (!$userId) {
            return $this->redirect('auth/login');
        }
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = [
                'trip_name' => $_POST['trip_name'] ?? '',
                'created_by' => $userId,
                'destination_id' => $_POST['destination_id'] ?? null,
                'start_date' => $_POST['start_date'] ?? null,
                'end_date' => $_POST['end_date'] ?? null,
                'max_participants' => $_POST['max_participants'] ?? null,
                'is_public' => $_POST['is_public'] ?? 0,
                'description' => $_POST['description'] ?? null,
                'status' => 'planning'
            ];
            
            $result = $this->socialFeaturesService->createGroupTrip($data);
            
            if ($result['success']) {
                Session::flash('success', 'Group trip created successfully');
                return $this->redirect('social-features/group-trips');
            } else {
                Session::flash('error', $result['error']);
            }
        }
        
        $data = [
            'trips' => [], // Would fetch from model
            'destinations' => [] // Would fetch from model
        ];
        $this->view('social_features/group_trips', $data);
    }
    
    /**
     * Group trips index
     */
    public function groupTrips() {
        $userId = $_SESSION['user_id'] ?? null;
        
        if (!$userId) {
            return $this->redirect('auth/login');
        }
        
        $data = [
            'trips' => [], // Would fetch from model
            'destinations' => [] // Would fetch from model
        ];
        $this->view('social_features/group_trips', $data);
    }
    
    /**
     * Invite participant
     */
    public function inviteParticipant() {
        $tripId = $_POST['trip_id'] ?? 0;
        $userId = $_POST['user_id'] ?? 0;
        $role = $_POST['role'] ?? 'participant';
        
        $result = $this->socialFeaturesService->inviteParticipant($tripId, $userId, $role);
        return $this->json($result);
    }
    
    /**
     * Accept invitation
     */
    public function acceptInvitation() {
        $userId = $_SESSION['user_id'] ?? null;
        
        if (!$userId) {
            return $this->json(['success' => false, 'error' => 'Not logged in']);
        }
        
        $tripId = $_POST['trip_id'] ?? 0;
        $result = $this->socialFeaturesService->acceptInvitation($tripId, $userId);
        return $this->json($result);
    }
    
    /**
     * Get group trip details
     */
    public function groupTripDetails() {
        $tripId = $_GET['trip_id'] ?? 0;
        $details = $this->socialFeaturesService->getGroupTripDetails($tripId);
        return $this->json(['success' => true, 'data' => $details]);
    }
    
    /**
     * Create shared wishlist
     */
    public function createWishlist() {
        $userId = $_SESSION['user_id'] ?? null;
        
        if (!$userId) {
            return $this->redirect('auth/login');
        }
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = [
                'wishlist_name' => $_POST['wishlist_name'] ?? '',
                'created_by' => $userId,
                'is_public' => $_POST['is_public'] ?? 0,
                'description' => $_POST['description'] ?? null
            ];
            
            $result = $this->socialFeaturesService->createSharedWishlist($data);
            
            if ($result['success']) {
                Session::flash('success', 'Wishlist created successfully');
                return $this->redirect('social-features/wishlists');
            } else {
                Session::flash('error', $result['error']);
            }
        }
        
        $data = [
            'wishlists' => [] // Would fetch from model
        ];
        $this->view('social_features/wishlists', $data);
    }
    
    /**
     * Wishlists index
     */
    public function wishlists() {
        $userId = $_SESSION['user_id'] ?? null;
        
        if (!$userId) {
            return $this->redirect('auth/login');
        }
        
        $data = [
            'wishlists' => [] // Would fetch from model
        ];
        $this->view('social_features/wishlists', $data);
    }
    
    /**
     * Add to wishlist
     */
    public function addToWishlist() {
        $wishlistId = $_POST['wishlist_id'] ?? 0;
        $destinationId = $_POST['destination_id'] ?? 0;
        $userId = $_SESSION['user_id'] ?? null;
        
        if (!$userId) {
            return $this->json(['success' => false, 'error' => 'Not logged in']);
        }
        
        $extraData = [
            'notes' => $_POST['notes'] ?? null,
            'priority' => $_POST['priority'] ?? 'medium'
        ];
        
        $result = $this->socialFeaturesService->addToWishlist($wishlistId, $destinationId, $userId, $extraData);
        return $this->json($result);
    }
    
    /**
     * Add collaborator
     */
    public function addCollaborator() {
        $wishlistId = $_POST['wishlist_id'] ?? 0;
        $userId = $_POST['user_id'] ?? 0;
        $invitedBy = $_SESSION['user_id'] ?? null;
        $permission = $_POST['permission'] ?? 'view';
        
        if (!$invitedBy) {
            return $this->json(['success' => false, 'error' => 'Not logged in']);
        }
        
        $result = $this->socialFeaturesService->addCollaborator($wishlistId, $userId, $invitedBy, $permission);
        return $this->json($result);
    }
    
    /**
     * Get wishlist details
     */
    public function wishlistDetails() {
        $wishlistId = $_GET['wishlist_id'] ?? 0;
        $details = $this->socialFeaturesService->getWishlistDetails($wishlistId);
        return $this->json(['success' => true, 'data' => $details]);
    }
    
    /**
     * Create split payment group
     */
    public function createPaymentGroup() {
        $userId = $_SESSION['user_id'] ?? null;
        
        if (!$userId) {
            return $this->json(['success' => false, 'error' => 'Not logged in']);
        }
        
        $data = [
            'group_name' => $_POST['group_name'] ?? '',
            'created_by' => $userId,
            'booking_id' => $_POST['booking_id'] ?? null,
            'total_amount' => $_POST['total_amount'] ?? 0,
            'currency' => 'IDR',
            'status' => 'active',
            'settlement_deadline' => $_POST['settlement_deadline'] ?? null
        ];
        
        $result = $this->socialFeaturesService->createSplitPaymentGroup($data);
        return $this->json($result);
    }
    
    /**
     * Add payment member
     */
    public function addPaymentMember() {
        $groupId = $_POST['group_id'] ?? 0;
        $userId = $_POST['user_id'] ?? 0;
        $shareAmount = $_POST['share_amount'] ?? 0;
        
        $result = $this->socialFeaturesService->addPaymentMember($groupId, $userId, $shareAmount);
        return $this->json($result);
    }
    
    /**
     * Record payment
     */
    public function recordPayment() {
        $memberId = $_POST['member_id'] ?? 0;
        $amount = $_POST['amount'] ?? 0;
        
        $result = $this->socialFeaturesService->recordPayment($memberId, $amount);
        return $this->json($result);
    }
    
    /**
     * Get payment group details
     */
    public function paymentGroupDetails() {
        $groupId = $_GET['group_id'] ?? 0;
        $details = $this->socialFeaturesService->getPaymentGroupDetails($groupId);
        return $this->json(['success' => true, 'data' => $details]);
    }
    
    /**
     * Create trip album
     */
    public function createAlbum() {
        $userId = $_SESSION['user_id'] ?? null;
        
        if (!$userId) {
            return $this->redirect('auth/login');
        }
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = [
                'group_trip_id' => $_POST['group_trip_id'] ?? null,
                'album_name' => $_POST['album_name'] ?? '',
                'created_by' => $userId,
                'description' => $_POST['description'] ?? null,
                'is_public' => $_POST['is_public'] ?? 0
            ];
            
            $result = $this->socialFeaturesService->createTripAlbum($data);
            
            if ($result['success']) {
                Session::flash('success', 'Album created successfully');
                return $this->redirect('social-features/albums');
            } else {
                Session::flash('error', $result['error']);
            }
        }
        
        $data = [
            'albums' => [], // Would fetch from model
            'group_trips' => [] // Would fetch from model
        ];
        $this->view('social_features/albums', $data);
    }
    
    /**
     * Albums index
     */
    public function albums() {
        $userId = $_SESSION['user_id'] ?? null;
        
        if (!$userId) {
            return $this->redirect('auth/login');
        }
        
        $data = [
            'albums' => [], // Would fetch from model
            'group_trips' => [] // Would fetch from model
        ];
        $this->view('social_features/albums', $data);
    }
    
    /**
     * Add photo
     */
    public function addPhoto() {
        $userId = $_SESSION['user_id'] ?? null;
        
        if (!$userId) {
            return $this->json(['success' => false, 'error' => 'Not logged in']);
        }
        
        $albumId = $_POST['album_id'] ?? 0;
        $filePath = $_FILES['photo']['tmp_name'] ?? '';
        
        if (!$filePath) {
            return $this->json(['success' => false, 'error' => 'No photo uploaded']);
        }
        
        $extraData = [
            'caption' => $_POST['caption'] ?? null,
            'location' => $_POST['location'] ?? null,
            'latitude' => $_POST['latitude'] ?? null,
            'longitude' => $_POST['longitude'] ?? null
        ];
        
        $result = $this->socialFeaturesService->addPhoto($albumId, $userId, $filePath, $extraData);
        return $this->json($result);
    }
    
    /**
     * Add comment
     */
    public function addComment() {
        $userId = $_SESSION['user_id'] ?? null;
        
        if (!$userId) {
            return $this->json(['success' => false, 'error' => 'Not logged in']);
        }
        
        $photoId = $_POST['photo_id'] ?? 0;
        $comment = $_POST['comment'] ?? '';
        
        $result = $this->socialFeaturesService->addComment($photoId, $userId, $comment);
        return $this->json($result);
    }
    
    /**
     * Get album details
     */
    public function albumDetails() {
        $albumId = $_GET['album_id'] ?? 0;
        $details = $this->socialFeaturesService->getAlbumDetails($albumId);
        return $this->json(['success' => true, 'data' => $details]);
    }
}
