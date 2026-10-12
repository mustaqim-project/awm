/* sw.js — service worker for a PUBLIC marketing/blog site. Place at the site root (public/sw.js) so scope is "/".
   Strategy: network-first for pages (always fresh HTML, offline fallback), stale-while-revalidate for static assets.
   HTML is never served stale while online, so crawlers and users always get current content.
   Bump VERSION on every deploy that changes precached files. Never cache authenticated areas (see NEVER_CACHE). */
const VERSION = 'v1';
const STATIC = `static-${VERSION}`;
const PAGES = `pages-${VERSION}`;
const OFFLINE_URL = '/offline.html';
const PRECACHE = [OFFLINE_URL, '/manifest.webmanifest', '/icons/icon-192.png'];
const MAX_PAGES = 30;
const MAX_STATIC = 120;

// Extend with the app's real private prefixes (php artisan route:list). These bypass the SW entirely.
const NEVER_CACHE = [/^\/(admin|dashboard|login|logout|register|password|app|api|livewire|sanctum|horizon|telescope|storage)(\/|$)/];
const STATIC_EXT = /\.(?:css|js|mjs|woff2?|png|jpe?g|webp|avif|svg|ico|gif)$/i;

self.addEventListener('install', (event) => {
  event.waitUntil(caches.open(STATIC).then((c) => c.addAll(PRECACHE)));
  // Do not skipWaiting here: the page decides when to update (see pwa-register.js) so users aren't reloaded mid-task.
});

self.addEventListener('activate', (event) => {
  event.waitUntil((async () => {
    const keep = new Set([STATIC, PAGES]);
    for (const key of await caches.keys()) if (!keep.has(key)) await caches.delete(key);
    if (self.registration.navigationPreload) await self.registration.navigationPreload.enable();
    await self.clients.claim();
  })());
});

self.addEventListener('message', (event) => {
  if (event.data === 'SKIP_WAITING') self.skipWaiting();
});

self.addEventListener('fetch', (event) => {
  const req = event.request;
  if (req.method !== 'GET') return;                       // forms/POST always hit the network
  const url = new URL(req.url);
  if (url.origin !== self.location.origin) return;
  if (NEVER_CACHE.some((re) => re.test(url.pathname))) return;
  if (req.headers.has('range')) return;                   // media range requests
  if (req.mode === 'navigate') return void event.respondWith(pageStrategy(event));
  if (STATIC_EXT.test(url.pathname)) return void event.respondWith(staleWhileRevalidate(req));
});

async function pageStrategy(event) {
  const cache = await caches.open(PAGES);
  try {
    const res = (await event.preloadResponse) || (await fetch(event.request));
    // A redirected response can't be replayed for a navigation, and no-store means "don't keep it".
    const cacheable = res.ok && res.type === 'basic' && !res.redirected &&
      !(res.headers.get('Cache-Control') || '').includes('no-store');
    if (cacheable) { cache.put(event.request, res.clone()); trim(PAGES, MAX_PAGES); }
    return res;
  } catch (_) {
    return (await cache.match(event.request)) || (await caches.match(OFFLINE_URL)) ||
      new Response('Offline', { status: 503, headers: { 'Content-Type': 'text/plain; charset=utf-8' } });
  }
}

async function staleWhileRevalidate(req) {
  const cache = await caches.open(STATIC);
  const cached = await cache.match(req);
  const network = fetch(req).then((res) => {
    if (res.ok && res.type === 'basic') { cache.put(req, res.clone()); trim(STATIC, MAX_STATIC); }
    return res;
  }).catch(() => cached);
  return cached || network;
}

async function trim(name, max) {
  const cache = await caches.open(name);
  const keys = await cache.keys();
  for (let i = 0; i < keys.length - max; i++) await cache.delete(keys[i]);
}
