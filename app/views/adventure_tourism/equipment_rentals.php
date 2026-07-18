<?php include APP_ROOT . '/app/views/layouts/header.php'; ?>

<div class="container py-4">
    <div class="row mb-4">
        <div class="col-md-12">
            <h1 class="mb-3"><i class="fas fa-tools me-2"></i>Equipment Rental</h1>
            <p class="text-muted">Sewa peralatan petualangan dengan harga terjangkau</p>
        </div>
    </div>
    
    <!-- Search & Filter -->
    <div class="card mb-4 glass-card">
        <div class="card-body">
            <form method="GET" action="<?= View::url('adventure-tourism/equipment-rentals') ?>">
                <div class="row">
                    <div class="col-md-4 mb-3">
                        <div class="form-floating">
                            <input type="text" class="form-control" name="search" id="search" placeholder="Cari equipment..." value="<?= View::e($filters['search'] ?? '') ?>">
                            <label for="search">Cari equipment...</label>
                        </div>
                    </div>
                    <div class="col-md-3 mb-3">
                        <div class="form-floating">
                            <select class="form-select" name="equipment_type" id="equipment_type">
                                <option value="">Semua Tipe</option>
                                <option value="hiking">Hiking Gear</option>
                                <option value="diving">Diving Equipment</option>
                                <option value="surfing">Surfing Gear</option>
                                <option value="climbing">Climbing Equipment</option>
                                <option value="camping">Camping Gear</option>
                            </select>
                            <label for="equipment_type">Tipe Equipment</label>
                        </div>
                    </div>
                    <div class="col-md-3 mb-3">
                        <div class="form-floating">
                            <select class="form-select" name="size" id="size">
                                <option value="">Semua Ukuran</option>
                                <option value="xs">XS</option>
                                <option value="s">S</option>
                                <option value="m">M</option>
                                <option value="l">L</option>
                                <option value="xl">XL</option>
                            </select>
                            <label for="size">Ukuran</label>
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
    
    <!-- Equipment List -->
    <div class="row">
        <div class="col-md-12">
            <h3 class="mb-3 section-title"><i class="fas fa-box-open me-2"></i>Equipment Tersedia</h3>
            <?php if (empty($equipment)): ?>
                <div class="empty-state text-center py-5">
                    <i class="fas fa-search fa-3x text-muted mb-3"></i>
                    <h5 class="text-muted">Tidak ada equipment ditemukan</h5>
                    <p class="text-muted">Coba ubah filter atau kata kunci pencarian</p>
                    <a href="<?= View::url('adventure-tourism/equipment-rentals') ?>" class="btn btn-primary mt-2">
                        <i class="fas fa-redo me-2"></i>Reset Filter
                    </a>
                </div>
            <?php else: ?>
                <div class="row">
                    <?php foreach ($equipment as $item): ?>
                    <div class="col-md-4 mb-4">
                        <div class="card h-100 hover-shadow">
                            <?php if (!empty($item['image_url'])): ?>
                                <?php if (filter_var($item['image_url'], FILTER_VALIDATE_URL)): ?>
                                    <img src="<?= View::e($item['image_url']) ?>" class="card-img-top" alt="<?= View::e($item['name']) ?>" style="height: 200px; object-fit: cover;">
                                <?php else: ?>
                                    <img src="<?= View::asset('uploads/adventure_tourism/' . $item['image_url']) ?>" class="card-img-top" alt="<?= View::e($item['name']) ?>" style="height: 200px; object-fit: cover;">
                                <?php endif; ?>
                            <?php else: ?>
                                <div class="card-img-top bg-secondary d-flex align-items-center justify-content-center" style="height: 200px;">
                                    <i class="fas fa-tools text-white" style="font-size: 3rem;"></i>
                                </div>
                            <?php endif; ?>
                            <div class="card-body">
                                <h5 class="card-title"><?= View::e($item['name']) ?></h5>
                                <p class="card-text text-muted small">
                                    <i class="fas fa-tag me-1"></i><?= ucfirst($item['equipment_type']) ?>
                                </p>
                                <p class="card-text small"><?= View::e(substr($item['description'], 0, 80)) ?>...</p>
                                <div class="d-flex justify-content-between align-items-center mt-2">
                                    <div class="text-primary fw-bold">
                                        <?= $item['display_price'] ?> / hari
                                    </div>
                                    <span class="badge bg-success"><?= $item['available_quantity'] ?> tersedia</span>
                                </div>
                                <a href="<?= View::url('adventure-tourism/book-equipment?id=' . $item['id']) ?>" class="btn btn-primary btn-modern mt-3 w-100">
                                    Sewa Sekarang
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
