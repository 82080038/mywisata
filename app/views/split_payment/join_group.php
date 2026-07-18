<?php include APP_ROOT . '/app/views/layouts/header.php'; ?>

<div class="container py-4">
    <div class="row mb-4">
        <div class="col-md-12">
            <h1 class="mb-3"><i class="fas fa-users me-2"></i>Join Split Payment Group</h1>
            <p class="text-muted">Gabung ke grup pembayaran bersama</p>
        </div>
    </div>
    
    <!-- Join by Code -->
    <div class="row mb-4">
        <div class="col-md-6">
            <div class="card glass-card">
                <div class="card-body">
                    <h4 class="mb-3">Gabung dengan Kode Grup</h4>
                    <form id="join-by-code-form">
                        <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">
                        
                        <div class="mb-3">
                            <label class="form-label">Kode Grup</label>
                            <input type="text" class="form-control" name="group_code" required placeholder="Contoh: ABC123">
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Nama Anda</label>
                            <input type="text" class="form-control" name="participant_name" required>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Email</label>
                            <input type="email" class="form-control" name="participant_email" required>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">No. Telepon</label>
                            <input type="tel" class="form-control" name="participant_phone" required>
                        </div>
                        
                        <button type="submit" class="btn btn-primary btn-modern w-100">
                            <i class="fas fa-sign-in-alt me-2"></i>Gabung Grup
                        </button>
                    </form>
                </div>
            </div>
        </div>
        
        <div class="col-md-6">
            <div class="card glass-card">
                <div class="card-body">
                    <h4 class="mb-3">Cara Kerja Split Payment</h4>
                    <ol class="list-group list-group-numbered">
                        <li class="list-group-item">Masukkan kode grup yang diberikan oleh pembuat grup</li>
                        <li class="list-group-item">Isi data diri Anda</li>
                        <li class="list-group-item">Lihat rincian pembayaran Anda</li>
                        <li class="list-group-item">Lakukan pembayaran sesuai porsi Anda</li>
                        <li class="list-group-item">Pembayaran akan diverifikasi secara otomatis</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Your Groups -->
    <div class="row">
        <div class="col-md-12">
            <h3 class="mb-3 section-title"><i class="fas fa-layer-group me-2"></i>Grup Anda</h3>
            <div class="card glass-card">
                <div class="card-body">
                    <?php if (empty($yourGroups)): ?>
                        <div class="text-center py-5">
                            <i class="fas fa-users fa-3x text-muted mb-3"></i>
                            <p class="text-muted">Anda belum bergabung ke grup manapun</p>
                        </div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>Kode Grup</th>
                                        <th>Total</th>
                                        <th>Porsi Anda</th>
                                        <th>Status</th>
                                        <th>Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($yourGroups as $group): ?>
                                    <tr>
                                        <td><strong><?= View::e($group['group_code']) ?></strong></td>
                                        <td><?= $group['display_total_amount'] ?></td>
                                        <td><?= $group['display_share_amount'] ?></td>
                                        <td>
                                            <?php
                                            $statusColors = [
                                                'pending' => 'warning',
                                                'paid' => 'success',
                                                'overdue' => 'danger'
                                            ];
                                            ?>
                                            <span class="badge bg-<?= $statusColors[$group['payment_status']] ?? 'secondary' ?>">
                                                <?= ucfirst($group['payment_status']) ?>
                                            </span>
                                        </td>
                                        <td>
                                            <a href="<?= View::url('split-payment/group-status?code=' . $group['group_code']) ?>" class="btn btn-sm btn-primary">
                                                Lihat Detail
                                            </a>
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
    </div>
</div>

<script>
document.getElementById('join-by-code-form').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    const data = Object.fromEntries(formData.entries());
    
    fetch('<?= View::url('split-payment/join') ?>', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify(data)
    })
    .then(response => response.json())
    .then(result => {
        if (result.status === 'success') {
            alert('Berhasil bergabung ke grup!');
            window.location.href = '<?= View::url('split-payment/group-status?code=' . result.group_code) ?>';
        } else {
            alert('Gagal bergabung: ' + result.message);
        }
    })
    .catch(error => {
        alert('Terjadi kesalahan: ' + error.message);
    });
});
</script>

<?php include APP_ROOT . '/app/views/layouts/footer.php'; ?>
