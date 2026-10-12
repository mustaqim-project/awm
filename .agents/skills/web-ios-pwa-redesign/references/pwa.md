# PWA for a Public Marketing/Blog Site (Laravel, shared hosting friendly)

Contents: 1 Scope & goals · 2 Files & placement · 3 Manifest · 4 Icons · 5 Meta tags · 6 Service worker · 7 Install & update UX · 8 Shared hosting / Apache · 9 iOS specifics · 10 App-like details · 11 SEO interplay · 12 Testing

## 1. Scope & goals
Goal: the public site installs like an app, opens standalone, feels app-like on mobile (bottom tab bar, safe areas, press states), shows a branded offline page, and loads fast on repeat visits. **Not** in scope unless asked: push notifications, background sync, offline editing, caching authenticated areas. The service worker only touches public pages and static assets.

## 2. Files & placement
| File | Location | Notes |
|---|---|---|
| `manifest.webmanifest` | `public/` | link from every page head (`seo-head.blade.php` does it) |
| `sw.js` | `public/` (root) | must be at the root for scope `/`; keep the name stable |
| `offline.html` | `public/` | self-contained, `noindex` |
| `pwa-register.js` | `public/` or bundled | `defer` before `</body>` |
| icons | `public/icons/` | 192, 512, maskable 512, apple-touch 180, favicon svg/ico |

Replace every `REPLACE_*` token (`grep -r REPLACE_ public resources` must be empty at delivery).

## 3. Manifest
Required for installability: `name` or `short_name`, `icons` (192 and 512 PNG), `start_url`, `display` (`standalone`), served over HTTPS. Recommended: `id`, `scope`, `lang: "id"`, `theme_color`, `background_color`, `description`, `orientation: "any"` (tablets must rotate), `categories`, `shortcuts` (2–4 existing public routes), `screenshots` (optional, improves the install dialog).
- `theme_color` = the existing brand/top bar color; `background_color` = page background (used for the splash).
- `start_url: "/?source=pwa"` lets analytics separate app launches. The canonical tag keeps the query out of the index.
- Do not point shortcuts at routes that don't exist; check `php artisan route:list`.

## 4. Icons
- Generate from one SVG master: 192, 512, **maskable 512** (keep the logo inside the central 80% circle; fill the full square with the brand color), apple-touch-icon 180×180 (opaque, no transparency), favicon.svg + favicon.ico.
- Tools: `pwa-asset-generator`, `sharp` scripts, or any image editor. If the repository lacks a logo asset, use the existing one and say so; do not invent a logo.

## 5. Meta tags (in `partials/seo-head.blade.php`)
`viewport` with `viewport-fit=cover`; `theme-color`; `manifest`; `apple-touch-icon`; `mobile-web-app-capable` and `apple-mobile-web-app-capable`; `apple-mobile-web-app-title`; `apple-mobile-web-app-status-bar-style` (`default` keeps the status bar solid; `black-translucent` draws content under it and then **requires** top safe-area padding); `format-detection` (telephone=no) to avoid auto-linked numbers restyling the page.

## 6. Service worker (see `assets/sw.js`)
- **Pages**: network-first, offline fallback. Always-fresh HTML means crawlers, users, and editors never see stale content while online. Responses with redirects or `no-store` are not cached (a redirected response cannot be replayed to a navigation).
- **Static assets**: stale-while-revalidate, bounded cache size.
- **Never cached**: non-GET, cross-origin, private prefixes (`NEVER_CACHE`; extend with the project's real admin/auth/API paths), range requests.
- **Updates**: the new worker waits; `pwa-register.js` shows "Versi baru tersedia → Muat ulang", then `SKIP_WAITING` + reload on `controllerchange`. No surprise reloads mid-form.
- **Versioning**: bump `VERSION` whenever precached files change. Hashed build assets (Vite/Mix) are handled by runtime caching, not hardcoded precache lists.
- Avoid precaching HTML pages: they change with content and the CMS/blog.

## 7. Install & update UX
- Chromium (Android/desktop): capture `beforeinstallprompt`, expose `window.pwaInstall()`; show your own "Pasang aplikasi" control (in the footer, the More sheet, or a quiet banner after meaningful engagement). Never on first paint, never modal, remember dismissal.
- iOS Safari: no install API. Show a short instruction ("Ketuk Bagikan, lalu Tambahkan ke Layar Utama") when `pwaNeedsIOSHint()` is true, dismissible and remembered.
- Hide install UI when already standalone (`.hide-in-app`, or the `is-standalone` class).

## 8. Shared hosting / Apache (`public/.htaccess` additions; guard with IfModule)
```apache
<IfModule mod_mime.c>
  AddType application/manifest+json .webmanifest
</IfModule>
<IfModule mod_headers.c>
  <FilesMatch "^(sw\.js|manifest\.webmanifest|offline\.html)$">
    Header set Cache-Control "no-cache"
  </FilesMatch>
  <FilesMatch "^sw\.js$">
    Header set Service-Worker-Allowed "/"
  </FilesMatch>
</IfModule>
```
- HTTPS is mandatory (service workers do not register on insecure origins, except localhost).
- `sw.js` must be served as JavaScript from the site root, with no caching beyond revalidation.
- Laravel routes are not involved: all PWA files are static files in `public/`, so no existing route changes. Make sure no catch-all route or middleware rewrites them.
- Build assets locally (`npm run build`) and upload; the server needs no Node.

## 9. iOS specifics
- Add to Home Screen via Share sheet; no `beforeinstallprompt`; no Vibration API; storage can be evicted if the app is unused for a long period; Web Push exists for installed web apps on recent iOS versions but is out of scope here.
- Provide `apple-touch-icon` (180×180, no transparency). Splash images are generated from manifest/icon on recent iOS versions; verify on a device before adding legacy `apple-touch-startup-image` sets.
- Standalone iOS has no back button: always provide in-app back navigation.

## 10. App-like details (CSS/JS)
- Safe areas on every fixed element (`tokens.css` already uses `env(safe-area-inset-*)`).
- `touch-action: manipulation`, tap highlight off, press states, no accidental text selection on chrome, `overscroll-behavior`, `100dvh`.
- `@view-transition { navigation: auto; }` for smoother page changes where supported; harmless elsewhere.
- Preserve scroll position and state when navigating back (don't re-fetch with JS what the browser can restore).
- In standalone mode, external links open outside the app shell; mark them clearly and set `rel="noopener"`.

## 11. SEO interplay
- The service worker never changes what crawlers receive: they fetch from the network, and the pages remain fully server-rendered.
- Do not block `/sw.js`, `/manifest.webmanifest`, or `/icons/` in `robots.txt`.
- `offline.html` carries `noindex` and is excluded from the sitemap.
- No duplicate indexable URLs: `?source=pwa` has a canonical to the clean URL.
- Page speed gains from repeat-visit caching help Core Web Vitals in the field, but first-visit performance (what ranking data mostly reflects) must stand on its own: image optimization, font strategy, minimal JS.

## 12. Testing
- Chrome DevTools → Application: Manifest (installability warnings), Service Workers (status, update on reload), Cache Storage; Network → Offline to see `offline.html`; test an update by changing `VERSION`.
- Lighthouse no longer has a dedicated PWA category in recent versions; use the DevTools Application panel and Lighthouse performance/accessibility/SEO/best-practices.
- Real devices: Android Chrome (install prompt, standalone, back gesture, keyboard + tab bar), iOS Safari (Add to Home Screen, safe areas, status bar style, dark/light), iPad (top nav, orientation rotation, Split View).
- Confirm that `php artisan route:list` is unchanged and forms still post (the SW ignores non-GET).
- Report honestly what was tested versus what remains for device testing.
