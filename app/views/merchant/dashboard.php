<?php include APP_ROOT . '/app/views/layouts/header.php'; ?>

<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2><i class="fas fa-store me-2"></i>Dashboard Merchant</h2>
        <a href="<?= View::url('merchant/createProduct') ?>" class="btn btn-primary">
            <i class="fas fa-plus me-2"></i>Tambah Produk
        </a>
    </div>

    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card bg-primary text-white">
                <div class="card-body">
                    <h5 class="card-title">Total Produk</h5>
                    <h2 class="display-6"><?= $stats['total_products'] ?></h2>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-success text-white">
                <div class="card-body">
                    <h5 class="card-title">Produk Aktif</h5>
                    <h2 class="display-6"><?= $stats['active_products'] ?></h2>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-info text-white">
                <div class="card-body">
                    <h5 class="card-title">Total Pesanan</h5>
                    <h2 class="display-6"><?= $stats['total_orders'] ?></h2>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-warning text-white">
                <div class="card-body">
                    <h5 class="card-title">Pendapatan</h5>
                    <h2 class="display-6"><?= View::currency($stats['total_revenue']) ?></h2>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-7">
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">Pesanan Terbaru</h5>
                </div>
                <div class="card-body">
                    <?php if (empty($recent_orders)): ?>
                    <p class="text-muted text-center py-3">Belum ada pesanan.</p>
                    <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr><th>Order</th><th>Produk</th><th>Qty</th><th>Total</th><th>Status</th></tr>
                            </thead>
                            <tbody>
                                <?php foreach ($recent_orders as $order): ?>
                                <tr>
                                    <td><small><?= View::e($order['order_code']) ?></small></td>
                                    <td><?= View::e($order['product_name']) ?></td>
                                    <td><?= $order['quantity'] ?></td>
                                    <td><?= View::currency($order['subtotal']) ?></td>
                                    <td><span class="badge bg-<?= $order['payment_status'] === 'paid' ? 'success' : 'warning' ?>"><?= $order['payment_status'] ?></span></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <div class="col-md-5">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Produk Terlaris</h5>
                </div>
                <div class="card-body">
                    <?php if (empty($top_products)): ?>
                    <p class="text-muted text-center py-3">Belum ada data penjualan.</p>
                    <?php else: ?>
                    <?php foreach ($top_products as $tp): ?>
                    <div class="d-flex align-items-center mb-2">
                        <?php if (!empty($tp['main_image'])): ?>
                        <img src="<?= View::asset('uploads/products/' . $tp['main_image']) ?>" style="width: 50px; height: 50px; object-fit: cover;" class="rounded me-2">
                        <?php else: ?>
                        <div class="bg-light rounded me-2 d-flex align-items-center justify-content-center" style="width: 50px; height: 50px;"><i class="fas fa-image text-muted"></i></div>
                        <?php endif; ?>
                        <div class="flex-grow-1">
                            <small class="fw-bold"><?= View::e($tp['name']) ?></small><br>
                            <small class="text-muted">Terjual: <?= $tp['sold_count'] ?> | Stok: <?= $tp['stock'] ?></small>
                        </div>
                    </div>
                    <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <div class="mt-4">
        <a href="<?= View::url('merchant/products') ?>" class="btn btn-outline-primary">
            <i class="fas fa-list me-2"></i>Lihat Semua Produk
        </a>
    </div>
</div>

<?php include APP_ROOT . '/app/views/layouts/footer.php'; ?>
