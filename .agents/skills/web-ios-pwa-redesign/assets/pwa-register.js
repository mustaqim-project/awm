/* pwa-register.js — registers the service worker, handles updates and install prompts.
   Load with <script src="/pwa-register.js" defer></script> before </body> (or bundle it).
   It never draws UI on its own except the small update toast; install UI is yours (listen for the events below). */
(() => {
  const root = document.documentElement;
  const standalone = () => matchMedia('(display-mode: standalone)').matches || navigator.standalone === true;
  if (standalone()) root.classList.add('is-standalone');

  // ---- Install prompt (Chromium/Android) and iOS hint ----
  let deferred = null;
  window.addEventListener('beforeinstallprompt', (e) => {
    e.preventDefault();
    deferred = e;
    window.dispatchEvent(new CustomEvent('pwa:installable'));
  });
  window.addEventListener('appinstalled', () => { deferred = null; window.dispatchEvent(new CustomEvent('pwa:installed')); });
  window.pwaInstall = async () => {
    if (!deferred) return 'unavailable';
    deferred.prompt();
    const { outcome } = await deferred.userChoice;
    deferred = null;
    return outcome; // 'accepted' | 'dismissed'
  };
  const isIOS = /iphone|ipad|ipod/i.test(navigator.userAgent) || (navigator.platform === 'MacIntel' && navigator.maxTouchPoints > 1);
  // Safari has no install API. Show your own hint ("Bagikan → Tambahkan ke Layar Utama") when this is true.
  window.pwaNeedsIOSHint = () => isIOS && !standalone();

  // ---- Service worker + update flow ----
  if (!('serviceWorker' in navigator) || !window.isSecureContext) return;
  window.addEventListener('load', async () => {
    let reg;
    try { reg = await navigator.serviceWorker.register('/sw.js', { scope: '/' }); } catch (_) { return; }

    const showUpdate = (worker) => {
      if (document.getElementById('pwa-update')) return;
      const t = document.createElement('div');
      t.id = 'pwa-update'; t.className = 'toast'; t.setAttribute('role', 'status');
      t.innerHTML = '<span>Versi baru tersedia</span><button class="btn btn-quiet" type="button" style="min-height:32px;padding:0 14px">Muat ulang</button>';
      t.querySelector('button').addEventListener('click', () => worker.postMessage('SKIP_WAITING'));
      document.body.appendChild(t);
    };
    if (reg.waiting && navigator.serviceWorker.controller) showUpdate(reg.waiting);
    reg.addEventListener('updatefound', () => {
      const w = reg.installing;
      w && w.addEventListener('statechange', () => {
        if (w.state === 'installed' && navigator.serviceWorker.controller) showUpdate(w);
      });
    });
    let reloading = false;
    navigator.serviceWorker.addEventListener('controllerchange', () => { if (!reloading) { reloading = true; location.reload(); } });
    document.addEventListener('visibilitychange', () => { if (!document.hidden) reg.update().catch(() => {}); });
  });
})();
