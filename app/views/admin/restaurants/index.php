<?php include APP_ROOT . '/app/views/layouts/admin_header.php'; ?>

<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Manajemen Restoran <span class="badge bg-secondary"><?= $total ?></span></h2>
    </div>

    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Nama</th>
                            <th>Kota</th>
                            <th>Rating</th>
                            <th>Status</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($restaurants as $restaurant): ?>
                            <tr>
                                <td><?= $restaurant['id'] ?></td>
                                <td><?= View::e($restaurant['name']) ?></td>
                                <td><?= View::e($restaurant['city'] ?? '') ?></td>
                                <td><?= number_format($restaurant['rating_avg'] ?? 0, 1) ?></td>
                                <td>
                                    <?php if ($restaurant['is_approved']): ?>
                                        <span class="badge bg-success">Disetujui</span>
                                    <?php else: ?>
                                        <span class="badge bg-warning text-dark">Pending</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if (!$restaurant['is_approved']): ?>
                                        <button class="btn btn-sm btn-success approve-btn" data-id="<?= $restaurant['id'] ?>">
                                            <i class="fas fa-check"></i> Setujui
                                        </button>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script>
$('.approve-btn').on('click', function() {
    var id = $(this).data('id');
    Swal.fire({
        title: 'Konfirmasi', text: 'Setujui restoran ini?', icon: 'question',
        showCancelButton: true, confirmButtonText: 'Ya, Setujui'
    }).then(function(result) {
        if (result.isConfirmed) {
            $.post(window.APP_URL + 'admin/approveRestaurant', { id: id }, function(data) {
                Swal.fire({ icon: 'success', title: 'Berhasil', text: data.message, timer: 1500 }).then(() => location.reload());
            });
        }
    });
});
</script>

<?php include APP_ROOT . '/app/views/layouts/admin_footer.php'; ?>
