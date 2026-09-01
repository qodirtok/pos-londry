/* Londry POS — Service Worker | Cache shell + offline fallback */
const CACHE = 'londry-v2';
const OFFLINE_URL = '/offline';

const PRECACHE = [
  OFFLINE_URL,
  '/manifest.webmanifest',
  '/icons/icon-192x192.png',
  '/icons/icon-512x512.png',
  '/apple-touch-icon.png',
];

self.addEventListener('install', (e) => {
  e.waitUntil(caches.open(CACHE).then((c) => c.addAll(PRECACHE)).then(() => self.skipWaiting()));
});

self.addEventListener('activate', (e) => {
  e.waitUntil(
    caches.keys().then((keys) => Promise.all(keys.filter((k) => k !== CACHE).map((k) => caches.delete(k))))
      .then(() => self.clients.claim())
  );
});

function isAssetRequest(req) {
  const url = new URL(req.url);
  // Vite build assets + icons + fonts
  return url.pathname.startsWith('/build/') || url.pathname.startsWith('/icons/') || url.pathname === '/manifest.webmanifest' || url.pathname === '/apple-touch-icon.png';
}
function isApiRequest(req) {
  const url = new URL(req.url);
  return url.pathname.startsWith('/api/') || url.pathname.includes('-search');
}
function isNavigationRequest(req) {
  return req.mode === 'navigate' || req.headers.get('accept')?.includes('text/html');
}

self.addEventListener('fetch', (e) => {
  const req = e.request;
  if (req.method !== 'GET') return;

  // Assets: cache-first, fallback to network
  if (isAssetRequest(req)) {
    e.respondWith(
      caches.match(req).then((cached) => cached || fetch(req).then((res) => {
        if (res.ok) { const clone = res.clone(); caches.open(CACHE).then((c) => c.put(req, clone)); }
        return res;
      }))
    );
    return;
  }

  // API/search: network-first, no caching of POST side-effects
  if (isApiRequest(req)) {
    e.respondWith(
      fetch(req).catch(() => caches.match(req))
    );
    return;
  }

  // Navigations: network-first with offline fallback
  if (isNavigationRequest(req)) {
    e.respondWith(
      fetch(req).then((res) => {
        // cache successful navigations for offline
        if (res.ok) { const clone = res.clone(); caches.open(CACHE).then((c) => c.put(req, clone)); }
        return res;
      }).catch(async () => {
        const cached = await caches.match(req);
        if (cached) return cached;
        const offline = await caches.match(OFFLINE_URL);
        if (offline) return offline;
        return new Response('Offline', { status: 503, headers: { 'Content-Type': 'text/plain' } });
      })
    );
    return;
  }

  // Other GET: stale-while-revalidate
  e.respondWith(
    caches.match(req).then((cached) => {
      const fetched = fetch(req).then((res) => {
        if (res.ok) caches.open(CACHE).then((c) => c.put(req, res.clone()));
        return res;
      }).catch(() => null);
      return cached || fetched || Response.error();
    })
  );
});
