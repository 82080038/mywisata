<?php include APP_ROOT . '/app/views/layouts/header.php'; ?>

<div class="container py-4">
    <div class="row mb-4">
        <div class="col-md-12">
            <h1 class="mb-3"><i class="fas fa-mosque me-2"></i>Wisata Halal</h1>
            <p class="text-muted">Paket wisata halal dengan fasilitas prayer room dan makanan halal</p>
        </div>
    </div>
    
    <!-- Search & Filter -->
    <div class="card mb-4 glass-card">
        <div class="card-body">
            <form method="GET" action="<?= View::url('halal-tourism') ?>">
                <div class="row">
                    <div class="col-md-4 mb-3">
                        <div class="form-floating">
                            <input type="text" class="form-control" name="search" id="search" placeholder="Cari paket..." value="<?= View::e($filters['search'] ?? '') ?>">
                            <label for="search">Cari paket...</label>
                        </div>
                    </div>
                    <div class="col-md-3 mb-3">
                        <div class="form-floating">
                            <select class="form-select" name="duration" id="duration">
                                <option value="">Semua Durasi</option>
                                <option value="1">1 Hari</option>
                                <option value="2">2 Hari</option>
                                <option value="3">3 Hari</option>
                                <option value="5">5 Hari</option>
                                <option value="7">7 Hari</option>
                            </select>
                            <label for="duration">Durasi</label>
                        </div>
                    </div>
                    <div class="col-md-3 mb-3">
                        <div class="form-floating">
                            <select class="form-select" name="price_range" id="price_range">
                                <option value="">Semua Harga</option>
                                <option value="low">Di bawah 1 Juta</option>
                                <option value="medium">1-3 Juta</option>
                                <option value="high">3-5 Juta</option>
                                <option value="premium">Di atas 5 Juta</option>
                            </select>
                            <label for="price_range">Rentang Harga</label>
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
    
    <!-- Halal Packages -->
    <div class="row">
        <div class="col-md-12">
            <h3 class="mb-3 section-title"><i class="fas fa-suitcase me-2"></i>Paket Wisata Halal</h3>
            <?php if (empty($activities)): ?>
                <div class="empty-state text-center py-5">
                    <i class="fas fa-search fa-3x text-muted mb-3"></i>
                    <h5 class="text-muted">Tidak ada paket ditemukan</h5>
                    <p class="text-muted">Coba ubah filter atau kata kunci pencarian</p>
                    <a href="<?= View::url('halal-tourism') ?>" class="btn btn-primary mt-2">
                        <i class="fas fa-redo me-2"></i>Reset Filter
                    </a>
                </div>
            <?php else: ?>
                <div class="row">
                    <?php foreach ($activities as $activity): ?>
                    <div class="col-md-4 mb-4">
                        <div class="card h-100 hover-shadow">
                            <?php if (!empty($activity['image_url'])): ?>
                                <?php if (filter_var($activity['image_url'], FILTER_VALIDATE_URL)): ?>
                                    <img src="<?= View::e($activity['image_url']) ?>" class="card-img-top" alt="<?= View::e($activity['name']) ?>" style="height: 200px; object-fit: cover;">
                                <?php else: ?>
                                    <img src="<?= View::asset('uploads/halal_tourism/' . $activity['image_url']) ?>" class="card-img-top" alt="<?= View::e($activity['name']) ?>" style="height: 200px; object-fit: cover;">
                                <?php endif; ?>
                            <?php else: ?>
                                <div class="card-img-top bg-success d-flex align-items-center justify-content-center" style="height: 200px;">
                                    <i class="fas fa-mosque text-white" style="font-size: 3rem;"></i>
                                </div>
                            <?php endif; ?>
                            <div class="card-body">
                                <h5 class="card-title"><?= View::e($activity['name']) ?></h5>
                                <p class="card-text text-muted small">
                                    <i class="fas fa-clock me-1"></i><?= $activity['duration_days'] ?> Hari / <?= $activity['duration_nights'] ?> Malam
                                </p>
                                <p class="card-text small"><?= View::e(substr($activity['description'], 0, 100)) ?>...</p>
                                <div class="d-flex justify-content-between align-items-center mt-2">
                                    <div class="text-primary fw-bold">
                                        <?= $activity['display_price'] ?>
                                    </div>
                                    <?php if ($activity['is_featured']): ?>
                                        <span class="badge badge-recommendation">Unggulan</span>
                                    <?php endif; ?>
                                </div>
                                <a href="<?= View::url('halal-tourism/show?slug=' . $activity['slug']) ?>" class="btn btn-primary btn-modern mt-3 w-100">
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
    
    <!-- Prayer Rooms Section -->
    <div class="row mt-5">
        <div class="col-md-12">
            <h3 class="mb-3 section-title"><i class="fas fa-pray me-2"></i>Prayer Room Terdekat</h3>
            <div class="card glass-card">
                <div class="card-body">
                    <p class="text-muted">Aktifkan lokasi untuk menemukan prayer room terdekat</p>
                    <button onclick="getLocation()" class="btn btn-primary btn-modern">
                        <i class="fas fa-location-arrow me-2"></i>Aktifkan Lokasi
                    </button>
                    <div id="prayer-rooms-list" class="mt-3"></div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function getLocation() {
    if (navigator.geolocation) {
        navigator.geolocation.getCurrentPosition(showPrayerRooms, showError);
    } else {
        alert("Geolocation tidak didukung oleh browser ini");
    }
}

function showPrayerRooms(position) {
    const lat = position.coords.latitude;
    const lng = position.coords.longitude;
    
    fetch(`<?= View::url('halal-tourism/prayer-rooms') ?>?lat=${lat}&lng=${lng}&radius=5`)
        .then(response => response.json())
        .then(data => {
            if (data.status === 'success') {
                let html = '<div class="list-group">';
                data.data.forEach(room => {
                    html += `
                        <a href="#" class="list-group-item list-group-item-action">
                            <div class="d-flex w-100 justify-content-between">
                                <h5 class="mb-1">${room.name}</h5>
                                <small>${room.distance_km.toFixed(2)} km</small>
                            </div>
                            <p class="mb-1 small">${room.address}</p>
                            <small>${room.facilities}</small>
                        </a>
                    `;
                });
                html += '</div>';
                document.getElementById('prayer-rooms-list').innerHTML = html;
            }
        });
}

function showError(error) {
    alert("Gagal mendapatkan lokasi: " + error.message);
}
</script>

<?php include APP_ROOT . '/app/views/layouts/footer.php'; ?>
