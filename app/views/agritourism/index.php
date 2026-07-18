<?php include APP_ROOT . '/app/views/layouts/header.php'; ?>

<div class="container py-4">
    <div class="row mb-4">
        <div class="col-md-12">
            <h1 class="mb-3"><i class="fas fa-tractor me-2"></i>Agritourism</h1>
            <p class="text-muted">Jelajahi wisata pertanian dan peternakan yang edukatif</p>
        </div>
    </div>
    
    <!-- Search & Filter -->
    <div class="card mb-4 glass-card">
        <div class="card-body">
            <form method="GET" action="<?= View::url('agritourism') ?>">
                <div class="row">
                    <div class="col-md-4 mb-3">
                        <div class="form-floating">
                            <input type="text" class="form-control" name="search" id="search" placeholder="Cari farm..." value="<?= View::e($filters['search'] ?? '') ?>">
                            <label for="search">Cari farm...</label>
                        </div>
                    </div>
                    <div class="col-md-3 mb-3">
                        <div class="form-floating">
                            <select class="form-select" name="farm_type" id="farm_type">
                                <option value="">Semua Tipe</option>
                                <option value="organic">Organic Farm</option>
                                <option value="dairy">Dairy Farm</option>
                                <option value="fruit">Fruit Orchard</option>
                                <option value="vegetable">Vegetable Farm</option>
                                <option value="livestock">Livestock Farm</option>
                            </select>
                            <label for="farm_type">Tipe Farm</label>
                        </div>
                    </div>
                    <div class="col-md-3 mb-3">
                        <div class="form-floating">
                            <select class="form-select" name="city" id="city">
                                <option value="">Semua Kota</option>
                                <option value="Bandung">Bandung</option>
                                <option value="Bogor">Bogor</option>
                                <option value="Malang">Malang</option>
                                <option value="Bali">Bali</option>
                                <option value="Yogyakarta">Yogyakarta</option>
                            </select>
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
    
    <!-- Farms List -->
    <div class="row">
        <div class="col-md-12">
            <h3 class="mb-3 section-title"><i class="fas fa-seedling me-2"></i>Farm Tersedia</h3>
            <?php if (empty($farms)): ?>
                <div class="empty-state text-center py-5">
                    <i class="fas fa-search fa-3x text-muted mb-3"></i>
                    <h5 class="text-muted">Tidak ada farm ditemukan</h5>
                    <p class="text-muted">Coba ubah filter atau kata kunci pencarian</p>
                    <a href="<?= View::url('agritourism') ?>" class="btn btn-primary mt-2">
                        <i class="fas fa-redo me-2"></i>Reset Filter
                    </a>
                </div>
            <?php else: ?>
                <div class="row">
                    <?php foreach ($farms as $farm): ?>
                    <div class="col-md-4 mb-4">
                        <div class="card h-100 hover-shadow">
                            <?php if (!empty($farm['image_url'])): ?>
                                <?php if (filter_var($farm['image_url'], FILTER_VALIDATE_URL)): ?>
                                    <img src="<?= View::e($farm['image_url']) ?>" class="card-img-top" alt="<?= View::e($farm['name']) ?>" style="height: 200px; object-fit: cover;">
                                <?php else: ?>
                                    <img src="<?= View::asset('uploads/agritourism/' . $farm['image_url']) ?>" class="card-img-top" alt="<?= View::e($farm['name']) ?>" style="height: 200px; object-fit: cover;">
                                <?php endif; ?>
                            <?php else: ?>
                                <div class="card-img-top bg-success d-flex align-items-center justify-content-center" style="height: 200px;">
                                    <i class="fas fa-tractor text-white" style="font-size: 3rem;"></i>
                                </div>
                            <?php endif; ?>
                            <div class="card-body">
                                <h5 class="card-title"><?= View::e($farm['name']) ?></h5>
                                <p class="card-text text-muted small">
                                    <i class="fas fa-map-marker-alt me-1"></i><?= View::e($farm['location']) ?>
                                </p>
                                <p class="card-text text-muted small">
                                    <i class="fas fa-tag me-1"></i><?= ucfirst($farm['farm_type']) ?>
                                </p>
                                <p class="card-text small"><?= View::e(substr($farm['description'], 0, 80)) ?>...</p>
                                <div class="d-flex justify-content-between align-items-center mt-2">
                                    <span class="badge bg-info"><?= $farm['activities_count'] ?> aktivitas</span>
                                    <?php if ($farm['is_organic']): ?>
                                        <span class="badge bg-success">Organic</span>
                                    <?php endif; ?>
                                </div>
                                <a href="<?= View::url('agritourism/show?slug=' . $farm['slug']) ?>" class="btn btn-primary btn-modern mt-3 w-100">
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
