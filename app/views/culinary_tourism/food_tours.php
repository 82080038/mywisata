<?php include APP_ROOT . '/app/views/layouts/header.php'; ?>

<div class="container py-4">
    <div class="row mb-4">
        <div class="col-md-12">
            <h1 class="mb-3"><i class="fas fa-utensils me-2"></i>Food Tours</h1>
            <p class="text-muted">Jelajahi kuliner lokal dengan food tour yang menyenangkan</p>
        </div>
    </div>
    
    <!-- Search & Filter -->
    <div class="card mb-4 glass-card">
        <div class="card-body">
            <form method="GET" action="<?= View::url('culinary-tourism/food-tours') ?>">
                <div class="row">
                    <div class="col-md-4 mb-3">
                        <div class="form-floating">
                            <input type="text" class="form-control" name="search" id="search" placeholder="Cari food tour..." value="<?= View::e($filters['search'] ?? '') ?>">
                            <label for="search">Cari food tour...</label>
                        </div>
                    </div>
                    <div class="col-md-3 mb-3">
                        <div class="form-floating">
                            <select class="form-select" name="city" id="city">
                                <option value="">Semua Kota</option>
                                <option value="Jakarta">Jakarta</option>
                                <option value="Bandung">Bandung</option>
                                <option value="Yogyakarta">Yogyakarta</option>
                                <option value="Bali">Bali</option>
                                <option value="Surabaya">Surabaya</option>
                            </select>
                            <label for="city">Kota</label>
                        </div>
                    </div>
                    <div class="col-md-3 mb-3">
                        <div class="form-floating">
                            <select class="form-select" name="dietary" id="dietary">
                                <option value="">Semua Diet</option>
                                <option value="halal">Halal</option>
                                <option value="vegetarian">Vegetarian</option>
                                <option value="vegan">Vegan</option>
                            </select>
                            <label for="dietary">Dietary</label>
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
    
    <!-- Food Tours -->
    <div class="row">
        <div class="col-md-12">
            <h3 class="mb-3 section-title"><i class="fas fa-route me-2"></i>Food Tours Tersedia</h3>
            <?php if (empty($tours)): ?>
                <div class="empty-state text-center py-5">
                    <i class="fas fa-search fa-3x text-muted mb-3"></i>
                    <h5 class="text-muted">Tidak ada food tour ditemukan</h5>
                    <p class="text-muted">Coba ubah filter atau kata kunci pencarian</p>
                    <a href="<?= View::url('culinary-tourism/food-tours') ?>" class="btn btn-primary mt-2">
                        <i class="fas fa-redo me-2"></i>Reset Filter
                    </a>
                </div>
            <?php else: ?>
                <div class="row">
                    <?php foreach ($tours as $tour): ?>
                    <div class="col-md-4 mb-4">
                        <div class="card h-100 hover-shadow">
                            <?php if (!empty($tour['image_url'])): ?>
                                <?php if (filter_var($tour['image_url'], FILTER_VALIDATE_URL)): ?>
                                    <img src="<?= View::e($tour['image_url']) ?>" class="card-img-top" alt="<?= View::e($tour['name']) ?>" style="height: 200px; object-fit: cover;">
                                <?php else: ?>
                                    <img src="<?= View::asset('uploads/culinary_tourism/' . $tour['image_url']) ?>" class="card-img-top" alt="<?= View::e($tour['name']) ?>" style="height: 200px; object-fit: cover;">
                                <?php endif; ?>
                            <?php else: ?>
                                <div class="card-img-top bg-warning d-flex align-items-center justify-content-center" style="height: 200px;">
                                    <i class="fas fa-utensils text-white" style="font-size: 3rem;"></i>
                                </div>
                            <?php endif; ?>
                            <div class="card-body">
                                <h5 class="card-title"><?= View::e($tour['name']) ?></h5>
                                <p class="card-text text-muted small">
                                    <i class="fas fa-map-marker-alt me-1"></i><?= View::e($tour['city']) ?>
                                </p>
                                <p class="card-text text-muted small">
                                    <i class="fas fa-clock me-1"></i><?= $tour['duration_hours'] ?> Jam
                                </p>
                                <p class="card-text small"><?= View::e(substr($tour['description'], 0, 80)) ?>...</p>
                                <div class="d-flex justify-content-between align-items-center mt-2">
                                    <div class="text-primary fw-bold">
                                        <?= $tour['display_price'] ?>
                                    </div>
                                    <?php if ($tour['is_featured']): ?>
                                        <span class="badge badge-recommendation">Unggulan</span>
                                    <?php endif; ?>
                                </div>
                                <a href="<?= View::url('culinary-tourism/food-tour?slug=' . $tour['slug']) ?>" class="btn btn-primary btn-modern mt-3 w-100">
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
