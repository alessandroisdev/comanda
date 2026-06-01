const CACHE_NAME = 'comanda-cache-v1';
const ASSETS_TO_CACHE = [
  '/',
  '/manifest.json',
  '/favicon.ico',
  '/robots.txt'
];

// Instalação do Service Worker e caching inicial de ativos essenciais
self.addEventListener('install', (event) => {
  event.waitUntil(
    caches.open(CACHE_NAME).then((cache) => {
      console.log('[Service Worker] Caching app shell assets');
      return cache.addAll(ASSETS_TO_CACHE);
    }).then(() => self.skipWaiting())
  );
});

// Ativação e limpeza de caches antigos obsoletos
self.addEventListener('activate', (event) => {
  event.waitUntil(
    caches.keys().then((cacheNames) => {
      return Promise.all(
        cacheNames.map((cacheName) => {
          if (cacheName !== CACHE_NAME) {
            console.log('[Service Worker] Deleting old cache:', cacheName);
            return caches.delete(cacheName);
          }
        })
      );
    }).then(() => self.clients.claim())
  );
});

// Intercepção de requisições de rede com estratégias dinâmicas
self.addEventListener('fetch', (event) => {
  const requestUrl = new URL(event.request.url);

  // 1. Pular requisições POST, PUT, DELETE, PATCH (métodos não-GET não podem ser cacheados)
  if (event.request.method !== 'GET') {
    return;
  }

  // 2. Canal SSE ou APIs operacionais em tempo real: Network-Only para evitar cache indesejado
  if (requestUrl.pathname.startsWith('/sse/') || requestUrl.pathname.startsWith('/api/')) {
    event.respondWith(fetch(event.request));
    return;
  }

  // 3. Estratégia Stale-While-Revalidate para recursos estáticos locais (CSS, JS, Imagens, Fontes)
  if (
    requestUrl.origin === self.location.origin &&
    (requestUrl.pathname.startsWith('/build/') ||
      requestUrl.pathname.startsWith('/js/') ||
      ASSETS_TO_CACHE.includes(requestUrl.pathname))
  ) {
    event.respondWith(
      caches.open(CACHE_NAME).then((cache) => {
        return cache.match(event.request).then((cachedResponse) => {
          const fetchPromise = fetch(event.request).then((networkResponse) => {
            if (networkResponse && networkResponse.status === 200) {
              cache.put(event.request, networkResponse.clone());
            }
            return networkResponse;
          }).catch(() => {
            console.warn('[Service Worker] Failed fetch for asset, serving from cache:', requestUrl.pathname);
          });
          return cachedResponse || fetchPromise;
        });
      })
    );
    return;
  }

  // 4. Estratégia Network-First com Fallback de Cache para demais páginas públicas/Blade
  event.respondWith(
    fetch(event.request)
      .then((networkResponse) => {
        if (networkResponse && networkResponse.status === 200) {
          const responseClone = networkResponse.clone();
          caches.open(CACHE_NAME).then((cache) => {
            cache.put(event.request, responseClone);
          });
        }
        return networkResponse;
      })
      .catch(() => {
        return caches.match(event.request).then((cachedResponse) => {
          if (cachedResponse) {
            return cachedResponse;
          }
          // Caso a página offline específica não esteja no cache, retorna uma resposta mockada amigável offline
          if (event.request.headers.get('accept').includes('text/html')) {
            return new Response(
              `<!DOCTYPE html>
              <html lang="pt-BR">
              <head>
                <meta charset="UTF-8">
                <meta name="viewport" content="width=device-width, initial-scale=1.0">
                <title>Modo Offline — Comanda</title>
                <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
                <style>
                  body {
                    background: #0f172a;
                    color: #f1f5f9;
                    font-family: 'Inter', sans-serif;
                    display: flex;
                    align-items: center;
                    justify-content: center;
                    height: 100vh;
                    margin: 0;
                    text-align: center;
                  }
                  .container {
                    padding: 2rem;
                    border: 1px solid #1e293b;
                    border-radius: 12px;
                    background: #1e293b;
                    max-width: 400px;
                  }
                  h1 { font-size: 1.5rem; margin-bottom: 1rem; color: #ef4444; }
                  p { color: #94a3b8; font-size: 0.95rem; line-height: 1.5; }
                  .btn {
                    display: inline-block;
                    margin-top: 1.5rem;
                    padding: 0.6rem 1.2rem;
                    background: #3b82f6;
                    color: white;
                    text-decoration: none;
                    border-radius: 6px;
                    font-weight: 600;
                  }
                </style>
              </head>
              <body>
                <div class="container">
                  <h1>Conexão perdida</h1>
                  <p>Você está temporariamente offline. Verifique sua conexão com a internet para continuar enviando e acompanhando pedidos.</p>
                  <a href="" class="btn" onclick="window.location.reload(); return false;">Tentar Novamente</a>
                </div>
              </body>
              </html>`,
              {
                status: 200,
                headers: { 'Content-Type': 'text/html; charset=utf-8' }
              }
            );
          }
        });
      })
  );
});
