<?php include APP_ROOT . '/app/views/layouts/header.php'; ?>

<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <h2 class="mb-4"><i class="fas fa-plus-circle me-2"></i>Tambah Produk Souvenir</h2>

            <?php if (Session::hasFlash('error')): ?>
            <div class="alert alert-danger"><?= View::e(Session::getFlash('error')) ?></div>
            <?php endif; ?>

            <div class="card shadow">
                <div class="card-body">
                    <form method="POST" action="<?= View::url('merchant/storeProduct') ?>" enctype="multipart/form-data">
                        <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">

                        <div class="mb-3">
                            <label class="form-label">Nama Produk <span class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control" required placeholder="cth: Batik Tulis Madura">
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Kategori</label>
                                <select name="category_id" class="form-select">
                                    <option value="">-- Pilih Kategori --</option>
                                    <?php foreach ($categories as $cat): ?>
                                    <option value="<?= $cat['id'] ?>"><?= View::e($cat['name']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Daerah/Region <span class="text-danger">*</span></label>
                                <input type="text" name="region" class="form-control" required placeholder="cth: Yogyakarta, Bali, Madura">
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Destinasi Terkait (opsional)</label>
                            <select name="destination_id" class="form-select">
                                <option value="">-- Tidak ada --</option>
                                <?php foreach ($destinations as $dest): ?>
                                <option value="<?= $dest['id'] ?>"><?= View::e($dest['name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Deskripsi Singkat</label>
                            <input type="text" name="short_desc" class="form-control" maxlength="300" placeholder="Deskripsi singkat produk...">
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Deskripsi Lengkap</label>
                            <textarea name="description" class="form-control" rows="4" placeholder="Jelaskan produk Anda secara detail..."></textarea>
                        </div>

                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Harga (Rp) <span class="text-danger">*</span></label>
                                <input type="number" name="price" class="form-control" required min="0" step="100" placeholder="0">
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Harga Diskon</label>
                                <input type="number" name="discount_price" class="form-control" min="0" step="100" value="0">
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Stok <span class="text-danger">*</span></label>
                                <input type="number" name="stock" class="form-control" required min="0" value="1">
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">SKU (opsional)</label>
                                <input type="text" name="sku" class="form-control" placeholder="cth: BTK-001">
                            </div>
                            <div class="col-md-6 mb-3">
                                <div class="form-check mt-4">
                                    <input type="checkbox" name="is_active" value="1" class="form-check-input" checked>
                                    <label class="form-check-label">Aktif (tampilkan di toko)</label>
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Gambar Utama <span class="text-danger">*</span></label>
                            <input type="file" name="main_image" class="form-control" accept="image/*" required>
                            <small class="text-muted">Format: JPG, PNG, WebP. Max 2MB. Rekomendasi 400x300px</small>
                            <div id="imagePreview" class="mt-2"></div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Gambar Tambahan (opsional)</label>
                            <input type="file" name="images[]" class="form-control" accept="image/*" multiple>
                            <small class="text-muted">Pilih beberapa gambar untuk galeri produk</small>
                        </div>

                        <div class="d-flex justify-content-between">
                            <a href="<?= View::url('merchant/products') ?>" class="btn btn-secondary">Batal</a>
                            <button type="submit" class="btn btn-primary"><i class="fas fa-save me-2"></i>Simpan Produk</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.querySelector('input[name="main_image"]').addEventListener('change', function(e) {
    var preview = document.getElementById('imagePreview');
    preview.innerHTML = '';
    if (e.target.files && e.target.files[0]) {
        var reader = new FileReader();
        reader.onload = function(ev) {
            preview.innerHTML = '<img src="' + ev.target.result + '" class="rounded border" style="max-height: 150px;">';
        };
        reader.readAsDataURL(e.target.files[0]);
    }
});
</script>

<?php include APP_ROOT . '/app/views/layouts/footer.php'; ?>
