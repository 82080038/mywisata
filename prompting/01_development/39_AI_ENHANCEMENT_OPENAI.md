# AI ENHANCEMENT WITH OPENAI
# Module 39 - OpenAI Integration for Enhanced Tour Guide Application

## OVERVIEW

This prompting template guides the AI through integrating OpenAI API to enhance the Tour Guide Application with intelligent features like smart recommendations, natural language processing, and advanced chat capabilities.

## OPENAI FEATURES

### Potential Features
1. **Smart Recommendations** - AI-powered destination and tour guide recommendations
2. **Natural Language Search** - Conversational search interface
3. **Trip Planning** - AI-assisted itinerary planning
4. **Translation** - Real-time language translation
5. **Content Generation** - Automated descriptions and reviews
6. **Sentiment Analysis** - Analyze user feedback
7. **Image Recognition** - Identify landmarks from photos
8. **Voice Assistant** - Voice-activated tour guide

### Recommended Features for Phase 1
1. Smart recommendations
2. Enhanced chat interface
3. Trip planning assistant
4. Content generation

## OPENAI API SETUP

### API Key Configuration
```env
OPENAI_API_KEY=sk-your-api-key-here
OPENAI_MODEL=gpt-4
OPENAI_TEMPERATURE=0.7
OPENAI_MAX_TOKENS=1000
```

### config/openai.php
```php
<?php
return [
    'api_key' => env('OPENAI_API_KEY', ''),
    'model' => env('OPENAI_MODEL', 'gpt-4'),
    'temperature' => env('OPENAI_TEMPERATURE', 0.7),
    'max_tokens' => env('OPENAI_MAX_TOKENS', 1000),
    'organization' => env('OPENAI_ORGANIZATION', ''),
    'endpoint' => 'https://api.openai.com/v1/chat/completions'
];
```

## OPENAI SERVICE CLASS

### OpenAIService.php
```php
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
            // Parse JSON from response
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

        // Build messages array
        $messages = [
            ['role' => 'system', 'content' => $systemPrompt]
        ];

        // Add conversation history
        foreach ($conversationHistory as $message) {
            $messages[] = [
                'role' => $message['role'],
                'content' => $message['content']
            ];
        }

        // Add current user message
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
```

## AI CONTROLLER

### AIController.php
```php
<?php
namespace App\Controllers;

use App\Services\OpenAIService;
use App\Models\Destination;
use App\Models\TourGuide;

class AIController extends Controller
{
    private $openai;

    public function __construct()
    {
        $this->openai = new OpenAIService();
    }

    /**
     * Get AI recommendations
     */
    public function recommendations()
    {
        $preferences = [
            'interests' => $_POST['interests'] ?? [],
            'budget' => $_POST['budget'] ?? 'medium',
            'duration' => $_POST['duration'] ?? 3,
            'group_size' => $_POST['group_size'] ?? 2,
            'language' => $_POST['language'] ?? 'english'
        ];

        $result = $this->openai->recommendDestinations($preferences);

        return $this->json($result);
    }

    /**
     * Get tour guide recommendations
     */
    public function guideRecommendations()
    {
        $requirements = [
            'destination' => $_POST['destination'] ?? '',
            'date' => $_POST['date'] ?? '',
            'duration' => $_POST['duration'] ?? 4,
            'languages' => $_POST['languages'] ?? ['english'],
            'specializations' => $_POST['specializations'] ?? []
        ];

        $guideModel = new TourGuide();
        $availableGuides = $guideModel->getAvailableGuides($requirements);

        $result = $this->openai->recommendTourGuides($requirements, $availableGuides);

        return $this->json($result);
    }

    /**
     * Generate trip itinerary
     */
    public function itinerary()
    {
        $destination = $_POST['destination'] ?? '';
        $duration = $_POST['duration'] ?? 3;
        $interests = $_POST['interests'] ?? [];

        $result = $this->openai->generateItinerary($destination, $duration, $interests);

        return $this->json($result);
    }

    /**
     * Generate content
     */
    public function generateContent()
    {
        $type = $_POST['type'] ?? 'description';
        $name = $_POST['name'] ?? '';
        $features = $_POST['features'] ?? [];

        if ($type === 'description') {
            $result = $this->openai->generateDescription($name, $features);
        } else {
            $result = ['success' => false, 'error' => 'Unknown content type'];
        }

        return $this->json($result);
    }

    /**
     * Analyze sentiment
     */
    public function sentiment()
    {
        $text = $_POST['text'] ?? '';

        $result = $this->openai->analyzeSentiment($text);

        return $this->json($result);
    }

    /**
     * AI chat endpoint
     */
    public function chat()
    {
        $conversationHistory = json_decode($_POST['conversation_history'] ?? '[]', true);
        $userMessage = $_POST['message'] ?? '';
        $context = [
            'user_id' => $_SESSION['user_id'] ?? null,
            'current_page' => $_POST['current_page'] ?? ''
        ];

        $result = $this->openai->chatConversation($conversationHistory, $userMessage, $context);

        return $this->json($result);
    }

    /**
     * Translate text
     */
    public function translate()
    {
        $text = $_POST['text'] ?? '';
        $targetLanguage = $_POST['target_language'] ?? 'english';

        $result = $this->openai->translate($text, $targetLanguage);

        return $this->json($result);
    }
}
```

## ENHANCED CHAT INTERFACE

### AI Chat View
```php
<?php $this->layout('layouts/header'); ?>

<div class="container mt-5">
    <div class="row">
        <div class="col-md-8 mx-auto">
            <div class="card">
                <div class="card-header">
                    <h4>AI Tour Guide Assistant</h4>
                </div>
                <div class="card-body">
                    <div id="chat-container" class="chat-container mb-3" style="height: 400px; overflow-y: auto;">
                        <div class="message assistant-message">
                            <div class="message-content">
                                <p>Hello! I'm your AI tour guide assistant. How can I help you plan your trip to Indonesia?</p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="input-group">
                        <input type="text" id="user-message" class="form-control" placeholder="Ask me anything about travel in Indonesia...">
                        <button id="send-message" class="btn btn-primary">Send</button>
                    </div>
                    
                    <div class="mt-3">
                        <h6>Quick Actions:</h6>
                        <button class="btn btn-sm btn-outline-primary quick-action" data-action="recommend">Get Recommendations</button>
                        <button class="btn btn-sm btn-outline-primary quick-action" data-action="plan">Plan Trip</button>
                        <button class="btn btn-sm btn-outline-primary quick-action" data-action="guides">Find Tour Guides</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.chat-container {
    border: 1px solid #ddd;
    border-radius: 5px;
    padding: 15px;
    background: #f9f9f9;
}

.message {
    margin-bottom: 15px;
}

.user-message {
    text-align: right;
}

.assistant-message {
    text-align: left;
}

.message-content {
    display: inline-block;
    max-width: 70%;
    padding: 10px 15px;
    border-radius: 10px;
}

.user-message .message-content {
    background: #007bff;
    color: white;
}

.assistant-message .message-content {
    background: white;
    border: 1px solid #ddd;
}
</style>

<script>
let conversationHistory = [];

document.getElementById('send-message').addEventListener('click', sendMessage);
document.getElementById('user-message').addEventListener('keypress', function(e) {
    if (e.key === 'Enter') sendMessage();
});

document.querySelectorAll('.quick-action').forEach(btn => {
    btn.addEventListener('click', function() {
        const action = this.dataset.action;
        let message = '';
        
        switch(action) {
            case 'recommend':
                message = 'Can you recommend some destinations for me?';
                break;
            case 'plan':
                message = 'I need help planning a trip. Can you assist me?';
                break;
            case 'guides':
                message = 'I need to find a tour guide. What information do you need?';
                break;
        }
        
        document.getElementById('user-message').value = message;
        sendMessage();
    });
});

function sendMessage() {
    const messageInput = document.getElementById('user-message');
    const message = messageInput.value.trim();
    
    if (!message) return;
    
    // Add user message to chat
    addMessage('user', message);
    conversationHistory.push({role: 'user', content: message});
    
    messageInput.value = '';
    
    // Send to AI
    fetch('/ai/chat', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            conversation_history: JSON.stringify(conversationHistory),
            message: message,
            current_page: window.location.pathname
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            addMessage('assistant', data.assistant_message);
            conversationHistory.push({role: 'assistant', content: data.assistant_message});
        } else {
            addMessage('assistant', 'Sorry, I encountered an error. Please try again.');
        }
    });
}

function addMessage(role, content) {
    const container = document.getElementById('chat-container');
    const messageDiv = document.createElement('div');
    messageDiv.className = `message ${role}-message`;
    
    messageDiv.innerHTML = `
        <div class="message-content">
            <p>${content}</p>
        </div>
    `;
    
    container.appendChild(messageDiv);
    container.scrollTop = container.scrollHeight;
}
</script>

<?php $this->layout('layouts/footer'); ?>
```

## ROUTE CONFIGURATION

### Add to routes
```php
// AI routes
$router->get('/ai/chat', [AIController::class, 'chat']);
$router->post('/ai/chat', [AIController::class, 'chat']);
$router->post('/ai/recommendations', [AIController::class, 'recommendations']);
$router->post('/ai/guide-recommendations', [AIController::class, 'guideRecommendations']);
$router->post('/ai/itinerary', [AIController::class, 'itinerary']);
$router->post('/ai/generate-content', [AIController::class, 'generateContent']);
$router->post('/ai/sentiment', [AIController::class, 'sentiment']);
$router->post('/ai/translate', [AIController::class, 'translate']);
```

## COST MANAGEMENT

### Usage Tracking
```php
// UsageTracker.php
class UsageTracker
{
    private $redis;
    
    public function __construct()
    {
        $this->redis = new \App\Services\RedisService();
    }
    
    public function trackUsage($userId, $tokens, $operation)
    {
        $key = "openai:usage:{$userId}:" . date('Y-m-d');
        $current = $this->redis->get($key) ?? ['tokens' => 0, 'operations' => []];
        
        $current['tokens'] += $tokens;
        $current['operations'][] = [
            'operation' => $operation,
            'tokens' => $tokens,
            'timestamp' => time()
        ];
        
        $this->redis->set($key, $current, 86400); // 24 hours
    }
    
    public function getDailyUsage($userId)
    {
        $key = "openai:usage:{$userId}:" . date('Y-m-d');
        return $this->redis->get($key) ?? ['tokens' => 0, 'operations' => []];
    }
    
    public function checkLimit($userId, $maxTokens = 100000)
    {
        $usage = $this->getDailyUsage($userId);
        return $usage['tokens'] < $maxTokens;
    }
}
```

## IMPLEMENTATION TASKS

### Phase 1: Setup
1. Get OpenAI API key
2. Configure environment variables
3. Create OpenAI service class
4. Test API connection
5. Set up usage tracking

### Phase 2: Core Features
1. Implement recommendation system
2. Implement chat interface
3. Implement itinerary generation
4. Implement content generation
5. Implement sentiment analysis

### Phase 3: Integration
1. Create AI controller
2. Add routes
3. Create chat interface
4. Integrate with existing features
5. Add to admin panel

### Phase 4: Optimization
1. Implement caching
2. Add rate limiting
3. Optimize prompts
4. Monitor usage
5. Cost optimization

### Phase 5: Testing
1. Test all AI features
2. Test error handling
3. Test rate limiting
4. Test cost tracking
5. User acceptance testing

## DELIVERABLES

1. OpenAIService class
2. AIController
3. UsageTracker class
4. Chat interface
5. Recommendation system
6. Itinerary generator
7. Content generator
8. Sentiment analyzer
9. Configuration files
10. Documentation

## ACCEPTANCE CRITERIA

- OpenAI API integrated
- Recommendation system working
- Chat interface functional
- Itinerary generation working
- Content generation working
- Usage tracking implemented
- Rate limiting configured
- Error handling robust
- Cost monitoring active
- Documentation complete

## NOTES

- Monitor API costs closely
- Implement rate limiting
- Cache AI responses when possible
- Use appropriate models for tasks
- Handle API errors gracefully
- Provide fallback responses
- Regular prompt optimization
- User feedback collection
- Ethical AI usage
- Privacy considerations

---

**Module:** 39_AI_ENHANCEMENT_OPENAI  
**Priority:** MEDIUM  
**Status:** READY FOR DEVELOPMENT  
**Last Updated:** 2026-07-18
