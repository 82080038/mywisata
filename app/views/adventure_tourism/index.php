<?php include APP_ROOT . '/app/views/layouts/header.php'; ?>

<div class="container py-4">
    <div class="row mb-4">
        <div class="col-md-12">
            <h1 class="mb-3"><i class="fas fa-hiking me-2"></i>Adventure Tourism</h1>
            <p class="text-muted">Aktivitas petualangan yang menantang dan seru</p>
        </div>
    </div>
    
    <!-- Search & Filter -->
    <div class="card mb-4 glass-card">
        <div class="card-body">
            <form method="GET" action="<?= View::url('adventure-tourism') ?>">
                <div class="row">
                    <div class="col-md-4 mb-3">
                        <div class="form-floating">
                            <input type="text" class="form-control" name="search" id="search" placeholder="Cari aktivitas..." value="<?= View::e($filters['search'] ?? '') ?>">
                            <label for="search">Cari aktivitas...</label>
                        </div>
                    </div>
                    <div class="col-md-3 mb-3">
                        <div class="form-floating">
                            <select class="form-select" name="activity_type" id="activity_type">
                                <option value="">Semua Tipe</option>
                                <option value="hiking">Hiking</option>
                                <option value="rafting">Rafting</option>
                                <option value="climbing">Climbing</option>
                                <option value="diving">Diving</option>
                                <option value="surfing">Surfing</option>
                                <option value="paragliding">Paragliding</option>
                            </select>
                            <label for="activity_type">Tipe Aktivitas</label>
                        </div>
                    </div>
                    <div class="col-md-3 mb-3">
                        <div class="form-floating">
                            <select class="form-select" name="difficulty_level" id="difficulty_level">
                                <option value="">Semua Level</option>
                                <option value="easy">Easy</option>
                                <option value="moderate">Moderate</option>
                                <option value="hard">Hard</option>
                                <option value="extreme">Extreme</option>
                            </select>
                            <label for="difficulty_level">Level Kesulitan</label>
                        </div>
                    </div>
                    <div class="col-md-2 mb-3">
                        <button type="submit" class="btn btn-primary btn-modern w-100 h-100">
                            <i class="fas fa-search"></i>
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
    
    <!-- Adventure Activities -->
    <div class="row">
        <div class="col-md-12">
            <h3 class="mb-3 section-title"><i class="fas fa-mountain me-2"></i>Aktivitas Petualangan</h3>
            <?php if (empty($activities)): ?>
                <div class="empty-state text-center py-5">
                    <i class="fas fa-search fa-3x text-muted mb-3"></i>
                    <h5 class="text-muted">Tidak ada aktivitas ditemukan</h5>
                    <p class="text-muted">Coba ubah filter atau kata kunci pencarian</p>
                    <a href="<?= View::url('adventure-tourism') ?>" class="btn btn-primary mt-2">
                        <i class="fas fa-redo me-2"></i>Reset Filter
                    </a>
                </div>
            <?php else: ?>
                <div class="row">
                    <?php foreach ($activities as $activity): ?>
                    <div class="col-md-4 mb-4">
                        <div class="card h-100 hover-shadow">
                            <?php if (!empty($activity['image_url'])): ?>
                                <?php if (filter_var($activity['image_url'], FILTER_VALIDATE_URL)): ?>
                                    <img src="<?= View::e($activity['image_url']) ?>" class="card-img-top" alt="<?= View::e($activity['name']) ?>" style="height: 200px; object-fit: cover;">
                                <?php else: ?>
                                    <img src="<?= View::asset('uploads/adventure_tourism/' . $activity['image_url']) ?>" class="card-img-top" alt="<?= View::e($activity['name']) ?>" style="height: 200px; object-fit: cover;">
                                <?php endif; ?>
                            <?php else: ?>
                                <div class="card-img-top bg-danger d-flex align-items-center justify-content-center" style="height: 200px;">
                                    <i class="fas fa-hiking text-white" style="font-size: 3rem;"></i>
                                </div>
                            <?php endif; ?>
                            <div class="card-body">
                                <h5 class="card-title"><?= View::e($activity['name']) ?></h5>
                                <p class="card-text text-muted small">
                                    <i class="fas fa-tag me-1"></i><?= ucfirst($activity['activity_type']) ?>
                                </p>
                                <p class="card-text text-muted small">
                                    <i class="fas fa-clock me-1"></i><?= $activity['duration_hours'] ?> Jam
                                </p>
                                <p class="card-text small"><?= View::e(substr($activity['description'], 0, 80)) ?>...</p>
                                <div class="d-flex justify-content-between align-items-center mt-2">
                                    <div class="text-primary fw-bold">
                                        <?= $activity['display_price'] ?>
                                    </div>
                                    <span class="badge bg-<?= $activity['difficulty_level'] === 'easy' ? 'success' : ($activity['difficulty_level'] === 'extreme' ? 'danger' : 'warning') ?>">
                                        <?= ucfirst($activity['difficulty_level']) ?>
                                    </span>
                                </div>
                                <a href="<?= View::url('adventure-tourism/show?slug=' . $activity['slug']) ?>" class="btn btn-primary btn-modern mt-3 w-100">
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
