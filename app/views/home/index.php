<?php include APP_ROOT . '/app/views/layouts/header.php'; ?>

<div class="hero-section text-center py-5">
    <div class="hero-overlay">
        <div class="container">
            <h1 class="display-4 fw-bold mb-3 animate-slide-up"><?= Language::trans('home.welcome') ?></h1>
            <p class="lead mb-4 animate-slide-up" style="animation-delay: 0.1s"><?= Language::trans('home.subtitle') ?></p>
            
            <!-- Search Bar -->
            <div class="row justify-content-center mt-4 animate-slide-up" style="animation-delay: 0.2s">
                <div class="col-md-8">
                    <div class="search-autocomplete">
                        <div class="input-group input-group-lg">
                            <input type="text" class="form-control" placeholder="Cari destinasi, hotel, atau restoran..." aria-label="Search">
                            <button class="btn btn-primary btn-modern" type="button">
                                <i class="fas fa-search"></i>
                            </button>
                        </div>
                        <div class="suggestions"></div>
                    </div>
                </div>
            </div>
            
            <!-- Statistics -->
            <div class="row mt-5 pt-5">
                <div class="col-md-3 mb-4">
                    <div class="stat-card glass-card p-4">
                        <div class="stat-counter" data-target="500">0</div>
                        <p class="mb-0">Destinasi</p>
                    </div>
                </div>
                <div class="col-md-3 mb-4">
                    <div class="stat-card glass-card p-4">
                        <div class="stat-counter" data-target="1000">0</div>
                        <p class="mb-0">Tour Guide</p>
                    </div>
                </div>
                <div class="col-md-3 mb-4">
                    <div class="stat-card glass-card p-4">
                        <div class="stat-counter" data-target="50000">0</div>
                        <p class="mb-0">Pengguna</p>
                    </div>
                </div>
                <div class="col-md-3 mb-4">
                    <div class="stat-card glass-card p-4">
                        <div class="stat-counter" data-target="100000">0</div>
                        <p class="mb-0">Booking</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="container py-5">
    <div class="row mt-5">
        <div class="col-md-4 mb-4">
            <a href="<?= View::url('tourguides') ?>" class="text-decoration-none">
                <div class="card h-100 glass-card hover-shadow">
                    <div class="card-body text-center">
                        <i class="fas fa-user-tie fa-3x text-primary mb-3"></i>
                        <h5 class="card-title"><?= Language::trans('home.card_tour_guide_title') ?></h5>
                        <p class="card-text"><?= Language::trans('home.card_tour_guide_desc') ?></p>
                    </div>
                </div>
            </a>
        </div>
        <div class="col-md-4 mb-4">
            <a href="<?= View::url('destinations') ?>" class="text-decoration-none">
                <div class="card h-100 glass-card hover-shadow">
                    <div class="card-body text-center">
                        <i class="fas fa-map-marked-alt fa-3x text-success mb-3"></i>
                        <h5 class="card-title"><?= Language::trans('home.card_destination_title') ?></h5>
                        <p class="card-text"><?= Language::trans('home.card_destination_desc') ?></p>
                    </div>
                </div>
            </a>
        </div>
        <div class="col-md-4 mb-4">
            <a href="<?= View::url('destinations') ?>" class="text-decoration-none">
                <div class="card h-100 glass-card hover-shadow">
                    <div class="card-body text-center">
                        <i class="fas fa-ticket-alt fa-3x text-warning mb-3"></i>
                        <h5 class="card-title"><?= Language::trans('home.card_ticket_title') ?></h5>
                        <p class="card-text"><?= Language::trans('home.card_ticket_desc') ?></p>
                    </div>
                </div>
            </a>
        </div>
    </div>
    <div class="row mt-3">
        <div class="col-md-4 mb-4">
            <a href="<?= View::url('hotels') ?>" class="text-decoration-none">
                <div class="card h-100 glass-card hover-shadow">
                    <div class="card-body text-center">
                        <i class="fas fa-hotel fa-3x text-info mb-3"></i>
                        <h5 class="card-title"><?= Language::trans('home.card_hotel_title') ?></h5>
                        <p class="card-text"><?= Language::trans('home.card_hotel_desc') ?></p>
                    </div>
                </div>
            </a>
        </div>
        <div class="col-md-4 mb-4">
            <a href="<?= View::url('restaurants') ?>" class="text-decoration-none">
                <div class="card h-100 glass-card hover-shadow">
                    <div class="card-body text-center">
                        <i class="fas fa-utensils fa-3x text-danger mb-3"></i>
                        <h5 class="card-title"><?= Language::trans('home.card_restaurant_title') ?></h5>
                        <p class="card-text"><?= Language::trans('home.card_restaurant_desc') ?></p>
                    </div>
                </div>
            </a>
        </div>
        <div class="col-md-4 mb-4">
            <a href="<?= View::url('events') ?>" class="text-decoration-none">
                <div class="card h-100 glass-card hover-shadow">
                    <div class="card-body text-center">
                        <i class="fas fa-calendar-alt fa-3x text-secondary mb-3"></i>
                        <h5 class="card-title"><?= Language::trans('home.card_event_title') ?></h5>
                        <p class="card-text"><?= Language::trans('home.card_event_desc') ?></p>
                    </div>
                </div>
            </a>
        </div>
    </div>
</div>

<?php include APP_ROOT . '/app/views/layouts/footer.php'; ?>
