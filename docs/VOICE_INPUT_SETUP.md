# Voice Input Setup for Indonesian Language

## Overview
Devin/Cascade AI assistant has been configured to accept Indonesian voice input using Web Speech API and process it through OpenAI service for intelligent responses.

## Components Implemented

### 1. Backend Controller (`app/controllers/SpeechController.php`)
Handles speech input processing and AI integration:
- `processInput()` - General speech processing with context-aware responses
- `recommendDestinations()` - Voice-based destination recommendations
- `recommendTourGuides()` - Voice-based tour guide matching
- `generateItinerary()` - Voice-based itinerary generation
- Context-aware system prompts for different use cases
- Indonesian language prompts for all AI interactions

### 2. Frontend Helper (`public/assets/js/voice-input.js`)
Web Speech API integration:
- Browser compatibility detection
- Indonesian language recognition (`id-ID`)
- Event handlers for start, result, error, end
- Server communication for speech processing
- Specialized methods for different voice commands

### 3. UI Component (`app/views/partials/voice-input-button.php`)
Reusable voice input button:
- Visual feedback during listening
- Real-time transcription display
- AI response display
- Toast notifications for status updates
- Animated listening indicator

### 4. Test Page (`app/views/test/voice-input.php`)
Comprehensive testing interface:
- Voice input testing
- Manual text input testing
- Context selection (general, destination, tour_guide, booking, itinerary)
- Example Indonesian prompts
- Browser support detection

### 5. Routing (`app/core/App.php`)
Added speech controller routing:
- `/speech/processInput` - General speech processing
- `/speech/recommendDestinations` - Destination recommendations
- `/speech/recommendTourGuides` - Tour guide recommendations
- `/speech/generateItinerary` - Itinerary generation
- `/test/voiceInput` - Test page

## Usage

### Basic Voice Input
```javascript
// Initialize voice input
VoiceInput.init({
    language: 'id-ID',
    continuous: false,
    interimResults: true
});

// Start listening
VoiceInput.start();

// Stop listening
VoiceInput.stop();
```

### Process Speech with Context
```javascript
VoiceInput.on('result', function(transcript, isFinal) {
    if (isFinal) {
        VoiceInput.processSpeech(transcript, 'destination')
            .then(data => {
                console.log('Response:', data.response);
            });
    }
});
```

### Specialized Voice Commands
```javascript
// Destination recommendations
VoiceInput.recommendDestinations("Cari pantai di Bali")
    .then(data => {
        console.log('Recommendations:', data.recommendations);
    });

// Tour guide recommendations
VoiceInput.recommendTourGuides("Pemandu wisata bahasa Jepang")
    .then(data => {
        console.log('Guides:', data.recommendations);
    });

// Itinerary generation
VoiceInput.generateItinerary("Buat itinerary 3 hari di Yogyakarta")
    .then(data => {
        console.log('Itinerary:', data.itinerary);
    });
```

### Include Voice Input Button
```php
<?php include APP_ROOT . '/app/views/partials/voice-input-button.php'; ?>
```

## Contexts

### General
- System prompt: General Indonesian assistance
- Use cases: General questions, information requests
- Example: "Halo, bisa bantu saya?"

### Destination
- System prompt: Indonesian tourism expert
- Use cases: Destination information, recommendations
- Example: "Rekomendasikan pantai terbaik di Bali"

### Tour Guide
- System prompt: Tour guide matching expert
- Use cases: Finding suitable tour guides
- Example: "Cari pemandu wisata yang bisa bahasa Jepang"

### Booking
- System prompt: Booking assistance
- Use cases: Booking process, payment information
- Example: "Bagaimana cara booking pemandu wisata?"

### Itinerary
- System prompt: Travel planning expert
- Use cases: Trip planning, day-by-day schedules
- Example: "Buat itinerary 3 hari di Yogyakarta"

## API Endpoints

### POST /speech/processInput
Process general speech input:
```json
{
    "text": "Halo, bisa bantu saya?",
    "context": "general"
}
```

Response:
```json
{
    "success": true,
    "input_text": "Halo, bisa bantu saya?",
    "response": "Halo! Saya siap membantu Anda dengan informasi wisata Indonesia...",
    "context": "general",
    "usage": {...}
}
```

### POST /speech/recommendDestinations
Get destination recommendations:
```json
{
    "text": "Cari pantai di Bali"
}
```

Response:
```json
{
    "success": true,
    "input_text": "Cari pantai di Bali",
    "extracted_preferences": {...},
    "recommendations": [...]
}
```

### POST /speech/recommendTourGuides
Get tour guide recommendations:
```json
{
    "text": "Pemandu wisata bahasa Jepang"
}
```

Response:
```json
{
    "success": true,
    "input_text": "Pemandu wisata bahasa Jepang",
    "extracted_requirements": {...},
    "recommendations": [...]
}
```

### POST /speech/generateItinerary
Generate travel itinerary:
```json
{
    "text": "Buat itinerary 3 hari di Yogyakarta"
}
```

Response:
```json
{
    "success": true,
    "input_text": "Buat itinerary 3 hari di Yogyakarta",
    "extracted_details": {...},
    "itinerary": {...}
}
```

## Browser Support

### Supported Browsers
- **Chrome**: Full support (recommended)
- **Edge**: Full support
- **Safari**: Full support
- **Firefox**: Limited support

### Browser Detection
```javascript
const hasSupport = 'webkitSpeechRecognition' in window || 'SpeechRecognition' in window;
```

## Testing

### Access Test Page
Navigate to: `http://localhost/mywisata/test/voiceInput`

### Test Scenarios
1. **Basic Voice Input**: Click microphone button and speak in Indonesian
2. **Manual Text Input**: Type Indonesian text and process
3. **Context Testing**: Try different contexts with appropriate prompts
4. **Browser Support**: Check if your browser supports Web Speech API

### Example Test Prompts
- "Halo, apa kabar?" (General)
- "Rekomendasikan destinasi budaya di Jawa" (Destination)
- "Cari pemandu wisata spesialis sejarah" (Tour Guide)
- "Bagaimana cara booking?" (Booking)
- "Rencanakan perjalanan 2 hari ke Bandung" (Itinerary)

## Integration with Existing Features

### Add to Search Page
```php
// In search view
<?php include APP_ROOT . '/app/views/partials/voice-input-button.php'; ?>
```

### Add to Booking Page
```php
// In booking view
<div class="voice-search-section">
    <p>Atau gunakan suara untuk mencari pemandu wisata:</p>
    <?php include APP_ROOT . '/app/views/partials/voice-input-button.php'; ?>
</div>
```

### Add to AI Tour Guide
```php
// In AI tour guide view
<div class="voice-input-section">
    <p>Berbicara dengan AI Tour Guide:</p>
    <?php include APP_ROOT . '/app/views/partials/voice-input-button.php'; ?>
</div>
```

## Customization

### Change Language
```javascript
VoiceInput.init({
    language: 'en-US', // Change to English
    // or
    language: 'ja-JP', // Japanese
});
```

### Customize System Prompts
Edit `app/controllers/SpeechController.php`:
```php
private function getContextSystemPrompt($context) {
    $prompts = [
        'general' => 'Your custom system prompt...',
        // Add more contexts
    ];
    return $prompts[$context] ?? $prompts['general'];
}
```

### Customize UI
Edit `app/views/partials/voice-input-button.php` to change:
- Button styling
- Animation effects
- Response display format
- Error handling

## Troubleshooting

### Voice Recognition Not Working
1. Check browser support
2. Ensure microphone permissions are granted
3. Check if HTTPS is required (some browsers require HTTPS for microphone access)
4. Verify Web Speech API is enabled in browser settings

### Server Processing Errors
1. Check OpenAI API key is configured
2. Verify speech controller routes are properly set up
3. Check server logs for errors
4. Ensure user is authenticated (speech controller requires login)

### Indonesian Recognition Issues
1. Verify language is set to `id-ID`
2. Check if browser supports Indonesian language
3. Try speaking more clearly
4. Test with different Indonesian phrases

## Security Considerations

### Authentication
- All speech endpoints require user authentication
- Role-based access control (wisatawan, tour_guide, admin)

### Privacy
- Voice data is processed on-the-fly
- No voice recording storage
- Consider adding user consent for voice processing

### Rate Limiting
- Consider implementing rate limiting for speech endpoints
- Prevent abuse of AI processing

## Performance Optimization

### Client-Side
- Debounce voice input to prevent excessive API calls
- Cache common voice commands
- Implement progressive enhancement

### Server-Side
- Implement response caching for common queries
- Use connection pooling for OpenAI API
- Add request queuing for high traffic

## Future Enhancements

### Planned Features
- Voice command shortcuts
- Multi-language support
- Voice biometrics for authentication
- Offline voice recognition
- Voice-activated navigation
- Real-time translation

### Extension Points
- Add custom voice commands
- Integrate with other AI services
- Add voice analytics
- Implement voice training
- Support regional Indonesian dialects

## Related Documentation

- `docs/INDONESIAN_LANGUAGE_SETUP.md` - Indonesian language configuration
- `docs/INDONESIAN_SPEECH_RECOGNITION_SETUP.md` - Server-side speech recognition
- `docs/17_MODUL_AI_TOUR_GUIDE.md` - AI tour guide module
- `config/openai.php` - OpenAI configuration
- `app/services/OpenAIService.php` - AI service implementation

## Summary

Devin/Cascade is now fully configured to accept Indonesian voice input through:
- Web Speech API for browser-based voice recognition
- OpenAI integration for intelligent Indonesian responses
- Context-aware processing for different use cases
- Reusable UI components for easy integration
- Comprehensive testing interface

Users can now interact with the MyWisata application using natural Indonesian voice commands for:
- General assistance
- Destination recommendations
- Tour guide matching
- Booking assistance
- Travel itinerary planning

All voice interactions are processed with Indonesian language prompts and return responses in Bahasa Indonesia.
