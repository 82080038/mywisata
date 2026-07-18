<?php include APP_ROOT . '/app/views/layouts/header.php'; ?>

<div class="container py-4">
    <div class="row mb-4">
        <div class="col-md-12">
            <h1 class="mb-2"><i class="fas fa-bed me-2"></i>Penginapan</h1>
            <p class="text-muted">Hotel, Resort, Villa, Homestay, Hostel, Glamping, Cottage, Bungalow, Cabin, Apartemen, dan lainnya</p>
        </div>
    </div>

    <!-- Type Tabs -->
    <div class="mb-3">
        <div class="d-flex flex-wrap gap-2">
            <a href="<?= View::url('hotels') ?>" class="btn btn-sm <?= empty($filters['type']) ? 'btn-primary' : 'btn-outline-primary' ?>">
                <i class="fas fa-th-large me-1"></i>Semua
            </a>
            <?php
            $allTypes = ['hotel','resort','villa','homestay','guesthouse','hostel','glamping','bungalow','cottage','cabin','apartment','inn','lodging','camping'];
            foreach ($allTypes as $t):
                $count = 0;
                foreach ($types as $tc) { if ($tc['type'] === $t) { $count = $tc['count']; break; } }
                if ($count === 0) continue;
            ?>
            <a href="<?= View::url('hotels') ?>?type=<?= $t ?>" class="btn btn-sm <?= $filters['type'] === $t ? 'btn-primary' : 'btn-outline-primary' ?>">
                <i class="fas <?= Hotel::typeIcon($t) ?> me-1"></i><?= Hotel::typeLabel($t) ?> (<?= $count ?>)
            </a>
            <?php endforeach; ?>
        </div>
    </div>

    <div class="row">
        <!-- Filter Sidebar -->
        <div class="col-md-3 mb-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-filter me-2"></i>Filter</h5>
                </div>
                <div class="card-body">
                    <form method="GET" action="<?= View::url('hotels') ?>">
                        <?php if (!empty($filters['type'])): ?>
                        <input type="hidden" name="type" value="<?= View::e($filters['type']) ?>">
                        <?php endif; ?>

                        <div class="mb-3">
                            <label class="form-label small fw-bold">Cari</label>
                            <input type="text" name="search" class="form-control form-control-sm" value="<?= View::e($filters['search'] ?? '') ?>" placeholder="Nama/kota...">
                        </div>

                        <div class="mb-3">
                            <label class="form-label small fw-bold">Kota</label>
                            <input type="text" name="city" class="form-control form-control-sm" value="<?= View::e($filters['city'] ?? '') ?>" placeholder="cth: Bali">
                        </div>

                        <div class="mb-3">
                            <label class="form-label small fw-bold">Harga Min (Rp)</label>
                            <input type="number" name="min_price" class="form-control form-control-sm" value="<?= View::e($filters['min_price'] ?? '') ?>" placeholder="0">
                        </div>

                        <div class="mb-3">
                            <label class="form-label small fw-bold">Harga Max (Rp)</label>
                            <input type="number" name="max_price" class="form-control form-control-sm" value="<?= View::e($filters['max_price'] ?? '') ?>" placeholder="9999999">
                        </div>

                        <div class="mb-3">
                            <label class="form-label small fw-bold">Bintang</label>
                            <select name="star_rating" class="form-select form-select-sm">
                                <option value="">Semua</option>
                                <option value="1" <?= ($filters['star_rating'] ?? '') == '1' ? 'selected' : '' ?>>1+ Bintang</option>
                                <option value="2" <?= ($filters['star_rating'] ?? '') == '2' ? 'selected' : '' ?>>2+ Bintang</option>
                                <option value="3" <?= ($filters['star_rating'] ?? '') == '3' ? 'selected' : '' ?>>3+ Bintang</option>
                                <option value="4" <?= ($filters['star_rating'] ?? '') == '4' ? 'selected' : '' ?>>4+ Bintang</option>
                                <option value="5" <?= ($filters['star_rating'] ?? '') == '5' ? 'selected' : '' ?>>5 Bintang</option>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label small fw-bold">Fasilitas</label>
                            <select name="facility" class="form-select form-select-sm">
                                <option value="">Semua</option>
                                <?php foreach ($allFacilities as $f): ?>
                                <option value="<?= $f ?>" <?= ($filters['facility'] ?? '') === $f ? 'selected' : '' ?>><?= Hotel::facilityLabel($f) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label small fw-bold">Urutkan</label>
                            <select name="sort" class="form-select form-select-sm">
                                <option value="newest" <?= ($filters['sort'] ?? '') === 'newest' ? 'selected' : '' ?>>Terbaru</option>
                                <option value="price_low" <?= ($filters['sort'] ?? '') === 'price_low' ? 'selected' : '' ?>>Harga Terendah</option>
                                <option value="price_high" <?= ($filters['sort'] ?? '') === 'price_high' ? 'selected' : '' ?>>Harga Tertinggi</option>
                                <option value="rating" <?= ($filters['sort'] ?? '') === 'rating' ? 'selected' : '' ?>>Rating Tertinggi</option>
                            </select>
                        </div>

                        <button type="submit" class="btn btn-primary btn-sm w-100"><i class="fas fa-search me-1"></i>Filter</button>
                        <a href="<?= View::url('hotels') ?>" class="btn btn-outline-secondary btn-sm w-100 mt-2">Reset</a>
                    </form>
                </div>
            </div>
        </div>

        <!-- Accommodation List -->
        <div class="col-md-9">
            <p class="text-muted mb-3">Menampilkan <strong><?= count($hotels) ?></strong> penginapan<?= !empty($filters['type']) ? ' tipe ' . Hotel::typeLabel($filters['type']) : '' ?></p>

            <?php if (empty($hotels)): ?>
            <div class="text-center py-5">
                <i class="fas fa-bed fa-4x text-muted mb-3"></i>
                <h4 class="text-muted">Tidak ada penginapan ditemukan</h4>
                <p class="text-muted">Coba ubah filter pencarian Anda</p>
            </div>
            <?php else: ?>
            <div class="row">
                <?php foreach ($hotels as $hotel): ?>
                <div class="col-md-6 mb-4">
                    <div class="card h-100 shadow-sm accommodation-card">
                        <?php if (!empty($hotel['main_image'])): ?>
                        <img src="<?= View::asset('uploads/hotels/' . $hotel['main_image']) ?>" class="card-img-top" alt="<?= View::e($hotel['name']) ?>" style="height: 200px; object-fit: cover;">
                        <?php else: ?>
                        <div class="card-img-top bg-light d-flex align-items-center justify-content-center" style="height: 200px;">
                            <i class="fas <?= Hotel::typeIcon($hotel['type']) ?> fa-4x text-muted"></i>
                        </div>
                        <?php endif; ?>
                        <div class="card-body d-flex flex-column">
                            <div class="mb-1">
                                <span class="badge bg-primary"><i class="fas <?= Hotel::typeIcon($hotel['type']) ?> me-1"></i><?= Hotel::typeLabel($hotel['type']) ?></span>
                                <?php if (!empty($hotel['star_rating'])): ?>
                                <span class="badge bg-warning text-dark">
                                    <?php for ($i = 0; $i < $hotel['star_rating']; $i++): ?><i class="fas fa-star"></i><?php endfor; ?>
                                </span>
                                <?php endif; ?>
                            </div>
                            <h5 class="card-title"><?= View::e($hotel['name']) ?></h5>
                            <p class="card-text text-muted small">
                                <i class="fas fa-map-marker-alt me-1"></i><?= View::e($hotel['city']) ?>, <?= View::e($hotel['province'] ?? '') ?>
                            </p>
                            <p class="card-text small text-muted flex-grow-1">
                                <?= View::e(substr($hotel['description'] ?? '', 0, 100)) ?>...
                            </p>

                            <?php $facilities = Hotel::parseFacilities($hotel['facilities'] ?? null); ?>
                            <?php if (!empty($facilities)): ?>
                            <div class="mb-2">
                                <?php foreach (array_slice($facilities, 0, 5) as $f): ?>
                                <span class="badge bg-light text-dark border me-1 mb-1"><i class="fas fa-check text-success me-1"></i><?= Hotel::facilityLabel($f) ?></span>
                                <?php endforeach; ?>
                                <?php if (count($facilities) > 5): ?>
                                <span class="badge bg-light text-muted">+<?= count($facilities) - 5 ?> lainnya</span>
                                <?php endif; ?>
                            </div>
                            <?php endif; ?>

                            <div class="d-flex justify-content-between align-items-center mt-2">
                                <div>
                                    <i class="fas fa-star text-warning"></i> <?= number_format($hotel['rating_avg'], 1) ?>
                                    <small class="text-muted">(<?= $hotel['review_count'] ?>)</small>
                                </div>
                                <div class="text-end">
                                    <?php if (!empty($hotel['price_range_min'])): ?>
                                    <small class="text-muted">mulai dari</small><br>
                                    <span class="fw-bold text-primary"><?= View::currency($hotel['price_range_min']) ?></span>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <a href="<?= View::url('hotels/detail/' . $hotel['id']) ?>" class="btn btn-primary btn-sm w-100 mt-2">
                                <i class="fas fa-info-circle me-1"></i>Lihat Detail
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
