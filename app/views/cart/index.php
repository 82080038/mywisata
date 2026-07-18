<?php include APP_ROOT . '/app/views/layouts/header.php'; ?>

<div class="container py-5">
    <h2 class="fw-bold mb-4"><i class="fas fa-shopping-cart me-2"></i>Keranjang Belanja</h2>

    <?php if (Session::hasFlash('success')): ?>
        <div class="alert alert-success alert-dismissible fade show">
            <?= View::e(Session::getFlash('success')) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>
    <?php if (Session::hasFlash('error')): ?>
        <div class="alert alert-danger alert-dismissible fade show">
            <?= View::e(Session::getFlash('error')) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <?php if (empty($cart)): ?>
        <div class="text-center py-5">
            <i class="fas fa-shopping-cart fa-4x text-muted mb-3"></i>
            <h4 class="text-muted">Keranjang Anda kosong</h4>
            <p class="text-muted mb-4">Mulai belanja dan tambahkan item ke keranjang Anda</p>
            <a href="<?= View::url('destinations') ?>" class="btn btn-primary">
                <i class="fas fa-map-marked-alt me-2"></i>Jelajahi Destinasi
            </a>
        </div>
    <?php else: ?>
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Item</th>
                        <th>Tipe</th>
                        <th class="text-center">Qty</th>
                        <th class="text-end">Harga</th>
                        <th class="text-end">Subtotal</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($cart as $key => $item): ?>
                        <tr>
                            <td><?= View::e($item['name']) ?></td>
                            <td><span class="badge bg-info text-dark"><?= View::e($item['type']) ?></span></td>
                            <td class="text-center"><?= (int)$item['quantity'] ?></td>
                            <td class="text-end"><?= View::currency($item['price']) ?></td>
                            <td class="text-end fw-bold"><?= View::currency($item['price'] * $item['quantity']) ?></td>
                            <td class="text-end">
                                <form method="POST" action="<?= View::url('cart/remove') ?>" class="d-inline">
                                    <input type="hidden" name="csrf_token" value="<?= Middleware::csrfToken() ?>">
                                    <input type="hidden" name="item_key" value="<?= $key ?>">
                                    <button type="submit" class="btn btn-sm btn-outline-danger">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
                <tfoot>
                    <tr class="table-light">
                        <td colspan="4" class="text-end fw-bold">Total:</td>
                        <td class="text-end fw-bold fs-5"><?= View::currency($total) ?></td>
                        <td></td>
                    </tr>
                </tfoot>
            </table>
        </div>

        <div class="d-flex justify-content-between mt-4">
            <a href="<?= View::url('destinations') ?>" class="btn btn-outline-primary">
                <i class="fas fa-arrow-left me-2"></i>Lanjut Belanja
            </a>
            <div>
                <form method="POST" action="<?= View::url('cart/clear') ?>" class="d-inline" onsubmit="return confirm('Kosongkan keranjang?')">
                    <input type="hidden" name="csrf_token" value="<?= Middleware::csrfToken() ?>">
                    <button type="submit" class="btn btn-outline-danger me-2">
                        <i class="fas fa-trash me-2"></i>Kosongkan
                    </button>
                </form>
                <a href="<?= View::url('cart/checkout') ?>" class="btn btn-success">
                    <i class="fas fa-checkout me-2"></i>Checkout
                </a>
            </div>
        </div>
    <?php endif; ?>
</div>

<?php include APP_ROOT . '/app/views/layouts/footer.php'; ?>
