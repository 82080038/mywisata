<?php include APP_ROOT . '/app/views/layouts/admin_header.php'; ?>

<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Manajemen Transaksi <span class="badge bg-secondary"><?= $total ?></span></h2>
    </div>

    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Kode</th>
                            <th>User</th>
                            <th>Tipe</th>
                            <th>Jumlah</th>
                            <th>Metode</th>
                            <th>Status</th>
                            <th>Tanggal</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($transactions as $trx): ?>
                            <tr>
                                <td><?= View::e($trx['transaction_code']) ?></td>
                                <td><?= View::e($trx['user_name'] ?? '') ?></td>
                                <td><span class="badge bg-info text-dark"><?= View::e($trx['type']) ?></span></td>
                                <td><?= View::currency($trx['net_amount']) ?></td>
                                <td><?= View::e($trx['payment_method'] ?? '-') ?></td>
                                <td>
                                    <?php
                                    $statusClass = [
                                        'paid' => 'success',
                                        'pending' => 'warning',
                                        'failed' => 'danger',
                                        'cancelled' => 'secondary',
                                        'expired' => 'dark',
                                    ];
                                    $cls = $statusClass[$trx['payment_status']] ?? 'secondary';
                                    ?>
                                    <span class="badge bg-<?= $cls ?>"><?= View::e($trx['payment_status']) ?></span>
                                </td>
                                <td><?= View::date($trx['created_at']) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php include APP_ROOT . '/app/views/layouts/admin_footer.php'; ?>
