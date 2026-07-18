<?php include APP_ROOT . '/app/views/layouts/header.php'; ?>

<div class="container py-4">
    <div class="row mb-4">
        <div class="col-md-12">
            <h1 class="mb-3"><i class="fas fa-leaf me-2"></i>Green Credits</h1>
            <p class="text-muted">Dapatkan green credits untuk booking eco-friendly dan tukarkan dengan rewards</p>
        </div>
    </div>
    
    <!-- Credits Balance Card -->
    <div class="row mb-4">
        <div class="col-md-4">
            <div class="card glass-card">
                <div class="card-body text-center">
                    <h5 class="card-title">Saldo Green Credits</h5>
                    <div class="display-4 text-success fw-bold"><?= number_format($credits['credits_balance'], 0) ?></div>
                    <p class="text-muted small">Credits</p>
                    <div class="mt-3">
                        <span class="badge bg-info">Tier: <?= ucfirst($credits['tier']) ?></span>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card glass-card">
                <div class="card-body text-center">
                    <h5 class="card-title">Total Earned</h5>
                    <div class="display-4 text-primary fw-bold"><?= number_format($credits['credits_earned'], 0) ?></div>
                    <p class="text-muted small">Credits</p>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card glass-card">
                <div class="card-body text-center">
                    <h5 class="card-title">Total Spent</h5>
                    <div class="display-4 text-warning fw-bold"><?= number_format($credits['credits_spent'], 0) ?></div>
                    <p class="text-muted small">Credits</p>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Tier Progress -->
    <div class="card mb-4 glass-card">
        <div class="card-body">
            <h5 class="card-title mb-3">Progress Tier</h5>
            <div class="progress mb-2" style="height: 30px;">
                <div class="progress-bar bg-success" role="progressbar" style="width: <?= $tierProgress ?>%" aria-valuenow="<?= $tierProgress ?>" aria-valuemin="0" aria-valuemax="100">
                    <?= ucfirst($credits['tier']) ?>
                </div>
            </div>
            <div class="d-flex justify-content-between small text-muted">
                <span>Bronze (0)</span>
                <span>Silver (100)</span>
                <span>Gold (500)</span>
                <span>Platinum (1000)</span>
                <span>Diamond (5000)</span>
            </div>
        </div>
    </div>
    
    <!-- Available Rewards -->
    <div class="row mb-4">
        <div class="col-md-12">
            <h3 class="mb-3 section-title"><i class="fas fa-gift me-2"></i>Rewards Tersedia</h3>
            <div class="row">
                <?php foreach ($rewards as $reward): ?>
                <div class="col-md-4 mb-4">
                    <div class="card h-100 hover-shadow">
                        <div class="card-body">
                            <h5 class="card-title"><?= View::e($reward['reward_name']) ?></h5>
                            <p class="card-text small"><?= View::e($reward['description']) ?></p>
                            <div class="d-flex justify-content-between align-items-center mt-3">
                                <span class="badge bg-primary"><?= $reward['credits_required'] ?> Credits</span>
                                <?php if ($credits['credits_balance'] >= $reward['credits_required']): ?>
                                    <button onclick="claimReward(<?= $reward['id'] ?>)" class="btn btn-success btn-sm">
                                        <i class="fas fa-check me-1"></i>Claim
                                    </button>
                                <?php else: ?>
                                    <button class="btn btn-secondary btn-sm" disabled>
                                        <i class="fas fa-lock me-1"></i>Kurang
                                    </button>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
    
    <!-- Transaction History -->
    <div class="row">
        <div class="col-md-12">
            <h3 class="mb-3 section-title"><i class="fas fa-history me-2"></i>Riwayat Transaksi</h3>
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
                                        <th>Tipe</th>
                                        <th>Jumlah</th>
                                        <th>Alasan</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($transactions as $transaction): ?>
                                    <tr>
                                        <td><?= date('d-m-Y H:i', strtotime($transaction['created_at'])) ?></td>
                                        <td>
                                            <?php if ($transaction['transaction_type'] === 'earned'): ?>
                                                <span class="badge bg-success">Earned</span>
                                            <?php else: ?>
                                                <span class="badge bg-warning">Spent</span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="<?= $transaction['transaction_type'] === 'earned' ? 'text-success' : 'text-warning' ?>">
                                            <?= $transaction['transaction_type'] === 'earned' ? '+' : '-' ?><?= number_format($transaction['amount'], 0) ?>
                                        </td>
                                        <td><?= View::e($transaction['reason']) ?></td>
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
function claimReward(rewardId) {
    if (confirm('Apakah Anda yakin ingin claim reward ini?')) {
        const formData = new FormData();
        formData.append('reward_id', rewardId);
        formData.append('csrf_token', '<?= $csrf_token ?>');
        
        fetch('<?= View::url('green-credits/claim') ?>', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(result => {
            if (result.status === 'success') {
                alert('Reward berhasil di-claim!');
                location.reload();
            } else {
                alert('Gagal claim reward: ' + result.message);
            }
        })
        .catch(error => {
            alert('Terjadi kesalahan: ' + error.message);
        });
    }
}
</script>

<?php include APP_ROOT . '/app/views/layouts/footer.php'; ?>
