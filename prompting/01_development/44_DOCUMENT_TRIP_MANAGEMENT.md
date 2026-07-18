# MODUL 44 — DOCUMENT & TRIP MANAGEMENT

> **Modul:** Document & Trip Management Features  
> **Versi:** 1.0  
> **Tanggal:** 2026-07-18  
> **Tujuan:** Implementasi document dan trip management features (Digital Wallet, PDF Import, Real-time Updates, Trip Timeline, Printable PDF)

---

## 1. OBJECTIVE

Implementasi document dan trip management features untuk meningkatkan user experience dalam mengelola perjalanan menggunakan solusi self-hosted.

## 2. FITUR YANG AKAN DIIMPLEMENTASIKAN

### 2.1 Digital Wallet
- Wallet balance system
- Top-up functionality
- Payment dengan wallet
- Transaction history
- Refund ke wallet

### 2.2 PDF Itinerary Import AI
- Upload PDF itinerary
- AI extraction dengan Ollama
- Convert ke itinerary format
- Auto-populate trip data

### 2.3 Real-time Updates
- WebSocket untuk real-time updates
- Flight changes (manual input)
- Weather alerts
- Gate changes

### 2.4 Trip Timeline Day-by-Day
- Visual timeline dengan Vis.js/FullCalendar
- Drag-and-drop arrangement
- Integration dengan booking system
- Day-by-day view

### 2.5 Printable PDF Itinerary
- Generate PDF dari itinerary
- Include QR codes, maps, contact info
- Custom branding
- Download dan email

## 3. PREREQUISITES

### 3.1 Software Requirements
- TCPDF atau DomPDF (LGPL License) - untuk PDF generation
- Socket.IO (MIT License) - untuk real-time updates
- Vis.js (MIT License) atau FullCalendar - untuk timeline
- Ollama (sudah diinstall di Modul 40) - untuk AI extraction

### 3.2 Configuration
Baca `prompting/config.json` untuk environment configuration.

## 4. IMPLEMENTATION STEPS

### 4.1 Phase 1: Digital Wallet

**Step 1: Create Wallet Model**
```php
// app/models/Wallet.php
<?php
class Wallet extends Model {
    protected $table = 'user_wallets';
    
    public function getBalance($userId) {
        $sql = "SELECT balance FROM user_wallets WHERE user_id = :user_id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':user_id' => $userId]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        return $result['balance'] ?? 0;
    }
    
    public function createWallet($userId) {
        $sql = "INSERT INTO user_wallets (user_id, balance, created_at) 
                VALUES (:user_id, 0, NOW())";
        
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([':user_id' => $userId]);
    }
    
    public function topUp($userId, $amount, $method, $reference) {
        $this->db->beginTransaction();
        
        try {
            // Update balance
            $sql = "UPDATE user_wallets 
                    SET balance = balance + :amount 
                    WHERE user_id = :user_id";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                ':amount' => $amount,
                ':user_id' => $userId
            ]);
            
            // Record transaction
            $sql = "INSERT INTO wallet_transactions 
                    (user_id, type, amount, method, reference, status, created_at)
                    VALUES (:user_id, 'credit', :amount, :method, :reference, 'completed', NOW())";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                ':user_id' => $userId,
                ':amount' => $amount,
                ':method' => $method,
                ':reference' => $reference
            ]);
            
            $this->db->commit();
            return true;
        } catch (Exception $e) {
            $this->db->rollBack();
            return false;
        }
    }
    
    public function deduct($userId, $amount, $reference) {
        $balance = $this->getBalance($userId);
        
        if ($balance < $amount) {
            return false; // Insufficient balance
        }
        
        $this->db->beginTransaction();
        
        try {
            // Update balance
            $sql = "UPDATE user_wallets 
                    SET balance = balance - :amount 
                    WHERE user_id = :user_id";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                ':amount' => $amount,
                ':user_id' => $userId
            ]);
            
            // Record transaction
            $sql = "INSERT INTO wallet_transactions 
                    (user_id, type, amount, method, reference, status, created_at)
                    VALUES (:user_id, 'debit', :amount, 'wallet', :reference, 'completed', NOW())";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                ':user_id' => $userId,
                ':amount' => $amount,
                ':reference' => $reference
            ]);
            
            $this->db->commit();
            return true;
        } catch (Exception $e) {
            $this->db->rollBack();
            return false;
        }
    }
    
    public function getTransactions($userId, $limit = 50) {
        $sql = "SELECT * FROM wallet_transactions 
                WHERE user_id = :user_id 
                ORDER BY created_at DESC 
                LIMIT :limit";
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':user_id', $userId, PDO::PARAM_INT);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
```

**Step 2: Create Database Migration**
```sql
-- database/migrations/add_wallet_tables.sql
CREATE TABLE user_wallets (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED NOT NULL UNIQUE,
    balance DECIMAL(15, 2) DEFAULT 0.00,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE wallet_transactions (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED NOT NULL,
    type ENUM('credit', 'debit') NOT NULL,
    amount DECIMAL(15, 2) NOT NULL,
    method VARCHAR(50),
    reference VARCHAR(100),
    status VARCHAR(20) DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_user_id (user_id),
    INDEX idx_created_at (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

**Step 3: Create Wallet Controller**
```php
// app/controllers/WalletController.php
<?php
class WalletController extends Controller {
    public function index() {
        $userId = $_SESSION['user_id'];
        
        $walletModel = new Wallet();
        $balance = $walletModel->getBalance($userId);
        $transactions = $walletModel->getTransactions($userId);
        
        return $this->view('wallet/index', [
            'balance' => $balance,
            'transactions' => $transactions
        ]);
    }
    
    public function topUp() {
        $userId = $_SESSION['user_id'];
        $amount = $_POST['amount'] ?? 0;
        $method = $_POST['method'] ?? 'manual';
        $reference = $_POST['reference'] ?? '';
        
        if ($amount <= 0) {
            return $this->json(['status' => 'error', 'message' => 'Invalid amount']);
        }
        
        $walletModel = new Wallet();
        $result = $walletModel->topUp($userId, $amount, $method, $reference);
        
        if ($result) {
            return $this->json(['status' => 'success', 'message' => 'Top-up successful']);
        } else {
            return $this->json(['status' => 'error', 'message' => 'Top-up failed']);
        }
    }
    
    public function payWithWallet($bookingId) {
        $userId = $_SESSION['user_id'];
        
        $bookingModel = new Booking();
        $booking = $bookingModel->getById($bookingId);
        
        $walletModel = new Wallet();
        $result = $walletModel->deduct($userId, $booking['total_price'], 'BK' . $booking['booking_code']);
        
        if ($result) {
            // Update booking status
            $bookingModel->updateStatus($bookingId, 'paid');
            
            return $this->json(['status' => 'success', 'message' => 'Payment successful']);
        } else {
            return $this->json(['status' => 'error', 'message' => 'Insufficient balance']);
        }
    }
}
```

### 4.2 Phase 2: PDF Itinerary Import AI

**Step 1: Create PDF Import Service**
```php
// app/services/PDFImportService.php
<?php
class PDFImportService {
    private $ollamaUrl;
    private $model;
    
    public function __construct() {
        $this->ollamaUrl = 'http://127.0.0.1:11434';
        $this->model = 'llama3.2:3b';
    }
    
    public function importPDF($filePath) {
        // Extract text dari PDF
        $text = $this->extractPDFText($filePath);
        
        // Use AI untuk parse dan structure data
        $structuredData = $this->parseWithAI($text);
        
        return $structuredData;
    }
    
    private function extractPDFText($filePath) {
        // Gunakan library seperti TCPDF parser atau external tool
        // Untuk simplicity, gunakan pdftotext jika available
        $output = shell_exec("pdftotext '{$filePath}' -");
        
        return $output;
    }
    
    private function parseWithAI($text) {
        $prompt = "You are a travel itinerary parser. ";
        $prompt .= "Extract structured data from this itinerary text:\n\n";
        $prompt .= $text;
        $prompt .= "\n\nReturn JSON with: destination, dates, activities, accommodation, transport, notes";
        
        $ch = curl_init($this->ollamaUrl . '/api/generate');
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode([
            'model' => $this->model,
            'prompt' => $prompt,
            'stream' => false,
            'format' => 'json'
        ]));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 120); // 2 minute timeout untuk large PDF
        $response = curl_exec($ch);
        curl_close($ch);
        
        $result = json_decode($response, true);
        $parsedData = json_decode($result['response'], true);
        
        return $parsedData;
    }
}
```

**Step 2: Create PDF Import Controller**
```php
// app/controllers/PDFImportController.php
<?php
class PDFImportController extends Controller {
    public function upload() {
        if (!isset($_FILES['pdf_file'])) {
            return $this->json(['status' => 'error', 'message' => 'No file uploaded']);
        }
        
        $file = $_FILES['pdf_file'];
        
        // Validate file type
        if ($file['type'] !== 'application/pdf') {
            return $this->json(['status' => 'error', 'message' => 'Only PDF files are allowed']);
        }
        
        // Upload file
        $uploadPath = $this->uploadFile($file);
        
        if (!$uploadPath) {
            return $this->json(['status' => 'error', 'message' => 'Upload failed']);
        }
        
        // Import PDF
        $importService = new PDFImportService();
        $structuredData = $importService->importPDF($uploadPath);
        
        return $this->json([
            'status' => 'success',
            'data' => $structuredData
        ]);
    }
    
    private function uploadFile($file) {
        $uploadDir = __DIR__ . '/../../public/uploads/pdf/';
        
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }
        
        $filename = uniqid() . '.pdf';
        $filepath = $uploadDir . $filename;
        
        if (move_uploaded_file($file['tmp_name'], $filepath)) {
            return $filepath;
        }
        
        return false;
    }
}
```

### 4.3 Phase 3: Real-time Updates dengan WebSocket

**Step 1: Install Socket.IO Server**
```bash
cd /opt/lampp/htdocs/mywisata
npm install socket.io
```

**Step 2: Create Socket.IO Server**
```javascript
// socket-server.js
const io = require('socket.io')(3000, {
    cors: {
        origin: "*",
        methods: ["GET", "POST"]
    }
});

io.on('connection', (socket) => {
    console.log('User connected:', socket.id);
    
    // Join user-specific room
    socket.on('join', (userId) => {
        socket.join('user_' + userId);
        console.log('User joined room:', userId);
    });
    
    // Send update ke specific user
    socket.on('send_update', (data) => {
        const { userId, update } = data;
        io.to('user_' + userId).emit('receive_update', update);
    });
    
    socket.on('disconnect', () => {
        console.log('User disconnected:', socket.id);
    });
});

console.log('Socket.IO server running on port 3000');
```

**Step 3: Create Real-time Update Service**
```php
// app/services/RealTimeUpdateService.php
<?php
class RealTimeUpdateService {
    private $socketServerUrl;
    
    public function __construct() {
        $this->socketServerUrl = 'http://localhost:3000';
    }
    
    public function sendUpdate($userId, $update) {
        $ch = curl_init($this->socketServerUrl . '/send_update');
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode([
            'userId' => $userId,
            'update' => $update
        ]));
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_exec($ch);
        curl_close($ch);
    }
    
    public function sendFlightChange($userId, $flightData) {
        $this->sendUpdate($userId, [
            'type' => 'flight_change',
            'data' => $flightData,
            'timestamp' => date('Y-m-d H:i:s')
        ]);
    }
    
    public function sendWeatherAlert($userId, $weatherData) {
        $this->sendUpdate($userId, [
            'type' => 'weather_alert',
            'data' => $weatherData,
            'timestamp' => date('Y-m-d H:i:s')
        ]);
    }
}
```

**Step 4: Create Frontend Socket.IO Client**
```html
<!-- Di layout atau view -->
<script src="https://cdn.socket.io/4.5.4/socket.io.min.js"></script>
<script>
var socket = io('http://localhost:3000');

// Join user room
socket.emit('join', <?php echo $_SESSION['user_id']; ?>);

// Listen untuk updates
socket.on('receive_update', function(update) {
    if (update.type === 'flight_change') {
        showFlightChangeNotification(update.data);
    } else if (update.type === 'weather_alert') {
        showWeatherAlert(update.data);
    }
});

function showFlightChangeNotification(data) {
    alert('Flight Change Alert: ' + data.message);
}

function showWeatherAlert(data) {
    alert('Weather Alert: ' + data.message);
}
</script>
```

### 4.4 Phase 4: Trip Timeline Day-by-Day

**Step 1: Add Vis.js ke Frontend**
```html
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/vis-timeline@7.7.0/styles/vis-timeline-graph2d.min.css">
<script src="https://cdn.jsdelivr.net/npm/vis-timeline@7.7.0/dist/vis-timeline-graph2d.min.js"></script>
```

**Step 2: Create Timeline Controller**
```php
// app/controllers/TimelineController.php
<?php
class TimelineController extends Controller {
    public function show($bookingId) {
        $bookingModel = new Booking();
        $booking = $bookingModel->getById($bookingId);
        
        // Get itinerary items
        $itineraryModel = new Itinerary();
        $items = $itineraryModel->getByBookingId($bookingId);
        
        // Format untuk Vis.js timeline
        $timelineItems = [];
        foreach ($items as $item) {
            $timelineItems[] = [
                'id' => $item['id'],
                'content' => $item['title'],
                'start' => $item['start_datetime'],
                'end' => $item['end_datetime'],
                'type' => $item['type'] // 'point', 'range', 'background'
            ];
        }
        
        return $this->view('timeline/show', [
            'booking' => $booking,
            'items' => json_encode($timelineItems)
        ]);
    }
    
    public function updateItem() {
        $itemId = $_POST['item_id'] ?? '';
        $newStart = $_POST['new_start'] ?? '';
        $newEnd = $_POST['new_end'] ?? '';
        
        $itineraryModel = new Itinerary();
        $itineraryModel->updateTiming($itemId, $newStart, $newEnd);
        
        return $this->json(['status' => 'success']);
    }
}
```

**Step 3: Create Timeline View**
```php
// app/views/timeline/show.php
<div class="container mt-4">
    <h2>Trip Timeline: <?php echo htmlspecialchars($booking['destination_name']); ?></h2>
    
    <div id="timeline"></div>
</div>

<script>
var container = document.getElementById('timeline');
var items = new vis.DataSet(<?php echo $items; ?>);

var options = {
    editable: true,
    margin: {
        item: {
            horizontal: 0,
            vertical: 10
        }
    },
    onMove: function(item, callback) {
        // Update di server
        $.ajax({
            url: '/timeline/update-item',
            method: 'POST',
            data: {
                item_id: item.id,
                new_start: item.start,
                new_end: item.end
            },
            success: function(response) {
                callback(item);
            },
            error: function() {
                callback(null); // Cancel move
            }
        });
    }
};

var timeline = new vis.Timeline(container, items, options);
</script>
```

### 4.5 Phase 5: Printable PDF Itinerary

**Step 1: Create PDF Generation Service**
```php
// app/services/PDFItineraryService.php
<?php
class PDFItineraryService {
    public function generateItineraryPDF($bookingId) {
        // Get booking data
        $bookingModel = new Booking();
        $booking = $bookingModel->getById($bookingId);
        
        // Get itinerary
        $itineraryModel = new Itinerary();
        $itinerary = $itineraryModel->getByBookingId($bookingId);
        
        // Get QR code
        $qrCode = $this->generateQRCode($booking['booking_code']);
        
        // Generate PDF
        require_once(__DIR__ . '/../../vendor/tcpdf/tcpdf.php');
        
        $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
        
        // Set document info
        $pdf->SetCreator('MyWisata');
        $pdf->SetAuthor('MyWisata');
        $pdf->SetTitle('Itinerary - ' . $booking['booking_code']);
        
        // Add page
        $pdf->AddPage();
        
        // Build content
        $html = $this->buildItineraryHTML($booking, $itinerary, $qrCode);
        
        $pdf->writeHTML($html, true, false, true, false, '');
        
        // Output
        $filename = 'itinerary_' . $booking['booking_code'] . '.pdf';
        $pdf->Output($filename, 'D');
        
        return $filename;
    }
    
    private function buildItineraryHTML($booking, $itinerary, $qrCode) {
        $html = '<h1>MyWisata Itinerary</h1>';
        $html .= '<h2>Booking Code: ' . htmlspecialchars($booking['booking_code']) . '</h2>';
        $html .= '<p><strong>Destination:</strong> ' . htmlspecialchars($booking['destination_name']) . '</p>';
        $html .= '<p><strong>Date:</strong> ' . htmlspecialchars($booking['date']) . '</p>';
        $html .= '<p><strong>Guide:</strong> ' . htmlspecialchars($booking['guide_name']) . '</p>';
        
        $html .= '<h3>Itinerary</h3>';
        $html .= '<table border="1" cellpadding="5">';
        $html .= '<tr><th>Time</th><th>Activity</th><th>Location</th></tr>';
        
        foreach ($itinerary as $item) {
            $html .= '<tr>';
            $html .= '<td>' . htmlspecialchars($item['time']) . '</td>';
            $html .= '<td>' . htmlspecialchars($item['title']) . '</td>';
            $html .= '<td>' . htmlspecialchars($item['location']) . '</td>';
            $html .= '</tr>';
        }
        
        $html .= '</table>';
        
        $html .= '<h3>QR Code</h3>';
        $html .= '<img src="' . $qrCode . '" alt="QR Code">';
        
        $html .= '<p><em>Thank you for choosing MyWisata!</em></p>';
        
        return $html;
    }
    
    private function generateQRCode($bookingCode) {
        // Gunakan library QR code atau API
        // Untuk simplicity, gunakan QR code API
        return 'https://api.qrserver.com/v1/create-qr-code/?size=150x150&data=' . urlencode($bookingCode);
    }
}
```

**Step 2: Create PDF Controller**
```php
// app/controllers/PDFController.php
<?php
class PDFController extends Controller {
    public function generateItinerary($bookingId) {
        $pdfService = new PDFItineraryService();
        $pdfService->generateItineraryPDF($bookingId);
    }
    
    public function sendItineraryEmail($bookingId) {
        $pdfService = new PDFItineraryService();
        $filename = $pdfService->generateItineraryPDF($bookingId);
        
        // Send email dengan PDF attachment
        $emailService = new EmailService();
        $emailService->sendItineraryEmail($bookingId, $filename);
        
        return $this->json(['status' => 'success', 'message' => 'Itinerary sent to email']);
    }
}
```

## 5. TESTING

### 5.1 Unit Tests
```php
// tests/Unit/Services/WalletServiceTest.php
<?php
class WalletServiceTest extends PHPUnit\Framework\TestCase {
    private $walletModel;
    
    protected function setUp(): void {
        $this->walletModel = new Wallet();
    }
    
    public function testTopUp() {
        $result = $this->walletModel->topUp(1, 100000, 'manual', 'TEST001');
        
        $this->assertTrue($result);
        $this->assertEquals(100000, $this->walletModel->getBalance(1));
    }
    
    public function testDeduct() {
        $this->walletModel->topUp(1, 100000, 'manual', 'TEST002');
        $result = $this->walletModel->deduct(1, 50000, 'TEST003');
        
        $this->assertTrue($result);
        $this->assertEquals(50000, $this->walletModel->getBalance(1));
    }
}
```

## 6. DOCUMENTATION UPDATES

Update dokumentasi berikut:
- `docs/document_management_guide.md` - Create new guide
- `docs/DEVELOPER_GUIDE.md` - Add document management section
- API documentation - Add document management endpoints

## 7. COMPLETION CRITERIA

Modul ini selesai ketika:
- ✅ Digital wallet berfungsi
- ✅ PDF itinerary import AI berfungsi
- ✅ Real-time updates dengan WebSocket berfungsi
- ✅ Trip timeline day-by-day berfungsi
- ✅ Printable PDF itinerary berfungsi
- ✅ Semua tests passing
- ✅ Documentation updated

---

## NEXT STEPS

Setelah modul ini selesai, lanjut ke:
- Modul 45: Social Features
