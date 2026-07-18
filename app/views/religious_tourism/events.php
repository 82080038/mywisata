<?php include APP_ROOT . '/app/views/layouts/header.php'; ?>

<div class="container py-4">
    <div class="row mb-4">
        <div class="col-md-12">
            <h1 class="mb-3"><i class="fas fa-calendar-alt me-2"></i>Event Religi</h1>
            <p class="text-muted">Event keagamaan dan ziarah yang akan datang</p>
        </div>
    </div>
    
    <!-- Search & Filter -->
    <div class="card mb-4 glass-card">
        <div class="card-body">
            <form method="GET" action="<?= View::url('religious-tourism/events') ?>">
                <div class="row">
                    <div class="col-md-4 mb-3">
                        <div class="form-floating">
                            <input type="text" class="form-control" name="search" id="search" placeholder="Cari event..." value="<?= View::e($filters['search'] ?? '') ?>">
                            <label for="search">Cari event...</label>
                        </div>
                    </div>
                    <div class="col-md-3 mb-3">
                        <div class="form-floating">
                            <select class="form-select" name="event_type" id="event_type">
                                <option value="">Semua Tipe</option>
                                <option value="pilgrimage">Ziarah</option>
                                <option value="festival">Festival</option>
                                <option value="seminar">Seminar</option>
                                <option value="ceremony">Upacara</option>
                            </select>
                            <label for="event_type">Tipe Event</label>
                        </div>
                    </div>
                    <div class="col-md-3 mb-3">
                        <div class="form-floating">
                            <input type="month" class="form-control" name="month" id="month" value="<?= View::e($filters['month'] ?? '') ?>">
                            <label for="month">Bulan</label>
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
    
    <!-- Events List -->
    <div class="row">
        <div class="col-md-12">
            <h3 class="mb-3 section-title"><i class="fas fa-calendar-week me-2"></i>Event yang Akan Datang</h3>
            <?php if (empty($events)): ?>
                <div class="empty-state text-center py-5">
                    <i class="fas fa-calendar-times fa-3x text-muted mb-3"></i>
                    <h5 class="text-muted">Tidak ada event ditemukan</h5>
                    <p class="text-muted">Coba ubah filter atau kata kunci pencarian</p>
                    <a href="<?= View::url('religious-tourism/events') ?>" class="btn btn-primary mt-2">
                        <i class="fas fa-redo me-2"></i>Reset Filter
                    </a>
                </div>
            <?php else: ?>
                <div class="row">
                    <?php foreach ($events as $event): ?>
                    <div class="col-md-4 mb-4">
                        <div class="card h-100 hover-shadow">
                            <?php if (!empty($event['image_url'])): ?>
                                <?php if (filter_var($event['image_url'], FILTER_VALIDATE_URL)): ?>
                                    <img src="<?= View::e($event['image_url']) ?>" class="card-img-top" alt="<?= View::e($event['event_name']) ?>" style="height: 200px; object-fit: cover;">
                                <?php else: ?>
                                    <img src="<?= View::asset('uploads/religious_tourism/' . $event['image_url']) ?>" class="card-img-top" alt="<?= View::e($event['event_name']) ?>" style="height: 200px; object-fit: cover;">
                                <?php endif; ?>
                            <?php else: ?>
                                <div class="card-img-top bg-warning d-flex align-items-center justify-content-center" style="height: 200px;">
                                    <i class="fas fa-calendar-alt text-white" style="font-size: 3rem;"></i>
                                </div>
                            <?php endif; ?>
                            <div class="card-body">
                                <h5 class="card-title"><?= View::e($event['event_name']) ?></h5>
                                <p class="card-text text-muted small">
                                    <i class="fas fa-calendar me-1"></i><?= date('d-m-Y', strtotime($event['event_date'])) ?>
                                </p>
                                <p class="card-text text-muted small">
                                    <i class="fas fa-clock me-1"></i><?= $event['event_time'] ?>
                                </p>
                                <p class="card-text text-muted small">
                                    <i class="fas fa-map-marker-alt me-1"></i><?= View::e($event['location']) ?>
                                </p>
                                <p class="card-text small"><?= View::e(substr($event['description'], 0, 80)) ?>...</p>
                                <div class="d-flex justify-content-between align-items-center mt-2">
                                    <div class="text-primary fw-bold">
                                        <?= $event['display_fee'] ?>
                                    </div>
                                    <span class="badge bg-secondary"><?= ucfirst($event['event_type']) ?></span>
                                </div>
                                <a href="<?= View::url('religious-tourism/event?id=' . $event['id']) ?>" class="btn btn-primary btn-modern mt-3 w-100">
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
