<?php include APP_ROOT . '/app/views/layouts/header.php'; ?>

<div class="container py-4">
    <div class="row mb-4">
        <div class="col-md-12">
            <h1 class="mb-2"><i class="fas fa-gift me-2"></i>Souvenir & Khasanah Lokal</h1>
            <p class="text-muted">Temukan produk kerajinan, kuliner, dan cinderamata khas daerah Indonesia</p>
        </div>
    </div>

    <?php if (!empty($featured)): ?>
    <div class="row mb-4">
        <div class="col-12">
            <h4 class="mb-3"><i class="fas fa-star text-warning me-2"></i>Produk Unggulan</h4>
        </div>
        <?php foreach ($featured as $product): ?>
        <div class="col-md-3 mb-3">
            <div class="card h-100 product-card">
                <?php if (!empty($product['main_image'])): ?>
                <img src="<?= View::asset('uploads/products/' . $product['main_image']) ?>" class="card-img-top" alt="<?= View::e($product['name']) ?>" style="height: 180px; object-fit: cover;">
                <?php else: ?>
                <div class="card-img-top bg-light d-flex align-items-center justify-content-center" style="height: 180px;">
                    <i class="fas fa-image fa-3x text-muted"></i>
                </div>
                <?php endif; ?>
                <div class="card-body">
                    <span class="badge bg-warning text-dark mb-1">Featured</span>
                    <h6 class="card-title"><?= View::e($product['name']) ?></h6>
                    <p class="text-muted small mb-1"><i class="fas fa-map-marker-alt me-1"></i><?= View::e($product['region'] ?? '') ?></p>
                    <p class="fw-bold text-primary mb-2"><?= View::currency($product['price']) ?></p>
                    <a href="<?= View::url('products/detail/' . $product['id']) ?>" class="btn btn-sm btn-outline-primary w-100">Lihat Detail</a>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>

    <div class="row">
        <div class="col-md-3 mb-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-filter me-2"></i>Filter</h5>
                </div>
                <div class="card-body">
                    <form method="GET" action="<?= View::url('products') ?>">
                        <div class="mb-3">
                            <label class="form-label small fw-bold">Cari</label>
                            <input type="text" name="search" class="form-control form-control-sm" value="<?= View::e($filters['search']) ?>" placeholder="Nama produk...">
                        </div>
                        <div class="mb-3">
                            <label class="form-label small fw-bold">Kategori</label>
                            <select name="category" class="form-select form-select-sm">
                                <option value="">Semua</option>
                                <?php foreach ($categories as $cat): ?>
                                <option value="<?= $cat['id'] ?>" <?= $filters['category_id'] == $cat['id'] ? 'selected' : '' ?>><?= View::e($cat['name']) ?> (<?= $cat['product_count'] ?>)</option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label small fw-bold">Daerah</label>
                            <select name="region" class="form-select form-select-sm">
                                <option value="">Semua</option>
                                <?php foreach ($regions as $reg): ?>
                                <option value="<?= View::e($reg['region']) ?>" <?= $filters['region'] === $reg['region'] ? 'selected' : '' ?>><?= View::e($reg['region']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label small fw-bold">Harga Min</label>
                            <input type="number" name="min_price" class="form-control form-control-sm" value="<?= View::e($filters['min_price']) ?>" placeholder="0">
                        </div>
                        <div class="mb-3">
                            <label class="form-label small fw-bold">Harga Max</label>
                            <input type="number" name="max_price" class="form-control form-control-sm" value="<?= View::e($filters['max_price']) ?>" placeholder="999999">
                        </div>
                        <div class="mb-3">
                            <label class="form-label small fw-bold">Urutkan</label>
                            <select name="sort" class="form-select form-select-sm">
                                <option value="featured" <?= $filters['sort'] === 'featured' ? 'selected' : '' ?>>Unggulan</option>
                                <option value="price_low" <?= $filters['sort'] === 'price_low' ? 'selected' : '' ?>>Harga Terendah</option>
                                <option value="price_high" <?= $filters['sort'] === 'price_high' ? 'selected' : '' ?>>Harga Tertinggi</option>
                                <option value="name" <?= $filters['sort'] === 'name' ? 'selected' : '' ?>>Nama A-Z</option>
                            </select>
                        </div>
                        <button type="submit" class="btn btn-primary btn-sm w-100">
                            <i class="fas fa-search me-1"></i>Filter
                        </button>
                        <a href="<?= View::url('products') ?>" class="btn btn-outline-secondary btn-sm w-100 mt-2">Reset</a>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-md-9">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <p class="text-muted mb-0">Menampilkan <strong><?= count($products) ?></strong> dari <strong><?= $total ?></strong> produk</p>
            </div>

            <?php if (empty($products)): ?>
            <div class="text-center py-5">
                <i class="fas fa-gift fa-4x text-muted mb-3"></i>
                <h4 class="text-muted">Tidak ada produk ditemukan</h4>
                <p class="text-muted">Coba ubah filter pencarian Anda</p>
            </div>
            <?php else: ?>
            <div class="row">
                <?php foreach ($products as $product): ?>
                <div class="col-md-4 mb-4">
                    <div class="card h-100 product-card shadow-sm">
                        <?php if (!empty($product['main_image'])): ?>
                        <img src="<?= View::asset('uploads/products/' . $product['main_image']) ?>" class="card-img-top" alt="<?= View::e($product['name']) ?>" style="height: 200px; object-fit: cover;">
                        <?php else: ?>
                        <div class="card-img-top bg-light d-flex align-items-center justify-content-center" style="height: 200px;">
                            <i class="fas fa-image fa-3x text-muted"></i>
                        </div>
                        <?php endif; ?>
                        <div class="card-body d-flex flex-column">
                            <div class="mb-1">
                                <?php if (!empty($product['category_name'])): ?>
                                <span class="badge bg-light text-dark"><i class="fas <?= View::e($product['category_icon'] ?? 'fa-gift') ?> me-1"></i><?= View::e($product['category_name']) ?></span>
                                <?php endif; ?>
                                <?php if ($product['is_featured']): ?>
                                <span class="badge bg-warning text-dark">Featured</span>
                                <?php endif; ?>
                            </div>
                            <h6 class="card-title"><?= View::e($product['name']) ?></h6>
                            <p class="text-muted small mb-1">
                                <i class="fas fa-map-marker-alt me-1"></i><?= View::e($product['region'] ?? 'Indonesia') ?>
                            </p>
                            <p class="card-text small text-muted flex-grow-1">
                                <?= View::e(substr($product['short_desc'] ?? $product['description'] ?? '', 0, 80)) ?>...
                            </p>
                            <div class="d-flex justify-content-between align-items-center mt-2">
                                <div>
                                    <?php if ($product['discount_price'] > 0): ?>
                                    <small class="text-decoration-line-through text-muted"><?= View::currency($product['price']) ?></small><br>
                                    <span class="fw-bold text-danger"><?= View::currency($product['discount_price']) ?></span>
                                    <?php else: ?>
                                    <span class="fw-bold text-primary"><?= View::currency($product['price']) ?></span>
                                    <?php endif; ?>
                                </div>
                                <small class="text-<?= $product['stock'] > 0 ? 'success' : 'danger' ?>">
                                    <?= $product['stock'] > 0 ? 'Stok: ' . $product['stock'] : 'Habis' ?>
                                </small>
                            </div>
                            <div class="mt-2">
                                <a href="<?= View::url('products/detail/' . $product['id']) ?>" class="btn btn-sm btn-outline-primary">
                                    <i class="fas fa-eye me-1"></i>Detail
                                </a>
                                <?php if ($product['stock'] > 0): ?>
                                <button class="btn btn-sm btn-success float-end" onclick="addToCart(<?= $product['id'] ?>)">
                                    <i class="fas fa-cart-plus me-1"></i>Keranjang
                                </button>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>

            <?php if ($totalPages > 1): ?>
            <nav class="mt-4">
                <ul class="pagination justify-content-center">
                    <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                    <li class="page-item <?= $page == $i ? 'active' : '' ?>">
                        <a class="page-link" href="<?= View::url('products') ?>?page=<?= $i ?><?= !empty($filters['search']) ? '&search=' . urlencode($filters['search']) : '' ?><?= !empty($filters['category_id']) ? '&category=' . $filters['category_id'] : '' ?><?= !empty($filters['region']) ? '&region=' . urlencode($filters['region']) : '' ?><?= !empty($filters['sort']) ? '&sort=' . $filters['sort'] : '' ?>"><?= $i ?></a>
                    </li>
                    <?php endfor; ?>
                </ul>
            </nav>
            <?php endif; ?>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
function addToCart(productId) {
    var formData = new FormData();
    formData.append('csrf_token', '<?= $csrf_token ?>');
    formData.append('product_id', productId);
    formData.append('quantity', 1);

    fetch(window.APP_URL + 'products/addToCart', {
        method: 'POST',
        headers: { 'X-Requested-With': 'XMLHttpRequest' },
        body: formData
    })
    .then(function(r) { return r.json(); })
    .then(function(data) {
        if (data.status === 'success') {
            Swal.fire({ icon: 'success', title: 'Berhasil', text: data.message, timer: 1200, showConfirmButton: false });
        } else {
            Swal.fire({ icon: 'error', title: 'Error', text: data.message });
        }
    });
}
</script>

<?php include APP_ROOT . '/app/views/layouts/footer.php'; ?>
