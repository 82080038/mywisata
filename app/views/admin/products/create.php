<?php include APP_ROOT . '/app/views/layouts/admin_header.php'; ?>

<div class="container-fluid py-4">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <h2 class="mb-4">Tambah Produk Souvenir</h2>

            <?php if (Session::hasFlash('error')): ?>
            <div class="alert alert-danger"><?= View::e(Session::getFlash('error')) ?></div>
            <?php endif; ?>

            <div class="card">
                <div class="card-body">
                    <form method="POST" action="<?= View::url('admin/storeProduct') ?>" enctype="multipart/form-data">
                        <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">

                        <div class="mb-3">
                            <label class="form-label">Nama Produk <span class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control" required>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Kategori</label>
                                <select name="category_id" class="form-select">
                                    <option value="">-- Pilih --</option>
                                    <?php foreach ($categories as $cat): ?>
                                    <option value="<?= $cat['id'] ?>"><?= View::e($cat['name']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Destinasi Terkait</label>
                                <select name="destination_id" class="form-select">
                                    <option value="">-- Tidak ada --</option>
                                    <?php foreach ($destinations as $dest): ?>
                                    <option value="<?= $dest['id'] ?>"><?= View::e($dest['name']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Deskripsi Singkat</label>
                            <input type="text" name="short_desc" class="form-control" maxlength="300">
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Deskripsi Lengkap</label>
                            <textarea name="description" class="form-control" rows="4"></textarea>
                        </div>

                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Harga (Rp) <span class="text-danger">*</span></label>
                                <input type="number" name="price" class="form-control" required min="0" step="100">
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Harga Diskon</label>
                                <input type="number" name="discount_price" class="form-control" min="0" step="100" value="0">
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Stok <span class="text-danger">*</span></label>
                                <input type="number" name="stock" class="form-control" required min="0" value="0">
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">SKU</label>
                                <input type="text" name="sku" class="form-control">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Daerah/Region</label>
                                <input type="text" name="region" class="form-control" placeholder="cth: Yogyakarta, Bali, Lombok">
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Gambar Produk</label>
                            <input type="file" name="main_image" class="form-control" accept="image/*">
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <div class="form-check">
                                    <input type="checkbox" name="is_featured" value="1" class="form-check-input">
                                    <label class="form-check-label">Produk Unggulan (Featured)</label>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-check">
                                    <input type="checkbox" name="is_active" value="1" class="form-check-input" checked>
                                    <label class="form-check-label">Aktif</label>
                                </div>
                            </div>
                        </div>

                        <div class="d-flex justify-content-between">
                            <a href="<?= View::url('admin/products') ?>" class="btn btn-secondary">Batal</a>
                            <button type="submit" class="btn btn-primary"><i class="fas fa-save me-2"></i>Simpan</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include APP_ROOT . '/app/views/layouts/admin_footer.php'; ?>
