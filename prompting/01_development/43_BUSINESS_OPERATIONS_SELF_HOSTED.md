# MODUL 43 — BUSINESS OPERATIONS (SELF-HOSTED)

> **Modul:** Business Operations untuk Pelaku Wisata  
> **Versi:** 1.0  
> **Tanggal:** 2026-07-18  
> **Tujuan:** Implementasi business operations features (AI Match Engine, Smart Schedule, Payroll, GPS Clock-in) tanpa biaya API komersial

---

## 1. OBJECTIVE

Implementasi business operations features untuk meningkatkan efisiensi operasional tour guide dan pelaku wisata lainnya menggunakan solusi self-hosted.

## 2. FITUR YANG AKAN DIIMPLEMENTASIKAN

### 2.1 AI Match Engine untuk Guide Assignment
- Auto-assignment guide ke booking
- Matching berdasarkan skill, availability, location, rating
- Maximize guide utilization

### 2.2 Smart Schedule Drag-and-Drop
- Visual schedule management untuk guides
- Drag-and-drop interface
- Conflict detection
- Real-time updates

### 2.3 Payroll Automation
- Automated payment cycles untuk guides
- Commission tracking
- Integration dengan payment system
- Monthly settlements

### 2.4 GPS Clock-in
- Verified location tracking untuk guides
- Geofencing untuk tour locations
- Attendance verification
- Real-time location updates

### 2.5 Express Book Walk-in
- Quick booking untuk walk-in customers
- Mobile-friendly interface untuk guides
- Instant confirmation
- No paperwork

## 3. PREREQUISITES

### 3.1 Software Requirements
- FullCalendar (MIT License) - untuk schedule management
- jQuery UI (MIT License) - untuk drag-and-drop
- Browser Geolocation API - untuk GPS
- Ollama (sudah diinstall di Modul 40) - untuk AI matching

### 3.2 Configuration
Baca `prompting/config.json` untuk environment configuration.

## 4. IMPLEMENTATION STEPS

### 4.1 Phase 1: AI Match Engine

**Step 1: Create AI Match Service (Extend dari Modul 40)**
```php
// app/services/AIMatchService.php
<?php
class AIMatchService {
    private $ollamaUrl;
    private $model;
    
    public function __construct() {
        $this->ollamaUrl = 'http://127.0.0.1:11434';
        $this->model = 'mistral:7b'; // Use larger model untuk reasoning
    }
    
    public function matchGuideToBooking($bookingId) {
        // Get booking details
        $booking = $this->getBookingDetails($bookingId);
        
        // Get available guides
        $guides = $this->getAvailableGuides($booking);
        
        // Build matching prompt
        $prompt = $this->buildMatchPrompt($booking, $guides);
        
        // Call Ollama untuk scoring
        $response = $this->callOllama($prompt);
        
        // Parse dan return best match
        return $this->parseMatchResponse($response, $guides);
    }
    
    private function getBookingDetails($bookingId) {
        $bookingModel = new Booking();
        return $bookingModel->getById($bookingId);
    }
    
    private function getAvailableGuides($booking) {
        $guideModel = new TourGuide();
        return $guideModel->getAvailableForBooking(
            $booking['date'],
            $booking['destination_id'],
            $booking['language_preference'] ?? null
        );
    }
    
    private function buildMatchPrompt($booking, $guides) {
        $prompt = "You are a tour guide matching expert. ";
        $prompt .= "Match this booking to the best available guide:\n\n";
        $prompt .= "BOOKING DETAILS:\n";
        $prompt .= "- Destination: {$booking['destination_name']}\n";
        $prompt .= "- Date: {$booking['date']}\n";
        $prompt .= "- Time: {$booking['time']}\n";
        $prompt .= "- Group Size: {$booking['group_size']}\n";
        $prompt .= "- Language Preference: {$booking['language_preference']}\n";
        $prompt .= "- Special Requirements: {$booking['special_requirements']}\n\n";
        
        $prompt .= "AVAILABLE GUIDES:\n";
        foreach ($guides as $index => $guide) {
            $prompt .= "Guide " . ($index + 1) . ":\n";
            $prompt .= "- ID: {$guide['id']}\n";
            $prompt .= "- Name: {$guide['name']}\n";
            $prompt .= "- Languages: {$guide['languages']}\n";
            $prompt .= "- Specialization: {$guide['specialization']}\n";
            $prompt .= "- Rating: {$guide['rating']}\n";
            $prompt .= "- Location: {$guide['location']}\n";
            $prompt .= "- Experience: {$guide['experience_years']} years\n\n";
        }
        
        $prompt .= "EVALUATION CRITERIA:\n";
        $prompt .= "1. Language match (critical)\n";
        $prompt .= "2. Specialization relevance\n";
        $prompt .= "3. Location proximity\n";
        $prompt .= "4. Rating and experience\n";
        $prompt .= "5. Availability confirmation\n\n";
        
        $prompt .= "Return JSON format:\n";
        $prompt .= "{\n";
        $prompt .= "  \"best_guide_id\": <guide_id>,\n";
        $prompt .= "  \"match_score\": <0-100>,\n";
        $prompt .= "  \"reasoning\": \"<explanation>\"\n";
        $prompt .= "}\n";
        
        return $prompt;
    }
    
    private function callOllama($prompt) {
        $ch = curl_init($this->ollamaUrl . '/api/generate');
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode([
            'model' => $this->model,
            'prompt' => $prompt,
            'stream' => false,
            'format' => 'json'
        ]));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 60); // 60 second timeout
        $response = curl_exec($ch);
        curl_close($ch);
        
        return json_decode($response, true);
    }
    
    private function parseMatchResponse($response, $guides) {
        $result = json_decode($response['response'], true);
        
        if (json_last_error() !== JSON_ERROR_NONE) {
            // Fallback: simple scoring jika AI response invalid
            return $this->fallbackMatch($guides);
        }
        
        return [
            'guide_id' => $result['best_guide_id'],
            'match_score' => $result['match_score'],
            'reasoning' => $result['reasoning']
        ];
    }
    
    private function fallbackMatch($guides) {
        // Simple scoring fallback
        $bestGuide = $guides[0];
        $bestScore = 0;
        
        foreach ($guides as $guide) {
            $score = $guide['rating'] * 10 + $guide['experience_years'];
            if ($score > $bestScore) {
                $bestScore = $score;
                $bestGuide = $guide;
            }
        }
        
        return [
            'guide_id' => $bestGuide['id'],
            'match_score' => $bestScore,
            'reasoning' => 'Fallback match based on rating and experience'
        ];
    }
}
```

**Step 2: Integrate dengan Booking System**
```php
// Di BookingController
public function createBooking() {
    // ... existing booking logic ...
    
    // Auto-assign guide menggunakan AI
    $aiMatchService = new AIMatchService();
    $matchResult = $aiMatchService->matchGuideToBooking($bookingId);
    
    // Update booking dengan assigned guide
    $bookingModel = new Booking();
    $bookingModel->assignGuide($bookingId, $matchResult['guide_id']);
    
    // Log match result
    $this->logGuideAssignment($bookingId, $matchResult);
}
```

### 4.2 Phase 2: Smart Schedule Management

**Step 1: Add FullCalendar ke Frontend**
```html
<!-- Di admin layout atau guide dashboard -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.css">
<script src="https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.js"></script>
```

**Step 2: Create Schedule Controller**
```php
// app/controllers/ScheduleController.php
<?php
class ScheduleController extends Controller {
    public function guideSchedule($guideId) {
        $bookingModel = new Booking();
        $bookings = $bookingModel->getGuideBookings($guideId);
        
        // Format untuk FullCalendar
        $events = [];
        foreach ($bookings as $booking) {
            $events[] = [
                'id' => $booking['id'],
                'title' => $booking['destination_name'],
                'start' => $booking['date'] . 'T' . $booking['time'],
                'end' => $booking['end_date'] . 'T' . $booking['end_time'],
                'backgroundColor' => $this->getEventColor($booking['status']),
                'extendedProps' => [
                    'booking_code' => $booking['booking_code'],
                    'guide_name' => $booking['guide_name'],
                    'group_size' => $booking['group_size']
                ]
            ];
        }
        
        return $this->json([
            'status' => 'success',
            'data' => $events
        ]);
    }
    
    public function updateSchedule() {
        $bookingId = $_POST['booking_id'] ?? '';
        $newDate = $_POST['new_date'] ?? '';
        $newTime = $_POST['new_time'] ?? '';
        
        // Check untuk conflicts
        if ($this->hasScheduleConflict($bookingId, $newDate, $newTime)) {
            return $this->json([
                'status' => 'error',
                'message' => 'Schedule conflict detected'
            ]);
        }
        
        // Update booking
        $bookingModel = new Booking();
        $bookingModel->updateSchedule($bookingId, $newDate, $newTime);
        
        return $this->json([
            'status' => 'success',
            'message' => 'Schedule updated successfully'
        ]);
    }
    
    private function hasScheduleConflict($bookingId, $date, $time) {
        $bookingModel = new Booking();
        $currentBooking = $bookingModel->getById($bookingId);
        
        // Get other bookings untuk guide yang sama di tanggal/waktu yang sama
        $otherBookings = $bookingModel->getGuideBookingsAtTime(
            $currentBooking['guide_id'],
            $date,
            $time
        );
        
        return count($otherBookings) > 0;
    }
    
    private function getEventColor($status) {
        $colors = [
            'confirmed' => '#28a745',
            'pending' => '#ffc107',
            'completed' => '#17a2b8',
            'cancelled' => '#dc3545'
        ];
        
        return $colors[$status] ?? '#6c757d';
    }
}
```

**Step 3: Create Frontend Schedule Interface**
```javascript
// public/js/schedule.js
$(document).ready(function() {
    var calendarEl = document.getElementById('calendar');
    var guideId = $('#guide-id').val();
    
    var calendar = new FullCalendar.Calendar(calendarEl, {
        initialView: 'timeGridWeek',
        headerToolbar: {
            left: 'prev,next today',
            center: 'title',
            right: 'dayGridMonth,timeGridWeek,timeGridDay'
        },
        editable: true,
        eventDrop: function(info) {
            // Update schedule di server
            var newDate = info.event.start.toISOString().split('T')[0];
            var newTime = info.event.start.toTimeString().split(' ')[0].substring(0, 5);
            
            $.ajax({
                url: '/schedule/update',
                method: 'POST',
                data: {
                    booking_id: info.event.id,
                    new_date: newDate,
                    new_time: newTime
                },
                success: function(response) {
                    if (response.status === 'error') {
                        info.revert();
                        alert(response.message);
                    }
                },
                error: function() {
                    info.revert();
                    alert('Failed to update schedule');
                }
            });
        },
        events: '/schedule/guide/' + guideId
    });
    
    calendar.render();
});
```

### 4.3 Phase 3: Payroll Automation

**Step 1: Create Payroll Model**
```php
// app/models/Payroll.php
<?php
class Payroll extends Model {
    protected $table = 'guide_payroll';
    
    public function createPayrollPeriod($startDate, $endDate) {
        $sql = "INSERT INTO guide_payroll_periods 
                (start_date, end_date, status, created_at)
                VALUES (:start_date, :end_date, 'pending', NOW())";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            ':start_date' => $startDate,
            ':end_date' => $endDate
        ]);
        
        return $this->db->lastInsertId();
    }
    
    public function calculateGuideEarnings($guideId, $periodId) {
        // Get semua bookings untuk guide dalam period
        $sql = "SELECT b.*, g.commission_rate 
                FROM bookings b
                JOIN tour_guides g ON b.guide_id = g.id
                WHERE b.guide_id = :guide_id
                AND b.date BETWEEN 
                    (SELECT start_date FROM guide_payroll_periods WHERE id = :period_id)
                    AND (SELECT end_date FROM guide_payroll_periods WHERE id = :period_id)
                AND b.status = 'completed'";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            ':guide_id' => $guideId,
            ':period_id' => $periodId
        ]);
        $bookings = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        $totalEarnings = 0;
        $breakdown = [];
        
        foreach ($bookings as $booking) {
            $commission = $booking['total_price'] * ($booking['commission_rate'] / 100);
            $totalEarnings += $commission;
            
            $breakdown[] = [
                'booking_id' => $booking['id'],
                'booking_code' => $booking['booking_code'],
                'date' => $booking['date'],
                'total_price' => $booking['total_price'],
                'commission_rate' => $booking['commission_rate'],
                'commission' => $commission
            ];
        }
        
        return [
            'total_earnings' => $totalEarnings,
            'breakdown' => $breakdown,
            'total_bookings' => count($bookings)
        ];
    }
    
    public function createPayrollRecord($data) {
        $sql = "INSERT INTO guide_payroll 
                (guide_id, period_id, total_earnings, breakdown, status, created_at)
                VALUES (:guide_id, :period_id, :total_earnings, :breakdown, 'pending', NOW())";
        
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            ':guide_id' => $data['guide_id'],
            ':period_id' => $data['period_id'],
            ':total_earnings' => $data['total_earnings'],
            ':breakdown' => json_encode($data['breakdown'])
        ]);
    }
}
```

**Step 2: Create Database Migration**
```sql
-- database/migrations/add_payroll_tables.sql
CREATE TABLE guide_payroll_periods (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    start_date DATE NOT NULL,
    end_date DATE NOT NULL,
    status VARCHAR(20) DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_dates (start_date, end_date)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE guide_payroll (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    guide_id BIGINT UNSIGNED NOT NULL,
    period_id BIGINT UNSIGNED NOT NULL,
    total_earnings DECIMAL(15, 2) NOT NULL,
    breakdown JSON,
    status VARCHAR(20) DEFAULT 'pending',
    paid_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (guide_id) REFERENCES tour_guides(id) ON DELETE CASCADE,
    FOREIGN KEY (period_id) REFERENCES guide_payroll_periods(id) ON DELETE CASCADE,
    INDEX idx_guide_id (guide_id),
    INDEX idx_period_id (period_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

**Step 3: Create Payroll Controller**
```php
// app/controllers/PayrollController.php
<?php
class PayrollController extends Controller {
    public function index() {
        $payrollModel = new Payroll();
        $periods = $payrollModel->getAllPeriods();
        
        return $this->view('admin/payroll/index', [
            'periods' => $periods
        ]);
    }
    
    public function createPeriod() {
        $startDate = $_POST['start_date'] ?? date('Y-m-01');
        $endDate = $_POST['end_date'] ?? date('Y-m-t');
        
        $payrollModel = new Payroll();
        $periodId = $payrollModel->createPayrollPeriod($startDate, $endDate);
        
        // Calculate payroll untuk semua guides
        $guideModel = new TourGuide();
        $guides = $guideModel->getAll();
        
        foreach ($guides as $guide) {
            $earnings = $payrollModel->calculateGuideEarnings($guide['id'], $periodId);
            
            if ($earnings['total_earnings'] > 0) {
                $payrollModel->createPayrollRecord([
                    'guide_id' => $guide['id'],
                    'period_id' => $periodId,
                    'total_earnings' => $earnings['total_earnings'],
                    'breakdown' => $earnings['breakdown']
                ]);
            }
        }
        
        return $this->json([
            'status' => 'success',
            'message' => 'Payroll period created successfully'
        ]);
    }
    
    public function processPayment($payrollId) {
        $payrollModel = new Payroll();
        $payroll = $payrollModel->getById($payrollId);
        
        // Process payment via Midtrans atau manual transfer
        $paymentService = new PaymentService();
        $result = $paymentService->transferToGuide(
            $payroll['guide_id'],
            $payroll['total_earnings']
        );
        
        if ($result['success']) {
            $payrollModel->markAsPaid($payrollId);
        }
        
        return $this->json($result);
    }
}
```

### 4.4 Phase 4: GPS Clock-in

**Step 1: Create GPS Clock-in Service**
```php
// app/services/GPSClockInService.php
<?php
class GPSClockInService {
    public function verifyLocation($guideId, $latitude, $longitude, $destinationId) {
        // Get destination location
        $destinationModel = new Destination();
        $destination = $destinationModel->getById($destinationId);
        
        // Calculate distance
        $distance = $this->calculateDistance(
            $latitude,
            $longitude,
            $destination['latitude'],
            $destination['longitude']
        );
        
        // Allow 100 meter tolerance
        $isWithinRange = $distance <= 0.1; // 0.1 km = 100 meters
        
        return [
            'within_range' => $isWithinRange,
            'distance_meters' => $distance * 1000,
            'destination_name' => $destination['name']
        ];
    }
    
    private function calculateDistance($lat1, $lon1, $lat2, $lon2) {
        // Haversine formula
        $earthRadius = 6371; // km
        
        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);
        
        $a = sin($dLat/2) * sin($dLat/2) +
             cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
             sin($dLon/2) * sin($dLon/2);
        
        $c = 2 * atan2(sqrt($a), sqrt(1-$a));
        
        return $earthRadius * $c;
    }
    
    public function recordClockIn($guideId, $bookingId, $latitude, $longitude) {
        $sql = "INSERT INTO guide_clock_in 
                (guide_id, booking_id, latitude, longitude, clock_in_time, created_at)
                VALUES (:guide_id, :booking_id, :latitude, :longitude, NOW(), NOW())";
        
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            ':guide_id' => $guideId,
            ':booking_id' => $bookingId,
            ':latitude' => $latitude,
            ':longitude' => $longitude
        ]);
    }
}
```

**Step 2: Create GPS Clock-in Controller**
```php
// app/controllers/GPSClockInController.php
<?php
class GPSClockInController extends Controller {
    public function clockIn() {
        $guideId = $_POST['guide_id'] ?? '';
        $bookingId = $_POST['booking_id'] ?? '';
        $latitude = $_POST['latitude'] ?? '';
        $longitude = $_POST['longitude'] ?? '';
        
        if (empty($guideId) || empty($bookingId) || empty($latitude) || empty($longitude)) {
            return $this->json(['status' => 'error', 'message' => 'Missing required fields']);
        }
        
        // Get booking destination
        $bookingModel = new Booking();
        $booking = $bookingModel->getById($bookingId);
        
        // Verify location
        $gpsService = new GPSClockInService();
        $verification = $gpsService->verifyLocation(
            $guideId,
            $latitude,
            $longitude,
            $booking['destination_id']
        );
        
        if (!$verification['within_range']) {
            return $this->json([
                'status' => 'error',
                'message' => 'You are not within the required location',
                'data' => $verification
            ]);
        }
        
        // Record clock-in
        $gpsService->recordClockIn($guideId, $bookingId, $latitude, $longitude);
        
        return $this->json([
            'status' => 'success',
            'message' => 'Clock-in successful',
            'data' => $verification
        ]);
    }
}
```

**Step 3: Create Frontend GPS Clock-in**
```javascript
// public/js/gps-clockin.js
$(document).ready(function() {
    $('#clock-in-btn').on('click', function() {
        if (!navigator.geolocation) {
            alert('Geolocation is not supported by your browser');
            return;
        }
        
        navigator.geolocation.getCurrentPosition(
            function(position) {
                var latitude = position.coords.latitude;
                var longitude = position.coords.longitude;
                
                $.ajax({
                    url: '/gps/clock-in',
                    method: 'POST',
                    data: {
                        guide_id: $('#guide-id').val(),
                        booking_id: $('#booking-id').val(),
                        latitude: latitude,
                        longitude: longitude
                    },
                    success: function(response) {
                        if (response.status === 'success') {
                            alert('Clock-in successful! ' + response.data.destination_name);
                            $('#clock-in-status').text('Clocked in at ' + new Date().toLocaleTimeString());
                        } else {
                            alert(response.message);
                        }
                    },
                    error: function() {
                        alert('Failed to clock-in');
                    }
                });
            },
            function(error) {
                alert('Unable to retrieve your location: ' + error.message);
            }
        );
    });
});
```

**Step 4: Create Database Migration**
```sql
-- database/migrations/add_gps_clockin.sql
CREATE TABLE guide_clock_in (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    guide_id BIGINT UNSIGNED NOT NULL,
    booking_id BIGINT UNSIGNED NOT NULL,
    latitude DECIMAL(10, 7) NOT NULL,
    longitude DECIMAL(10, 7) NOT NULL,
    clock_in_time TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (guide_id) REFERENCES tour_guides(id) ON DELETE CASCADE,
    FOREIGN KEY (booking_id) REFERENCES bookings(id) ON DELETE CASCADE,
    INDEX idx_guide_id (guide_id),
    INDEX idx_booking_id (booking_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

### 4.5 Phase 5: Express Book Walk-in

**Step 1: Create Express Book Controller**
```php
// app/controllers/ExpressBookController.php
<?php
class ExpressBookController extends Controller {
    public function index() {
        // Mobile-friendly interface untuk guides
        return $this->view('guide/express-book');
    }
    
    public function quickBook() {
        $guideId = $_POST['guide_id'] ?? '';
        $destinationId = $_POST['destination_id'] ?? '';
        $customerName = $_POST['customer_name'] ?? '';
        $customerPhone = $_POST['customer_phone'] ?? '';
        $groupSize = $_POST['group_size'] ?? 1;
        $price = $_POST['price'] ?? 0;
        
        // Generate booking code
        $bookingCode = 'WK' . strtoupper(substr(md5(uniqid()), 0, 8));
        
        // Create booking
        $bookingModel = new Booking();
        $bookingId = $bookingModel->create([
            'booking_code' => $bookingCode,
            'guide_id' => $guideId,
            'destination_id' => $destinationId,
            'customer_name' => $customerName,
            'customer_phone' => $customerPhone,
            'group_size' => $groupSize,
            'total_price' => $price,
            'status' => 'confirmed',
            'booking_type' => 'walk_in',
            'date' => date('Y-m-d'),
            'time' => date('H:i')
        ]);
        
        return $this->json([
            'status' => 'success',
            'data' => [
                'booking_id' => $bookingId,
                'booking_code' => $bookingCode
            ]
        ]);
    }
}
```

**Step 2: Create Mobile-Friendly Express Book View**
```php
// app/views/guide/express-book.php
<!DOCTYPE html>
<html>
<head>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Express Book - MyWisata</title>
    <link rel="stylesheet" href="/assets/css/bootstrap.min.css">
</head>
<body>
    <div class="container mt-4">
        <h2>Express Book (Walk-in)</h2>
        
        <form id="express-book-form">
            <div class="form-group">
                <label>Destination</label>
                <select name="destination_id" class="form-control" required>
                    <?php foreach ($destinations as $dest): ?>
                    <option value="<?php echo $dest['id']; ?>">
                        <?php echo htmlspecialchars($dest['name']); ?> - Rp <?php echo number_format($dest['price']); ?>
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div class="form-group">
                <label>Customer Name</label>
                <input type="text" name="customer_name" class="form-control" required>
            </div>
            
            <div class="form-group">
                <label>Customer Phone</label>
                <input type="tel" name="customer_phone" class="form-control" required>
            </div>
            
            <div class="form-group">
                <label>Group Size</label>
                <input type="number" name="group_size" class="form-control" value="1" min="1" required>
            </div>
            
            <div class="form-group">
                <label>Price</label>
                <input type="number" name="price" class="form-control" required>
            </div>
            
            <input type="hidden" name="guide_id" value="<?php echo $guideId; ?>">
            
            <button type="submit" class="btn btn-primary btn-block">Quick Book</button>
        </form>
    </div>
    
    <script src="/assets/js/jquery.min.js"></script>
    <script>
    $('#express-book-form').on('submit', function(e) {
        e.preventDefault();
        
        $.ajax({
            url: '/express-book/quick',
            method: 'POST',
            data: $(this).serialize(),
            success: function(response) {
                if (response.status === 'success') {
                    alert('Booking created! Code: ' + response.data.booking_code);
                    window.location.reload();
                }
            },
            error: function() {
                alert('Failed to create booking');
            }
        });
    });
    </script>
</body>
</html>
```

## 5. TESTING

### 5.1 Unit Tests
```php
// tests/Unit/Services/AIMatchServiceTest.php
<?php
class AIMatchServiceTest extends PHPUnit\Framework\TestCase {
    private $aiMatchService;
    
    protected function setUp(): void {
        $this->aiMatchService = new AIMatchService();
    }
    
    public function testGuideMatching() {
        $result = $this->aiMatchService->matchGuideToBooking(1);
        
        $this->assertIsArray($result);
        $this->assertArrayHasKey('guide_id', $result);
        $this->assertArrayHasKey('match_score', $result);
    }
}
```

## 6. DOCUMENTATION UPDATES

Update dokumentasi berikut:
- `docs/business_operations_guide.md` - Create new guide
- `docs/DEVELOPER_GUIDE.md` - Add business operations section
- API documentation - Add business operations endpoints

## 7. COMPLETION CRITERIA

Modul ini selesai ketika:
- ✅ AI Match Engine berfungsi
- ✅ Smart Schedule drag-and-drop berfungsi
- ✅ Payroll automation berfungsi
- ✅ GPS Clock-in berfungsi
- ✅ Express Book walk-in berfungsi
- ✅ Semua tests passing
- ✅ Documentation updated

---

## NEXT STEPS

Setelah modul ini selesai, lanjut ke:
- Modul 44: Document & Trip Management
