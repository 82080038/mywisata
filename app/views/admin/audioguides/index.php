<?php include APP_ROOT . '/app/views/layouts/admin_header.php'; ?>

<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Manajemen Audio Guide</h2>
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addModal">
            <i class="fas fa-plus me-2"></i>Tambah Audio Guide
        </button>
    </div>

    <div class="card">
        <div class="card-body">
            <?php if (empty($audio_guides)): ?>
                <p class="text-muted text-center py-4">Belum ada audio guide.</p>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Judul</th>
                                <th>Destinasi</th>
                                <th>Bahasa</th>
                                <th>Durasi</th>
                                <th>Status</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($audio_guides as $audio): ?>
                            <tr>
                                <td><?= $audio['id'] ?></td>
                                <td><?= View::e($audio['title']) ?></td>
                                <td><?= View::e($audio['destination_name'] ?? '-') ?></td>
                                <td><?= View::e($audio['language'] ?? 'Indonesia') ?></td>
                                <td><?= ($audio['duration'] ?? 0) ?> menit</td>
                                <td>
                                    <?php if ($audio['is_active']): ?>
                                        <span class="badge bg-success">Aktif</span>
                                    <?php else: ?>
                                        <span class="badge bg-secondary">Nonaktif</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <a href="<?= View::url('audioguide/play?id=' . $audio['id']) ?>" class="btn btn-sm btn-outline-primary">
                                        <i class="fas fa-play"></i>
                                    </a>
                                    <button class="btn btn-sm btn-outline-danger" onclick="deleteAudioGuide(<?= $audio['id'] ?>)">
                                        <i class="fas fa-trash"></i>
                                    </button>
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

<!-- Add Modal -->
<div class="modal fade" id="addModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Tambah Audio Guide</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="addForm" enctype="multipart/form-data">
                    <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">
                    <div class="mb-3">
                        <label class="form-label">Destinasi *</label>
                        <select name="destination_id" class="form-select" required>
                            <option value="">Pilih Destinasi</option>
                            <?php foreach ($destinations as $dest): ?>
                            <option value="<?= $dest['id'] ?>"><?= View::e($dest['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Judul *</label>
                        <input type="text" name="title" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Bahasa</label>
                        <input type="text" name="language" class="form-control" value="Indonesia">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Durasi (menit)</label>
                        <input type="number" name="duration" class="form-control" value="0" min="0">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Deskripsi</label>
                        <textarea name="description" class="form-control" rows="3"></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">File Audio (MP3/WAV/OGG, max 50MB)</label>
                        <input type="file" name="audio_file" class="form-control" accept="audio/*">
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                <button type="button" class="btn btn-primary" onclick="submitAdd()">Simpan</button>
            </div>
        </div>
    </div>
</div>

<script>
function submitAdd() {
    var formData = new FormData(document.getElementById('addForm'));
    fetch(window.APP_URL + 'admin/createAudioGuide', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.status === 'success') {
            Swal.fire({ icon: 'success', title: 'Berhasil', text: data.message, timer: 1500, showConfirmButton: false })
                .then(() => location.reload());
        } else {
            Swal.fire({ icon: 'error', title: 'Error', text: data.message });
        }
    });
}

function deleteAudioGuide(id) {
    Swal.fire({
        title: 'Hapus Audio Guide?',
        text: 'Tindakan ini tidak dapat dibatalkan',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        confirmButtonText: 'Hapus'
    }).then((result) => {
        if (result.isConfirmed) {
            var formData = new FormData();
            formData.append('csrf_token', '<?= $csrf_token ?>');
            formData.append('id', id);
            fetch(window.APP_URL + 'admin/deleteAudioGuide', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.status === 'success') {
                    Swal.fire({ icon: 'success', title: 'Berhasil', text: data.message, timer: 1500, showConfirmButton: false })
                        .then(() => location.reload());
                }
            });
        }
    });
}
</script>

<?php include APP_ROOT . '/app/views/layouts/admin_footer.php'; ?>
