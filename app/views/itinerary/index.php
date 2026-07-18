<?php include APP_ROOT . '/app/views/layouts/header.php'; ?>

<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2><i class="fas fa-route me-2"></i>Itinerary Saya</h2>
        <a href="<?= View::url('itinerary/builder') ?>" class="btn btn-primary">
            <i class="fas fa-plus me-1"></i>Buat Itinerary Baru
        </a>
    </div>

    <?php if (Session::hasFlash('success')): ?>
    <div class="alert alert-success"><?= View::e(Session::getFlash('success')) ?></div>
    <?php endif; ?>
    <?php if (Session::hasFlash('error')): ?>
    <div class="alert alert-danger"><?= View::e(Session::getFlash('error')) ?></div>
    <?php endif; ?>

    <?php if (empty($itineraries)): ?>
    <div class="text-center py-5">
        <i class="fas fa-route fa-3x text-muted mb-3"></i>
        <h4 class="text-muted">Belum ada itinerary</h4>
        <p class="text-muted">Buat itinerary pertama Anda dengan AI Builder</p>
        <a href="<?= View::url('itinerary/builder') ?>" class="btn btn-primary mt-2">
            <i class="fas fa-magic me-1"></i>Mulai Buat Itinerary
        </a>
    </div>
    <?php else: ?>
    <div class="row">
        <?php foreach ($itineraries as $it): ?>
        <div class="col-md-6 col-lg-4 mb-3">
            <div class="card h-100 shadow-sm">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <h5 class="card-title"><?= View::e($it['title']) ?></h5>
                        <span class="badge bg-<?= $it['status'] === 'confirmed' ? 'success' : ($it['status'] === 'completed' ? 'primary' : ($it['status'] === 'cancelled' ? 'danger' : 'secondary')) ?>">
                            <?= ucfirst($it['status']) ?>
                        </span>
                    </div>
                    <p class="text-muted small mb-2">
                        <i class="fas fa-calendar me-1"></i><?= date('d M Y', strtotime($it['start_date'])) ?> - <?= date('d M Y', strtotime($it['end_date'])) ?>
                    </p>
                    <p class="text-muted small mb-2">
                        <i class="fas fa-users me-1"></i><?= $it['num_travelers'] ?> traveler<?= $it['num_travelers'] > 1 ? 's' : '' ?> · 
                        <i class="fas fa-clock me-1"></i><?= $it['num_days'] ?> hari
                    </p>
                    <?php if ($it['total_estimated_cost']): ?>
                    <p class="text-muted small mb-2">
                        <i class="fas fa-wallet me-1"></i>Estimasi: Rp <?= number_format($it['total_estimated_cost'], 0, ',', '.') ?>
                    </p>
                    <?php endif; ?>
                    <?php if ($it['description']): ?>
                    <p class="card-text small text-muted"><?= View::e(mb_substr($it['description'], 0, 100)) ?><?= mb_strlen($it['description']) > 100 ? '...' : '' ?></p>
                    <?php endif; ?>
                </div>
                <div class="card-footer bg-transparent">
                    <a href="<?= View::url('itinerary/detail/' . $it['id']) ?>" class="btn btn-sm btn-outline-primary">
                        <i class="fas fa-eye me-1"></i>Lihat Detail
                    </a>
                    <button class="btn btn-sm btn-outline-danger float-end" 
                            data-id="<?= $it['id'] ?>" 
                            onclick="deleteItinerary(this)">
                        <i class="fas fa-trash"></i>
                    </button>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>
</div>

<script>
function deleteItinerary(btn) {
    if (!confirm('Hapus itinerary ini?')) return;
    fetch(APP_URL + 'itinerary/delete', {
        method: 'POST',
        headers: { 'X-Requested-With': 'XMLHttpRequest' },
        body: new URLSearchParams({
            id: btn.dataset.id,
            csrf_token: '<?= Middleware::csrfToken() ?>'
        })
    }).then(r => r.json()).then(data => {
        if (data.status === 'success') {
            location.reload();
        } else {
            alert(data.message || 'Error');
        }
    });
}
</script>

<?php include APP_ROOT . '/app/views/layouts/footer.php'; ?>
