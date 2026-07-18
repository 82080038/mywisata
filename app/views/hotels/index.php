<?php include APP_ROOT . '/app/views/layouts/header.php'; ?>

<div class="container py-4">
    <div class="row mb-4">
        <div class="col-md-12">
            <h1 class="mb-3">Hotel & Homestay</h1>
            <p class="text-muted">Temukan akomodasi terbaik untuk perjalanan Anda</p>
        </div>
    </div>
    
    <!-- Search & Filter -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" action="<?= View::url('hotels') ?>">
                <div class="row">
                    <div class="col-md-4 mb-3">
                        <input type="text" class="form-control" name="search" placeholder="Cari hotel..." value="<?= View::e($filters['search'] ?? '') ?>">
                    </div>
                    <div class="col-md-3 mb-3">
                        <input type="text" class="form-control" name="city" placeholder="Kota..." value="<?= View::e($filters['city'] ?? '') ?>">
                    </div>
                    <div class="col-md-3 mb-3">
                        <select class="form-select" name="type">
                            <option value="">Semua Tipe</option>
                            <option value="hotel" <?= (isset($filters['type']) && $filters['type'] == 'hotel') ? 'selected' : '' ?>>Hotel</option>
                            <option value="homestay" <?= (isset($filters['type']) && $filters['type'] == 'homestay') ? 'selected' : '' ?>>Homestay</option>
                            <option value="villa" <?= (isset($filters['type']) && $filters['type'] == 'villa') ? 'selected' : '' ?>>Villa</option>
                            <option value="guesthouse" <?= (isset($filters['type']) && $filters['type'] == 'guesthouse') ? 'selected' : '' ?>>Guesthouse</option>
                        </select>
                    </div>
                    <div class="col-md-2 mb-3">
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="fas fa-search me-2"></i>Cari
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
    
    <!-- Hotels List -->
    <div class="row">
        <div class="col-md-12">
            <?php if (empty($hotels)): ?>
                <div class="empty-state text-center py-5">
                    <i class="fas fa-hotel fa-3x text-muted mb-3"></i>
                    <h5 class="text-muted">Tidak ada hotel ditemukan</h5>
                    <p class="text-muted">Coba ubah filter atau kata kunci pencarian</p>
                    <a href="<?= View::url('hotels') ?>" class="btn btn-primary mt-2">
                        <i class="fas fa-redo me-2"></i>Reset Filter
                    </a>
                </div>
            <?php else: ?>
                <div class="row">
                    <?php foreach ($hotels as $hotel): ?>
                    <div class="col-md-4 mb-4">
                        <div class="card h-100">
                            <?php if (!empty($hotel['main_image'])): ?>
                                <?php if (filter_var($hotel['main_image'], FILTER_VALIDATE_URL)): ?>
                                    <img src="<?= View::e($hotel['main_image']) ?>" class="card-img-top" alt="<?= View::e($hotel['name']) ?>" style="height: 200px; object-fit: cover;">
                                <?php else: ?>
                                    <img src="<?= View::asset('uploads/hotels/' . $hotel['main_image']) ?>" class="card-img-top" alt="<?= View::e($hotel['name']) ?>" style="height: 200px; object-fit: cover;">
                                <?php endif; ?>
                            <?php else: ?>
                                <div class="card-img-top bg-secondary d-flex align-items-center justify-content-center" style="height: 200px;">
                                    <i class="fas fa-hotel text-white" style="font-size: 2rem;"></i>
                                </div>
                            <?php endif; ?>
                            <div class="card-body">
                                <h5 class="card-title"><?= View::e($hotel['name']) ?></h5>
                                <p class="card-text text-muted small">
                                    <i class="fas fa-map-marker-alt me-1"></i><?= View::e($hotel['city'] ?? 'Indonesia') ?>
                                </p>
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <div>
                                        <i class="fas fa-star text-warning"></i> <?= number_format($hotel['rating_avg'], 1) ?>
                                    </div>
                                    <span class="badge bg-info"><?= View::e($hotel['star_rating'] ?? '0') ?> Bintang</span>
                                </div>
                                <div class="d-flex flex-wrap gap-1 mb-2">
                                    <?php if (!empty($hotel['has_prayer_room'])): ?>
                                        <span class="badge bg-success"><i class="fas fa-mosque me-1"></i>Prayer Room</span>
                                    <?php endif; ?>
                                    <?php if (!empty($hotel['is_alcohol_free'])): ?>
                                        <span class="badge bg-danger"><i class="fas fa-ban me-1"></i>Alcohol Free</span>
                                    <?php endif; ?>
                                    <?php if (!empty($hotel['has_women_only_facilities'])): ?>
                                        <span class="badge bg-primary"><i class="fas fa-female me-1"></i>Women Only</span>
                                    <?php endif; ?>
                                    <?php if (!empty($hotel['qibla_direction'])): ?>
                                        <span class="badge bg-info"><i class="fas fa-compass me-1"></i><?= View::e($hotel['qibla_direction']) ?></span>
                                    <?php endif; ?>
                                    <?php if (!empty($hotel['distance_to_mosque'])): ?>
                                        <span class="badge bg-secondary"><i class="fas fa-place-of-worship me-1"></i><?= number_format($hotel['distance_to_mosque'], 1) ?> km</span>
                                    <?php endif; ?>
                                </div>
                                <p class="card-text small text-muted">
                                    <?= View::e($hotel['address'] ?? 'Alamat tidak tersedia') ?>
                                </p>
                                <a href="<?= View::url('hotels/detail?id=' . $hotel['id']) ?>" class="btn btn-primary w-100">
                                    Lihat Detail
                                </a>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php include APP_ROOT . '/app/views/layouts/footer.php'; ?>
