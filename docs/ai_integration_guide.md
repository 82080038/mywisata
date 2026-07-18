# AI INTEGRATION GUIDE
# Tour Guide Application

## OVERVIEW

This guide provides comprehensive instructions for using OpenAI integration in the Tour Guide Application to enable AI-powered features like smart recommendations, natural language processing, and advanced chat capabilities.

## OPENAI SERVICE

### OpenAIService Class
Location: `app/services/OpenAIService.php`

### Basic Operations

#### Chat Completion
```php
$openai = new \App\Services\OpenAIService();
$response = $openai->chat([
    ['role' => 'system', 'content' => 'You are a helpful assistant.'],
    ['role' => 'user', 'content' => 'Hello!']
]);
```

#### Destination Recommendations
```php
$preferences = [
    'interests' => ['beach', 'culture'],
    'budget' => 'medium',
    'duration' => 5,
    'group_size' => 2,
    'language' => 'english'
];

$result = $openai->recommendDestinations($preferences, 5);
```

#### Tour Guide Recommendations
```php
$requirements = [
    'destination' => 'Bali',
    'date' => '2026-08-01',
    'duration' => 4,
    'languages' => ['english', 'indonesian'],
    'specializations' => ['culture', 'adventure']
];

$availableGuides = [
    ['id' => 1, 'name' => 'John', 'languages' => ['english'], 'specializations' => ['culture']],
    ['id' => 2, 'name' => 'Jane', 'languages' => ['indonesian'], 'specializations' => ['adventure']]
];

$result = $openai->recommendTourGuides($requirements, $availableGuides);
```

#### Trip Itinerary Generation
```php
$destination = 'Bali';
$duration = 5;
$interests = ['beach', 'culture', 'food'];

$result = $openai->generateItinerary($destination, $duration, $interests);
```

#### Content Generation
```php
$destinationName = 'Ubud';
$keyFeatures = ['rice terraces', 'temples', 'art markets', 'monkey forest'];

$result = $openai->generateDescription($destinationName, $keyFeatures);
```

#### Sentiment Analysis
```php
$text = 'The tour guide was excellent and very knowledgeable about the local culture.';

$result = $openai->analyzeSentiment($text);
```

#### Chat Conversation
```php
$conversationHistory = [
    ['role' => 'user', 'content' => 'I want to visit Bali'],
    ['role' => 'assistant', 'content' => 'Bali is a beautiful destination with beaches and temples.']
];

$userMessage = 'What are the best beaches in Bali?';
$context = ['user_id' => 123, 'current_page' => '/destinations'];

$result = $openai->chatConversation($conversationHistory, $userMessage, $context);
```

#### Translation
```php
$text = 'Hello, how are you?';
$targetLanguage = 'Indonesian';

$result = $openai->translate($text, $targetLanguage);
```

## AI CONTROLLER

### AIController Class
Location: `app/controllers/AIController.php`

### Endpoints

#### Get Recommendations
```php
POST /ai/recommendations
Parameters:
- interests: array
- budget: string
- duration: int
- group_size: int
- language: string
```

#### Get Tour Guide Recommendations
```php
POST /ai/guide-recommendations
Parameters:
- destination: string
- date: string
- duration: int
- languages: array
- specializations: array
- available_guides: array
```

#### Generate Itinerary
```php
POST /ai/itinerary
Parameters:
- destination: string
- duration: int
- interests: array
```

#### Generate Content
```php
POST /ai/generate-content
Parameters:
- type: string
- name: string
- features: array
```

#### Analyze Sentiment
```php
POST /ai/sentiment
Parameters:
- text: string
```

#### Chat
```php
POST /ai/chat
Parameters:
- conversation_history: string (JSON)
- message: string
- current_page: string
```

#### Translate
```php
POST /ai/translate
Parameters:
- text: string
- target_language: string
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

## CONFIGURATION

### Environment Variables
```env
OPENAI_API_KEY=sk-your-api-key-here
OPENAI_MODEL=gpt-4
OPENAI_TEMPERATURE=0.7
OPENAI_MAX_TOKENS=1000
OPENAI_ORGANIZATION=
```

### Configuration Options
- **OPENAI_API_KEY** - Your OpenAI API key (required)
- **OPENAI_MODEL** - Model to use (gpt-4, gpt-3.5-turbo)
- **OPENAI_TEMPERATURE** - Creativity level (0.0-1.0)
- **OPENAI_MAX_TOKENS** - Maximum response tokens
- **OPENAI_ORGANIZATION** - Organization ID (optional)

## COST MANAGEMENT

### Usage Tracking
Monitor API usage to control costs:
- Track tokens per request
- Set daily limits per user
- Monitor total usage
- Alert on high usage

### Cost Optimization
1. Use appropriate model for each task
2. Set reasonable max_tokens limits
3. Cache AI responses when possible
4. Implement rate limiting
5. Monitor usage regularly

## ERROR HANDLING

### Common Errors
- **API Key Not Configured** - Check environment variables
- **Rate Limit Exceeded** - Implement backoff strategy
- **Invalid Response** - Handle JSON parsing errors
- **Timeout** - Increase timeout or reduce complexity

### Fallback Strategy
When OpenAI API fails:
1. Return cached response if available
2. Provide default recommendations
3. Show user-friendly error message
4. Log error for debugging

## BEST PRACTICES

1. **Always use API keys securely** - Never commit to version control
2. **Implement rate limiting** - Prevent abuse and control costs
3. **Cache responses** - Reduce API calls and costs
4. **Monitor usage** - Track tokens and costs
5. **Handle errors gracefully** - Provide fallback responses
6. **Use appropriate models** - Balance quality and cost
7. **Optimize prompts** - Reduce token usage
8. **Respect privacy** - Don't send sensitive data

## TROUBLESHOOTING

### API Key Not Working
- Verify key is correct
- Check key has proper permissions
- Ensure billing is set up
- Check OpenAI service status

### High Costs
- Review usage patterns
- Implement caching
- Reduce max_tokens
- Switch to cheaper model
- Add rate limiting

### Slow Responses
- Use gpt-3.5-turbo instead of gpt-4
- Reduce max_tokens
- Implement streaming
- Use caching
- Optimize prompts

### Poor Quality Responses
- Improve system prompts
- Provide better context
- Adjust temperature
- Use more capable model
- Add examples in prompts

## RESOURCES

- [OpenAI Documentation](https://platform.openai.com/docs)
- [OpenAIService.php](../app/services/OpenAIService.php)
- [AIController.php](../app/controllers/AIController.php)
- [OpenAI Setup Guide](openai_setup_guide.md)

---

**Version:** 1.0  
**Last Updated:** 2026-07-18
