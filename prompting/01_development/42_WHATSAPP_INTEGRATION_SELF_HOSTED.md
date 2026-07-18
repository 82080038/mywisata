# MODUL 42 — WHATSAPP INTEGRATION (SELF-HOSTED)

> **Modul:** WhatsApp Integration dengan OpenWA/WaSphere  
> **Versi:** 1.0  
> **Tanggal:** 2026-07-18  
> **Tujuan:** Implementasi WhatsApp notifications menggunakan OpenWA atau WaSphere (open-source, self-hosted)

---

## 1. OBJECTIVE

Implementasi WhatsApp integration untuk booking confirmations, reminders, dan customer service menggunakan OpenWA atau WaSphere (open-source, MIT License, zero per-message costs).

## 2. FITUR YANG AKAN DIIMPLEMENTASIKAN

### 2.1 Booking Confirmations via WhatsApp
- Automatic booking confirmation messages
- Include booking details, QR code, contact info
- Send saat booking berhasil

### 2.2 Reminders
- 24-hour reminder sebelum trip
- 2-hour reminder dengan meeting point
- Weather alerts untuk outdoor tours

### 2.3 Post-Tour Review Requests
- Automatic review request setelah trip selesai
- Link ke review form
- Incentive untuk reviews (green credits)

### 2.4 Customer Service
- WhatsApp chatbot untuk FAQs
- Human handoff untuk complex queries
- Integration dengan existing messaging system

## 3. PREREQUISITES

### 3.1 Software Requirements
- Docker dan Docker Compose
- Server dengan akses internet (untuk WhatsApp connection)
- WhatsApp Business account (untuk OpenWA) atau regular WhatsApp (untuk WaSphere)

### 3.2 Configuration
Baca `prompting/config.json` untuk environment configuration.

## 4. IMPLEMENTATION STEPS

### 4.1 Phase 1: Choose dan Deploy WhatsApp Gateway

**Option A: OpenWA (Recommended untuk production)**
```bash
# Clone OpenWA
cd /opt/lampp/htdocs/mywisata
git clone https://github.com/rmyndharis/OpenWA.git tools/openwa
cd tools/openwa

# Install dependencies
npm install

# Configure
cp config.example.json config.json
# Edit config.json dengan settings

# Start server
npm start
```

**Option B: WaSphere (Recommended untuk stability)**
```bash
# Clone WaSphere
cd /opt/lampp/htdocs/mywisata
git clone https://github.com/wasphere/wasphere.git tools/wasphere
cd tools/wasphere

# Deploy via Docker Compose
docker-compose up -d
```

**Step 2: Connect WhatsApp Account**
- Scan QR code dari web interface
- Verify connection
- Test send message

### 4.2 Phase 2: WhatsApp Service Integration

**Step 1: Create WhatsApp Service**
```php
// app/services/WhatsAppService.php
<?php
class WhatsAppService {
    private $apiUrl;
    private $apiKey;
    private $sessionId;
    
    public function __construct() {
        // Konfigurasi dari config.json atau environment
        $this->apiUrl = 'http://localhost:3000'; // OpenWA default
        $this->apiKey = getenv('WHATSAPP_API_KEY') ?? '';
        $this->sessionId = getenv('WHATSAPP_SESSION_ID') ?? 'default';
    }
    
    public function sendMessage($phoneNumber, $message, $media = null) {
        $endpoint = '/api/sendText';
        
        $payload = [
            'chatId' => $this->formatPhoneNumber($phoneNumber),
            'text' => $message,
            'sessionId' => $this->sessionId
        ];
        
        if ($media) {
            $endpoint = '/api/sendMedia';
            $payload['media'] = $media;
        }
        
        return $this->callAPI($endpoint, $payload);
    }
    
    public function sendBookingConfirmation($bookingData) {
        $message = $this->buildBookingConfirmationMessage($bookingData);
        return $this->sendMessage($bookingData['phone'], $message);
    }
    
    public function sendReminder($bookingData, $hoursBefore) {
        $message = $this->buildReminderMessage($bookingData, $hoursBefore);
        return $this->sendMessage($bookingData['phone'], $message);
    }
    
    public function sendReviewRequest($bookingData) {
        $message = $this->buildReviewRequestMessage($bookingData);
        return $this->sendMessage($bookingData['phone'], $message);
    }
    
    private function formatPhoneNumber($phone) {
        // Format ke WhatsApp format: 628xxx@c.us
        $phone = preg_replace('/[^0-9]/', '', $phone);
        if (substr($phone, 0, 1) === '0') {
            $phone = '62' . substr($phone, 1);
        }
        return $phone . '@c.us';
    }
    
    private function buildBookingConfirmationMessage($booking) {
        $message = "🎉 *Booking Confirmed!*\n\n";
        $message .= "Booking ID: {$booking['booking_code']}\n";
        $message .= "Destination: {$booking['destination_name']}\n";
        $message .= "Date: {$booking['date']}\n";
        $message .= "Time: {$booking['time']}\n";
        $message .= "Guide: {$booking['guide_name']}\n";
        $message .= "Total: Rp " . number_format($booking['total_price'], 0, ',', '.') . "\n\n";
        $message .= "Terima kasih telah booking dengan MyWisata! 🌴";
        
        return $message;
    }
    
    private function buildReminderMessage($booking, $hoursBefore) {
        $message = "⏰ *Reminder: Trip in {$hoursBefore} hours*\n\n";
        $message .= "Destination: {$booking['destination_name']}\n";
        $message .= "Date: {$booking['date']}\n";
        $message .= "Time: {$booking['time']}\n";
        $message .= "Meeting Point: {$booking['meeting_point']}\n\n";
        
        if ($hoursBefore <= 2) {
            $message .= "Weather: " . $this->getWeatherInfo($booking['destination_id']) . "\n\n";
        }
        
        $message .= "Jangan lupa siap! 🚀";
        
        return $message;
    }
    
    private function buildReviewRequestMessage($booking) {
        $message = "⭐ *How was your trip?*\n\n";
        $message .= "Kami ingin mendengar pengalaman Anda di {$booking['destination_name']}!\n\n";
        $message .= "Berikan review dan dapatkan *10 Green Credits*!\n\n";
        $message .= "Link Review: " . $this->getReviewLink($booking['id']) . "\n\n";
        $message .= "Terima kasih! 🙏";
        
        return $message;
    }
    
    private function callAPI($endpoint, $payload) {
        $url = $this->apiUrl . $endpoint;
        
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Authorization: Bearer ' . $this->apiKey
        ]);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        return [
            'success' => $httpCode === 200,
            'response' => json_decode($response, true),
            'http_code' => $httpCode
        ];
    }
}
```

**Step 2: Integrate dengan Booking System**
```php
// Di BookingController
public function createBooking() {
    // ... existing booking logic ...
    
    // Send WhatsApp confirmation
    $whatsappService = new WhatsAppService();
    $result = $whatsappService->sendBookingConfirmation($bookingData);
    
    // Log result
    $this->logWhatsAppResult($result);
}
```

### 4.3 Phase 3: Automated Reminders

**Step 1: Create Reminder Scheduler**
```php
// app/services/ReminderScheduler.php
<?php
class ReminderScheduler {
    private $whatsappService;
    private $bookingModel;
    
    public function __construct() {
        $this->whatsappService = new WhatsAppService();
        $this->bookingModel = new Booking();
    }
    
    public function send24HourReminders() {
        // Get bookings dalam 24 jam
        $bookings = $this->bookingModel->getBookingsInHours(24);
        
        foreach ($bookings as $booking) {
            $this->whatsappService->sendReminder($booking, 24);
        }
    }
    
    public function send2HourReminders() {
        // Get bookings dalam 2 jam
        $bookings = $this->bookingModel->getBookingsInHours(2);
        
        foreach ($bookings as $booking) {
            $this->whatsappService->sendReminder($booking, 2);
        }
    }
    
    public function sendReviewRequests() {
        // Get bookings yang selesai 24 jam ago
        $bookings = $this->bookingModel->getCompletedBookingsHoursAgo(24);
        
        foreach ($bookings as $booking) {
            $this->whatsappService->sendReviewRequest($booking);
        }
    }
}
```

**Step 2: Setup Cron Jobs**
```bash
# Add ke crontab
# 24-hour reminders (setiap jam)
0 * * * * php /opt/lampp/htdocs/mywisata/cron/send_reminders.php 24

# 2-hour reminders (setiap 10 menit)
*/10 * * * * php /opt/lampp/htdocs/mywisata/cron/send_reminders.php 2

# Review requests (setiap jam)
0 * * * * php /opt/lampp/htdocs/mywisata/cron/send_review_requests.php
```

**Step 3: Create Cron Script**
```php
// cron/send_reminders.php
<?php
require_once __DIR__ . '/../index.php';

$hours = $argv[1] ?? 24;

$scheduler = new ReminderScheduler();

if ($hours == 24) {
    $scheduler->send24HourReminders();
} elseif ($hours == 2) {
    $scheduler->send2HourReminders();
}
```

### 4.4 Phase 4: WhatsApp Customer Service

**Step 1: Create WhatsApp Webhook Handler**
```php
// app/controllers/WhatsAppWebhookController.php
<?php
class WhatsAppWebhookController extends Controller {
    private $chatbotService;
    
    public function __construct() {
        $this->chatbotService = new AIChatbotService();
    }
    
    public function receiveMessage() {
        $input = json_decode(file_get_contents('php://input'), true);
        
        $message = $input['message'] ?? '';
        $phoneNumber = $input['from'] ?? '';
        
        if (empty($message)) {
            return $this->json(['status' => 'error', 'message' => 'No message']);
        }
        
        // Process message dengan AI chatbot
        $response = $this->chatbotService->chat($message);
        
        // Send response via WhatsApp
        $whatsappService = new WhatsAppService();
        $whatsappService->sendMessage($phoneNumber, $response);
        
        return $this->json(['status' => 'success']);
    }
}
```

**Step 2: Configure Webhook di OpenWA/WaSphere**
- Set webhook URL ke `/whatsapp/webhook`
- Configure untuk receive incoming messages

### 4.5 Phase 6: Admin Dashboard for WhatsApp

**Step 1: Create WhatsApp Logs Model**
```php
// app/models/WhatsAppLog.php
<?php
class WhatsAppLog extends Model {
    protected $table = 'whatsapp_logs';
    
    public function create($data) {
        $sql = "INSERT INTO whatsapp_logs 
                (phone_number, message_type, message_content, status, created_at)
                VALUES (:phone_number, :message_type, :message_content, :status, NOW())";
        
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            ':phone_number' => $data['phone_number'],
            ':message_type' => $data['message_type'],
            ':message_content' => $data['message_content'],
            ':status' => $data['status']
        ]);
    }
    
    public function getAll($limit = 100) {
        $sql = "SELECT * FROM whatsapp_logs ORDER BY created_at DESC LIMIT :limit";
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
```

**Step 2: Create Database Migration**
```sql
-- database/migrations/add_whatsapp_logs.sql
CREATE TABLE whatsapp_logs (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    phone_number VARCHAR(20) NOT NULL,
    message_type VARCHAR(50) NOT NULL,
    message_content TEXT,
    status VARCHAR(20) NOT NULL,
    error_message TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_phone_number (phone_number),
    INDEX idx_created_at (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

**Step 3: Create Admin WhatsApp Dashboard**
```php
// app/controllers/AdminWhatsAppController.php
<?php
class AdminWhatsAppController extends Controller {
    public function dashboard() {
        $logModel = new WhatsAppLog();
        $logs = $logModel->getAll(100);
        
        // Get statistics
        $stats = [
            'total_sent' => $this->getTotalSent(),
            'total_delivered' => $this->getTotalDelivered(),
            'total_failed' => $this->getTotalFailed()
        ];
        
        return $this->view('admin/whatsapp/dashboard', [
            'logs' => $logs,
            'stats' => $stats
        ]);
    }
    
    public function sendTestMessage() {
        $phone = $_POST['phone'] ?? '';
        $message = $_POST['message'] ?? 'Test message from MyWisata';
        
        $whatsappService = new WhatsAppService();
        $result = $whatsappService->sendMessage($phone, $message);
        
        // Log result
        $logModel = new WhatsAppLog();
        $logModel->create([
            'phone_number' => $phone,
            'message_type' => 'test',
            'message_content' => $message,
            'status' => $result['success'] ? 'sent' : 'failed',
            'error_message' => $result['success'] ? null : json_encode($result['response'])
        ]);
        
        return $this->json($result);
    }
}
```

## 5. TESTING

### 5.1 Unit Tests
```php
// tests/Unit/Services/WhatsAppServiceTest.php
<?php
class WhatsAppServiceTest extends PHPUnit\Framework\TestCase {
    private $whatsappService;
    
    protected function setUp(): void {
        $this->whatsappService = new WhatsAppService();
    }
    
    public function testPhoneNumberFormatting() {
        $formatted = $this->whatsappService->formatPhoneNumber('08123456789');
        $this->assertEquals('628123456789@c.us', $formatted);
    }
    
    public function testBookingConfirmationMessage() {
        $booking = [
            'booking_code' => 'BK123',
            'destination_name' => 'Bali Beach',
            'date' => '2026-07-20',
            'time' => '09:00',
            'guide_name' => 'John Doe',
            'total_price' => 500000
        ];
        
        $message = $this->whatsappService->buildBookingConfirmationMessage($booking);
        
        $this->assertStringContainsString('Booking Confirmed', $message);
        $this->assertStringContainsString('BK123', $message);
    }
}
```

## 6. SECURITY CONSIDERATIONS

### 6.1 API Security
- Gunakan API key untuk WhatsApp service
- Validate semua incoming webhook requests
- Rate limiting untuk prevent abuse

### 6.2 Data Privacy
- Mask phone numbers di logs
- Encrypt sensitive data
- Compliance dengan regulasi privasi

### 6.3 WhatsApp Account Safety
- Follow WhatsApp ToS
- Don't spam messages
- Use proper message templates

## 7. MONITORING

### 7.1 Log Monitoring
```bash
# Monitor WhatsApp service logs
tail -f tools/openwa/logs/*.log

# Monitor application logs
tail -f logs/whatsapp.log
```

### 7.2 Health Checks
```php
// app/services/WhatsAppHealthCheck.php
<?php
class WhatsAppHealthCheck {
    public function check() {
        $whatsappService = new WhatsAppService();
        
        // Check connection
        $result = $whatsappService->callAPI('/api/status', []);
        
        return [
            'status' => $result['success'] ? 'healthy' : 'unhealthy',
            'timestamp' => date('Y-m-d H:i:s')
        ];
    }
}
```

## 8. DOCUMENTATION UPDATES

Update dokumentasi berikut:
- `docs/whatsapp_integration_guide.md` - Create new guide
- `docs/DEVELOPER_GUIDE.md` - Add WhatsApp section
- API documentation - Add WhatsApp endpoints

## 9. COMPLETION CRITERIA

Modul ini selesai ketika:
- ✅ OpenWA/WaSphere terdeploy dan connected
- ✅ WhatsApp service berfungsi
- ✅ Booking confirmations terkirim otomatis
- ✅ Reminders berfungsi (24h dan 2h)
- ✅ Review requests berfungsi
- ✅ WhatsApp customer service berfungsi
- ✅ Admin dashboard berfungsi
- ✅ Semua tests passing
- ✅ Documentation updated

---

## NEXT STEPS

Setelah modul ini selesai, lanjut ke:
- Modul 43: Business Operations (Match Engine, Payroll)
