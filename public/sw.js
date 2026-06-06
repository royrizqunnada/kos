// Cozy Corner — service worker (manual, tanpa build plugin).
const CACHE = 'cozy-v2';
const PRECACHE = ['/offline.html', '/icons/icon-192.png', '/icons/icon-512.png', '/manifest.webmanifest'];

self.addEventListener('install', (event) => {
    event.waitUntil(
        caches.open(CACHE).then((c) => c.addAll(PRECACHE)).then(() => self.skipWaiting())
    );
});

self.addEventListener('activate', (event) => {
    event.waitUntil(
        caches.keys()
            .then((keys) => Promise.all(keys.filter((k) => k !== CACHE).map((k) => caches.delete(k))))
            .then(() => self.clients.claim())
    );
});

self.addEventListener('fetch', (event) => {
    const req = event.request;
    if (req.method !== 'GET' || new URL(req.url).origin !== self.location.origin) return;

    // Navigasi halaman: network-first (data selalu segar), fallback offline.
    if (req.mode === 'navigate') {
        event.respondWith(fetch(req).catch(() => caches.match('/offline.html')));
        return;
    }

    // Aset statis (build & icons): cache-first + perbarui di latar belakang.
    const path = new URL(req.url).pathname;
    if (path.startsWith('/build/') || path.startsWith('/icons/')) {
        event.respondWith(
            caches.match(req).then((cached) => {
                const network = fetch(req)
                    .then((res) => {
                        const copy = res.clone();
                        caches.open(CACHE).then((c) => c.put(req, copy));
                        return res;
                    })
                    .catch(() => cached);
                return cached || network;
            })
        );
    }
});
