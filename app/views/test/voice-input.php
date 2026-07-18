<?php include APP_ROOT . '/app/views/layouts/header.php'; ?>

<div class="container mt-5">
    <div class="row">
        <div class="col-md-8 offset-md-2">
            <div class="card">
                <div class="card-header">
                    <h4><i class="fas fa-microphone me-2"></i>Input Suara Bahasa Indonesia</h4>
                </div>
                <div class="card-body">
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle me-2"></i>
                        Halaman ini untuk testing input suara Bahasa Indonesia menggunakan Web Speech API.
                        Pastikan browser Anda mendukung Web Speech API (Chrome, Edge, Safari).
                    </div>

                    <!-- Voice Input Button -->
                    <div class="text-center mb-4">
                        <?php include APP_ROOT . '/app/views/partials/voice-input-button.php'; ?>
                    </div>

                    <!-- Manual Test Section -->
                    <div class="mt-4">
                        <h5>Test Manual</h5>
                        <div class="mb-3">
                            <label for="manualInput" class="form-label">Input Teks Manual:</label>
                            <textarea class="form-control" id="manualInput" rows="3" placeholder="Ketik teks untuk test pemrosesan..."></textarea>
                        </div>
                        <button id="processManualBtn" class="btn btn-success">
                            <i class="fas fa-paper-plane me-2"></i>Proses Teks
                        </button>
                        <div id="manualResult" class="mt-3" style="display: none;">
                            <div class="card">
                                <div class="card-body">
                                    <h6 class="card-subtitle mb-2 text-muted">Hasil:</h6>
                                    <p id="manualResponse" class="card-text"></p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Context Selection -->
                    <div class="mt-4">
                        <h5>Konteks</h5>
                        <div class="btn-group" role="group">
                            <button type="button" class="btn btn-outline-primary context-btn active" data-context="general">Umum</button>
                            <button type="button" class="btn btn-outline-primary context-btn" data-context="destination">Destinasi</button>
                            <button type="button" class="btn btn-outline-primary context-btn" data-context="tour_guide">Pemandu Wisata</button>
                            <button type="button" class="btn btn-outline-primary context-btn" data-context="booking">Booking</button>
                            <button type="button" class="btn btn-outline-primary context-btn" data-context="itinerary">Itinerary</button>
                        </div>
                    </div>

                    <!-- Example Prompts -->
                    <div class="mt-4">
                        <h5>Contoh Prompt Bahasa Indonesia:</h5>
                        <ul class="list-group">
                            <li class="list-group-item">
                                <strong>Umum:</strong> "Halo, bisa bantu saya?"
                            </li>
                            <li class="list-group-item">
                                <strong>Destinasi:</strong> "Rekomendasikan pantai terbaik di Bali"
                            </li>
                            <li class="list-group-item">
                                <strong>Pemandu Wisata:</strong> "Cari pemandu wisata yang bisa bahasa Jepang"
                            </li>
                            <li class="list-group-item">
                                <strong>Booking:</strong> "Bagaimana cara booking pemandu wisata?"
                            </li>
                            <li class="list-group-item">
                                <strong>Itinerary:</strong> "Buat itinerary 3 hari di Yogyakarta"
                            </li>
                        </ul>
                    </div>

                    <!-- Browser Support Info -->
                    <div class="mt-4">
                        <h5>Dukungan Browser</h5>
                        <div id="browserSupport" class="alert alert-warning">
                            <i class="fas fa-exclamation-triangle me-2"></i>
                            Memeriksa dukungan browser...
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    let currentContext = 'general';

    // Check browser support
    function checkBrowserSupport() {
        const supportDiv = document.getElementById('browserSupport');
        const hasSupport = 'webkitSpeechRecognition' in window || 'SpeechRecognition' in window;
        
        if (hasSupport) {
            supportDiv.className = 'alert alert-success';
            supportDiv.innerHTML = '<i class="fas fa-check-circle me-2"></i>Browser Anda mendukung Web Speech API';
        } else {
            supportDiv.className = 'alert alert-danger';
            supportDiv.innerHTML = '<i class="fas fa-times-circle me-2"></i>Browser Anda tidak mendukung Web Speech API. Gunakan Chrome, Edge, atau Safari.';
        }
    }

    checkBrowserSupport();

    // Context button handlers
    document.querySelectorAll('.context-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            document.querySelectorAll('.context-btn').forEach(b => b.classList.remove('active'));
            this.classList.add('active');
            currentContext = this.dataset.context;
        });
    });

    // Manual text processing
    document.getElementById('processManualBtn').addEventListener('click', function() {
        const text = document.getElementById('manualInput').value.trim();
        
        if (!text) {
            if (typeof Toast !== 'undefined') {
                Toast.error('Masukkan teks terlebih dahulu');
            }
            return;
        }

        this.disabled = true;
        this.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Memproses...';

        fetch('/speech/processInput', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify({
                text: text,
                context: currentContext
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                document.getElementById('manualResponse').textContent = data.response;
                document.getElementById('manualResult').style.display = 'block';
                
                if (typeof Toast !== 'undefined') {
                    Toast.success('Teks diproses berhasil');
                }
            } else {
                throw new Error(data.error || 'Processing failed');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            if (typeof Toast !== 'undefined') {
                Toast.error('Gagal memproses teks: ' + error.message);
            }
        })
        .finally(() => {
            this.disabled = false;
            this.innerHTML = '<i class="fas fa-paper-plane me-2"></i>Proses Teks';
        });
    });

    // Override voice input context
    if (typeof VoiceInput !== 'undefined') {
        const originalProcess = VoiceInput.processSpeech;
        VoiceInput.processSpeech = function(text, context) {
            return originalProcess.call(this, text, currentContext);
        };
    }
});
</script>

<?php include APP_ROOT . '/app/views/layouts/footer.php'; ?>
