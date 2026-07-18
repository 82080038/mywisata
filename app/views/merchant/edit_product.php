<?php include APP_ROOT . '/app/views/layouts/header.php'; ?>

<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <h2 class="mb-4"><i class="fas fa-edit me-2"></i>Edit Produk</h2>

            <?php if (Session::hasFlash('error')): ?>
            <div class="alert alert-danger"><?= View::e(Session::getFlash('error')) ?></div>
            <?php endif; ?>

            <div class="card shadow">
                <div class="card-body">
                    <form method="POST" action="<?= View::url('merchant/updateProduct') ?>" enctype="multipart/form-data">
                        <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">
                        <input type="hidden" name="id" value="<?= $product['id'] ?>">
                        <input type="hidden" name="existing_image" value="<?= View::e($product['main_image'] ?? '') ?>">

                        <div class="mb-3">
                            <label class="form-label">Nama Produk <span class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control" value="<?= View::e($product['name']) ?>" required>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Kategori</label>
                                <select name="category_id" class="form-select">
                                    <option value="">-- Pilih Kategori --</option>
                                    <?php foreach ($categories as $cat): ?>
                                    <option value="<?= $cat['id'] ?>" <?= $product['category_id'] == $cat['id'] ? 'selected' : '' ?>><?= View::e($cat['name']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Daerah/Region</label>
                                <input type="text" name="region" class="form-control" value="<?= View::e($product['region'] ?? '') ?>">
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Destinasi Terkait</label>
                            <select name="destination_id" class="form-select">
                                <option value="">-- Tidak ada --</option>
                                <?php foreach ($destinations as $dest): ?>
                                <option value="<?= $dest['id'] ?>" <?= $product['destination_id'] == $dest['id'] ? 'selected' : '' ?>><?= View::e($dest['name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Deskripsi Singkat</label>
                            <input type="text" name="short_desc" class="form-control" maxlength="300" value="<?= View::e($product['short_desc'] ?? '') ?>">
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Deskripsi Lengkap</label>
                            <textarea name="description" class="form-control" rows="4"><?= View::e($product['description'] ?? '') ?></textarea>
                        </div>

                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Harga (Rp)</label>
                                <input type="number" name="price" class="form-control" value="<?= $product['price'] ?>" required min="0" step="100">
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Harga Diskon</label>
                                <input type="number" name="discount_price" class="form-control" value="<?= $product['discount_price'] ?? 0 ?>" min="0" step="100">
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Stok</label>
                                <input type="number" name="stock" class="form-control" value="<?= $product['stock'] ?>" required min="0">
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">SKU</label>
                                <input type="text" name="sku" class="form-control" value="<?= View::e($product['sku'] ?? '') ?>">
                            </div>
                            <div class="col-md-6 mb-3">
                                <div class="form-check mt-4">
                                    <input type="checkbox" name="is_active" value="1" class="form-check-input" <?= $product['is_active'] ? 'checked' : '' ?>>
                                    <label class="form-check-label">Aktif (tampilkan di toko)</label>
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Gambar Utama</label>
                            <?php if (!empty($product['main_image'])): ?>
                            <div class="mb-2">
                                <img src="<?= View::asset('uploads/products/' . $product['main_image']) ?>" class="rounded border" style="max-height: 150px;">
                            </div>
                            <?php endif; ?>
                            <input type="file" name="main_image" class="form-control" accept="image/*">
                            <small class="text-muted">Kosongkan jika tidak ingin mengganti gambar</small>
                        </div>

                        <?php if (!empty($images)): ?>
                        <div class="mb-3">
                            <label class="form-label">Gambar Tambahan</label>
                            <div class="row">
                                <?php foreach ($images as $img): ?>
                                <div class="col-3 mb-2 position-relative">
                                    <img src="<?= View::asset('uploads/products/' . $img['image']) ?>" class="img-thumbnail" style="height: 80px; object-fit: cover;">
                                    <button type="button" class="btn btn-sm btn-danger position-absolute top-0 end-0" style="padding: 2px 6px;" onclick="deleteImage(<?= $img['id'] ?>)">
                                        <i class="fas fa-times"></i>
                                    </button>
                                </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                        <?php endif; ?>

                        <div class="mb-3">
                            <label class="form-label">Tambah Gambar Lain</label>
                            <input type="file" name="images[]" class="form-control" accept="image/*" multiple>
                        </div>

                        <div class="d-flex justify-content-between">
                            <a href="<?= View::url('merchant/products') ?>" class="btn btn-secondary">Batal</a>
                            <button type="submit" class="btn btn-primary"><i class="fas fa-save me-2"></i>Update Produk</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function deleteImage(imageId) {
    Swal.fire({
        title: 'Hapus gambar?',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        confirmButtonText: 'Hapus'
    }).then((result) => {
        if (result.isConfirmed) {
            var formData = new FormData();
            formData.append('csrf_token', '<?= $csrf_token ?>');
            formData.append('image_id', imageId);
            fetch(window.APP_URL + 'merchant/deleteImage', {
                method: 'POST',
                body: formData
            })
            .then(r => r.json())
            .then(data => {
                if (data.status === 'success') location.reload();
            });
        }
    });
}
</script>

<?php include APP_ROOT . '/app/views/layouts/footer.php'; ?>
