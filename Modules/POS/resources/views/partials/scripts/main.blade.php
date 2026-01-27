<script>
// المتغيرات العامة - يجب أن تكون في النطاق العام
let cart = [];
let selectedCategory = '';
let selectedCustomer = {{ $clientsAccounts->first()->id ?? 0 }};
let selectedTable = null;
let invoiceNotes = '';
let isOnline = navigator.onLine;
let db = null;
let itemsCache = {};
let initialProductsData = [];
let scaleSettings = null; // إعدادات الميزان - يتم جلبها مرة واحدة

// تعريف دالة msg في النطاق العام قبل كل شيء
window.msg = function(message, type = 'success') {
    if (typeof jQuery === 'undefined') {
        alert(message);
        return;
    }
    const bgColor = type === 'success' ? '#28a745' : 
                    type === 'error' ? '#dc3545' : 
                    type === 'warning' ? '#ffc107' : '#17a2b8';
    const icon = type === 'success' ? 'fa-check-circle' : 
                 type === 'error' ? 'fa-exclamation-circle' : 
                 type === 'warning' ? 'fa-exclamation-triangle' : 'fa-info-circle';
    
    const toast = jQuery(`
        <div class="toast-notification" style="position: fixed; top: 20px; left: 50%; transform: translateX(-50%); background: ${bgColor}; color: white; padding: 1rem 2rem; border-radius: 8px; z-index: 9999; box-shadow: 0 4px 6px rgba(0,0,0,0.2); display: flex; align-items: center; gap: 0.5rem; min-width: 300px;">
            <i class="fas ${icon}"></i>
            <span>${message}</span>
        </div>
    `);
    jQuery('body').append(toast);
    setTimeout(function() {
        toast.fadeOut(function() {
            jQuery(this).remove();
        });
    }, 3000);
};

// تعريف showToast للتوافق مع الكود القديم
window.showToast = window.msg;

// التأكد من تحميل jQuery قبل تنفيذ الكود
(function() {
    function initPOS() {
        if (typeof jQuery === 'undefined') {
            setTimeout(initPOS, 50);
            return;
        }
        
        // الآن jQuery جاهز
        $(document).ready(function() {
            // تهيئة المتغيرات
            db = new POSIndexedDB();

            // Initialize IndexedDB
            db.open().then(() => {
                console.log('IndexedDB initialized');
                loadItemsFromIndexedDB();
            }).catch(err => {
                console.error('IndexedDB error:', err);
            });

            // تخزين بيانات الأصناف محلياً
            itemsCache = @json($itemsData);
            initialProductsData = @json($initialProductsData);
            
            // حفظ الأصناف في IndexedDB
            if (itemsCache && Object.keys(itemsCache).length > 0) {
                const itemsArray = Object.values(itemsCache);
                db.saveItems(itemsArray).then(() => {
                    console.log('Items saved to IndexedDB');
                });
            }

            // مراقبة حالة الاتصال
            window.addEventListener('online', function() {
                isOnline = true;
                updateOnlineStatus();
                syncPendingTransactions();
            });

            window.addEventListener('offline', function() {
                isOnline = false;
                updateOnlineStatus();
            });

            // تحديث حالة الاتصال
            function updateOnlineStatus() {
                const statusIndicator = $('#onlineStatus');
                if (isOnline) {
                    statusIndicator.removeClass('offline').addClass('online')
                        .html('<i class="fas fa-wifi"></i> متصل');
                } else {
                    statusIndicator.removeClass('online').addClass('offline')
                        .html('<i class="fas fa-wifi-slash"></i> غير متصل');
                }
            }

            // تحميل الأصناف من IndexedDB
            async function loadItemsFromIndexedDB() {
                try {
                    const items = await db.getAllItems();
                    if (items && items.length > 0) {
                        items.forEach(item => {
                            itemsCache[item.id] = item;
                        });
                        console.log('Loaded', items.length, 'items from IndexedDB');
                    }
                } catch (err) {
                    console.error('Error loading items from IndexedDB:', err);
                }
            }
            
            // جلب بيانات الصنف
            function getItemData(itemId) {
                if (itemsCache[itemId]) {
                    return Promise.resolve(itemsCache[itemId]);
                }
                
                return $.ajax({
                    url: '{{ route("pos.api.item-details", ":id") }}'.replace(':id', itemId),
                    method: 'GET'
                }).then(function(response) {
                    itemsCache[itemId] = response;
                    return response;
                });
            }

            // جلب إعدادات الميزان مرة واحدة
            async function loadScaleSettings() {
                try {
                    const settingsResponse = await $.ajax({
                        url: '{{ route("pos.api.settings.scale") }}',
                        method: 'GET',
                        timeout: 2000
                    });
                    if (settingsResponse.success) {
                        scaleSettings = settingsResponse;
                    }
                } catch (err) {
                    console.warn('فشل جلب إعدادات الميزان:', err);
                }
            }
            loadScaleSettings();

            // Initialize
            loadAllProducts();
            updateCartDisplay();
            updateOnlineStatus();
            updatePendingCount();
            
            // محاولة المزامنة كل 30 ثانية
            setInterval(function() {
                if (isOnline) {
                    syncPendingTransactions();
                }
            }, 30000);

            @include('pos::partials.scripts.search')
            @include('pos::partials.scripts.cart')
            @include('pos::partials.scripts.modals')
            @include('pos::partials.scripts.invoice')
            @include('pos::partials.scripts.sync')
            @include('pos::partials.scripts.recent-transactions')
            @include('pos::partials.scripts.held-orders')
            @include('pos::partials.scripts.helpers')
        });
    }
    
    // بدء التهيئة
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initPOS);
    } else {
        initPOS();
    }
})();
</script>
