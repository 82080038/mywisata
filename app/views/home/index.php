<?php include APP_ROOT . '/app/views/layouts/header.php'; ?>

<div class="hero-section text-center py-5">
    <div class="container">
        <h1 class="display-4 fw-bold mb-3"><?= View::e($title) ?></h1>
        <p class="lead mb-4"><?= View::e($description) ?></p>
        <div class="row mt-5">
            <div class="col-md-4 mb-4">
                <div class="card h-100 shadow-sm">
                    <div class="card-body text-center">
                        <i class="fas fa-user-tie fa-3x text-primary mb-3"></i>
                        <h5 class="card-title">Tour Guide</h5>
                        <p class="card-text">Temukan pemandu wisata profesional untuk perjalanan Anda</p>
                    </div>
                </div>
            </div>
            <div class="col-md-4 mb-4">
                <div class="card h-100 shadow-sm">
                    <div class="card-body text-center">
                        <i class="fas fa-map-marked-alt fa-3x text-success mb-3"></i>
                        <h5 class="card-title">Destinasi</h5>
                        <p class="card-text">Jelajahi destinasi wisata terbaik di Indonesia</p>
                    </div>
                </div>
            </div>
            <div class="col-md-4 mb-4">
                <div class="card h-100 shadow-sm">
                    <div class="card-body text-center">
                        <i class="fas fa-ticket-alt fa-3x text-warning mb-3"></i>
                        <h5 class="card-title">Tiket</h5>
                        <p class="card-text">Beli tiket wisata dengan mudah dan cepat</p>
                    </div>
                </div>
            </div>
        </div>
        <div class="row mt-3">
            <div class="col-md-4 mb-4">
                <div class="card h-100 shadow-sm">
                    <div class="card-body text-center">
                        <i class="fas fa-hotel fa-3x text-info mb-3"></i>
                        <h5 class="card-title">Hotel</h5>
                        <p class="card-text">Booking hotel dan homestay dengan harga terbaik</p>
                    </div>
                </div>
            </div>
            <div class="col-md-4 mb-4">
                <div class="card h-100 shadow-sm">
                    <div class="card-body text-center">
                        <i class="fas fa-utensils fa-3x text-danger mb-3"></i>
                        <h5 class="card-title">Restoran</h5>
                        <p class="card-text">Temukan kuliner lokal dan restoran terbaik</p>
                    </div>
                </div>
            </div>
            <div class="col-md-4 mb-4">
                <div class="card h-100 shadow-sm">
                    <div class="card-body text-center">
                        <i class="fas fa-calendar-alt fa-3x text-secondary mb-3"></i>
                        <h5 class="card-title">Event</h5>
                        <p class="card-text">Daftar event budaya dan festival menarik</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include APP_ROOT . '/app/views/layouts/footer.php'; ?>
