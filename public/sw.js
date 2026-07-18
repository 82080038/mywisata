const CACHE_NAME = 'mywisata-v3';
const STATIC_ASSETS = [
    '/mywisata/',
    '/mywisata/destinations',
    '/mywisata/hotels',
    '/mywisata/restaurants',
    '/mywisata/events',
    '/mywisata/desawisata',
    '/mywisata/packages',
    '/mywisata/itinerary/builder',
    '/mywisata/public/offline.html',
    'https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css',
    'https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js',
    'https://code.jquery.com/jquery-3.7.0.min.js',
    'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css',
];

self.addEventListener('install', function (event) {
    event.waitUntil(
        caches.open(CACHE_NAME).then(function (cache) {
            return cache.addAll(STATIC_ASSETS).catch(function (err) {
                console.log('Cache addAll error:', err);
            });
        })
    );
    self.skipWaiting();
});

self.addEventListener('activate', function (event) {
    event.waitUntil(
        caches.keys().then(function (cacheNames) {
            return Promise.all(
                cacheNames.filter(function (name) {
                    return name !== CACHE_NAME;
                }).map(function (name) {
                    return caches.delete(name);
                })
            );
        })
    );
    self.clients.claim();
});

self.addEventListener('fetch', function (event) {
    // Skip non-GET requests
    if (event.request.method !== 'GET') return;

    // Skip API and AJAX requests
    if (event.request.headers.get('X-Requested-With') === 'XMLHttpRequest') return;

    // Network-first for navigation requests, never cache HTML pages
    if (event.request.mode === 'navigate') {
        event.respondWith(
            fetch(event.request).then(function (response) {
                // Don't cache navigation responses - always fetch fresh
                return response;
            }).catch(function () {
                return caches.match(event.request).then(function (response) {
                    return response || caches.match('/mywisata/public/offline.html');
                });
            })
        );
        return;
    }

    // Cache-first for static assets
    if (event.request.url.includes('/public/') ||
        event.request.url.includes('cdn.jsdelivr.net') ||
        event.request.url.includes('cdnjs.cloudflare.com') ||
        event.request.url.includes('code.jquery.com')) {
        event.respondWith(
            caches.match(event.request).then(function (response) {
                return response || fetch(event.request).then(function (response) {
                    if (response && response.status === 200) {
                        var responseClone = response.clone();
                        caches.open(CACHE_NAME).then(function (cache) {
                            cache.put(event.request, responseClone);
                        });
                    }
                    return response;
                });
            })
        );
        return;
    }

    // Network-first for everything else
    event.respondWith(
        fetch(event.request).catch(function () {
            return caches.match(event.request);
        })
    );
});
