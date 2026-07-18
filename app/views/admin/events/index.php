<?php include APP_ROOT . '/app/views/layouts/admin_header.php'; ?>

<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Manajemen Event <span class="badge bg-secondary"><?= $total ?></span></h2>
    </div>

    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Judul</th>
                            <th>Kota</th>
                            <th>Tanggal</th>
                            <th>Status</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($events as $event): ?>
                            <tr>
                                <td><?= $event['id'] ?></td>
                                <td><?= View::e($event['title']) ?></td>
                                <td><?= View::e($event['city'] ?? '') ?></td>
                                <td><?= View::date($event['event_date'] ?? $event['start_date'] ?? '') ?></td>
                                <td>
                                    <?php if ($event['is_active']): ?>
                                        <span class="badge bg-success">Aktif</span>
                                    <?php else: ?>
                                        <span class="badge bg-warning text-dark">Pending</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if (!$event['is_active']): ?>
                                        <button class="btn btn-sm btn-success approve-btn" data-id="<?= $event['id'] ?>">
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
        title: 'Konfirmasi', text: 'Setujui event ini?', icon: 'question',
        showCancelButton: true, confirmButtonText: 'Ya, Setujui'
    }).then(function(result) {
        if (result.isConfirmed) {
            $.post(window.APP_URL + 'admin/approveEvent', { id: id }, function(data) {
                Swal.fire({ icon: 'success', title: 'Berhasil', text: data.message, timer: 1500 }).then(() => location.reload());
            });
        }
    });
});
</script>

<?php include APP_ROOT . '/app/views/layouts/admin_footer.php'; ?>
