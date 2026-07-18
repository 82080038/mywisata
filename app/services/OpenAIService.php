<?php
namespace App\Services;

class OpenAIService
{
    private $apiKey;
    private $model;
    private $temperature;
    private $maxTokens;
    private $endpoint;

    public function __construct()
    {
        $config = require __DIR__ . '/../config/external/openai.php';
        $this->apiKey = $config['api_key'];
        $this->model = $config['model'];
        $this->temperature = $config['temperature'];
        $this->maxTokens = $config['max_tokens'];
        $this->endpoint = $config['endpoint'];
        $this->language = $config['language'] ?? 'id';
        $this->locale = $config['locale'] ?? 'id-ID';
        $this->systemPrompt = $config['system_prompt'] ?? 'You are a helpful assistant.';
    }

    /**
     * Generate chat completion
     */
    public function chat($messages, $options = [])
    {
        if (empty($this->apiKey)) {
            return [
                'success' => false,
                'error' => 'OpenAI API key not configured'
            ];
        }

        $temperature = $options['temperature'] ?? $this->temperature;
        $maxTokens = $options['max_tokens'] ?? $this->maxTokens;
        $model = $options['model'] ?? $this->model;
        $useSystemPrompt = $options['use_system_prompt'] ?? true;

        // Add system prompt if not already present and enabled
        if ($useSystemPrompt && !empty($this->systemPrompt)) {
            $hasSystemPrompt = false;
            foreach ($messages as $msg) {
                if ($msg['role'] === 'system') {
                    $hasSystemPrompt = true;
                    break;
                }
            }
            if (!$hasSystemPrompt) {
                array_unshift($messages, ['role' => 'system', 'content' => $this->systemPrompt]);
            }
        }

        $payload = [
            'model' => $model,
            'messages' => $messages,
            'temperature' => $temperature,
            'max_tokens' => $maxTokens
        ];

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->endpoint);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Authorization: Bearer ' . $this->apiKey
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode !== 200) {
            error_log("OpenAI API error: " . $response);
            return [
                'success' => false,
                'error' => 'API request failed'
            ];
        }

        $result = json_decode($response, true);

        return [
            'success' => true,
            'message' => $result['choices'][0]['message']['content'],
            'usage' => $result['usage']
        ];
    }

    /**
     * Generate destination recommendation
     */
    public function recommendDestinations($preferences, $limit = 5)
    {
        $systemPrompt = "You are a travel expert for Indonesia. Based on user preferences, recommend tourist destinations. Provide specific destinations with brief descriptions and reasons why they match the preferences.";

        $userPrompt = "User preferences: " . json_encode($preferences) . 
                     "\n\nRecommend up to {$limit} destinations that match these preferences. " .
                     "Format your response as a JSON array with objects containing: name, description, reasons, and tags.";

        $messages = [
            ['role' => 'system', 'content' => $systemPrompt],
            ['role' => 'user', 'content' => $userPrompt]
        ];

        $response = $this->chat($messages);

        if ($response['success']) {
            $jsonStart = strpos($response['message'], '[');
            $jsonEnd = strrpos($response['message'], ']');
            
            if ($jsonStart !== false && $jsonEnd !== false) {
                $jsonString = substr($response['message'], $jsonStart, $jsonEnd - $jsonStart + 1);
                $recommendations = json_decode($jsonString, true);
                
                return [
                    'success' => true,
                    'recommendations' => $recommendations
                ];
            }
        }

        return [
            'success' => false,
            'error' => 'Failed to generate recommendations'
        ];
    }

    /**
     * Generate tour guide recommendation
     */
    public function recommendTourGuides($requirements, $availableGuides)
    {
        $systemPrompt = "Anda adalah ahli pencocokan pemandu wisata. Cocokkan kebutuhan pengguna dengan pemandu wisata yang tersedia berdasarkan keahlian, bahasa, dan spesialisasi mereka. Respon dalam Bahasa Indonesia.";

        $userPrompt = "Kebutuhan pengguna: " . json_encode($requirements) . 
                     "\n\nPemandu tersedia: " . json_encode($availableGuides) .
                     "\n\nRekomendasikan 3 pemandu terbaik untuk pengguna ini. " .
                     "Format respon sebagai array JSON dengan objek yang berisi: guide_id, name, match_score, dan reasons.";

        $messages = [
            ['role' => 'system', 'content' => $systemPrompt],
            ['role' => 'user', 'content' => $userPrompt]
        ];

        $response = $this->chat($messages);

        if ($response['success']) {
            $jsonStart = strpos($response['message'], '[');
            $jsonEnd = strrpos($response['message'], ']');
            
            if ($jsonStart !== false && $jsonEnd !== false) {
                $jsonString = substr($response['message'], $jsonStart, $jsonEnd - $jsonStart + 1);
                $recommendations = json_decode($jsonString, true);
                
                return [
                    'success' => true,
                    'recommendations' => $recommendations
                ];
            }
        }

        return [
            'success' => false,
            'error' => 'Failed to generate recommendations'
        ];
    }

    /**
     * Generate trip itinerary
     */
    public function generateItinerary($destination, $duration, $interests)
    {
        $systemPrompt = "Anda adalah perencana perjalanan profesional. Buat rencana perjalanan hari demi hari yang detail untuk wisatawan yang mengunjungi destinasi di Indonesia. Respon dalam Bahasa Indonesia.";

        $userPrompt = "Destinasi: {$destination}\n" .
                     "Durasi: {$duration} hari\n" .
                     "Minat: " . implode(', ', $interests) . "\n\n" .
                     "Buat rencana perjalanan detail dengan aktivitas pagi, siang, dan malam untuk setiap hari. " .
                     "Sertakan lokasi spesifik, estimasi waktu, dan tips. " .
                     "Format respon sebagai objek JSON dengan array 'days' yang berisi objek hari dengan 'day', 'date', dan 'activities'.";

        $messages = [
            ['role' => 'system', 'content' => $systemPrompt],
            ['role' => 'user', 'content' => $userPrompt]
        ];

        $response = $this->chat($messages, ['max_tokens' => 2000]);

        if ($response['success']) {
            $jsonStart = strpos($response['message'], '{');
            $jsonEnd = strrpos($response['message'], '}');
            
            if ($jsonStart !== false && $jsonEnd !== false) {
                $jsonString = substr($response['message'], $jsonStart, $jsonEnd - $jsonStart + 1);
                $itinerary = json_decode($jsonString, true);
                
                return [
                    'success' => true,
                    'itinerary' => $itinerary
                ];
            }
        }

        return [
            'success' => false,
            'error' => 'Failed to generate itinerary'
        ];
    }

    /**
     * Generate destination description
     */
    public function generateDescription($destinationName, $keyFeatures)
    {
        $systemPrompt = "Anda adalah penulis perjalanan kreatif. Tulis deskripsi yang menarik dan informatif untuk destinasi wisata. Respon dalam Bahasa Indonesia.";

        $userPrompt = "Destinasi: {$destinationName}\n" .
                     "Fitur utama: " . implode(', ', $keyFeatures) . "\n\n" .
                     "Tulis deskripsi 200-300 kata yang menarik yang menonjolkan daya tarik unik dari destinasi ini. " .
                     "Buatlah deskripsi yang mengundang dan informatif untuk wisatawan potensial.";

        $messages = [
            ['role' => 'system', 'content' => $systemPrompt],
            ['role' => 'user', 'content' => $userPrompt]
        ];

        $response = $this->chat($messages);

        if ($response['success']) {
            return [
                'success' => true,
                'description' => $response['message']
            ];
        }

        return [
            'success' => false,
            'error' => 'Failed to generate description'
        ];
    }

    /**
     * Analyze sentiment of review
     */
    public function analyzeSentiment($text)
    {
        $systemPrompt = "Anda adalah ahli analisis sentimen. Analisis sentimen dari teks yang diberikan dan berikan peringkat dari -1 (sangat negatif) hingga 1 (sangat positif), bersama dengan tema utama. Respon dalam Bahasa Indonesia.";

        $userPrompt = "Analisis sentimen dari ulasan ini: \"{$text}\"\n\n" .
                     "Berikan respon sebagai objek JSON dengan 'sentiment_score' (-1 hingga 1), 'sentiment_label' (positif/negatif/netral), dan 'themes' (array topik utama).";

        $messages = [
            ['role' => 'system', 'content' => $systemPrompt],
            ['role' => 'user', 'content' => $userPrompt]
        ];

        $response = $this->chat($messages);

        if ($response['success']) {
            $jsonStart = strpos($response['message'], '{');
            $jsonEnd = strrpos($response['message'], '}');
            
            if ($jsonStart !== false && $jsonEnd !== false) {
                $jsonString = substr($response['message'], $jsonStart, $jsonEnd - $jsonStart + 1);
                $analysis = json_decode($jsonString, true);
                
                return [
                    'success' => true,
                    'analysis' => $analysis
                ];
            }
        }

        return [
            'success' => false,
            'error' => 'Failed to analyze sentiment'
        ];
    }

    /**
     * Enhanced chat conversation
     */
    public function chatConversation($conversationHistory, $userMessage, $context = [])
    {
        $systemPrompt = "You are an AI tour guide assistant for MyWisata, a tour guide application in Indonesia. " .
                       "Help users with travel planning, destination recommendations, and tour guide bookings. " .
                       "Be friendly, informative, and helpful. " .
                       "Context: " . json_encode($context);

        $messages = [
            ['role' => 'system', 'content' => $systemPrompt]
        ];

        foreach ($conversationHistory as $message) {
            $messages[] = [
                'role' => $message['role'],
                'content' => $message['content']
            ];
        }

        $messages[] = [
            'role' => 'user',
            'content' => $userMessage
        ];

        $response = $this->chat($messages);

        if ($response['success']) {
            return [
                'success' => true,
                'assistant_message' => $response['message'],
                'usage' => $response['usage']
            ];
        }

        return [
            'success' => false,
            'error' => 'Failed to generate response'
        ];
    }

    /**
     * Translate text
     */
    public function translate($text, $targetLanguage)
    {
        $systemPrompt = "You are a professional translator. Translate the given text accurately while maintaining the tone and context.";

        $userPrompt = "Translate the following text to {$targetLanguage}: \"{$text}\"";

        $messages = [
            ['role' => 'system', 'content' => $systemPrompt],
            ['role' => 'user', 'content' => $userPrompt]
        ];

        $response = $this->chat($messages);

        if ($response['success']) {
            return [
                'success' => true,
                'translation' => $response['message']
            ];
        }

        return [
            'success' => false,
            'error' => 'Failed to translate'
        ];
    }
}
