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
        $config = require __DIR__ . '/../config/openai.php';
        $this->apiKey = $config['api_key'];
        $this->model = $config['model'];
        $this->temperature = $config['temperature'];
        $this->maxTokens = $config['max_tokens'];
        $this->endpoint = $config['endpoint'];
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
        $systemPrompt = "You are a tour guide matching expert. Match user requirements with available tour guides based on their skills, languages, and specializations.";

        $userPrompt = "User requirements: " . json_encode($requirements) . 
                     "\n\nAvailable guides: " . json_encode($availableGuides) .
                     "\n\nRecommend the best 3 guides for this user. " .
                     "Format your response as a JSON array with objects containing: guide_id, name, match_score, and reasons.";

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
        $systemPrompt = "You are a professional travel planner. Create detailed day-by-day itineraries for tourists visiting destinations in Indonesia.";

        $userPrompt = "Destination: {$destination}\n" .
                     "Duration: {$duration} days\n" .
                     "Interests: " . implode(', ', $interests) . "\n\n" .
                     "Create a detailed itinerary with morning, afternoon, and evening activities for each day. " .
                     "Include specific locations, estimated times, and tips. " .
                     "Format your response as a JSON object with a 'days' array containing day objects with 'day', 'date', and 'activities'.";

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
        $systemPrompt = "You are a creative travel writer. Write engaging and informative descriptions for tourist destinations.";

        $userPrompt = "Destination: {$destinationName}\n" .
                     "Key features: " . implode(', ', $keyFeatures) . "\n\n" .
                     "Write a compelling 200-300 word description that highlights the unique appeal of this destination. " .
                     "Make it inviting and informative for potential tourists.";

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
        $systemPrompt = "You are a sentiment analysis expert. Analyze the sentiment of the given text and provide a rating from -1 (very negative) to 1 (very positive), along with key themes.";

        $userPrompt = "Analyze the sentiment of this review: \"{$text}\"\n\n" .
                     "Provide your response as a JSON object with 'sentiment_score' (-1 to 1), 'sentiment_label' (positive/negative/neutral), and 'themes' (array of key topics).";

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
