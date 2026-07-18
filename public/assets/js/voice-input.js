/**
 * MyWisata Application - Voice Input Helper
 * 
 * Provides Web Speech API integration for Indonesian voice input
 * 
 * @package MyWisata
 * @version 1.0.0
 * @since 2026-07-18
 */

const VoiceInput = {
    recognition: null,
    isListening: false,
    language: 'id-ID', // Indonesian
    onResult: null,
    onError: null,
    onStart: null,
    onEnd: null,

    /**
     * Initialize voice recognition
     */
    init: function(options = {}) {
        // Check browser support
        if (!('webkitSpeechRecognition' in window) && !('SpeechRecognition' in window)) {
            console.error('Web Speech API not supported in this browser');
            return false;
        }

        // Create recognition instance
        const SpeechRecognition = window.SpeechRecognition || window.webkitSpeechRecognition;
        this.recognition = new SpeechRecognition();

        // Configure recognition
        this.recognition.lang = options.language || this.language;
        this.recognition.continuous = options.continuous || false;
        this.recognition.interimResults = options.interimResults || false;
        this.recognition.maxAlternatives = options.maxAlternatives || 1;

        // Set up event handlers
        this.recognition.onstart = () => {
            this.isListening = true;
            if (this.onStart) this.onStart();
        };

        this.recognition.onresult = (event) => {
            let transcript = '';
            let isFinal = false;

            for (let i = event.resultIndex; i < event.results.length; i++) {
                transcript += event.results[i][0].transcript;
                if (event.results[i].isFinal) {
                    isFinal = true;
                }
            }

            if (this.onResult) {
                this.onResult(transcript, isFinal);
            }
        };

        this.recognition.onerror = (event) => {
            this.isListening = false;
            if (this.onError) {
                this.onError(event.error);
            }
        };

        this.recognition.onend = () => {
            this.isListening = false;
            if (this.onEnd) this.onEnd();
        };

        return true;
    },

    /**
     * Start listening
     */
    start: function() {
        if (!this.recognition) {
            console.error('Voice recognition not initialized');
            return false;
        }

        if (this.isListening) {
            console.warn('Already listening');
            return false;
        }

        try {
            this.recognition.start();
            return true;
        } catch (error) {
            console.error('Error starting recognition:', error);
            if (this.onError) this.onError('start_error');
            return false;
        }
    },

    /**
     * Stop listening
     */
    stop: function() {
        if (!this.recognition || !this.isListening) {
            return false;
        }

        try {
            this.recognition.stop();
            return true;
        } catch (error) {
            console.error('Error stopping recognition:', error);
            return false;
        }
    },

    /**
     * Set event handlers
     */
    on: function(event, callback) {
        switch (event) {
            case 'result':
                this.onResult = callback;
                break;
            case 'error':
                this.onError = callback;
                break;
            case 'start':
                this.onStart = callback;
                break;
            case 'end':
                this.onEnd = callback;
                break;
        }
    },

    /**
     * Send speech text to server for processing
     */
    processSpeech: function(text, context = 'general') {
        return fetch('/speech/processInput', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify({
                text: text,
                context: context
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                return data;
            } else {
                throw new Error(data.error || 'Processing failed');
            }
        });
    },

    /**
     * Get destination recommendations via voice
     */
    recommendDestinations: function(text) {
        return fetch('/speech/recommendDestinations', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify({
                text: text
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                return data;
            } else {
                throw new Error(data.error || 'Recommendation failed');
            }
        });
    },

    /**
     * Get tour guide recommendations via voice
     */
    recommendTourGuides: function(text) {
        return fetch('/speech/recommendTourGuides', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify({
                text: text
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                return data;
            } else {
                throw new Error(data.error || 'Recommendation failed');
            }
        });
    },

    /**
     * Generate itinerary via voice
     */
    generateItinerary: function(text) {
        return fetch('/speech/generateItinerary', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify({
                text: text
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                return data;
            } else {
                throw new Error(data.error || 'Itinerary generation failed');
            }
        });
    }
};

// Auto-initialize when DOM is ready
document.addEventListener('DOMContentLoaded', function() {
    // Make VoiceInput available globally
    window.VoiceInput = VoiceInput;
    console.log('Voice Input Helper: Initialized successfully');
});
