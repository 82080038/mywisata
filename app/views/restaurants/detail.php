<?php include APP_ROOT . '/app/views/layouts/header.php'; ?>

<div class="container py-4">
    <nav aria-label="breadcrumb" class="mb-4">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="<?= View::url('home') ?>">Beranda</a></li>
            <li class="breadcrumb-item"><a href="<?= View::url('restaurants') ?>">Restoran</a></li>
            <li class="breadcrumb-item active" aria-current="page"><?= View::e($restaurant['name']) ?></li>
        </ol>
    </nav>

    <?php include APP_ROOT . '/app/views/partials/translate_widget.php'; ?>

    <div class="row">
        <div class="col-md-8">
            <div class="card mb-4">
                <?php if (!empty($restaurant['main_image'])): ?>
                    <img src="<?= View::asset('uploads/restaurants/' . $restaurant['main_image']) ?>" class="card-img-top" alt="<?= View::e($restaurant['name']) ?>" style="height: 400px; object-fit: cover;">
                <?php else: ?>
                    <img src="https://via.placeholder.com/800x400" class="card-img-top" alt="<?= View::e($restaurant['name']) ?>" style="height: 400px; object-fit: cover;">
                <?php endif; ?>
                <div class="card-body">
                    <h1 class="card-title"><?= View::e($restaurant['name']) ?></h1>
                    <p class="text-muted">
                        <i class="fas fa-map-marker-alt me-1"></i><?= View::e($restaurant['address']) ?>, <?= View::e($restaurant['city']) ?>
                    </p>
                    <?php
                    $shareUrl = View::url('restaurants/detail/' . $restaurant['id']);
                    $shareTitle = $restaurant['name'] . ' - MyWisata';
                    $shareText = 'Makan di ' . $restaurant['name'] . ' ' . ($restaurant['city'] ?? '');
                    include APP_ROOT . '/app/views/partials/social_share.php';
                    ?>
                    <div class="d-flex align-items-center mb-3">
                        <div class="me-3">
                            <i class="fas fa-star text-warning"></i> <?= number_format($restaurant['rating_avg'], 1) ?>
                            <span class="text-muted small">(<?= $restaurant['review_count'] ?> review)</span>
                        </div>
                        <span class="badge bg-success"><?= View::e($restaurant['cuisine_type'] ?? 'restaurant') ?></span>
                    </div>
                    <p class="card-text"><?= nl2br(View::e($restaurant['description'])) ?></p>
                    
                    <div class="row mt-4">
                        <div class="col-md-6">
                            <h6>Jam Buka</h6>
                            <p class="card-text"><?= View::e($restaurant['opening_time'] ?? '-') ?> - <?= View::e($restaurant['closing_time'] ?? '-') ?></p>
                        </div>
                        <div class="col-md-6">
                            <h6>Kontak</h6>
                            <p class="card-text">Telepon: <?= View::e($restaurant['phone'] ?? '-') ?></p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Facilities -->
            <?php if (!empty($facilities)): ?>
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="card-title mb-0"><i class="fas fa-list-check me-2 text-primary"></i>Fasilitas Restoran</h5>
                </div>
                <div class="card-body">
                    <?php foreach ($facilities as $category => $items): ?>
                    <div class="mb-3">
                        <h6 class="text-muted small fw-bold mb-2">
                            <i class="fas <?= Facility::categoryIcon($category) ?> me-1"></i>
                            <?= Facility::categoryLabel($category) ?>
                        </h6>
                        <div class="row">
                            <?php foreach ($items as $f): ?>
                            <div class="col-md-4 mb-2 d-flex align-items-center">
                                <div class="bg-success bg-opacity-10 rounded-circle d-flex align-items-center justify-content-center me-2" style="width:32px;height:32px;min-width:32px;">
                                    <i class="fas <?= View::e($f['icon']) ?> text-success" style="font-size:14px;"></i>
                                </div>
                                <div>
                                    <span><?= View::e($f['name']) ?></span>
                                    <?php if (!empty($f['notes'])): ?>
                                    <small class="text-muted d-block" style="font-size:11px;"><?= View::e($f['notes']) ?></small>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>

            <!-- Menu -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">Menu</h5>
                </div>
                <div class="card-body">
                    <?php if (empty($menu_items)): ?>
                        <p class="text-muted">Belum ada menu tersedia.</p>
                    <?php else: ?>
                        <div class="row">
                            <?php foreach ($menu_items as $item): ?>
                            <div class="col-md-6 mb-3">
                                <div class="card h-100">
                                    <div class="card-body">
                                        <h6 class="card-title"><?= View::e($item['name']) ?></h6>
                                        <p class="card-text small text-muted">
                                            <?= View::e($item['description']) ?>
                                        </p>
                                        <?php if (!empty($menu_variants[$item['id']])): ?>
                                        <!-- Menu Variants -->
                                        <div class="mb-2">
                                            <label class="form-label small fw-bold mb-1"><i class="fas fa-utensils me-1"></i>Pilih Paket:</label>
                                            <select class="form-select form-select-sm menu-variant-select" data-menu-id="<?= $item['id'] ?>" data-base-price="<?= $item['price'] ?>">
                                                <?php foreach ($menu_variants[$item['id']] as $variant): ?>
                                                <?php $tierBadge = $variantModel->getTierBadge($variant); ?>
                                                <option value="<?= $variant['id'] ?>" data-price="<?= $variant['price'] ?>" data-stock="<?= $variant['stock'] ?>">
                                                    <?= View::e($variant['name']) ?> - <?= View::currency($variant['price']) ?>
                                                    <?= $variant['stock'] <= 0 ? '(Habis)' : '' ?>
                                                </option>
                                                <?php endforeach; ?>
                                            </select>
                                            <div class="mt-1" id="variantAttrs_<?= $item['id'] ?>">
                                                <?php foreach ($menu_variants[$item['id']] as $v): ?>
                                                <div id="attrs_<?= $v['id'] ?>" class="small" style="display:none;">
                                                    <?= $variantModel->formatAttributes($v) ?>
                                                    <?= $variantModel->getTierBadge($v) ?>
                                                </div>
                                                <?php endforeach; ?>
                                            </div>
                                        </div>
                                        <?php endif; ?>
                                        <p class="card-text fw-bold text-success menu-price" data-menu-id="<?= $item['id'] ?>">
                                            <?= View::currency($item['price']) ?>
                                        </p>
                                        <?php if ($item['is_available']): ?>
                                        <button class="btn btn-sm btn-primary" <?= !empty($menu_variants[$item['id']]) ? '' : '' ?>>
                                            <i class="fas fa-shopping-cart me-1"></i>Pesan
                                        </button>
                                        <?php else: ?>
                                        <button class="btn btn-sm btn-secondary" disabled>
                                            <i class="fas fa-ban me-1"></i>Tidak Tersedia
                                        </button>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
            
            <!-- Video Gallery -->
            <?php include APP_ROOT . '/app/views/partials/video_gallery.php'; ?>

            <!-- Reviews -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">Review</h5>
                </div>
                <div class="card-body">
                    <?php if (empty($reviews)): ?>
                        <p class="text-muted">Belum ada review.</p>
                    <?php else: ?>
                        <?php foreach ($reviews as $review): ?>
                        <div class="card mb-3">
                            <div class="card-body">
                                <div class="d-flex justify-content-between">
                                    <div>
                                        <h6 class="mb-1"><?= View::e($review['user_name']) ?></h6>
                                        <div class="text-warning">
                                            <?php for ($i = 1; $i <= 5; $i++): ?>
                                                <i class="fas fa-star <?= $i <= $review['rating'] ? '' : 'far' ?>"></i>
                                            <?php endfor; ?>
                                        </div>
                                    </div>
                                    <small class="text-muted"><?= View::date($review['created_at']) ?></small>
                                </div>
                                <p class="mb-0 mt-2"><?= View::e($review['comment']) ?></p>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        
        <div class="col-md-4">
            <!-- Weather Widget -->
            <?php
            $latitude = $restaurant['latitude'] ?? null;
            $longitude = $restaurant['longitude'] ?? null;
            $cityName = $restaurant['city'] ?? $restaurant['name'];
            include APP_ROOT . '/app/views/partials/weather_widget.php';
            ?>
            <!-- Table Availability -->
            <?php if (!empty($restaurant['max_tables'])): ?>
            <?php
            $availTables = $restaurant['available_tables'] ?? 0;
            $maxTables = $restaurant['max_tables'] ?? 0;
            $isFull = $availTables <= 0;
            $isLow = $availTables > 0 && $availTables <= $maxTables * 0.3;
            ?>
            <div class="card mb-3 <?= $isFull ? 'border-danger' : ($isLow ? 'border-warning' : '') ?>">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <i class="fas fa-chair me-2 text-primary"></i>
                            <span class="fw-bold">Ketersediaan Meja</span>
                        </div>
                        <?php if ($isFull): ?>
                        <span class="badge bg-danger"><i class="fas fa-times-circle me-1"></i>Penuh</span>
                        <?php elseif ($isLow): ?>
                        <span class="badge bg-warning text-dark"><i class="fas fa-exclamation-triangle me-1"></i>Tersisa <?= $availTables ?></span>
                        <?php else: ?>
                        <span class="badge bg-success"><i class="fas fa-check-circle me-1"></i><?= $availTables ?> meja</span>
                        <?php endif; ?>
                    </div>
                    <?php if (!empty($restaurant['opening_time'])): ?>
                    <hr class="my-2">
                    <div class="small text-muted">
                        <i class="fas fa-clock me-1"></i>Buka: <?= date('H:i', strtotime($restaurant['opening_time'])) ?>
                        <?php if (!empty($restaurant['closing_time'])): ?>
                        - <?= date('H:i', strtotime($restaurant['closing_time'])) ?> WIB
                        <?php endif; ?>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
            <?php endif; ?>

            <!-- Food Allergy Warning -->
            <?php if (Session::get('user_id')): ?>
            <?php
            $userModel = new User();
            $foodPrefs = $userModel->getFoodPreferences(Session::get('user_id'));
            ?>
            <?php if (!empty($foodPrefs['allergies']) || !empty($foodPrefs['preferences'])): ?>
            <div class="card border-danger mb-3">
                <div class="card-header bg-danger bg-opacity-10">
                    <h6 class="mb-0"><i class="fas fa-exclamation-triangle text-danger me-2"></i>Alergi & Preferensi Makanan Anda</h6>
                </div>
                <div class="card-body">
                    <?php if (!empty($foodPrefs['allergies'])): ?>
                    <p class="small fw-bold text-danger mb-1">Alergi:</p>
                    <?php foreach ($foodPrefs['allergies'] as $a): ?>
                    <span class="badge bg-danger me-1 mb-1"><?= User::allergyLabel($a) ?></span>
                    <?php endforeach; ?>
                    <?php endif; ?>
                    <?php if (!empty($foodPrefs['preferences'])): ?>
                    <p class="small fw-bold text-success mb-1 mt-2">Preferensi:</p>
                    <?php foreach ($foodPrefs['preferences'] as $p): ?>
                    <span class="badge bg-success me-1 mb-1"><?= User::preferenceLabel($p) ?></span>
                    <?php endforeach; ?>
                    <?php endif; ?>
                    <?php if (!empty($foodPrefs['notes'])): ?>
                    <p class="small text-muted mt-2 mb-0"><i class="fas fa-sticky-note me-1"></i><?= View::e($foodPrefs['notes']) ?></p>
                    <?php endif; ?>
                    <hr>
                    <a href="<?= View::url('dashboard/foodPreferences') ?>" class="small text-decoration-none">
                        <i class="fas fa-edit me-1"></i>Ubah Preferensi
                    </a>
                </div>
            </div>
            <?php endif; ?>
            <?php endif; ?>

            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Pesan Sekarang</h5>
                </div>
                <div class="card-body">
                    <form>
                        <div class="mb-3">
                            <label class="form-label">Tanggal</label>
                            <input type="date" class="form-control" required min="<?= date('Y-m-d') ?>">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Waktu</label>
                            <input type="time" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Jumlah Orang</label>
                            <input type="number" class="form-control" value="2" min="1" required>
                        </div>
                        <?php if (Session::get('user_id') && (!empty($foodPrefs['allergies']) || !empty($foodPrefs['preferences']))): ?>
                        <div class="form-check mb-3">
                            <input class="form-check-input" type="checkbox" id="informAllergy" checked>
                            <label class="form-check-label small" for="informAllergy">
                                Sertakan informasi alergi & preferensi makanan saya ke restoran
                            </label>
                        </div>
                        <?php endif; ?>
                        <button type="submit" class="btn btn-primary w-100">Pesan Meja</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.querySelectorAll('.menu-variant-select').forEach(function(sel) {
    sel.addEventListener('change', function() {
        var menuId = this.dataset.menuId;
        var selectedOption = this.options[this.selectedIndex];
        var price = selectedOption.dataset.price;
        var variantId = selectedOption.value;

        // Update price display
        var priceEl = document.querySelector('.menu-price[data-menu-id="' + menuId + '"]');
        if (priceEl) {
            priceEl.textContent = 'Rp ' + parseInt(price).toLocaleString('id-ID');
        }

        // Show variant attributes
        var attrsContainer = document.getElementById('variantAttrs_' + menuId);
        if (attrsContainer) {
            attrsContainer.querySelectorAll('[id^="attrs_"]').forEach(function(el) { el.style.display = 'none'; });
            var attrEl = document.getElementById('attrs_' + variantId);
            if (attrEl) attrEl.style.display = 'block';
        }
    });

    // Trigger initial display
    if (sel.options.length > 0) {
        sel.dispatchEvent(new Event('change'));
    }
});
</script>

<?php include APP_ROOT . '/app/views/layouts/footer.php'; ?>
