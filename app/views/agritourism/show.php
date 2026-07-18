<?php include APP_ROOT . '/app/views/layouts/header.php'; ?>

<div class="container py-4">
    <div class="row">
        <div class="col-md-8">
            <!-- Farm Details -->
            <div class="card mb-4 glass-card">
                <div class="card-body">
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="<?= View::url('home') ?>">Home</a></li>
                            <li class="breadcrumb-item"><a href="<?= View::url('agritourism') ?>">Agritourism</a></li>
                            <li class="breadcrumb-item active"><?= View::e($farm['name']) ?></li>
                        </ol>
                    </nav>
                    
                    <?php if (!empty($farm['image_url'])): ?>
                        <?php if (filter_var($farm['image_url'], FILTER_VALIDATE_URL)): ?>
                            <img src="<?= View::e($farm['image_url']) ?>" class="img-fluid rounded mb-3" alt="<?= View::e($farm['name']) ?>">
                        <?php else: ?>
                            <img src="<?= View::asset('uploads/agritourism/' . $farm['image_url']) ?>" class="img-fluid rounded mb-3" alt="<?= View::e($farm['name']) ?>">
                        <?php endif; ?>
                    <?php endif; ?>
                    
                    <h1 class="mb-3"><?= View::e($farm['name']) ?></h1>
                    <div class="d-flex align-items-center mb-3">
                        <span class="badge bg-info me-2">
                            <i class="fas fa-tag me-1"></i><?= ucfirst($farm['farm_type']) ?>
                        </span>
                        <span class="badge bg-success me-2">
                            <i class="fas fa-map-marker-alt me-1"></i><?= View::e($farm['location']) ?>
                        </span>
                        <?php if ($farm['is_organic']): ?>
                            <span class="badge bg-success">
                                <i class="fas fa-leaf me-1"></i>Organic
                            </span>
                        <?php endif; ?>
                    </div>
                    
                    <h4 class="mt-4">Deskripsi</h4>
                    <p><?= nl2br(View::e($farm['description'])) ?></p>
                    
                    <h4 class="mt-4">Aktivitas Tersedia</h4>
                    <div class="row">
                        <?php foreach ($activities as $activity): ?>
                        <div class="col-md-6 mb-3">
                            <div class="card">
                                <div class="card-body">
                                    <h6 class="card-title"><?= View::e($activity['name']) ?></h6>
                                    <p class="card-text small"><?= View::e(substr($activity['description'], 0, 60)) ?>...</p>
                                    <div class="d-flex justify-content-between align-items-center">
                                        <span class="text-primary fw-bold"><?= $activity['display_price'] ?></span>
                                        <button onclick="bookActivity(<?= $activity['id'] ?>)" class="btn btn-sm btn-primary">
                                            Booking
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    
                    <h4 class="mt-4">Paket Tour</h4>
                    <div class="row">
                        <?php foreach ($packages as $package): ?>
                        <div class="col-md-6 mb-3">
                            <div class="card">
                                <div class="card-body">
                                    <h6 class="card-title"><?= View::e($package['name']) ?></h6>
                                    <p class="card-text small">
                                        <i class="fas fa-clock me-1"></i><?= $package['duration_hours'] ?> Jam
                                    </p>
                                    <div class="d-flex justify-content-between align-items-center">
                                        <span class="text-primary fw-bold"><?= $package['display_price'] ?></span>
                                        <button onclick="bookPackage(<?= $package['id'] ?>)" class="btn btn-sm btn-primary">
                                            Booking
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-4">
            <!-- Products -->
            <div class="card glass-card sticky-top" style="top: 20px;">
                <div class="card-body">
                    <h5 class="card-title mb-3">Produk Farm</h5>
                    <?php if (empty($products)): ?>
                        <p class="text-muted">Tidak ada produk tersedia</p>
                    <?php else: ?>
                        <div class="list-group">
                            <?php foreach ($products as $product): ?>
                            <a href="#" class="list-group-item list-group-item-action">
                                <div class="d-flex w-100 justify-content-between">
                                    <h6 class="mb-1"><?= View::e($product['name']) ?></h6>
                                    <small class="text-primary fw-bold"><?= $product['display_price'] ?></small>
                                </div>
                                <p class="mb-1 small text-muted"><?= View::e(substr($product['description'], 0, 50)) ?>...</p>
                                <small class="text-muted">Stok: <?= $product['available_quantity'] ?></small>
                            </a>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function bookActivity(activityId) {
    window.location.href = '<?= View::url('agritourism/book-activity') ?>?activity_id=' + activityId;
}

function bookPackage(packageId) {
    window.location.href = '<?= View::url('agritourism/book-package') ?>?package_id=' + packageId;
}
</script>

<?php include APP_ROOT . '/app/views/layouts/footer.php'; ?>
