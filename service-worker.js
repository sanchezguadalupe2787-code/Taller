// D'Juniors - Service Worker
// Cachea únicamente los archivos estáticos (CSS, JS, íconos, logo) para que la
// interfaz cargue rápido y la app sea instalable. Las páginas PHP (datos del
// taller) siempre se piden a la red para no mostrar información desactualizada;
// si no hay conexión, se muestra una página de aviso en vez de un error.

const CACHE_VERSION = 'djuniors-v1';
const ARCHIVOS_ESTATICOS = [
    'css/style.css',
    'js/script.js',
    'manifest.json',
    'offline.html',
    'assets/img/logo-header.png',
    'assets/img/logo-full.png',
    'assets/icons/icon-192.png',
    'assets/icons/icon-512.png'
];

self.addEventListener('install', function (event) {
    event.waitUntil(
        caches.open(CACHE_VERSION).then(function (cache) {
            return cache.addAll(ARCHIVOS_ESTATICOS).catch(function () {
                // Si algún archivo no existe en este despliegue, no se bloquea la instalación.
            });
        }).then(function () {
            return self.skipWaiting();
        })
    );
});

self.addEventListener('activate', function (event) {
    event.waitUntil(
        caches.keys().then(function (nombres) {
            return Promise.all(
                nombres.filter(function (n) { return n !== CACHE_VERSION; })
                       .map(function (n) { return caches.delete(n); })
            );
        }).then(function () {
            return self.clients.claim();
        })
    );
});

function esArchivoEstatico(url) {
    return /\.(css|js|png|jpg|jpeg|svg|webp|ico|json)$/i.test(url.pathname);
}

self.addEventListener('fetch', function (event) {
    const req = event.request;
    if (req.method !== 'GET') return;

    const url = new URL(req.url);
    if (url.origin !== self.location.origin) return;

    if (esArchivoEstatico(url)) {
        // Estático: cache primero, red como respaldo.
        event.respondWith(
            caches.match(req).then(function (cacheado) {
                return cacheado || fetch(req).then(function (resp) {
                    const copia = resp.clone();
                    caches.open(CACHE_VERSION).then(function (cache) { cache.put(req, copia); });
                    return resp;
                });
            })
        );
        return;
    }

    if (req.mode === 'navigate') {
        // Navegación (páginas PHP): siempre red; si falla, página sin conexión.
        event.respondWith(
            fetch(req).catch(function () {
                return caches.match('offline.html');
            })
        );
    }
});
