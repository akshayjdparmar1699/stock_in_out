const CACHE_NAME = 'stock-in-out-shell-v2';

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

// Shows the OS/browser-level notification a server push triggers, and
// also tells any already-open tab directly — some browsers suppress or
// soft-pedal the OS notification banner while the app itself is the
// focused, visible tab, so relying on showNotification() alone misses
// exactly the case where someone's actively looking at the app.
self.addEventListener('push', (event) => {
    let data = { title: 'Stock In Out', body: '' };
    try {
        data = event.data ? event.data.json() : data;
    } catch (e) {
        // Non-JSON payload (shouldn't happen from our own server) — fall back to the default.
    }

    event.waitUntil(
        Promise.all([
            self.registration.showNotification(data.title, {
                body: data.body,
                icon: '/icons/icon-192.png',
                badge: '/icons/icon-192.png',
                data: { url: data.url || '/dashboard' },
            }),
            self.clients.matchAll({ type: 'window', includeUncontrolled: true }).then((clients) => {
                clients.forEach((client) => client.postMessage({
                    type: 'push-received',
                    title: data.title,
                    body: data.body,
                    url: data.url || '/dashboard',
                }));
            }),
        ])
    );
});

// Focuses an already-open tab on that URL if one exists, instead of
// always opening a new one, so tapping a notification doesn't pile up
// extra tabs/windows.
self.addEventListener('notificationclick', (event) => {
    event.notification.close();
    const url = event.notification.data?.url || '/dashboard';

    event.waitUntil(
        self.clients.matchAll({ type: 'window', includeUncontrolled: true }).then((clients) => {
            for (const client of clients) {
                if (client.url === url && 'focus' in client) {
                    return client.focus();
                }
            }
            return self.clients.openWindow(url);
        })
    );
});
