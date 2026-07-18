<?php include APP_ROOT . '/app/views/layouts/header.php'; ?>

<div class="container py-4">
    <div class="row mb-4">
        <div class="col-md-12">
            <h1 class="mb-3"><i class="fas fa-kaaba me-2"></i>Wisata Religi</h1>
            <p class="text-muted">Paket ibadah dan ziarah dengan fasilitas lengkap</p>
        </div>
    </div>
    
    <!-- Search & Filter -->
    <div class="card mb-4 glass-card">
        <div class="card-body">
            <form method="GET" action="<?= View::url('religious-tourism') ?>">
                <div class="row">
                    <div class="col-md-4 mb-3">
                        <div class="form-floating">
                            <input type="text" class="form-control" name="search" id="search" placeholder="Cari paket..." value="<?= View::e($filters['search'] ?? '') ?>">
                            <label for="search">Cari paket...</label>
                        </div>
                    </div>
                    <div class="col-md-3 mb-3">
                        <div class="form-floating">
                            <select class="form-select" name="destination_type" id="destination_type">
                                <option value="">Semua Destinasi</option>
                                <option value="mecca">Mekkah</option>
                                <option value="medina">Madinah</option>
                                <option value="jerusalem">Jerusalem</option>
                                <option value="vatican">Vatikan</option>
                                <option value="local">Lokal Indonesia</option>
                            </select>
                            <label for="destination_type">Destinasi</label>
                        </div>
                    </div>
                    <div class="col-md-3 mb-3">
                        <div class="form-floating">
                            <select class="form-select" name="duration" id="duration">
                                <option value="">Semua Durasi</option>
                                <option value="7">7 Hari</option>
                                <option value="9">9 Hari</option>
                                <option value="12">12 Hari</option>
                                <option value="15">15 Hari</option>
                            </select>
                            <label for="duration">Durasi</label>
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
    
    <!-- Pilgrimage Packages -->
    <div class="row">
        <div class="col-md-12">
            <h3 class="mb-3 section-title"><i class="fas fa-plane-departure me-2"></i>Paket Ibadah</h3>
            <?php if (empty($packages)): ?>
                <div class="empty-state text-center py-5">
                    <i class="fas fa-search fa-3x text-muted mb-3"></i>
                    <h5 class="text-muted">Tidak ada paket ditemukan</h5>
                    <p class="text-muted">Coba ubah filter atau kata kunci pencarian</p>
                    <a href="<?= View::url('religious-tourism') ?>" class="btn btn-primary mt-2">
                        <i class="fas fa-redo me-2"></i>Reset Filter
                    </a>
                </div>
            <?php else: ?>
                <div class="row">
                    <?php foreach ($packages as $package): ?>
                    <div class="col-md-4 mb-4">
                        <div class="card h-100 hover-shadow">
                            <?php if (!empty($package['image_url'])): ?>
                                <?php if (filter_var($package['image_url'], FILTER_VALIDATE_URL)): ?>
                                    <img src="<?= View::e($package['image_url']) ?>" class="card-img-top" alt="<?= View::e($package['name']) ?>" style="height: 200px; object-fit: cover;">
                                <?php else: ?>
                                    <img src="<?= View::asset('uploads/religious_tourism/' . $package['image_url']) ?>" class="card-img-top" alt="<?= View::e($package['name']) ?>" style="height: 200px; object-fit: cover;">
                                <?php endif; ?>
                            <?php else: ?>
                                <div class="card-img-top bg-info d-flex align-items-center justify-content-center" style="height: 200px;">
                                    <i class="fas fa-kaaba text-white" style="font-size: 3rem;"></i>
                                </div>
                            <?php endif; ?>
                            <div class="card-body">
                                <h5 class="card-title"><?= View::e($package['name']) ?></h5>
                                <p class="card-text text-muted small">
                                    <i class="fas fa-map-marker-alt me-1"></i><?= ucfirst($package['destination_type']) ?>
                                </p>
                                <p class="card-text text-muted small">
                                    <i class="fas fa-clock me-1"></i><?= $package['duration_days'] ?> Hari
                                </p>
                                <p class="card-text small"><?= View::e(substr($package['description'], 0, 80)) ?>...</p>
                                <div class="d-flex justify-content-between align-items-center mt-2">
                                    <div class="text-primary fw-bold">
                                        <?= $package['display_price'] ?>
                                    </div>
                                    <?php if ($package['is_featured']): ?>
                                        <span class="badge badge-recommendation">Unggulan</span>
                                    <?php endif; ?>
                                </div>
                                <a href="<?= View::url('religious-tourism/show?slug=' . $package['slug']) ?>" class="btn btn-primary btn-modern mt-3 w-100">
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
