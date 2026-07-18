<?php include APP_ROOT . '/app/views/layouts/header.php'; ?>

<div class="container py-5">
    <h1 class="fw-bold mb-4"><?= View::e($title) ?></h1>
    <p class="lead mb-5"><?= View::e($description) ?></p>

    <div class="row g-4">
        <div class="col-md-5">
            <div class="card shadow-sm">
                <div class="card-body">
                    <h4 class="mb-4"><i class="fas fa-info-circle text-primary me-2"></i>Informasi Kontak</h4>
                    <p class="mb-3"><i class="fas fa-envelope me-3 text-muted"></i> admin@mywisata.com</p>
                    <p class="mb-3"><i class="fas fa-phone me-3 text-muted"></i> +62 812 3456 7890</p>
                    <p class="mb-3"><i class="fas fa-map-marker-alt me-3 text-muted"></i> Jakarta, Indonesia</p>
                    <hr>
                    <h5 class="mt-4">Jam Operasional</h5>
                    <p class="mb-1 text-muted">Senin - Jumat: 08:00 - 17:00 WIB</p>
                    <p class="mb-0 text-muted">Sabtu - Minggu: 09:00 - 15:00 WIB</p>
                </div>
            </div>
        </div>
        <div class="col-md-7">
            <div class="card shadow-sm">
                <div class="card-body">
                    <h4 class="mb-4"><i class="fas fa-paper-plane text-primary me-2"></i>Kirim Pesan</h4>
                    <form method="POST" action="<?= View::url('home/contact') ?>">
                        <input type="hidden" name="csrf_token" value="<?= Middleware::csrfToken() ?>">
                        <div class="mb-3">
                            <label class="form-label">Nama Lengkap</label>
                            <input type="text" name="name" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Email</label>
                            <input type="email" name="email" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Subjek</label>
                            <input type="text" name="subject" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Pesan</label>
                            <textarea name="message" rows="5" class="form-control" required></textarea>
                        </div>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-paper-plane me-2"></i>Kirim Pesan
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include APP_ROOT . '/app/views/layouts/footer.php'; ?>
