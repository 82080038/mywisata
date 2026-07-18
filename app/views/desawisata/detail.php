<?php include APP_ROOT . '/app/views/layouts/header.php'; ?>

<div class="container py-4">
    <div class="row">
        <div class="col-md-8">
            <!-- Main Info -->
            <div class="card mb-4">
                <?php if (!empty($destination['main_image'])): ?>
                <img src="<?= View::asset('uploads/destinations/' . $destination['main_image']) ?>" class="card-img-top" style="height:350px;object-fit:cover;">
                <?php else: ?>
                <img src="http://localhost/mywisata/public/assets/img/placeholder.png" class="card-img-top" style="height:350px;object-fit:cover;">
                <?php endif; ?>
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <h1 class="card-title"><?= View::e($destination['name']) ?></h1>
                            <p class="text-muted">
                                <i class="fas fa-map-marker-alt me-1"></i><?= View::e($destination['village_name'] ?? '') ?>, <?= View::e($destination['city']) ?>
                            </p>
                        </div>
                        <?php if ($destination['eco_badge']): ?>
                        <span class="badge bg-<?= $destination['eco_badge'] === 'Gold' ? 'warning' : ($destination['eco_badge'] === 'Silver' ? 'secondary' : 'success') ?> text-dark fs-6">
                            <i class="fas fa-leaf me-1"></i>Eco <?= View::e($destination['eco_badge']) ?> (<?= $destination['eco_score'] ?>)
                        </span>
                        <?php endif; ?>
                    </div>
                    
                    <?php
                    $shareUrl = View::url('desawisata/detail/' . $destination['id']);
                    $shareTitle = $destination['name'] . ' - Desa Wisata MyWisata';
                    $shareText = 'Kunjungi ' . $destination['village_name'] . ' - ' . $destination['name'];
                    include APP_ROOT . '/app/views/partials/social_share.php';
                    ?>

                    <div class="d-flex align-items-center mb-3 mt-2">
                        <div class="me-3">
                            <i class="fas fa-star text-warning"></i> <?= number_format($destination['rating_avg'], 1) ?>
                            <span class="text-muted small">(<?= $destination['review_count'] ?> review)</span>
                        </div>
                        <?php if ($destination['umkm_count'] > 0): ?>
                        <span class="badge bg-info"><i class="fas fa-store me-1"></i><?= $destination['umkm_count'] ?> UMKM</span>
                        <?php endif; ?>
                    </div>

                    <p class="card-text"><?= nl2br(View::e($destination['description'])) ?></p>

                    <?php if ($destination['community_leader']): ?>
                    <div class="alert alert-light">
                        <i class="fas fa-user-tie me-2"></i><strong>Ketua Komunitas:</strong> <?= View::e($destination['community_leader']) ?>
                    </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- UMKM Products -->
            <?php if (!empty($umkmProducts)): ?>
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-store me-2 text-info"></i>Produk UMKM Desa</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <?php foreach ($umkmProducts as $product): ?>
                        <div class="col-md-4 mb-3">
                            <div class="card h-100">
                                <?php if (!empty($product['image'])): ?>
                                <img src="<?= View::asset('uploads/products/' . $product['image']) ?>" class="card-img-top" style="height:120px;object-fit:cover;">
                                <?php endif; ?>
                                <div class="card-body p-2">
                                    <h6 class="small mb-1"><?= View::e($product['name']) ?></h6>
                                    <p class="text-primary small mb-1">Rp <?= number_format($product['price'], 0, ',', '.') ?></p>
                                    <a href="<?= View::url('products/detail/' . $product['id']) ?>" class="btn btn-sm btn-outline-primary w-100">Lihat</a>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
            <?php endif; ?>

            <!-- Video Gallery -->
            <?php include APP_ROOT . '/app/views/partials/video_gallery.php'; ?>

            <!-- Reviews -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">Review Pengunjung</h5>
                </div>
                <div class="card-body">
                    <?php if (empty($reviews)): ?>
                    <p class="text-muted text-center">Belum ada review</p>
                    <?php else: ?>
                    <?php foreach ($reviews as $review): ?>
                    <div class="mb-3 border-bottom pb-3">
                        <div class="d-flex align-items-center mb-2">
                            <strong><?= View::e($review['user_name']) ?></strong>
                            <span class="ms-2 text-warning">
                                <?php for ($i = 0; $i < $review['rating']; $i++): ?><i class="fas fa-star"></i><?php endfor; ?>
                            </span>
                        </div>
                        <p class="mb-0"><?= View::e($review['comment']) ?></p>
                    </div>
                    <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <!-- Weather -->
            <?php
            $latitude = $destination['latitude'] ?? null;
            $longitude = $destination['longitude'] ?? null;
            $cityName = $destination['village_name'] ?? $destination['city'];
            include APP_ROOT . '/app/views/partials/weather_widget.php';
            ?>

            <!-- Eco Score Card -->
            <?php if ($destination['eco_score'] > 0): ?>
            <div class="card mb-4 border-success">
                <div class="card-header bg-success text-white">
                    <h5 class="mb-0"><i class="fas fa-leaf me-2"></i>Eco Score</h5>
                </div>
                <div class="card-body text-center">
                    <h2 class="text-success mb-0"><?= $destination['eco_score'] ?><small class="text-muted">/100</small></h2>
                    <span class="badge bg-<?= $destination['eco_badge'] === 'Gold' ? 'warning' : ($destination['eco_badge'] === 'Silver' ? 'secondary' : 'success') ?> text-dark fs-5 mt-2">
                        <?= View::e($destination['eco_badge']) ?> Badge
                    </span>
                    <p class="small text-muted mt-2 mb-0">Destinasi ini menerapkan praktik pariwisata berkelanjutan</p>
                </div>
            </div>
            <?php endif; ?>

            <!-- Ticket Info -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-ticket-alt me-2"></i>Tiket Masuk</h5>
                </div>
                <div class="card-body">
                    <?php if ($destination['entry_fee'] > 0): ?>
                    <h4 class="text-primary">Rp <?= number_format($destination['entry_fee'], 0, ',', '.') ?></h4>
                    <?php else: ?>
                    <h4 class="text-success">Gratis</h4>
                    <?php endif; ?>
                    <a href="<?= View::url('ticket/create?destination_id=' . $destination['id']) ?>" class="btn btn-primary w-100 mt-2">
                        <i class="fas fa-ticket-alt me-1"></i>Beli Tiket
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include APP_ROOT . '/app/views/layouts/footer.php'; ?>
