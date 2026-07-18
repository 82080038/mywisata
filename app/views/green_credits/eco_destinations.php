<?php include APP_ROOT . '/app/views/layouts/header.php'; ?>

<div class="container py-4">
    <div class="row mb-4">
        <div class="col-md-12">
            <h1 class="mb-3"><i class="fas fa-tree me-2"></i>Eco-Certified Destinations</h1>
            <p class="text-muted">Destinasi wisata dengan sertifikasi eco-friendly</p>
        </div>
    </div>
    
    <!-- Eco Destinations -->
    <div class="row">
        <div class="col-md-12">
            <h3 class="mb-3 section-title"><i class="fas fa-globe-asia me-2"></i>Destinasi Eco-Certified</h3>
            <?php if (empty($destinations)): ?>
                <div class="empty-state text-center py-5">
                    <i class="fas fa-search fa-3x text-muted mb-3"></i>
                    <h5 class="text-muted">Tidak ada destinasi eco-certified ditemukan</h5>
                </div>
            <?php else: ?>
                <div class="row">
                    <?php foreach ($destinations as $destination): ?>
                    <div class="col-md-4 mb-4">
                        <div class="card h-100 hover-shadow">
                            <?php if (!empty($destination['image_url'])): ?>
                                <?php if (filter_var($destination['image_url'], FILTER_VALIDATE_URL)): ?>
                                    <img src="<?= View::e($destination['image_url']) ?>" class="card-img-top" alt="<?= View::e($destination['name']) ?>" style="height: 200px; object-fit: cover;">
                                <?php else: ?>
                                    <img src="<?= View::asset('uploads/green_credits/' . $destination['image_url']) ?>" class="card-img-top" alt="<?= View::e($destination['name']) ?>" style="height: 200px; object-fit: cover;">
                                <?php endif; ?>
                            <?php else: ?>
                                <div class="card-img-top bg-success d-flex align-items-center justify-content-center" style="height: 200px;">
                                    <i class="fas fa-leaf text-white" style="font-size: 3rem;"></i>
                                </div>
                            <?php endif; ?>
                            <div class="card-body">
                                <h5 class="card-title"><?= View::e($destination['name']) ?></h5>
                                <p class="card-text text-muted small">
                                    <i class="fas fa-map-marker-alt me-1"></i><?= View::e($destination['location']) ?>
                                </p>
                                <div class="mb-2">
                                    <span class="badge bg-success">Eco Score: <?= $destination['eco_score'] ?>/100</span>
                                </div>
                                <p class="card-text small"><?= View::e(substr($destination['description'], 0, 80)) ?>...</p>
                                <div class="mb-2">
                                    <small class="text-muted">Green Credits: +<?= $destination['green_credits_awarded'] ?></small>
                                </div>
                                <a href="<?= View::url('destinations/detail?id=' . $destination['destination_id']) ?>" class="btn btn-primary btn-modern mt-3 w-100">
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
    
    <!-- Low Carbon Routes -->
    <div class="row mt-5">
        <div class="col-md-12">
            <h3 class="mb-3 section-title"><i class="fas fa-route me-2"></i>Rute Low-Carbon</h3>
            <div class="card glass-card">
                <div class="card-body">
                    <?php if (empty($routes)): ?>
                        <div class="text-center py-5">
                            <i class="fas fa-route fa-3x text-muted mb-3"></i>
                            <p class="text-muted">Tidak ada rute low-carbon ditemukan</p>
                        </div>
                    <?php else: ?>
                        <div class="list-group">
                            <?php foreach ($routes as $route): ?>
                            <a href="#" class="list-group-item list-group-item-action">
                                <div class="d-flex w-100 justify-content-between">
                                    <h5 class="mb-1"><?= View::e($route['route_name']) ?></h5>
                                    <small class="text-success">-<?= $route['carbon_savings_kg'] ?> kg CO2</small>
                                </div>
                                <p class="mb-1 small text-muted"><?= View::e($route['description']) ?></p>
                                <small class="text-muted">Distance: <?= $route['distance_km'] ?> km | Green Credits: +<?= $route['green_credits_awarded'] ?></small>
                            </a>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include APP_ROOT . '/app/views/layouts/footer.php'; ?>
