<?php include APP_ROOT . '/app/views/layouts/header.php'; ?>

<div class="container py-4">
    <div class="row mb-4">
        <div class="col-md-12">
            <h1 class="mb-3">Destinasi Wisata</h1>
            <p class="text-muted">Temukan destinasi wisata terbaik di Indonesia</p>
        </div>
    </div>
    
    <!-- Search & Filter -->
    <div class="card mb-4 glass-card">
        <div class="card-body">
            <form method="GET" action="<?= View::url('destinations') ?>">
                <div class="row">
                    <div class="col-md-4 mb-3">
                        <div class="form-floating">
                            <input type="text" class="form-control" name="search" id="search" placeholder="Cari destinasi..." value="<?= View::e($filters['search'] ?? '') ?>">
                            <label for="search">Cari destinasi...</label>
                        </div>
                    </div>
                    <div class="col-md-3 mb-3">
                        <div class="form-floating">
                            <select class="form-select" name="category" id="category">
                                <option value="">Semua Kategori</option>
                                <?php foreach ($categories as $cat): ?>
                                    <option value="<?= $cat['id'] ?>" <?= (isset($filters['category_id']) && $filters['category_id'] == $cat['id']) ? 'selected' : '' ?>>
                                        <?= View::e($cat['name']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <label for="category">Kategori</label>
                        </div>
                    </div>
                    <div class="col-md-3 mb-3">
                        <div class="form-floating">
                            <input type="text" class="form-control" name="city" id="city" placeholder="Kota..." value="<?= View::e($filters['city'] ?? '') ?>">
                            <label for="city">Kota</label>
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
    
    <!-- Featured Destinations -->
    <?php if (!empty($featured)): ?>
    <div class="row mb-4">
        <div class="col-md-12">
            <h3 class="mb-3 section-title"><i class="fas fa-star text-warning me-2"></i>Destinasi Unggulan</h3>
            <div class="row">
                <?php foreach ($featured as $dest): ?>
                <div class="col-md-4 mb-4">
                    <div class="card h-100 hover-shadow">
                        <?php if (!empty($dest['main_image'])): ?>
                            <?php if (filter_var($dest['main_image'], FILTER_VALIDATE_URL)): ?>
                                <img src="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 1 1'%3E%3C/svg%3E" 
                                     data-src="<?= View::e($dest['main_image']) ?>" 
                                     class="card-img-top lazyload avatar-img" 
                                     alt="<?= View::e($dest['name']) ?>">
                            <?php else: ?>
                                <img src="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 1 1'%3E%3C/svg%3E" 
                                     data-src="<?= View::asset('uploads/destinations/' . $dest['main_image']) ?>" 
                                     class="card-img-top lazyload avatar-img" 
                                     alt="<?= View::e($dest['name']) ?>">
                            <?php endif; ?>
                        <?php else: ?>
                            <div class="card-img-top bg-primary d-flex align-items-center justify-content-center" style="height: 200px;">
                                <i class="fas fa-map-marked-alt text-white" style="font-size: 3rem;"></i>
                            </div>
                        <?php endif; ?>
                        <div class="card-body">
                            <h5 class="card-title"><?= View::e($dest['name']) ?></h5>
                            <p class="card-text text-muted small">
                                <i class="fas fa-map-marker-alt me-1"></i><?= View::e($dest['city'] ?? 'Indonesia') ?>
                            </p>
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <i class="fas fa-star text-warning"></i> <?= number_format($dest['rating_avg'], 1) ?>
                                </div>
                                <span class="badge badge-recommendation">Unggulan</span>
                            </div>
                            <a href="<?= View::url('destinations/detail?id=' . $dest['id']) ?>" class="btn btn-primary btn-modern mt-3 w-100">
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
    
    <!-- Popular Destinations -->
    <?php if (!empty($popular)): ?>
    <div class="row mb-4">
        <div class="col-md-12">
            <h3 class="mb-3">Destinasi Populer</h3>
            <div class="row">
                <?php foreach ($popular as $dest): ?>
                <div class="col-md-4 mb-4">
                    <div class="card h-100">
                        <?php if (!empty($dest['main_image'])): ?>
                            <?php if (filter_var($dest['main_image'], FILTER_VALIDATE_URL)): ?>
                                <img src="<?= View::e($dest['main_image']) ?>" class="card-img-top" alt="<?= View::e($dest['name']) ?>" style="height: 200px; object-fit: cover;">
                            <?php else: ?>
                                <img src="<?= View::asset('uploads/destinations/' . $dest['main_image']) ?>" class="card-img-top" alt="<?= View::e($dest['name']) ?>" style="height: 200px; object-fit: cover;">
                            <?php endif; ?>
                        <?php else: ?>
                            <div class="card-img-top bg-primary d-flex align-items-center justify-content-center" style="height: 200px;">
                                <i class="fas fa-map-marked-alt text-white" style="font-size: 3rem;"></i>
                            </div>
                        <?php endif; ?>
                        <div class="card-body">
                            <h5 class="card-title"><?= View::e($dest['name']) ?></h5>
                            <p class="card-text text-muted small">
                                <i class="fas fa-map-marker-alt me-1"></i><?= View::e($dest['city'] ?? 'Indonesia') ?>
                            </p>
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <i class="fas fa-star text-warning"></i> <?= number_format($dest['rating_avg'], 1) ?>
                                </div>
                                <span class="badge bg-success">Populer</span>
                            </div>
                            <a href="<?= View::url('destinations/detail?id=' . $dest['id']) ?>" class="btn btn-primary mt-3 w-100">
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
    
    <!-- All Destinations -->
    <div class="row">
        <div class="col-md-12">
            <h3 class="mb-3 section-title"><i class="fas fa-list me-2"></i>Semua Destinasi</h3>
            <?php if (empty($destinations)): ?>
                <div class="empty-state text-center py-5">
                    <i class="fas fa-search fa-3x text-muted mb-3"></i>
                    <h5 class="text-muted">Tidak ada destinasi ditemukan</h5>
                    <p class="text-muted">Coba ubah filter atau kata kunci pencarian</p>
                    <a href="<?= View::url('destinations') ?>" class="btn btn-primary mt-2">
                        <i class="fas fa-redo me-2"></i>Reset Filter
                    </a>
                </div>
            <?php else: ?>
                <div class="masonry-grid">
                    <?php foreach ($destinations as $dest): ?>
                    <div class="masonry-item">
                        <div class="card h-100 hover-shadow">
                            <?php if (!empty($dest['main_image'])): ?>
                                <?php if (filter_var($dest['main_image'], FILTER_VALIDATE_URL)): ?>
                                    <img src="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 1 1'%3E%3C/svg%3E" 
                                         data-src="<?= View::e($dest['main_image']) ?>" 
                                         class="card-img-top lazyload avatar-img" 
                                         alt="<?= View::e($dest['name']) ?>">
                                <?php else: ?>
                                    <img src="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 1 1'%3E%3C/svg%3E" 
                                         data-src="<?= View::asset('uploads/destinations/' . $dest['main_image']) ?>" 
                                         class="card-img-top lazyload avatar-img" 
                                         alt="<?= View::e($dest['name']) ?>">
                                <?php endif; ?>
                            <?php else: ?>
                                <div class="card-img-top bg-primary d-flex align-items-center justify-content-center" style="height: 200px;">
                                <i class="fas fa-map-marked-alt text-white" style="font-size: 3rem;"></i>
                            </div>
                            <?php endif; ?>
                            <div class="card-body">
                                <h5 class="card-title"><?= View::e($dest['name'] ?? 'Destinasi') ?></h5>
                                <p class="card-text text-muted small">
                                    <i class="fas fa-map-marker-alt me-1"></i><?= View::e($dest['city'] ?? 'Indonesia') ?>
                                </p>
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <i class="fas fa-star text-warning"></i> <?= number_format($dest['rating_avg'], 1) ?>
                                    </div>
                                    <span class="badge bg-secondary"><?= View::e($dest['category_name'] ?? 'Umum') ?></span>
                                </div>
                                <a href="<?= View::url('destinations/detail?id=' . $dest['id']) ?>" class="btn btn-primary btn-modern mt-3 w-100">
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
