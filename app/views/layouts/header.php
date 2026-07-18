<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="<?= isset($description) ? View::e($description) : 'MyWisata - Platform Marketplace Pariwisata' ?>">
    <meta name="theme-color" content="#0d6efd">
    <link rel="manifest" href="<?= BASE_URL ?>public/manifest.json">
    <title><?= isset($title) ? View::e($title) : 'MyWisata' ?></title>
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    
    <!-- Custom CSS -->
    <link href="<?= View::asset('css/style.css') ?>" rel="stylesheet">
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container">
            <a class="navbar-brand" href="<?= View::url() ?>">
                <i class="fas fa-map-marked-alt me-2"></i>MyWisata
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="<?= View::url() ?>">Beranda</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="<?= View::url('home/about') ?>">Tentang</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="<?= View::url('home/contact') ?>">Kontak</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="<?= View::url('aitourguide') ?>"><i class="fas fa-robot me-1"></i>AI Guide</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="<?= View::url('audioguide') ?>"><i class="fas fa-headphones me-1"></i>Audio Guide</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="<?= View::url('itinerary/builder') ?>"><i class="fas fa-route me-1"></i>Itinerary</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="<?= View::url('desawisata') ?>"><i class="fas fa-house-chimney me-1"></i>Desa Wisata</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="<?= View::url('packages') ?>"><i class="fas fa-box me-1"></i>Paket Wisata</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="<?= View::url('events/calendar') ?>"><i class="fas fa-calendar-alt me-1"></i>Kalender Event</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="<?= View::url('translation/widget') ?>"><i class="fas fa-language me-1"></i>Translator</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="<?= View::url('products') ?>"><i class="fas fa-gift me-1"></i>Souvenir</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="<?= View::url('currency/scanner') ?>"><i class="fas fa-camera me-1"></i>Scan Harga</a>
                    </li>
                </ul>
                <ul class="navbar-nav">
                    <!-- Language Toggle -->
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" title="Bahasa / Language">
                            <i class="fas fa-globe me-1"></i><span id="currentLangLabel">ID</span>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end" style="max-height:400px;overflow-y:auto;min-width:200px;">
                            <li><h6 class="dropdown-header">Pilih Bahasa / Select Language</h6></li>
                            <?php
                            $db = Database::getInstance();
                            $langs = $db->query("SELECT code, name, native_name FROM languages ORDER BY id")->fetchAll();
                            foreach ($langs as $lang):
                            ?>
                            <li>
                                <a class="dropdown-item lang-option d-flex justify-content-between align-items-center" href="#" data-lang="<?= $lang['code'] ?>" onclick="MyWisataI18n.changeLanguage('<?= $lang['code'] ?>');return false;">
                                    <span><span class="me-2"><?= strtoupper($lang['code']) ?></span><?= View::e($lang['name']) ?> <small class="text-muted"><?= View::e($lang['native_name']) ?></small></span>
                                    <i class="fas fa-check text-success lang-check" style="visibility:hidden;"></i>
                                </a>
                            </li>
                            <?php endforeach; ?>
                        </ul>
                    </li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" title="Mata Uang">
                            <i class="fas fa-money-bill-wave me-1"></i><span id="navCurrencyCode"><?= CurrencyHelper::getUserCurrency() ?></span>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end" id="currencyDropdown">
                            <?php foreach (CurrencyHelper::getCurrencies() as $code => $cur): ?>
                            <li><a class="dropdown-item currency-switch" href="#" data-currency="<?= $code ?>">
                                <span class="me-2"><?= $cur['currency_symbol'] ?></span>
                                <?= $code ?> - <?= View::e($cur['currency_name']) ?>
                            </a></li>
                            <?php endforeach; ?>
                        </ul>
                    </li>
                    <?php if (Session::get('user_id')): ?>
                        <li class="nav-item">
                            <a class="nav-link position-relative" href="<?= View::url('messages') ?>">
                                <i class="fas fa-envelope me-1"></i>Pesan
                                <span class="badge bg-danger rounded-pill position-absolute top-0 start-100 translate-middle" id="navUnreadBadge" style="display:none; font-size: 10px;"></span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="<?= View::url('dashboard') ?>">
                                <i class="fas fa-tachometer-alt me-1"></i>Dashboard
                            </a>
                        </li>
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                                <i class="fas fa-user me-1"></i><?= View::e(Session::get('user_name', 'User')) ?>
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end">
                                <li><a class="dropdown-item" href="<?= View::url('dashboard') ?>"><i class="fas fa-tachometer-alt me-2"></i>Dashboard</a></li>
                                <li><a class="dropdown-item" href="<?= View::url('bookings') ?>"><i class="fas fa-calendar-check me-2"></i>Booking Saya</a></li>
                                <li><a class="dropdown-item" href="<?= View::url('favorites') ?>"><i class="fas fa-heart me-2"></i>Favorit</a></li>
                                <?php if (Session::get('role') === 'merchant'): ?>
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item" href="<?= View::url('merchant/dashboard') ?>"><i class="fas fa-store me-2"></i>Toko Saya</a></li>
                                <li><a class="dropdown-item" href="<?= View::url('merchant/products') ?>"><i class="fas fa-box me-2"></i>Produk Saya</a></li>
                                <?php endif; ?>
                                <?php if (Session::get('role') === 'admin'): ?>
                                    <li><hr class="dropdown-divider"></li>
                                    <li><a class="dropdown-item" href="<?= View::url('admin/dashboard') ?>"><i class="fas fa-cog me-2"></i>Admin Panel</a></li>
                                <?php elseif (Session::get('role') === 'tour_guide'): ?>
                                    <li><hr class="dropdown-divider"></li>
                                    <li><a class="dropdown-item" href="<?= View::url('tourguide/dashboard') ?>"><i class="fas fa-user-tie me-2"></i>Guide Panel</a></li>
                                <?php endif; ?>
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item text-danger" href="<?= View::url('auth/logout') ?>"><i class="fas fa-sign-out-alt me-2"></i>Keluar</a></li>
                            </ul>
                        </li>
                    <?php else: ?>
                        <li class="nav-item">
                            <a class="nav-link" href="<?= View::url('auth/login') ?>">
                                <i class="fas fa-sign-in-alt me-1"></i>Masuk
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="<?= View::url('auth/register') ?>">
                                <i class="fas fa-user-plus me-1"></i>Daftar
                            </a>
                        </li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>
    
    <!-- i18n Script -->
    <script src="<?= View::asset('js/i18n.js') ?>"></script>

    <!-- Main Content -->
    <main>
