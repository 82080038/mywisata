<?php include APP_ROOT . '/app/views/layouts/header.php'; ?>

<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2><i class="fas fa-route me-2"></i><?= View::e($itinerary['title']) ?></h2>
        <a href="<?= View::url('itinerary') ?>" class="btn btn-outline-secondary btn-sm">
            <i class="fas fa-arrow-left me-1"></i>Kembali
        </a>
    </div>

    <?php if (Session::hasFlash('success')): ?>
    <div class="alert alert-success"><?= View::e(Session::getFlash('success')) ?></div>
    <?php endif; ?>

    <!-- Summary Cards -->
    <div class="row mb-4">
        <div class="col-md-3 mb-2">
            <div class="card text-center shadow-sm">
                <div class="card-body">
                    <i class="fas fa-calendar text-primary mb-1"></i>
                    <h6 class="mb-0"><?= date('d M', strtotime($itinerary['start_date'])) ?> - <?= date('d M Y', strtotime($itinerary['end_date'])) ?></h6>
                    <small class="text-muted">Tanggal</small>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-2">
            <div class="card text-center shadow-sm">
                <div class="card-body">
                    <i class="fas fa-clock text-info mb-1"></i>
                    <h6 class="mb-0"><?= $itinerary['num_days'] ?> Hari</h6>
                    <small class="text-muted">Durasi</small>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-2">
            <div class="card text-center shadow-sm">
                <div class="card-body">
                    <i class="fas fa-users text-success mb-1"></i>
                    <h6 class="mb-0"><?= $itinerary['num_travelers'] ?> Traveler</h6>
                    <small class="text-muted">Peserta</small>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-2">
            <div class="card text-center shadow-sm">
                <div class="card-body">
                    <i class="fas fa-wallet text-warning mb-1"></i>
                    <h6 class="mb-0">Rp <?= number_format($itinerary['total_estimated_cost'] ?? 0, 0, ',', '.') ?></h6>
                    <small class="text-muted">Estimasi Total</small>
                </div>
            </div>
        </div>
    </div>

    <?php if ($itinerary['description']): ?>
    <div class="alert alert-info">
        <i class="fas fa-info-circle me-1"></i><?= View::e($itinerary['description']) ?>
    </div>
    <?php endif; ?>

    <!-- Day-by-day Itinerary -->
    <?php foreach ($itinerary['items_by_day'] as $day => $items): ?>
    <div class="card shadow-sm mb-3">
        <div class="card-header bg-light d-flex justify-content-between align-items-center">
            <h5 class="mb-0"><i class="fas fa-day-<?= $day ?> me-2"></i>Hari ke-<?= $day ?></h5>
            <?php
            $dayCost = 0;
            foreach ($items as $item) { $dayCost += $item['estimated_cost']; }
            ?>
            <span class="badge bg-secondary">Rp <?= number_format($dayCost, 0, ',', '.') ?></span>
        </div>
        <div class="card-body p-0">
            <table class="table table-hover mb-0">
                <?php foreach ($items as $item): ?>
                <tr>
                    <td style="width:60px;" class="text-center align-middle">
                        <?php
                        $icons = ['destination' => 'fa-map-marked-alt text-primary', 'hotel' => 'fa-hotel text-info', 'restaurant' => 'fa-utensils text-warning', 'event' => 'fa-calendar-alt text-success', 'transport' => 'fa-car text-secondary', 'rest' => 'fa-coffee text-light', 'custom' => 'fa-star text-dark'];
                        ?>
                        <i class="fas <?= $icons[$item['item_type']] ?? 'fa-star' ?> fa-lg"></i>
                    </td>
                    <td class="align-middle">
                        <strong><?= View::e($item['item_name']) ?></strong>
                        <?php if ($item['start_time']): ?>
                        <br><small class="text-muted"><i class="fas fa-clock me-1"></i><?= $item['start_time'] ?><?= $item['end_time'] ? ' - ' . $item['end_time'] : '' ?></small>
                        <?php endif; ?>
                        <?php if ($item['location']): ?>
                        <br><small class="text-muted"><i class="fas fa-map-pin me-1"></i><?= View::e($item['location']) ?></small>
                        <?php endif; ?>
                        <?php if ($item['notes']): ?>
                        <br><small class="text-muted"><?= View::e($item['notes']) ?></small>
                        <?php endif; ?>
                    </td>
                    <td class="text-end align-middle">
                        <?php if ($item['estimated_cost'] > 0): ?>
                        <span class="text-muted">Rp <?= number_format($item['estimated_cost'], 0, ',', '.') ?></span>
                        <?php endif; ?>
                        <button class="btn btn-sm btn-outline-danger ms-2" 
                                data-item-id="<?= $item['id'] ?>"
                                data-itin-id="<?= $itinerary['id'] ?>"
                                onclick="removeItem(this)">
                            <i class="fas fa-times"></i>
                        </button>
                    </td>
                </tr>
                <?php endforeach; ?>
            </table>
        </div>
    </div>
    <?php endforeach; ?>

    <!-- Add Item Form -->
    <div class="card shadow-sm">
        <div class="card-header">
            <h6 class="mb-0"><i class="fas fa-plus me-1"></i>Tambah Item</h6>
        </div>
        <div class="card-body">
            <form id="addItemForm">
                <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">
                <input type="hidden" name="itinerary_id" value="<?= $itinerary['id'] ?>">
                <div class="row g-2">
                    <div class="col-md-2">
                        <label class="form-label small">Hari</label>
                        <select class="form-select form-select-sm" name="day_number" required>
                            <?php for ($d = 1; $d <= $itinerary['num_days']; $d++): ?>
                            <option value="<?= $d ?>">Hari <?= $d ?></option>
                            <?php endfor; ?>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label small">Tipe</label>
                        <select class="form-select form-select-sm" name="item_type" required>
                            <option value="destination">Destinasi</option>
                            <option value="hotel">Hotel</option>
                            <option value="restaurant">Restoran</option>
                            <option value="event">Event</option>
                            <option value="transport">Transport</option>
                            <option value="rest">Istirahat</option>
                            <option value="custom">Custom</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label small">Nama</label>
                        <input type="text" class="form-control form-control-sm" name="item_name" required placeholder="Nama aktivitas">
                    </div>
                    <div class="col-md-1">
                        <label class="form-label small">Mulai</label>
                        <input type="time" class="form-control form-control-sm" name="start_time">
                    </div>
                    <div class="col-md-1">
                        <label class="form-label small">Selesai</label>
                        <input type="time" class="form-control form-control-sm" name="end_time">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label small">Biaya</label>
                        <input type="number" class="form-control form-control-sm" name="estimated_cost" value="0" min="0">
                    </div>
                    <div class="col-md-1">
                        <label class="form-label small">&nbsp;</label>
                        <button type="submit" class="btn btn-sm btn-primary w-100"><i class="fas fa-plus"></i></button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
$('#addItemForm').on('submit', function(e) {
    e.preventDefault();
    ajax({
        url: APP_URL + 'itinerary/addItem',
        method: 'POST',
        data: $(this).serialize(),
        success: function(response) {
            if (response.status === 'success') {
                location.reload();
            } else {
                Swal.fire({ icon: 'error', title: 'Error', text: response.message || 'Gagal' });
            }
        }
    });
});

function removeItem(btn) {
    if (!confirm('Hapus item ini?')) return;
    ajax({
        url: APP_URL + 'itinerary/removeItem',
        method: 'POST',
        data: {
            item_id: btn.dataset.itemId,
            itinerary_id: btn.dataset.itinId,
            csrf_token: '<?= $csrf_token ?>'
        },
        success: function(response) {
            if (response.status === 'success') {
                location.reload();
            } else {
                alert(response.message || 'Error');
            }
        }
    });
}
</script>

<?php include APP_ROOT . '/app/views/layouts/footer.php'; ?>
