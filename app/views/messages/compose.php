<?php include APP_ROOT . '/app/views/layouts/header.php'; ?>

<?php
$roleLabels = ['admin' => 'Admin', 'merchant' => 'Penjual Souvenir', 'tour_guide' => 'Tour Guide', 'wisatawan' => 'Wisatawan'];
$roleColors = ['admin' => 'danger', 'merchant' => 'success', 'tour_guide' => 'info', 'wisatawan' => 'secondary'];
$roleIcons = ['admin' => 'fa-cog', 'merchant' => 'fa-store', 'tour_guide' => 'fa-user-tie', 'wisatawan' => 'fa-user'];
$ctxLabels = ['hotel' => 'Penginapan', 'product' => 'Produk Souvenir', 'tour_guide' => 'Tour Guide', 'general' => 'Umum'];
$ctxIcons = ['hotel' => 'fa-bed', 'product' => 'fa-gift', 'tour_guide' => 'fa-user-tie', 'general' => 'fa-comment'];
?>

<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <h2 class="mb-4"><i class="fas fa-pen me-2"></i>Pesan Baru</h2>

            <div class="card shadow-sm">
                <div class="card-body">
                    <!-- Recipient Info -->
                    <div class="d-flex align-items-center mb-4 p-3 bg-light rounded">
                        <div class="bg-<?= $roleColors[$recipient['role']] ?? 'secondary' ?> bg-opacity-10 rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 55px; height: 55px;">
                            <i class="fas <?= $roleIcons[$recipient['role']] ?? 'fa-user' ?> text-<?= $roleColors[$recipient['role']] ?? 'secondary' ?> fa-lg"></i>
                        </div>
                        <div>
                            <h5 class="mb-0"><?= View::e($recipient['name']) ?></h5>
                            <span class="badge bg-<?= $roleColors[$recipient['role']] ?? 'secondary' ?>"><?= $roleLabels[$recipient['role']] ?? 'User' ?></span>
                        </div>
                    </div>

                    <!-- Context Info -->
                    <?php if (!empty($contextInfo)): ?>
                    <div class="alert alert-info d-flex align-items-center">
                        <i class="fas <?= $ctxIcons[$contextType] ?? 'fa-comment' ?> fa-lg me-3"></i>
                        <div>
                            <strong>Terkait <?= $ctxLabels[$contextType] ?? 'Item' ?>:</strong><br>
                            <?= View::e($contextInfo['name']) ?>
                        </div>
                    </div>
                    <?php endif; ?>

                    <form id="composeForm">
                        <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">
                        <input type="hidden" name="recipient_id" value="<?= $recipient['id'] ?>">
                        <input type="hidden" name="context_type" value="<?= View::e($contextType) ?>">
                        <input type="hidden" name="context_id" value="<?= View::e($contextId ?? '') ?>">
                        <input type="hidden" name="subject" value="<?= !empty($contextInfo) ? View::e($contextInfo['name']) : '' ?>">

                        <div class="mb-3">
                            <label class="form-label">Pesan <span class="text-danger">*</span></label>
                            <textarea name="message" class="form-control" rows="5" required placeholder="Tulis pesan Anda...&#10;&#10;Misal: Apakah kamar deluxe tersedia untuk tanggal 20-22 Juli? Apakah ada sarapan?"></textarea>
                        </div>

                        <div class="d-flex justify-content-between">
                            <a href="javascript:history.back()" class="btn btn-secondary">Batal</a>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-paper-plane me-2"></i>Kirim Pesan
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.getElementById('composeForm').addEventListener('submit', function(e) {
    e.preventDefault();
    var formData = new FormData(this);

    fetch(window.APP_URL + 'messages/start', {
        method: 'POST',
        headers: { 'X-Requested-With': 'XMLHttpRequest' },
        body: formData
    })
    .then(function(r) { return r.json(); })
    .then(function(data) {
        if (data.status === 'success') {
            window.location.href = data.redirect;
        } else {
            Swal.fire({ icon: 'error', title: 'Error', text: data.message });
        }
    });
});
</script>

<?php include APP_ROOT . '/app/views/layouts/footer.php'; ?>
