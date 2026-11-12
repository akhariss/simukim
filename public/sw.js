const CACHE_NAME = 'simukim-v1';
const OFFLINE_URL = '/offline.html';

const PRECACHE_ASSETS = [
    OFFLINE_URL,
    '/manifest.json',
    '/favicon-32x32.png',
    '/favicon-16x16.png',
    '/favicon.ico',
    '/icons/icon-192x192.png',
    '/icons/icon-512x512.png',
    '/icons/icon-maskable-512x512.png'
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
    // Only handle HTTP/HTTPS GET requests
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

    // Static Assets (fonts, images, icons, compiled build) -> Stale-while-revalidate or Cache Fallback
    event.respondWith(
        caches.match(event.request).then((cachedResponse) => {
            if (cachedResponse) {
                // Fetch in background to update cache
                fetch(event.request).then((networkResponse) => {
                    if (networkResponse && networkResponse.status === 200) {
                        caches.open(CACHE_NAME).then((cache) => {
                            cache.put(event.request, networkResponse.clone());
                        });
                    }
                }).catch(() => {/* Ignore background fetch failures */});

                return cachedResponse;
            }

            return fetch(event.request).then((networkResponse) => {
                if (!networkResponse || networkResponse.status !== 200 || networkResponse.type !== 'basic') {
                    return networkResponse;
                }

                // Cache static assets on the fly
                const url = event.request.url;
                if (url.includes('/build/') || url.includes('/fonts/') || url.includes('/icons/') || url.includes('/images/')) {
                    const responseToCache = networkResponse.clone();
                    caches.open(CACHE_NAME).then((cache) => {
                        cache.put(event.request, responseToCache);
                    });
                }

                return networkResponse;
            }).catch(() => {
                // Asset offline fallback if needed
                return null;
            });
        })
    );
});

