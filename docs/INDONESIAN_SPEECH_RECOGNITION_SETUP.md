# Indonesian Speech Recognition Setup Guide

## Setup Summary

Komputer Linux ini telah dikonfigurasi untuk menerima input suara bahasa Indonesia dengan menggunakan Python SpeechRecognition library.

## Installation Status

✅ **Completed:**
- Python 3.12.3 installed
- Virtual environment created at `/opt/lampp/htdocs/mywisata/venv`
- SpeechRecognition library installed
- PyAudio installed (for microphone access)
- System dependencies installed (ffmpeg, portaudio)
- Test script created

⚠️ **Audio Configuration:**
- Microphone detected but requires proper audio input device
- ALSA/Jack audio warnings present (common in server environments)

## Usage

### Activate Virtual Environment
```bash
cd /opt/lampp/htdocs/mywisata
source venv/bin/activate
```

### Run Test Script
```bash
python3 test_speech_indonesian.py
```

### Basic Usage Example
```python
import speech_recognition as sr

# Create recognizer instance
r = sr.Recognizer()

# Use microphone as source
with sr.Microphone() as source:
    # Adjust for ambient noise
    r.adjust_for_ambient_noise(source, duration=1)
    
    # Listen to audio
    audio = r.listen(source, timeout=5, phrase_time_limit=10)
    
    # Recognize Indonesian speech
    try:
        text = r.recognize_google(audio, language="id-ID")
        print(f"Recognized: {text}")
    except sr.UnknownValueError:
        print("Could not understand audio")
    except sr.RequestError as e:
        print(f"Error: {e}")
```

## Supported Speech Recognition Engines

### 1. Google Speech Recognition (Default)
```python
text = r.recognize_google(audio, language="id-ID")
```
- Requires internet connection
- Supports Indonesian language (id-ID)
- Free but has usage limits

### 2. Sphinx (Offline)
```python
# Install pocketsphinx
pip install pocketsphinx

text = r.recognize_sphinx(audio, language="id-ID")
```
- Works offline
- Requires Indonesian language model
- Less accurate than Google

### 3. Whisper (OpenAI)
```python
# Install openai-whisper
pip install openai-whisper

text = r.recognize_whisper(audio, language="indonesian")
```
- High accuracy
- Works offline
- Requires more resources

## Integration with MyWisata Application

### JavaScript Web Speech API (Recommended for Web)
For browser-based speech recognition, use the Web Speech API:

```javascript
// Check browser support
if ('webkitSpeechRecognition' in window || 'SpeechRecognition' in window) {
    const SpeechRecognition = window.SpeechRecognition || window.webkitSpeechRecognition;
    const recognition = new SpeechRecognition();
    
    recognition.lang = 'id-ID'; // Indonesian
    recognition.continuous = false;
    recognition.interimResults = false;
    
    recognition.onresult = function(event) {
        const transcript = event.results[0][0].transcript;
        console.log('Recognized:', transcript);
        
        // Send to server
        fetch('/api/speech-input', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ text: transcript })
        });
    };
    
    recognition.onerror = function(event) {
        console.error('Speech recognition error:', event.error);
    };
    
    // Start recognition
    recognition.start();
}
```

### Python Backend Integration
Create API endpoint for speech input:

```php
// app/controllers/SpeechController.php
<?php
class SpeechController extends Controller {
    public function __construct() {
        Middleware::requireRole(['wisatawan', 'tour_guide', 'admin']);
    }
    
    public function processInput() {
        $data = json_decode(file_get_contents('php://input'), true);
        $text = $data['text'] ?? '';
        
        // Process the recognized text
        // Example: Search destinations, booking, etc.
        
        echo json_encode([
            'status' => 'success',
            'text' => $text,
            'processed' => true
        ]);
    }
}
```

## Troubleshooting

### Microphone Not Detected
```bash
# Check available audio devices
arecord -l

# Test microphone recording
arecord -f cd -d 5 test-mic.wav
aplay test-mic.wav
```

### ALSA Errors
The ALSA warnings are normal in server environments. To suppress them:
```bash
export ALSA_DEVICE="default"
```

### Permission Issues
```bash
# Add user to audio group
sudo usermod -a -G audio $USER

# Re-login to apply changes
```

### No Audio Input Device
If running in a headless/server environment:
1. Use USB microphone
2. Configure PulseAudio for remote audio
3. Use Web Speech API in browser instead

## Alternative Solutions

### 1. Browser-Based Speech Recognition
Use Web Speech API for client-side speech recognition:
- No server setup required
- Works in modern browsers (Chrome, Edge, Safari)
- Supports Indonesian language
- Free and easy to implement

### 2. Third-Party Services
- Google Cloud Speech-to-Text
- Amazon Transcribe
- Microsoft Azure Speech Services
- IBM Watson Speech to Text

### 3. Mobile App Integration
Use native speech recognition on mobile devices:
- Android: SpeechRecognizer API
- iOS: SFSpeechRecognizer

## Files Created

1. `/opt/lampp/htdocs/mywisata/venv/` - Python virtual environment
2. `/opt/lampp/htdocs/mywisata/test_speech_indonesian.py` - Test script
3. `/opt/lampp/htdocs/mywisata/docs/INDONESIAN_SPEECH_RECOGNITION_SETUP.md` - This guide

## Next Steps

1. **Test with actual microphone input** - Connect a microphone and run the test script
2. **Implement Web Speech API** - Add browser-based speech recognition to MyWisata
3. **Create speech-enabled features** - Voice search, voice commands, etc.
4. **Add Indonesian language models** - For offline recognition if needed

## Security Considerations

- Speech data sent to Google servers (if using Google Speech Recognition)
- Implement proper authentication for speech input endpoints
- Sanitize speech input to prevent injection attacks
- Consider privacy implications of voice data collection

## Performance Tips

- Use Web Speech API for better performance in browser
- Implement speech recognition with debouncing to avoid excessive API calls
- Cache common speech patterns
- Use offline recognition for better privacy and performance

## Support

For issues with:
- **Python SpeechRecognition**: https://github.com/Uberi/speech_recognition
- **PyAudio**: http://people.csail.mit.edu/hubert/pyaudio/
- **Web Speech API**: https://developer.mozilla.org/en-US/docs/Web/API/Web_Speech_API
