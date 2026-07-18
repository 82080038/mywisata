<?php
namespace App\Services;

use App\Models\GroupTrip;
use App\Models\GroupTripParticipant;
use App\Models\SharedWishlist;
use App\Models\SharedWishlistItem;
use App\Models\SharedWishlistCollaborator;
use App\Models\SplitPaymentGroup;
use App\Models\SplitPaymentMember;
use App\Models\TripAlbum;
use App\Models\TripAlbumPhoto;
use App\Models\TripAlbumComment;

/**
 * Social Features Service
 * 
 * Service for group trip planning, shared wishlists, split payments, and trip albums
 * 
 * @package App\Services
 */
class SocialFeaturesService {
    private $groupTrip;
    private $groupTripParticipant;
    private $sharedWishlist;
    private $sharedWishlistItem;
    private $sharedWishlistCollaborator;
    private $splitPaymentGroup;
    private $splitPaymentMember;
    private $tripAlbum;
    private $tripAlbumPhoto;
    private $tripAlbumComment;
    
    public function __construct() {
        $this->groupTrip = new GroupTrip();
        $this->groupTripParticipant = new GroupTripParticipant();
        $this->sharedWishlist = new SharedWishlist();
        $this->sharedWishlistItem = new SharedWishlistItem();
        $this->sharedWishlistCollaborator = new SharedWishlistCollaborator();
        $this->splitPaymentGroup = new SplitPaymentGroup();
        $this->splitPaymentMember = new SplitPaymentMember();
        $this->tripAlbum = new TripAlbum();
        $this->tripAlbumPhoto = new TripAlbumPhoto();
        $this->tripAlbumComment = new TripAlbumComment();
    }
    
    /**
     * Create group trip
     * 
     * @param array $data Trip data
     * @return array Result
     */
    public function createGroupTrip($data) {
        $tripId = $this->groupTrip->create($data);
        
        if (!$tripId) {
            return ['success' => false, 'error' => 'Failed to create group trip'];
        }
        
        // Add creator as organizer
        $this->groupTripParticipant->create([
            'group_trip_id' => $tripId,
            'user_id' => $data['created_by'],
            'role' => 'organizer',
            'status' => 'accepted'
        ]);
        
        return ['success' => true, 'trip_id' => $tripId];
    }
    
    /**
     * Invite participant to group trip
     * 
     * @param int $tripId Trip ID
     * @param int $userId User ID
     * @param string $role Role
     * @return array Result
     */
    public function inviteParticipant($tripId, $userId, $role = 'participant') {
        $existing = $this->groupTripParticipant->getByTripId($tripId);
        
        foreach ($existing as $participant) {
            if ($participant['user_id'] == $userId) {
                return ['success' => false, 'error' => 'User already in trip'];
            }
        }
        
        $id = $this->groupTripParticipant->create([
            'group_trip_id' => $tripId,
            'user_id' => $userId,
            'role' => $role,
            'status' => 'invited'
        ]);
        
        if ($id) {
            return ['success' => true, 'participant_id' => $id];
        }
        
        return ['success' => false, 'error' => 'Failed to invite participant'];
    }
    
    /**
     * Accept group trip invitation
     * 
     * @param int $tripId Trip ID
     * @param int $userId User ID
     * @return array Result
     */
    public function acceptInvitation($tripId, $userId) {
        $result = $this->groupTripParticipant->updateStatus($tripId, $userId, 'accepted');
        
        if ($result) {
            return ['success' => true];
        }
        
        return ['success' => false, 'error' => 'Failed to accept invitation'];
    }
    
    /**
     * Create shared wishlist
     * 
     * @param array $data Wishlist data
     * @return array Result
     */
    public function createSharedWishlist($data) {
        $wishlistId = $this->sharedWishlist->create($data);
        
        if ($wishlistId) {
            return ['success' => true, 'wishlist_id' => $wishlistId];
        }
        
        return ['success' => false, 'error' => 'Failed to create wishlist'];
    }
    
    /**
     * Add destination to wishlist
     * 
     * @param int $wishlistId Wishlist ID
     * @param int $destinationId Destination ID
     * @param int $userId User ID
     * @param array $extraData Extra data (notes, priority)
     * @return array Result
     */
    public function addToWishlist($wishlistId, $destinationId, $userId, $extraData = []) {
        $data = array_merge([
            'wishlist_id' => $wishlistId,
            'destination_id' => $destinationId,
            'added_by' => $userId
        ], $extraData);
        
        $id = $this->sharedWishlistItem->create($data);
        
        if ($id) {
            return ['success' => true, 'item_id' => $id];
        }
        
        return ['success' => false, 'error' => 'Failed to add to wishlist'];
    }
    
    /**
     * Add collaborator to wishlist
     * 
     * @param int $wishlistId Wishlist ID
     * @param int $userId User ID
     * @param int $invitedBy User ID who invited
     * @param string $permission Permission
     * @return array Result
     */
    public function addCollaborator($wishlistId, $userId, $invitedBy, $permission = 'view') {
        $id = $this->sharedWishlistCollaborator->create([
            'wishlist_id' => $wishlistId,
            'user_id' => $userId,
            'permission' => $permission,
            'invited_by' => $invitedBy,
            'status' => 'invited'
        ]);
        
        if ($id) {
            return ['success' => true, 'collaborator_id' => $id];
        }
        
        return ['success' => false, 'error' => 'Failed to add collaborator'];
    }
    
    /**
     * Create split payment group
     * 
     * @param array $data Payment group data
     * @return array Result
     */
    public function createSplitPaymentGroup($data) {
        $groupId = $this->splitPaymentGroup->create($data);
        
        if (!$groupId) {
            return ['success' => false, 'error' => 'Failed to create payment group'];
        }
        
        // Add creator as member
        $shareAmount = $data['total_amount'] / 1; // Will be updated when more members added
        $this->splitPaymentMember->create([
            'payment_group_id' => $groupId,
            'user_id' => $data['created_by'],
            'share_amount' => $shareAmount,
            'paid_amount' => 0,
            'status' => 'pending'
        ]);
        
        return ['success' => true, 'group_id' => $groupId];
    }
    
    /**
     * Add member to split payment group
     * 
     * @param int $groupId Group ID
     * @param int $userId User ID
     * @param float $shareAmount Share amount
     * @return array Result
     */
    public function addPaymentMember($groupId, $userId, $shareAmount) {
        $id = $this->splitPaymentMember->create([
            'payment_group_id' => $groupId,
            'user_id' => $userId,
            'share_amount' => $shareAmount,
            'paid_amount' => 0,
            'status' => 'pending'
        ]);
        
        if ($id) {
            // Update total members count
            $group = $this->splitPaymentGroup->getById($groupId);
            $this->splitPaymentGroup->update($groupId, [
                'total_recipients' => $group['total_recipients'] + 1
            ]);
            
            return ['success' => true, 'member_id' => $id];
        }
        
        return ['success' => false, 'error' => 'Failed to add member'];
    }
    
    /**
     * Record payment for member
     * 
     * @param int $memberId Member ID
     * @param float $amount Amount paid
     * @return array Result
     */
    public function recordPayment($memberId, $amount) {
        $result = $this->splitPaymentMember->updatePayment($memberId, $amount);
        
        if ($result) {
            // Check if all members have paid
            $member = $this->splitPaymentMember->getById($memberId);
            $group = $this->splitPaymentGroup->getById($member['payment_group_id']);
            $members = $this->splitPaymentMember->getByGroupId($group['id']);
            
            $allPaid = true;
            foreach ($members as $m) {
                if ($m['status'] !== 'settled') {
                    $allPaid = false;
                    break;
                }
            }
            
            if ($allPaid) {
                $this->splitPaymentGroup->updateStatus($group['id'], 'settled');
            }
            
            return ['success' => true];
        }
        
        return ['success' => false, 'error' => 'Failed to record payment'];
    }
    
    /**
     * Create trip album
     * 
     * @param array $data Album data
     * @return array Result
     */
    public function createTripAlbum($data) {
        $albumId = $this->tripAlbum->create($data);
        
        if ($albumId) {
            return ['success' => true, 'album_id' => $albumId];
        }
        
        return ['success' => false, 'error' => 'Failed to create album'];
    }
    
    /**
     * Add photo to album
     * 
     * @param int $albumId Album ID
     * @param int $userId User ID
     * @param string $filePath File path
     * @param array $extraData Extra data (caption, location, etc.)
     * @return array Result
     */
    public function addPhoto($albumId, $userId, $filePath, $extraData = []) {
        $data = array_merge([
            'album_id' => $albumId,
            'uploaded_by' => $userId,
            'file_path' => $filePath
        ], $extraData);
        
        $photoId = $this->tripAlbumPhoto->create($data);
        
        if ($photoId) {
            // Update album cover if first photo
            $album = $this->tripAlbum->getById($albumId);
            if (!$album['cover_photo']) {
                $this->tripAlbum->update($albumId, ['cover_photo' => $filePath]);
            }
            
            return ['success' => true, 'photo_id' => $photoId];
        }
        
        return ['success' => false, 'error' => 'Failed to add photo'];
    }
    
    /**
     * Add comment to photo
     * 
     * @param int $photoId Photo ID
     * @param int $userId User ID
     * @param string $comment Comment text
     * @return array Result
     */
    public function addComment($photoId, $userId, $comment) {
        $id = $this->tripAlbumComment->create([
            'photo_id' => $photoId,
            'user_id' => $userId,
            'comment' => $comment
        ]);
        
        if ($id) {
            return ['success' => true, 'comment_id' => $id];
        }
        
        return ['success' => false, 'error' => 'Failed to add comment'];
    }
    
    /**
     * Get group trip details
     * 
     * @param int $tripId Trip ID
     * @return array Trip details
     */
    public function getGroupTripDetails($tripId) {
        $trip = $this->groupTrip->getById($tripId);
        $participants = $this->groupTripParticipant->getByTripId($tripId);
        
        return [
            'trip' => $trip,
            'participants' => $participants
        ];
    }
    
    /**
     * Get shared wishlist details
     * 
     * @param int $wishlistId Wishlist ID
     * @return array Wishlist details
     */
    public function getWishlistDetails($wishlistId) {
        $wishlist = $this->sharedWishlist->getById($wishlistId);
        $items = $this->sharedWishlistItem->getByWishlistId($wishlistId);
        $collaborators = $this->sharedWishlistCollaborator->getByWishlistId($wishlistId);
        
        return [
            'wishlist' => $wishlist,
            'items' => $items,
            'collaborators' => $collaborators
        ];
    }
    
    /**
     * Get split payment group details
     * 
     * @param int $groupId Group ID
     * @return array Group details
     */
    public function getPaymentGroupDetails($groupId) {
        $group = $this->splitPaymentGroup->getById($groupId);
        $members = $this->splitPaymentMember->getByGroupId($groupId);
        
        $totalPaid = 0;
        foreach ($members as $member) {
            $totalPaid += $member['paid_amount'];
        }
        
        return [
            'group' => $group,
            'members' => $members,
            'total_paid' => $totalPaid,
            'remaining' => $group['total_amount'] - $totalPaid
        ];
    }
    
    /**
     * Get trip album details
     * 
     * @param int $albumId Album ID
     * @return array Album details
     */
    public function getAlbumDetails($albumId) {
        $album = $this->tripAlbum->getById($albumId);
        $photos = $this->tripAlbumPhoto->getByAlbumId($albumId);
        
        foreach ($photos as &$photo) {
            $photo['comments'] = $this->tripAlbumComment->getByPhotoId($photo['id']);
        }
        
        return [
            'album' => $album,
            'photos' => $photos
        ];
    }
}
