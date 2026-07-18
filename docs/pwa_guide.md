# PWA IMPLEMENTATION GUIDE
# Tour Guide Application

## OVERVIEW

This guide provides comprehensive instructions for the Progressive Web App (PWA) implementation of the Tour Guide Application.

## PWA FEATURES

### Implemented Features
- **Web App Manifest** - App metadata and installability
- **Service Worker** - Offline caching and background sync
- **IndexedDB** - Offline data storage
- **Push Notifications** - Notification infrastructure
- **Offline Page** - Fallback page for offline users
- **PWA Meta Tags** - Mobile app-like experience

## FILE STRUCTURE

```
mywisata/
├── manifest.json                    # Web app manifest
├── public/
│   ├── js/
│   │   ├── service-worker.js       # Service worker
│   │   ├── sw-registration.js      # Service worker registration
│   │   ├── indexeddb-helper.js     # IndexedDB helper
│   │   └── push-notification.js    # Push notification helper
│   ├── offline.html                # Offline fallback page
│   └── icons/                      # PWA icons (to be created)
└── app/views/layouts/
    └── header.php                  # PWA meta tags and scripts
```

## INSTALLATION REQUIREMENTS

### HTTPS Requirement
PWA requires HTTPS to function properly. The service worker will only work on:
- HTTPS sites
- localhost (for development)

### PWA Icons
Create the following icon sizes in `/public/icons/`:
- icon-72x72.png
- icon-96x96.png
- icon-128x128.png
- icon-144x144.png
- icon-152x152.png
- icon-192x192.png
- icon-384x384.png
- icon-512x512.png
- shortcut-destinations.png (96x96)
- shortcut-guides.png (96x96)
- shortcut-bookings.png (96x96)

## SERVICE WORKER

### Caching Strategy
The service worker uses a cache-first strategy with network fallback:
1. Check cache first
2. If cache hit, return cached response
3. If cache miss, fetch from network
4. Cache network response for future use
5. On network failure, serve offline page

### Cache Version
Current cache version: `mywisata-v1`
Update this version when you want to force cache refresh.

### Offline Fallback
When offline, the service worker serves `/offline.html` for document requests.

## INDEXEDDB STORAGE

### Database Structure
- **Database Name:** mywisata-offline
- **Version:** 1

### Object Stores
- **destinations** - Cached destination data
- **tourguides** - Cached tour guide data
- **bookings** - Offline booking data
- **syncQueue** - Queue for offline actions to sync

### Usage Example
```javascript
// Add data
await offlineDB.add('destinations', { id: 1, name: 'Bali' });

// Get data
const destination = await offlineDB.get('destinations', 1);

// Get all data
const allDestinations = await offlineDB.getAll('destinations');

// Update data
await offlineDB.update('destinations', { id: 1, name: 'Bali Updated' });

// Delete data
await offlineDB.delete('destinations', 1);
```

## PUSH NOTIFICATIONS

### Setup
1. Generate VAPID keys using web-push library
2. Replace `YOUR_VAPID_PUBLIC_KEY` in `push-notification.js`
3. Implement server-side push notification endpoint

### Request Permission
```javascript
// Request notification permission
await requestNotificationPermission();
```

### Subscribe to Push
```javascript
// Subscribe to push notifications
await subscribeToPush();
```

### Show Local Notification
```javascript
// Show local notification
showLocalNotification('New Booking', 'You have a new booking request');
```

## TESTING PWA

### Chrome DevTools
1. Open Chrome DevTools (F12)
2. Go to Application tab
3. Check Service Workers section
4. Verify service worker is registered and active
5. Check Manifest section for manifest validation

### Lighthouse
1. Open Chrome DevTools
2. Go to Lighthouse tab
3. Select Progressive Web App
4. Run audit
5. Target score: > 90

### Offline Testing
1. Open DevTools Network tab
2. Check "Offline" checkbox
3. Navigate the application
4. Verify offline page is shown
5. Verify cached content is displayed

### Install Testing
1. Open application in Chrome
2. Look for install icon in address bar
3. Click install icon
4. Verify app installs
5. Verify app launches in standalone mode

## DEPLOYMENT

### Production Checklist
- [ ] HTTPS enabled
- [ ] PWA icons created
- [ ] Service worker registered
- [ ] Manifest accessible
- [ ] IndexedDB working
- [ ] Offline page accessible
- [ ] VAPID keys configured (for push notifications)
- [ ] Test on real devices
- [ ] Lighthouse score > 90

## TROUBLESHOOTING

### Service Worker Not Registering
- Check browser console for errors
- Verify service worker file path is correct
- Ensure HTTPS is enabled (or localhost)
- Check .htaccess for service worker MIME type

### Cache Not Working
- Clear browser cache
- Update cache version in service worker
- Check network tab for cache status
- Verify cache.addAll URLs are correct

### IndexedDB Errors
- Check browser console for errors
- Verify database version
- Check object store names
- Ensure proper error handling

### Push Notifications Not Working
- Verify VAPID keys are configured
- Check notification permission
- Verify service worker is active
- Check browser console for errors

## BEST PRACTICES

1. **Regular Cache Updates** - Update cache version when content changes
2. **Monitor Service Worker** - Check for errors in production
3. **Test Offline** - Regularly test offline functionality
4. **Optimize Assets** - Keep PWA assets lightweight
5. **User Feedback** - Provide feedback for offline actions
6. **Sync Strategy** - Implement robust sync for offline data
7. **Battery Usage** - Consider battery impact of background sync

## RESOURCES

- [PWA Documentation](https://web.dev/progressive-web-apps/)
- [Service Worker API](https://developer.mozilla.org/en-US/docs/Web/API/Service_Worker_API)
- [IndexedDB API](https://developer.mozilla.org/en-US/docs/Web/API/IndexedDB_API)
- [Web Push API](https://developer.mozilla.org/en-US/docs/Web/API/Push_API)
- [Lighthouse PWA Audit](https://web.dev/lighthouse-pwa/)

---

**Version:** 1.0  
**Last Updated:** 2026-07-18  
**Status:** Active
