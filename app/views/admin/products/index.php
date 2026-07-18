<?php include APP_ROOT . '/app/views/layouts/admin_header.php'; ?>

<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Manajemen Produk Souvenir</h2>
        <a href="<?= View::url('admin/createProduct') ?>" class="btn btn-primary">
            <i class="fas fa-plus me-2"></i>Tambah Produk
        </a>
    </div>

    <?php if (Session::hasFlash('success')): ?>
    <div class="alert alert-success alert-dismissible fade show">
        <?= View::e(Session::getFlash('success')) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    <?php endif; ?>

    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-striped datatable">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Gambar</th>
                            <th>Nama</th>
                            <th>Kategori</th>
                            <th>Daerah</th>
                            <th>Harga</th>
                            <th>Stok</th>
                            <th>Status</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($products as $product): ?>
                        <tr>
                            <td><?= $product['id'] ?></td>
                            <td>
                                <?php if (!empty($product['main_image'])): ?>
                                <img src="<?= View::asset('uploads/products/' . $product['main_image']) ?>" style="width: 40px; height: 40px; object-fit: cover;" class="rounded">
                                <?php else: ?>
                                <i class="fas fa-image text-muted"></i>
                                <?php endif; ?>
                            </td>
                            <td><?= View::e($product['name']) ?></td>
                            <td><?= View::e($product['category_name'] ?? '-') ?></td>
                            <td><?= View::e($product['region'] ?? '-') ?></td>
                            <td><?= View::currency($product['price']) ?></td>
                            <td>
                                <span class="badge bg-<?= $product['stock'] > 0 ? 'success' : 'danger' ?>"><?= $product['stock'] ?></span>
                            </td>
                            <td>
                                <?php if ($product['is_active']): ?>
                                <span class="badge bg-success">Active</span>
                                <?php else: ?>
                                <span class="badge bg-secondary">Inactive</span>
                                <?php endif; ?>
                                <?php if ($product['is_featured']): ?>
                                <span class="badge bg-warning text-dark">Featured</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <a href="<?= View::url('products/detail/' . $product['id']) ?>" class="btn btn-sm btn-info">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <a href="<?= View::url('admin/editProduct?id=' . $product['id']) ?>" class="btn btn-sm btn-warning">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <button class="btn btn-sm btn-danger" onclick="deleteProduct(<?= $product['id'] ?>)">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <p class="text-muted text-center">Total: <?= count($products) ?> produk</p>
        </div>
    </div>
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
            fetch(window.APP_URL + 'admin/deleteProduct', {
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
