<?php
// Inline translate widget - can be embedded in any detail page
// Requires $csrf_token to be available in the view
?>
<div class="card border-info mb-3">
    <div class="card-header bg-info text-white py-2">
        <div class="d-flex justify-content-between align-items-center">
            <span class="fw-bold small">
                <i class="fas fa-language me-1"></i>Real-Time Translator
            </span>
            <button class="btn btn-sm btn-light py-0 px-2" type="button" data-bs-toggle="collapse" data-bs-target="#translateWidget" aria-expanded="false">
                <i class="fas fa-chevron-down"></i>
            </button>
        </div>
    </div>
    <div class="collapse" id="translateWidget">
        <div class="card-body">
            <div class="row align-items-end mb-2">
                <div class="col-5">
                    <label class="form-label small mb-1">Dari</label>
                    <select class="form-select form-select-sm" id="inlineSourceLang">
                        <?php
                        $db = Database::getInstance();
                        $langs = $db->query("SELECT code, name, native_name FROM languages ORDER BY id")->fetchAll();
                        foreach ($langs as $lang):
                        ?>
                        <option value="<?= $lang['code'] ?>" <?= $lang['code'] === 'id' ? 'selected' : '' ?>><?= View::e($lang['name']) ?> (<?= View::e($lang['native_name']) ?>)</option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-2 text-center">
                    <button class="btn btn-outline-info btn-sm py-0 px-2" onclick="inlineSwapLangs()" title="Tukar">
                        <i class="fas fa-exchange-alt"></i>
                    </button>
                </div>
                <div class="col-5">
                    <label class="form-label small mb-1">Ke</label>
                    <select class="form-select form-select-sm" id="inlineTargetLang">
                        <?php foreach ($langs as $lang): ?>
                        <option value="<?= $lang['code'] ?>" <?= $lang['code'] === 'en' ? 'selected' : '' ?>><?= View::e($lang['name']) ?> (<?= View::e($lang['native_name']) ?>)</option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <div class="btn-group w-100 mb-2">
                <button class="btn btn-primary btn-sm" id="inlineSpeakBtn" onclick="inlineStartListening()">
                    <i class="fas fa-microphone me-1"></i>Bicara
                </button>
                <button class="btn btn-outline-primary btn-sm" onclick="inlineOpenFullWidget()">
                    <i class="fas fa-expand me-1"></i>Mode Percakapan
                </button>
            </div>

            <div id="inlineSourceText" class="border rounded p-2 mb-2 small" style="min-height: 40px;">
                <em class="text-muted">Tekan "Bicara" dan mulai berbicara...</em>
            </div>

            <div id="inlineTranslatedText" class="border rounded p-2 small bg-light" style="min-height: 40px;">
                <em class="text-muted">Terjemahan akan muncun di sini...</em>
            </div>

            <div class="d-flex gap-1 mt-2">
                <button class="btn btn-success btn-sm flex-grow-1" id="inlinePlayBtn" onclick="inlineSpeakTranslation()" disabled>
                    <i class="fas fa-volume-up me-1"></i>Dengar
                </button>
                <button class="btn btn-outline-secondary btn-sm" onclick="inlineCopyTranslation()">
                    <i class="fas fa-copy"></i>
                </button>
            </div>

            <div class="mt-2">
                <small class="text-muted fw-bold">Frasa Cepat:</small>
                <div class="d-flex flex-wrap gap-1 mt-1">
                    <button class="btn btn-outline-info btn-sm py-0 px-2" style="font-size:11px;" onclick="inlineQuickTranslate('Halo, selamat datang!')">Halo</button>
                    <button class="btn btn-outline-info btn-sm py-0 px-2" style="font-size:11px;" onclick="inlineQuickTranslate('Berapa harga tiket?')">Harga tiket?</button>
                    <button class="btn btn-outline-info btn-sm py-0 px-2" style="font-size:11px;" onclick="inlineQuickTranslate('Di mana toilet?')">Toilet?</button>
                    <button class="btn btn-outline-info btn-sm py-0 px-2" style="font-size:11px;" onclick="inlineQuickTranslate('Terima kasih!')">Terima kasih</button>
                    <button class="btn btn-outline-info btn-sm py-0 px-2" style="font-size:11px;" onclick="inlineQuickTranslate('Jam berapa buka?')">Jam buka?</button>
                </div>
            </div>

            <small class="text-muted d-block mt-2 text-center" id="inlineStatus">
                <i class="fas fa-circle text-success" style="font-size:6px;"></i> Siap
            </small>
        </div>
    </div>
</div>

<script>
if (typeof inlineTranslInitialized === 'undefined') {
    var inlineTranslInitialized = true;
    var inlineRecognition = null;
    var inlineIsListening = false;

    function inlineInitRec() {
        var SR = window.SpeechRecognition || window.webkitSpeechRecognition;
        if (!SR) return null;
        var r = new SR();
        r.continuous = false;
        r.interimResults = true;
        r.maxAlternatives = 1;
        return r;
    }

    function inlineStartListening() {
        if (inlineIsListening) { inlineStopListening(); return; }
        inlineRecognition = inlineInitRec();
        if (!inlineRecognition) { alert('Browser tidak mendukung Web Speech API. Gunakan Chrome/Edge.'); return; }

        inlineRecognition.lang = document.getElementById('inlineSourceLang').value;
        inlineIsListening = true;

        var btn = document.getElementById('inlineSpeakBtn');
        btn.innerHTML = '<i class="fas fa-stop me-1"></i>Stop';
        btn.classList.replace('btn-primary', 'btn-danger');

        document.getElementById('inlineSourceText').innerHTML = '<em class="text-muted">Mendengarkan...</em>';
        document.getElementById('inlineTranslatedText').innerHTML = '<em class="text-muted">Menunggu...</em>';
        document.getElementById('inlinePlayBtn').disabled = true;

        var finalText = '';
        inlineRecognition.onresult = function(e) {
            var interim = '';
            for (var i = e.resultIndex; i < e.results.length; i++) {
                if (e.results[i].isFinal) finalText += e.results[i][0].transcript;
                else interim += e.results[i][0].transcript;
            }
            document.getElementById('inlineSourceText').innerHTML = 
                '<span>' + finalText + '</span><em class="text-muted">' + interim + '</em>';
        };
        inlineRecognition.onerror = function(e) { inlineStopListening(); };
        inlineRecognition.onend = function() {
            if (finalText) {
                document.getElementById('inlineSourceText').textContent = finalText;
                inlineDoTranslate(finalText, function(result) {
                    document.getElementById('inlineTranslatedText').innerHTML = result;
                    document.getElementById('inlinePlayBtn').disabled = false;
                    inlineSpeak(result, document.getElementById('inlineTargetLang').value);
                });
            }
            inlineStopListening();
        };
        inlineRecognition.start();
    }

    function inlineStopListening() {
        if (inlineRecognition && inlineIsListening) inlineRecognition.stop();
        inlineIsListening = false;
        var btn = document.getElementById('inlineSpeakBtn');
        btn.innerHTML = '<i class="fas fa-microphone me-1"></i>Bicara';
        btn.classList.replace('btn-danger', 'btn-primary');
    }

    function inlineDoTranslate(text, cb) {
        document.getElementById('inlineStatus').innerHTML = '<i class="fas fa-spinner fa-spin text-warning" style="font-size:6px;"></i> Menerjemahkan...';
        var fd = new FormData();
        fd.append('csrf_token', '<?= $csrf_token ?? "" ?>');
        fd.append('source_lang', document.getElementById('inlineSourceLang').value);
        fd.append('target_lang', document.getElementById('inlineTargetLang').value);
        fd.append('text', text);
        fetch(window.APP_URL + 'translation/translate', {
            method: 'POST', headers: {'X-Requested-With':'XMLHttpRequest'}, body: fd
        })
        .then(function(r){return r.json();})
        .then(function(d){
            document.getElementById('inlineStatus').innerHTML = '<i class="fas fa-circle text-success" style="font-size:6px;"></i> ' + (d.cached ? 'Cached' : 'Selesai');
            if (d.status === 'success') cb(d.translated_text);
            else cb('<span class="text-danger">Error</span>');
        })
        .catch(function(){ cb('<span class="text-danger">Error koneksi</span>'); });
    }

    function inlineSpeak(text, lang) {
        if (!('speechSynthesis' in window)) return;
        window.speechSynthesis.cancel();
        var u = new SpeechSynthesisUtterance(text);
        u.lang = lang; u.rate = 0.9;
        var voices = window.speechSynthesis.getVoices();
        var v = voices.find(function(v){return v.lang.startsWith(lang);});
        if (v) u.voice = v;
        window.speechSynthesis.speak(u);
    }

    function inlineSpeakTranslation() {
        var t = document.getElementById('inlineTranslatedText').textContent;
        if (t) inlineSpeak(t, document.getElementById('inlineTargetLang').value);
    }

    function inlineCopyTranslation() {
        var t = document.getElementById('inlineTranslatedText').textContent;
        navigator.clipboard.writeText(t);
    }

    function inlineQuickTranslate(text) {
        document.getElementById('inlineSourceText').textContent = text;
        inlineDoTranslate(text, function(result) {
            document.getElementById('inlineTranslatedText').innerHTML = result;
            document.getElementById('inlinePlayBtn').disabled = false;
            inlineSpeak(result, document.getElementById('inlineTargetLang').value);
        });
    }

    function inlineSwapLangs() {
        var s = document.getElementById('inlineSourceLang');
        var t = document.getElementById('inlineTargetLang');
        var tmp = s.value; s.value = t.value; t.value = tmp;
    }

    function inlineOpenFullWidget() {
        window.open(window.APP_URL + 'translation/widget', '_blank', 'width=800,height=700');
    }
}
</script>
