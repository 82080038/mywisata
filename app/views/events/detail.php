<?php include APP_ROOT . '/app/views/layouts/header.php'; ?>

<div class="container py-4">
    <nav aria-label="breadcrumb" class="mb-4">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="<?= View::url('home') ?>">Beranda</a></li>
            <li class="breadcrumb-item"><a href="<?= View::url('events') ?>">Event</a></li>
            <li class="breadcrumb-item active" aria-current="page"><?= View::e($event['title']) ?></li>
        </ol>
    </nav>

    <?php include APP_ROOT . '/app/views/partials/translate_widget.php'; ?>

    <div class="row">
        <div class="col-md-8">
            <div class="card mb-4">
                <?php if (!empty($event['main_image'])): ?>
                    <img src="<?= View::asset('uploads/events/' . $event['main_image']) ?>" class="card-img-top" alt="<?= View::e($event['title']) ?>" style="height: 400px; object-fit: cover;">
                <?php else: ?>
                    <img src="https://via.placeholder.com/800x400" class="card-img-top" alt="<?= View::e($event['title']) ?>" style="height: 400px; object-fit: cover;">
                <?php endif; ?>
                <div class="card-body">
                    <h1 class="card-title"><?= View::e($event['title']) ?></h1>
                    <p class="text-muted">
                        <i class="fas fa-calendar me-1"></i><?= View::date($event['start_date']) ?>
                        <i class="fas fa-clock ms-3 me-1"></i><?= isset($event['start_date']) ? date('H:i', strtotime($event['start_date'])) : '-' ?>
                    </p>
                    <?php
                    $shareUrl = View::url('events/detail/' . $event['id']);
                    $shareTitle = $event['title'] . ' - MyWisata';
                    $shareText = 'Jangan lewatkan ' . $event['title'] . ' di ' . ($event['city'] ?? $event['location_name'] ?? '');
                    include APP_ROOT . '/app/views/partials/social_share.php';
                    ?>
                    <p class="text-muted">
                        <i class="fas fa-map-marker-alt me-1"></i><?= View::e($event['location_name'] ?? '-') ?><?= !empty($event['address']) ? ', ' . View::e($event['address']) : '' ?>
                    </p>
                    <div class="d-flex align-items-center mb-3">
                        <div class="me-3">
                            <i class="fas fa-star text-warning"></i> <?= number_format($event['rating_avg'], 1) ?>
                            <span class="text-muted small">(<?= $event['review_count'] ?> review)</span>
                        </div>
                        <span class="badge bg-primary"><?= View::e($event['category']) ?></span>
                        <?php
                        $organizerLabels = ['business' => 'Bisnis', 'government' => 'Dinas/Pemerintah', 'community' => 'Komunitas', 'individual' => 'Individu'];
                        $organizerIcons = ['business' => 'fa-store', 'government' => 'fa-landmark', 'community' => 'fa-users', 'individual' => 'fa-user'];
                        $orgType = $event['organizer_type'] ?? 'business';
                        ?>
                        <span class="badge bg-info text-dark ms-2">
                            <i class="fas <?= $organizerIcons[$orgType] ?? 'fa-building' ?> me-1"></i><?= $organizerLabels[$orgType] ?? 'Bisnis' ?>
                        </span>
                        <?php
                        $statusLabels = ['upcoming' => 'Akan Datang', 'ongoing' => 'Berlangsung', 'completed' => 'Selesai', 'cancelled' => 'Dibatalkan'];
                        $statusColors = ['upcoming' => 'success', 'ongoing' => 'primary', 'completed' => 'secondary', 'cancelled' => 'danger'];
                        $status = $event['event_status'] ?? 'upcoming';
                        ?>
                        <span class="badge bg-<?= $statusColors[$status] ?? 'secondary' ?> ms-2"><?= $statusLabels[$status] ?? 'Akan Datang' ?></span>
                    </div>
                    <p class="card-text"><?= nl2br(View::e($event['description'])) ?></p>
                    
                    <div class="row mt-4">
                        <div class="col-md-4">
                            <h6>Harga</h6>
                            <?php if (!empty($event['requires_ticket']) && $event['price'] > 0): ?>
                            <p class="card-text fw-bold text-success"><?= View::currency($event['price']) ?></p>
                            <?php else: ?>
                            <p class="card-text fw-bold text-success"><i class="fas fa-gift me-1"></i>GRATIS</p>
                            <?php endif; ?>
                        </div>
                        <div class="col-md-4">
                            <h6>Registrasi</h6>
                            <?php
                            $regLabels = ['ticket' => 'Perlu Tiket', 'rsvp' => 'Wajib RSVP', 'open' => 'Terbuka Umum', 'none' => 'Tanpa Registrasi'];
                            $regIcons = ['ticket' => 'fa-ticket-alt', 'rsvp' => 'fa-calendar-check', 'open' => 'fa-door-open', 'none' => 'fa-ban'];
                            $regType = $event['registration_type'] ?? 'ticket';
                            ?>
                            <p class="card-text">
                                <i class="fas <?= $regIcons[$regType] ?? 'fa-ticket-alt' ?> me-1"></i>
                                <?= $regLabels[$regType] ?? 'Perlu Tiket' ?>
                            </p>
                        </div>
                        <div class="col-md-4">
                            <h6>Kapasitas</h6>
                            <p class="card-text">
                                <?php if (!empty($event['max_attendees'])): ?>
                                    <?= number_format($event['registered_count'] ?? 0) ?> / <?= number_format($event['max_attendees']) ?> terdaftar
                                <?php elseif (!empty($event['max_participants'])): ?>
                                    <?= number_format($event['max_participants']) ?> peserta
                                <?php else: ?>
                                    <span class="text-muted">Tidak terbatas</span>
                                <?php endif; ?>
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Facilities -->
            <?php if (!empty($facilities)): ?>
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="card-title mb-0"><i class="fas fa-list-check me-2 text-primary"></i>Fasilitas Event</h5>
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
            $latitude = $event['latitude'] ?? null;
            $longitude = $event['longitude'] ?? null;
            $cityName = $event['location_name'] ?? $event['address'] ?? '';
            include APP_ROOT . '/app/views/partials/weather_widget.php';
            ?>
            <?php
            $regType = $event['registration_type'] ?? 'ticket';
            $requiresTicket = !empty($event['requires_ticket']);
            $ticketsSold = $event['tickets_sold'] ?? 0;
            $maxPax = $event['max_participants'] ?? 0;
            $registeredCount = $event['registered_count'] ?? 0;
            $maxAttendees = $event['max_attendees'] ?? null;
            $remaining = $requiresTicket ? ($maxPax - $ticketsSold) : ($maxAttendees ? ($maxAttendees - $registeredCount) : 999999);
            $isSoldOut = $remaining <= 0;
            $isLow = $remaining > 0 && $remaining <= ($maxAttendees ?? $maxPax) * 0.1;
            ?>

            <!-- Availability / Registration Status -->
            <?php if ($regType === 'ticket'): ?>
            <div class="card mb-3 <?= $isSoldOut ? 'border-danger' : ($isLow ? 'border-warning' : '') ?>">
                <div class="card-body">
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted"><i class="fas fa-ticket-alt me-1"></i>Tiket Terjual:</span>
                        <span class="fw-bold"><?= number_format($ticketsSold) ?> / <?= number_format($maxPax) ?></span>
                    </div>
                    <div class="progress mb-2" style="height: 8px;">
                        <div class="progress-bar <?= $isSoldOut ? 'bg-danger' : ($isLow ? 'bg-warning' : 'bg-success') ?>" style="width: <?= $maxPax > 0 ? round($ticketsSold / $maxPax * 100) : 0 ?>%"></div>
                    </div>
                    <div class="d-flex justify-content-between">
                        <span class="text-muted">Tersisa:</span>
                        <?php if ($isSoldOut): ?>
                        <span class="badge bg-danger"><i class="fas fa-times-circle me-1"></i>Habis</span>
                        <?php else: ?>
                        <span class="fw-bold <?= $isLow ? 'text-warning' : 'text-success' ?>"><?= number_format($remaining) ?> tiket</span>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <?php elseif ($regType === 'rsvp' && $maxAttendees): ?>
            <div class="card mb-3 <?= $isSoldOut ? 'border-danger' : ($isLow ? 'border-warning' : '') ?>">
                <div class="card-body">
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted"><i class="fas fa-calendar-check me-1"></i>Terdaftar:</span>
                        <span class="fw-bold"><?= number_format($registeredCount) ?> / <?= number_format($maxAttendees) ?></span>
                    </div>
                    <div class="progress mb-2" style="height: 8px;">
                        <div class="progress-bar <?= $isSoldOut ? 'bg-danger' : ($isLow ? 'bg-warning' : 'bg-success') ?>" style="width: <?= round($registeredCount / $maxAttendees * 100) ?>%"></div>
                    </div>
                    <div class="d-flex justify-content-between">
                        <span class="text-muted">Slot tersisa:</span>
                        <?php if ($isSoldOut): ?>
                        <span class="badge bg-danger"><i class="fas fa-times-circle me-1"></i>Penuh</span>
                        <?php else: ?>
                        <span class="fw-bold <?= $isLow ? 'text-warning' : 'text-success' ?>"><?= number_format($remaining) ?> slot</span>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <?php elseif ($regType === 'open'): ?>
            <div class="card mb-3 border-success">
                <div class="card-body text-center">
                    <i class="fas fa-door-open fa-2x text-success mb-2"></i>
                    <h6 class="text-success mb-1">Terbuka untuk Umum</h6>
                    <p class="text-muted small mb-0">Tidak perlu registrasi, langsung datang!</p>
                    <?php if ($maxAttendees): ?>
                    <p class="small text-muted mt-1 mb-0"><?= number_format($registeredCount) ?> sudah terdaftar</p>
                    <?php endif; ?>
                </div>
            </div>
            <?php elseif ($regType === 'none'): ?>
            <div class="card mb-3 border-info">
                <div class="card-body text-center">
                    <i class="fas fa-info-circle fa-2x text-info mb-2"></i>
                    <h6 class="text-info mb-1">Tanpa Registrasi</h6>
                    <p class="text-muted small mb-0">Acara gratis, langsung hadir saja.</p>
                </div>
            </div>
            <?php endif; ?>

            <!-- Registration / Ticket Purchase -->
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <?php if ($regType === 'ticket'): ?>
                            <i class="fas fa-ticket-alt me-1"></i>Beli Tiket
                        <?php elseif ($regType === 'rsvp'): ?>
                            <i class="fas fa-calendar-check me-1"></i>RSVP Sekarang
                        <?php elseif ($regType === 'open'): ?>
                            <i class="fas fa-door-open me-1"></i>Info Kehadiran
                        <?php else: ?>
                            <i class="fas fa-info-circle me-1"></i>Informasi
                        <?php endif; ?>
                    </h5>
                </div>
                <div class="card-body">
                    <?php if ($regType === 'ticket' && $isSoldOut): ?>
                    <div class="text-center py-4">
                        <i class="fas fa-times-circle fa-3x text-danger mb-3"></i>
                        <h5 class="text-danger">Tiket Habis</h5>
                        <p class="text-muted">Semua tiket untuk event ini sudah terjual habis.</p>
                    </div>

                    <?php elseif ($regType === 'rsvp' && $isSoldOut): ?>
                    <div class="text-center py-4">
                        <i class="fas fa-times-circle fa-3x text-danger mb-3"></i>
                        <h5 class="text-danger">Slot Penuh</h5>
                        <p class="text-muted">Maaf, semua slot RSVP sudah terisi.</p>
                    </div>

                    <?php elseif ($regType === 'ticket'): ?>
                    <!-- Ticket Tier Selection -->
                    <?php if (!empty($variants)): ?>
                    <div class="mb-3">
                        <label class="form-label fw-bold"><i class="fas fa-ticket-alt me-1 text-primary"></i>Pilih Jenis Tiket:</label>
                        <?php foreach ($variants as $idx => $variant): ?>
                        <?php $tierBadge = $variantModel->getTierBadge($variant); ?>
                        <label class="card mb-2 <?= $idx === 0 ? 'border-primary' : '' ?>" 
                               style="cursor: pointer; transition: all 0.2s;"
                               onclick="selectEventVariant(<?= $variant['id'] ?>, <?= $variant['price'] ?>, <?= $variant['stock'] ?>)">
                            <div class="card-body p-2">
                                <div class="d-flex justify-content-between align-items-start">
                                    <div>
                                        <div class="form-check">
                                            <input class="form-check-input" type="radio" name="event_variant_id" value="<?= $variant['id'] ?>" id="ev<?= $variant['id'] ?>" <?= $idx === 0 ? 'checked' : '' ?>>
                                            <label class="form-check-label fw-bold" for="ev<?= $variant['id'] ?>">
                                                <?= $tierBadge ?>
                                                <?= View::e($variant['name']) ?>
                                            </label>
                                        </div>
                                        <div class="ps-4 small text-muted mt-1"><?= View::e($variant['description'] ?? '') ?></div>
                                        <div class="ps-4 mt-1"><?= $variantModel->formatAttributes($variant) ?></div>
                                    </div>
                                    <div class="text-end">
                                        <span class="fw-bold <?= $variant['stock'] > 100 ? 'text-success' : ($variant['stock'] > 0 ? 'text-warning' : 'text-danger') ?>">
                                            <?= View::currency($variant['price']) ?>
                                        </span><br>
                                        <?php if ($variant['stock'] > 100): ?>
                                        <span class="badge bg-success" style="font-size:10px;">Tersedia</span>
                                        <?php elseif ($variant['stock'] > 0): ?>
                                        <span class="badge bg-warning text-dark" style="font-size:10px;">Tersisa <?= $variant['stock'] ?></span>
                                        <?php else: ?>
                                        <span class="badge bg-danger" style="font-size:10px;">Habis</span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </label>
                        <?php endforeach; ?>
                    </div>
                    <hr>
                    <?php endif; ?>
                    <form>
                        <div class="mb-3">
                            <label class="form-label">Jumlah Tiket</label>
                            <input type="number" class="form-control" id="eventQty" value="1" min="1" max="<?= min($remaining, 10) ?>" required>
                            <small class="text-muted">Maksimal <?= min($remaining, 10) ?> tiket per pesanan</small>
                        </div>
                        <div class="card bg-light mb-3">
                            <div class="card-body">
                                <div class="d-flex justify-content-between">
                                    <span>Harga per tiket:</span>
                                    <span id="eventUnitPrice"><?= View::currency($event['price'] ?? 0) ?></span>
                                </div>
                                <hr>
                                <div class="d-flex justify-content-between">
                                    <strong>Total:</strong>
                                    <strong id="eventTotal"><?= View::currency($event['price'] ?? 0) ?></strong>
                                </div>
                            </div>
                        </div>
                        <button type="submit" class="btn btn-primary w-100">Beli Tiket</button>
                    </form>

                    <?php elseif ($regType === 'rsvp'): ?>
                    <!-- RSVP Form (Free) -->
                    <div class="alert alert-success py-2 mb-3">
                        <i class="fas fa-gift me-1"></i><strong>Event GRATIS!</strong> Cukup RSVP untuk mengamankan slot Anda.
                    </div>
                    <form>
                        <div class="mb-3">
                            <label class="form-label">Jumlah Peserta</label>
                            <input type="number" class="form-control" value="1" min="1" max="<?= min($remaining, 5) ?>" required>
                            <small class="text-muted">Maksimal <?= min($remaining, 5) ?> peserta per RSVP</small>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Nama (sesuai KTP)</label>
                            <input type="text" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">No. Telepon</label>
                            <input type="tel" class="form-control" required>
                        </div>
                        <button type="submit" class="btn btn-success w-100">
                            <i class="fas fa-calendar-check me-1"></i>RSVP Sekarang
                        </button>
                    </form>

                    <?php elseif ($regType === 'open'): ?>
                    <!-- Open Event - No registration needed -->
                    <div class="text-center py-3">
                        <i class="fas fa-door-open fa-3x text-success mb-3"></i>
                        <h5 class="text-success">Gratis & Terbuka Umum</h5>
                        <p class="text-muted">Tidak perlu tiket atau registrasi.<br>Langsung datang ke lokasi!</p>
                        <hr>
                        <div class="text-start">
                            <p class="mb-1"><i class="fas fa-calendar me-2 text-primary"></i><?= View::date($event['start_date']) ?></p>
                            <p class="mb-1"><i class="fas fa-clock me-2 text-primary"></i><?= date('H:i', strtotime($event['start_date'])) ?> - <?= date('H:i', strtotime($event['end_date'])) ?> WIB</p>
                            <p class="mb-1"><i class="fas fa-map-marker-alt me-2 text-primary"></i><?= View::e($event['location_name'] ?? '') ?></p>
                        </div>
                        <?php if (Session::get('user_id')): ?>
                        <button class="btn btn-outline-success w-100 mt-3" onclick="markInterest(<?= $event['id'] ?>)">
                            <i class="fas fa-heart me-1"></i>Saya Tertarik Hadir
                        </button>
                        <?php endif; ?>
                    </div>

                    <?php elseif ($regType === 'none'): ?>
                    <!-- No registration at all -->
                    <div class="text-center py-3">
                        <i class="fas fa-bullhorn fa-3x text-info mb-3"></i>
                        <h5 class="text-info">Acara Publik Gratis</h5>
                        <p class="text-muted">Tidak perlu apa-apa, langsung nikmati!</p>
                        <hr>
                        <div class="text-start">
                            <p class="mb-1"><i class="fas fa-calendar me-2 text-primary"></i><?= View::date($event['start_date']) ?></p>
                            <p class="mb-1"><i class="fas fa-clock me-2 text-primary"></i><?= date('H:i', strtotime($event['start_date'])) ?> - <?= date('H:i', strtotime($event['end_date'])) ?> WIB</p>
                            <p class="mb-1"><i class="fas fa-map-marker-alt me-2 text-primary"></i><?= View::e($event['location_name'] ?? '') ?></p>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function selectEventVariant(id, price, stock) {
    document.querySelectorAll('input[name="event_variant_id"]').forEach(function(r) { r.checked = false; });
    var radio = document.getElementById('ev' + id);
    if (radio) radio.checked = true;

    document.querySelectorAll('.card.border-primary').forEach(function(c) { c.classList.remove('border-primary'); });
    event.currentTarget.classList.add('border-primary');

    var qtyInput = document.getElementById('eventQty');
    if (qtyInput) {
        qtyInput.max = Math.min(stock, 10);
        if (parseInt(qtyInput.value) > stock) qtyInput.value = stock;
        updateEventTotal(price, parseInt(qtyInput.value));
    }
}

if (document.getElementById('eventQty')) {
    document.getElementById('eventQty').addEventListener('input', function() {
        var checked = document.querySelector('input[name="event_variant_id"]:checked');
        var price = checked ? parseFloat(checked.closest('.card-body').querySelector('.fw-bold').textContent.replace(/[^\d]/g, '')) : <?= $event['price'] ?? 0 ?>;
        updateEventTotal(price, parseInt(this.value) || 1);
    });
}

function updateEventTotal(price, qty) {
    var total = price * qty;
    var totalEl = document.getElementById('eventTotal');
    var unitEl = document.getElementById('eventUnitPrice');
    if (totalEl) totalEl.textContent = 'Rp ' + total.toLocaleString('id-ID');
    if (unitEl) unitEl.textContent = 'Rp ' + price.toLocaleString('id-ID');
}
</script>

<?php include APP_ROOT . '/app/views/layouts/footer.php'; ?>
