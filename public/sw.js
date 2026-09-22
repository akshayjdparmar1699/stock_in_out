const CACHE_NAME = 'stock-in-out-shell-v1';

const SHELL_ASSETS = [
    '/icons/icon-192.png',
    '/icons/icon-512.png',
];

self.addEventListener('install', (event) => {
    event.waitUntil(
        caches.open(CACHE_NAME).then((cache) => cache.addAll(SHELL_ASSETS))
    );
    self.skipWaiting();
});

self.addEventListener('activate', (event) => {
    event.waitUntil(
        caches.keys().then((keys) => Promise.all(
            keys.filter((key) => key !== CACHE_NAME).map((key) => caches.delete(key))
        ))
    );
    self.clients.claim();
});

// Only cache-first the built, versioned static assets (CSS/JS/icons/fonts).
// Everything else (pages, forms, API calls) always goes to the network so
// stock/invoice data is never served stale.
self.addEventListener('fetch', (event) => {
    const url = new URL(event.request.url);
    const isStaticAsset = event.request.method === 'GET'
        && (url.pathname.startsWith('/build/') || url.pathname.startsWith('/icons/'));

    if (!isStaticAsset) {
        return;
    }

    event.respondWith(
        caches.match(event.request).then((cached) => {
            if (cached) {
                return cached;
            }

            return fetch(event.request).then((response) => {
                const copy = response.clone();
                caches.open(CACHE_NAME).then((cache) => cache.put(event.request, copy));
                return response;
            });
        })
    );
});
