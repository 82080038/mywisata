<?php include APP_ROOT . '/app/views/layouts/header.php'; ?>

<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="fw-bold mb-0"><i class="fas fa-tachometer-alt me-2"></i>Dashboard</h2>
        <span class="text-muted">Selamat datang, <?= View::e(Session::get('user_name', 'User')) ?></span>
    </div>

    <?php if (Session::hasFlash('success')): ?>
        <div class="alert alert-success alert-dismissible fade show">
            <?= View::e(Session::getFlash('success')) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>
    <?php if (Session::hasFlash('error')): ?>
        <div class="alert alert-danger alert-dismissible fade show">
            <?= View::e(Session::getFlash('error')) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <?php if (!empty($pendingPayments)): ?>
        <div class="alert alert-warning">
            <i class="fas fa-exclamation-triangle me-2"></i>
            Anda memiliki <?= count($pendingPayments) ?> pembayaran tertunda.
            <a href="<?= View::url('payments') ?>" class="alert-link">Lihat Pembayaran</a>
        </div>
    <?php endif; ?>

    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="card text-white bg-primary h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-uppercase mb-1">Booking</h6>
                            <h2 class="mb-0"><?= $totalBookings ?></h2>
                        </div>
                        <i class="fas fa-calendar-check fa-2x opacity-50"></i>
                    </div>
                </div>
                <div class="card-footer bg-transparent border-0 pb-3">
                    <a href="<?= View::url('bookings') ?>" class="text-white text-decoration-none small">Lihat semua →</a>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-white bg-success h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-uppercase mb-1">Tiket</h6>
                            <h2 class="mb-0"><?= $totalTickets ?></h2>
                        </div>
                        <i class="fas fa-ticket-alt fa-2x opacity-50"></i>
                    </div>
                </div>
                <div class="card-footer bg-transparent border-0 pb-3">
                    <a href="<?= View::url('tickets') ?>" class="text-white text-decoration-none small">Lihat semua →</a>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-white bg-danger h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-uppercase mb-1">Favorit</h6>
                            <h2 class="mb-0"><?= $totalFavorites ?></h2>
                        </div>
                        <i class="fas fa-heart fa-2x opacity-50"></i>
                    </div>
                </div>
                <div class="card-footer bg-transparent border-0 pb-3">
                    <a href="<?= View::url('favorites') ?>" class="text-white text-decoration-none small">Lihat semua →</a>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-white bg-warning h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-uppercase mb-1">Pembayaran Tertunda</h6>
                            <h2 class="mb-0"><?= count($pendingPayments) ?></h2>
                        </div>
                        <i class="fas fa-clock fa-2x opacity-50"></i>
                    </div>
                </div>
                <div class="card-footer bg-transparent border-0 pb-3">
                    <a href="<?= View::url('payments') ?>" class="text-white text-decoration-none small">Lihat semua →</a>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <div class="col-md-6">
            <div class="card shadow-sm">
                <div class="card-header bg-white">
                    <h5 class="mb-0"><i class="fas fa-calendar-check text-primary me-2"></i>Booking Terbaru</h5>
                </div>
                <div class="card-body">
                    <?php if (empty($recentBookings)): ?>
                        <p class="text-muted text-center py-3">Belum ada booking</p>
                    <?php else: ?>
                        <div class="list-group list-group-flush">
                            <?php foreach ($recentBookings as $booking): ?>
                                <div class="list-group-item d-flex justify-content-between align-items-center">
                                    <div>
                                        <strong><?= View::e($booking['booking_code'] ?? '') ?></strong>
                                        <br><small class="text-muted"><?= View::date($booking['booking_date'] ?? '') ?></small>
                                    </div>
                                    <span class="badge bg-<?= $booking['status'] === 'confirmed' ? 'success' : ($booking['status'] === 'pending' ? 'warning' : 'danger') ?>">
                                        <?= View::e($booking['status'] ?? '') ?>
                                    </span>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card shadow-sm">
                <div class="card-header bg-white">
                    <h5 class="mb-0"><i class="fas fa-ticket-alt text-success me-2"></i>Tiket Terbaru</h5>
                </div>
                <div class="card-body">
                    <?php if (empty($recentTickets)): ?>
                        <p class="text-muted text-center py-3">Belum ada tiket</p>
                    <?php else: ?>
                        <div class="list-group list-group-flush">
                            <?php foreach ($recentTickets as $ticket): ?>
                                <div class="list-group-item d-flex justify-content-between align-items-center">
                                    <div>
                                        <strong><?= View::e($ticket['destination_name'] ?? $ticket['order_code'] ?? '') ?></strong>
                                        <br><small class="text-muted"><?= View::date($ticket['visit_date'] ?? '') ?> · <?= $ticket['quantity'] ?? 1 ?> tiket</small>
                                    </div>
                                    <span class="badge bg-<?= $ticket['status'] === 'paid' ? 'success' : ($ticket['status'] === 'pending' ? 'warning' : 'secondary') ?>">
                                        <?= View::e($ticket['status'] ?? '') ?>
                                    </span>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4 mt-1">
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-header bg-white">
                    <h5 class="mb-0"><i class="fas fa-heart text-danger me-2"></i>Favorit Terbaru</h5>
                </div>
                <div class="card-body">
                    <?php if (empty($favorites)): ?>
                        <p class="text-muted text-center py-3">Belum ada favorit</p>
                    <?php else: ?>
                        <div class="row g-3">
                            <?php foreach ($favorites as $fav): ?>
                                <div class="col-md-3">
                                    <div class="card h-100 border-0 shadow-sm">
                                        <div class="card-body text-center">
                                            <i class="fas fa-map-marked-alt fa-2x text-primary mb-2"></i>
                                            <h6 class="card-title"><?= View::e($fav['item_name'] ?? 'Item') ?></h6>
                                            <span class="badge bg-info text-dark"><?= View::e($fav['item_type'] ?? '') ?></span>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Food Preferences Quick Access -->
    <div class="row g-4 mt-1">
        <div class="col-12">
            <div class="card shadow-sm border-warning">
                <div class="card-body d-flex align-items-center justify-content-between">
                    <div>
                        <h5 class="mb-1"><i class="fas fa-utensils text-warning me-2"></i>Preferensi & Alergi Makanan</h5>
                        <p class="text-muted small mb-0">Atur alergi makanan dan preferensi diet Anda untuk pengalaman wisata kuliner yang aman</p>
                    </div>
                    <a href="<?= View::url('dashboard/foodPreferences') ?>" class="btn btn-warning btn-lg">
                        <i class="fas fa-cog me-1"></i>Atur Sekarang
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include APP_ROOT . '/app/views/layouts/footer.php'; ?>
