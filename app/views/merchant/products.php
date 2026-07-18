<?php include APP_ROOT . '/app/views/layouts/header.php'; ?>

<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2><i class="fas fa-box me-2"></i>Produk Saya</h2>
        <a href="<?= View::url('merchant/createProduct') ?>" class="btn btn-primary">
            <i class="fas fa-plus me-2"></i>Tambah Produk
        </a>
    </div>

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

    <?php if (empty($products)): ?>
    <div class="text-center py-5">
        <i class="fas fa-box fa-4x text-muted mb-3"></i>
        <h4 class="text-muted">Belum ada produk</h4>
        <p class="text-muted mb-4">Mulai jual produk souvenir & khasanah lokal Anda</p>
        <a href="<?= View::url('merchant/createProduct') ?>" class="btn btn-primary">
            <i class="fas fa-plus me-2"></i>Tambah Produk Pertama
        </a>
    </div>
    <?php else: ?>
    <div class="row">
        <?php foreach ($products as $product): ?>
        <div class="col-md-4 mb-4">
            <div class="card h-100 shadow-sm">
                <?php if (!empty($product['main_image'])): ?>
                <img src="<?= View::asset('uploads/products/' . $product['main_image']) ?>" class="card-img-top" style="height: 200px; object-fit: cover;">
                <?php else: ?>
                <div class="card-img-top bg-light d-flex align-items-center justify-content-center" style="height: 200px;">
                    <i class="fas fa-image fa-3x text-muted"></i>
                </div>
                <?php endif; ?>
                <div class="card-body">
                    <h6 class="card-title"><?= View::e($product['name']) ?></h6>
                    <p class="text-muted small mb-1"><i class="fas fa-map-marker-alt me-1"></i><?= View::e($product['region'] ?? '') ?></p>
                    <p class="fw-bold text-primary mb-1"><?= View::currency($product['price']) ?></p>
                    <p class="small mb-2">
                        Stok: <span class="badge bg-<?= $product['stock'] > 0 ? 'success' : 'danger' ?>"><?= $product['stock'] ?></span>
                        <?php if ($product['is_active']): ?>
                        <span class="badge bg-success ms-1">Active</span>
                        <?php else: ?>
                        <span class="badge bg-secondary ms-1">Inactive</span>
                        <?php endif; ?>
                    </p>
                    <div class="btn-group w-100">
                        <a href="<?= View::url('products/detail/' . $product['id']) ?>" class="btn btn-sm btn-outline-info">
                            <i class="fas fa-eye"></i>
                        </a>
                        <a href="<?= View::url('merchant/editProduct?id=' . $product['id']) ?>" class="btn btn-sm btn-outline-warning">
                            <i class="fas fa-edit"></i> Edit
                        </a>
                        <button class="btn btn-sm btn-outline-danger" onclick="deleteProduct(<?= $product['id'] ?>)">
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>
</div>

<script>
function deleteProduct(id) {
    Swal.fire({
        title: 'Hapus Produk?',
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
            fetch(window.APP_URL + 'merchant/deleteProduct', {
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
    });
}
</script>

<?php include APP_ROOT . '/app/views/layouts/footer.php'; ?>
