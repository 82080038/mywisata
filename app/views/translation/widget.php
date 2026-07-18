<?php include APP_ROOT . '/app/views/layouts/header.php'; ?>

<div class="container py-4">
    <nav aria-label="breadcrumb" class="mb-3">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="<?= View::url('home') ?>">Beranda</a></li>
            <li class="breadcrumb-item active">Real-Time Translator</li>
        </ol>
    </nav>

    <div class="row justify-content-center">
        <div class="col-md-10 col-lg-8">
            <div class="card shadow">
                <div class="card-header bg-primary text-white">
                    <h4 class="mb-0">
                        <i class="fas fa-language me-2"></i>Real-Time Audio Translator
                    </h4>
                    <small>Terjemahkan percakapan secara langsung dengan suara</small>
                </div>
                <div class="card-body">

                    <!-- Language Selector -->
                    <div class="row align-items-end mb-4">
                        <div class="col-5">
                            <label class="form-label small fw-bold">Bahasa Anda (Tourist)</label>
                            <select class="form-select" id="sourceLang">
                                <?php foreach ($languages as $lang): ?>
                                <option value="<?= $lang['code'] ?>" <?= $lang['code'] === 'en' ? 'selected' : '' ?>>
                                    <?= View::e($lang['name']) ?> (<?= View::e($lang['native_name']) ?>)
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-2 text-center">
                            <button class="btn btn-outline-primary btn-sm" id="swapLangs" title="Tukar bahasa">
                                <i class="fas fa-exchange-alt"></i>
                            </button>
                        </div>
                        <div class="col-5">
                            <label class="form-label small fw-bold">Bahasa Lokal (Guide/Lokal)</label>
                            <select class="form-select" id="targetLang">
                                <?php foreach ($languages as $lang): ?>
                                <option value="<?= $lang['code'] ?>" <?= $lang['code'] === 'id' ? 'selected' : '' ?>>
                                    <?= View::e($lang['name']) ?> (<?= View::e($lang['native_name']) ?>)
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <!-- Mode Toggle -->
                    <div class="btn-group w-100 mb-4" role="group">
                        <button type="button" class="btn btn-outline-primary active" id="modeSingle" onclick="setMode('single')">
                            <i class="fas fa-microphone me-1"></i>Mode Bicara Satu Arah
                        </button>
                        <button type="button" class="btn btn-outline-primary" id="modeConversation" onclick="setMode('conversation')">
                            <i class="fas fa-comments me-1"></i>Mode Percakapan Dua Arah
                        </button>
                    </div>

                    <!-- Single Mode -->
                    <div id="singleModePanel">
                        <div class="row">
                            <!-- Tourist Side -->
                            <div class="col-md-6">
                                <div class="card border-primary">
                                    <div class="card-header bg-light">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <span class="fw-bold">
                                                <i class="fas fa-user me-1"></i>Tourist
                                                (<span id="sourceLangLabel">English</span>)
                                            </span>
                                            <button class="btn btn-primary btn-sm" id="btnSpeakSource" onclick="startListening('source')">
                                                <i class="fas fa-microphone"></i> Bicara
                                            </button>
                                        </div>
                                    </div>
                                    <div class="card-body" style="min-height: 120px;">
                                        <div id="sourceText" class="text-muted">
                                            <em>Tekan tombol "Bicara" dan mulai berbicara...</em>
                                        </div>
                                        <div id="sourceListening" class="text-center mt-2" style="display:none;">
                                            <span class="badge bg-danger pulse">
                                                <i class="fas fa-circle me-1"></i>Mendengarkan...
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Translation Result -->
                            <div class="col-md-6">
                                <div class="card border-success">
                                    <div class="card-header bg-light">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <span class="fw-bold">
                                                <i class="fas fa-volume-up me-1"></i>Terjemahan
                                                (<span id="targetLangLabel">Bahasa Indonesia</span>)
                                            </span>
                                            <div class="btn-group">
                                                <button class="btn btn-success btn-sm" id="btnSpeakTarget" onclick="speakTranslation()" disabled>
                                                    <i class="fas fa-volume-up"></i> Dengar
                                                </button>
                                                <button class="btn btn-outline-secondary btn-sm" onclick="copyTranslation()">
                                                    <i class="fas fa-copy"></i>
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="card-body" style="min-height: 120px;">
                                        <div id="translatedText" class="text-muted">
                                            <em>Terjemahan akan muncul di sini...</em>
                                        </div>
                                        <div id="translatingIndicator" class="text-center mt-2" style="display:none;">
                                            <span class="badge bg-warning text-dark">
                                                <i class="fas fa-spinner fa-spin me-1"></i>Menerjemahkan...
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Conversation Mode -->
                    <div id="conversationModePanel" style="display:none;">
                        <div class="text-center mb-3">
                            <div class="alert alert-info py-2">
                                <i class="fas fa-info-circle me-1"></i>
                                Mode percakapan: Tekan tombol sesuai siapa yang sedang bicara.
                                Sistem akan otomatis menerjemahkan ke bahasa lawan bicara.
                            </div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-6 text-center">
                                <button class="btn btn-primary btn-lg w-100" id="convTouristBtn" onclick="startConversation('tourist')">
                                    <i class="fas fa-microphone me-2"></i>Tourist Bicara
                                </button>
                                <small class="text-muted d-block mt-1" id="convTouristLang">English</small>
                            </div>
                            <div class="col-6 text-center">
                                <button class="btn btn-success btn-lg w-100" id="convLocalBtn" onclick="startConversation('local')">
                                    <i class="fas fa-microphone me-2"></i>Guide/Lokal Bicara
                                </button>
                                <small class="text-muted d-block mt-1" id="convLocalLang">Bahasa Indonesia</small>
                            </div>
                        </div>

                        <!-- Conversation History -->
                        <div class="card">
                            <div class="card-header bg-light d-flex justify-content-between align-items-center">
                                <span class="fw-bold"><i class="fas fa-comments me-1"></i>Riwayat Percakapan</span>
                                <button class="btn btn-outline-secondary btn-sm" onclick="clearConversation()">
                                    <i class="fas fa-trash me-1"></i>Bersihkan
                                </button>
                            </div>
                            <div class="card-body" id="conversationHistory" style="max-height: 300px; overflow-y: auto; min-height: 100px;">
                                <p class="text-muted text-center"><em>Belum ada percakapan. Mulai bicara!</em></p>
                            </div>
                        </div>
                    </div>

                    <!-- Quick Phrases -->
                    <div class="mt-4">
                        <h6><i class="fas fa-book me-1 text-primary"></i>Frasa Cepat</h6>
                        <div class="d-flex flex-wrap gap-2">
                            <button class="btn btn-outline-primary btn-sm" onclick="quickTranslate('Halo, selamat datang!')">Halo, selamat datang!</button>
                            <button class="btn btn-outline-primary btn-sm" onclick="quickTranslate('Berapa harga tiket masuk?')">Berapa harga tiket?</button>
                            <button class="btn btn-outline-primary btn-sm" onclick="quickTranslate('Di mana toilet terdekat?')">Di mana toilet?</button>
                            <button class="btn btn-outline-primary btn-sm" onclick="quickTranslate('Bisa bicara lebih pelan?')">Bicara pelan?</button>
                            <button class="btn btn-outline-primary btn-sm" onclick="quickTranslate('Terima kasih banyak!')">Terima kasih!</button>
                            <button class="btn btn-outline-primary btn-sm" onclick="quickTranslate('Apakah ada pemandu berbahasa Inggris?')">Pemandu bahasa Inggris?</button>
                            <button class="btn btn-outline-primary btn-sm" onclick="quickTranslate('Jam berapa buka?')">Jam berapa buka?</button>
                            <button class="btn btn-outline-primary btn-sm" onclick="quickTranslate('Bisa minta foto bersama?')">Foto bersama?</button>
                        </div>
                    </div>

                    <!-- Status -->
                    <div class="mt-3 text-center">
                        <small class="text-muted" id="statusBar">
                            <i class="fas fa-circle text-success me-1" style="font-size: 8px;"></i>
                            Siap digunakan
                        </small>
                    </div>
                </div>
            </div>

            <!-- Info Card -->
            <div class="card mt-3 border-info">
                <div class="card-body">
                    <h6><i class="fas fa-lightbulb text-warning me-1"></i>Cara Penggunaan:</h6>
                    <ul class="small text-muted mb-0">
                        <li><strong>Mode Satu Arah:</strong> Tourist bicara → sistem terjemahkan ke bahasa lokal → mainkan audio</li>
                        <li><strong>Mode Percakapan:</strong> Tekan tombol sesuai siapa bicara, sistem terjemahkan ke bahasa lawan</li>
                        <li>Izinkan akses mikrofon saat browser meminta permission</li>
                        <li>Gunakan frasa cepat untuk komunikasi instan</li>
                        <li>Terjemahan di-cache untuk penggunaan berulang</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.pulse {
    animation: pulse-animation 1.5s infinite;
}
@keyframes pulse-animation {
    0% { opacity: 1; }
    50% { opacity: 0.5; }
    100% { opacity: 1; }
}
.conv-bubble-tourist {
    background: #cfe8ff;
    border-radius: 12px 12px 4px 12px;
    padding: 10px 14px;
    margin-bottom: 8px;
    max-width: 80%;
    margin-left: auto;
}
.conv-bubble-local {
    background: #d1e7dd;
    border-radius: 12px 12px 12px 4px;
    padding: 10px 14px;
    margin-bottom: 8px;
    max-width: 80%;
}
.conv-original {
    font-size: 0.85em;
    color: #6c757d;
    margin-bottom: 4px;
}
.conv-translated {
    font-weight: 500;
}
.conv-speaker {
    font-size: 0.75em;
    color: #6c757d;
    margin-bottom: 2px;
}
</style>

<script>
var csrfToken = '<?= $csrf_token ?>';
var currentMode = 'single';
var recognition = null;
var isListening = false;
var conversationMessages = [];

// Initialize Speech Recognition
function initSpeechRecognition() {
    var SpeechRecognition = window.SpeechRecognition || window.webkitSpeechRecognition;
    if (!SpeechRecognition) {
        alert('Browser Anda tidak mendukung Web Speech API. Gunakan Chrome atau Edge.');
        return null;
    }
    var rec = new SpeechRecognition();
    rec.continuous = false;
    rec.interimResults = true;
    rec.maxAlternatives = 1;
    return rec;
}

// Update language labels
function updateLangLabels() {
    var srcSelect = document.getElementById('sourceLang');
    var tgtSelect = document.getElementById('targetLang');
    document.getElementById('sourceLangLabel').textContent = srcSelect.options[srcSelect.selectedIndex].text.split('(')[0].trim();
    document.getElementById('targetLangLabel').textContent = tgtSelect.options[tgtSelect.selectedIndex].text.split('(')[0].trim();
    document.getElementById('convTouristLang').textContent = srcSelect.options[srcSelect.selectedIndex].text.split('(')[0].trim();
    document.getElementById('convLocalLang').textContent = tgtSelect.options[tgtSelect.selectedIndex].text.split('(')[0].trim();
}

document.getElementById('sourceLang').addEventListener('change', updateLangLabels);
document.getElementById('targetLang').addEventListener('change', updateLangLabels);

// Swap languages
document.getElementById('swapLangs').addEventListener('click', function() {
    var src = document.getElementById('sourceLang');
    var tgt = document.getElementById('targetLang');
    var tmp = src.value;
    src.value = tgt.value;
    tgt.value = tmp;
    updateLangLabels();
});

// Set mode
function setMode(mode) {
    currentMode = mode;
    document.getElementById('modeSingle').classList.toggle('active', mode === 'single');
    document.getElementById('modeConversation').classList.toggle('active', mode === 'conversation');
    document.getElementById('singleModePanel').style.display = mode === 'single' ? 'block' : 'none';
    document.getElementById('conversationModePanel').style.display = mode === 'conversation' ? 'block' : 'none';
}

// Start listening (single mode)
function startListening(side) {
    if (isListening) {
        stopListening();
        return;
    }

    var lang = document.getElementById('sourceLang').value;
    recognition = initSpeechRecognition();
    if (!recognition) return;

    recognition.lang = lang;
    isListening = true;

    var btn = document.getElementById('btnSpeakSource');
    btn.innerHTML = '<i class="fas fa-stop"></i> Berhenti';
    btn.classList.remove('btn-primary');
    btn.classList.add('btn-danger');

    document.getElementById('sourceListening').style.display = 'block';
    document.getElementById('sourceText').innerHTML = '<em>Mendengarkan...</em>';
    document.getElementById('translatedText').innerHTML = '<em>Menunggu...</em>';
    document.getElementById('btnSpeakTarget').disabled = true;

    var finalTranscript = '';

    recognition.onresult = function(event) {
        var interimTranscript = '';
        for (var i = event.resultIndex; i < event.results.length; i++) {
            if (event.results[i].isFinal) {
                finalTranscript += event.results[i][0].transcript;
            } else {
                interimTranscript += event.results[i][0].transcript;
            }
        }
        document.getElementById('sourceText').innerHTML = 
            '<span class="text-dark">' + finalTranscript + '</span>' +
            '<span class="text-muted"><em>' + interimTranscript + '</em></span>';
    };

    recognition.onerror = function(event) {
        document.getElementById('sourceText').innerHTML = '<span class="text-danger">Error: ' + event.error + '</span>';
        stopListening();
    };

    recognition.onend = function() {
        if (finalTranscript) {
            document.getElementById('sourceText').innerHTML = finalTranscript;
            translateText(finalTranscript, function(result) {
                document.getElementById('translatedText').innerHTML = result;
                document.getElementById('btnSpeakTarget').disabled = false;
                // Auto-speak
                speakText(result, document.getElementById('targetLang').value);
            });
        }
        stopListening();
    };

    recognition.start();
}

function stopListening() {
    if (recognition && isListening) {
        recognition.stop();
    }
    isListening = false;
    var btn = document.getElementById('btnSpeakSource');
    btn.innerHTML = '<i class="fas fa-microphone"></i> Bicara';
    btn.classList.remove('btn-danger');
    btn.classList.add('btn-primary');
    document.getElementById('sourceListening').style.display = 'none';
}

// Translate text via API
function translateText(text, callback) {
    document.getElementById('translatingIndicator').style.display = 'block';
    var formData = new FormData();
    formData.append('csrf_token', csrfToken);
    formData.append('source_lang', document.getElementById('sourceLang').value);
    formData.append('target_lang', document.getElementById('targetLang').value);
    formData.append('text', text);

    fetch(window.APP_URL + 'translation/translate', {
        method: 'POST',
        headers: { 'X-Requested-With': 'XMLHttpRequest' },
        body: formData
    })
    .then(function(r) { return r.json(); })
    .then(function(data) {
        document.getElementById('translatingIndicator').style.display = 'none';
        if (data.status === 'success') {
            callback(data.translated_text);
            updateStatus('Terjemahan ' + (data.cached ? '(cached)' : '(baru)') + (data.fallback ? ' - fallback' : ''));
        } else {
            callback('<span class="text-danger">Error: ' + (data.message || 'Gagal menerjemahkan') + '</span>');
        }
    })
    .catch(function(err) {
        document.getElementById('translatingIndicator').style.display = 'none';
        callback('<span class="text-danger">Error koneksi</span>');
    });
}

// Speak text using TTS
function speakText(text, lang) {
    if (!('speechSynthesis' in window)) {
        console.warn('TTS not supported');
        return;
    }
    window.speechSynthesis.cancel();
    var utterance = new SpeechSynthesisUtterance(text);
    utterance.lang = lang;
    utterance.rate = 0.9;
    utterance.pitch = 1;

    // Try to find matching voice
    var voices = window.speechSynthesis.getVoices();
    var matchedVoice = voices.find(function(v) { return v.lang.startsWith(lang); });
    if (matchedVoice) utterance.voice = matchedVoice;

    window.speechSynthesis.speak(utterance);
}

function speakTranslation() {
    var text = document.getElementById('translatedText').textContent;
    if (text) speakText(text, document.getElementById('targetLang').value);
}

function copyTranslation() {
    var text = document.getElementById('translatedText').textContent;
    navigator.clipboard.writeText(text).then(function() {
        Swal.fire({ icon: 'success', title: 'Tersalin!', timer: 1000, showConfirmButton: false });
    });
}

// Quick translate
function quickTranslate(text) {
    document.getElementById('sourceText').textContent = text;
    translateText(text, function(result) {
        document.getElementById('translatedText').innerHTML = result;
        document.getElementById('btnSpeakTarget').disabled = false;
        speakText(result, document.getElementById('targetLang').value);
    });
}

// Conversation mode
function startConversation(speaker) {
    if (isListening) {
        stopListening();
        return;
    }

    var lang, fromLang, toLang;
    if (speaker === 'tourist') {
        lang = document.getElementById('sourceLang').value;
        fromLang = document.getElementById('sourceLang').value;
        toLang = document.getElementById('targetLang').value;
    } else {
        lang = document.getElementById('targetLang').value;
        fromLang = document.getElementById('targetLang').value;
        toLang = document.getElementById('sourceLang').value;
    }

    recognition = initSpeechRecognition();
    if (!recognition) return;

    recognition.lang = lang;
    isListening = true;

    var btnId = speaker === 'tourist' ? 'convTouristBtn' : 'convLocalBtn';
    var btn = document.getElementById(btnId);
    btn.innerHTML = '<i class="fas fa-stop me-2"></i>Berhenti';
    btn.classList.add('btn-danger');

    var finalTranscript = '';

    recognition.onresult = function(event) {
        for (var i = event.resultIndex; i < event.results.length; i++) {
            if (event.results[i].isFinal) {
                finalTranscript += event.results[i][0].transcript;
            }
        }
    };

    recognition.onerror = function(event) {
        stopConversation(speaker);
    };

    recognition.onend = function() {
        if (finalTranscript) {
            // Translate
            var formData = new FormData();
            formData.append('csrf_token', csrfToken);
            formData.append('source_lang', fromLang);
            formData.append('target_lang', toLang);
            formData.append('text', finalTranscript);

            fetch(window.APP_URL + 'translation/translate', {
                method: 'POST',
                headers: { 'X-Requested-With': 'XMLHttpRequest' },
                body: formData
            })
            .then(function(r) { return r.json(); })
            .then(function(data) {
                if (data.status === 'success') {
                    addConversationMessage(speaker, finalTranscript, data.translated_text, fromLang, toLang);
                    // Auto-speak translation
                    speakText(data.translated_text, toLang);
                }
            });
        }
        stopConversation(speaker);
    };

    recognition.start();
}

function stopConversation(speaker) {
    isListening = false;
    if (recognition) recognition.stop();
    var btnId = speaker === 'tourist' ? 'convTouristBtn' : 'convLocalBtn';
    var btn = document.getElementById(btnId);
    btn.classList.remove('btn-danger');
    if (speaker === 'tourist') {
        btn.innerHTML = '<i class="fas fa-microphone me-2"></i>Tourist Bicara';
    } else {
        btn.innerHTML = '<i class="fas fa-microphone me-2"></i>Guide/Lokal Bicara';
    }
}

function addConversationMessage(speaker, original, translated, fromLang, toLang) {
    var speakerName = speaker === 'tourist' ? 'Tourist' : 'Guide/Lokal';
    var bubbleClass = speaker === 'tourist' ? 'conv-bubble-tourist' : 'conv-bubble-local';

    var html = '<div class="' + bubbleClass + '">' +
        '<div class="conv-speaker">' + speakerName + ' (' + fromLang + ' → ' + toLang + ')</div>' +
        '<div class="conv-original">' + original + '</div>' +
        '<div class="conv-translated"><i class="fas fa-arrow-right me-1 text-muted"></i>' + translated + '</div>' +
        '</div>';

    var history = document.getElementById('conversationHistory');
    if (conversationMessages.length === 0) {
        history.innerHTML = '';
    }
    conversationMessages.push({ speaker: speaker, original: original, translated: translated });
    history.insertAdjacentHTML('beforeend', html);
    history.scrollTop = history.scrollHeight;
}

function clearConversation() {
    conversationMessages = [];
    document.getElementById('conversationHistory').innerHTML = 
        '<p class="text-muted text-center"><em>Belum ada percakapan. Mulai bicara!</em></p>';
}

function updateStatus(msg) {
    document.getElementById('statusBar').innerHTML = 
        '<i class="fas fa-circle text-success me-1" style="font-size: 8px;"></i>' + msg;
}

// Initialize
updateLangLabels();

// Load voices for TTS
if ('speechSynthesis' in window) {
    window.speechSynthesis.onvoiceschanged = function() {
        console.log('TTS voices loaded:', window.speechSynthesis.getVoices().length);
    };
}
</script>

<?php include APP_ROOT . '/app/views/layouts/footer.php'; ?>
