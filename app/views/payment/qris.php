<?php include APP_ROOT . '/app/views/layouts/header.php'; ?>

<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card shadow">
                <div class="card-header bg-primary text-white text-center">
                    <h4 class="mb-0"><i class="fas fa-qrcode me-2"></i>QRIS Payment</h4>
                </div>
                <div class="card-body text-center">
                    <p class="text-muted mb-3">Scan QR code di bawah dengan e-wallet atau m-banking</p>
                    
                    <div class="bg-white p-3 rounded border mb-3 d-inline-block">
                        <div id="qrcode" class="mx-auto"></div>
                    </div>

                    <div class="mb-3">
                        <h5 class="mb-1">Rp <?= number_format($transaction['net_amount'], 0, ',', '.') ?></h5>
                        <small class="text-muted">Kode: <?= View::e($transaction['transaction_code']) ?></small>
                    </div>

                    <div class="alert alert-info">
                        <i class="fas fa-info-circle me-1"></i>
                        Simulasi: Klik tombol di bawah untuk konfirmasi pembayaran
                    </div>

                    <button type="button" class="btn btn-success w-100" id="confirmBtn" data-id="<?= $transaction['id'] ?>">
                        <i class="fas fa-check me-1"></i>Saya Sudah Bayar
                    </button>

                    <a href="<?= View::url('payments') ?>" class="btn btn-link btn-sm mt-2">Kembali</a>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/qrcodejs@1.0.0/qrcode.min.js"></script>
<script>
new QRCode(document.getElementById('qrcode'), {
    text: '<?= View::e($qr_data) ?>',
    width: 250,
    height: 250,
    colorDark: '#000000',
    colorLight: '#ffffff',
    correctLevel: QRCode.CorrectLevel.M
});

$('#confirmBtn').on('click', function() {
    var btn = $(this);
    btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin me-1"></i>Memproses...');
    
    ajax({
        url: APP_URL + 'payment/confirmQris',
        method: 'POST',
        data: {
            transaction_id: btn.data('id'),
            csrf_token: '<?= $csrf_token ?>'
        },
        success: function(response) {
            if (response.status === 'success') {
                Swal.fire({
                    icon: 'success',
                    title: 'Berhasil',
                    text: response.message,
                    timer: 2000,
                    showConfirmButton: false
                }).then(function() {
                    window.location.href = APP_URL + 'payments';
                });
            } else {
                Swal.fire({ icon: 'error', title: 'Error', text: response.message });
                btn.prop('disabled', false).html('<i class="fas fa-check me-1"></i>Saya Sudah Bayar');
            }
        },
        error: function() {
            Swal.fire({ icon: 'error', title: 'Error', text: 'Terjadi kesalahan' });
            btn.prop('disabled', false).html('<i class="fas fa-check me-1"></i>Saya Sudah Bayar');
        }
    });
});
</script>

<?php include APP_ROOT . '/app/views/layouts/footer.php'; ?>
