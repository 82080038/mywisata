<?php include APP_ROOT . '/app/views/layouts/admin_header.php'; ?>

<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2><i class="fas <?= $config['icon'] ?? 'fa-table' ?> me-2"></i><?= View::e($config['label']) ?></h2>
        <a href="<?= View::url('mastertable/index') ?>" class="btn btn-outline-secondary btn-sm">
            <i class="fas fa-arrow-left me-1"></i>Kembali
        </a>
    </div>

    <div class="card shadow-sm">
        <div class="card-header d-flex justify-content-between align-items-center">
            <form method="GET" action="<?= View::url('mastertable/list') ?>" class="d-flex gap-2">
                <input type="hidden" name="table" value="<?= View::e($table) ?>">
                <input type="text" class="form-control form-control-sm" name="q" value="<?= View::e($search) ?>" placeholder="Cari..." style="width:200px;">
                <button type="submit" class="btn btn-outline-primary btn-sm"><i class="fas fa-search"></i></button>
                <?php if ($search): ?>
                <a href="<?= View::url('mastertable/list?table=' . $table) ?>" class="btn btn-outline-secondary btn-sm">Reset</a>
                <?php endif; ?>
            </form>
            <a href="<?= View::url('mastertable/create?table=' . $table) ?>" class="btn btn-primary btn-sm">
                <i class="fas fa-plus me-1"></i>Tambah <?= View::e($config['label']) ?>
            </a>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th width="50">#</th>
                            <?php foreach ($config['columns'] as $field => $fc): ?>
                                <?php if ($fc['type'] === 'hidden') continue; ?>
                            <th><?= View::e($fc['label']) ?></th>
                            <?php endforeach; ?>
                            <?php if (isset($config['columns']['is_active'])): ?>
                            <th width="80">Status</th>
                            <?php endif; ?>
                            <th width="120">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($records)): ?>
                        <tr>
                            <td colspan="20" class="text-center py-4 text-muted">Tidak ada data</td>
                        </tr>
                        <?php else: ?>
                        <?php foreach ($records as $i => $record): ?>
                        <tr>
                            <td><?= $i + 1 ?></td>
                            <?php foreach ($config['columns'] as $field => $fc): ?>
                                <?php if ($fc['type'] === 'hidden') continue; ?>
                            <td>
                                <?php if ($fc['type'] === 'boolean'): ?>
                                    <?php if ($record[$field] ?? 0): ?>
                                    <span class="badge bg-success">Ya</span>
                                    <?php else: ?>
                                    <span class="badge bg-secondary">Tidak</span>
                                    <?php endif; ?>
                                <?php elseif ($fc['type'] === 'textarea'): ?>
                                    <small><?= View::e(mb_substr($record[$field] ?? '', 0, 80)) ?><?= mb_strlen($record[$field] ?? '') > 80 ? '...' : '' ?></small>
                                <?php elseif ($fc['type'] === 'select' && isset($fc['options'][$record[$field] ?? ''])): ?>
                                    <span class="badge bg-info"><?= View::e($fc['options'][$record[$field]]) ?></span>
                                <?php else: ?>
                                    <?= View::e($record[$field] ?? '') ?>
                                <?php endif; ?>
                            </td>
                            <?php endforeach; ?>
                            <?php if (isset($config['columns']['is_active'])): ?>
                            <td>
                                <button class="btn btn-sm btn-toggle <?= ($record['is_active'] ?? 0) ? 'btn-outline-success' : 'btn-outline-secondary' ?>"
                                        data-table="<?= View::e($table) ?>"
                                        data-id="<?= $record['id'] ?>"
                                        onclick="toggleActiveMaster(this)">
                                    <i class="fas <?= ($record['is_active'] ?? 0) ? 'fa-toggle-on' : 'fa-toggle-off' ?>"></i>
                                </button>
                            </td>
                            <?php endif; ?>
                            <td>
                                <a href="<?= View::url('mastertable/edit?table=' . $table . '&id=' . $record['id']) ?>"
                                   class="btn btn-sm btn-outline-primary" title="Edit">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <button class="btn btn-sm btn-outline-danger"
                                        data-table="<?= View::e($table) ?>"
                                        data-id="<?= $record['id'] ?>"
                                        data-label="<?= View::e($config['label']) ?>"
                                        <?= (isset($record['is_system']) && $record['is_system'] == 1) ? 'data-system="1"' : '' ?>
                                        onclick="deleteMaster(this)">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script>
function toggleActiveMaster(btn) {
    if (!confirm('Ubah status aktif record ini?')) return;
    fetch(APP_URL + 'mastertable/toggleActive', {
        method: 'POST',
        headers: { 'X-Requested-With': 'XMLHttpRequest' },
        body: new URLSearchParams({
            table: btn.dataset.table,
            id: btn.dataset.id,
            csrf_token: '<?= $csrf_token ?>'
        })
    }).then(r => r.json()).then(data => {
        if (data.status === 'success') {
            location.reload();
        } else {
            alert(data.message || 'Error');
        }
    });
}

function deleteMaster(btn) {
    var isSystem = btn.dataset.system === '1';
    var msg = isSystem
        ? 'Record sistem tidak dapat dihapus, akan dinonaktifkan. Lanjutkan?'
        : 'Hapus record ' + btn.dataset.label + '?';
    if (!confirm(msg)) return;
    fetch(APP_URL + 'mastertable/delete', {
        method: 'POST',
        headers: { 'X-Requested-With': 'XMLHttpRequest' },
        body: new URLSearchParams({
            table: btn.dataset.table,
            id: btn.dataset.id,
            csrf_token: '<?= $csrf_token ?>'
        })
    }).then(r => r.json()).then(data => {
        if (data.status === 'success') {
            alert(data.message);
            location.reload();
        } else {
            alert(data.message || 'Error');
        }
    });
}
</script>

<?php include APP_ROOT . '/app/views/layouts/admin_footer.php'; ?>
