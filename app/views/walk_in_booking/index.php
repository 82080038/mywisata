<?php include APP_ROOT . '/app/views/layouts/header.php'; ?>

<div class="container py-4">
    <div class="row mb-4">
        <div class="col-md-12">
            <h1 class="mb-3"><i class="fas fa-bolt me-2"></i>Express Book (Walk-in)</h1>
            <p class="text-muted">Booking cepat untuk walk-in customers</p>
        </div>
    </div>
    
    <!-- Quick Booking Form -->
    <div class="row mb-4">
        <div class="col-md-8">
            <div class="card glass-card">
                <div class="card-body">
                    <h4 class="mb-3">Form Booking Cepat</h4>
                    <form id="walk-in-booking-form">
                        <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Tipe Booking</label>
                                <select class="form-select" name="booking_type" required>
                                    <option value="tour_guide">Tour Guide</option>
                                    <option value="destination">Destinasi</option>
                                    <option value="hotel">Hotel</option>
                                    <option value="restaurant">Restaurant</option>
                                    <option value="event">Event</option>
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Nama Customer</label>
                                <input type="text" class="form-control" name="customer_name" required>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">No. Telepon</label>
                                <input type="tel" class="form-control" name="customer_phone" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Email</label>
                                <input type="email" class="form-control" name="customer_email">
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Tanggal Booking</label>
                                <input type="date" class="form-control" name="booking_date" required value="<?= date('Y-m-d') ?>">
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Waktu</label>
                                <input type="time" class="form-control" name="booking_time" required>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Durasi (Jam)</label>
                                <input type="number" class="form-control" name="duration_hours" value="2" min="1" max="24">
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Jumlah Orang</label>
                                <input type="number" class="form-control" name="number_of_people" value="1" min="1" max="50" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Metode Pembayaran</label>
                                <select class="form-select" name="payment_method">
                                    <option value="cash">Cash</option>
                                    <option value="card">Card</option>
                                    <option value="qr">QRIS</option>
                                    <option value="transfer">Transfer</option>
                                </select>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Catatan</label>
                            <textarea class="form-control" name="notes" rows="2"></textarea>
                        </div>
                        
                        <div class="alert alert-info">
                            <strong>Total Estimasi: <span id="total-estimate">Rp 0</span></strong>
                        </div>
                        
                        <button type="submit" class="btn btn-primary btn-modern w-100">
                            <i class="fas fa-bolt me-2"></i>Booking Cepat
                        </button>
                    </form>
                </div>
            </div>
        </div>
        
        <div class="col-md-4">
            <!-- Quick Templates -->
            <div class="card glass-card">
                <div class="card-body">
                    <h5 class="card-title mb-3">Template Cepat</h5>
                    <div class="list-group">
                        <?php foreach ($templates as $template): ?>
                        <a href="#" class="list-group-item list-group-item-action" onclick="loadTemplate(<?= $template['id'] ?>)">
                            <div class="d-flex w-100 justify-content-between">
                                <h6 class="mb-1"><?= View::e($template['name']) ?></h6>
                                <small><?= $template['estimated_duration'] ?> jam</small>
                            </div>
                            <small class="text-muted"><?= View::e($template['description']) ?></small>
                        </a>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.getElementById('walk-in-booking-form').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    const data = Object.fromEntries(formData.entries());
    
    fetch('<?= View::url('walk-in-booking/create') ?>', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify(data)
    })
    .then(response => response.json())
    .then(result => {
        if (result.status === 'success') {
            alert('Booking berhasil! Kode: ' + result.booking_code);
            this.reset();
        } else {
            alert('Gagal booking: ' + result.message);
        }
    })
    .catch(error => {
        alert('Terjadi kesalahan: ' + error.message);
    });
});

function loadTemplate(templateId) {
    fetch('<?= View::url('walk-in-booking/template') ?>?id=' + templateId)
        .then(response => response.json())
        .then(result => {
            if (result.status === 'success') {
                const template = result.data;
                document.querySelector('select[name="booking_type"]').value = template.booking_type;
                document.querySelector('input[name="duration_hours"]').value = template.estimated_duration;
                document.querySelector('textarea[name="notes"]').value = template.default_notes;
            }
        });
}

// Update total estimate based on people count
document.querySelector('input[name="number_of_people"]').addEventListener('change', function() {
    const basePrice = 50000; // Base price per person
    const quantity = this.value;
    const total = basePrice * quantity;
    document.getElementById('total-estimate').textContent = 'Rp ' + total.toLocaleString('id-ID');
});
</script>

<?php include APP_ROOT . '/app/views/layouts/footer.php'; ?>
