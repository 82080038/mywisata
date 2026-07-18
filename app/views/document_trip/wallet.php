<?php require_once APP_PATH . '/app/views/layouts/header.php'; ?>

<div class="container-fluid py-4">
    <div class="row">
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-header bg-info text-white">
                    <h4 class="mb-0">
                        <i class="fas fa-wallet me-2"></i>
                        Digital Wallet
                    </h4>
                </div>
                <div class="card-body">
                    <!-- Wallet Balance -->
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <div class="card bg-primary text-white">
                                <div class="card-body text-center">
                                    <h5 class="card-title">Current Balance</h5>
                                    <div class="display-4 fw-bold">
                                        Rp <?php echo number_format($data['wallet']['balance'] ?? 0, 0, ',', '.'); ?>
                                    </div>
                                    <small class="opacity-75">Available funds</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-body">
                                    <h5 class="card-title">Quick Actions</h5>
                                    <div class="d-grid gap-2">
                                        <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#addFundsModal">
                                            <i class="fas fa-plus-circle me-2"></i>Add Funds
                                        </button>
                                        <button class="btn btn-info" href="/document_trip/transactions">
                                            <i class="fas fa-history me-2"></i>View Transactions
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Recent Transactions -->
                    <div class="row">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="mb-0">Recent Transactions</h5>
                                </div>
                                <div class="card-body">
                                    <div class="table-responsive">
                                        <table class="table table-striped">
                                            <thead>
                                                <tr>
                                                    <th>Type</th>
                                                    <th>Amount</th>
                                                    <th>Description</th>
                                                    <th>Balance After</th>
                                                    <th>Date</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php if (!empty($data['transactions'])): ?>
                                                    <?php foreach ($data['transactions'] as $tx): ?>
                                                        <tr>
                                                            <td>
                                                                <span class="badge bg-<?php echo $tx['transaction_type'] === 'credit' ? 'success' : 'danger'; ?>">
                                                                    <?php echo ucfirst($tx['transaction_type']); ?>
                                                                </span>
                                                            </td>
                                                            <td class="<?php echo $tx['transaction_type'] === 'credit' ? 'text-success' : 'text-danger'; ?>">
                                                                <?php echo $tx['transaction_type'] === 'credit' ? '+' : '-'; ?>
                                                                Rp <?php echo number_format($tx['amount'], 0, ',', '.'); ?>
                                                            </td>
                                                            <td><?php echo htmlspecialchars($tx['description'] ?? '-'); ?></td>
                                                            <td>Rp <?php echo number_format($tx['balance_after'], 0, ',', '.'); ?></td>
                                                            <td><?php echo date('M d, Y H:i', strtotime($tx['created_at'])); ?></td>
                                                        </tr>
                                                    <?php endforeach; ?>
                                                <?php else: ?>
                                                    <tr>
                                                        <td colspan="5" class="text-center">No transactions yet</td>
                                                    </tr>
                                                <?php endif; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Add Funds Modal -->
<div class="modal fade" id="addFundsModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add Funds</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form method="POST" action="/document_trip/addFunds">
                    <div class="mb-3">
                        <label class="form-label">Amount (IDR)</label>
                        <input type="number" class="form-control" name="amount" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Description</label>
                        <input type="text" class="form-control" name="description">
                    </div>
                    <button type="submit" class="btn btn-success w-100">Add Funds</button>
                </form>
            </div>
        </div>
    </div>
</div>

<?php require_once APP_PATH . '/app/views/layouts/footer.php'; ?>
