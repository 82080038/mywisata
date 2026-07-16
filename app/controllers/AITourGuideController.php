<?php
/**
 * MyWisata Application - AI Tour Guide Controller
 * 
 * Handles AI chat functionality for tour guidance with enhanced intents.
 * 
 * @package MyWisata
 * @version 1.0.0
 * @since 2026-07-01
 */

class AITourGuideController extends Controller {
    
    private $destinationModel;
    private $tourGuideModel;
    private $hotelModel;
    private $restaurantModel;
    
    public function __construct() {
        parent::__construct();
        $this->destinationModel = $this->model('Destination');
        $this->tourGuideModel = $this->model('TourGuide');
        $this->hotelModel = $this->model('Hotel');
        $this->restaurantModel = $this->model('Restaurant');
    }
    
    /**
     * Index - Show AI chat interface
     */
    public function index() {
        $data = [
            'title' => 'AI Tour Guide - MyWisata',
            'csrf_token' => Middleware::csrfToken()
        ];
        
        $this->view('aitourguide/index', $data);
    }
    
    /**
     * Chat - Process AI chat message
     */
    public function chat() {
        if (!$this->isAjax()) {
            $this->redirect('home');
        }
        
        // Verify CSRF
        if (!$this->validateCsrf()) {
            $this->json(['status' => 'error', 'message' => 'CSRF token mismatch'], 419);
        }
        
        $message = $this->post('message');
        $userId = Session::get('user_id');
        
        if (empty($message)) {
            $this->json(['status' => 'error', 'message' => 'Message cannot be empty'], 400);
        }
        
        // Detect intent and generate response
        $intent = $this->detectIntent($message);
        $response = $this->generateAIResponse($message, $intent);
        
        // Log the conversation
        $db = Database::getInstance();
        $db->query("INSERT INTO ai_conversations (user_id, user_message, ai_response, intent, created_at) 
                    VALUES (:user_id, :user_message, :ai_response, :intent, NOW())", 
                    ['user_id' => $userId, 'user_message' => $message, 'ai_response' => $response, 'intent' => $intent]);
        
        $this->json(['status' => 'success', 'response' => $response, 'intent' => $intent]);
    }
    
    /**
     * Detect user intent from message
     * 
     * @param string $message User message
     * @return string Detected intent
     */
    private function detectIntent($message) {
        $message = strtolower($message);
        
        // Destination-related intents
        if (preg_match('/(destinasi|wisata|tempat|kunjung|jalan-jalan|liburan)/i', $message)) {
            if (preg_match('/(rekomendasi|terbaik|populer|favorit)/i', $message)) {
                return 'destination_recommendation';
            }
            if (preg_match('/(bali|yogyakarta|jakarta|bandung|surabaya|medan)/i', $message)) {
                return 'destination_city';
            }
            if (preg_match('/(pantai|gunung|danau|air terjun)/i', $message)) {
                return 'destination_type';
            }
            return 'destination_general';
        }
        
        // Hotel-related intents
        if (preg_match('/(hotel|penginapan|menginap|kamar|akomodasi)/i', $message)) {
            if (preg_match('/(murah|budget|hemat)/i', $message)) {
                return 'hotel_budget';
            }
            if (preg_match('/(mewah|luxury|bintang 5)/i', $message)) {
                return 'hotel_luxury';
            }
            if (preg_match('/(dekat|pusat|tengah)/i', $message)) {
                return 'hotel_location';
            }
            return 'hotel_general';
        }
        
        // Restaurant-related intents
        if (preg_match('/(makanan|kuliner|restoran|makan|warung)/i', $message)) {
            if (preg_match('/(halal|santai|non-halal)/i', $message)) {
                return 'restaurant_dietary';
            }
            if (preg_match('/(murah|budget|hemat)/i', $message)) {
                return 'restaurant_budget';
            }
            if (preg_match('/(tradisional|khas|lokal)/i', $message)) {
                return 'restaurant_traditional';
            }
            return 'restaurant_general';
        }
        
        // Tour guide-related intents
        if (preg_match('/(tour guide|pemandu|guide|pemandu wisata)/i', $message)) {
            if (preg_match('/(bahasa|inggris|jepang|korea|cina)/i', $message)) {
                return 'guide_language';
            }
            if (preg_match('/(harga|biaya|tarif)/i', $message)) {
                return 'guide_pricing';
            }
            return 'guide_general';
        }
        
        // Pricing-related intents
        if (preg_match('/(harga|biaya|tarif|ongkos|berapa)/i', $message)) {
            return 'pricing';
        }
        
        // Booking-related intents
        if (preg_match('/(booking|pesan|reservasi|booking)/i', $message)) {
            return 'booking';
        }
        
        // Weather-related intents
        if (preg_match('/(cuaca|hujan|panas|dingin|weather)/i', $message)) {
            return 'weather';
        }
        
        // Itinerary-related intents
        if (preg_match('/(itinerary|rencana|jadwal|rute|perjalanan)/i', $message)) {
            return 'itinerary';
        }
        
        // Greeting intents
        if (preg_match('/(halo|hai|hello|selamat|pagi|siang|sore|malam)/i', $message)) {
            return 'greeting';
        }
        
        // Help intents
        if (preg_match('/(bantu|help|cara|bagaimana)/i', $message)) {
            return 'help';
        }
        
        return 'general';
    }
    
    /**
     * Generate AI response based on intent
     * 
     * @param string $message User message
     * @param string $intent Detected intent
     * @return string AI response
     */
    private function generateAIResponse($message, $intent) {
        switch ($intent) {
            case 'greeting':
                return "Halo! Selamat datang di MyWisata. Saya adalah AI Tour Guide yang siap membantu Anda merencanakan perjalanan wisata. Apa yang ingin Anda ketahui? Anda bisa bertanya tentang destinasi, hotel, restoran, tour guide, atau harga.";
            
            case 'destination_recommendation':
                return "Untuk destinasi wisata terbaik di Indonesia, saya merekomendasikan:\n\n🏝️ **Bali** - Pantai, budaya, dan spiritual\n🏛️ **Yogyakarta** - Candi Borobudur dan Keraton\n🏝️ **Raja Ampat** - Surga bawah laut\n🌋 **Komodo** - Komodo dragon dan diving\n🏔️ **Bromo** - Sunrise gunung berapi\n\nDestinasi mana yang ingin Anda ketahui lebih lanjut?";
            
            case 'destination_city':
                if (preg_match('/bali/i', $message)) {
                    return "Bali adalah destinasi wisata terpopuler di Indonesia dengan pantai indah, sawah terasering, dan budaya yang kaya. Tempat terbaik di Bali: Kuta, Ubud, Seminyak, dan Nusa Penida. Apakah Anda ingin rekomendasi hotel atau tour guide di Bali?";
                }
                if (preg_match('/yogyakarta/i', $message)) {
                    return "Yogyakarta adalah pusat budaya Jawa dengan Candi Borobudur dan Prambanan, serta Keraton Yogyakarta. Jangan lewatkan Malioboro untuk belanja oleh-oleh. Apakah Anda ingin rekomendasi hotel atau tour guide di Yogyakarta?";
                }
                return "Saya bisa membantu dengan rekomendasi destinasi di berbagai kota Indonesia. Kota mana yang Anda minati?";
            
            case 'destination_type':
                if (preg_match('/pantai/i', $message)) {
                    return "Untuk pantai terbaik di Indonesia: Kuta Bali, Tanjung Benoa, Pantai Jimbaran, Pantai Pink di Labuan Bajo, dan Pantai Parangtritis di Yogyakarta. Apakah Anda ingin rekomendasi hotel dekat pantai?";
                }
                if (preg_match('/gunung/i', $message)) {
                    return "Untuk pendakian gunung: Bromo untuk sunrise, Rinjani untuk hiking, Merbabu untuk pemula, dan Gede Pangrango untuk trekking. Apakah Anda ingin rekomendasi tour guide untuk pendakian?";
                }
                return "Indonesia memiliki berbagai jenis wisata alam. Apakah Anda ingin rekomendasi pantai, gunung, atau danau?";
            
            case 'destination_general':
                return "Indonesia memiliki banyak destinasi wisata menarik. Apakah Anda mencari destinasi pantai, gunung, budaya, atau kota? Beritahu saya preferensi Anda agar saya bisa memberikan rekomendasi yang tepat.";
            
            case 'hotel_budget':
                return "Untuk hotel budget di Indonesia, kami merekomendasikan:\n\n🏨 **Hostel** - Mulai Rp 100.000/night\n🏨 **Guest House** - Mulai Rp 150.000/night\n🏨 **Budget Hotel** - Mulai Rp 200.000/night\n\nApakah Anda ingin mencari hotel budget di kota tertentu?";
            
            case 'hotel_luxury':
                return "Untuk hotel luxury di Indonesia, kami merekomendasikan:\n\n🏨 **5-Star Resort** - Mulai Rp 2.000.000/night\n🏨 **Villa Private** - Mulai Rp 3.000.000/night\n🏨 **Boutique Hotel** - Mulai Rp 1.500.000/night\n\nApakah Anda ingin mencari hotel luxury di destinasi tertentu?";
            
            case 'hotel_location':
                return "Untuk hotel di lokasi strategis, kami merekomendasikan hotel di pusat kota dekat dengan:\n\n📍 Pusat perbelanjaan\n📍 Transportasi umum\n📍 Atraksi wisata\n\nKota mana yang Anda minati untuk mencari hotel?";
            
            case 'hotel_general':
                return "Kami memiliki berbagai pilihan hotel mulai dari budget hingga luxury. Apakah Anda memiliki preferensi lokasi, harga, atau fasilitas tertentu?";
            
            case 'restaurant_dietary':
                return "Untuk restoran halal di Indonesia, kami merekomendasikan restoran dengan sertifikasi halal. Kami juga memiliki rekomendasi restoran vegetarian dan non-halal. Apakah Anda ingin rekomendasi di kota tertentu?";
            
            case 'restaurant_budget':
                return "Untuk kuliner budget di Indonesia, coba warung lokal dengan harga mulai Rp 15.000-30.000. Jangan lewatkan Nasi Goreng, Mie Ayam, dan Sate. Apakah Anda ingin rekomendasi warung di kota tertentu?";
            
            case 'restaurant_traditional':
                return "Kuliner tradisional Indonesia yang wajib dicoba:\n\n🍛 **Rendang** - Padang\n🍛 **Gudeg** - Yogyakarta\n🍛 **Sate** - Madura\n🍛 **Bakso** - Seluruh Indonesia\n🍛 **Pempek** - Palembang\n\nApakah Anda ingin rekomendasi restoran yang menyajikan makanan tradisional?";
            
            case 'restaurant_general':
                return "Indonesia memiliki kuliner yang beragam dari Sabang sampai Merauke. Apakah Anda ingin rekomendasi restoran makanan tradisional, internasional, atau street food?";
            
            case 'guide_language':
                return "Kami menyediakan tour guide yang berbicara berbagai bahasa:\n\n🌐 Bahasa Indonesia\n🌐 Bahasa Inggris\n🌐 Bahasa Jepang\n🌐 Bahasa Korea\n🌐 Bahasa Mandarin\n🌐 Bahasa Jerman\n\nBahasa apa yang Anda butuhkan untuk tour guide?";
            
            case 'guide_pricing':
                return "Harga tour guide kami:\n\n👨‍🏫 **Local Guide** - Rp 150.000/jam\n👨‍🏫 **Professional Guide** - Rp 250.000/jam\n👨‍🏫 **Multi-language Guide** - Rp 350.000/jam\n👨‍🏫 **Full Day Guide** - Rp 1.500.000/hari\n\nApakah Anda ingin booking tour guide?";
            
            case 'guide_general':
                return "Kami menyediakan layanan tour guide profesional yang berpengalaman dan berbicara berbagai bahasa. Tour guide kami dapat membantu Anda menjelajahi destinasi dengan lebih dalam. Apakah Anda ingin mencari tour guide untuk destinasi tertentu?";
            
            case 'pricing':
                return "Harga layanan MyWisata:\n\n🎫 Tiket Destinasi - Mulai Rp 50.000\n👨‍🏫 Tour Guide - Mulai Rp 150.000/jam\n🏨 Hotel - Mulai Rp 200.000/night\n🍽️ Restoran - Mulai Rp 30.000/porsi\n\nApakah Anda ingin detail harga layanan tertentu?";
            
            case 'booking':
                return "Untuk booking layanan di MyWisata:\n\n1️⃣ Pilih layanan yang Anda inginkan\n2️⃣ Tambahkan ke keranjang\n3️⃣ Checkout dan pembayaran\n4️⃣ Dapatkan konfirmasi booking\n\nApakah Anda ingin saya bantu booking layanan tertentu?";
            
            case 'weather':
                return "Untuk informasi cuaca, saya bisa membantu Anda mengecek cuaca di destinasi wisata. Cuaca Indonesia umumnya tropis dengan dua musim: hujan (November-Maret) dan kemarau (April-Oktober). Destinasi mana yang ingin Anda cek cuacanya?";
            
            case 'itinerary':
                return "Saya bisa membantu Anda membuat itinerary perjalanan. Beritahu saya:\n\n📅 Berapa hari perjalanan Anda?\n📍 Destinasi mana yang ingin dikunjungi?\n💰 Budget perjalanan Anda?\n👥 Berapa orang dalam rombongan?\n\nDengan informasi ini, saya bisa membuat itinerary yang sesuai untuk Anda.";
            
            case 'help':
                return "Saya bisa membantu Anda dengan:\n\n🏝️ Rekomendasi destinasi wisata\n🏨 Pencarian hotel dan penginapan\n🍽️ Rekomendasi restoran dan kuliner\n👨‍🏫 Booking tour guide\n💰 Informasi harga\n📅 Perencanaan itinerary\n🌤️ Informasi cuaca\n\nApa yang ingin Anda tanyakan?";
            
            case 'general':
            default:
                return "Terima kasih atas pertanyaan Anda. Saya adalah AI Tour Guide MyWisata yang dapat membantu Anda merencanakan perjalanan wisata. Silakan tanyakan tentang destinasi, hotel, restoran, tour guide, harga, atau itinerary perjalanan.";
        }
    }
    
    /**
     * Get conversation history
     */
    public function getHistory() {
        Middleware::requireAuth();
        
        if (!$this->isAjax()) {
            $this->redirect('home');
        }
        
        $userId = Session::get('user_id');
        $limit = $this->get('limit', 20);
        
        $db = Database::getInstance();
        $history = $db->query("SELECT * FROM ai_conversations 
                                  WHERE user_id = :user_id 
                                  ORDER BY created_at DESC 
                                  LIMIT :limit", 
                                  ['user_id' => $userId, 'limit' => $limit])->fetchAll();
        
        $this->json([
            'status' => 'success',
            'data' => [
                'history' => $history
            ]
        ]);
    }
    
    /**
     * Clear conversation history
     */
    public function clearHistory() {
        Middleware::requireAuth();
        
        if (!$this->isAjax()) {
            $this->redirect('home');
        }
        
        // Verify CSRF
        if (!$this->validateCsrf()) {
            $this->json(['status' => 'error', 'message' => 'CSRF token mismatch'], 419);
        }
        
        $userId = Session::get('user_id');
        
        $db = Database::getInstance();
        $db->query("DELETE FROM ai_conversations WHERE user_id = :user_id", ['user_id' => $userId]);
        
        $this->json(['status' => 'success', 'message' => 'Riwayat percakapan berhasil dihapus']);
    }
}
