# MODUL 40 — AI SELF-HOSTED (OLLAMA)

> **Modul:** AI Self-Hosted dengan Ollama  
> **Versi:** 1.0  
> **Tanggal:** 2026-07-18  
> **Tujuan:** Implementasi AI features dengan Ollama (local LLM) tanpa biaya API komersial

---

## 1. OBJECTIVE

Implementasi AI features menggunakan Ollama untuk local LLM inference, menggantikan OpenAI API dengan solusi self-hosted yang gratis dan tanpa vendor lock-in.

## 2. FITUR YANG AKAN DIIMPLEMENTASIKAN

### 2.1 AI Search Natural Language
- Natural language queries untuk pencarian destinasi
- Intent-based search bukan keyword-based
- Integration dengan existing search system

### 2.2 AI Customer Service Otomatis
- Chatbot untuk menjawab pertanyaan booking otomatis
- Answer FAQs dari product data dan reviews
- Reduce support load

### 2.3 AI Match Engine
- Auto-assignment guide ke booking
- Matching berdasarkan skill, availability, location, rating
- Maximize guide utilization

### 2.4 AI Content Automation
- Auto-generate deskripsi destinasi
- Optimize gambar descriptions
- Generate content untuk vendor

## 3. PREREQUISITES

### 3.1 Hardware Requirements
- **Minimum:** 8 GB RAM (untuk model 3B)
- **Recommended:** 16-32 GB RAM + GPU (untuk model 7B+)
- **Storage:** 20-50 GB untuk model files

### 3.2 Software Requirements
- Linux server dengan akses internet
- Docker (opsional, untuk containerized deployment)
- Python 3.8+ (untuk GHG Calculator jika diperlukan)

### 3.3 Configuration
Baca `prompting/config.json` untuk environment configuration.

## 4. IMPLEMENTATION STEPS

### 4.1 Phase 1: Ollama Installation & Setup

**Step 1: Install Ollama**
```bash
# Linux installation
curl -fsSL https://ollama.com/install.sh | sh

# Atau via Docker
docker run -d -v ollama:/root/.ollama -p 11434:11434 --name ollama ollama/ollama
```

**Step 2: Verify Installation**
```bash
ollama --version
# Test dengan simple prompt
ollama run llama3.2:3b "Hello, how are you?"
```

**Step 3: Pull Model**
```bash
# Untuk server dengan RAM terbatas (CPU-only)
ollama pull llama3.2:3b

# Untuk server dengan GPU (recommended)
ollama pull mistral:7b

# Untuk reasoning tasks
ollama pull deepseek-r1:8b
```

**Step 4: Configure Ollama Service**
```bash
# Edit systemd service jika perlu
sudo systemctl edit ollama.service

# Bind ke localhost only untuk security
[Service]
Environment="OLLAMA_HOST=127.0.0.1:11434"
```

**Step 5: Test REST API**
```bash
curl http://127.0.0.1:11434/api/generate -d '{
  "model": "llama3.2:3b",
  "prompt": "Why is the sky blue?",
  "stream": false
}'
```

### 4.2 Phase 2: AI Search Implementation

**Step 1: Create AI Search Service**
```php
// app/services/AISearchService.php
<?php
class AISearchService {
    private $ollamaUrl;
    private $model;
    
    public function __construct() {
        $this->ollamaUrl = 'http://127.0.0.1:11434';
        $this->model = 'llama3.2:3b'; // atau 'mistral:7b'
    }
    
    public function searchNaturalLanguage($query, $filters = []) {
        // Build prompt untuk search
        $prompt = $this->buildSearchPrompt($query, $filters);
        
        // Call Ollama API
        $response = $this->callOllama($prompt);
        
        // Parse response
        return $this->parseSearchResponse($response);
    }
    
    private function buildSearchPrompt($query, $filters) {
        $prompt = "You are a travel search assistant. ";
        $prompt .= "Convert this natural language query to structured search criteria:\n\n";
        $prompt .= "Query: {$query}\n\n";
        $prompt .= "Return JSON with: destination_type, location, budget, activities, preferences";
        
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
        $response = curl_exec($ch);
        curl_close($ch);
        
        return json_decode($response, true);
    }
    
    private function parseSearchResponse($response) {
        // Parse AI response dan convert ke search criteria
        return json_decode($response['response'], true);
    }
}
```

**Step 2: Create AI Search Controller**
```php
// app/controllers/AISearchController.php
<?php
class AISearchController extends Controller {
    private $aiSearchService;
    
    public function __construct() {
        $this->aiSearchService = new AISearchService();
    }
    
    public function naturalSearch() {
        $query = $_POST['query'] ?? '';
        
        if (empty($query)) {
            return $this->json(['status' => 'error', 'message' => 'Query required']);
        }
        
        // Get AI-parsed search criteria
        $criteria = $this->aiSearchService->searchNaturalLanguage($query);
        
        // Search destinations dengan criteria
        $destinationModel = new Destination();
        $results = $destinationModel->searchWithCriteria($criteria);
        
        return $this->json([
            'status' => 'success',
            'data' => [
                'criteria' => $criteria,
                'results' => $results
            ]
        ]);
    }
}
```

**Step 3: Add Route**
```php
// router.php
$router->post('/ai/search/natural', 'AISearchController@naturalSearch');
```

**Step 4: Create Frontend Interface**
```javascript
// public/js/ai-search.js
$(document).ready(function() {
    $('#ai-search-form').on('submit', function(e) {
        e.preventDefault();
        
        const query = $('#ai-search-input').val();
        
        $.ajax({
            url: '/ai/search/natural',
            method: 'POST',
            data: { query: query },
            success: function(response) {
                if (response.status === 'success') {
                    displaySearchResults(response.data);
                }
            }
        });
    });
});
```

### 4.3 Phase 3: AI Customer Service Implementation

**Step 1: Create AI Chatbot Service**
```php
// app/services/AIChatbotService.php
<?php
class AIChatbotService {
    private $ollamaUrl;
    private $model;
    private $knowledgeBase;
    
    public function __construct() {
        $this->ollamaUrl = 'http://127.0.0.1:11434';
        $this->model = 'llama3.2:3b';
        $this->loadKnowledgeBase();
    }
    
    private function loadKnowledgeBase() {
        // Load FAQs, product data, reviews dari database
        $this->knowledgeBase = [
            'faqs' => $this->loadFAQs(),
            'destinations' => $this->loadDestinations(),
            'booking_info' => $this->loadBookingInfo()
        ];
    }
    
    public function chat($message, $context = []) {
        // Build prompt dengan knowledge base
        $prompt = $this->buildChatPrompt($message, $context);
        
        // Call Ollama
        $response = $this->callOllama($prompt);
        
        return $response['response'];
    }
    
    private function buildChatPrompt($message, $context) {
        $prompt = "You are a helpful travel assistant for MyWisata platform. ";
        $prompt .= "Answer questions based on this knowledge base:\n\n";
        $prompt .= json_encode($this->knowledgeBase, JSON_PRETTY_PRINT);
        $prompt .= "\n\nUser question: {$message}\n\n";
        $prompt .= "Provide helpful, accurate answers. If you don't know, say so.";
        
        return $prompt;
    }
}
```

**Step 2: Create Chatbot Controller**
```php
// app/controllers/AIChatbotController.php
<?php
class AIChatbotController extends Controller {
    private $chatbotService;
    
    public function __construct() {
        $this->chatbotService = new AIChatbotService();
    }
    
    public function chat() {
        $message = $_POST['message'] ?? '';
        $context = $_POST['context'] ?? [];
        
        if (empty($message)) {
            return $this->json(['status' => 'error', 'message' => 'Message required']);
        }
        
        $response = $this->chatbotService->chat($message, $context);
        
        return $this->json([
            'status' => 'success',
            'data' => [
                'response' => $response
            ]
        ]);
    }
}
```

### 4.4 Phase 4: AI Match Engine Implementation

**Step 1: Create AI Match Service**
```php
// app/services/AIMatchService.php
<?php
class AIMatchService {
    private $ollamaUrl;
    private $model;
    
    public function __construct() {
        $this->ollamaUrl = 'http://127.0.0.1:11434';
        $this->model = 'mistral:7b'; // Use larger model for reasoning
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
    
    private function buildMatchPrompt($booking, $guides) {
        $prompt = "You are a tour guide matching system. ";
        $prompt .= "Match this booking to the best guide:\n\n";
        $prompt .= "Booking Details:\n";
        $prompt .= json_encode($booking, JSON_PRETTY_PRINT);
        $prompt .= "\n\nAvailable Guides:\n";
        $prompt .= json_encode($guides, JSON_PRETTY_PRINT);
        $prompt .= "\n\n";
        $prompt .= "Consider: language skills, specialization, location, rating, availability. ";
        $prompt .= "Return the guide ID with highest match score (0-100).";
        
        return $prompt;
    }
}
```

**Step 2: Integrate dengan Booking System**
```php
// Di BookingController, tambahkan auto-assignment
public function createBooking() {
    // ... existing booking logic ...
    
    // Auto-assign guide menggunakan AI
    $aiMatchService = new AIMatchService();
    $assignedGuide = $aiMatchService->matchGuideToBooking($bookingId);
    
    // Update booking dengan assigned guide
    $bookingModel->assignGuide($bookingId, $assignedGuide['guide_id']);
}
```

### 4.5 Phase 5: AI Content Automation

**Step 1: Create AI Content Service**
```php
// app/services/AIContentService.php
<?php
class AIContentService {
    private $ollamaUrl;
    private $model;
    
    public function __construct() {
        $this->ollamaUrl = 'http://127.0.0.1:11434';
        $this->model = 'llama3.2:3b';
    }
    
    public function generateDestinationDescription($destinationData) {
        $prompt = "Generate an engaging description for this travel destination:\n\n";
        $prompt .= json_encode($destinationData, JSON_PRETTY_PRINT);
        $prompt .= "\n\nWrite in Indonesian, 200-300 words, highlight unique features.";
        
        $response = $this->callOllama($prompt);
        return $response['response'];
    }
    
    public function optimizeImageDescription($imageData) {
        $prompt = "Write an SEO-friendly alt text and description for this travel image:\n\n";
        $prompt .= json_encode($imageData, JSON_PRETTY_PRINT);
        
        $response = $this->callOllama($prompt);
        return $response['response'];
    }
}
```

## 5. TESTING

### 5.1 Unit Tests
```php
// tests/Unit/Services/AISearchServiceTest.php
<?php
class AISearchServiceTest extends PHPUnit\Framework\TestCase {
    private $aiSearchService;
    
    protected function setUp(): void {
        $this->aiSearchService = new AISearchService();
    }
    
    public function testNaturalLanguageSearch() {
        $result = $this->aiSearchService->searchNaturalLanguage(
            "Cari pantai di Bali yang cocok untuk keluarga"
        );
        
        $this->assertIsArray($result);
        $this->assertArrayHasKey('destination_type', $result);
        $this->assertEquals('beach', $result['destination_type']);
    }
}
```

### 5.2 Integration Tests
```php
// tests/Integration/AIIntegrationTest.php
<?php
class AIIntegrationTest extends PHPUnit\Framework\TestCase {
    public function testOllamaConnection() {
        $service = new AISearchService();
        $result = $service->callOllama("Test connection");
        
        $this->assertNotEmpty($result);
    }
}
```

## 6. DEPLOYMENT

### 6.1 Production Setup
```bash
# Install Ollama di production server
curl -fsSL https://ollama.com/install.sh | sh

# Pull appropriate model
ollama pull mistral:7b

# Configure systemd service
sudo systemctl enable ollama
sudo systemctl start ollama

# Verify
curl http://127.0.0.1:11434/api/tags
```

### 6.2 Monitoring
```bash
# Monitor Ollama service
sudo systemctl status ollama

# Check logs
journalctl -u ollama -f

# Monitor resource usage
htop
```

## 7. SECURITY CONSIDERATIONS

### 7.1 Network Security
- Bind Ollama ke localhost only (127.0.0.1:11434)
- Jangan expose port 11434 ke public internet
- Gunakan reverse proxy (Nginx) dengan authentication jika perlu remote access

### 7.2 Input Validation
- Sanitize semua input sebelum dikirim ke Ollama
- Limit prompt length untuk prevent abuse
- Implement rate limiting

### 7.3 Output Sanitization
- Sanitize output dari Ollama sebelum display
- Validate JSON response
- Handle parsing errors gracefully

## 8. PERFORMANCE OPTIMIZATION

### 8.1 Caching
```php
// Cache AI responses untuk identical queries
class AISearchService {
    private $cache;
    
    public function __construct() {
        $this->cache = new Cache(); // Gunakan Redis atau file cache
    }
    
    public function searchNaturalLanguage($query, $filters = []) {
        $cacheKey = md5($query . json_encode($filters));
        
        // Check cache
        if ($cached = $this->cache->get($cacheKey)) {
            return $cached;
        }
        
        // Process dengan Ollama
        $result = $this->processWithOllama($query, $filters);
        
        // Cache result
        $this->cache->set($cacheKey, $result, 3600); // 1 hour
        
        return $result;
    }
}
```

### 8.2 Async Processing
```php
// Gunakan queue untuk heavy AI tasks
class AIContentService {
    public function queueDescriptionGeneration($destinationId) {
        Queue::push('GenerateDescriptionJob', ['destination_id' => $destinationId]);
    }
}
```

## 9. TROUBLESHOOTING

### 9.1 Common Issues

**Issue:** Ollama tidak merespons
**Solution:** Check service status: `sudo systemctl status ollama`

**Issue:** Model tidak ter-load
**Solution:** Verify model downloaded: `ollama list`

**Issue:** Slow inference
**Solution:** Upgrade ke GPU atau gunakan model lebih kecil

**Issue:** Out of memory
**Solution:** Gunakan model lebih kecil atau tambah RAM

## 10. DOCUMENTATION UPDATES

Update dokumentasi berikut:
- `docs/ai_integration_guide.md` - Update untuk Ollama
- `docs/DEVELOPER_GUIDE.md` - Add AI services section
- API documentation - Add AI endpoints

## 11. COMPLETION CRITERIA

Modul ini selesai ketika:
- ✅ Ollama terinstall dan running
- ✅ Model ter-download dan tested
- ✅ AI Search natural language berfungsi
- ✅ AI Customer Service chatbot berfungsi
- ✅ AI Match Engine auto-assignment berfungsi
- ✅ AI Content automation berfungsi
- ✅ Semua tests passing
- ✅ Documentation updated
- ✅ Security measures implemented

---

## NEXT STEPS

Setelah modul ini selesai, lanjut ke:
- Modul 41: Sustainability Features (Carbon Tracking)
