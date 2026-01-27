/**
 * POS IndexedDB Helper
 * إدارة التخزين المحلي للمعاملات والأصناف
 */

class POSIndexedDB {
    constructor() {
        this.dbName = 'POSDB';
        this.version = 1;
        this.db = null;
    }

    /**
     * فتح قاعدة البيانات
     */
    async open() {
        return new Promise((resolve, reject) => {
            const request = indexedDB.open(this.dbName, this.version);

            request.onerror = () => reject(request.error);
            request.onsuccess = () => {
                this.db = request.result;
                resolve(this.db);
            };

            request.onupgradeneeded = (event) => {
                const db = event.target.result;

                // Store للأصناف
                if (!db.objectStoreNames.contains('items')) {
                    const itemsStore = db.createObjectStore('items', { keyPath: 'id' });
                    itemsStore.createIndex('code', 'code', { unique: false });
                    itemsStore.createIndex('name', 'name', { unique: false });
                }

                // Store للتصنيفات
                if (!db.objectStoreNames.contains('categories')) {
                    db.createObjectStore('categories', { keyPath: 'id' });
                }

                // Store للمعاملات المعلقة
                if (!db.objectStoreNames.contains('transactions')) {
                    const transactionsStore = db.createObjectStore('transactions', { keyPath: 'local_id', autoIncrement: true });
                    transactionsStore.createIndex('sync_status', 'sync_status', { unique: false });
                    transactionsStore.createIndex('created_at', 'created_at', { unique: false });
                    transactionsStore.createIndex('server_id', 'server_id', { unique: false });
                }

                // Store لـ sync queue
                if (!db.objectStoreNames.contains('sync_queue')) {
                    const syncStore = db.createObjectStore('sync_queue', { keyPath: 'id', autoIncrement: true });
                    syncStore.createIndex('status', 'status', { unique: false });
                }
            };
        });
    }

    /**
     * حفظ صنف
     */
    async saveItem(item) {
        if (!this.db) await this.open();
        
        return new Promise((resolve, reject) => {
            const transaction = this.db.transaction(['items'], 'readwrite');
            const store = transaction.objectStore('items');
            const request = store.put(item);

            request.onsuccess = () => resolve(request.result);
            request.onerror = () => reject(request.error);
        });
    }

    /**
     * حفظ عدة أصناف
     */
    async saveItems(items) {
        if (!this.db) await this.open();
        
        const transaction = this.db.transaction(['items'], 'readwrite');
        const store = transaction.objectStore('items');
        
        const promises = items.map(item => {
            return new Promise((resolve, reject) => {
                const request = store.put(item);
                request.onsuccess = () => resolve();
                request.onerror = () => reject(request.error);
            });
        });

        return Promise.all(promises);
    }

    /**
     * جلب صنف
     */
    async getItem(itemId) {
        if (!this.db) await this.open();
        
        return new Promise((resolve, reject) => {
            const transaction = this.db.transaction(['items'], 'readonly');
            const store = transaction.objectStore('items');
            const request = store.get(itemId);

            request.onsuccess = () => resolve(request.result);
            request.onerror = () => reject(request.error);
        });
    }

    /**
     * جلب جميع الأصناف
     */
    async getAllItems() {
        if (!this.db) await this.open();
        
        return new Promise((resolve, reject) => {
            const transaction = this.db.transaction(['items'], 'readonly');
            const store = transaction.objectStore('items');
            const request = store.getAll();

            request.onsuccess = () => resolve(request.result);
            request.onerror = () => reject(request.error);
        });
    }

    /**
     * البحث في الأصناف
     */
    async searchItems(term) {
        if (!this.db) await this.open();
        
        return new Promise((resolve, reject) => {
            const transaction = this.db.transaction(['items'], 'readonly');
            const store = transaction.objectStore('items');
            const request = store.getAll();

            request.onsuccess = () => {
                const items = request.result;
                const filtered = items.filter(item => {
                    const searchTerm = term.toLowerCase();
                    const nameMatch = item.name && String(item.name).toLowerCase().includes(searchTerm);
                    const codeMatch = item.code && String(item.code).toLowerCase().includes(searchTerm);
                    return nameMatch || codeMatch;
                });
                resolve(filtered);
            };
            request.onerror = () => reject(request.error);
        });
    }

    /**
     * البحث بالباركود
     */
    async searchByBarcode(barcode) {
        if (!this.db) await this.open();
        
        return new Promise((resolve, reject) => {
            const transaction = this.db.transaction(['items'], 'readonly');
            const store = transaction.objectStore('items');
            const index = store.index('code');
            const request = index.getAll(barcode);

            request.onsuccess = () => {
                const items = request.result;
                // إذا لم يوجد تطابق دقيق، البحث الجزئي
                if (items.length === 0) {
                    const getAllRequest = store.getAll();
                    getAllRequest.onsuccess = () => {
                        const allItems = getAllRequest.result;
                        const filtered = allItems.filter(item => {
                            // التحقق من أن code موجود وأنه string
                            if (!item.code) return false;
                            const codeStr = String(item.code);
                            const barcodeStr = String(barcode);
                            return codeStr.toLowerCase().includes(barcodeStr.toLowerCase());
                        });
                        resolve(filtered);
                    };
                    getAllRequest.onerror = () => reject(getAllRequest.error);
                } else {
                    resolve(items);
                }
            };
            request.onerror = () => reject(request.error);
        });
    }

    /**
     * توليد UUID v4
     */
    generateUUID() {
        return 'xxxxxxxx-xxxx-4xxx-yxxx-xxxxxxxxxxxx'.replace(/[xy]/g, function(c) {
            const r = Math.random() * 16 | 0;
            const v = c === 'x' ? r : (r & 0x3 | 0x8);
            return v.toString(16);
        });
    }

    /**
     * حفظ معاملة معلقة
     */
    async saveTransaction(transaction) {
        if (!this.db) await this.open();
        
        // توليد UUID إذا لم يكن موجوداً
        const localId = transaction.local_id || this.generateUUID();
        
        const transactionData = {
            ...transaction,
            local_id: localId,
            server_id: null, // سيتم تحديثه بعد المزامنة
            sync_status: 'pending',
            created_at: new Date().toISOString(),
            updated_at: new Date().toISOString()
        };

        return new Promise((resolve, reject) => {
            const tx = this.db.transaction(['transactions'], 'readwrite');
            const store = tx.objectStore('transactions');
            const request = store.put(transactionData); // استخدام put بدلاً من add لتحديث إذا كان موجوداً

            request.onsuccess = () => resolve(localId);
            request.onerror = () => reject(request.error);
        });
    }

    /**
     * جلب المعاملات المعلقة
     */
    async getPendingTransactions() {
        if (!this.db) await this.open();
        
        return new Promise((resolve, reject) => {
            const transaction = this.db.transaction(['transactions'], 'readonly');
            const store = transaction.objectStore('transactions');
            const index = store.index('sync_status');
            const request = index.getAll('pending');

            request.onsuccess = () => resolve(request.result);
            request.onerror = () => reject(request.error);
        });
    }

    /**
     * تحديث حالة المعاملة بعد المزامنة
     */
    async updateTransactionStatus(localId, status) {
        if (!this.db) await this.open();
        
        return new Promise((resolve, reject) => {
            const tx = this.db.transaction(['transactions'], 'readwrite');
            const store = tx.objectStore('transactions');
            const getRequest = store.get(localId);

            getRequest.onsuccess = () => {
                const transaction = getRequest.result;
                if (transaction) {
                    transaction.sync_status = status;
                    transaction.updated_at = new Date().toISOString();
                    const updateRequest = store.put(transaction);
                    updateRequest.onsuccess = () => resolve();
                    updateRequest.onerror = () => reject(updateRequest.error);
                } else {
                    resolve();
                }
            };
            getRequest.onerror = () => reject(getRequest.error);
        });
    }

    /**
     * تحديث server_id للمعاملة بعد المزامنة
     */
    async updateTransactionServerId(localId, serverId) {
        if (!this.db) await this.open();
        
        return new Promise((resolve, reject) => {
            const tx = this.db.transaction(['transactions'], 'readwrite');
            const store = tx.objectStore('transactions');
            const getRequest = store.get(localId);

            getRequest.onsuccess = () => {
                const transaction = getRequest.result;
                if (transaction) {
                    transaction.server_id = serverId;
                    transaction.sync_status = 'synced';
                    transaction.updated_at = new Date().toISOString();
                    const updateRequest = store.put(transaction);
                    updateRequest.onsuccess = () => resolve();
                    updateRequest.onerror = () => reject(updateRequest.error);
                } else {
                    resolve();
                }
            };
            getRequest.onerror = () => reject(getRequest.error);
        });
    }

    /**
     * حذف معاملة بعد المزامنة الناجحة
     */
    async deleteTransaction(localId) {
        if (!this.db) await this.open();
        
        return new Promise((resolve, reject) => {
            const transaction = this.db.transaction(['transactions'], 'readwrite');
            const store = transaction.objectStore('transactions');
            const request = store.delete(localId);

            request.onsuccess = () => resolve();
            request.onerror = () => reject(request.error);
        });
    }

    /**
     * حفظ التصنيفات
     */
    async saveCategories(categories) {
        if (!this.db) await this.open();
        
        const transaction = this.db.transaction(['categories'], 'readwrite');
        const store = transaction.objectStore('categories');
        
        const promises = categories.map(category => {
            return new Promise((resolve, reject) => {
                const request = store.put(category);
                request.onsuccess = () => resolve();
                request.onerror = () => reject(request.error);
            });
        });

        return Promise.all(promises);
    }

    /**
     * جلب التصنيفات
     */
    async getCategories() {
        if (!this.db) await this.open();
        
        return new Promise((resolve, reject) => {
            const transaction = this.db.transaction(['categories'], 'readonly');
            const store = transaction.objectStore('categories');
            const request = store.getAll();

            request.onsuccess = () => resolve(request.result);
            request.onerror = () => reject(request.error);
        });
    }
}

// Export للاستخدام
window.POSIndexedDB = POSIndexedDB;
