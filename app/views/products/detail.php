<?php include APP_ROOT . '/app/views/layouts/header.php'; ?>

<div class="container py-4">
    <nav aria-label="breadcrumb" class="mb-3">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="<?= View::url() ?>">Beranda</a></li>
            <li class="breadcrumb-item"><a href="<?= View::url('products') ?>">Souvenir</a></li>
            <li class="breadcrumb-item active"><?= View::e($product['name']) ?></li>
        </ol>
    </nav>

    <div class="row">
        <div class="col-md-5">
            <div class="card shadow-sm mb-3">
                <?php if (!empty($product['main_image'])): ?>
                <img src="<?= View::asset('uploads/products/' . $product['main_image']) ?>" class="card-img-top" alt="<?= View::e($product['name']) ?>" style="max-height: 400px; object-fit: contain;">
                <?php else: ?>
                <div class="card-img-top bg-light d-flex align-items-center justify-content-center" style="height: 400px;">
                    <i class="fas fa-image fa-5x text-muted"></i>
                </div>
                <?php endif; ?>
            </div>

            <?php if (!empty($images)): ?>
            <div class="row">
                <?php foreach ($images as $img): ?>
                <div class="col-3 mb-2">
                    <img src="<?= View::asset('uploads/products/' . $img['image']) ?>" class="img-thumbnail" style="height: 80px; object-fit: cover;">
                </div>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
        </div>

        <div class="col-md-7">
            <div class="mb-2">
                <?php if (!empty($product['category_name'])): ?>
                <span class="badge bg-light text-dark"><i class="fas <?= View::e($product['category_icon'] ?? 'fa-gift') ?> me-1"></i><?= View::e($product['category_name']) ?></span>
                <?php endif; ?>
                <?php if ($product['is_featured']): ?>
                <span class="badge bg-warning text-dark"><i class="fas fa-star me-1"></i>Featured</span>
                <?php endif; ?>
            </div>

            <h2 class="mb-2"><?= View::e($product['name']) ?></h2>

            <p class="text-muted mb-3">
                <i class="fas fa-map-marker-alt me-1"></i>Daerah: <strong><?= View::e($product['region'] ?? 'Indonesia') ?></strong>
                <?php if (!empty($product['destination_name'])): ?>
                <br><i class="fas fa-place-of-worship me-1"></i>Destinasi: <a href="<?= View::url('destinations/detail/' . $product['destination_id']) ?>"><?= View::e($product['destination_name']) ?></a>
                <?php endif; ?>
                <?php if (!empty($product['sku'])): ?>
                <br><i class="fas fa-barcode me-1"></i>SKU: <?= View::e($product['sku']) ?>
                <?php endif; ?>
            </p>

            <div class="card bg-light mb-3">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <?php if ($product['discount_price'] > 0): ?>
                            <small class="text-decoration-line-through text-muted"><?= View::currency($product['price']) ?></small>
                            <h3 class="text-danger mb-0"><?= View::currency($product['discount_price']) ?></h3>
                            <?php else: ?>
                            <h3 class="text-primary mb-0"><?= View::currency($product['price']) ?></h3>
                            <?php endif; ?>
                        </div>
                        <div class="text-end">
                            <?php if ($product['stock'] > 10): ?>
                            <span class="badge bg-success"><i class="fas fa-check-circle me-1"></i>Stok: <?= $product['stock'] ?></span>
                            <?php elseif ($product['stock'] > 0): ?>
                            <span class="badge bg-warning text-dark"><i class="fas fa-exclamation-triangle me-1"></i>Tersisa <?= $product['stock'] ?>!</span>
                            <?php else: ?>
                            <span class="badge bg-danger"><i class="fas fa-times-circle me-1"></i>Stok Habis</span>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>

            <div class="mb-3">
                <h5>Deskripsi</h5>
                <p><?= nl2br(View::e($product['description'] ?? '')) ?></p>
            </div>

            <?php if ($product['stock'] > 0): ?>
            <div class="card shadow-sm">
                <div class="card-body">
                    <!-- Variant Selection -->
                    <?php if (!empty($variants)): ?>
                    <div class="mb-3">
                        <label class="form-label fw-bold"><i class="fas fa-layer-group me-1 text-primary"></i>Pilih Varian:</label>
                        <div class="row g-2">
                            <?php foreach ($variants as $idx => $variant): ?>
                            <div class="col-md-4">
                                <label class="card variant-card h-100 <?= $idx === 0 ? 'border-primary' : '' ?>" 
                                       style="cursor: pointer; transition: all 0.2s;" 
                                       onclick="selectVariant(<?= $variant['id'] ?>, <?= $variant['price'] ?>, <?= $variant['stock'] ?>, '<?= View::e($variant['name']) ?>')">
                                    <div class="card-body p-2">
                                        <div class="form-check">
                                            <input class="form-check-input" type="radio" name="variant_id" value="<?= $variant['id'] ?>" id="v<?= $variant['id'] ?>" <?= $idx === 0 ? 'checked' : '' ?>>
                                            <label class="form-check-label small fw-bold" for="v<?= $variant['id'] ?>">
                                                <?= View::e($variant['name']) ?>
                                            </label>
                                        </div>
                                        <div class="ps-4">
                                            <span class="fw-bold text-success"><?= View::currency($variant['price']) ?></span><br>
                                            <?php if ($variant['stock'] > 10): ?>
                                            <span class="badge bg-success" style="font-size:10px;">Stok: <?= $variant['stock'] ?></span>
                                            <?php elseif ($variant['stock'] > 0): ?>
                                            <span class="badge bg-warning text-dark" style="font-size:10px;">Tersisa <?= $variant['stock'] ?></span>
                                            <?php else: ?>
                                            <span class="badge bg-danger" style="font-size:10px;">Habis</span>
                                            <?php endif; ?>
                                            <div class="mt-1"><?= $variantModel->formatAttributes($variant) ?></div>
                                        </div>
                                    </div>
                                </label>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    <hr>
                    <?php else: ?>
                    <input type="hidden" name="variant_id" value="0" id="variant_id">
                    <?php endif; ?>

                    <div class="row align-items-end">
                        <div class="col-md-4 mb-2">
                            <label class="form-label">Jumlah</label>
                            <input type="number" id="quantity" class="form-control" value="1" min="1" max="<?= $product['stock'] ?>">
                        </div>
                        <div class="col-md-8">
                            <button class="btn btn-success btn-lg w-100" onclick="addToCart()">
                                <i class="fas fa-cart-plus me-2"></i>Tambah ke Keranjang
                            </button>
                        </div>
                    </div>
                    <?php if (!empty($product['seller_id'])): ?>
                    <?php if (Session::get('user_id') && Session::get('user_id') != $product['seller_id']): ?>
                    <a href="<?= View::url('messages/compose?to=' . $product['seller_id'] . '&context=product&context_id=' . $product['id']) ?>" 
                       class="btn btn-outline-info btn-sm w-100 mt-2">
                        <i class="fas fa-comments me-1"></i>Chat dengan Penjual
                    </a>
                    <?php elseif (!Session::get('user_id')): ?>
                    <a href="<?= View::url('auth/login') ?>" class="btn btn-outline-info btn-sm w-100 mt-2">
                        <i class="fas fa-sign-in-alt me-1"></i>Login untuk Chat Penjual
                    </a>
                    <?php endif; ?>
                    <?php endif; ?>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <?php if (!empty($related)): ?>
    <div class="row mt-5">
        <div class="col-12">
            <h4 class="mb-3">Produk Terkait</h4>
        </div>
        <?php foreach ($related as $rel): ?>
        <?php if ($rel['id'] != $product['id']): ?>
        <div class="col-md-3 mb-3">
            <div class="card h-100 shadow-sm">
                <?php if (!empty($rel['main_image'])): ?>
                <img src="<?= View::asset('uploads/products/' . $rel['main_image']) ?>" class="card-img-top" style="height: 150px; object-fit: cover;">
                <?php else: ?>
                <div class="card-img-top bg-light d-flex align-items-center justify-content-center" style="height: 150px;">
                    <i class="fas fa-image fa-2x text-muted"></i>
                </div>
                <?php endif; ?>
                <div class="card-body">
                    <h6 class="card-title"><?= View::e($rel['name']) ?></h6>
                    <p class="fw-bold text-primary mb-2"><?= View::currency($rel['price']) ?></p>
                    <a href="<?= View::url('products/detail/' . $rel['id']) ?>" class="btn btn-sm btn-outline-primary w-100">Lihat</a>
                </div>
            </div>
        </div>
        <?php endif; ?>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>
</div>

<script>
var selectedVariantId = 0;
var selectedVariantPrice = <?= $product['price'] ?>;
var selectedVariantStock = <?= $product['stock'] ?>;

function selectVariant(id, price, stock, name) {
    selectedVariantId = id;
    selectedVariantPrice = price;
    selectedVariantStock = stock;

    // Update radio
    document.querySelectorAll('input[name="variant_id"]').forEach(function(r) { r.checked = false; });
    var radio = document.getElementById('v' + id);
    if (radio) radio.checked = true;

    // Update card border
    document.querySelectorAll('.variant-card').forEach(function(c) { c.classList.remove('border-primary'); });
    event.currentTarget.classList.add('border-primary');

    // Update quantity max
    var qtyInput = document.getElementById('quantity');
    qtyInput.max = stock > 0 ? stock : 1;
    if (parseInt(qtyInput.value) > stock) qtyInput.value = stock;

    // Update display price
    var priceEl = document.getElementById('displayPrice');
    if (priceEl) priceEl.innerHTML = formatRupiah(price);
}

function formatRupiah(num) {
    return 'Rp ' + num.toLocaleString('id-ID');
}

function addToCart() {
    var qty = document.getElementById('quantity').value;
    var formData = new FormData();
    formData.append('csrf_token', '<?= $csrf_token ?>');
    formData.append('product_id', <?= $product['id'] ?>);
    formData.append('quantity', qty);
    if (selectedVariantId > 0) {
        formData.append('variant_id', selectedVariantId);
    }

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
