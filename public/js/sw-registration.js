/**
 * Service Worker Registration
 * Registers the service worker and handles updates
 */

if ('serviceWorker' in navigator) {
  window.addEventListener('load', () => {
    navigator.serviceWorker.register('/public/js/service-worker.js')
      .then(registration => {
        console.log('ServiceWorker registration successful with scope: ', registration.scope);
        
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
        console.log('ServiceWorker registration failed: ', error);
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
  notification.style.cssText = `
    position: fixed;
    bottom: 20px;
    right: 20px;
    background: #007bff;
    color: white;
    padding: 15px 20px;
    border-radius: 5px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.2);
    z-index: 9999;
  `;
  notification.innerHTML = `
    <p style="margin: 0 0 10px 0;">New version available. Please refresh.</p>
    <button onclick="window.location.reload()" style="background: white; color: #007bff; border: none; padding: 5px 15px; border-radius: 3px; cursor: pointer;">Refresh</button>
  `;
  document.body.appendChild(notification);
}
