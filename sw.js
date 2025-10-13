const CACHE_NAME = 'rubymed-crm-v1.0.0';
const OFFLINE_URL = '/offline.html';

// Archivos estáticos que se cachearán inmediatamente
const STATIC_CACHE_URLS = [
  '/',
  '/offline.html',
  '/assets/css/app_new.all.css',
  '/assets/css/style_ghost.css',
  '/assets/css/custom_style.css',
  '/assets/js/app.all.js',
  '/assets/bootstrap/css/bootstrap.min.css',
  '/assets/js/select2/select2.css',
  '/assets/js/select2/select2-bootstrap.min.css',
  '/assets/images/pwa-icon-192x192.png',
  '/assets/images/pwa-icon-512x512.png'
];

// Rutas que se cachearán dinámicamente
const DYNAMIC_CACHE_PATTERNS = [
  /^\/dashboard/,
  /^\/clients/,
  /^\/appointments/,
  /^\/projects/,
  /^\/tasks/,
  /^\/invoices/,
  /^\/estimates/,
  /^\/contracts/,
  /^\/team_members/,
  /^\/settings/,
  /^\/reports/,
  /^\/clockin/
];

// Instalación del Service Worker
self.addEventListener('install', event => {
  console.log('Service Worker: Instalando...');
  
  event.waitUntil(
    caches.open(CACHE_NAME)
      .then(cache => {
        console.log('Service Worker: Cacheando archivos estáticos');
        return cache.addAll(STATIC_CACHE_URLS);
      })
      .then(() => {
        console.log('Service Worker: Instalación completada');
        return self.skipWaiting();
      })
      .catch(error => {
        console.error('Service Worker: Error durante la instalación:', error);
      })
  );
});

// Activación del Service Worker
self.addEventListener('activate', event => {
  console.log('Service Worker: Activando...');
  
  event.waitUntil(
    caches.keys()
      .then(cacheNames => {
        return Promise.all(
          cacheNames.map(cacheName => {
            if (cacheName !== CACHE_NAME) {
              console.log('Service Worker: Eliminando cache antigua:', cacheName);
              return caches.delete(cacheName);
            }
          })
        );
      })
      .then(() => {
        console.log('Service Worker: Activación completada');
        return self.clients.claim();
      })
  );
});

// Interceptación de requests
self.addEventListener('fetch', event => {
  const { request } = event;
  const url = new URL(request.url);
  
  // Solo procesar requests GET
  if (request.method !== 'GET') {
    return;
  }
  
  // Estrategia para diferentes tipos de recursos
  if (isStaticAsset(request.url)) {
    // Cache First para archivos estáticos
    event.respondWith(cacheFirst(request));
  } else if (isAPIRequest(request.url)) {
    // Network First para APIs
    event.respondWith(networkFirst(request));
  } else if (isPageRequest(request)) {
    // Stale While Revalidate para páginas
    event.respondWith(staleWhileRevalidate(request));
  } else {
    // Network First por defecto
    event.respondWith(networkFirst(request));
  }
});

// Estrategia Cache First
async function cacheFirst(request) {
  try {
    const cachedResponse = await caches.match(request);
    if (cachedResponse) {
      return cachedResponse;
    }
    
    const networkResponse = await fetch(request);
    if (networkResponse.ok && networkResponse.status !== 206) {
      const cache = await caches.open(CACHE_NAME);
      cache.put(request, networkResponse.clone());
    }
    return networkResponse;
  } catch (error) {
    console.error('Cache First error:', error);
    return new Response('Error de red', { status: 503 });
  }
}

// Estrategia Network First
async function networkFirst(request) {
  try {
    const networkResponse = await fetch(request);
    if (networkResponse.ok && networkResponse.status !== 206) {
      const cache = await caches.open(CACHE_NAME);
      cache.put(request, networkResponse.clone());
    }
    return networkResponse;
  } catch (error) {
    console.log('Network First: Red no disponible, buscando en cache');
    const cachedResponse = await caches.match(request);
    if (cachedResponse) {
      return cachedResponse;
    }
    
    // Si es una página y no hay cache, mostrar página offline
    if (isPageRequest(request)) {
      return caches.match(OFFLINE_URL);
    }
    
    return new Response('Recurso no disponible offline', { status: 503 });
  }
}

// Estrategia Stale While Revalidate
async function staleWhileRevalidate(request) {
  const cache = await caches.open(CACHE_NAME);
  const cachedResponse = await cache.match(request);
  
  const fetchPromise = fetch(request).then(networkResponse => {
    if (networkResponse.ok && networkResponse.status !== 206) {
      cache.put(request, networkResponse.clone());
    }
    return networkResponse;
  }).catch(() => {
    // Si falla la red, devolver cache si existe
    return cachedResponse;
  });
  
  return cachedResponse || fetchPromise;
}

// Verificar si es un archivo estático
function isStaticAsset(url) {
  return url.includes('/assets/') || 
         url.includes('.css') || 
         url.includes('.js') || 
         url.includes('.png') || 
         url.includes('.jpg') || 
         url.includes('.jpeg') || 
         url.includes('.gif') || 
         url.includes('.svg') || 
         url.includes('.woff') || 
         url.includes('.woff2');
}

// Verificar si es una petición API
function isAPIRequest(url) {
  return url.includes('/api/') || 
         url.includes('ajax') || 
         url.includes('json') ||
         url.includes('clockin') ||
         url.includes('dashboard') ||
         url.includes('clients') ||
         url.includes('appointments');
}

// Verificar si es una petición de página
function isPageRequest(request) {
  return request.headers.get('accept').includes('text/html');
}

// Manejo de notificaciones push (opcional)
self.addEventListener('push', event => {
  if (event.data) {
    const data = event.data.json();
    const options = {
      body: data.body,
      icon: '/assets/images/pwa-icon-192x192.png',
      badge: '/assets/images/pwa-icon-72x72.png',
      vibrate: [100, 50, 100],
      data: {
        dateOfArrival: Date.now(),
        primaryKey: data.primaryKey
      },
      actions: [
        {
          action: 'explore',
          title: 'Ver detalles',
          icon: '/assets/images/pwa-icon-96x96.png'
        },
        {
          action: 'close',
          title: 'Cerrar',
          icon: '/assets/images/pwa-icon-96x96.png'
        }
      ]
    };
    
    event.waitUntil(
      self.registration.showNotification(data.title, options)
    );
  }
});

// Manejo de clics en notificaciones
self.addEventListener('notificationclick', event => {
  event.notification.close();
  
  if (event.action === 'explore') {
    event.waitUntil(
      clients.openWindow('/dashboard')
    );
  }
});

// Sincronización en segundo plano
self.addEventListener('sync', event => {
  if (event.tag === 'background-sync') {
    event.waitUntil(doBackgroundSync());
  }
});

async function doBackgroundSync() {
  // Implementar lógica de sincronización en segundo plano
  console.log('Realizando sincronización en segundo plano...');
}
