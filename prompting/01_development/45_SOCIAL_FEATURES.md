# MODUL 45 — SOCIAL FEATURES

> **Modul:** Social Features untuk Enhanced User Experience  
> **Versi:** 1.0  
> **Tanggal:** 2026-07-18  
> **Tujuan:** Implementasi social features (Group Trip Planning, Shared Wishlists, Split Payments, Trip Album) untuk meningkatkan engagement

---

## 1. OBJECTIVE

Implementasi social features untuk meningkatkan user engagement dan collaborative travel planning menggunakan solusi self-hosted.

## 2. FITUR YANG AKAN DIIMPLEMENTASIKAN

### 2.1 Group Trip Planning
- Create group trips
- Invite friends/family
- Shared itinerary
- Voting system untuk activities
- Group chat

### 2.2 Shared Wishlists
- Share wishlist dengan others
- Public/private wishlist
- Share via link
- Collaborative wishlist

### 2.3 Split Payments
- Split payment untuk group bookings
- Individual payment tracking
- Payment status per participant
- Integration dengan existing payment system

### 2.4 Trip Album Sharing
- Upload photos/videos per trip
- Auto-organize by day
- Social sharing
- Comments dan likes

## 3. PREREQUISITES

### 3.1 Software Requirements
- Existing file upload system (sudah ada)
- Existing favorites system (sudah ada)
- Existing payment system (sudah ada)

### 3.2 Configuration
Baca `prompting/config.json` untuk environment configuration.

## 4. IMPLEMENTATION STEPS

### 4.1 Phase 1: Group Trip Planning

**Step 1: Create Group Trip Model**
```php
// app/models/GroupTrip.php
<?php
class GroupTrip extends Model {
    protected $table = 'group_trips';
    
    public function create($data) {
        $sql = "INSERT INTO group_trips 
                (name, creator_id, destination_id, start_date, end_date, status, created_at)
                VALUES (:name, :creator_id, :destination_id, :start_date, :end_date, 'planning', NOW())";
        
        $stmt = $this->db->prepare($sql);
        $result = $stmt->execute([
            ':name' => $data['name'],
            ':creator_id' => $data['creator_id'],
            ':destination_id' => $data['destination_id'],
            ':start_date' => $data['start_date'],
            ':end_date' => $data['end_date']
        ]);
        
        if ($result) {
            $groupId = $this->db->lastInsertId();
            
            // Add creator sebagai member
            $this->addMember($groupId, $data['creator_id'], 'admin');
            
            return $groupId;
        }
        
        return false;
    }
    
    public function addMember($groupId, $userId, $role = 'member') {
        $sql = "INSERT INTO group_trip_members 
                (group_id, user_id, role, status, joined_at)
                VALUES (:group_id, :user_id, :role, 'accepted', NOW())";
        
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            ':group_id' => $groupId,
            ':user_id' => $userId,
            ':role' => $role
        ]);
    }
    
    public function inviteMember($groupId, $email) {
        // Generate invite token
        $token = bin2hex(random_bytes(16));
        
        $sql = "INSERT INTO group_trip_invites 
                (group_id, email, token, status, created_at)
                VALUES (:group_id, :email, :token, 'pending', NOW())";
        
        $stmt = $this->db->prepare($sql);
        $result = $stmt->execute([
            ':group_id' => $groupId,
            ':email' => $email,
            ':token' => $token
        ]);
        
        if ($result) {
            // Send invite email
            $this->sendInviteEmail($email, $token);
        }
        
        return $result;
    }
    
    public function getMembers($groupId) {
        $sql = "SELECT gtm.*, u.name, u.email 
                FROM group_trip_members gtm
                JOIN users u ON gtm.user_id = u.id
                WHERE gtm.group_id = :group_id";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':group_id' => $groupId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    public function getItinerary($groupId) {
        $sql = "SELECT * FROM group_trip_itinerary 
                WHERE group_id = :group_id 
                ORDER BY day_number, time";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':group_id' => $groupId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    private function sendInviteEmail($email, $token) {
        $inviteLink = "http://localhost/mywisata/group-trip/join?token=" . $token;
        
        // Send email dengan invite link
        $emailService = new EmailService();
        $emailService->send($email, 'Group Trip Invitation', 
            "You're invited to join a group trip! Click here: " . $inviteLink);
    }
}
```

**Step 2: Create Database Migration**
```sql
-- database/migrations/add_group_trip_tables.sql
CREATE TABLE group_trips (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    creator_id BIGINT UNSIGNED NOT NULL,
    destination_id BIGINT UNSIGNED,
    start_date DATE NOT NULL,
    end_date DATE NOT NULL,
    status VARCHAR(20) DEFAULT 'planning',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (creator_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (destination_id) REFERENCES destinations(id) ON DELETE SET NULL,
    INDEX idx_creator_id (creator_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE group_trip_members (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    group_id BIGINT UNSIGNED NOT NULL,
    user_id BIGINT UNSIGNED NOT NULL,
    role ENUM('admin', 'member') DEFAULT 'member',
    status ENUM('accepted', 'declined', 'pending') DEFAULT 'accepted',
    joined_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (group_id) REFERENCES group_trips(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    UNIQUE KEY unique_group_user (group_id, user_id),
    INDEX idx_group_id (group_id),
    INDEX idx_user_id (user_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE group_trip_invites (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    group_id BIGINT UNSIGNED NOT NULL,
    email VARCHAR(255) NOT NULL,
    token VARCHAR(64) NOT NULL UNIQUE,
    status ENUM('pending', 'accepted', 'declined') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (group_id) REFERENCES group_trips(id) ON DELETE CASCADE,
    INDEX idx_token (token)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE group_trip_itinerary (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    group_id BIGINT UNSIGNED NOT NULL,
    day_number INT NOT NULL,
    time TIME NOT NULL,
    activity VARCHAR(255) NOT NULL,
    location VARCHAR(255),
    notes TEXT,
    created_by BIGINT UNSIGNED,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (group_id) REFERENCES group_trips(id) ON DELETE CASCADE,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_group_id (group_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE group_trip_votes (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    group_id BIGINT UNSIGNED NOT NULL,
    itinerary_id BIGINT UNSIGNED NOT NULL,
    user_id BIGINT UNSIGNED NOT NULL,
    vote ENUM('yes', 'no', 'maybe') NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (group_id) REFERENCES group_trips(id) ON DELETE CASCADE,
    FOREIGN KEY (itinerary_id) REFERENCES group_trip_itinerary(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    UNIQUE KEY unique_vote (itinerary_id, user_id),
    INDEX idx_group_id (group_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

**Step 3: Create Group Trip Controller**
```php
// app/controllers/GroupTripController.php
<?php
class GroupTripController extends Controller {
    public function index() {
        $userId = $_SESSION['user_id'];
        
        $groupTripModel = new GroupTrip();
        $myTrips = $groupTripModel->getUserTrips($userId);
        
        return $this->view('group-trip/index', [
            'trips' => $myTrips
        ]);
    }
    
    public function create() {
        return $this->view('group-trip/create');
    }
    
    public function store() {
        $data = [
            'name' => $_POST['name'] ?? '',
            'creator_id' => $_SESSION['user_id'],
            'destination_id' => $_POST['destination_id'] ?? null,
            'start_date' => $_POST['start_date'] ?? '',
            'end_date' => $_POST['end_date'] ?? ''
        ];
        
        $groupTripModel = new GroupTrip();
        $groupId = $groupTripModel->create($data);
        
        if ($groupId) {
            return $this->json([
                'status' => 'success',
                'message' => 'Group trip created',
                'group_id' => $groupId
            ]);
        }
        
        return $this->json(['status' => 'error', 'message' => 'Failed to create group trip']);
    }
    
    public function show($groupId) {
        $groupTripModel = new GroupTrip();
        $trip = $groupTripModel->getById($groupId);
        $members = $groupTripModel->getMembers($groupId);
        $itinerary = $groupTripModel->getItinerary($groupId);
        
        return $this->view('group-trip/show', [
            'trip' => $trip,
            'members' => $members,
            'itinerary' => $itinerary
        ]);
    }
    
    public function invite() {
        $groupId = $_POST['group_id'] ?? '';
        $email = $_POST['email'] ?? '';
        
        $groupTripModel = new GroupTrip();
        $result = $groupTripModel->inviteMember($groupId, $email);
        
        if ($result) {
            return $this->json(['status' => 'success', 'message' => 'Invitation sent']);
        }
        
        return $this->json(['status' => 'error', 'message' => 'Failed to send invitation']);
    }
    
    public function addItineraryItem() {
        $data = [
            'group_id' => $_POST['group_id'] ?? '',
            'day_number' => $_POST['day_number'] ?? 1,
            'time' => $_POST['time'] ?? '',
            'activity' => $_POST['activity'] ?? '',
            'location' => $_POST['location'] ?? '',
            'notes' => $_POST['notes'] ?? '',
            'created_by' => $_SESSION['user_id']
        ];
        
        $sql = "INSERT INTO group_trip_itinerary 
                (group_id, day_number, time, activity, location, notes, created_by, created_at)
                VALUES (:group_id, :day_number, :time, :activity, :location, :notes, :created_by, NOW())";
        
        $stmt = $this->db->prepare($sql);
        $result = $stmt->execute($data);
        
        if ($result) {
            return $this->json(['status' => 'success', 'message' => 'Itinerary item added']);
        }
        
        return $this->json(['status' => 'error', 'message' => 'Failed to add itinerary item']);
    }
    
    public function vote() {
        $groupId = $_POST['group_id'] ?? '';
        $itineraryId = $_POST['itinerary_id'] ?? '';
        $vote = $_POST['vote'] ?? 'yes';
        
        $sql = "INSERT INTO group_trip_votes 
                (group_id, itinerary_id, user_id, vote, created_at)
                VALUES (:group_id, :itinerary_id, :user_id, :vote, NOW())
                ON DUPLICATE KEY UPDATE vote = :vote";
        
        $stmt = $this->db->prepare($sql);
        $result = $stmt->execute([
            ':group_id' => $groupId,
            ':itinerary_id' => $itineraryId,
            ':user_id' => $_SESSION['user_id'],
            ':vote' => $vote
        ]);
        
        if ($result) {
            return $this->json(['status' => 'success', 'message' => 'Vote recorded']);
        }
        
        return $this->json(['status' => 'error', 'message' => 'Failed to record vote']);
    }
}
```

### 4.2 Phase 2: Shared Wishlists

**Step 1: Extend Favorites System**
```php
// app/models/Favorite.php
<?php
class Favorite extends Model {
    protected $table = 'favorites';
    
    // ... existing methods ...
    
    public function createShareableLink($favoriteId) {
        // Generate unique share token
        $token = bin2hex(random_bytes(16));
        
        $sql = "UPDATE favorites 
                SET share_token = :share_token, 
                    is_public = 1,
                    shared_at = NOW()
                WHERE id = :id";
        
        $stmt = $this->db->prepare($sql);
        $result = $stmt->execute([
            ':share_token' => $token,
            ':id' => $favoriteId
        ]);
        
        if ($result) {
            return 'http://localhost/mywisata/wishlist/shared/' . $token;
        }
        
        return false;
    }
    
    public function getSharedWishlist($token) {
        $sql = "SELECT f.*, u.name as user_name, d.name as destination_name
                FROM favorites f
                JOIN users u ON f.user_id = u.id
                LEFT JOIN destinations d ON f.destination_id = d.id
                WHERE f.share_token = :token AND f.is_public = 1";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':token' => $token]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    public function getUserSharedWishlists($userId) {
        $sql = "SELECT * FROM favorites 
                WHERE user_id = :user_id AND is_public = 1
                ORDER BY shared_at DESC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':user_id' => $userId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
```

**Step 2: Update Favorites Table**
```sql
-- database/migrations/update_favorites_table.sql
ALTER TABLE favorites 
ADD COLUMN share_token VARCHAR(64) UNIQUE,
ADD COLUMN is_public TINYINT(1) DEFAULT 0,
ADD COLUMN shared_at TIMESTAMP NULL,
ADD INDEX idx_share_token (share_token);
```

**Step 3: Create Shared Wishlist Controller**
```php
// app/controllers/SharedWishlistController.php
<?php
class SharedWishlistController extends Controller {
    public function share($favoriteId) {
        $favoriteModel = new Favorite();
        $shareLink = $favoriteModel->createShareableLink($favoriteId);
        
        if ($shareLink) {
            return $this->json([
                'status' => 'success',
                'share_link' => $shareLink
            ]);
        }
        
        return $this->json(['status' => 'error', 'message' => 'Failed to create share link']);
    }
    
    public function viewShared($token) {
        $favoriteModel = new Favorite();
        $wishlist = $favoriteModel->getSharedWishlist($token);
        
        return $this->view('wishlist/shared', [
            'wishlist' => $wishlist
        ]);
    }
    
    public function myShared() {
        $userId = $_SESSION['user_id'];
        
        $favoriteModel = new Favorite();
        $sharedWishlists = $favoriteModel->getUserSharedWishlists($userId);
        
        return $this->view('wishlist/my-shared', [
            'wishlists' => $sharedWishlists
        ]);
    }
}
```

### 4.3 Phase 3: Split Payments

**Step 1: Create Split Payment Model**
```php
// app/models/SplitPayment.php
<?php
class SplitPayment extends Model {
    protected $table = 'split_payments';
    
    public function createSplitPayment($bookingId, $participants) {
        $bookingModel = new Booking();
        $booking = $bookingModel->getById($bookingId);
        
        $totalAmount = $booking['total_price'];
        $splitAmount = $totalAmount / count($participants);
        
        $this->db->beginTransaction();
        
        try {
            // Create split payment record
            $sql = "INSERT INTO split_payments 
                    (booking_id, total_amount, split_amount, participant_count, status, created_at)
                    VALUES (:booking_id, :total_amount, :split_amount, :participant_count, 'pending', NOW())";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                ':booking_id' => $bookingId,
                ':total_amount' => $totalAmount,
                ':split_amount' => $splitAmount,
                ':participant_count' => count($participants)
            ]);
            
            $splitPaymentId = $this->db->lastInsertId();
            
            // Add participants
            foreach ($participants as $participant) {
                $sql = "INSERT INTO split_payment_participants 
                        (split_payment_id, user_id, amount, status, created_at)
                        VALUES (:split_payment_id, :user_id, :amount, 'pending', NOW())";
                
                $stmt = $this->db->prepare($sql);
                $stmt->execute([
                    ':split_payment_id' => $splitPaymentId,
                    ':user_id' => $participant['user_id'],
                    ':amount' => $splitAmount
                ]);
            }
            
            $this->db->commit();
            return $splitPaymentId;
        } catch (Exception $e) {
            $this->db->rollBack();
            return false;
        }
    }
    
    public function recordParticipantPayment($participantId, $amount) {
        $sql = "UPDATE split_payment_participants 
                SET paid_amount = paid_amount + :amount,
                    status = CASE 
                        WHEN paid_amount + :amount >= amount THEN 'paid' 
                        ELSE 'partial' 
                    END
                WHERE id = :id";
        
        $stmt = $this->db->prepare($sql);
        $result = $stmt->execute([
            ':amount' => $amount,
            ':id' => $participantId
        ]);
        
        if ($result) {
            // Check jika semua participants sudah paid
            $this->checkSplitPaymentCompletion($participantId);
        }
        
        return $result;
    }
    
    private function checkSplitPaymentCompletion($participantId) {
        // Get split payment ID
        $sql = "SELECT split_payment_id FROM split_payment_participants WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':id' => $participantId]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        $splitPaymentId = $result['split_payment_id'];
        
        // Check jika semua participants paid
        $sql = "SELECT COUNT(*) as total, 
                       SUM(CASE WHEN status = 'paid' THEN 1 ELSE 0 END) as paid
                FROM split_payment_participants 
                WHERE split_payment_id = :split_payment_id";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':split_payment_id' => $splitPaymentId]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($result['total'] == $result['paid']) {
            // Mark split payment as complete
            $sql = "UPDATE split_payments SET status = 'completed' WHERE id = :id";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([':id' => $splitPaymentId]);
            
            // Update booking status
            $sql = "UPDATE bookings SET status = 'paid' 
                    WHERE id = (SELECT booking_id FROM split_payments WHERE id = :id)";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([':id' => $splitPaymentId]);
        }
    }
    
    public function getSplitPaymentStatus($splitPaymentId) {
        $sql = "SELECT sp.*, 
                       (SELECT COUNT(*) FROM split_payment_participants spp 
                        WHERE spp.split_payment_id = sp.id) as total_participants,
                       (SELECT COUNT(*) FROM split_payment_participants spp 
                        WHERE spp.split_payment_id = sp.id AND spp.status = 'paid') as paid_participants
                FROM split_payments sp
                WHERE sp.id = :id";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':id' => $splitPaymentId]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}
```

**Step 2: Create Database Migration**
```sql
-- database/migrations/add_split_payment_tables.sql
CREATE TABLE split_payments (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    booking_id BIGINT UNSIGNED NOT NULL,
    total_amount DECIMAL(15, 2) NOT NULL,
    split_amount DECIMAL(15, 2) NOT NULL,
    participant_count INT NOT NULL,
    status ENUM('pending', 'partial', 'completed') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (booking_id) REFERENCES bookings(id) ON DELETE CASCADE,
    INDEX idx_booking_id (booking_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE split_payment_participants (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    split_payment_id BIGINT UNSIGNED NOT NULL,
    user_id BIGINT UNSIGNED NOT NULL,
    amount DECIMAL(15, 2) NOT NULL,
    paid_amount DECIMAL(15, 2) DEFAULT 0.00,
    status ENUM('pending', 'partial', 'paid') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (split_payment_id) REFERENCES split_payments(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_split_payment_id (split_payment_id),
    INDEX idx_user_id (user_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

**Step 3: Create Split Payment Controller**
```php
// app/controllers/SplitPaymentController.php
<?php
class SplitPaymentController extends Controller {
    public function create() {
        $bookingId = $_POST['booking_id'] ?? '';
        $participants = $_POST['participants'] ?? []; // Array of user IDs
        
        if (empty($participants)) {
            return $this->json(['status' => 'error', 'message' => 'No participants specified']);
        }
        
        $splitPaymentModel = new SplitPayment();
        $splitPaymentId = $splitPaymentModel->createSplitPayment($bookingId, $participants);
        
        if ($splitPaymentId) {
            return $this->json([
                'status' => 'success',
                'message' => 'Split payment created',
                'split_payment_id' => $splitPaymentId
            ]);
        }
        
        return $this->json(['status' => 'error', 'message' => 'Failed to create split payment']);
    }
    
    public function status($splitPaymentId) {
        $splitPaymentModel = new SplitPayment();
        $status = $splitPaymentModel->getSplitPaymentStatus($splitPaymentId);
        
        return $this->json([
            'status' => 'success',
            'data' => $status
        ]);
    }
    
    public function pay($participantId) {
        $amount = $_POST['amount'] ?? 0;
        
        $splitPaymentModel = new SplitPayment();
        $result = $splitPaymentModel->recordParticipantPayment($participantId, $amount);
        
        if ($result) {
            return $this->json(['status' => 'success', 'message' => 'Payment recorded']);
        }
        
        return $this->json(['status' => 'error', 'message' => 'Failed to record payment']);
    }
}
```

### 4.4 Phase 4: Trip Album Sharing

**Step 1: Create Trip Album Model**
```php
// app/models/TripAlbum.php
<?php
class TripAlbum extends Model {
    protected $table = 'trip_albums';
    
    public function createAlbum($bookingId, $name) {
        $sql = "INSERT INTO trip_albums 
                (booking_id, name, created_by, created_at)
                VALUES (:booking_id, :name, :created_by, NOW())";
        
        $stmt = $this->db->prepare($sql);
        $result = $stmt->execute([
            ':booking_id' => $bookingId,
            ':name' => $name,
            ':created_by' => $_SESSION['user_id']
        ]);
        
        if ($result) {
            return $this->db->lastInsertId();
        }
        
        return false;
    }
    
    public function addPhoto($albumId, $filePath, $dayNumber = null) {
        $sql = "INSERT INTO trip_album_photos 
                (album_id, file_path, day_number, uploaded_by, uploaded_at)
                VALUES (:album_id, :file_path, :day_number, :uploaded_by, NOW())";
        
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            ':album_id' => $albumId,
            ':file_path' => $filePath,
            ':day_number' => $dayNumber,
            ':uploaded_by' => $_SESSION['user_id']
        ]);
    }
    
    public function getAlbumPhotos($albumId) {
        $sql = "SELECT tap.*, u.name as uploader_name
                FROM trip_album_photos tap
                JOIN users u ON tap.uploaded_by = u.id
                WHERE tap.album_id = :album_id
                ORDER BY tap.day_number, tap.uploaded_at";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':album_id' => $albumId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    public function addComment($photoId, $comment) {
        $sql = "INSERT INTO trip_album_comments 
                (photo_id, user_id, comment, created_at)
                VALUES (:photo_id, :user_id, :comment, NOW())";
        
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            ':photo_id' => $photoId,
            ':user_id' => $_SESSION['user_id'],
            ':comment' => $comment
        ]);
    }
    
    public function addLike($photoId) {
        $sql = "INSERT INTO trip_album_likes 
                (photo_id, user_id, created_at)
                VALUES (:photo_id, :user_id, NOW())
                ON DUPLICATE KEY UPDATE created_at = NOW()";
        
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            ':photo_id' => $photoId,
            ':user_id' => $_SESSION['user_id']
        ]);
    }
}
```

**Step 2: Create Database Migration**
```sql
-- database/migrations/add_trip_album_tables.sql
CREATE TABLE trip_albums (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    booking_id BIGINT UNSIGNED NOT NULL,
    name VARCHAR(255) NOT NULL,
    created_by BIGINT UNSIGNED NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (booking_id) REFERENCES bookings(id) ON DELETE CASCADE,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_booking_id (booking_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE trip_album_photos (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    album_id BIGINT UNSIGNED NOT NULL,
    file_path VARCHAR(500) NOT NULL,
    day_number INT,
    uploaded_by BIGINT UNSIGNED NOT NULL,
    uploaded_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (album_id) REFERENCES trip_albums(id) ON DELETE CASCADE,
    FOREIGN KEY (uploaded_by) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_album_id (album_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE trip_album_comments (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    photo_id BIGINT UNSIGNED NOT NULL,
    user_id BIGINT UNSIGNED NOT NULL,
    comment TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (photo_id) REFERENCES trip_album_photos(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_photo_id (photo_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE trip_album_likes (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    photo_id BIGINT UNSIGNED NOT NULL,
    user_id BIGINT UNSIGNED NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (photo_id) REFERENCES trip_album_photos(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    UNIQUE KEY unique_photo_user (photo_id, user_id),
    INDEX idx_photo_id (photo_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

**Step 3: Create Trip Album Controller**
```php
// app/controllers/TripAlbumController.php
<?php
class TripAlbumController extends Controller {
    public function create() {
        $bookingId = $_POST['booking_id'] ?? '';
        $name = $_POST['name'] ?? 'Trip Album';
        
        $albumModel = new TripAlbum();
        $albumId = $albumModel->createAlbum($bookingId, $name);
        
        if ($albumId) {
            return $this->json([
                'status' => 'success',
                'message' => 'Album created',
                'album_id' => $albumId
            ]);
        }
        
        return $this->json(['status' => 'error', 'message' => 'Failed to create album']);
    }
    
    public function uploadPhoto() {
        $albumId = $_POST['album_id'] ?? '';
        $dayNumber = $_POST['day_number'] ?? null;
        
        if (!isset($_FILES['photo'])) {
            return $this->json(['status' => 'error', 'message' => 'No photo uploaded']);
        }
        
        // Upload photo
        $uploadPath = $this->uploadPhoto($_FILES['photo']);
        
        if (!$uploadPath) {
            return $this->json(['status' => 'error', 'message' => 'Upload failed']);
        }
        
        // Add ke album
        $albumModel = new TripAlbum();
        $result = $albumModel->addPhoto($albumId, $uploadPath, $dayNumber);
        
        if ($result) {
            return $this->json(['status' => 'success', 'message' => 'Photo added']);
        }
        
        return $this->json(['status' => 'error', 'message' => 'Failed to add photo']);
    }
    
    public function view($albumId) {
        $albumModel = new TripAlbum();
        $photos = $albumModel->getAlbumPhotos($albumId);
        
        return $this->view('trip-album/view', [
            'photos' => $photos,
            'album_id' => $albumId
        ]);
    }
    
    public function addComment() {
        $photoId = $_POST['photo_id'] ?? '';
        $comment = $_POST['comment'] ?? '';
        
        $albumModel = new TripAlbum();
        $result = $albumModel->addComment($photoId, $comment);
        
        if ($result) {
            return $this->json(['status' => 'success', 'message' => 'Comment added']);
        }
        
        return $this->json(['status' => 'error', 'message' => 'Failed to add comment']);
    }
    
    public function like() {
        $photoId = $_POST['photo_id'] ?? '';
        
        $albumModel = new TripAlbum();
        $result = $albumModel->addLike($photoId);
        
        if ($result) {
            return $this->json(['status' => 'success', 'message' => 'Liked']);
        }
        
        return $this->json(['status' => 'error', 'message' => 'Failed to like']);
    }
    
    private function uploadPhoto($file) {
        $uploadDir = __DIR__ . '/../../public/uploads/trip-photos/';
        
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }
        
        $filename = uniqid() . '_' . time() . '.' . pathinfo($file['name'], PATHINFO_EXTENSION);
        $filepath = $uploadDir . $filename;
        
        if (move_uploaded_file($file['tmp_name'], $filepath)) {
            return '/uploads/trip-photos/' . $filename;
        }
        
        return false;
    }
}
```

## 5. TESTING

### 5.1 Unit Tests
```php
// tests/Unit/Models/GroupTripTest.php
<?php
class GroupTripTest extends PHPUnit\Framework\TestCase {
    private $groupTripModel;
    
    protected function setUp(): void {
        $this->groupTripModel = new GroupTrip();
    }
    
    public function testCreateGroupTrip() {
        $data = [
            'name' => 'Bali Trip 2026',
            'creator_id' => 1,
            'destination_id' => 1,
            'start_date' => '2026-08-01',
            'end_date' => '2026-08-07'
        ];
        
        $groupId = $this->groupTripModel->create($data);
        
        $this->assertIsNumeric($groupId);
        $this->assertGreaterThan(0, $groupId);
    }
}
```

## 6. DOCUMENTATION UPDATES

Update dokumentasi berikut:
- `docs/social_features_guide.md` - Create new guide
- `docs/DEVELOPER_GUIDE.md` - Add social features section
- API documentation - Add social features endpoints

## 7. COMPLETION CRITERIA

Modul ini selesai ketika:
- ✅ Group trip planning berfungsi
- ✅ Shared wishlists berfungsi
- ✅ Split payments berfungsi
- ✅ Trip album sharing berfungsi
- ✅ Semua tests passing
- ✅ Documentation updated

---

## NEXT STEPS

Setelah modul ini selesai, lanjut ke:
- Update config.json dengan semua modul baru
- Buat master prompting cycle untuk implementasi
- Update state.json untuk tracking progress
