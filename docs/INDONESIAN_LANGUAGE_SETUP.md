# Indonesian Language Setup for Cascade/Devin

## Overview
Devin/Cascade AI assistant has been configured to accept and respond in Indonesian (Bahasa Indonesia) for the MyWisata application.

## Configuration Changes

### 1. Prompting Configuration (`prompting/config.json`)
Added language preferences:
```json
"language": "id",
"locale": "id_ID",
"timezone": "Asia/Jakarta"
```

### 2. OpenAI Configuration (`config/openai.php`)
Added Indonesian language settings:
```php
'language' => getenv('OPENAI_LANGUAGE') ?: 'id',
'locale' => getenv('OPENAI_LOCALE') ?: 'id-ID',
'system_prompt' => getenv('OPENAI_SYSTEM_PROMPT') ?: 'You are a helpful assistant for the MyWisata application, a tour guide platform for Indonesia. Please respond in Indonesian (Bahasa Indonesia) unless specifically asked to use another language. Be helpful, friendly, and provide accurate information about Indonesian tourism destinations, culture, and travel tips.'
```

### 3. OpenAI Service (`app/services/OpenAIService.php`)
Updated to support Indonesian language:
- Added language and locale properties
- Added system prompt configuration
- Modified all AI methods to use Indonesian prompts:
  - `recommendDestinations()` - Indonesian destination recommendations
  - `recommendTourGuides()` - Indonesian tour guide matching
  - `generateItinerary()` - Indonesian trip planning
  - `generateDescription()` - Indonesian content generation
  - `analyzeSentiment()` - Indonesian sentiment analysis
- Added automatic system prompt injection for consistent Indonesian responses

## Features

### Automatic Indonesian Responses
All AI interactions now default to Indonesian:
- System prompts instruct AI to respond in Indonesian
- User prompts are in Indonesian
- Response formatting instructions are in Indonesian

### Customizable Language Settings
You can override defaults via environment variables:
```bash
export OPENAI_LANGUAGE=id
export OPENAI_LOCALE=id-ID
export OPENAI_SYSTEM_PROMPT="Your custom Indonesian system prompt"
```

### Method-Specific Language Control
Each method can use custom system prompts while maintaining Indonesian context:
```php
$response = $openaiService->chat($messages, [
    'use_system_prompt' => false // Use custom system prompt
]);
```

## Usage Examples

### Basic Chat in Indonesian
```php
$openaiService = new OpenAIService();
$messages = [
    ['role' => 'user', 'content' => 'Apa destinasi wisata terbaik di Bali?']
];
$response = $openaiService->chat($messages);
// Response will be in Indonesian
```

### Destination Recommendations
```php
$preferences = [
    'interest' => 'budaya',
    'budget' => 'sedang',
    'duration' => '3 hari'
];
$response = $openaiService->recommendDestinations($preferences);
// Returns Indonesian destination recommendations
```

### Tour Guide Matching
```php
$requirements = [
    'language' => 'Indonesia',
    'specialization' => 'budaya',
    'experience' => 'minimal 2 tahun'
];
$availableGuides = [...];
$response = $openaiService->recommendTourGuides($requirements, $availableGuides);
// Returns Indonesian tour guide recommendations
```

### Itinerary Generation
```php
$destination = 'Yogyakarta';
$duration = 5;
$interests = ['budaya', 'kuliner', 'sejarah'];
$response = $openaiService->generateItinerary($destination, $duration, $interests);
// Returns Indonesian travel itinerary
```

### Sentiment Analysis
```php
$review = 'Pemandu wisata ini sangat ramah dan profesional. Sangat direkomendasikan!';
$response = $openaiService->analyzeSentiment($review);
// Returns sentiment analysis in Indonesian context
```

## Environment Variables

Add to `.env` file:
```env
OPENAI_LANGUAGE=id
OPENAI_LOCALE=id-ID
OPENAI_SYSTEM_PROMPT=Anda adalah asisten yang membantu untuk aplikasi MyWisata, platform pemandu wisata untuk Indonesia. Silakan merespons dalam Bahasa Indonesia kecuali secara khusus diminta untuk menggunakan bahasa lain. Jadilah membantu, ramah, dan berikan informasi akurat tentang destinasi wisata Indonesia, budaya, dan tips perjalanan.
```

## Testing

### Test Indonesian Response
```php
$openaiService = new OpenAIService();
$messages = [
    ['role' => 'user', 'content' => 'Halo, bisa bantu saya?']
];
$response = $openaiService->chat($messages);
echo $response['message']; // Should respond in Indonesian
```

### Test Specific Methods
```bash
# Test destination recommendations
php -r "require 'app/services/OpenAIService.php'; \$service = new OpenAIService(); print_r(\$service->recommendDestinations(['interest' => 'pantai']));"

# Test itinerary generation
php -r "require 'app/services/OpenAIService.php'; \$service = new OpenAIService(); print_r(\$service->generateItinerary('Bali', 3, ['pantai', 'budaya']));"
```

## Benefits

### For Users
- Natural Indonesian language interactions
- Culturally appropriate responses
- Better understanding of Indonesian tourism context
- Localized travel recommendations

### For Developers
- Consistent language handling across all AI features
- Easy to maintain and extend
- Configurable language settings
- Backward compatible with existing code

### For Business
- Improved user experience for Indonesian users
- Better engagement with local market
- Professional Indonesian content generation
- Accurate sentiment analysis of Indonesian reviews

## Troubleshooting

### AI Not Responding in Indonesian
1. Check `config/openai.php` system prompt
2. Verify environment variables are set
3. Check if custom system prompts are overriding defaults
4. Review method-specific prompts in `OpenAIService.php`

### Mixed Language Responses
1. Ensure `use_system_prompt` is not set to false
2. Check if user messages are in Indonesian
3. Verify system prompt is being injected correctly

### API Key Issues
1. Set `OPENAI_API_KEY` in environment
2. Verify API key has sufficient credits
3. Check API endpoint configuration

## Future Enhancements

### Planned Features
- Multi-language support (English, Indonesian, others)
- Language detection from user input
- Regional Indonesian dialects support
- Custom vocabulary for tourism terms
- Language-specific content templates

### Extension Points
- Add language detection middleware
- Create language-specific prompt templates
- Implement translation fallback
- Add language preference per user
- Support for regional tourism terminology

## Related Documentation

- `docs/INDONESIAN_SPEECH_RECOGNITION_SETUP.md` - Voice input setup
- `docs/17_MODUL_AI_TOUR_GUIDE.md` - AI tour guide module
- `config/openai.php` - OpenAI configuration
- `app/services/OpenAIService.php` - AI service implementation

## Support

For issues with Indonesian language setup:
1. Check configuration files
2. Verify environment variables
3. Test basic AI functionality
4. Review OpenAI API status
5. Check system prompt configuration

## Summary

Devin/Cascade is now fully configured to accept and respond in Indonesian for all AI-powered features in the MyWisata application. This includes:
- Chat interactions
- Destination recommendations
- Tour guide matching
- Itinerary generation
- Content generation
- Sentiment analysis

All responses default to Indonesian while maintaining the ability to use custom prompts when needed.
