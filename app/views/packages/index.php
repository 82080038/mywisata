<?php include APP_ROOT . '/app/views/layouts/header.php'; ?>

<div class="container py-4">
    <div class="row align-items-center mb-4">
        <div class="col-md-8">
            <h2><i class="fas fa-box me-2"></i>Paket Wisata</h2>
            <p class="text-muted">Paket all-inclusive untuk liburan tanpa ribet</p>
        </div>
        <div class="col-md-4">
            <form method="GET" class="d-flex gap-2">
                <input type="text" class="form-control" name="search" placeholder="Cari paket..." value="<?= View::e($filters['search'] ?? '') ?>">
                <button type="submit" class="btn btn-primary"><i class="fas fa-search"></i></button>
            </form>
        </div>
    </div>

    <?php if (empty($packages)): ?>
    <div class="text-center py-5">
        <i class="fas fa-box fa-3x text-muted mb-3"></i>
        <h4 class="text-muted">Belum ada paket</h4>
    </div>
    <?php else: ?>
    <div class="row">
        <?php foreach ($packages as $pkg): ?>
        <div class="col-md-6 col-lg-4 mb-4">
            <div class="card h-100 shadow-sm <?= $pkg['is_featured'] ? 'border-primary' : '' ?>">
                <?php if ($pkg['main_image']): ?>
                <img src="<?= View::asset('uploads/packages/' . $pkg['main_image']) ?>" class="card-img-top" style="height:200px;object-fit:cover;">
                <?php else: ?>
                <img src="http://localhost/mywisata/public/assets/img/placeholder.png'title']) ?>" class="card-img-top" style="height:200px;object-fit:cover;">
                <?php endif; ?>
                
                <?php if ($pkg['is_featured']): ?>
                <div class="position-absolute top-0 start-0 m-2">
                    <span class="badge bg-primary"><i class="fas fa-star me-1"></i>Featured</span>
                </div>
                <?php endif; ?>

                <div class="card-body">
                    <h5 class="card-title"><?= View::e($pkg['title']) ?></h5>
                    <p class="card-text small text-muted">
                        <?= View::e(mb_substr($pkg['description'], 0, 80)) ?>...
                    </p>
                    <div class="d-flex gap-3 mb-2">
                        <span class="small"><i class="fas fa-clock me-1 text-primary"></i><?= $pkg['duration_days'] ?> hari</span>
                        <span class="small"><i class="fas fa-users me-1 text-primary"></i><?= $pkg['min_travelers'] ?>-<?= $pkg['max_travelers'] ?> orang</span>
                    </div>
                </div>
                <div class="card-footer bg-transparent">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <?php if ($pkg['discount_price']): ?>
                            <small class="text-muted text-decoration-line-through">Rp <?= number_format($pkg['price'], 0, ',', '.') ?></small><br>
                            <span class="text-danger fw-bold">Rp <?= number_format($pkg['discount_price'], 0, ',', '.') ?></span>
                            <?php else: ?>
                            <span class="text-primary fw-bold">Rp <?= number_format($pkg['price'], 0, ',', '.') ?></span>
                            <?php endif; ?>
                        </div>
                        <a href="<?= View::url('packages/detail/' . $pkg['id']) ?>" class="btn btn-sm btn-primary">
                            Lihat Paket
                        </a>
                    </div>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>
</div>

<?php include APP_ROOT . '/app/views/layouts/footer.php'; ?>
