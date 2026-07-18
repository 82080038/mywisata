<?php include APP_ROOT . '/app/views/layouts/admin_header.php'; ?>

<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2><i class="fas <?= $config['icon'] ?? 'fa-table' ?> me-2"></i><?= isset($record) ? 'Edit' : 'Tambah' ?> <?= View::e($config['label']) ?></h2>
        <a href="<?= View::url('mastertable/list?table=' . $table) ?>" class="btn btn-outline-secondary btn-sm">
            <i class="fas fa-arrow-left me-1"></i>Kembali
        </a>
    </div>

    <div class="card shadow-sm">
        <div class="card-body">
            <form method="POST" action="<?= View::url(isset($record) ? 'mastertable/update' : 'mastertable/store') ?>">
                <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">
                <input type="hidden" name="table" value="<?= View::e($table) ?>">
                <?php if (isset($record)): ?>
                <input type="hidden" name="id" value="<?= $record['id'] ?>">
                <?php endif; ?>

                <div class="row">
                    <?php foreach ($config['columns'] as $field => $fc): ?>
                        <?php if ($fc['type'] === 'hidden') continue; ?>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">
                            <?= View::e($fc['label']) ?>
                            <?php if ($fc['required'] ?? false): ?><span class="text-danger">*</span><?php endif; ?>
                        </label>
                        <?php
                        $currentValue = $record[$field] ?? ($fc['default'] ?? '');
                        if ($fc['type'] === 'boolean'):
                            // Boolean as a checkbox
                        ?>
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" name="<?= $field ?>"
                                   id="<?= $field ?>" <?= $currentValue ? 'checked' : '' ?>>
                            <label class="form-check-label" for="<?= $field ?>"><?= $currentValue ? 'Aktif' : 'Nonaktif' ?></label>
                        </div>
                        <?php elseif ($fc['type'] === 'textarea'): ?>
                        <textarea class="form-control" name="<?= $field ?>" id="<?= $field ?>"
                                  rows="3" <?= ($fc['required'] ?? false) ? 'required' : '' ?>><?= View::e($currentValue) ?></textarea>
                        <?php elseif ($fc['type'] === 'number'): ?>
                        <input type="number" class="form-control" name="<?= $field ?>" id="<?= $field ?>"
                               value="<?= View::e($currentValue) ?>"
                               step="<?= $fc['step'] ?? '1' ?>"
                               <?= ($fc['required'] ?? false) ? 'required' : '' ?>
                               placeholder="<?= $fc['placeholder'] ?? '' ?>">
                        <?php elseif ($fc['type'] === 'select'): ?>
                        <select class="form-select" name="<?= $field ?>" id="<?= $field ?>" <?= ($fc['required'] ?? false) ? 'required' : '' ?>>
                            <option value="">-- Pilih --</option>
                            <?php foreach ($fc['options'] ?? [] as $optVal => $optLabel): ?>
                            <option value="<?= View::e($optVal) ?>" <?= $currentValue == $optVal ? 'selected' : '' ?>><?= View::e($optLabel) ?></option>
                            <?php endforeach; ?>
                        </select>
                        <?php elseif ($fc['type'] === 'slug'): ?>
                        <input type="text" class="form-control" name="<?= $field ?>" id="<?= $field ?>"
                               value="<?= View::e($currentValue) ?>" readonly
                               style="background-color:#e9ecef;">
                        <small class="text-muted">Auto-generated dari <?= View::e($fc['source'] ?? 'name') ?></small>
                        <?php else: ?>
                        <input type="text" class="form-control" name="<?= $field ?>" id="<?= $field ?>"
                               value="<?= View::e($currentValue) ?>"
                               <?= ($fc['required'] ?? false) ? 'required' : '' ?>
                               placeholder="<?= $fc['placeholder'] ?? '' ?>">
                        <?php endif; ?>
                    </div>
                    <?php endforeach; ?>
                </div>

                <?php if (isset($record) && isset($record['is_system']) && $record['is_system'] == 1): ?>
                <div class="alert alert-warning">
                    <i class="fas fa-lock me-1"></i>Record sistem: dapat diedit tetapi tidak dapat dihapus.
                </div>
                <?php endif; ?>

                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save me-1"></i>Simpan
                    </button>
                    <a href="<?= View::url('mastertable/list?table=' . $table) ?>" class="btn btn-outline-secondary">Batal</a>
                </div>
            </form>
        </div>
    </div>
</div>

<?php include APP_ROOT . '/app/views/layouts/admin_footer.php'; ?>
