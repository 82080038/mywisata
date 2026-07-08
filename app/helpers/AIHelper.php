<?php

/**
 * MyWisata Application - AI Helper
 *
 * Handles AI/LLM integration for tour guide recommendations.
 *
 * @version 1.0.0
 *
 * @since 2026-07-01
 */
class AIHelper
{
    private static $apiKey;
    private static $apiEndpoint;
    private static $model;

    /**
     * Initialize AI helper
     */
    public static function init()
    {
        self::$apiKey = getenv('OPENAI_API_KEY') ?: '';
        self::$apiEndpoint = getenv('OPENAI_API_ENDPOINT') ?: 'https://api.openai.com/v1/chat/completions';
        self::$model = getenv('OPENAI_MODEL') ?: 'gpt-3.5-turbo';
    }

    /**
     * Get tour guide recommendations
     *
     * @param array $context Context data (location, interests, budget, etc.)
     * @return array AI recommendations
     */
    public static function getTourRecommendations($context)
    {
        self::init();

        if (empty(self::$apiKey)) {
            return self::getFallbackRecommendations($context);
        }

        try {
            $prompt = self::buildRecommendationPrompt($context);

            $response = self::callAPI([
                'model' => self::$model,
                'messages' => [
                    [
                        'role' => 'system',
                        'content' => 'You are a helpful Indonesian tour guide assistant. Provide recommendations in Indonesian language. Be concise and practical.',
                    ],
                    [
                        'role' => 'user',
                        'content' => $prompt,
                    ],
                ],
                'temperature' => 0.7,
                'max_tokens' => 500,
            ]);

            return self::parseRecommendations($response);

        } catch (Exception $e) {
            Logger::error('AI recommendation failed', [
                'error' => $e->getMessage(),
                'context' => $context,
            ]);

            return self::getFallbackRecommendations($context);
        }
    }

    /**
     * Get destination information
     *
     * @param string $destinationName Destination name
     * @return array Destination information
     */
    public static function getDestinationInfo($destinationName)
    {
        self::init();

        if (empty(self::$apiKey)) {
            return self::getFallbackDestinationInfo($destinationName);
        }

        try {
            $prompt = "Berikan informasi singkat tentang destinasi wisata: {$destinationName} dalam bahasa Indonesia. Termasuk: deskripsi, hal menarik, dan tips wisata.";

            $response = self::callAPI([
                'model' => self::$model,
                'messages' => [
                    [
                        'role' => 'system',
                        'content' => 'You are a helpful Indonesian tour guide assistant. Provide information in Indonesian language.',
                    ],
                    [
                        'role' => 'user',
                        'content' => $prompt,
                    ],
                ],
                'temperature' => 0.7,
                'max_tokens' => 300,
            ]);

            return self::parseDestinationInfo($response);

        } catch (Exception $e) {
            Logger::error('AI destination info failed', [
                'error' => $e->getMessage(),
                'destination' => $destinationName,
            ]);

            return self::getFallbackDestinationInfo($destinationName);
        }
    }

    /**
     * Chat with AI tour guide
     *
     * @param string $message User message
     * @param array $conversationHistory Previous conversation history
     * @return array AI response
     */
    public static function chat($message, $conversationHistory = [])
    {
        self::init();

        if (empty(self::$apiKey)) {
            return [
                'response' => 'Maaf, fitur AI tour guide belum dikonfigurasi. Silakan hubungi admin.',
                'tokens_used' => 0,
            ];
        }

        try {
            $messages = [
                [
                    'role' => 'system',
                    'content' => 'You are a helpful Indonesian tour guide assistant named "WisataAI". You provide travel advice, destination recommendations, and tourism information in Indonesian. Be friendly, helpful, and concise.',
                ],
            ];

            // Add conversation history
            foreach ($conversationHistory as $turn) {
                $messages[] = [
                    'role' => $turn['role'],
                    'content' => $turn['content'],
                ];
            }

            // Add current message
            $messages[] = [
                'role' => 'user',
                'content' => $message,
            ];

            $response = self::callAPI([
                'model' => self::$model,
                'messages' => $messages,
                'temperature' => 0.7,
                'max_tokens' => 400,
            ]);

            $tokensUsed = $response['usage']['total_tokens'] ?? 0;

            Logger::info('AI chat interaction', [
                'message_length' => strlen($message),
                'tokens_used' => $tokensUsed,
            ]);

            return [
                'response' => $response['choices'][0]['message']['content'],
                'tokens_used' => $tokensUsed,
            ];

        } catch (Exception $e) {
            Logger::error('AI chat failed', [
                'error' => $e->getMessage(),
            ]);

            return [
                'response' => 'Maaf, terjadi kesalahan saat memproses pesan Anda. Silakan coba lagi.',
                'tokens_used' => 0,
            ];
        }
    }

    /**
     * Call OpenAI API
     *
     * @param array $data API request data
     * @return array API response
     */
    private static function callAPI($data)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, self::$apiEndpoint);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Authorization: Bearer ' . self::$apiKey,
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode !== 200) {
            throw new Exception("API request failed with HTTP code: {$httpCode}");
        }

        return json_decode($response, true);
    }

    /**
     * Build recommendation prompt
     *
     * @param array $context Context data
     * @return string Prompt
     */
    private static function buildRecommendationPrompt($context)
    {
        $prompt = "Buatkan rekomendasi perjalanan wisata dengan kriteria berikut:\n\n";

        if (!empty($context['location'])) {
            $prompt .= "Lokasi: {$context['location']}\n";
        }

        if (!empty($context['interests'])) {
            $prompt .= "Minat: " . implode(', ', $context['interests']) . "\n";
        }

        if (!empty($context['budget'])) {
            $prompt .= "Budget: {$context['budget']}\n";
        }

        if (!empty($context['duration'])) {
            $prompt .= "Durasi: {$context['duration']}\n";
        }

        if (!empty($context['group_size'])) {
            $prompt .= "Jumlah orang: {$context['group_size']}\n";
        }

        $prompt .= "\nBerikan 3-5 rekomendasi destinasi dengan alasan singkat.";

        return $prompt;
    }

    /**
     * Parse AI recommendations
     *
     * @param array $response API response
     * @return array Parsed recommendations
     */
    private static function parseRecommendations($response)
    {
        $content = $response['choices'][0]['message']['content'];
        $lines = explode("\n", $content);
        $recommendations = [];

        foreach ($lines as $line) {
            if (preg_match('/^\d+\.\s*(.+)/', $line, $matches)) {
                $recommendations[] = [
                    'name' => trim($matches[1]),
                    'description' => '',
                ];
            }
        }

        return [
            'recommendations' => $recommendations,
            'raw_response' => $content,
        ];
    }

    /**
     * Parse destination info
     *
     * @param array $response API response
     * @return array Parsed destination info
     */
    private static function parseDestinationInfo($response)
    {
        $content = $response['choices'][0]['message']['content'];

        return [
            'description' => $content,
            'source' => 'ai',
        ];
    }

    /**
     * Get fallback recommendations (rule-based)
     *
     * @param array $context Context data
     * @return array Fallback recommendations
     */
    private static function getFallbackRecommendations($context)
    {
        $destinationModel = new Destination();
        $destinations = $destinationModel->getPopular(5);

        $recommendations = [];
        foreach ($destinations as $dest) {
            $recommendations[] = [
                'name' => $dest['name'],
                'description' => $dest['short_desc'] ?? $dest['description'],
            ];
        }

        return [
            'recommendations' => $recommendations,
            'source' => 'fallback',
        ];
    }

    /**
     * Get fallback destination info
     *
     * @param string $destinationName Destination name
     * @return array Fallback destination info
     */
    private static function getFallbackDestinationInfo($destinationName)
    {
        $destinationModel = new Destination();
        
        // Try to find destination by name
        $destinations = $destinationModel->getAllWithFilters([
            'search' => $destinationName,
            'is_active' => 1,
        ]);

        if (!empty($destinations)) {
            $dest = $destinations[0];
            return [
                'description' => $dest['description'],
                'source' => 'database',
            ];
        }

        return [
            'description' => 'Informasi tidak tersedia untuk destinasi ini.',
            'source' => 'fallback',
        ];
    }

    /**
     * Check if AI is configured
     *
     * @return bool
     */
    public static function isConfigured()
    {
        self::init();
        return !empty(self::$apiKey);
    }
}
