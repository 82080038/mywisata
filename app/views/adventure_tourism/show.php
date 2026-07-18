<?php include APP_ROOT . '/app/views/layouts/header.php'; ?>

<div class="container py-4">
    <div class="row">
        <div class="col-md-8">
            <!-- Activity Details -->
            <div class="card mb-4 glass-card">
                <div class="card-body">
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="<?= View::url('home') ?>">Home</a></li>
                            <li class="breadcrumb-item"><a href="<?= View::url('adventure-tourism') ?>">Adventure Tourism</a></li>
                            <li class="breadcrumb-item active"><?= View::e($activity['name']) ?></li>
                        </ol>
                    </nav>
                    
                    <?php if (!empty($activity['image_url'])): ?>
                        <?php if (filter_var($activity['image_url'], FILTER_VALIDATE_URL)): ?>
                            <img src="<?= View::e($activity['image_url']) ?>" class="img-fluid rounded mb-3" alt="<?= View::e($activity['name']) ?>">
                        <?php else: ?>
                            <img src="<?= View::asset('uploads/adventure_tourism/' . $activity['image_url']) ?>" class="img-fluid rounded mb-3" alt="<?= View::e($activity['name']) ?>">
                        <?php endif; ?>
                    <?php endif; ?>
                    
                    <h1 class="mb-3"><?= View::e($activity['name']) ?></h1>
                    <div class="d-flex align-items-center mb-3">
                        <span class="badge bg-info me-2">
                            <i class="fas fa-tag me-1"></i><?= ucfirst($activity['activity_type']) ?>
                        </span>
                        <span class="badge bg-<?= $activity['difficulty_level'] === 'easy' ? 'success' : ($activity['difficulty_level'] === 'extreme' ? 'danger' : 'warning') ?>">
                            <i class="fas fa-signal me-1"></i><?= ucfirst($activity['difficulty_level']) ?>
                        </span>
                        <span class="badge bg-primary">
                            <i class="fas fa-clock me-1"></i><?= $activity['duration_hours'] ?> Jam
                        </span>
                    </div>
                    
                    <h4 class="mt-4">Deskripsi</h4>
                    <p><?= nl2br(View::e($activity['description'])) ?></p>
                    
                    <h4 class="mt-4">Fasilitas</h4>
                    <div class="row">
                        <div class="col-md-6">
                            <ul class="list-unstyled">
                                <li><i class="fas fa-check text-success me-2"></i>Peralatan Safety</li>
                                <li><i class="fas fa-check text-success me-2"></i>Guide Profesional</li>
                                <li><i class="fas fa-check text-success me-2"></i>Asuransi</li>
                            </ul>
                        </div>
                        <div class="col-md-6">
                            <ul class="list-unstyled">
                                <li><i class="fas fa-check text-success me-2"></i>Transportasi</li>
                                <li><i class="fas fa-check text-success me-2"></i>Makanan & Minuman</li>
                                <li><i class="fas fa-check text-success me-2"></i>Dokumentasi</li>
                            </ul>
                        </div>
                    </div>
                    
                    <h4 class="mt-4">Persyaratan</h4>
                    <p><?= nl2br(View::e($activity['requirements'])) ?></p>
                    
                    <h4 class="mt-4">Termasuk</h4>
                    <p><?= nl2br(View::e($activity['includes'])) ?></p>
                    
                    <h4 class="mt-4">Tidak Termasuk</h4>
                    <p><?= nl2br(View::e($activity['excludes'])) ?></p>
                </div>
            </div>
        </div>
        
        <div class="col-md-4">
            <!-- Booking Card -->
            <div class="card glass-card sticky-top" style="top: 20px;">
                <div class="card-body">
                    <h4 class="mb-3">Booking Aktivitas</h4>
                    <div class="mb-3">
                        <label class="form-label">Harga per Orang</label>
                        <div class="display-4 text-primary fw-bold"><?= $activity['display_price'] ?></div>
                    </div>
                    
                    <form id="booking-form">
                        <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">
                        <input type="hidden" name="activity_id" value="<?= $activity['id'] ?>">
                        
                        <div class="mb-3">
                            <label class="form-label">Tanggal Aktivitas</label>
                            <input type="date" class="form-control" name="activity_date" required min="<?= date('Y-m-d') ?>">
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Waktu</label>
                            <input type="time" class="form-control" name="activity_time" required>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Jumlah Peserta</label>
                            <input type="number" class="form-control" name="number_of_participants" value="1" min="1" max="20" required>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Rental Equipment</label>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="equipment_rental" id="equipment_rental">
                                <label class="form-check-label" for="equipment_rental">
                                    Ya, rental equipment (+<?= $activity['equipment_rental_price'] ?>)
                                </label>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Medical Conditions</label>
                            <textarea class="form-control" name="medical_conditions" rows="2" placeholder="Jika ada kondisi medis khusus..."></textarea>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Dietary Requirements</label>
                            <textarea class="form-control" name="dietary_requirements" rows="2" placeholder="Alergi makanan, preferensi diet..."></textarea>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Nama Kontak</label>
                            <input type="text" class="form-control" name="contact_person_name" required>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">No. Telepon</label>
                            <input type="tel" class="form-control" name="contact_person_phone" required>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Email</label>
                            <input type="email" class="form-control" name="contact_person_email" required>
                        </div>
                        
                        <div class="alert alert-info">
                            <strong>Total: <span id="total-price"><?= $activity['display_price'] ?></span></strong>
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
    data.equipment_rental = document.querySelector('input[name="equipment_rental"]').checked ? 1 : 0;
    
    fetch('<?= View::url('adventure-tourism/book') ?>', {
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

document.querySelector('input[name="number_of_participants"]').addEventListener('change', updateTotal);
document.querySelector('input[name="equipment_rental"]').addEventListener('change', updateTotal);

function updateTotal() {
    const pricePerPerson = <?= str_replace(['Rp', '.'], '', $activity['display_price']) ?>;
    const equipmentPrice = <?= str_replace(['Rp', '.'], '', $activity['equipment_rental_price']) ?>;
    const quantity = document.querySelector('input[name="number_of_participants"]').value;
    const equipmentRental = document.querySelector('input[name="equipment_rental"]').checked ? 1 : 0;
    const total = (pricePerPerson * quantity) + (equipmentPrice * equipmentRental);
    document.getElementById('total-price').textContent = 'Rp ' + total.toLocaleString('id-ID');
}
</script>

<?php include APP_ROOT . '/app/views/layouts/footer.php'; ?>
