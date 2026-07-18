<?php include APP_ROOT . '/app/views/layouts/header.php'; ?>

<div class="container py-4">
    <div class="row">
        <div class="col-md-8">
            <!-- Package Details -->
            <div class="card mb-4 glass-card">
                <div class="card-body">
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="<?= View::url('home') ?>">Home</a></li>
                            <li class="breadcrumb-item"><a href="<?= View::url('religious-tourism') ?>">Wisata Religi</a></li>
                            <li class="breadcrumb-item active"><?= View::e($package['name']) ?></li>
                        </ol>
                    </nav>
                    
                    <?php if (!empty($package['image_url'])): ?>
                        <?php if (filter_var($package['image_url'], FILTER_VALIDATE_URL)): ?>
                            <img src="<?= View::e($package['image_url']) ?>" class="img-fluid rounded mb-3" alt="<?= View::e($package['name']) ?>">
                        <?php else: ?>
                            <img src="<?= View::asset('uploads/religious_tourism/' . $package['image_url']) ?>" class="img-fluid rounded mb-3" alt="<?= View::e($package['name']) ?>">
                        <?php endif; ?>
                    <?php endif; ?>
                    
                    <h1 class="mb-3"><?= View::e($package['name']) ?></h1>
                    <div class="d-flex align-items-center mb-3">
                        <span class="badge bg-info me-2">
                            <i class="fas fa-map-marker-alt me-1"></i><?= ucfirst($package['destination_type']) ?>
                        </span>
                        <span class="badge bg-primary">
                            <i class="fas fa-clock me-1"></i><?= $package['duration_days'] ?> Hari
                        </span>
                    </div>
                    
                    <h4 class="mt-4">Deskripsi</h4>
                    <p><?= nl2br(View::e($package['description'])) ?></p>
                    
                    <h4 class="mt-4">Fasilitas</h4>
                    <div class="row">
                        <div class="col-md-6">
                            <ul class="list-unstyled">
                                <li><i class="fas fa-check text-success me-2"></i>Akomodasi Hotel Bintang 4+</li>
                                <li><i class="fas fa-check text-success me-2"></i>Transportasi AC</li>
                                <li><i class="fas fa-check text-success me-2"></i>Makanan Halal</li>
                            </ul>
                        </div>
                        <div class="col-md-6">
                            <ul class="list-unstyled">
                                <li><i class="fas fa-check text-success me-2"></i>Guide Berbahasa Indonesia</li>
                                <li><i class="fas fa-check text-success me-2"></i>Visa Processing</li>
                                <li><i class="fas fa-check text-success me-2"></i>Asuransi Perjalanan</li>
                            </ul>
                        </div>
                    </div>
                    
                    <h4 class="mt-4">Termasuk</h4>
                    <p><?= nl2br(View::e($package['includes'])) ?></p>
                    
                    <h4 class="mt-4">Tidak Termasuk</h4>
                    <p><?= nl2br(View::e($package['excludes'])) ?></p>
                </div>
            </div>
        </div>
        
        <div class="col-md-4">
            <!-- Booking Card -->
            <div class="card glass-card sticky-top" style="top: 20px;">
                <div class="card-body">
                    <h4 class="mb-3">Booking Paket</h4>
                    <div class="mb-3">
                        <label class="form-label">Harga per Orang</label>
                        <div class="display-4 text-primary fw-bold"><?= $package['display_price'] ?></div>
                    </div>
                    
                    <form id="booking-form">
                        <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">
                        <input type="hidden" name="package_id" value="<?= $package['id'] ?>">
                        
                        <div class="mb-3">
                            <label class="form-label">Tanggal Keberangkatan</label>
                            <input type="date" class="form-control" name="departure_date" required min="<?= date('Y-m-d', strtotime('+1 month')) ?>">
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Tanggal Kembali</label>
                            <input type="date" class="form-control" name="return_date" required>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Jumlah Jamaah</label>
                            <input type="number" class="form-control" name="number_of_pilgrims" value="1" min="1" max="50" required>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Preferensi Kamar</label>
                            <select class="form-select" name="room_preference">
                                <option value="quad">Quad (4 orang)</option>
                                <option value="triple">Triple (3 orang)</option>
                                <option value="double">Double (2 orang)</option>
                                <option value="single">Single (1 orang)</option>
                            </select>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Nama Group Leader</label>
                            <input type="text" class="form-control" name="group_leader_name" required>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">No. Telepon</label>
                            <input type="tel" class="form-control" name="group_leader_phone" required>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Email</label>
                            <input type="email" class="form-control" name="group_leader_email" required>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Medical Requirements</label>
                            <textarea class="form-control" name="medical_requirements" rows="2" placeholder="Jika ada kondisi medis khusus..."></textarea>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Dietary Requirements</label>
                            <textarea class="form-control" name="dietary_requirements" rows="2" placeholder="Alergi makanan, preferensi diet..."></textarea>
                        </div>
                        
                        <div class="alert alert-info">
                            <strong>Total: <span id="total-price"><?= $package['display_price'] ?></span></strong>
                        </div>
                        
                        <button type="submit" class="btn btn-primary btn-modern w-100">
                            <i class="fas fa-calendar-check me-2"></i>Booking Sekarang
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.getElementById('booking-form').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    const data = Object.fromEntries(formData.entries());
    
    fetch('<?= View::url('religious-tourism/book') ?>', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify(data)
    })
    .then(response => response.json())
    .then(result => {
        if (result.status === 'success') {
            alert('Booking berhasil! Kode transaksi: ' + result.transaction_code);
            window.location.href = '<?= View::url('payment/index?code=' . result.transaction_code) ?>';
        } else {
            alert('Gagal booking: ' + result.message);
        }
    })
    .catch(error => {
        alert('Terjadi kesalahan: ' + error.message);
    });
});

document.querySelector('input[name="number_of_pilgrims"]').addEventListener('change', function() {
    const pricePerPerson = <?= str_replace(['Rp', '.'], '', $package['display_price']) ?>;
    const quantity = this.value;
    const total = pricePerPerson * quantity;
    document.getElementById('total-price').textContent = 'Rp ' + total.toLocaleString('id-ID');
});
</script>

<?php include APP_ROOT . '/app/views/layouts/footer.php'; ?>
