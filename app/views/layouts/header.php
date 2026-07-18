<?php
// Get current language (Language helper already loaded in index.php)
$currentLang = Language::getLanguage();
?>
<!DOCTYPE html>
<html lang="<?= $currentLang ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="<?= Language::trans('home.subtitle') ?>">
    <title><?= isset($title) ? $title : Language::trans('home.welcome') ?></title>
    
    <!-- PWA Meta Tags -->
    <link rel="manifest" href="<?= BASE_URL ?>manifest.json">
    <meta name="theme-color" content="#007bff">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="apple-mobile-web-app-title" content="MyWisata">
    <link rel="apple-touch-icon" href="<?= View::asset('icons/icon-152x152.png') ?>">
    <link rel="icon" type="image/png" sizes="192x192" href="<?= View::asset('icons/icon-192x192.png') ?>">
    <link rel="icon" type="image/png" sizes="512x512" href="<?= View::asset('icons/icon-512x512.png') ?>">
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    
    <!-- Custom CSS -->
    <link href="<?= View::asset('css/style.css') ?>" rel="stylesheet">
    
    <!-- Skeleton Loading CSS -->
    <link href="<?= View::asset('css/skeleton.css') ?>" rel="stylesheet">
    
    <!-- PWA Scripts -->
    <script src="<?= View::asset('js/sw-registration.js') ?>"></script>
    <script src="<?= View::asset('js/indexeddb-helper.js') ?>"></script>
    <script src="<?= View::asset('js/push-notification.js') ?>"></script>
    
    <script>
        window.APP_URL = '<?= BASE_URL ?>';
    </script>
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary navbar-sticky">
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
                        <a class="nav-link" href="<?= View::url() ?>"><?= Language::trans('nav.home') ?></a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="<?= View::url('map') ?>"><i class="fas fa-map me-1"></i>Peta</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="<?= View::url('destinations') ?>"><i class="fas fa-landmark me-1"></i>Destinasi</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="<?= View::url('home/about') ?>"><?= Language::trans('nav.about') ?></a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="<?= View::url('home/contact') ?>"><?= Language::trans('nav.contact') ?></a>
                    </li>
                </ul>
                <ul class="navbar-nav">
                    <!-- Dark Mode Toggle -->
                    <li class="nav-item">
                        <button class="nav-link theme-toggle" aria-label="Toggle dark mode" aria-pressed="false">
                            <i class="fas fa-moon"></i>
                        </button>
                    </li>
                    <!-- Language Selector -->
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="langDropdown" role="button" data-bs-toggle="dropdown">
                            <i class="fas fa-globe me-1"></i><?= Language::trans('common.language') ?>
                        </a>
                        <ul class="dropdown-menu">
                            <li>
                                <a class="dropdown-item <?= $currentLang === 'id' ? 'active' : '' ?>" 
                                   href="<?= View::url('language/set/id') ?>">
                                    <i class="fas fa-flag me-2"></i><?= Language::trans('common.indonesian') ?>
                                </a>
                            </li>
                            <li>
                                <a class="dropdown-item <?= $currentLang === 'en' ? 'active' : '' ?>" 
                                   href="<?= View::url('language/set/en') ?>">
                                    <i class="fas fa-flag me-2"></i><?= Language::trans('common.english') ?>
                                </a>
                            </li>
                        </ul>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="<?= View::url('auth/login') ?>">
                            <i class="fas fa-sign-in-alt me-1"></i><?= Language::trans('nav.login') ?>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="<?= View::url('auth/register') ?>">
                            <i class="fas fa-user-plus me-1"></i><?= Language::trans('nav.register') ?>
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>
    
    <!-- Bottom Navigation for Mobile -->
    <nav class="bottom-nav d-md-none">
        <div class="nav-item">
            <a class="nav-link" href="<?= View::url() ?>">
                <i class="fas fa-home"></i>
                <span>Beranda</span>
            </a>
        </div>
        <div class="nav-item">
            <a class="nav-link" href="<?= View::url('destinations') ?>">
                <i class="fas fa-landmark"></i>
                <span>Destinasi</span>
            </a>
        </div>
        <div class="nav-item">
            <a class="nav-link" href="<?= View::url('map') ?>">
                <i class="fas fa-map"></i>
                <span>Peta</span>
            </a>
        </div>
        <div class="nav-item">
            <a class="nav-link" href="<?= View::url('favorites') ?>">
                <i class="fas fa-heart"></i>
                <span>Favorit</span>
            </a>
        </div>
        <div class="nav-item">
            <a class="nav-link" href="<?= View::url('auth/login') ?>">
                <i class="fas fa-user"></i>
                <span>Akun</span>
            </a>
        </div>
    </nav>
    
    <!-- Main Content -->
    <main>
