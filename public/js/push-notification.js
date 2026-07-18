/**
 * Push Notification Helper
 * Handles push notification subscription and local notifications
 */

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

// Initialize push notifications
function initPushNotifications() {
  requestNotificationPermission().then(granted => {
    if (granted) {
      console.log('Notification permission granted');
      // Subscribe to push notifications when service worker is ready
      if ('serviceWorker' in navigator) {
        navigator.serviceWorker.ready.then(() => {
          subscribeToPush();
        });
      }
    }
  });
}

// Auto-initialize
if (document.readyState === 'loading') {
  document.addEventListener('DOMContentLoaded', initPushNotifications);
} else {
  initPushNotifications();
}
