<?php include APP_ROOT . '/app/views/layouts/admin_header.php'; ?>

<div class="container-fluid py-4">
    <h2 class="mb-4"><i class="fas fa-database me-2"></i>Master Data</h2>
    
    <div class="alert alert-info">
        <i class="fas fa-info-circle me-1"></i>
        Kelola data master/referensi yang digunakan di seluruh aplikasi. 
        Record <strong>sistem</strong> (seeded) tidak dapat dihapus, hanya dinonaktifkan.
    </div>

    <div class="row">
        <?php foreach ($tableInfos as $info): ?>
        <div class="col-md-4 col-lg-3 mb-3">
            <a href="<?= View::url('mastertable/list?table=' . $info['name']) ?>" class="text-decoration-none">
                <div class="card h-100 shadow-sm <?= strpos($currentUri ?? '', 'masterList') !== false ? 'border-primary' : '' ?>">
                    <div class="card-body text-center">
                        <i class="fas <?= $info['icon'] ?> fa-2x text-primary mb-2"></i>
                        <h6 class="card-title mb-1"><?= $info['label'] ?></h6>
                        <p class="text-muted small mb-0">
                            <span class="badge bg-success"><?= $info['active'] ?> aktif</span>
                            <span class="badge bg-secondary"><?= $info['total'] ?> total</span>
                        </p>
                    </div>
                </div>
            </a>
        </div>
        <?php endforeach; ?>
    </div>
</div>

<?php include APP_ROOT . '/app/views/layouts/admin_footer.php'; ?>
