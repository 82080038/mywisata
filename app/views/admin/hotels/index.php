<?php include APP_ROOT . '/app/views/layouts/admin_header.php'; ?>

<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Manajemen Hotel <span class="badge bg-secondary"><?= $total ?></span></h2>
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
                        <?php foreach ($hotels as $hotel): ?>
                            <tr>
                                <td><?= $hotel['id'] ?></td>
                                <td><?= View::e($hotel['name']) ?></td>
                                <td><?= View::e($hotel['city'] ?? '') ?></td>
                                <td><?= number_format($hotel['rating_avg'] ?? 0, 1) ?></td>
                                <td>
                                    <?php if ($hotel['is_approved']): ?>
                                        <span class="badge bg-success">Disetujui</span>
                                    <?php else: ?>
                                        <span class="badge bg-warning text-dark">Pending</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if (!$hotel['is_approved']): ?>
                                        <button class="btn btn-sm btn-success approve-btn" data-id="<?= $hotel['id'] ?>" data-type="hotel">
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
    var type = $(this).data('type');
    Swal.fire({
        title: 'Konfirmasi',
        text: 'Setujui ' + type + ' ini?',
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Ya, Setujui'
    }).then(function(result) {
        if (result.isConfirmed) {
            $.post(window.APP_URL + 'admin/approveHotel', { id: id, csrf_token: $('meta[name="csrf-token"]').attr('content') || '' }, function(data) {
                Swal.fire({ icon: 'success', title: 'Berhasil', text: data.message, timer: 1500 }).then(() => location.reload());
            });
        }
    });
});
</script>

<?php include APP_ROOT . '/app/views/layouts/admin_footer.php'; ?>
