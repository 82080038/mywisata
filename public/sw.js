/**
 * MyWisata Application - Service Worker
 * 
 * Progressive Web App service worker for offline functionality
 * Optimized for mobile devices
 * 
 * @package MyWisata
 * @version 1.0.0
 * @since 2026-07-16
 */

const CACHE_NAME = 'mywisata-v1';
const OFFLINE_URL = '/offline.html';

// Assets to cache for offline use (mobile-optimized)
const CACHE_ASSETS = [
    '/',
    '/offline.html',
    '/assets/css/style.css',
    '/assets/js/main.js',
    '/assets/icons/icon-72x72.png',
    '/assets/icons/icon-96x96.png',
    '/assets/icons/icon-128x128.png',
    '/assets/icons/icon-144x144.png',
    '/assets/icons/icon-152x152.png',
    '/assets/icons/icon-192x192.png',
    '/assets/icons/icon-384x384.png',
    '/assets/icons/icon-512x512.png',
    '/manifest.json'
];

// Install event - cache assets
self.addEventListener('install', (event) => {
    event.waitUntil(
        caches.open(CACHE_NAME)
            .then((cache) => {
                console.log('[Service Worker] Caching app shell');
                return cache.addAll(CACHE_ASSETS);
            })
            .then(() => {
                console.log('[Service Worker] Skip waiting');
                return self.skipWaiting();
            })
    );
});

// Activate event - clean up old caches
self.addEventListener('activate', (event) => {
    event.waitUntil(
        caches.keys()
            .then((cacheNames) => {
                return Promise.all(
                    cacheNames.map((cacheName) => {
                        if (cacheName !== CACHE_NAME) {
                            console.log('[Service Worker] Deleting old cache:', cacheName);
                            return caches.delete(cacheName);
                        }
                    })
                );
            })
            .then(() => {
                console.log('[Service Worker] Claiming clients');
                return self.clients.claim();
            })
    );
});

// Fetch event - serve from cache, fall back to network
self.addEventListener('fetch', (event) => {
    // Skip cross-origin requests
    if (!event.request.url.startsWith(self.location.origin)) {
        return;
    }

    // Skip API requests
    if (event.request.url.includes('/api/')) {
        return;
    }

    event.respondWith(
        caches.match(event.request)
            .then((response) => {
                // Cache hit - return response
                if (response) {
                    console.log('[Service Worker] Serving from cache:', event.request.url);
                    return response;
                }

                // Cache miss - fetch from network
                console.log('[Service Worker] Fetching from network:', event.request.url);
                return fetch(event.request)
                    .then((response) => {
                        // Check if valid response
                        if (!response || response.status !== 200 || response.type !== 'basic') {
                            return response;
                        }

                        // Clone response to cache
                        const responseToCache = response.clone();
                        caches.open(CACHE_NAME)
                            .then((cache) => {
                                cache.put(event.request, responseToCache);
                            });

                        return response;
                    })
                    .catch(() => {
                        // If both cache and network fail, serve offline page
                        if (event.request.destination === 'document') {
                            return caches.match(OFFLINE_URL);
                        }
                    });
            })
    );
});

// Handle background sync for offline actions
self.addEventListener('sync', (event) => {
    if (event.tag === 'sync-favorites') {
        event.waitUntil(syncFavorites());
    }
    if (event.tag === 'sync-bookings') {
        event.waitUntil(syncBookings());
    }
});

// Handle push notifications
self.addEventListener('push', (event) => {
    const options = {
        body: event.data ? event.data.text() : 'New notification from MyWisata',
        icon: '/assets/icons/icon-192x192.png',
        badge: '/assets/icons/badge-72x72.png',
        vibrate: [100, 50, 100],
        data: {
            dateOfArrival: Date.now(),
            primaryKey: 1
        },
        actions: [
            {
                action: 'explore',
                title: 'Explore',
                icon: '/assets/icons/action-explore.png'
            },
            {
                action: 'close',
                title: 'Close',
                icon: '/assets/icons/action-close.png'
            }
        ]
    };

    event.waitUntil(
        self.registration.showNotification('MyWisata', options)
    );
});

// Handle notification click
self.addEventListener('notificationclick', (event) => {
    event.notification.close();

    if (event.action === 'explore') {
        event.waitUntil(
            clients.openWindow('/')
        );
    }
});

// Sync favorites from offline storage
function syncFavorites() {
    return new Promise((resolve, reject) => {
        // Implementation for syncing offline favorites
        console.log('[Service Worker] Syncing favorites');
        resolve();
    });
}

// Sync bookings from offline storage
function syncBookings() {
    return new Promise((resolve, reject) => {
        // Implementation for syncing offline bookings
        console.log('[Service Worker] Syncing bookings');
        resolve();
    });
}
