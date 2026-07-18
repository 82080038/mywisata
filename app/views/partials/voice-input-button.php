<!-- Voice Input Button Component -->
<div class="voice-input-container">
    <button id="voiceInputBtn" class="btn btn-primary voice-input-btn" title="Input Suara Bahasa Indonesia">
        <i class="fas fa-microphone"></i>
        <span class="voice-status">Tekan untuk bicara</span>
    </button>
    <div id="voiceResult" class="voice-result mt-2" style="display: none;">
        <div class="card">
            <div class="card-body">
                <h6 class="card-subtitle mb-2 text-muted">Terdeteksi:</h6>
                <p id="voiceText" class="card-text"></p>
                <div id="voiceResponse" class="voice-response mt-2"></div>
            </div>
        </div>
    </div>
</div>

<style>
.voice-input-container {
    position: relative;
    display: inline-block;
}

.voice-input-btn {
    position: relative;
    overflow: hidden;
}

.voice-input-btn.listening {
    animation: pulse 1.5s infinite;
    background-color: #dc3545;
}

.voice-input-btn.listening .voice-status {
    content: 'Mendengarkan...';
}

@keyframes pulse {
    0% {
        box-shadow: 0 0 0 0 rgba(220, 53, 69, 0.7);
    }
    70% {
        box-shadow: 0 0 0 10px rgba(220, 53, 69, 0);
    }
    100% {
        box-shadow: 0 0 0 0 rgba(220, 53, 69, 0);
    }
}

.voice-result {
    position: absolute;
    top: 100%;
    left: 0;
    right: 0;
    z-index: 1000;
    min-width: 300px;
}

.voice-response {
    padding: 10px;
    background-color: #f8f9fa;
    border-radius: 5px;
    border-left: 4px solid #0d6efd;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const voiceBtn = document.getElementById('voiceInputBtn');
    const voiceResult = document.getElementById('voiceResult');
    const voiceText = document.getElementById('voiceText');
    const voiceResponse = document.getElementById('voiceResponse');
    const voiceStatus = voiceBtn.querySelector('.voice-status');

    if (!voiceBtn) return;

    // Initialize voice input
    if (typeof VoiceInput !== 'undefined') {
        VoiceInput.init({
            language: 'id-ID',
            continuous: false,
            interimResults: true
        });

        // Set up event handlers
        VoiceInput.on('start', function() {
            voiceBtn.classList.add('listening');
            voiceStatus.textContent = 'Mendengarkan...';
            voiceResult.style.display = 'none';
        });

        VoiceInput.on('result', function(transcript, isFinal) {
            voiceText.textContent = transcript;
            voiceResult.style.display = 'block';

            if (isFinal) {
                voiceStatus.textContent = 'Memproses...';
                
                // Send to server for processing
                VoiceInput.processSpeech(transcript, 'general')
                    .then(data => {
                        voiceResponse.innerHTML = '<strong>Respon:</strong> ' + data.response;
                        voiceStatus.textContent = 'Tekan untuk bicara';
                        voiceBtn.classList.remove('listening');
                        
                        // Show toast notification
                        if (typeof Toast !== 'undefined') {
                            Toast.success('Suara diproses berhasil');
                        }
                    })
                    .catch(error => {
                        voiceResponse.innerHTML = '<strong>Error:</strong> ' + error.message;
                        voiceStatus.textContent = 'Tekan untuk bicara';
                        voiceBtn.classList.remove('listening');
                        
                        if (typeof Toast !== 'undefined') {
                            Toast.error('Gagal memproses suara');
                        }
                    });
            }
        });

        VoiceInput.on('error', function(error) {
            console.error('Voice recognition error:', error);
            voiceStatus.textContent = 'Error: ' + error;
            voiceBtn.classList.remove('listening');
            
            if (typeof Toast !== 'undefined') {
                Toast.error('Error pengenalan suara: ' + error);
            }
        });

        VoiceInput.on('end', function() {
            if (!voiceBtn.classList.contains('listening')) {
                voiceStatus.textContent = 'Tekan untuk bicara';
            }
        });

        // Button click handler
        voiceBtn.addEventListener('click', function() {
            if (VoiceInput.isListening) {
                VoiceInput.stop();
            } else {
                VoiceInput.start();
            }
        });
    } else {
        console.error('VoiceInput not available');
        voiceBtn.disabled = true;
        voiceStatus.textContent = 'Tidak didukung';
    }
});
</script>
