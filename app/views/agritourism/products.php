<?php include APP_ROOT . '/app/views/layouts/header.php'; ?>

<div class="container py-4">
    <div class="row mb-4">
        <div class="col-md-12">
            <h1 class="mb-3"><i class="fas fa-shopping-basket me-2"></i>Produk Farm</h1>
            <p class="text-muted">Produk segar langsung dari petani lokal</p>
        </div>
    </div>
    
    <!-- Search & Filter -->
    <div class="card mb-4 glass-card">
        <div class="card-body">
            <form method="GET" action="<?= View::url('agritourism/products') ?>">
                <div class="row">
                    <div class="col-md-4 mb-3">
                        <div class="form-floating">
                            <input type="text" class="form-control" name="search" id="search" placeholder="Cari produk..." value="<?= View::e($filters['search'] ?? '') ?>">
                            <label for="search">Cari produk...</label>
                        </div>
                    </div>
                    <div class="col-md-3 mb-3">
                        <div class="form-floating">
                            <select class="form-select" name="product_type" id="product_type">
                                <option value="">Semua Tipe</option>
                                <option value="vegetable">Sayuran</option>
                                <option value="fruit">Buah</option>
                                <option value="dairy">Susu & Produk Susu</option>
                                <option value="meat">Daging</option>
                                <option value="processed">Olahan</option>
                            </select>
                            <label for="product_type">Tipe Produk</label>
                        </div>
                    </div>
                    <div class="col-md-3 mb-3">
                        <div class="form-floating">
                            <select class="form-select" name="farm_id" id="farm_id">
                                <option value="">Semua Farm</option>
                                <?php foreach ($farms as $farm): ?>
                                    <option value="<?= $farm['id'] ?>"><?= View::e($farm['name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                            <label for="farm_id">Farm</label>
                        </div>
                    </div>
                    <div class="col-md-2 mb-3">
                        <button type="submit" class="btn btn-primary btn-modern w-100 h-100">
                            <i class="fas fa-search"></i>
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
    
    <!-- Products List -->
    <div class="row">
        <div class="col-md-12">
            <h3 class="mb-3 section-title"><i class="fas fa-apple-alt me-2"></i>Produk Tersedia</h3>
            <?php if (empty($products)): ?>
                <div class="empty-state text-center py-5">
                    <i class="fas fa-shopping-basket fa-3x text-muted mb-3"></i>
                    <h5 class="text-muted">Tidak ada produk ditemukan</h5>
                    <p class="text-muted">Coba ubah filter atau kata kunci pencarian</p>
                    <a href="<?= View::url('agritourism/products') ?>" class="btn btn-primary mt-2">
                        <i class="fas fa-redo me-2"></i>Reset Filter
                    </a>
                </div>
            <?php else: ?>
                <div class="row">
                    <?php foreach ($products as $product): ?>
                    <div class="col-md-4 mb-4">
                        <div class="card h-100 hover-shadow">
                            <?php if (!empty($product['image_url'])): ?>
                                <?php if (filter_var($product['image_url'], FILTER_VALIDATE_URL)): ?>
                                    <img src="<?= View::e($product['image_url']) ?>" class="card-img-top" alt="<?= View::e($product['name']) ?>" style="height: 200px; object-fit: cover;">
                                <?php else: ?>
                                    <img src="<?= View::asset('uploads/agritourism/' . $product['image_url']) ?>" class="card-img-top" alt="<?= View::e($product['name']) ?>" style="height: 200px; object-fit: cover;">
                                <?php endif; ?>
                            <?php else: ?>
                                <div class="card-img-top bg-warning d-flex align-items-center justify-content-center" style="height: 200px;">
                                    <i class="fas fa-apple-alt text-white" style="font-size: 3rem;"></i>
                                </div>
                            <?php endif; ?>
                            <div class="card-body">
                                <h5 class="card-title"><?= View::e($product['name']) ?></h5>
                                <p class="card-text text-muted small">
                                    <i class="fas fa-tag me-1"></i><?= ucfirst($product['product_type']) ?>
                                </p>
                                <p class="card-text small"><?= View::e(substr($product['description'], 0, 80)) ?>...</p>
                                <div class="d-flex justify-content-between align-items-center mt-2">
                                    <div class="text-primary fw-bold">
                                        <?= $product['display_price'] ?> / <?= $product['unit'] ?>
                                    </div>
                                    <span class="badge bg-<?= $product['available_quantity'] > 10 ? 'success' : 'warning' ?>">
                                        <?= $product['available_quantity'] ?> tersedia
                                    </span>
                                </div>
                                <?php if ($product['is_organic']): ?>
                                    <span class="badge bg-success mt-2">
                                        <i class="fas fa-leaf me-1"></i>Organic
                                    </span>
                                <?php endif; ?>
                                <button onclick="addToCart(<?= $product['id'] ?>)" class="btn btn-primary btn-modern mt-3 w-100">
                                    <i class="fas fa-cart-plus me-2"></i>Tambah ke Keranjang
                                </button>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
function addToCart(productId) {
    const formData = new FormData();
    formData.append('product_id', productId);
    formData.append('quantity', 1);
    formData.append('csrf_token', '<?= $csrf_token ?>');
    
    fetch('<?= View::url('cart/add') ?>', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(result => {
        if (result.status === 'success') {
            alert('Produk ditambahkan ke keranjang!');
        } else {
            alert('Gagal menambahkan: ' + result.message);
        }
    })
    .catch(error => {
        alert('Terjadi kesalahan: ' + error.message);
    });
}
</script>

<?php include APP_ROOT . '/app/views/layouts/footer.php'; ?>
