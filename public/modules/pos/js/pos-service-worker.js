/**
 * POS Service Worker - Offline First
 * يوفر قدرات offline-first للمعاملات والمزامنة
 */

const CACHE_VERSION = 'pos-v1.0.0';
const CACHE_NAME = `${CACHE_VERSION}`;
const DYNAMIC_CACHE_NAME = `${CACHE_VERSION}-dynamic`;

// الملفات المطلوبة للعمل offline
const STATIC_CACHE_URLS = [
    '/pos/create',
    '/css/app.css',
    '/js/app.js',
];

/**
 * Install Event
 */
self.addEventListener('install', (event) => {
    console.log('[POS SW] Installing...');
    
    event.waitUntil(
        caches.open(CACHE_NAME)
            .then((cache) => {
                console.log('[POS SW] Caching static assets');
                return cache.addAll(STATIC_CACHE_URLS);
            })
            .then(() => {
                console.log('[POS SW] Installation complete');
                return self.skipWaiting();
            })
            .catch((error) => {
                console.error('[POS SW] Installation failed:', error);
            })
    );
});

/**
 * Activate Event
 */
self.addEventListener('activate', (event) => {
    console.log('[POS SW] Activating...');
    
    event.waitUntil(
        caches.keys()
            .then((cacheNames) => {
                return Promise.all(
                    cacheNames
                        .filter((cacheName) => {
                            return cacheName.startsWith('pos-') && 
                                   cacheName !== CACHE_NAME &&
                                   cacheName !== DYNAMIC_CACHE_NAME;
                        })
                        .map((cacheName) => {
                            console.log('[POS SW] Deleting old cache:', cacheName);
                            return caches.delete(cacheName);
                        })
                );
            })
            .then(() => {
                console.log('[POS SW] Activation complete');
                return self.clients.claim();
            })
    );
});

/**
 * Fetch Event - Network First Strategy
 */
self.addEventListener('fetch', (event) => {
    const { request } = event;
    const url = new URL(request.url);

    // تجاهل طلبات chrome-extension
    if (url.protocol === 'chrome-extension:') {
        return;
    }

    // API calls - Network First
    if (url.pathname.startsWith('/pos/api/')) {
        event.respondWith(networkFirstStrategy(request));
        return;
    }

    // Static assets - Cache First
    if (
        request.destination === 'style' ||
        request.destination === 'script' ||
        request.destination === 'image' ||
        request.destination === 'font'
    ) {
        event.respondWith(cacheFirstStrategy(request));
        return;
    }

    // Pages - Network First with Cache fallback
    if (request.mode === 'navigate') {
        event.respondWith(navigateStrategy(request));
        return;
    }

    // Default - Network First
    event.respondWith(networkFirstStrategy(request));
});

/**
 * Network First Strategy
 */
async function networkFirstStrategy(request) {
    try {
        const networkResponse = await fetch(request);
        
        if (networkResponse.ok) {
            const cache = await caches.open(DYNAMIC_CACHE_NAME);
            cache.put(request, networkResponse.clone());
        }
        
        return networkResponse;
    } catch (error) {
        console.log('[POS SW] Network failed, trying cache:', request.url);
        const cachedResponse = await caches.match(request);
        
        if (cachedResponse) {
            return cachedResponse;
        }
        
        throw error;
    }
}

/**
 * Cache First Strategy
 */
async function cacheFirstStrategy(request) {
    const cachedResponse = await caches.match(request);
    
    if (cachedResponse) {
        return cachedResponse;
    }
    
    try {
        const networkResponse = await fetch(request);
        
        if (networkResponse.ok) {
            const cache = await caches.open(CACHE_NAME);
            cache.put(request, networkResponse.clone());
        }
        
        return networkResponse;
    } catch (error) {
        throw error;
    }
}

/**
 * Navigate Strategy
 */
async function navigateStrategy(request) {
    try {
        const networkResponse = await fetch(request);
        
        if (networkResponse.ok) {
            const cache = await caches.open(DYNAMIC_CACHE_NAME);
            cache.put(request, networkResponse.clone());
        }
        
        return networkResponse;
    } catch (error) {
        const cachedResponse = await caches.match(request);
        if (cachedResponse) {
            return cachedResponse;
        }
        throw error;
    }
}

/**
 * Background Sync Event
 */
self.addEventListener('sync', (event) => {
    console.log('[POS SW] Background sync triggered:', event.tag);
    
    if (event.tag === 'sync-pos-transactions') {
        event.waitUntil(syncPendingTransactions());
    }
});

/**
 * مزامنة المعاملات المعلقة
 */
async function syncPendingTransactions() {
    try {
        console.log('[POS SW] Starting transaction sync...');
        
        // إرسال رسالة للصفحة لبدء المزامنة
        const clients = await self.clients.matchAll();
        clients.forEach(client => {
            client.postMessage({
                type: 'SYNC_TRANSACTIONS'
            });
        });
        
    } catch (error) {
        console.error('[POS SW] Sync failed:', error);
        throw error;
    }
}

/**
 * Message Handler
 */
self.addEventListener('message', (event) => {
    if (event.data && event.data.type === 'SKIP_WAITING') {
        self.skipWaiting();
    }
});
