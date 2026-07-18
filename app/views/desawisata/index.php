<?php include APP_ROOT . '/app/views/layouts/header.php'; ?>

<div class="container py-4">
    <div class="row align-items-center mb-4">
        <div class="col-md-8">
            <h2><i class="fas fa-house-chimney me-2"></i>Desa Wisata</h2>
            <p class="text-muted">Temukan pengalaman otentik di desa-desa wisata Indonesia</p>
        </div>
        <div class="col-md-4">
            <form method="GET" class="d-flex gap-2">
                <input type="text" class="form-control" name="search" placeholder="Cari desa wisata..." value="<?= View::e($filters['search'] ?? '') ?>">
                <button type="submit" class="btn btn-primary"><i class="fas fa-search"></i></button>
            </form>
        </div>
    </div>

    <!-- City Filter -->
    <?php if (!empty($cities)): ?>
    <div class="mb-3 d-flex gap-2 flex-wrap">
        <a href="<?= View::url('desawisata') ?>" class="btn btn-sm <?= empty($filters['city']) ? 'btn-primary' : 'btn-outline-primary' ?>">Semua</a>
        <?php foreach ($cities as $city): ?>
        <a href="<?= View::url('desawisata?city=' . urlencode($city['city'])) ?>" class="btn btn-sm <?= ($filters['city'] ?? '') === $city['city'] ? 'btn-primary' : 'btn-outline-primary' ?>">
            <?= View::e($city['city']) ?>
        </a>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>

    <?php if (empty($destinations)): ?>
    <div class="text-center py-5">
        <i class="fas fa-house-chimney fa-3x text-muted mb-3"></i>
        <h4 class="text-muted">Belum ada desa wisata</h4>
    </div>
    <?php else: ?>
    <div class="row">
        <?php foreach ($destinations as $dest): ?>
        <div class="col-md-6 col-lg-4 mb-4">
            <div class="card h-100 shadow-sm">
                <?php if (!empty($dest['main_image'])): ?>
                <img src="<?= View::asset('uploads/destinations/' . $dest['main_image']) ?>" class="card-img-top" alt="<?= View::e($dest['name']) ?>" style="height:200px;object-fit:cover;">
                <?php else: ?>
                <img src="http://localhost/mywisata/public/assets/img/placeholder.png" class="card-img-top" style="height:200px;object-fit:cover;">
                <?php endif; ?>
                
                <?php if ($dest['eco_badge']): ?>
                <div class="position-absolute top-0 end-0 m-2">
                    <span class="badge bg-<?= $dest['eco_badge'] === 'Gold' ? 'warning' : ($dest['eco_badge'] === 'Silver' ? 'secondary' : 'success') ?> text-dark">
                        <i class="fas fa-leaf me-1"></i>Eco <?= View::e($dest['eco_badge']) ?>
                    </span>
                </div>
                <?php endif; ?>

                <div class="card-body">
                    <h5 class="card-title"><?= View::e($dest['name']) ?></h5>
                    <p class="text-muted small mb-2">
                        <i class="fas fa-map-marker-alt me-1"></i><?= View::e($dest['village_name'] ?? $dest['city']) ?>
                    </p>
                    <p class="card-text small">
                        <?= View::e(mb_substr($dest['description'] ?? '', 0, 100)) ?><?= mb_strlen($dest['description'] ?? '') > 100 ? '...' : '' ?>
                    </p>
                    
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <div>
                            <i class="fas fa-star text-warning"></i> <?= number_format($dest['rating_avg'], 1) ?>
                            <small class="text-muted">(<?= $dest['review_count'] ?>)</small>
                        </div>
                        <?php if ($dest['umkm_count'] > 0): ?>
                        <span class="badge bg-info"><i class="fas fa-store me-1"></i><?= $dest['umkm_count'] ?> UMKM</span>
                        <?php endif; ?>
                    </div>
                    
                    <?php if ($dest['community_leader']): ?>
                    <p class="small text-muted mb-2"><i class="fas fa-user me-1"></i>Ketua: <?= View::e($dest['community_leader']) ?></p>
                    <?php endif; ?>
                </div>
                <div class="card-footer bg-transparent">
                    <a href="<?= View::url('desawisata/detail/' . $dest['id']) ?>" class="btn btn-sm btn-primary w-100">
                        <i class="fas fa-eye me-1"></i>Lihat Detail
                    </a>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>
</div>

<?php include APP_ROOT . '/app/views/layouts/footer.php'; ?>
