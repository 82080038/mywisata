<?php include APP_ROOT . '/app/views/layouts/header.php'; ?>

<div class="container py-4">
    <div class="row mb-4">
        <div class="col-md-12">
            <h1 class="mb-3"><i class="fas fa-users me-2"></i>Status Grup Pembayaran</h1>
            <p class="text-muted">Kode Grup: <strong><?= View::e($group['group_code']) ?></strong></p>
        </div>
    </div>
    
    <!-- Group Summary -->
    <div class="row mb-4">
        <div class="col-md-4">
            <div class="card glass-card">
                <div class="card-body text-center">
                    <h5 class="card-title">Total Pembayaran</h5>
                    <div class="display-4 text-primary fw-bold"><?= $group['display_total_amount'] ?></div>
                    <p class="text-muted small">Total Tagihan</p>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card glass-card">
                <div class="card-body text-center">
                    <h5 class="card-title">Terbayar</h5>
                    <div class="display-4 text-success fw-bold"><?= $group['display_paid_amount'] ?></div>
                    <p class="text-muted small"><?= $group['paid_count'] ?> / <?= $group['total_participants'] ?> peserta</p>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card glass-card">
                <div class="card-body text-center">
                    <h5 class="card-title">Sisa</h5>
                    <div class="display-4 text-warning fw-bold"><?= $group['display_remaining_amount'] ?></div>
                    <p class="text-muted small">Belum dibayar</p>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Progress -->
    <div class="card mb-4 glass-card">
        <div class="card-body">
            <h5 class="card-title mb-3">Progress Pembayaran</h5>
            <div class="progress mb-2" style="height: 30px;">
                <div class="progress-bar bg-success" role="progressbar" style="width: <?= $group['progress_percentage'] ?>%" aria-valuenow="<?= $group['progress_percentage'] ?>" aria-valuemin="0" aria-valuemax="100">
                    <?= $group['progress_percentage'] ?>%
                </div>
            </div>
            <div class="d-flex justify-content-between small text-muted">
                <span><?= $group['paid_count'] ?> peserta sudah bayar</span>
                <span><?= $group['total_participants'] - $group['paid_count'] ?> peserta belum bayar</span>
            </div>
        </div>
    </div>
    
    <!-- Participants -->
    <div class="row">
        <div class="col-md-12">
            <h3 class="mb-3 section-title"><i class="fas fa-user-friends me-2"></i>Peserta</h3>
            <div class="card glass-card">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>Nama</th>
                                    <th>Email</th>
                                    <th>Porsi</th>
                                    <th>Terbayar</th>
                                    <th>Sisa</th>
                                    <th>Status</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($participants as $participant): ?>
                                <tr>
                                    <td>
                                        <strong><?= View::e($participant['participant_name']) ?></strong>
                                        <?php if ($participant['is_creator']): ?>
                                            <span class="badge bg-info ms-1">Creator</span>
                                        <?php endif; ?>
                                    </td>
                                    <td><?= View::e($participant['participant_email']) ?></td>
                                    <td><?= $participant['display_share_amount'] ?></td>
                                    <td class="text-success"><?= $participant['display_amount_paid'] ?></td>
                                    <td class="text-warning"><?= $participant['display_amount_remaining'] ?></td>
                                    <td>
                                        <?php
                                        $statusColors = [
                                            'pending' => 'warning',
                                            'paid' => 'success',
                                            'overdue' => 'danger'
                                        ];
                                        ?>
                                        <span class="badge bg-<?= $statusColors[$participant['payment_status']] ?? 'secondary' ?>">
                                            <?= ucfirst($participant['payment_status']) ?>
                                        </span>
                                    </td>
                                    <td>
                                        <?php if ($participant['payment_status'] === 'pending' && $participant['user_id'] == $_SESSION['user_id']): ?>
                                            <button onclick="payShare(<?= $participant['id'] ?>)" class="btn btn-sm btn-primary">
                                                <i class="fas fa-credit-card me-1"></i>Bayar
                                            </button>
                                        <?php elseif ($participant['payment_status'] === 'paid'): ?>
                                            <button class="btn btn-sm btn-success" disabled>
                                                <i class="fas fa-check me-1"></i>Lunas
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
    </div>
    
    <!-- Payment History -->
    <div class="row mt-4">
        <div class="col-md-12">
            <h3 class="mb-3 section-title"><i class="fas fa-history me-2"></i>Riwayat Pembayaran</h3>
            <div class="card glass-card">
                <div class="card-body">
                    <?php if (empty($transactions)): ?>
                        <div class="text-center py-5">
                            <i class="fas fa-receipt fa-3x text-muted mb-3"></i>
                            <p class="text-muted">Belum ada transaksi</p>
                        </div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>Tanggal</th>
                                        <th>Peserta</th>
                                        <th>Jumlah</th>
                                        <th>Metode</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($transactions as $transaction): ?>
                                    <tr>
                                        <td><?= date('d-m-Y H:i', strtotime($transaction['created_at'])) ?></td>
                                        <td><?= View::e($transaction['participant_name']) ?></td>
                                        <td class="text-success"><?= $transaction['display_amount'] ?></td>
                                        <td><?= View::e($transaction['payment_method']) ?></td>
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
function payShare(participantId) {
    if (confirm('Lanjutkan pembayaran porsi Anda?')) {
        window.location.href = '<?= View::url('payment/index?split_payment_participant_id=' . participantId) ?>';
    }
}
</script>

<?php include APP_ROOT . '/app/views/layouts/footer.php'; ?>
