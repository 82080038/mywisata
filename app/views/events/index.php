<?php include APP_ROOT . '/app/views/layouts/header.php'; ?>

<div class="container py-4">
    <div class="row mb-4">
        <div class="col-md-12">
            <h1 class="mb-3">Event & Budaya</h1>
            <p class="text-muted">Temukan event budaya dan festival menarik</p>
        </div>
    </div>
    
    <!-- Search & Filter -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" action="<?= View::url('events') ?>">
                <div class="row">
                    <div class="col-md-4 mb-3">
                        <input type="text" class="form-control" name="search" placeholder="Cari event..." value="<?= View::e($filters['search'] ?? '') ?>">
                    </div>
                    <div class="col-md-3 mb-3">
                        <input type="text" class="form-control" name="city" placeholder="Kota..." value="<?= View::e($filters['city'] ?? '') ?>">
                    </div>
                    <div class="col-md-3 mb-3">
                        <select class="form-select" name="category">
                            <option value="">Semua Kategori</option>
                            <option value="festival" <?= (isset($filters['category']) && $filters['category'] == 'festival') ? 'selected' : '' ?>>Festival</option>
                            <option value="seni" <?= (isset($filters['category']) && $filters['category'] == 'seni') ? 'selected' : '' ?>>Seni</option>
                            <option value="kuliner" <?= (isset($filters['category']) && $filters['category'] == 'kuliner') ? 'selected' : '' ?>>Kuliner</option>
                            <option value="olahraga" <?= (isset($filters['category']) && $filters['category'] == 'olahraga') ? 'selected' : '' ?>>Olahraga</option>
                            <option value="budaya" <?= (isset($filters['category']) && $filters['category'] == 'budaya') ? 'selected' : '' ?>>Budaya</option>
                            <option value="religi" <?= (isset($filters['category']) && $filters['category'] == 'religi') ? 'selected' : '' ?>>Religi</option>
                            <option value="other" <?= (isset($filters['category']) && $filters['category'] == 'other') ? 'selected' : '' ?>>Lainnya</option>
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
    
    <!-- Upcoming Events -->
    <?php if (!empty($upcoming)): ?>
    <div class="row mb-4">
        <div class="col-md-12">
            <h3 class="mb-3">Event Mendatang</h3>
            <div class="row">
                <?php foreach ($upcoming as $event): ?>
                <div class="col-md-4 mb-4">
                    <div class="card h-100">
                        <?php if (!empty($event['main_image'])): ?>
                            <img src="<?= View::asset('uploads/events/' . $event['main_image']) ?>" class="card-img-top" alt="<?= View::e($event['name'] ?? 'Event') ?>" style="height: 200px; object-fit: cover;">
                        <?php else: ?>
                            <div class="card-img-top bg-secondary d-flex align-items-center justify-content-center" style="height: 200px;">
                                <i class="fas fa-calendar-alt text-white" style="font-size: 2rem;"></i>
                            </div>
                        <?php endif; ?>
                        <div class="card-body">
                            <h5 class="card-title"><?= View::e($event['name'] ?? 'Event Name') ?></h5>
                            <p class="card-text text-muted small">
                                <i class="fas fa-calendar me-1"></i><?= View::date($event['event_date'] ?? null) ?>
                            </p>
                            <p class="card-text text-muted small">
                                <i class="fas fa-map-marker-alt me-1"></i><?= View::e($event['city'] ?? 'Unknown') ?>
                            </p>
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <i class="fas fa-star text-warning"></i> <?= number_format($event['rating_avg'] ?? 0, 1) ?>
                                </div>
                                <span class="badge bg-primary"><?= View::e($event['category'] ?? 'General') ?></span>
                            </div>
                            <a href="<?= View::url('events/detail?id=' . $event['id']) ?>" class="btn btn-primary mt-3 w-100">
                                Lihat Detail
                            </a>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
    <?php endif; ?>
    
    <!-- All Events -->
    <div class="row">
        <div class="col-md-12">
            <h3 class="mb-3">Semua Event</h3>
            <?php if (empty($events)): ?>
                <div class="empty-state text-center py-5">
                    <i class="fas fa-calendar-alt fa-3x text-muted mb-3"></i>
                    <h5 class="text-muted">Tidak ada event ditemukan</h5>
                    <p class="text-muted">Coba ubah filter atau kata kunci pencarian</p>
                    <a href="<?= View::url('events') ?>" class="btn btn-primary mt-2">
                        <i class="fas fa-redo me-2"></i>Reset Filter
                    </a>
                </div>
            <?php else: ?>
                <div class="row">
                    <?php foreach ($events as $event): ?>
                    <div class="col-md-4 mb-4">
                        <div class="card h-100">
                            <?php if (!empty($event['main_image'])): ?>
                                <img src="<?= View::asset('uploads/events/' . $event['main_image']) ?>" class="card-img-top" alt="<?= View::e($event['name'] ?? 'Event') ?>" style="height: 200px; object-fit: cover;">
                            <?php else: ?>
                                <div class="card-img-top bg-warning d-flex align-items-center justify-content-center" style="height: 200px;">
                                    <i class="fas fa-calendar-alt text-white" style="font-size: 3rem;"></i>
                                </div>
                            <?php endif; ?>
                            <div class="card-body">
                                <h5 class="card-title"><?= View::e($event['name'] ?? 'Event Name') ?></h5>
                                <p class="card-text text-muted small">
                                    <i class="fas fa-calendar me-1"></i><?= View::date($event['event_date'] ?? null) ?>
                                </p>
                                <p class="card-text text-muted small">
                                    <i class="fas fa-map-marker-alt me-1"></i><?= View::e($event['city'] ?? 'Unknown') ?>
                                </p>
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <i class="fas fa-star text-warning"></i> <?= number_format($event['rating_avg'] ?? 0, 1) ?>
                                    </div>
                                    <span class="badge bg-secondary"><?= View::e($event['category'] ?? 'General') ?></span>
                                </div>
                                <a href="<?= View::url('events/detail?id=' . $event['id']) ?>" class="btn btn-primary mt-3 w-100">
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
