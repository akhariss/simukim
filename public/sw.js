const CACHE_NAME = 'simukim-v2';
const OFFLINE_URL = '/offline.html';

const PRECACHE_ASSETS = [
    OFFLINE_URL,
    '/manifest.json',
    '/favicon-32x32.png',
    '/favicon-16x16.png',
    '/favicon.ico',
    '/icons/icon-72x72.png',
    '/icons/icon-96x96.png',
    '/icons/icon-128x128.png',
    '/icons/icon-144x144.png',
    '/icons/icon-152x152.png',
    '/icons/icon-192x192.png',
    '/icons/icon-384x384.png',
    '/icons/icon-512x512.png',
    '/icons/icon-maskable-512x512.png',
    '/screenshots/screenshot-desktop-1.png',
    '/screenshots/screenshot-mobile-1.png'
];

// Install Event - Precache essential assets
self.addEventListener('install', (event) => {
    event.waitUntil(
        caches.open(CACHE_NAME).then((cache) => {
            return cache.addAll(PRECACHE_ASSETS);
        })
    );
    self.skipWaiting();
});

// Activate Event - Clean up stale caches
self.addEventListener('activate', (event) => {
    event.waitUntil(
        caches.keys().then((cacheNames) => {
            return Promise.all(
                cacheNames.map((cache) => {
                    if (cache !== CACHE_NAME) {
                        return caches.delete(cache);
                    }
                })
            );
        }).then(() => self.clients.claim())
    );
});

// Fetch Event - Handle network-first for navigation, cache-first/fallback for assets
self.addEventListener('fetch', (event) => {
    if (event.request.method !== 'GET') return;
    if (!event.request.url.startsWith('http')) return;

    // Navigation requests (HTML pages) -> Network First with Offline Fallback
    if (event.request.mode === 'navigate') {
        event.respondWith(
            fetch(event.request)
                .catch(() => {
                    return caches.open(CACHE_NAME).then((cache) => {
                        return cache.match(OFFLINE_URL);
                    });
                })
        );
        return;
    }

    // Static Assets (fonts, images, icons, compiled build) -> Stale-while-revalidate
    event.respondWith(
        caches.match(event.request).then((cachedResponse) => {
            if (cachedResponse) {
                fetch(event.request).then((networkResponse) => {
                    if (networkResponse && networkResponse.status === 200) {
                        caches.open(CACHE_NAME).then((cache) => {
                            cache.put(event.request, networkResponse.clone());
                        });
                    }
                }).catch(() => {});

                return cachedResponse;
            }

            return fetch(event.request).then((networkResponse) => {
                if (!networkResponse || networkResponse.status !== 200 || networkResponse.type !== 'basic') {
                    return networkResponse;
                }

                const url = event.request.url;
                if (url.includes('/build/') || url.includes('/fonts/') || url.includes('/icons/') || url.includes('/images/') || url.includes('/screenshots/')) {
                    const responseToCache = networkResponse.clone();
                    caches.open(CACHE_NAME).then((cache) => {
                        cache.put(event.request, responseToCache);
                    });
                }

                return networkResponse;
            }).catch(() => null);
        })
    );
});

// Background Sync capability (for offline form submission / deferred actions)
self.addEventListener('sync', (event) => {
    if (event.tag === 'sync-presensi' || event.tag === 'sync-data') {
        event.waitUntil(Promise.resolve());
    }
});

// Periodic Background Sync capability (periodic update checks)
self.addEventListener('periodicsync', (event) => {
    if (event.tag === 'check-announcements' || event.tag === 'update-cache') {
        event.waitUntil(Promise.resolve());
    }
});

// Web Push Notifications capability
self.addEventListener('push', (event) => {
    let payload = { title: 'SIMUKIM Ma\'had Al-Jami\'ah', body: 'Pembaruan data asrama terbaru.' };
    try {
        if (event.data) payload = event.data.json();
    } catch (e) {
        if (event.data) payload.body = event.data.text();
    }

    const options = {
        body: payload.body,
        icon: '/icons/icon-192x192.png',
        badge: '/icons/icon-72x72.png',
        vibrate: [100, 50, 100],
        data: {
            url: payload.url || '/dashboard'
        }
    };

    event.waitUntil(
        self.registration.showNotification(payload.title, options)
    );
});

// Notification Click Handler
self.addEventListener('notificationclick', (event) => {
    event.notification.close();
    const targetUrl = event.notification.data?.url || '/';

    event.waitUntil(
        clients.matchAll({ type: 'window', includeUncontrolled: true }).then((clientList) => {
            for (const client of clientList) {
                if (client.url === targetUrl && 'focus' in client) {
                    return client.focus();
                }
            }
            if (clients.openWindow) {
                return clients.openWindow(targetUrl);
            }
        })
    );
});
