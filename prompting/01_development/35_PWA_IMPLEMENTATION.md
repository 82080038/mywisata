# PWA IMPLEMENTATION
# Module 35 - Progressive Web App for Tour Guide Application

## OVERVIEW

This prompting template guides the AI through implementing Progressive Web App (PWA) features for the Tour Guide Application to provide offline capabilities, improved performance, and native app-like experience.

## PWA REQUIREMENTS

### Core PWA Features
1. **Service Worker** - Offline caching and background sync
2. **Web App Manifest** - App metadata and installability
3. **Responsive Design** - Mobile-first approach
4. **HTTPS** - Secure connection required
5. **Offline Functionality** - Core features work offline
6. **Push Notifications** - Optional but recommended
7. **App Shell** - Instant loading structure

## WEB APP MANIFEST

### manifest.json
```json
{
  "name": "MyWisata - Tour Guide Application",
  "short_name": "MyWisata",
  "description": "Find local tour guides and explore destinations",
  "start_url": "/",
  "display": "standalone",
  "background_color": "#ffffff",
  "theme_color": "#007bff",
  "orientation": "portrait-primary",
  "icons": [
    {
      "src": "/public/icons/icon-72x72.png",
      "sizes": "72x72",
      "type": "image/png",
      "purpose": "any"
    },
    {
      "src": "/public/icons/icon-96x96.png",
      "sizes": "96x96",
      "type": "image/png",
      "purpose": "any"
    },
    {
      "src": "/public/icons/icon-128x128.png",
      "sizes": "128x128",
      "type": "image/png",
      "purpose": "any"
    },
    {
      "src": "/public/icons/icon-144x144.png",
      "sizes": "144x144",
      "type": "image/png",
      "purpose": "any"
    },
    {
      "src": "/public/icons/icon-152x152.png",
      "sizes": "152x152",
      "type": "image/png",
      "purpose": "any"
    },
    {
      "src": "/public/icons/icon-192x192.png",
      "sizes": "192x192",
      "type": "image/png",
      "purpose": "any"
    },
    {
      "src": "/public/icons/icon-384x384.png",
      "sizes": "384x384",
      "type": "image/png",
      "purpose": "any"
    },
    {
      "src": "/public/icons/icon-512x512.png",
      "sizes": "512x512",
      "type": "image/png",
      "purpose": "any"
    }
  ],
  "categories": ["travel", "tourism"],
  "screenshots": [
    {
      "src": "/public/screenshots/home.png",
      "sizes": "1280x720",
      "type": "image/png"
    },
    {
      "src": "/public/screenshots/destinations.png",
      "sizes": "1280x720",
      "type": "image/png"
    }
  ],
  "shortcuts": [
    {
      "name": "View Destinations",
      "short_name": "Destinations",
      "description": "Browse tourist destinations",
      "url": "/destinations",
      "icons": [{ "src": "/public/icons/shortcut-destinations.png", "sizes": "96x96" }]
    },
    {
      "name": "Find Tour Guides",
      "short_name": "Tour Guides",
      "description": "Find local tour guides",
      "url": "/tourguides",
      "icons": [{ "src": "/public/icons/shortcut-guides.png", "sizes": "96x96" }]
    },
    {
      "name": "My Bookings",
      "short_name": "Bookings",
      "description": "View your bookings",
      "url": "/bookings",
      "icons": [{ "src": "/public/icons/shortcut-bookings.png", "sizes": "96x96" }]
    }
  ]
}
```

## SERVICE WORKER

### service-worker.js
```javascript
const CACHE_NAME = 'mywisata-v1';
const urlsToCache = [
  '/',
  '/index.php',
  '/public/css/style.css',
  '/public/css/bootstrap.min.css',
  '/public/js/main.js',
  '/public/js/bootstrap.bundle.min.js',
  '/public/icons/icon-192x192.png',
  '/public/icons/icon-512x512.png'
];

// Install event - cache resources
self.addEventListener('install', event => {
  event.waitUntil(
    caches.open(CACHE_NAME)
      .then(cache => {
        console.log('Opened cache');
        return cache.addAll(urlsToCache);
      })
  );
});

// Fetch event - serve from cache when offline
self.addEventListener('fetch', event => {
  event.respondWith(
    caches.match(event.request)
      .then(response => {
        // Cache hit - return response
        if (response) {
          return response;
        }

        // Clone the request
        const fetchRequest = event.request.clone();

        return fetch(fetchRequest).then(response => {
          // Check if valid response
          if (!response || response.status !== 200 || response.type !== 'basic') {
            return response;
          }

          // Clone the response
          const responseToCache = response.clone();

          caches.open(CACHE_NAME)
            .then(cache => {
              cache.put(event.request, responseToCache);
            });

          return response;
        });
      })
      .catch(() => {
        // Offline fallback
        if (event.request.destination === 'document') {
          return caches.match('/offline.html');
        }
      })
  );
});

// Activate event - clean up old caches
self.addEventListener('activate', event => {
  const cacheWhitelist = [CACHE_NAME];
  event.waitUntil(
    caches.keys().then(cacheNames => {
      return Promise.all(
        cacheNames.map(cacheName => {
          if (cacheWhitelist.indexOf(cacheName) === -1) {
            return caches.delete(cacheName);
          }
        })
      );
    })
  );
});

// Background sync for offline actions
self.addEventListener('sync', event => {
  if (event.tag === 'sync-bookings') {
    event.waitUntil(syncBookings());
  }
});

// Push notifications
self.addEventListener('push', event => {
  const options = {
    body: event.data ? event.data.text() : 'New notification from MyWisata',
    icon: '/public/icons/icon-192x192.png',
    badge: '/public/icons/badge-72x72.png',
    vibrate: [100, 50, 100],
    data: {
      dateOfArrival: Date.now(),
      primaryKey: 1
    }
  };

  event.waitUntil(
    self.registration.showNotification('MyWisata', options)
  );
});

// Notification click handler
self.addEventListener('notificationclick', event => {
  event.notification.close();
  event.waitUntil(
    clients.openWindow('/')
  );
});

// Sync bookings when back online
function syncBookings() {
  // Get offline bookings from IndexedDB
  // Send to server
  // Update local state
  return Promise.resolve();
}
```

## SERVICE WORKER REGISTRATION

### sw-registration.js
```javascript
if ('serviceWorker' in navigator) {
  window.addEventListener('load', () => {
    navigator.serviceWorker.register('/public/js/service-worker.js')
      .then(registration => {
        console.log('ServiceWorker registration successful');
        
        // Check for updates
        registration.addEventListener('updatefound', () => {
          const newWorker = registration.installing;
          
          newWorker.addEventListener('statechange', () => {
            if (newWorker.state === 'installed' && navigator.serviceWorker.controller) {
              // New content available
              showUpdateNotification();
            }
          });
        });
      })
      .catch(error => {
        console.log('ServiceWorker registration failed:', error);
      });
  });

  // Handle controller change
  navigator.serviceWorker.addEventListener('controllerchange', () => {
    window.location.reload();
  });
}

function showUpdateNotification() {
  // Show update available notification
  const notification = document.createElement('div');
  notification.className = 'update-notification';
  notification.innerHTML = `
    <p>New version available. Please refresh.</p>
    <button onclick="window.location.reload()">Refresh</button>
  `;
  document.body.appendChild(notification);
}
```

## OFFLINE PAGE

### offline.html
```html
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>You're Offline - MyWisata</title>
  <link rel="stylesheet" href="/public/css/bootstrap.min.css">
  <link rel="stylesheet" href="/public/css/style.css">
</head>
<body>
  <div class="container mt-5">
    <div class="row justify-content-center">
      <div class="col-md-6 text-center">
        <div class="offline-icon">
          <i class="fas fa-wifi" style="font-size: 100px; color: #6c757d;"></i>
        </div>
        <h1 class="mt-4">You're Offline</h1>
        <p class="lead">Please check your internet connection.</p>
        <p class="text-muted">
          Some features may not be available while offline. 
          Cached content will be displayed where possible.
        </p>
        <button onclick="window.location.reload()" class="btn btn-primary">
          <i class="fas fa-sync-alt"></i> Retry
        </button>
        <div class="mt-4">
          <h5>Available Offline:</h5>
          <ul class="list-unstyled">
            <li><i class="fas fa-check text-success"></i> Previously viewed destinations</li>
            <li><i class="fas fa-check text-success"></i> Cached tour guide profiles</li>
            <li><i class="fas fa-check text-success"></i> Your bookings (cached)</li>
          </ul>
        </div>
      </div>
    </div>
  </div>
</body>
</html>
```

## INDEXEDDB FOR OFFLINE DATA

### indexeddb-helper.js
```javascript
class IndexedDBHelper {
  constructor(dbName, version) {
    this.dbName = dbName;
    this.version = version;
    this.db = null;
  }

  async init() {
    return new Promise((resolve, reject) => {
      const request = indexedDB.open(this.dbName, this.version);

      request.onerror = () => reject(request.error);
      request.onsuccess = () => {
        this.db = request.result;
        resolve(this.db);
      };

      request.onupgradeneeded = (event) => {
        const db = event.target.result;

        // Create stores
        if (!db.objectStoreNames.contains('destinations')) {
          const destStore = db.createObjectStore('destinations', { keyPath: 'id' });
          destStore.createIndex('name', 'name', { unique: false });
        }

        if (!db.objectStoreNames.contains('tourguides')) {
          const guideStore = db.createObjectStore('tourguides', { keyPath: 'id' });
          guideStore.createIndex('name', 'name', { unique: false });
        }

        if (!db.objectStoreNames.contains('bookings')) {
          const bookingStore = db.createObjectStore('bookings', { keyPath: 'id' });
          bookingStore.createIndex('user_id', 'user_id', { unique: false });
        }

        if (!db.objectStoreNames.contains('syncQueue')) {
          const syncStore = db.createObjectStore('syncQueue', { keyPath: 'id', autoIncrement: true });
          syncStore.createIndex('timestamp', 'timestamp', { unique: false });
        }
      };
    });
  }

  async add(storeName, data) {
    return new Promise((resolve, reject) => {
      const transaction = this.db.transaction([storeName], 'readwrite');
      const store = transaction.objectStore(storeName);
      const request = store.add(data);

      request.onsuccess = () => resolve(request.result);
      request.onerror = () => reject(request.error);
    });
  }

  async get(storeName, key) {
    return new Promise((resolve, reject) => {
      const transaction = this.db.transaction([storeName], 'readonly');
      const store = transaction.objectStore(storeName);
      const request = store.get(key);

      request.onsuccess = () => resolve(request.result);
      request.onerror = () => reject(request.error);
    });
  }

  async getAll(storeName) {
    return new Promise((resolve, reject) => {
      const transaction = this.db.transaction([storeName], 'readonly');
      const store = transaction.objectStore(storeName);
      const request = store.getAll();

      request.onsuccess = () => resolve(request.result);
      request.onerror = () => reject(request.error);
    });
  }

  async update(storeName, data) {
    return new Promise((resolve, reject) => {
      const transaction = this.db.transaction([storeName], 'readwrite');
      const store = transaction.objectStore(storeName);
      const request = store.put(data);

      request.onsuccess = () => resolve(request.result);
      request.onerror = () => reject(request.error);
    });
  }

  async delete(storeName, key) {
    return new Promise((resolve, reject) => {
      const transaction = this.db.transaction([storeName], 'readwrite');
      const store = transaction.objectStore(storeName);
      const request = store.delete(key);

      request.onsuccess = () => resolve();
      request.onerror = () => reject(request.error);
    });
  }
}

// Initialize
const offlineDB = new IndexedDBHelper('mywisata-offline', 1);
offlineDB.init();
```

## PUSH NOTIFICATIONS

### push-notification.js
```javascript
// Request notification permission
async function requestNotificationPermission() {
  if (!('Notification' in window)) {
    console.log('This browser does not support notifications');
    return false;
  }

  if (Notification.permission === 'granted') {
    return true;
  }

  if (Notification.permission !== 'denied') {
    const permission = await Notification.requestPermission();
    return permission === 'granted';
  }

  return false;
}

// Subscribe to push notifications
async function subscribeToPush() {
  if (!('serviceWorker' in navigator)) {
    return;
  }

  const registration = await navigator.serviceWorker.ready;
  
  try {
    const subscription = await registration.pushManager.subscribe({
      userVisibleOnly: true,
      applicationServerKey: urlBase64ToUint8Array('YOUR_VAPID_PUBLIC_KEY')
    });

    // Send subscription to server
    await fetch('/api/push/subscribe', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
      },
      body: JSON.stringify(subscription)
    });

    return subscription;
  } catch (error) {
    console.error('Push subscription failed:', error);
  }
}

// Helper function to convert VAPID key
function urlBase64ToUint8Array(base64String) {
  const padding = '='.repeat((4 - base64String.length % 4) % 4);
  const base64 = (base64String + padding)
    .replace(/-/g, '+')
    .replace(/_/g, '/');

  const rawData = window.atob(base64);
  const outputArray = new Uint8Array(rawData.length);

  for (let i = 0; i < rawData.length; ++i) {
    outputArray[i] = rawData.charCodeAt(i);
  }

  return outputArray;
}

// Show local notification
function showLocalNotification(title, body, icon) {
  if (Notification.permission === 'granted') {
    new Notification(title, {
      body: body,
      icon: icon || '/public/icons/icon-192x192.png'
    });
  }
}
```

## HTML HEADERS UPDATE

### Add to all pages
```html
<link rel="manifest" href="/manifest.json">
<meta name="theme-color" content="#007bff">
<meta name="apple-mobile-web-app-capable" content="yes">
<meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
<meta name="apple-mobile-web-app-title" content="MyWisata">
<link rel="apple-touch-icon" href="/public/icons/icon-152x152.png">
<link rel="icon" type="image/png" sizes="192x192" href="/public/icons/icon-192x192.png">
<link rel="icon" type="image/png" sizes="512x512" href="/public/icons/icon-512x512.png">
```

## IMPLEMENTATION TASKS

### Phase 1: Basic PWA Setup
1. Create manifest.json
2. Create service worker
3. Register service worker
4. Add PWA meta tags
5. Create app icons
6. Test installability

### Phase 2: Offline Functionality
1. Implement caching strategy
2. Create offline page
3. Set up IndexedDB
4. Cache critical resources
5. Implement offline fallbacks

### Phase 3: Data Sync
1. Implement background sync
2. Create sync queue
3. Handle conflict resolution
4. Implement retry logic
5. Add sync status indicator

### Phase 4: Push Notifications
1. Set up VAPID keys
2. Implement subscription
3. Create notification API endpoint
4. Implement notification handling
5. Add notification preferences

### Phase 5: App Shell
1. Create app shell structure
2. Implement instant loading
3. Cache app shell
4. Dynamic content loading
5. Smooth transitions

### Phase 6: Testing
1. Test offline functionality
2. Test installation
3. Test push notifications
4. Test background sync
5. Test across browsers

## DELIVERABLES

1. manifest.json
2. service-worker.js
3. sw-registration.js
4. indexeddb-helper.js
5. push-notification.js
6. offline.html
7. App icons (multiple sizes)
8. PWA documentation
9. Installation guide
10. Testing report

## ACCEPTANCE CRITERIA

- App is installable on mobile devices
- Core features work offline
- Service worker registered and active
- Caching strategy implemented
- Background sync working
- Push notifications functional
- IndexedDB for offline data
- App shell for instant loading
- Cross-browser compatibility
- Lighthouse PWA score > 90

## NOTES

- HTTPS is required for PWA
- Test on real devices
- Consider battery usage
- Implement graceful degradation
- Regular cache updates
- Monitor service worker errors
- Keep app shell lightweight
- Optimize images for mobile

---

**Module:** 35_PWA_IMPLEMENTATION  
**Priority:** MEDIUM  
**Status:** READY FOR DEVELOPMENT  
**Last Updated:** 2026-07-18
