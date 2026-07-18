<?php
namespace App\Controllers;

use App\Services\OpenAIService;
use App\Core\Controller;
use App\Core\Middleware;

class SpeechController extends Controller
{
    private $openaiService;

    public function __construct()
    {
        Middleware::requireRole(['wisatawan', 'tour_guide', 'admin']);
        $this->openaiService = new OpenAIService();
    }

    /**
     * Process speech input and return AI response
     */
    public function processInput()
    {
        header('Content-Type: application/json');

        try {
            // Get speech text from request
            $input = json_decode(file_get_contents('php://input'), true);
            $speechText = $input['text'] ?? '';
            $context = $input['context'] ?? 'general';

            if (empty($speechText)) {
                echo json_encode([
                    'success' => false,
                    'error' => 'Speech text is required'
                ]);
                return;
            }

            // Build context-aware system prompt
            $systemPrompt = $this->getContextSystemPrompt($context);

            // Prepare messages for AI
            $messages = [
                ['role' => 'system', 'content' => $systemPrompt],
                ['role' => 'user', 'content' => $speechText]
            ];

            // Get AI response
            $response = $this->openaiService->chat($messages, [
                'use_system_prompt' => false // Use our custom system prompt
            ]);

            if ($response['success']) {
                echo json_encode([
                    'success' => true,
                    'input_text' => $speechText,
                    'response' => $response['message'],
                    'context' => $context,
                    'usage' => $response['usage'] ?? null
                ]);
            } else {
                echo json_encode([
                    'success' => false,
                    'error' => $response['error'] ?? 'Failed to process speech input'
                ]);
            }

        } catch (\Exception $e) {
            echo json_encode([
                'success' => false,
                'error' => 'Server error: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Get destination recommendations via voice
     */
    public function recommendDestinations()
    {
        header('Content-Type: application/json');

        try {
            $input = json_decode(file_get_contents('php://input'), true);
            $speechText = $input['text'] ?? '';

            if (empty($speechText)) {
                echo json_encode([
                    'success' => false,
                    'error' => 'Speech text is required'
                ]);
                return;
            }

            // Extract preferences from speech text using AI
            $messages = [
                ['role' => 'system', 'content' => 'Anda adalah asisten wisata. Ekstrak preferensi wisata dari teks pengguna dan kembalikan sebagai JSON dengan field: interest (minat), budget (anggaran), duration (durasi), location (lokasi), dan any_other_preferences (preferensi lain). Respon dalam Bahasa Indonesia.'],
                ['role' => 'user', 'content' => $speechText]
            ];

            $response = $this->openaiService->chat($messages, [
                'use_system_prompt' => false
            ]);

            if ($response['success']) {
                // Parse the extracted preferences
                $preferences = $this->parsePreferences($response['message']);

                // Get destination recommendations
                $recommendations = $this->openaiService->recommendDestinations($preferences);

                echo json_encode([
                    'success' => true,
                    'input_text' => $speechText,
                    'extracted_preferences' => $preferences,
                    'recommendations' => $recommendations['recommendations'] ?? []
                ]);
            } else {
                echo json_encode([
                    'success' => false,
                    'error' => 'Failed to extract preferences'
                ]);
            }

        } catch (\Exception $e) {
            echo json_encode([
                'success' => false,
                'error' => 'Server error: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Get tour guide recommendations via voice
     */
    public function recommendTourGuides()
    {
        header('Content-Type: application/json');

        try {
            $input = json_decode(file_get_contents('php://input'), true);
            $speechText = $input['text'] ?? '';

            if (empty($speechText)) {
                echo json_encode([
                    'success' => false,
                    'error' => 'Speech text is required'
                ]);
                return;
            }

            // Extract requirements from speech text
            $messages = [
                ['role' => 'system', 'content' => 'Anda adalah asisten pencocokan pemandu wisata. Ekstrak kebutuhan pemandu dari teks pengguna dan kembalikan sebagai JSON dengan field: language (bahasa), specialization (spesialisasi), experience (pengalaman), location (lokasi), dan budget (anggaran). Respon dalam Bahasa Indonesia.'],
                ['role' => 'user', 'content' => $speechText]
            ];

            $response = $this->openaiService->chat($messages, [
                'use_system_prompt' => false
            ]);

            if ($response['success']) {
                $requirements = $this->parsePreferences($response['message']);

                // Get available tour guides (would need to fetch from database)
                $availableGuides = $this->getAvailableTourGuides();

                $recommendations = $this->openaiService->recommendTourGuides($requirements, $availableGuides);

                echo json_encode([
                    'success' => true,
                    'input_text' => $speechText,
                    'extracted_requirements' => $requirements,
                    'recommendations' => $recommendations['recommendations'] ?? []
                ]);
            } else {
                echo json_encode([
                    'success' => false,
                    'error' => 'Failed to extract requirements'
                ]);
            }

        } catch (\Exception $e) {
            echo json_encode([
                'success' => false,
                'error' => 'Server error: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Generate itinerary via voice
     */
    public function generateItinerary()
    {
        header('Content-Type: application/json');

        try {
            $input = json_decode(file_get_contents('php://input'), true);
            $speechText = $input['text'] ?? '';

            if (empty($speechText)) {
                echo json_encode([
                    'success' => false,
                    'error' => 'Speech text is required'
                ]);
                return;
            }

            // Extract itinerary details from speech
            $messages = [
                ['role' => 'system', 'content' => 'Anda adalah asisten perencana perjalanan. Ekstrak detail perjalanan dari teks pengguna dan kembalikan sebagai JSON dengan field: destination (destinasi), duration (durasi dalam hari), dan interests (array minat). Respon dalam Bahasa Indonesia.'],
                ['role' => 'user', 'content' => $speechText]
            ];

            $response = $this->openaiService->chat($messages, [
                'use_system_prompt' => false
            ]);

            if ($response['success']) {
                $details = $this->parsePreferences($response['message']);

                $itinerary = $this->openaiService->generateItinerary(
                    $details['destination'] ?? 'Indonesia',
                    $details['duration'] ?? 3,
                    $details['interests'] ?? ['wisata']
                );

                echo json_encode([
                    'success' => true,
                    'input_text' => $speechText,
                    'extracted_details' => $details,
                    'itinerary' => $itinerary['itinerary'] ?? null
                ]);
            } else {
                echo json_encode([
                    'success' => false,
                    'error' => 'Failed to extract itinerary details'
                ]);
            }

        } catch (\Exception $e) {
            echo json_encode([
                'success' => false,
                'error' => 'Server error: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Get context-specific system prompt
     */
    private function getContextSystemPrompt($context)
    {
        $prompts = [
            'general' => 'Anda adalah asisten yang membantu untuk aplikasi MyWisata, platform pemandu wisata untuk Indonesia. Silakan merespons dalam Bahasa Indonesia kecuali secara khusus diminta untuk menggunakan bahasa lain. Jadilah membantu, ramah, dan berikan informasi akurat tentang destinasi wisata Indonesia, budaya, dan tips perjalanan.',
            'destination' => 'Anda adalah ahli wisata Indonesia. Berikan informasi detail tentang destinasi wisata Indonesia dalam Bahasa Indonesia. Sertakan tips praktis, informasi transportasi, dan rekomendasi aktivitas.',
            'tour_guide' => 'Anda adalah ahli pemandu wisata Indonesia. Berikan informasi tentang pemandu wisata, kualifikasi, dan tips memilih pemandu yang tepat dalam Bahasa Indonesia.',
            'booking' => 'Anda adalah asisten booking untuk MyWisata. Bantu pengguna dengan proses booking tour guide dalam Bahasa Indonesia. Jelaskan langkah-langkah dengan jelas.',
            'itinerary' => 'Anda adalah perencana perjalanan profesional untuk Indonesia. Buat rencana perjalanan detail dan praktis dalam Bahasa Indonesia.'
        ];

        return $prompts[$context] ?? $prompts['general'];
    }

    /**
     * Parse preferences from AI response
     */
    private function parsePreferences($text)
    {
        // Try to extract JSON from response
        $jsonStart = strpos($text, '{');
        $jsonEnd = strrpos($text, '}');

        if ($jsonStart !== false && $jsonEnd !== false) {
            $jsonString = substr($text, $jsonStart, $jsonEnd - $jsonStart + 1);
            $preferences = json_decode($jsonString, true);

            if (json_last_error() === JSON_ERROR_NONE) {
                return $preferences;
            }
        }

        // Fallback: return empty preferences
        return [];
    }

    /**
     * Get available tour guides (placeholder)
     */
    private function getAvailableTourGuides()
    {
        // This would fetch from database in production
        return [
            ['guide_id' => 1, 'name' => 'Budi Santoso', 'languages' => ['Indonesia', 'English'], 'specialization' => 'budaya', 'experience' => '5 tahun'],
            ['guide_id' => 2, 'name' => 'Siti Rahayu', 'languages' => ['Indonesia', 'Japanese'], 'specialization' => 'alam', 'experience' => '3 tahun'],
            ['guide_id' => 3, 'name' => 'Ahmad Fauzi', 'languages' => ['Indonesia', 'Arabic'], 'specialization' => 'sejarah', 'experience' => '7 tahun']
        ];
    }
}
