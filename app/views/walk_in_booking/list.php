<?php include APP_ROOT . '/app/views/layouts/header.php'; ?>

<div class="container py-4">
    <div class="row mb-4">
        <div class="col-md-12">
            <h1 class="mb-3"><i class="fas fa-list me-2"></i>Daftar Walk-in Booking</h1>
            <p class="text-muted">Kelola semua booking walk-in</p>
        </div>
    </div>
    
    <!-- Filter -->
    <div class="card mb-4 glass-card">
        <div class="card-body">
            <form method="GET" action="<?= View::url('walk-in-booking/list') ?>">
                <div class="row">
                    <div class="col-md-3 mb-3">
                        <div class="form-floating">
                            <input type="date" class="form-control" name="date" id="date" value="<?= View::e($filters['date'] ?? '') ?>">
                            <label for="date">Tanggal</label>
                        </div>
                    </div>
                    <div class="col-md-3 mb-3">
                        <div class="form-floating">
                            <select class="form-select" name="status" id="status">
                                <option value="">Semua Status</option>
                                <option value="pending">Pending</option>
                                <option value="confirmed">Confirmed</option>
                                <option value="completed">Completed</option>
                                <option value="cancelled">Cancelled</option>
                            </select>
                            <label for="status">Status</label>
                        </div>
                    </div>
                    <div class="col-md-3 mb-3">
                        <div class="form-floating">
                            <input type="text" class="form-control" name="search" id="search" placeholder="Cari..." value="<?= View::e($filters['search'] ?? '') ?>">
                            <label for="search">Cari...</label>
                        </div>
                    </div>
                    <div class="col-md-3 mb-3">
                        <button type="submit" class="btn btn-primary btn-modern w-100 h-100">
                            <i class="fas fa-search"></i>
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
    
    <!-- Bookings List -->
    <div class="card glass-card">
        <div class="card-body">
            <?php if (empty($bookings)): ?>
                <div class="text-center py-5">
                    <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                    <p class="text-muted">Tidak ada booking ditemukan</p>
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>Kode</th>
                                <th>Customer</th>
                                <th>Tipe</th>
                                <th>Tanggal</th>
                                <th>Waktu</th>
                                <th>Jumlah</th>
                                <th>Total</th>
                                <th>Status</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($bookings as $booking): ?>
                            <tr>
                                <td><strong><?= View::e($booking['booking_code']) ?></strong></td>
                                <td>
                                    <div><?= View::e($booking['customer_name']) ?></div>
                                    <small class="text-muted"><?= View::e($booking['customer_phone']) ?></small>
                                </td>
                                <td><?= View::e($booking['booking_type']) ?></td>
                                <td><?= date('d-m-Y', strtotime($booking['booking_date'])) ?></td>
                                <td><?= date('H:i', strtotime($booking['booking_time'])) ?></td>
                                <td><?= $booking['number_of_people'] ?></td>
                                <td><?= $booking['display_price'] ?></td>
                                <td>
                                    <?php
                                    $statusColors = [
                                        'pending' => 'warning',
                                        'confirmed' => 'success',
                                        'completed' => 'primary',
                                        'cancelled' => 'danger'
                                    ];
                                    ?>
                                    <span class="badge bg-<?= $statusColors[$booking['status']] ?? 'secondary' ?>">
                                        <?= ucfirst($booking['status']) ?>
                                    </span>
                                </td>
                                <td>
                                    <div class="btn-group">
                                        <button onclick="updateStatus(<?= $booking['id'] ?>, 'confirmed')" class="btn btn-sm btn-success">
                                            <i class="fas fa-check"></i>
                                        </button>
                                        <button onclick="updateStatus(<?= $booking['id'] ?>, 'completed')" class="btn btn-sm btn-primary">
                                            <i class="fas fa-check-double"></i>
                                        </button>
                                        <button onclick="updateStatus(<?= $booking['id'] ?>, 'cancelled')" class="btn btn-sm btn-danger">
                                            <i class="fas fa-times"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
function updateStatus(bookingId, status) {
    if (confirm('Ubah status ke ' + status + '?')) {
        const formData = new FormData();
        formData.append('booking_id', bookingId);
        formData.append('status', status);
        formData.append('csrf_token', '<?= $csrf_token ?>');
        
        fetch('<?= View::url('walk-in-booking/update-status') ?>', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(result => {
            if (result.status === 'success') {
                location.reload();
            } else {
                alert('Gagal update status: ' + result.message);
            }
        })
        .catch(error => {
            alert('Terjadi kesalahan: ' + error.message);
        });
    }
}
</script>

<?php include APP_ROOT . '/app/views/layouts/footer.php'; ?>
