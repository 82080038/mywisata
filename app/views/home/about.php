<?php include APP_ROOT . '/app/views/layouts/header.php'; ?>

<div class="container py-5">
    <h1 class="fw-bold mb-4"><?= View::e($title) ?></h1>
    <p class="lead mb-5"><?= View::e($description) ?></p>

    <div class="row g-4 mb-5">
        <div class="col-md-6">
            <h3><i class="fas fa-bullseye text-primary me-2"></i>Misi Kami</h3>
            <p class="text-muted">Menjadi platform marketplace pariwisata terdepan di Indonesia yang menghubungkan wisatawan dengan pemandu wisata profesional, destinasi menarik, hotel, restoran, dan event budaya dalam satu tempat.</p>
        </div>
        <div class="col-md-6">
            <h3><i class="fas fa-eye text-success me-2"></i>Visi Kami</h3>
            <p class="text-muted">Mendukung pertumbuhan ekonomi pariwisata lokal dengan teknologi yang mudah diakses, aman, dan terpercaya bagi seluruh pelaku pariwisata di Indonesia.</p>
        </div>
    </div>

    <div class="row g-4">
        <div class="col-md-3">
            <div class="card h-100 shadow-sm text-center">
                <div class="card-body">
                    <i class="fas fa-user-tie fa-2x text-primary mb-3"></i>
                    <h5>Pemandu Wisata</h5>
                    <p class="text-muted small mb-0">Temukan pemandu profesional berlisensi</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card h-100 shadow-sm text-center">
                <div class="card-body">
                    <i class="fas fa-map-marked-alt fa-2x text-success mb-3"></i>
                    <h5>Destinasi</h5>
                    <p class="text-muted small mb-0">Jelajahi destinasi wisata terbaik</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card h-100 shadow-sm text-center">
                <div class="card-body">
                    <i class="fas fa-hotel fa-2x text-info mb-3"></i>
                    <h5>Akomodasi</h5>
                    <p class="text-muted small mb-0">Hotel, homestay, dan villa</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card h-100 shadow-sm text-center">
                <div class="card-body">
                    <i class="fas fa-utensils fa-2x text-danger mb-3"></i>
                    <h5>Kuliner</h5>
                    <p class="text-muted small mb-0">Restoran dan kuliner lokal</p>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include APP_ROOT . '/app/views/layouts/footer.php'; ?>
