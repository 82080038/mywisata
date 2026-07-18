<?php include APP_ROOT . '/app/views/layouts/header.php'; ?>

<?php $facilities = Hotel::parseFacilities($hotel['facilities'] ?? null); ?>

<div class="container py-4">
    <nav aria-label="breadcrumb" class="mb-3">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="<?= View::url() ?>">Beranda</a></li>
            <li class="breadcrumb-item"><a href="<?= View::url('hotels') ?>">Penginapan</a></li>
            <?php if (!empty($hotel['type'])): ?>
            <li class="breadcrumb-item"><a href="<?= View::url('hotels') ?>?type=<?= $hotel['type'] ?>"><?= Hotel::typeLabel($hotel['type']) ?></a></li>
            <?php endif; ?>
            <li class="breadcrumb-item active"><?= View::e($hotel['name']) ?></li>
        </ol>
    </nav>

    <?php include APP_ROOT . '/app/views/partials/translate_widget.php'; ?>

    <div class="row">
        <div class="col-md-8">
            <!-- Photo Gallery -->
            <div class="card shadow-sm mb-3 p-0 overflow-hidden">
                <?php if (!empty($images)): ?>
                <!-- Main image + thumbnail gallery -->
                <div class="row g-0">
                    <div class="col-md-8">
                        <img id="mainGalleryImage" src="<?= View::asset('uploads/hotels/' . $images[0]['image']) ?>" class="w-100" alt="<?= View::e($hotel['name']) ?>" style="height: 400px; object-fit: cover; cursor: pointer;" data-bs-toggle="modal" data-bs-target="#lightboxModal" data-index="0">
                    </div>
                    <div class="col-md-4">
                        <div class="d-flex flex-column gap-1 h-100">
                            <?php for ($i = 1; $i <= min(3, count($images) - 1); $i++): ?>
                            <img src="<?= View::asset('uploads/hotels/' . $images[$i]['image']) ?>" 
                                 class="w-100 gallery-thumb <?= $i === 1 ? '' : '' ?>" 
                                 alt="<?= View::e($images[$i]['caption'] ?? '') ?>"
                                 style="height: calc(400px / 3.2); object-fit: cover; cursor: pointer;"
                                 onclick="changeMainImage(this, '<?= View::asset('uploads/hotels/' . $images[$i]['image']) ?>', <?= $i ?>)"
                                 data-bs-toggle="modal" data-bs-target="#lightboxModal" data-index="<?= $i ?>">
                            <?php endfor; ?>
                            <?php if (count($images) > 4): ?>
                            <div class="position-relative">
                                <img src="<?= View::asset('uploads/hotels/' . $images[3]['image']) ?>" class="w-100" style="height: calc(400px / 3.2); object-fit: cover; cursor: pointer; filter: brightness(0.5);" data-bs-toggle="modal" data-bs-target="#lightboxModal" data-index="3">
                                <div class="position-absolute top-50 start-50 translate-middle text-white fw-bold" style="cursor: pointer;" data-bs-toggle="modal" data-bs-target="#lightboxModal" data-index="3">
                                    +<?= count($images) - 4 ?> foto
                                </div>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                <?php elseif (!empty($hotel['main_image'])): ?>
                <img src="<?= View::asset('uploads/hotels/' . $hotel['main_image']) ?>" class="w-100" alt="<?= View::e($hotel['name']) ?>" style="height: 400px; object-fit: cover;">
                <?php else: ?>
                <div class="bg-light d-flex align-items-center justify-content-center" style="height: 400px;">
                    <i class="fas <?= Hotel::typeIcon($hotel['type']) ?> fa-5x text-muted"></i>
                </div>
                <?php endif; ?>
            </div>

            <!-- Thumbnail strip -->
            <?php if (!empty($images) && count($images) > 1): ?>
            <div class="d-flex gap-2 mb-3 overflow-auto">
                <?php foreach ($images as $idx => $img): ?>
                <img src="<?= View::asset('uploads/hotels/' . $img['image']) ?>" 
                     class="rounded border thumb-strip <?= $idx === 0 ? 'border-primary border-2' : '' ?>"
                     alt="<?= View::e($img['caption'] ?? '') ?>"
                     style="width: 80px; height: 60px; object-fit: cover; cursor: pointer;"
                     onclick="changeMainImage(this, '<?= View::asset('uploads/hotels/' . $img['image']) ?>', <?= $idx ?>)"
                     data-bs-toggle="modal" data-bs-target="#lightboxModal" data-index="<?= $idx ?>">
                <?php endforeach; ?>
            </div>
            <?php endif; ?>

            <!-- Main Info -->
            <div class="card shadow-sm mb-3">
                <div class="card-body">
                    <div class="mb-2">
                        <span class="badge bg-primary fs-6"><i class="fas <?= Hotel::typeIcon($hotel['type']) ?> me-1"></i><?= Hotel::typeLabel($hotel['type']) ?></span>
                        <?php if (!empty($hotel['star_rating'])): ?>
                        <span class="badge bg-warning text-dark fs-6">
                            <?php for ($i = 0; $i < $hotel['star_rating']; $i++): ?><i class="fas fa-star"></i><?php endfor; ?>
                        </span>
                        <?php endif; ?>
                    </div>
                    <h2 class="mb-2"><?= View::e($hotel['name']) ?></h2>
                    <p class="text-muted mb-2">
                        <i class="fas fa-map-marker-alt me-1"></i><?= View::e($hotel['address'] ?? '') ?>, <?= View::e($hotel['city']) ?>, <?= View::e($hotel['province'] ?? '') ?>
                    </p>
                    <?php
                    $shareUrl = View::url('hotels/detail/' . $hotel['id']);
                    $shareTitle = $hotel['name'] . ' - MyWisata';
                    $shareText = 'Ingin menginap di ' . $hotel['name'] . ' ' . ($hotel['city'] ?? '');
                    include APP_ROOT . '/app/views/partials/social_share.php';
                    ?>
                    <div class="d-flex gap-3 mb-3">
                        <div>
                            <i class="fas fa-star text-warning"></i>
                            <strong><?= number_format($hotel['rating_avg'], 1) ?></strong>
                            <small class="text-muted">(<?= $hotel['review_count'] ?> reviews)</small>
                        </div>
                        <?php if (!empty($hotel['total_rooms'])): ?>
                        <div><i class="fas fa-door-open me-1"></i><?= $hotel['total_rooms'] ?> kamar</div>
                        <?php endif; ?>
                        <?php if (!empty($images)): ?>
                        <div><i class="fas fa-camera me-1"></i><?= count($images) ?> foto</div>
                        <?php endif; ?>
                    </div>
                    <p class="lead"><?= nl2br(View::e($hotel['description'] ?? '')) ?></p>
                </div>
            </div>

            <!-- Facilities with icons -->
            <?php if (!empty($facilities)): ?>
            <div class="card shadow-sm mb-3">
                <div class="card-header bg-light">
                    <h5 class="mb-0"><i class="fas fa-list-check me-2 text-primary"></i>Fasilitas Penginapan</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <?php foreach ($facilities as $f): ?>
                        <div class="col-md-4 mb-2 d-flex align-items-center">
                            <div class="bg-success bg-opacity-10 rounded-circle d-flex align-items-center justify-content-center me-2" style="width: 32px; height: 32px; min-width: 32px;">
                                <i class="fas fa-check text-success"></i>
                            </div>
                            <span><?= Hotel::facilityLabel($f) ?></span>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
            <?php endif; ?>

            <!-- Structured Facilities (from entity_facilities) -->
            <?php if (!empty($facilities)): ?>
            <div class="card shadow-sm mb-3">
                <div class="card-header bg-light">
                    <h5 class="mb-0"><i class="fas fa-list-check me-2 text-primary"></i>Fasilitas Lengkap</h5>
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

            <!-- Foto & Dokumentasi Fasilitas -->
            <?php if (!empty($images)): ?>
            <div class="card shadow-sm mb-3">
                <div class="card-header bg-light">
                    <h5 class="mb-0"><i class="fas fa-camera me-2 text-primary"></i>Foto & Dokumentasi Fasilitas</h5>
                </div>
                <div class="card-body">
                    <?php foreach ($images as $img): ?>
                    <div class="row mb-4 pb-4 border-bottom align-items-start">
                        <div class="col-md-5">
                            <img src="<?= View::asset('uploads/hotels/' . $img['image']) ?>" 
                                 class="rounded shadow-sm w-100" 
                                 alt="<?= View::e($img['caption'] ?? '') ?>"
                                 style="height: 220px; object-fit: cover; cursor: pointer;"
                                 data-bs-toggle="modal" data-bs-target="#lightboxModal" data-index="<?= array_search($img, array_values($images)) ?>">
                        </div>
                        <div class="col-md-7">
                            <div class="d-flex align-items-center mb-2">
                                <?php if (!empty($img['facility_key'])): ?>
                                <span class="badge bg-primary me-2">
                                    <i class="fas fa-check-circle me-1"></i><?= Hotel::facilityLabel($img['facility_key']) ?>
                                </span>
                                <?php endif; ?>
                                <h6 class="mb-0"><?= View::e($img['caption'] ?? '') ?></h6>
                            </div>
                            <?php if (!empty($img['description'])): ?>
                            <p class="text-muted small mb-0"><?= View::e($img['description']) ?></p>
                            <?php endif; ?>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>

            <!-- Rooms with photos -->
            <?php if (!empty($rooms)): ?>
            <div class="card shadow-sm mb-3">
                <div class="card-header bg-light">
                    <h5 class="mb-0"><i class="fas fa-bed me-2 text-primary"></i>Pilihan Kamar</h5>
                </div>
                <div class="card-body">
                    <?php foreach ($rooms as $room): ?>
                    <div class="row mb-3 pb-3 border-bottom align-items-center">
                        <div class="col-md-3">
                            <?php if (!empty($room['image'])): ?>
                            <img src="<?= View::asset('uploads/rooms/' . $room['image']) ?>" class="rounded w-100" alt="<?= View::e($room['room_type']) ?>" style="height: 120px; object-fit: cover;">
                            <?php else: ?>
                            <div class="bg-light rounded d-flex align-items-center justify-content-center" style="height: 120px;">
                                <i class="fas fa-bed fa-2x text-muted"></i>
                            </div>
                            <?php endif; ?>
                        </div>
                        <div class="col-md-5">
                            <h6 class="mb-1"><?= View::e($room['room_type']) ?></h6>
                            <p class="text-muted small mb-1"><?= View::e($room['description'] ?? '') ?></p>
                            <div class="d-flex gap-3 text-muted small">
                                <span><i class="fas fa-users me-1"></i><?= $room['capacity'] ?> orang</span>
                                <span><i class="fas fa-door-closed me-1"></i><?= $room['available_rooms'] ?>/<?= $room['total_rooms'] ?> tersedia</span>
                            </div>
                            <?php if (!empty($room['amenities'])): ?>
                            <?php $roomAmenities = json_decode($room['amenities'], true); ?>
                            <?php if (is_array($roomAmenities)): ?>
                            <div class="mt-1">
                                <?php foreach ($roomAmenities as $a): ?>
                                <span class="badge bg-light text-dark border me-1"><?= Hotel::facilityLabel($a) ?></span>
                                <?php endforeach; ?>
                            </div>
                            <?php endif; ?>
                            <?php endif; ?>
                        </div>
                        <div class="col-md-4 text-end">
                            <p class="fw-bold text-success fs-5 mb-0"><?= View::currency($room['price_per_night']) ?></p>
                            <small class="text-muted">per malam</small><br>
                            <?php if ($room['available_rooms'] > 0): ?>
                                <?php if ($room['available_rooms'] <= 3): ?>
                                <span class="badge bg-warning text-dark mb-2"><i class="fas fa-exclamation-triangle me-1"></i>Tersisa <?= $room['available_rooms'] ?> kamar!</span><br>
                                <?php else: ?>
                                <span class="badge bg-success mb-2"><i class="fas fa-check-circle me-1"></i>Tersedia</span><br>
                                <?php endif; ?>
                            <button class="btn btn-sm btn-primary mt-1" onclick="addToCart('hotel', <?= $hotel['id'] ?>, '<?= View::e($hotel['name']) ?>', <?= $room['price_per_night'] ?>)">
                                <i class="fas fa-cart-plus me-1"></i>Booking
                            </button>
                            <?php else: ?>
                            <span class="badge bg-danger mb-2"><i class="fas fa-times-circle me-1"></i>Habis</span><br>
                            <button class="btn btn-sm btn-secondary mt-1" disabled>
                                <i class="fas fa-ban me-1"></i>Booking
                            </button>
                            <?php endif; ?>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>

            <!-- Video Gallery -->
            <?php include APP_ROOT . '/app/views/partials/video_gallery.php'; ?>

            <!-- Reviews -->
            <?php if (!empty($reviews)): ?>
            <div class="card shadow-sm mb-3">
                <div class="card-header bg-light">
                    <h5 class="mb-0"><i class="fas fa-comments me-2 text-primary"></i>Review Tamu</h5>
                </div>
                <div class="card-body">
                    <?php foreach ($reviews as $review): ?>
                    <div class="mb-3 pb-3 border-bottom">
                        <div class="d-flex justify-content-between">
                            <div>
                                <i class="fas fa-user-circle fa-lg text-muted me-1"></i>
                                <strong><?= View::e($review['user_name'] ?? 'Anonim') ?></strong>
                            </div>
                            <span>
                                <?php for ($i = 0; $i < $review['rating']; $i++): ?><i class="fas fa-star text-warning"></i><?php endfor; ?>
                                <?php for ($i = $review['rating']; $i < 5; $i++): ?><i class="far fa-star text-muted"></i><?php endfor; ?>
                            </span>
                        </div>
                        <p class="text-muted small mb-0 mt-1"><?= View::e($review['comment'] ?? '') ?></p>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>
        </div>

        <!-- Sidebar -->
        <div class="col-md-4">
            <!-- Weather Widget -->
            <?php
            $latitude = $hotel['latitude'] ?? null;
            $longitude = $hotel['longitude'] ?? null;
            $cityName = $hotel['city'] ?? $hotel['name'];
            include APP_ROOT . '/app/views/partials/weather_widget.php';
            ?>
            <div class="card shadow-sm mb-3 sticky-top" style="top: 80px;">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="fas fa-info-circle me-2"></i>Informasi Penginapan</h5>
                </div>
                <div class="card-body">
                    <table class="table table-sm">
                        <tr>
                            <td><i class="fas fa-tag me-2 text-primary"></i>Tipe</td>
                            <td class="text-end fw-bold"><?= Hotel::typeLabel($hotel['type']) ?></td>
                        </tr>
                        <?php if (!empty($hotel['star_rating'])): ?>
                        <tr>
                            <td><i class="fas fa-star me-2 text-warning"></i>Bintang</td>
                            <td class="text-end"><?= $hotel['star_rating'] ?> Bintang</td>
                        </tr>
                        <?php endif; ?>
                        <tr>
                            <td><i class="fas fa-map-marker-alt me-2 text-danger"></i>Lokasi</td>
                            <td class="text-end"><?= View::e($hotel['city']) ?></td>
                        </tr>
                        <?php if (!empty($hotel['check_in_time'])): ?>
                        <tr>
                            <td><i class="fas fa-clock me-2 text-success"></i>Check-in</td>
                            <td class="text-end"><?= date('H:i', strtotime($hotel['check_in_time'])) ?> WIB</td>
                        </tr>
                        <?php endif; ?>
                        <?php if (!empty($hotel['check_out_time'])): ?>
                        <tr>
                            <td><i class="fas fa-clock me-2 text-danger"></i>Check-out</td>
                            <td class="text-end"><?= date('H:i', strtotime($hotel['check_out_time'])) ?> WIB</td>
                        </tr>
                        <?php endif; ?>
                        <?php if (!empty($hotel['phone'])): ?>
                        <tr>
                            <td><i class="fas fa-phone me-2 text-info"></i>Telepon</td>
                            <td class="text-end"><?= View::e($hotel['phone']) ?></td>
                        </tr>
                        <?php endif; ?>
                        <?php if (!empty($hotel['email'])): ?>
                        <tr>
                            <td><i class="fas fa-envelope me-2 text-secondary"></i>Email</td>
                            <td class="text-end"><small><?= View::e($hotel['email']) ?></small></td>
                        </tr>
                        <?php endif; ?>
                        <?php if (!empty($hotel['total_rooms'])): ?>
                        <tr>
                            <td><i class="fas fa-door-open me-2 text-primary"></i>Total Kamar</td>
                            <td class="text-end"><?= $hotel['total_rooms'] ?></td>
                        </tr>
                        <?php endif; ?>
                    </table>

                    <?php if (Session::get('user_id') && Session::get('user_id') != $hotel['owner_id']): ?>
                    <a href="<?= View::url('messages/compose?to=' . $hotel['owner_id'] . '&context=hotel&context_id=' . $hotel['id']) ?>" 
                       class="btn btn-success btn-sm w-100 mb-2">
                        <i class="fas fa-comments me-1"></i>Chat dengan Pemilik
                    </a>
                    <?php elseif (!Session::get('user_id')): ?>
                    <a href="<?= View::url('auth/login') ?>" class="btn btn-outline-success btn-sm w-100 mb-2">
                        <i class="fas fa-sign-in-alt me-1"></i>Login untuk Chat
                    </a>
                    <?php endif; ?>

                    <?php if (Session::get('user_id')): ?>
                    <?php
                    $userModel = new User();
                    $foodPrefs = $userModel->getFoodPreferences(Session::get('user_id'));
                    ?>
                    <?php if (!empty($foodPrefs['allergies']) || !empty($foodPrefs['preferences'])): ?>
                    <div class="alert alert-warning border-warning mt-2 p-2">
                        <small class="fw-bold"><i class="fas fa-utensils text-warning me-1"></i>Alergi Makanan Anda:</small><br>
                        <?php foreach ($foodPrefs['allergies'] as $a): ?>
                        <span class="badge bg-danger me-1 mb-1" style="font-size: 10px;"><?= User::allergyLabel($a) ?></span>
                        <?php endforeach; ?>
                        <?php foreach ($foodPrefs['preferences'] as $p): ?>
                        <span class="badge bg-success me-1 mb-1" style="font-size: 10px;"><?= User::preferenceLabel($p) ?></span>
                        <?php endforeach; ?>
                        <br><a href="<?= View::url('dashboard/foodPreferences') ?>" class="small text-decoration-none">Ubah Preferensi</a>
                    </div>
                    <?php endif; ?>
                    <?php endif; ?>

                    <?php if (!empty($hotel['price_range_min'])): ?>
                    <hr>
                    <div class="text-center bg-light rounded p-3">
                        <small class="text-muted d-block">Rentang Harga</small>
                        <h4 class="text-primary mb-0"><?= View::currency($hotel['price_range_min']) ?></h4>
                        <small class="text-muted">sampai <?= View::currency($hotel['price_range_max']) ?></small>
                        <small class="text-muted d-block mt-1">per malam</small>
                    </div>
                    <?php endif; ?>

                    <?php if (!empty($facilities)): ?>
                    <hr>
                    <small class="fw-bold text-muted d-block mb-2">Fasilitas Utama:</small>
                    <div>
                        <?php foreach (array_slice($facilities, 0, 6) as $f): ?>
                        <span class="badge bg-light text-dark border me-1 mb-1"><i class="fas fa-check text-success me-1"></i><?= Hotel::facilityLabel($f) ?></span>
                        <?php endforeach; ?>
                        <?php if (count($facilities) > 6): ?>
                        <span class="badge bg-primary">+<?= count($facilities) - 6 ?></span>
                        <?php endif; ?>
                    </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Map -->
            <?php if (!empty($hotel['latitude']) && !empty($hotel['longitude'])): ?>
            <div class="card shadow-sm mb-3">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-map me-2 text-primary"></i>Lokasi</h5>
                </div>
                <div class="card-body p-0">
                    <div id="hotelMap" style="height: 250px;"></div>
                </div>
            </div>
            <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
            <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
            <script>
                var map = L.map('hotelMap').setView([<?= $hotel['latitude'] ?>, <?= $hotel['longitude'] ?>], 13);
                L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png').addTo(map);
                L.marker([<?= $hotel['latitude'] ?>, <?= $hotel['longitude'] ?>]).addTo(map)
                    .bindPopup('<?= View::e($hotel['name']) ?>').openPopup();
            </script>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Lightbox Modal -->
<div class="modal fade" id="lightboxModal" tabindex="-1">
    <div class="modal-dialog modal-xl modal-dialog-centered">
        <div class="modal-content bg-dark">
            <div class="modal-header border-0">
                <h5 class="modal-title text-white" id="lightboxCaption"></h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body text-center p-0">
                <img id="lightboxImage" src="" class="img-fluid" style="max-height: 80vh;">
            </div>
            <div class="modal-footer border-0 justify-content-between">
                <button type="button" class="btn btn-light" onclick="navigateLightbox(-1)"><i class="fas fa-chevron-left"></i></button>
                <span class="text-white" id="lightboxCounter"></span>
                <button type="button" class="btn btn-light" onclick="navigateLightbox(1)"><i class="fas fa-chevron-right"></i></button>
            </div>
        </div>
    </div>
</div>

<script>
var galleryImages = [
    <?php if (!empty($images)): ?>
    <?php foreach ($images as $img): ?>
    { src: '<?= View::asset('uploads/hotels/' . $img['image']) ?>', caption: '<?= View::e($img['caption'] ?? '') ?>' },
    <?php endforeach; ?>
    <?php endif; ?>
];
var currentLightboxIndex = 0;

function changeMainImage(thumb, src, index) {
    document.getElementById('mainGalleryImage').src = src;
    document.getElementById('mainGalleryImage').dataset.index = index;
    document.querySelectorAll('.thumb-strip').forEach(function(t) { t.classList.remove('border-primary', 'border-2'); });
    if (thumb.classList.contains('thumb-strip')) {
        thumb.classList.add('border-primary', 'border-2');
    }
}

document.getElementById('lightboxModal').addEventListener('show.bs.modal', function(event) {
    var trigger = event.relatedTarget;
    var index = parseInt(trigger.dataset.index || 0);
    currentLightboxIndex = index;
    showLightboxImage();
});

function showLightboxImage() {
    if (galleryImages.length === 0) return;
    var img = galleryImages[currentLightboxIndex];
    document.getElementById('lightboxImage').src = img.src;
    document.getElementById('lightboxCaption').textContent = img.caption;
    document.getElementById('lightboxCounter').textContent = (currentLightboxIndex + 1) + ' / ' + galleryImages.length;
}

function navigateLightbox(direction) {
    currentLightboxIndex += direction;
    if (currentLightboxIndex < 0) currentLightboxIndex = galleryImages.length - 1;
    if (currentLightboxIndex >= galleryImages.length) currentLightboxIndex = 0;
    showLightboxImage();
}

document.addEventListener('keydown', function(e) {
    var modal = document.getElementById('lightboxModal');
    if (modal.classList.contains('show')) {
        if (e.key === 'ArrowLeft') navigateLightbox(-1);
        if (e.key === 'ArrowRight') navigateLightbox(1);
        if (e.key === 'Escape') bootstrap.Modal.getInstance(modal).hide();
    }
});

function addToCart(type, itemId, name, price) {
    var csrf = '<?= Middleware::csrfToken() ?>';
    var formData = new FormData();
    formData.append('csrf_token', csrf);
    formData.append('type', type);
    formData.append('item_id', itemId);
    formData.append('quantity', 1);
    formData.append('data[name]', name);
    formData.append('data[price]', price);
    formData.append('data[room_price]', price);

    fetch(window.APP_URL + 'cart/add', {
        method: 'POST',
        headers: { 'X-Requested-With': 'XMLHttpRequest' },
        body: formData
    })
    .then(function(r) { return r.json(); })
    .then(function(data) {
        if (data.status === 'success') {
            Swal.fire({ icon: 'success', title: 'Berhasil', text: data.message, timer: 1200, showConfirmButton: false });
        } else {
            Swal.fire({ icon: 'error', title: 'Error', text: data.message });
        }
    });
}
</script>

<?php include APP_ROOT . '/app/views/layouts/footer.php'; ?>
