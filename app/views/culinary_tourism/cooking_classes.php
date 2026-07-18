<?php include APP_ROOT . '/app/views/layouts/header.php'; ?>

<div class="container py-4">
    <div class="row mb-4">
        <div class="col-md-12">
            <h1 class="mb-3"><i class="fas fa-chef-hat me-2"></i>Cooking Classes</h1>
            <p class="text-muted">Belajar memasak masakan lokal dengan chef profesional</p>
        </div>
    </div>
    
    <!-- Search & Filter -->
    <div class="card mb-4 glass-card">
        <div class="card-body">
            <form method="GET" action="<?= View::url('culinary-tourism/cooking-classes') ?>">
                <div class="row">
                    <div class="col-md-4 mb-3">
                        <div class="form-floating">
                            <input type="text" class="form-control" name="search" id="search" placeholder="Cari cooking class..." value="<?= View::e($filters['search'] ?? '') ?>">
                            <label for="search">Cari cooking class...</label>
                        </div>
                    </div>
                    <div class="col-md-3 mb-3">
                        <div class="form-floating">
                            <select class="form-select" name="cuisine" id="cuisine">
                                <option value="">Semua Masakan</option>
                                <option value="indonesian">Masakan Indonesia</option>
                                <option value="chinese">Masakan China</option>
                                <option value="japanese">Masakan Jepang</option>
                                <option value="italian">Masakan Italia</option>
                                <option value="thai">Masakan Thailand</option>
                            </select>
                            <label for="cuisine">Jenis Masakan</label>
                        </div>
                    </div>
                    <div class="col-md-3 mb-3">
                        <div class="form-floating">
                            <select class="form-select" name="skill_level" id="skill_level">
                                <option value="">Semua Level</option>
                                <option value="beginner">Pemula</option>
                                <option value="intermediate">Menengah</option>
                                <option value="advanced">Lanjutan</option>
                            </select>
                            <label for="skill_level">Level Skill</label>
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
    
    <!-- Cooking Classes -->
    <div class="row">
        <div class="col-md-12">
            <h3 class="mb-3 section-title"><i class="fas fa-graduation-cap me-2"></i>Kelas Memasak Tersedia</h3>
            <?php if (empty($classes)): ?>
                <div class="empty-state text-center py-5">
                    <i class="fas fa-search fa-3x text-muted mb-3"></i>
                    <h5 class="text-muted">Tidak ada cooking class ditemukan</h5>
                    <p class="text-muted">Coba ubah filter atau kata kunci pencarian</p>
                    <a href="<?= View::url('culinary-tourism/cooking-classes') ?>" class="btn btn-primary mt-2">
                        <i class="fas fa-redo me-2"></i>Reset Filter
                    </a>
                </div>
            <?php else: ?>
                <div class="row">
                    <?php foreach ($classes as $class): ?>
                    <div class="col-md-4 mb-4">
                        <div class="card h-100 hover-shadow">
                            <?php if (!empty($class['image_url'])): ?>
                                <?php if (filter_var($class['image_url'], FILTER_VALIDATE_URL)): ?>
                                    <img src="<?= View::e($class['image_url']) ?>" class="card-img-top" alt="<?= View::e($class['name']) ?>" style="height: 200px; object-fit: cover;">
                                <?php else: ?>
                                    <img src="<?= View::asset('uploads/culinary_tourism/' . $class['image_url']) ?>" class="card-img-top" alt="<?= View::e($class['name']) ?>" style="height: 200px; object-fit: cover;">
                                <?php endif; ?>
                            <?php else: ?>
                                <div class="card-img-top bg-info d-flex align-items-center justify-content-center" style="height: 200px;">
                                    <i class="fas fa-chef-hat text-white" style="font-size: 3rem;"></i>
                                </div>
                            <?php endif; ?>
                            <div class="card-body">
                                <h5 class="card-title"><?= View::e($class['name']) ?></h5>
                                <p class="card-text text-muted small">
                                    <i class="fas fa-utensils me-1"></i><?= View::e($class['cuisine_type']) ?>
                                </p>
                                <p class="card-text text-muted small">
                                    <i class="fas fa-clock me-1"></i><?= $class['duration_hours'] ?> Jam
                                </p>
                                <p class="card-text text-muted small">
                                    <i class="fas fa-users me-1"></i>Max <?= $class['max_participants'] ?> peserta
                                </p>
                                <p class="card-text small"><?= View::e(substr($class['description'], 0, 80)) ?>...</p>
                                <div class="d-flex justify-content-between align-items-center mt-2">
                                    <div class="text-primary fw-bold">
                                        <?= $class['display_price'] ?>
                                    </div>
                                    <span class="badge bg-secondary"><?= View::e($class['skill_level']) ?></span>
                                </div>
                                <a href="<?= View::url('culinary-tourism/cooking-class?slug=' . $class['slug']) ?>" class="btn btn-primary btn-modern mt-3 w-100">
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
