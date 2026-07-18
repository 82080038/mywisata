<?php include APP_ROOT . '/app/views/layouts/admin_header.php'; ?>

<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Edit Destinasi</h2>
        <a href="<?= View::url('admin/destinations') ?>" class="btn btn-secondary">
            <i class="fas fa-arrow-left me-2"></i>Kembali
        </a>
    </div>

    <div class="card">
        <div class="card-body">
            <form method="POST" action="<?= View::url('admin/updateDestination') ?>" enctype="multipart/form-data">
                <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">
                <input type="hidden" name="id" value="<?= $destination['id'] ?>">
                <input type="hidden" name="existing_image" value="<?= View::e($destination['main_image'] ?? '') ?>">
                <div class="row">
                    <div class="col-md-8">
                        <div class="mb-3">
                            <label class="form-label">Nama Destinasi *</label>
                            <input type="text" name="name" class="form-control" value="<?= View::e($destination['name']) ?>" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Deskripsi Singkat</label>
                            <input type="text" name="short_desc" class="form-control" maxlength="200" value="<?= View::e($destination['short_desc'] ?? '') ?>">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Deskripsi Lengkap</label>
                            <textarea name="description" class="form-control" rows="5"><?= View::e($destination['description'] ?? '') ?></textarea>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Kota</label>
                                <input type="text" name="city" class="form-control" value="<?= View::e($destination['city'] ?? '') ?>">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Provinsi</label>
                                <input type="text" name="province" class="form-control" value="<?= View::e($destination['province'] ?? '') ?>">
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Latitude</label>
                                <input type="text" name="latitude" class="form-control" value="<?= View::e($destination['latitude'] ?? '') ?>">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Longitude</label>
                                <input type="text" name="longitude" class="form-control" value="<?= View::e($destination['longitude'] ?? '') ?>">
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="mb-3">
                            <label class="form-label">Kategori</label>
                            <select name="category_id" class="form-select">
                                <option value="">Pilih Kategori</option>
                                <?php foreach ($categories as $cat): ?>
                                <option value="<?= $cat['id'] ?>" <?= $destination['category_id'] == $cat['id'] ? 'selected' : '' ?>><?= View::e($cat['name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Harga Tiket (Rp)</label>
                            <input type="number" name="entry_fee" class="form-control" value="<?= $destination['entry_fee'] ?? 0 ?>" min="0">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Foto Utama</label>
                            <?php if (!empty($destination['main_image'])): ?>
                            <img src="<?= View::asset('uploads/destinations/' . $destination['main_image']) ?>" class="img-thumbnail mb-2" style="max-height: 150px;">
                            <?php endif; ?>
                            <input type="file" name="main_image" class="form-control" accept="image/*">
                            <small class="text-muted">Kosongkan jika tidak ingin mengubah</small>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Status</label>
                            <select name="is_active" class="form-select">
                                <option value="1" <?= ($destination['is_active'] ?? 1) ? 'selected' : '' ?>>Aktif</option>
                                <option value="0" <?= !($destination['is_active'] ?? 1) ? 'selected' : '' ?>>Nonaktif</option>
                            </select>
                        </div>
                    </div>
                </div>
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save me-2"></i>Update
                </button>
            </form>
        </div>
    </div>
</div>

<?php include APP_ROOT . '/app/views/layouts/admin_footer.php'; ?>
