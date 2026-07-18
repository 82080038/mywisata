<?php include APP_ROOT . '/app/views/layouts/header.php'; ?>

<div class="container py-4">
    <div class="row mb-4">
        <div class="col-md-6">
            <h1 class="mb-3">Event & Budaya</h1>
            <p class="text-muted">Temukan event budaya dan festival menarik</p>
        </div>
        <div class="col-md-6 text-end">
            <a href="<?= View::url('events/calendar') ?>" class="btn btn-outline-primary">
                <i class="fas fa-calendar-alt me-1"></i>Lihat Kalender
            </a>
        </div>
    </div>
    
    <!-- Search & Filter -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" action="<?= View::url('events') ?>">
                <div class="row">
                    <div class="col-md-4 mb-3">
                        <input type="text" class="form-control" name="search" placeholder="Cari event..." value="<?= View::e($filters['search'] ?? '') ?>">
                    </div>
                    <div class="col-md-3 mb-3">
                        <select class="form-select" name="registration_type">
                            <option value="">Semua Jenis</option>
                            <option value="ticket" <?= ($filters['registration_type'] ?? '') === 'ticket' ? 'selected' : '' ?>>Berbayar (Tiket)</option>
                            <option value="rsvp" <?= ($filters['registration_type'] ?? '') === 'rsvp' ? 'selected' : '' ?>>Gratis (RSVP)</option>
                            <option value="open" <?= ($filters['registration_type'] ?? '') === 'open' ? 'selected' : '' ?>>Gratis (Terbuka)</option>
                            <option value="none" <?= ($filters['registration_type'] ?? '') === 'none' ? 'selected' : '' ?>>Gratis (Tanpa Registrasi)</option>
                        </select>
                    </div>
                    <div class="col-md-3 mb-3">
                        <input type="text" class="form-control" name="city" placeholder="Lokasi..." value="<?= View::e($filters['city'] ?? '') ?>">
                    </div>
                    <div class="col-md-2 mb-3">
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="fas fa-search me-2"></i>Cari
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
    
    <!-- Upcoming Events -->
    <?php if (!empty($upcoming)): ?>
    <div class="row mb-4">
        <div class="col-md-12">
            <h3 class="mb-3">Event Mendatang</h3>
            <div class="row">
                <?php foreach ($upcoming as $event): ?>
                <div class="col-md-4 mb-4">
                    <div class="card h-100">
                        <?php if (!empty($event['main_image'])): ?>
                            <img src="<?= View::asset('uploads/events/' . $event['main_image']) ?>" class="card-img-top" alt="<?= View::e($event['title']) ?>" style="height: 200px; object-fit: cover;">
                        <?php else: ?>
                            <img src="http://localhost/mywisata/public/assets/img/placeholder.png" class="card-img-top" alt="<?= View::e($event['title']) ?>" style="height: 200px; object-fit: cover;">
                        <?php endif; ?>
                        <div class="card-body">
                            <h5 class="card-title"><?= View::e($event['title']) ?></h5>
                            <p class="card-text text-muted small">
                                <i class="fas fa-calendar me-1"></i><?= View::date($event['start_date']) ?>
                            </p>
                            <p class="card-text text-muted small">
                                <i class="fas fa-map-marker-alt me-1"></i><?= View::e($event['location_name'] ?? '') ?>
                            </p>
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <div>
                                    <i class="fas fa-star text-warning"></i> <?= number_format($event['rating_avg'] ?? 0, 1) ?>
                                </div>
                                <span class="badge bg-primary"><?= View::e($event['category']) ?></span>
                            </div>
                            <div class="mb-2">
                                <?php if (!empty($event['requires_ticket']) && ($event['price'] ?? 0) > 0): ?>
                                <span class="badge bg-success"><i class="fas fa-ticket-alt me-1"></i><?= View::currency($event['price']) ?></span>
                                <?php else: ?>
                                <span class="badge bg-success"><i class="fas fa-gift me-1"></i>GRATIS</span>
                                <?php endif; ?>
                                <?php
                                $regLabels = ['ticket' => 'Tiket', 'rsvp' => 'RSVP', 'open' => 'Terbuka', 'none' => 'Tanpa Registrasi'];
                                $regColors = ['ticket' => 'info', 'rsvp' => 'warning', 'open' => 'success', 'none' => 'secondary'];
                                $rt = $event['registration_type'] ?? 'ticket';
                                ?>
                                <span class="badge bg-<?= $regColors[$rt] ?? 'info' ?> <?= $rt === 'rsvp' ? 'text-dark' : '' ?>"><?= $regLabels[$rt] ?? 'Tiket' ?></span>
                            </div>
                            <a href="<?= View::url('events/detail/' . $event['id']) ?>" class="btn btn-primary mt-1 w-100">
                                Lihat Detail
                            </a>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
    <?php endif; ?>
    
    <!-- All Events -->
    <div class="row">
        <div class="col-md-12">
            <h3 class="mb-3">Semua Event</h3>
            <?php if (empty($events)): ?>
                <p class="text-muted">Tidak ada event ditemukan.</p>
            <?php else: ?>
                <div class="row">
                    <?php foreach ($events as $event): ?>
                    <div class="col-md-4 mb-4">
                        <div class="card h-100">
                            <?php if (!empty($event['main_image'])): ?>
                                <img src="<?= View::asset('uploads/events/' . $event['main_image']) ?>" class="card-img-top" alt="<?= View::e($event['title']) ?>" style="height: 200px; object-fit: cover;">
                            <?php else: ?>
                                <img src="http://localhost/mywisata/public/assets/img/placeholder.png" class="card-img-top" alt="<?= View::e($event['title']) ?>" style="height: 200px; object-fit: cover;">
                            <?php endif; ?>
                            <div class="card-body">
                                <h5 class="card-title"><?= View::e($event['title']) ?></h5>
                                <p class="card-text text-muted small">
                                    <i class="fas fa-calendar me-1"></i><?= View::date($event['start_date']) ?>
                                </p>
                                <p class="card-text text-muted small">
                                    <i class="fas fa-map-marker-alt me-1"></i><?= View::e($event['location_name'] ?? '') ?>
                                </p>
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <div>
                                        <i class="fas fa-star text-warning"></i> <?= number_format($event['rating_avg'] ?? 0, 1) ?>
                                    </div>
                                    <span class="badge bg-secondary"><?= View::e($event['category']) ?></span>
                                </div>
                                <div class="mb-2">
                                    <?php if (!empty($event['requires_ticket']) && ($event['price'] ?? 0) > 0): ?>
                                    <span class="badge bg-success"><i class="fas fa-ticket-alt me-1"></i><?= View::currency($event['price']) ?></span>
                                    <?php else: ?>
                                    <span class="badge bg-success"><i class="fas fa-gift me-1"></i>GRATIS</span>
                                    <?php endif; ?>
                                    <?php
                                    $regLabels = ['ticket' => 'Tiket', 'rsvp' => 'RSVP', 'open' => 'Terbuka', 'none' => 'Tanpa Registrasi'];
                                    $regColors = ['ticket' => 'info', 'rsvp' => 'warning', 'open' => 'success', 'none' => 'secondary'];
                                    $rt = $event['registration_type'] ?? 'ticket';
                                    ?>
                                    <span class="badge bg-<?= $regColors[$rt] ?? 'info' ?> <?= $rt === 'rsvp' ? 'text-dark' : '' ?>"><?= $regLabels[$rt] ?? 'Tiket' ?></span>
                                </div>
                                <a href="<?= View::url('events/detail/' . $event['id']) ?>" class="btn btn-primary mt-1 w-100">
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
