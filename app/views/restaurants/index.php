<?php include APP_ROOT . '/app/views/layouts/header.php'; ?>

<div class="container py-4">
    <div class="row mb-4">
        <div class="col-md-12">
            <h1 class="mb-3">Restoran & UMKM</h1>
            <p class="text-muted">Temukan kuliner lokal terbaik</p>
        </div>
    </div>
    
    <!-- Search & Filter -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" action="<?= View::url('restaurants') ?>">
                <div class="row">
                    <div class="col-md-4 mb-3">
                        <input type="text" class="form-control" name="search" placeholder="Cari restoran..." value="<?= View::e($filters['search'] ?? '') ?>">
                    </div>
                    <div class="col-md-3 mb-3">
                        <input type="text" class="form-control" name="city" placeholder="Kota..." value="<?= View::e($filters['city'] ?? '') ?>">
                    </div>
                    <div class="col-md-3 mb-3">
                        <select class="form-select" name="type">
                            <option value="">Semua Tipe</option>
                            <option value="restoran" <?= (isset($filters['type']) && $filters['type'] == 'restoran') ? 'selected' : '' ?>>Restoran</option>
                            <option value="warung" <?= (isset($filters['type']) && $filters['type'] == 'warung') ? 'selected' : '' ?>>Warung</option>
                            <option value="kafe" <?= (isset($filters['type']) && $filters['type'] == 'kafe') ? 'selected' : '' ?>>Kafe</option>
                            <option value="umkm" <?= (isset($filters['type']) && $filters['type'] == 'umkm') ? 'selected' : '' ?>>UMKM</option>
                            <option value="street_food" <?= (isset($filters['type']) && $filters['type'] == 'street_food') ? 'selected' : '' ?>>Street Food</option>
                        </select>
                    </div>
                    <div class="col-md-2 mb-3">
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="fas fa-search me-2"></i>Cari
                        </button>
                    </div>
                </div>
                <!-- Dietary Filters -->
                <div class="row mt-2">
                    <div class="col-md-12">
                        <label class="form-label small text-muted">Preferensi Diet:</label>
                        <div class="d-flex flex-wrap gap-2">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="is_halal" value="1" <?= (isset($filters['is_halal']) && $filters['is_halal'] == '1') ? 'checked' : '' ?>>
                                <label class="form-check-label">Halal</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="is_kosher" value="1" <?= (isset($filters['is_kosher']) && $filters['is_kosher'] == '1') ? 'checked' : '' ?>>
                                <label class="form-check-label">Kosher</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="is_vegan_friendly" value="1" <?= (isset($filters['is_vegan_friendly']) && $filters['is_vegan_friendly'] == '1') ? 'checked' : '' ?>>
                                <label class="form-check-label">Vegan Friendly</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="is_vegetarian_friendly" value="1" <?= (isset($filters['is_vegetarian_friendly']) && $filters['is_vegetarian_friendly'] == '1') ? 'checked' : '' ?>>
                                <label class="form-check-label">Vegetarian Friendly</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="is_gluten_free_friendly" value="1" <?= (isset($filters['is_gluten_free_friendly']) && $filters['is_gluten_free_friendly'] == '1') ? 'checked' : '' ?>>
                                <label class="form-check-label">Gluten Free</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="has_prayer_space" value="1" <?= (isset($filters['has_prayer_space']) && $filters['has_prayer_space'] == '1') ? 'checked' : '' ?>>
                                <label class="form-check-label">Prayer Space</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="is_alcohol_free" value="1" <?= (isset($filters['is_alcohol_free']) && $filters['is_alcohol_free'] == '1') ? 'checked' : '' ?>>
                                <label class="form-check-label">Alcohol Free</label>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
    
    <!-- Restaurants List -->
    <div class="row">
        <div class="col-md-12">
            <?php if (empty($restaurants)): ?>
                <p class="text-muted">Tidak ada restoran ditemukan.</p>
            <?php else: ?>
                <div class="row">
                    <?php foreach ($restaurants as $restaurant): ?>
                    <div class="col-md-4 mb-4">
                        <div class="card h-100">
                            <?php if (!empty($restaurant['main_image'])): ?>
                                <?php if (filter_var($restaurant['main_image'], FILTER_VALIDATE_URL)): ?>
                                    <img src="<?= View::e($restaurant['main_image']) ?>" class="card-img-top" alt="<?= View::e($restaurant['name']) ?>" style="height: 200px; object-fit: cover;">
                                <?php else: ?>
                                    <img src="<?= View::asset('uploads/restaurants/' . $restaurant['main_image']) ?>" class="card-img-top" alt="<?= View::e($restaurant['name']) ?>" style="height: 200px; object-fit: cover;">
                                <?php endif; ?>
                            <?php else: ?>
                                <div class="card-img-top bg-secondary d-flex align-items-center justify-content-center" style="height: 200px;">
                                    <i class="fas fa-utensils text-white" style="font-size: 2rem;"></i>
                                </div>
                            <?php endif; ?>
                            <div class="card-body">
                                <h5 class="card-title"><?= View::e($restaurant['name']) ?></h5>
                                <p class="card-text text-muted small">
                                    <i class="fas fa-map-marker-alt me-1"></i><?= View::e($restaurant['city'] ?? 'Indonesia') ?>
                                </p>
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <div>
                                        <i class="fas fa-star text-warning"></i> <?= number_format($restaurant['rating_avg'], 1) ?>
                                    </div>
                                    <span class="badge bg-success"><?= View::e($restaurant['cuisine_type'] ?? 'Kuliner') ?></span>
                                </div>
                                <div class="d-flex flex-wrap gap-1 mb-2">
                                    <?php if (!empty($restaurant['is_halal'])): ?>
                                        <span class="badge bg-success"><i class="fas fa-check-circle me-1"></i>Halal</span>
                                    <?php endif; ?>
                                    <?php if (!empty($restaurant['halal_certification'])): ?>
                                        <span class="badge bg-info"><?= View::e($restaurant['halal_certification']) ?></span>
                                    <?php endif; ?>
                                    <?php if (!empty($restaurant['is_kosher'])): ?>
                                        <span class="badge bg-primary"><i class="fas fa-check-circle me-1"></i>Kosher</span>
                                    <?php endif; ?>
                                    <?php if (!empty($restaurant['is_vegan_friendly'])): ?>
                                        <span class="badge bg-secondary"><i class="fas fa-leaf me-1"></i>Vegan</span>
                                    <?php endif; ?>
                                    <?php if (!empty($restaurant['is_vegetarian_friendly'])): ?>
                                        <span class="badge bg-secondary"><i class="fas fa-leaf me-1"></i>Vegetarian</span>
                                    <?php endif; ?>
                                    <?php if (!empty($restaurant['is_gluten_free_friendly'])): ?>
                                        <span class="badge bg-warning text-dark"><i class="fas fa-bread-slice me-1"></i>Gluten Free</span>
                                    <?php endif; ?>
                                    <?php if (!empty($restaurant['has_prayer_space'])): ?>
                                        <span class="badge bg-info"><i class="fas fa-mosque me-1"></i>Prayer Space</span>
                                    <?php endif; ?>
                                    <?php if (!empty($restaurant['is_alcohol_free'])): ?>
                                        <span class="badge bg-danger"><i class="fas fa-ban me-1"></i>Alcohol Free</span>
                                    <?php endif; ?>
                                </div>
                                <p class="card-text small text-muted">
                                    <?= View::e($restaurant['address'] ?? 'Alamat tidak tersedia') ?>
                                </p>
                                <a href="<?= View::url('restaurants/detail?id=' . $restaurant['id']) ?>" class="btn btn-primary w-100">
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
