<?php include APP_ROOT . '/app/views/layouts/header.php'; ?>

<div class="container py-5">
    <h2 class="fw-bold mb-4"><i class="fas fa-search me-2"></i>Pencarian</h2>

    <form method="GET" action="<?= View::url('search') ?>" class="mb-4">
        <div class="row g-2">
            <div class="col-md-6">
                <input type="text" name="q" value="<?= isset($query) ? View::e($query) : '' ?>" class="form-control form-control-lg" placeholder="Cari destinasi, hotel, restoran, event...">
            </div>
            <div class="col-md-2">
                <select name="type" class="form-select form-select-lg">
                    <option value="">Semua</option>
                    <option value="destinations" <?= ($type ?? '') === 'destinations' ? 'selected' : '' ?>>Destinasi</option>
                    <option value="hotels" <?= ($type ?? '') === 'hotels' ? 'selected' : '' ?>>Hotel</option>
                    <option value="restaurants" <?= ($type ?? '') === 'restaurants' ? 'selected' : '' ?>>Restoran</option>
                    <option value="events" <?= ($type ?? '') === 'events' ? 'selected' : '' ?>>Event</option>
                    <option value="tour_guides" <?= ($type ?? '') === 'tour_guides' ? 'selected' : '' ?>>Tour Guide</option>
                </select>
            </div>
            <div class="col-md-2">
                <input type="text" name="city" value="<?= isset($filters['city']) ? View::e($filters['city']) : '' ?>" class="form-control form-control-lg" placeholder="Kota">
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-primary btn-lg w-100">
                    <i class="fas fa-search me-1"></i>Cari
                </button>
            </div>
        </div>
    </form>

    <?php if (isset($results) && !empty($results)): ?>
        <p class="text-muted mb-3">Ditemukan <?= count($results) ?> hasil</p>
        <div class="row g-4">
            <?php foreach ($results as $result): ?>
                <div class="col-md-6 col-lg-4">
                    <div class="card h-100 shadow-sm">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-start mb-2">
                                <span class="badge bg-info text-dark"><?= View::e($result['type']) ?></span>
                                <?php if (!empty($result['rating'])): ?>
                                    <span class="text-warning">
                                        <i class="fas fa-star"></i> <?= number_format($result['rating'], 1) ?>
                                    </span>
                                <?php endif; ?>
                            </div>
                            <h5 class="card-title"><?= View::e($result['name']) ?></h5>
                            <p class="card-text text-muted small"><?= View::e($result['description'] ?? '') ?></p>
                            <?php if (!empty($result['price'])): ?>
                                <p class="fw-bold text-primary mb-2"><?= View::currency($result['price']) ?></p>
                            <?php endif; ?>
                            <a href="<?= $result['url'] ?? '#' ?>" class="btn btn-sm btn-outline-primary">
                                Lihat Detail <i class="fas fa-arrow-right ms-1"></i>
                            </a>
                            <?php if ($result['type'] === 'tour_guide' && Session::get('user_id')): ?>
                            <a href="<?= View::url('messages/compose?to=' . $result['id'] . '&context=tour_guide&context_id=' . $result['id']) ?>" 
                               class="btn btn-sm btn-outline-success ms-1">
                                <i class="fas fa-comments me-1"></i>Chat
                            </a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php elseif (isset($results)): ?>
        <div class="text-center py-5">
            <i class="fas fa-search fa-4x text-muted mb-3"></i>
            <h4 class="text-muted">Tidak ada hasil ditemukan</h4>
            <p class="text-muted">Coba gunakan kata kunci lain atau ubah filter pencarian</p>
        </div>
    <?php endif; ?>
</div>

<?php include APP_ROOT . '/app/views/layouts/footer.php'; ?>
