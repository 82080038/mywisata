<?php include APP_ROOT . '/app/views/layouts/header.php'; ?>

<div class="container py-4">
    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb" class="mb-4">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="<?= View::url('home') ?>">Beranda</a></li>
            <li class="breadcrumb-item"><a href="<?= View::url('destinations') ?>">Destinasi</a></li>
            <li class="breadcrumb-item active" aria-current="page"><?= View::e($destination['name']) ?></li>
        </ol>
    </nav>

    <?php include APP_ROOT . '/app/views/partials/translate_widget.php'; ?>

    <div class="row">
        <div class="col-md-8">
            <!-- Main Image -->
            <div class="card mb-4">
                <?php if (!empty($destination['main_image'])): ?>
                    <img src="<?= View::asset('uploads/destinations/' . $destination['main_image']) ?>" class="card-img-top" alt="<?= View::e($destination['name']) ?>" style="height: 400px; object-fit: cover;">
                <?php else: ?>
                    <img src="https://via.placeholder.com/800x400" class="card-img-top" alt="<?= View::e($destination['name']) ?>" style="height: 400px; object-fit: cover;">
                <?php endif; ?>
                <div class="card-body">
                    <h1 class="card-title"><?= View::e($destination['name']) ?></h1>
                    <p class="card-text text-muted">
                        <i class="fas fa-map-marker-alt me-1"></i><?= View::e($destination['address']) ?>, <?= View::e($destination['city']) ?>
                    </p>
                    <?php
                    $shareUrl = View::url('destinations/detail/' . $destination['id']);
                    $shareTitle = $destination['name'] . ' - MyWisata';
                    $shareText = 'Kunjungi ' . $destination['name'] . ' di ' . ($destination['city'] ?? 'Indonesia');
                    include APP_ROOT . '/app/views/partials/social_share.php';
                    ?>
                    <div class="d-flex align-items-center mb-3">
                        <div class="me-3">
                            <i class="fas fa-star text-warning"></i> <?= number_format($destination['rating_avg'], 1) ?>
                            <span class="text-muted small">(<?= $destination['review_count'] ?> review)</span>
                        </div>
                        <span class="badge bg-primary"><?= View::e($destination['category_name']) ?></span>
                    </div>
                    <p class="card-text"><?= nl2br(View::e($destination['description'])) ?></p>
                    
                    <div class="row mt-4">
                        <div class="col-md-6">
                            <h6>Informasi Tiket</h6>
                            <p class="card-text">Harga Tiket: <?= View::currency($destination['entry_fee']) ?></p>
                            <p class="card-text">Jam Buka: <?= View::e($destination['opening_hours'] ?? '-') ?></p>
                        </div>
                        <div class="col-md-6">
                            <h6>Kontak</h6>
                            <p class="card-text">Telepon: <?= View::e($destination['contact_phone'] ?? '-') ?></p>
                            <p class="card-text">Website: <?= View::e($destination['website'] ?? '-') ?></p>
                        </div>
                    </div>
                    
                    <div class="d-flex gap-2 mt-3">
                        <a href="#" class="btn btn-primary btn-lg">
                            <i class="fas fa-ticket-alt me-2"></i>Beli Tiket
                        </a>
                        <?php if (Session::get('user_id')): ?>
                        <button class="btn btn-outline-danger btn-lg" id="favBtn" onclick="toggleFavorite('destination', <?= $destination['id'] ?>)">
                            <i class="far fa-heart"></i>
                        </button>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            
            <!-- Gallery -->
            <?php if (!empty($images) && count($images) > 1): ?>
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">Galeri</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <?php foreach ($images as $img): ?>
                            <?php if ($img['file_path'] !== $destination['main_image']): ?>
                            <div class="col-md-4 mb-3">
                                <img src="<?= View::asset('uploads/destinations/' . $img['file_path']) ?>" class="img-fluid rounded" alt="<?= View::e($img['caption'] ?? 'Gallery') ?>">
                            </div>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
            <?php endif; ?>

            <!-- Facilities -->
            <?php if (!empty($facilities)): ?>
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="card-title mb-0"><i class="fas fa-list-check me-2 text-primary"></i>Fasilitas Destinasi</h5>
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

            <!-- Video Gallery -->
            <?php include APP_ROOT . '/app/views/partials/video_gallery.php'; ?>

            <!-- Reviews -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">Review</h5>
                </div>
                <div class="card-body">
                    <?php if (Session::get('user_id')): ?>
                    <div class="mb-4">
                        <h6>Tulis Review</h6>
                        <form id="reviewForm">
                            <input type="hidden" name="destination_id" value="<?= $destination['id'] ?>">
                            <div class="mb-3">
                                <label class="form-label">Rating</label>
                                <select class="form-select" name="rating" required>
                                    <option value="">Pilih Rating</option>
                                    <option value="5">5 - Sangat Baik</option>
                                    <option value="4">4 - Baik</option>
                                    <option value="3">3 - Biasa</option>
                                    <option value="2">2 - Kurang</option>
                                    <option value="1">1 - Sangat Buruk</option>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Komentar</label>
                                <textarea class="form-control" name="comment" rows="3" required></textarea>
                            </div>
                            <button type="submit" class="btn btn-primary">Kirim Review</button>
                        </form>
                    </div>
                    <?php endif; ?>
                    
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
            $latitude = $destination['latitude'] ?? null;
            $longitude = $destination['longitude'] ?? null;
            $cityName = $destination['city'] ?? $destination['name'];
            include APP_ROOT . '/app/views/partials/weather_widget.php';
            ?>
            
            <!-- Eco Score Card -->
            <?php if (!empty($destination['eco_score']) && $destination['eco_score'] > 0): ?>
            <div class="card mb-4 border-success">
                <div class="card-header bg-success text-white">
                    <h5 class="card-title mb-0"><i class="fas fa-leaf me-2"></i>Eco Score</h5>
                </div>
                <div class="card-body text-center">
                    <h2 class="text-success mb-0"><?= $destination['eco_score'] ?><small class="text-muted">/100</small></h2>
                    <?php if (!empty($destination['eco_badge'])): ?>
                    <span class="badge bg-<?= $destination['eco_badge'] === 'Gold' ? 'warning' : ($destination['eco_badge'] === 'Silver' ? 'secondary' : 'success') ?> text-dark fs-5 mt-2">
                        <?= View::e($destination['eco_badge']) ?> Badge
                    </span>
                    <?php endif; ?>
                    <p class="small text-muted mt-2 mb-0">Destinasi ini menerapkan praktik pariwisata berkelanjutan</p>
                </div>
            </div>
            <?php endif; ?>
            
            <!-- Map -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">Lokasi</h5>
                </div>
                <div class="card-body">
                    <div id="map" style="height: 300px; background-color: #e9ecef; display: flex; align-items: center; justify-content: center;">
                        <span class="text-muted">Peta akan ditampilkan di sini</span>
                    </div>
                    <p class="mt-2 mb-0">
                        <i class="fas fa-map-marker-alt me-1"></i>
                        <?= View::e($destination['latitude']) ?>, <?= View::e($destination['longitude']) ?>
                    </p>
                </div>
            </div>
            
            <!-- Ticket Availability -->
            <?php if (!empty($destination['daily_quota'])): ?>
            <?php
            $remaining = $destination['daily_quota'] - ($destination['daily_quota_used'] ?? 0);
            $percent = round(($destination['daily_quota_used'] ?? 0) / $destination['daily_quota'] * 100);
            $isLow = $remaining <= $destination['daily_quota'] * 0.2;
            $isSoldOut = $remaining <= 0;
            ?>
            <div class="card mb-4 <?= $isSoldOut ? 'border-danger' : ($isLow ? 'border-warning' : '') ?>">
                <div class="card-header <?= $isSoldOut ? 'bg-danger bg-opacity-10' : ($isLow ? 'bg-warning bg-opacity-10' : 'bg-light') ?>">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-ticket-alt me-2 <?= $isSoldOut ? 'text-danger' : ($isLow ? 'text-warning' : 'text-success') ?>"></i>
                        Ketersediaan Tiket Hari Ini
                    </h5>
                </div>
                <div class="card-body">
                    <?php if ($isSoldOut): ?>
                    <div class="text-center py-2">
                        <span class="badge bg-danger fs-6"><i class="fas fa-times-circle me-1"></i>Tiket Habis</span>
                        <p class="text-muted small mt-2 mb-0">Tiket untuk hari ini sudah terjual habis. Coba tanggal lain.</p>
                    </div>
                    <?php else: ?>
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted">Tersedia:</span>
                        <span class="fw-bold <?= $isLow ? 'text-warning' : 'text-success' ?>"><?= number_format($remaining) ?> tiket</span>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted">Terjual:</span>
                        <span><?= number_format($destination['daily_quota_used'] ?? 0) ?> / <?= number_format($destination['daily_quota']) ?></span>
                    </div>
                    <div class="progress mb-3" style="height: 8px;">
                        <div class="progress-bar <?= $isLow ? 'bg-warning' : 'bg-success' ?>" style="width: <?= $percent ?>%"></div>
                    </div>
                    <?php if ($isLow): ?>
                    <div class="alert alert-warning py-2 mb-0">
                        <i class="fas fa-exclamation-triangle me-1"></i>Tiket tersisa <?= $remaining ?>! Segera pesan.
                    </div>
                    <?php endif; ?>
                    <?php endif; ?>
                </div>
            </div>
            <?php endif; ?>

            <!-- Nearby Destinations -->
            <?php if (!empty($nearby)): ?>
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">Destinasi Terdekat</h5>
                </div>
                <div class="card-body">
                    <?php foreach ($nearby as $near): ?>
                        <?php if ($near['id'] != $destination['id']): ?>
                        <div class="d-flex align-items-center mb-3">
                            <?php if (!empty($near['main_image'])): ?>
                                <img src="<?= View::asset('uploads/destinations/' . $near['main_image']) ?>" class="rounded me-3" style="width: 60px; height: 60px; object-fit: cover;">
                            <?php else: ?>
                                <img src="https://via.placeholder.com/60" class="rounded me-3" style="width: 60px; height: 60px; object-fit: cover;">
                            <?php endif; ?>
                            <div class="flex-grow-1">
                                <h6 class="mb-0"><?= View::e($near['name']) ?></h6>
                                <small class="text-muted"><?= number_format($near['distance'], 2) ?> km</small>
                            </div>
                            <a href="<?= View::url('destinations/detail?id=' . $near['id']) ?>" class="btn btn-sm btn-outline-primary">
                                Lihat
                            </a>
                        </div>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
document.getElementById('reviewForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    var formData = new FormData(this);
    formData.append('csrf_token', '<?= Middleware::csrfToken() ?>');
    
    fetch(window.APP_URL + 'destinations/addReview', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.status === 'success') {
            Swal.fire({
                icon: 'success',
                title: 'Berhasil',
                text: data.message,
                timer: 1500,
                showConfirmButton: false
            }).then(function() {
                location.reload();
            });
        } else {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: data.message,
                confirmButtonColor: '#0d6efd'
            });
        }
    })
    .catch(error => {
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: 'Terjadi kesalahan',
            confirmButtonColor: '#0d6efd'
        });
    });
});
</script>

<script>
function toggleFavorite(type, id) {
    var formData = new FormData();
    formData.append('item_type', type);
    formData.append('item_id', id);
    
    fetch(window.APP_URL + 'favorites/toggle', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.status === 'success') {
            var btn = document.getElementById('favBtn');
            var icon = btn.querySelector('i');
            if (data.action === 'added') {
                icon.className = 'fas fa-heart';
                btn.classList.remove('btn-outline-danger');
                btn.classList.add('btn-danger');
            } else {
                icon.className = 'far fa-heart';
                btn.classList.remove('btn-danger');
                btn.classList.add('btn-outline-danger');
            }
            Swal.fire({ icon: 'success', title: data.action === 'added' ? 'Ditambahkan' : 'Dihapus', text: data.message, timer: 1000, showConfirmButton: false });
        }
    });
}
</script>

<?php include APP_ROOT . '/app/views/layouts/footer.php'; ?>
