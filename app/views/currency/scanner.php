<?php include APP_ROOT . '/app/views/layouts/header.php'; ?>

<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow">
                <div class="card-header bg-primary text-white">
                    <h4 class="mb-0"><i class="fas fa-camera me-2"></i>Scan Harga - Konverter Mata Uang</h4>
                </div>
                <div class="card-body">
                    <p class="text-muted">Arahkan kamera ke harga yang ingin dikonversi. Sistem akan membaca angka secara otomatis dan mengkonversinya ke mata uang pilihan Anda.</p>

                    <div class="mb-3">
                        <label class="form-label">Mata Uang Target</label>
                        <select id="targetCurrency" class="form-select">
                            <?php foreach ($currencies as $code => $cur): ?>
                            <option value="<?= $code ?>" <?= $code === $user_currency ? 'selected' : '' ?>>
                                <?= $cur['currency_symbol'] ?> <?= $code ?> - <?= View::e($cur['currency_name']) ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="text-center mb-3">
                        <video id="cameraVideo" autoplay playsinline class="img-fluid rounded border" style="max-width: 100%;"></video>
                        <canvas id="captureCanvas" style="display: none;"></canvas>
                    </div>

                    <div class="d-grid gap-2 mb-3">
                        <button id="startCameraBtn" class="btn btn-primary">
                            <i class="fas fa-camera me-2"></i>Buka Kamera
                        </button>
                        <button id="captureBtn" class="btn btn-success" disabled>
                            <i class="fas fa-bullseye me-2"></i>Scan Harga
                        </button>
                    </div>

                    <div id="processingDiv" style="display: none;" class="text-center py-3">
                        <div class="spinner-border text-primary" role="status"></div>
                        <p class="mt-2">Memproses gambar...</p>
                    </div>

                    <div id="resultDiv" style="display: none;" class="alert alert-info">
                        <h6 class="alert-heading"><i class="fas fa-check-circle me-2"></i>Hasil Scan</h6>
                        <div id="rawText" class="mb-2"></div>
                        <div id="detectedPrices" class="mb-3"></div>
                        <div id="convertedPrices"></div>
                    </div>

                    <hr>

                    <div class="mb-3">
                        <h6>Atau input manual</h6>
                        <div class="row g-2 align-items-end">
                            <div class="col-md-4">
                                <label class="form-label small">Jumlah</label>
                                <input type="number" id="manualAmount" class="form-control" placeholder="50000">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label small">Dari Mata Uang</label>
                                <select id="manualFrom" class="form-select">
                                    <option value="IDR" selected>IDR - Rupiah</option>
                                    <?php foreach ($currencies as $code => $cur): ?>
                                    <?php if ($code !== 'IDR'): ?>
                                    <option value="<?= $code ?>"><?= $code ?> - <?= View::e($cur['currency_name']) ?></option>
                                    <?php endif; ?>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <button class="btn btn-outline-primary w-100" onclick="convertManual()">
                                    <i class="fas fa-exchange-alt me-1"></i>Konversi
                                </button>
                            </div>
                        </div>
                        <div id="manualResult" class="mt-2"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/tesseract.js@5.0.4/dist/tesseract.min.js"></script>
<script>
let stream = null;

document.getElementById('startCameraBtn').addEventListener('click', async function() {
    try {
        stream = await navigator.mediaDevices.getUserMedia({ video: { facingMode: 'environment' } });
        const video = document.getElementById('cameraVideo');
        video.srcObject = stream;
        document.getElementById('captureBtn').disabled = false;
        this.disabled = true;
        this.innerHTML = '<i class="fas fa-check me-2"></i>Kamera Aktif';
    } catch (err) {
        Swal.fire({ icon: 'error', title: 'Error', text: 'Tidak dapat mengakses kamera: ' + err.message });
    }
});

document.getElementById('captureBtn').addEventListener('click', function() {
    const video = document.getElementById('cameraVideo');
    const canvas = document.getElementById('captureCanvas');
    const ctx = canvas.getContext('2d');

    canvas.width = video.videoWidth;
    canvas.height = video.videoHeight;
    ctx.drawImage(video, 0, 0);

    document.getElementById('processingDiv').style.display = 'block';
    document.getElementById('resultDiv').style.display = 'none';

    Tesseract.recognize(canvas, 'eng', {
        logger: function(m) {
            if (m.status === 'recognizing text') {
                document.querySelector('#processingDiv p').textContent = 'Membaca teks... ' + Math.round(m.progress * 100) + '%';
            }
        }
    }).then(function(result) {
        document.getElementById('processingDiv').style.display = 'none';

        const rawText = result.data.text.trim();
        document.getElementById('rawText').innerHTML = '<small class="text-muted">Teks terbaca:</small><br><code>' + rawText.replace(/\n/g, '<br>') + '</code>';

        // Extract prices - look for patterns like Rp 50000, $10, 50000, etc.
        const pricePatterns = [
            /Rp\.?\s*([\d.,]+)/gi,
            /\$\s*([\d.,]+)/gi,
            /([\d.,]+)\s*(?:k|rb|ribu|juta|jt)/gi,
            /\b([\d]{1,3}(?:[.,]\d{3})*(?:[.,]\d+)?)\b/g
        ];

        let prices = [];
        let matches;

        // Try Rp pattern first
        const rpRegex = /Rp\.?\s*([\d.,]+)/gi;
        while ((matches = rpRegex.exec(rawText)) !== null) {
            let numStr = matches[1].replace(/\./g, '').replace(',', '.');
            let num = parseFloat(numStr);
            if (!isNaN(num) && num > 0) {
                prices.push({ amount: num, currency: 'IDR', raw: matches[0] });
            }
        }

        // Try dollar pattern
        const dollarRegex = /\$\s*([\d.,]+)/gi;
        while ((matches = dollarRegex.exec(rawText)) !== null) {
            let num = parseFloat(matches[1].replace(/,/g, ''));
            if (!isNaN(num) && num > 0) {
                prices.push({ amount: num, currency: 'USD', raw: matches[0] });
            }
        }

        // If no prices found, try plain numbers
        if (prices.length === 0) {
            const numRegex = /(\d[\d.,]*)/g;
            while ((matches = numRegex.exec(rawText)) !== null) {
                let numStr = matches[1].replace(/\./g, '').replace(',', '.');
                let num = parseFloat(numStr);
                if (!isNaN(num) && num > 100) {
                    prices.push({ amount: num, currency: 'IDR', raw: matches[0] });
                }
            }
        }

        const targetCurrency = document.getElementById('targetCurrency').value;

        if (prices.length === 0) {
            document.getElementById('detectedPrices').innerHTML = '<small>Tidak ada harga terdeteksi. Coba scan ulang atau input manual.</small>';
            document.getElementById('convertedPrices').innerHTML = '';
        } else {
            let html = '<small class="text-muted">Harga terdeteksi:</small><ul class="list-unstyled mt-1">';
            prices.forEach(function(p) {
                html += '<li><strong>' + p.raw + '</strong> (' + p.currency + ')</li>';
            });
            html += '</ul>';

            html += '<small class="text-muted">Konversi ke ' + targetCurrency + ':</small><ul class="list-unstyled mt-1">';
            prices.forEach(function(p) {
                html += '<li class="mb-1"><span class="badge bg-success me-1">→</span> <strong id="conv_' + p.amount + '_' + p.currency + '">Loading...</strong></li>';
                convertPrice(p.amount, p.currency, targetCurrency, 'conv_' + p.amount + '_' + p.currency);
            });
            html += '</ul>';

            document.getElementById('detectedPrices').innerHTML = html;
            document.getElementById('convertedPrices').innerHTML = '';
        }

        document.getElementById('resultDiv').style.display = 'block';
    }).catch(function(err) {
        document.getElementById('processingDiv').style.display = 'none';
        Swal.fire({ icon: 'error', title: 'OCR Error', text: err.message });
    });
});

function convertPrice(amount, from, to, elementId) {
    var formData = new FormData();
    formData.append('amount', amount);
    formData.append('from', from);
    formData.append('to', to);

    fetch(window.APP_URL + 'currency/convert', {
        method: 'POST',
        headers: { 'X-Requested-With': 'XMLHttpRequest' },
        body: formData
    })
    .then(function(r) { return r.json(); })
    .then(function(data) {
        if (data.status === 'success') {
            document.getElementById(elementId).textContent = data.formatted + ' (' + to + ')';
        }
    });
}

function convertManual() {
    var amount = document.getElementById('manualAmount').value;
    var from = document.getElementById('manualFrom').value;
    var to = document.getElementById('targetCurrency').value;

    if (!amount || amount <= 0) {
        Swal.fire({ icon: 'error', title: 'Error', text: 'Masukkan jumlah yang valid' });
        return;
    }

    var formData = new FormData();
    formData.append('amount', amount);
    formData.append('from', from);
    formData.append('to', to);

    fetch(window.APP_URL + 'currency/convert', {
        method: 'POST',
        headers: { 'X-Requested-With': 'XMLHttpRequest' },
        body: formData
    })
    .then(function(r) { return r.json(); })
    .then(function(data) {
        if (data.status === 'success') {
            document.getElementById('manualResult').innerHTML =
                '<div class="alert alert-success"><strong>' + data.formatted + '</strong><br><small>' + amount + ' ' + from + ' = ' + data.converted_amount + ' ' + to + '</small></div>';
        }
    });
}
</script>

<?php include APP_ROOT . '/app/views/layouts/footer.php'; ?>
