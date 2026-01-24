@extends('pos::layouts.master')

@push('styles')
<style>
    .pos-create-container {
        height: 100vh;
        display: flex;
        flex-direction: column;
        background: #f5f5f5;
        overflow: hidden;
    }
    .product-card {
        cursor: pointer;
        transition: transform 0.2s;
    }
    .product-card:hover {
        transform: scale(1.02);
    }
    .category-btn.active {
        background: #FFD700 !important;
        border-color: #FFD700 !important;
    }
    #onlineStatus {
        transition: all 0.3s ease;
    }
    #onlineStatus.online {
        background-color: #28a745 !important;
        color: white;
    }
    #onlineStatus.offline {
        background-color: #dc3545 !important;
        color: white;
    }
</style>
@endpush

@section('content')
<div class="pos-create-container">
    
    {{-- Top Navigation Bar --}}
    <div class="pos-top-nav bg-white shadow-sm" style="padding: 1rem; border-bottom: 1px solid #e0e0e0;">
        <div class="d-flex align-items-center justify-content-between">
            {{-- Left Side: Menu & Logo --}}
            <div class="d-flex align-items-center gap-3">
                <a href="{{ route('admin.dashboard') }}" 
                   class="btn btn-link p-0" 
                   style="font-size: 1.5rem; text-decoration: none; color: #00695C;"
                   title="العودة للرئيسية">
                    <i class="fas fa-home"></i>
                </a>
                <button type="button" class="btn btn-link p-0" style="font-size: 1.5rem;">
                    <i class="fas fa-bars"></i>
                </button>
             
            </div>

            {{-- Center: Search Bars --}}
            <div class="flex-grow-1 mx-4 d-flex gap-2" style="max-width: 700px;">
                <div class="position-relative flex-grow-1">
                    <input type="text" 
                           id="productSearch"
                           class="form-control form-control-lg"
                           placeholder="البحث عن المنتجات..."
                           style="border-radius: 25px; padding-right: 45px;">
                    <i class="fas fa-search position-absolute" 
                       style="right: 15px; top: 50%; transform: translateY(-50%); color: #999;"></i>
                </div>
                <div class="position-relative" style="width: 200px;">
                    <input type="text" 
                           id="barcodeSearch"
                           class="form-control form-control-lg"
                           placeholder="البحث بالباركود..."
                           style="border-radius: 25px; padding-right: 45px;">
                    <i class="fas fa-barcode position-absolute" 
                       style="right: 15px; top: 50%; transform: translateY(-50%); color: #999;"></i>
                </div>
            </div>

            {{-- Right Side: Order Number, Orders, Register Button --}}
            <div class="d-flex align-items-center gap-3">
                {{-- Online Status Indicator --}}
                <div id="onlineStatus" class="badge" style="padding: 0.5rem 1rem; font-size: 0.85rem;">
                    <i class="fas fa-wifi"></i> متصل
                </div>
                <div class="d-flex align-items-center gap-2">
                    <span id="orderNumber" style="font-size: 1.2rem; font-weight: bold; color: #00695C;">{{ $nextProId }}</span>
                    <button type="button" class="btn btn-sm btn-outline-primary" style="border-radius: 50%; width: 30px; height: 30px; padding: 0;">
                        <i class="fas fa-plus"></i>
                    </button>
                </div>
                <button type="button" class="btn btn-link text-dark" style="text-decoration: none;">
                    <i class="fas fa-list me-1"></i> الطلبات
                </button>
                <button type="button" 
                        id="registerBtn"
                        class="btn btn-primary"
                        style="border-radius: 25px; padding: 0.5rem 1.5rem;">
                    <i class="fas fa-cash-register me-2"></i> تسجيل
                </button>
            </div>
        </div>
    </div>

    {{-- Category Filter Bar --}}
    <div class="pos-category-bar bg-white shadow-sm" style="padding: 0.75rem 1rem; border-bottom: 1px solid #e0e0e0;">
        <div class="d-flex gap-2">
            <button type="button" 
                    class="btn category-btn active"
                    data-category=""
                    style="border-radius: 20px; padding: 0.5rem 1.5rem; border: 2px solid #e0e0e0; background: white; color: #333;">
                الكل
            </button>
            @foreach($categories as $category)
                <button type="button" 
                        class="btn category-btn"
                        data-category="{{ $category->id }}"
                        style="border-radius: 20px; padding: 0.5rem 1.5rem; border: 2px solid #e0e0e0; background: white; color: #333;">
                    {{ $category->name }}
                </button>
            @endforeach
        </div>
    </div>

    {{-- Main Content Area: Split 2/3 Products + 1/3 Cart --}}
    <div class="d-flex flex-grow-1" style="overflow: hidden;">
        {{-- Products Grid: 2/3 of screen --}}
        <div class="pos-product-grid" style="flex: 2; overflow-y: auto; padding: 1.5rem; background: #f5f5f5; border-left: 1px solid #e0e0e0;">
            <div class="row g-3" id="productsGrid">
                @foreach($items as $item)
                    <div class="col-lg-4 col-md-6 col-sm-6">
                        <div class="product-card card h-100" 
                             data-item-id="{{ $item->id }}"
                             style="border: none; border-radius: 15px; overflow: hidden;">
                            <div class="product-image" style="height: 200px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); display: flex; align-items: center; justify-content: center;">
                                <i class="fas fa-box fa-4x text-white opacity-50"></i>
                            </div>
                            <div class="card-body p-3">
                                <h6 class="card-title mb-2" style="font-size: 0.95rem; font-weight: 600; color: #333;">
                                    {{ $item->name }}
                                </h6>
                                <div class="product-footer" style="height: 4px; background: #FFD700; border-radius: 2px;"></div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        {{-- Cart Sidebar: 1/3 of screen --}}
        <div class="pos-cart-sidebar bg-white shadow-sm" style="flex: 1; display: flex; flex-direction: column; border-right: 1px solid #e0e0e0;">
            {{-- Cart Header --}}
            <div class="p-3 border-bottom" style="background: #f8f9fa;">
                <div class="d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        <i class="fas fa-shopping-cart me-2"></i>
                        سلة التسوق
                    </h5>
                    <span class="badge bg-primary" id="cartItemsCount">0</span>
                </div>
            </div>

            {{-- Cart Items List --}}
            <div class="flex-grow-1" style="overflow-y: auto; padding: 1rem;">
                <div id="cartItems">
                    <div class="text-center py-5">
                        <i class="fas fa-shopping-cart fa-3x text-muted mb-3"></i>
                        <p class="text-muted">السلة فارغة</p>
                        <small class="text-muted">اختر المنتجات لإضافتها للسلة</small>
                    </div>
                </div>
            </div>

            {{-- Cart Footer: Totals --}}
            <div class="border-top bg-light p-3" style="background: #f8f9fa !important;">
                <div class="mb-2">
                    <div class="d-flex justify-content-between mb-1">
                        <span class="text-muted">المجموع الفرعي:</span>
                        <span id="cartSubtotal" class="fw-bold">0.00 ريال</span>
                    </div>
                    <div class="d-flex justify-content-between mb-1">
                        <span class="text-muted">الخصم:</span>
                        <span id="cartDiscount" class="text-danger">0.00 ريال</span>
                    </div>
                    <div class="d-flex justify-content-between mb-1">
                        <span class="text-muted">الإضافي:</span>
                        <span id="cartAdditional" class="text-success">0.00 ريال</span>
                    </div>
                </div>
                <hr>
                <div class="d-flex justify-content-between align-items-center">
                    <strong style="font-size: 1.1rem;">الإجمالي:</strong>
                    <strong class="text-primary" style="font-size: 1.5rem;" id="cartTotal">0.00 ريال</strong>
                </div>
            </div>
        </div>
    </div>

    {{-- Bottom Action Bar --}}
    <div class="pos-bottom-bar bg-white shadow-lg" style="padding: 1rem; border-top: 1px solid #e0e0e0;">
        <div class="d-flex align-items-center justify-content-end gap-3">
            <button type="button" 
                    id="customerBtn"
                    class="btn btn-outline-primary"
                    style="border-radius: 25px; padding: 0.75rem 1.5rem;">
                <i class="fas fa-user me-2"></i> العميل
            </button>
            <button type="button" 
                    id="paymentBtn"
                    class="btn btn-success"
                    style="border-radius: 25px; padding: 0.75rem 1.5rem;">
                <i class="fas fa-money-bill-wave me-2"></i> الدفع
            </button>
            <button type="button" 
                    id="notesBtn"
                    class="btn btn-outline-secondary"
                    style="border-radius: 25px; padding: 0.75rem 1.5rem;">
                <i class="fas fa-sticky-note me-2"></i> الملاحظات
            </button>
            <div class="dropdown">
                <button class="btn btn-outline-secondary" 
                        type="button" 
                        id="moreOptionsDropdown" 
                        data-bs-toggle="dropdown"
                        style="border-radius: 25px; padding: 0.75rem 1.5rem;">
                    <i class="fas fa-ellipsis-v"></i>
                </button>
                <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="moreOptionsDropdown">
                    <li><a class="dropdown-item" href="#" id="tableBtn"><i class="fas fa-table me-2"></i> اختيار الطاولة</a></li>
                    <li><a class="dropdown-item" href="#" id="resetBtn"><i class="fas fa-redo me-2"></i> إعادة تعيين</a></li>
                    <li><hr class="dropdown-divider"></li>
                    <li><a class="dropdown-item" href="{{ route('pos.index') }}"><i class="fas fa-home me-2"></i> العودة للرئيسية</a></li>
                </ul>
            </div>
        </div>
    </div>


    {{-- Bootstrap Modals --}}
    
    {{-- Payment Modal --}}
    <div class="modal fade" id="paymentModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">الدفع</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">الإجمالي</label>
                        <input type="text" class="form-control" id="paymentTotal" readonly>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">طريقة الدفع</label>
                        <select id="paymentMethod" class="form-select">
                            <option value="cash">نقدي</option>
                            <option value="card">بطاقة</option>
                            <option value="mixed">مختلط</option>
                        </select>
                    </div>
                    <div class="mb-3" id="cashAmountDiv">
                        <label class="form-label">المبلغ النقدي</label>
                        <input type="number" id="cashAmount" class="form-control" step="0.01" min="0">
                    </div>
                    <div class="mb-3" id="cardAmountDiv" style="display: none;">
                        <label class="form-label">مبلغ البطاقة</label>
                        <input type="number" id="cardAmount" class="form-control" step="0.01" min="0">
                    </div>
                    <div class="alert alert-success" id="changeAmountDiv" style="display: none;">
                        المبلغ المتبقي للعميل: <span id="changeAmount">0.00</span> ريال
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إلغاء</button>
                    <button type="button" id="saveAndPrintBtn" class="btn btn-primary">
                        <i class="fas fa-print me-2"></i> دفع وطباعة
                    </button>
                    <button type="button" id="saveOnlyBtn" class="btn btn-success">
                        <i class="fas fa-save me-2"></i> حفظ فقط
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- Customer Modal --}}
    <div class="modal fade" id="customerModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">اختيار العميل</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">العميل</label>
                        <select id="selectedCustomer" class="form-select">
                            @foreach($clientsAccounts as $client)
                                <option value="{{ $client->id }}">{{ $client->aname }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="alert alert-info" id="customerBalance">
                        <strong>رصيد العميل:</strong> <span id="balanceAmount">0.00</span> ريال
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إلغاء</button>
                    <button type="button" class="btn btn-primary" data-bs-dismiss="modal">حفظ</button>
                </div>
            </div>
        </div>
    </div>

    {{-- Notes Modal --}}
    <div class="modal fade" id="notesModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">الملاحظات</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">ملاحظات الفاتورة</label>
                        <textarea id="invoiceNotes" class="form-control" rows="5" placeholder="أدخل ملاحظات الفاتورة..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إلغاء</button>
                    <button type="button" class="btn btn-primary" data-bs-dismiss="modal">حفظ</button>
                </div>
            </div>
        </div>
    </div>

    {{-- Table Selection Modal --}}
    <div class="modal fade" id="tableModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">اختيار الطاولة</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-3">
                        @for($i = 1; $i <= 20; $i++)
                            <div class="col-md-3 col-sm-4 col-6">
                                <button type="button" 
                                        class="btn btn-outline-primary w-100 table-btn"
                                        data-table="{{ $i }}"
                                        style="height: 80px; border-radius: 10px;">
                                    <i class="fas fa-table d-block mb-2"></i>
                                    طاولة {{ $i }}
                                </button>
                            </div>
                        @endfor
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إلغاء</button>
                </div>
            </div>
        </div>
    </div>

    {{-- Modal: Pending Transactions --}}
    <div class="modal fade" id="pendingTransactionsModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">المعاملات المحفوظة محلياً</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div id="pendingTransactionsList" style="max-height: 400px; overflow-y: auto;">
                        <div class="text-center py-4">
                            <i class="fas fa-spinner fa-spin fa-2x text-muted"></i>
                            <p class="mt-2 text-muted">جاري التحميل...</p>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إغلاق</button>
                    <button type="button" class="btn btn-primary" id="syncAllPendingBtn">
                        <i class="fas fa-sync-alt me-1"></i> مزامنة الكل
                    </button>
                </div>
            </div>
        </div>
    </div>

</div>

@push('scripts')
{{-- IndexedDB Helper --}}
<script src="{{ asset('modules/pos/js/pos-indexeddb.js') }}"></script>

<script>
$(document).ready(function() {
    let cart = [];
    let selectedCategory = '';
    let selectedCustomer = {{ $clientsAccounts->first()->id ?? 0 }};
    let selectedTable = null;
    let invoiceNotes = '';
    let isOnline = navigator.onLine;
    let db = new POSIndexedDB();

    // Initialize IndexedDB
    db.open().then(() => {
        console.log('IndexedDB initialized');
        // تحميل البيانات من IndexedDB إذا كانت موجودة
        loadItemsFromIndexedDB();
    }).catch(err => {
        console.error('IndexedDB error:', err);
    });

    // تخزين بيانات الأصناف محلياً لتجنب AJAX calls
    let itemsCache = @json($itemsData);
    
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

    // تحديث حالة الاتصال في UI
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
                // دمج مع itemsCache
                items.forEach(item => {
                    itemsCache[item.id] = item;
                });
                console.log('Loaded', items.length, 'items from IndexedDB');
            }
        } catch (err) {
            console.error('Error loading items from IndexedDB:', err);
        }
    }
    
    // دالة لجلب بيانات الصنف من الـ cache أو الـ server
    function getItemData(itemId) {
        // محاولة جلب من الـ cache أولاً
        if (itemsCache[itemId]) {
            return Promise.resolve(itemsCache[itemId]);
        }
        
        // إذا لم يكن موجود في الـ cache، جلب من الـ server
        return $.ajax({
            url: '{{ route("pos.api.item-details", ":id") }}'.replace(':id', itemId),
            method: 'GET'
        }).then(function(response) {
            // حفظ في الـ cache للمرة القادمة
            itemsCache[itemId] = response;
            return response;
        });
    }

    // Initialize
    updateCartDisplay();
    updateOnlineStatus();
    updatePendingCount();
    
    // محاولة المزامنة كل 30 ثانية إذا كان online
    setInterval(function() {
        if (isOnline) {
            syncPendingTransactions();
        }
    }, 30000);

    // Product Search
    let searchTimeout;
    $('#productSearch').on('input', function() {
        clearTimeout(searchTimeout);
        const term = $(this).val();
        
        if (term.length < 2) {
            loadAllProducts();
            return;
        }

        searchTimeout = setTimeout(async function() {
            if (isOnline) {
                // البحث من الـ server
                $.ajax({
                    url: '{{ route("pos.api.search-items") }}',
                    method: 'GET',
                    data: { term: term },
                    success: function(response) {
                        displayProducts(response.items);
                        // حفظ النتائج في IndexedDB
                        if (response.items && response.items.length > 0) {
                            db.saveItems(response.items);
                        }
                    },
                    error: function() {
                        // في حالة الخطأ، البحث محلياً
                        searchLocal(term);
                    }
                });
            } else {
                // البحث محلياً من IndexedDB
                searchLocal(term);
            }
        }, 300);
    });

    // البحث المحلي
    async function searchLocal(term) {
        try {
            const items = await db.searchItems(term);
            displayProducts(items);
        } catch (err) {
            console.error('Local search error:', err);
        }
    }

    // Barcode Search
    let barcodeTimeout;
    $('#barcodeSearch').on('input', function() {
        clearTimeout(barcodeTimeout);
        const barcode = $(this).val().trim();
        
        if (barcode.length === 0) {
            loadAllProducts();
            return;
        }

        barcodeTimeout = setTimeout(async function() {
            if (isOnline) {
                // البحث من الـ server
                $.ajax({
                    url: '{{ route("pos.api.search-barcode") }}',
                    method: 'GET',
                    data: { barcode: barcode },
                    success: function(response) {
                        if (response.items && response.items.length > 0) {
                            displayProducts(response.items);
                            
                            // إذا كان هناك تطابق دقيق، إضافة المنتج للسلة مباشرة
                            if (response.exact_match && response.items.length === 1) {
                                const itemId = response.items[0].id;
                                addItemToCart(itemId);
                                // مسح حقل البحث بالباركود
                                $('#barcodeSearch').val('');
                                showToast('تم إضافة المنتج للسلة', 'success');
                            }
                            
                            // حفظ النتائج في IndexedDB
                            db.saveItems(response.items);
                        } else {
                            showToast('لم يتم العثور على منتج بهذا الباركود', 'error');
                        }
                    },
                    error: function() {
                        // في حالة الخطأ، البحث محلياً
                        searchBarcodeLocal(barcode);
                    }
                });
            } else {
                // البحث محلياً من IndexedDB
                searchBarcodeLocal(barcode);
            }
        }, 300);
    });

    // البحث بالباركود محلياً
    async function searchBarcodeLocal(barcode) {
        try {
            const items = await db.searchByBarcode(barcode);
            if (items && items.length > 0) {
                displayProducts(items);
                // إذا كان هناك منتج واحد فقط، إضافته للسلة
                if (items.length === 1) {
                    const itemId = items[0].id;
                    addItemToCart(itemId);
                    $('#barcodeSearch').val('');
                    showToast('تم إضافة المنتج للسلة', 'success');
                }
            } else {
                showToast('لم يتم العثور على منتج بهذا الباركود', 'error');
            }
        } catch (err) {
            console.error('Local barcode search error:', err);
        }
    }

    // Category Selection
    $('.category-btn').on('click', function() {
        $('.category-btn').removeClass('active');
        $(this).addClass('active');
        selectedCategory = $(this).data('category');

        if (selectedCategory) {
            if (isOnline) {
                $.ajax({
                    url: '{{ route("pos.api.category-items", ":id") }}'.replace(':id', selectedCategory),
                    method: 'GET',
                    success: function(response) {
                        displayProducts(response.items);
                        // حفظ في IndexedDB
                        if (response.items && response.items.length > 0) {
                            db.saveItems(response.items);
                        }
                    },
                    error: function() {
                        // في حالة الخطأ، استخدام البيانات المحلية
                        loadAllProducts();
                    }
                });
            } else {
                // استخدام البيانات المحلية
                loadAllProducts();
            }
        } else {
            loadAllProducts();
        }
    });

    // Add Product to Cart
    $(document).on('click', '.product-card', function() {
        const itemId = $(this).data('item-id');
        addItemToCart(itemId);
    });

    // Modal Triggers
    $('#registerBtn').on('click', function() {
        if (cart.length === 0) {
            alert('السلة فارغة');
            return;
        }
        $('#paymentTotal').val(calculateTotal().toFixed(2) + ' ريال');
        $('#paymentModal').modal('show');
    });

    $('#customerBtn').on('click', function() {
        $('#customerModal').modal('show');
    });

    $('#paymentBtn').on('click', function() {
        if (cart.length === 0) {
            alert('السلة فارغة');
            return;
        }
        $('#paymentTotal').val(calculateTotal().toFixed(2) + ' ريال');
        $('#paymentModal').modal('show');
    });

    $('#notesBtn').on('click', function() {
        $('#notesModal').modal('show');
    });

    $('#tableBtn').on('click', function(e) {
        e.preventDefault();
        $('#tableModal').modal('show');
    });

    // Table Selection
    $('.table-btn').on('click', function() {
        selectedTable = $(this).data('table');
        $('.table-btn').removeClass('btn-primary').addClass('btn-outline-primary');
        $(this).removeClass('btn-outline-primary').addClass('btn-primary');
    });

    // Payment Method Change
    $('#paymentMethod').on('change', function() {
        const method = $(this).val();
        if (method === 'cash') {
            $('#cashAmountDiv').show();
            $('#cardAmountDiv').hide();
        } else if (method === 'card') {
            $('#cashAmountDiv').hide();
            $('#cardAmountDiv').show();
        } else {
            $('#cashAmountDiv').show();
            $('#cardAmountDiv').show();
        }
    });

    // Calculate Change
    $('#cashAmount, #cardAmount').on('input', function() {
        calculateChange();
    });

    // Save Functions
    $('#saveAndPrintBtn').on('click', function() {
        saveInvoice(true);
    });

    $('#saveOnlyBtn').on('click', function() {
        saveInvoice(false);
    });

    // Functions
    function loadAllProducts() {
        // Load initial products from server
        // For now, products are loaded in the view
    }

    function displayProducts(items) {
        const grid = $('#productsGrid');
        grid.empty();
        
        items.forEach(function(item) {
            // حفظ بيانات الصنف في الـ cache عند عرضه
            if (!itemsCache[item.id]) {
                // إذا لم تكن البيانات كاملة، جلبها
                getItemData(item.id).then(function(fullData) {
                    itemsCache[item.id] = fullData;
                });
            }
            
            const card = `
                <div class="col-lg-4 col-md-6 col-sm-6">
                    <div class="product-card card h-100" data-item-id="${item.id}" style="border: none; border-radius: 15px; overflow: hidden; cursor: pointer;">
                        <div class="product-image" style="height: 200px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); display: flex; align-items: center; justify-content: center;">
                            <i class="fas fa-box fa-4x text-white opacity-50"></i>
                        </div>
                        <div class="card-body p-3">
                            <h6 class="card-title mb-2" style="font-size: 0.95rem; font-weight: 600; color: #333;">${item.name}</h6>
                            <div class="product-footer" style="height: 4px; background: #FFD700; border-radius: 2px;"></div>
                        </div>
                    </div>
                </div>
            `;
            grid.append(card);
        });
    }

    function addItemToCart(itemId) {
        // استخدام الـ cache أولاً (فوري)
        if (itemsCache[itemId]) {
            const response = itemsCache[itemId];
            addItemToCartFromData(response);
            return;
        }
        
        // إذا لم يكن في الـ cache، جلب من الـ server
        getItemData(itemId).then(function(response) {
            addItemToCartFromData(response);
        }).catch(function() {
            showToast('حدث خطأ أثناء جلب بيانات المنتج', 'error');
        });
    }

    function addItemToCartFromData(response) {
        const existingItem = cart.find(item => item.id === response.id);
        if (existingItem) {
            existingItem.quantity++;
        } else {
            cart.push({
                id: response.id,
                name: response.name,
                code: response.code,
                quantity: 1,
                price: response.prices[0]?.value || 0,
                unit_id: response.units[0]?.id || null,
                unit_name: response.units[0]?.name || 'قطعة'
            });
        }
        updateCartDisplay();
        
        // إظهار رسالة تأكيد
        showToast('تم إضافة المنتج للسلة', 'success');
    }

    function showToast(message, type = 'success') {
        // استخدام SweetAlert2 أو Bootstrap toast
        const bgColor = type === 'success' ? '#28a745' : '#dc3545';
        const toast = $(`
            <div class="toast-notification" style="position: fixed; top: 20px; left: 50%; transform: translateX(-50%); background: ${bgColor}; color: white; padding: 1rem 2rem; border-radius: 5px; z-index: 9999; box-shadow: 0 4px 6px rgba(0,0,0,0.1);">
                ${message}
            </div>
        `);
        $('body').append(toast);
        setTimeout(function() {
            toast.fadeOut(function() {
                $(this).remove();
            });
        }, 2000);
    }

    function updateCartDisplay() {
        const cartItems = $('#cartItems');
        cartItems.empty();

        if (cart.length === 0) {
            cartItems.html(`
                <div class="text-center py-5">
                    <i class="fas fa-shopping-cart fa-3x text-muted mb-3"></i>
                    <p class="text-muted">السلة فارغة</p>
                    <small class="text-muted">اختر المنتجات لإضافتها للسلة</small>
                </div>
            `);
            $('#cartItemsCount').text('0');
        } else {
            cart.forEach(function(item, index) {
                const subtotal = (item.quantity * item.price).toFixed(2);
                const cartItem = `
                    <div class="card mb-2 shadow-sm" style="border: 1px solid #e0e0e0;">
                        <div class="card-body p-2">
                            <div class="d-flex justify-content-between align-items-start mb-2">
                                <div class="flex-grow-1">
                                    <h6 class="mb-1 fw-bold" style="font-size: 0.9rem; color: #333;">${item.name}</h6>
                                    <small class="text-muted">${item.code || ''}</small>
                                </div>
                                <button type="button" 
                                        class="btn btn-sm btn-outline-danger remove-item"
                                        data-index="${index}"
                                        style="border: none; padding: 0.25rem 0.5rem;">
                                    <i class="fas fa-times"></i>
                                </button>
                            </div>
                            <div class="d-flex justify-content-between align-items-center">
                                <div class="d-flex align-items-center gap-2">
                                    <button type="button" 
                                            class="btn btn-sm btn-outline-secondary quantity-btn"
                                            data-index="${index}"
                                            data-action="decrease"
                                            style="width: 30px; height: 30px; padding: 0;">
                                        <i class="fas fa-minus" style="font-size: 0.7rem;"></i>
                                    </button>
                                    <input type="number" 
                                           class="form-control form-control-sm cart-quantity text-center" 
                                           data-index="${index}"
                                           value="${item.quantity}"
                                           style="width: 60px; height: 30px;"
                                           min="1">
                                    <button type="button" 
                                            class="btn btn-sm btn-outline-secondary quantity-btn"
                                            data-index="${index}"
                                            data-action="increase"
                                            style="width: 30px; height: 30px; padding: 0;">
                                        <i class="fas fa-plus" style="font-size: 0.7rem;"></i>
                                    </button>
                                    <span class="text-muted ms-2">x ${parseFloat(item.price).toFixed(2)} ريال</span>
                                </div>
                                <div class="text-end">
                                    <div class="fw-bold text-success" style="font-size: 1rem;">${subtotal} ريال</div>
                                </div>
                            </div>
                        </div>
                    </div>
                `;
                cartItems.append(cartItem);
            });
            $('#cartItemsCount').text(cart.length);
        }

        const subtotal = calculateTotal();
        const discount = 0; // يمكن إضافة خصم لاحقاً
        const additional = 0; // يمكن إضافة إضافي لاحقاً
        const total = subtotal - discount + additional;
        
        $('#cartSubtotal').text(subtotal.toFixed(2) + ' ريال');
        $('#cartDiscount').text(discount.toFixed(2) + ' ريال');
        $('#cartAdditional').text(additional.toFixed(2) + ' ريال');
        $('#cartTotal').text(total.toFixed(2) + ' ريال');
    }

    function calculateTotal() {
        return cart.reduce((sum, item) => sum + (item.quantity * item.price), 0);
    }

    function calculateChange() {
        const total = calculateTotal();
        const cash = parseFloat($('#cashAmount').val() || 0);
        const card = parseFloat($('#cardAmount').val() || 0);
        const paid = cash + card;
        const change = paid - total;

        if (change > 0) {
            $('#changeAmount').text(change.toFixed(2));
            $('#changeAmountDiv').show();
        } else {
            $('#changeAmountDiv').hide();
        }
    }

    // دالة توليد UUID v4
    function generateUUID() {
        return 'xxxxxxxx-xxxx-4xxx-yxxx-xxxxxxxxxxxx'.replace(/[xy]/g, function(c) {
            const r = Math.random() * 16 | 0;
            const v = c === 'x' ? r : (r & 0x3 | 0x8);
            return v.toString(16);
        });
    }

    async function saveInvoice(print = false) {
        // توليد UUID فريد
        const localId = generateUUID();
        
        const data = {
            local_id: localId, // UUID فريد
            items: cart,
            customer_id: selectedCustomer,
            table: selectedTable,
            notes: invoiceNotes,
            payment_method: $('#paymentMethod').val(),
            cash_amount: $('#cashAmount').val() || 0,
            card_amount: $('#cardAmount').val() || 0
        };

        // إذا كان offline، حفظ محلياً
        if (!isOnline) {
            try {
                const localId = await db.saveTransaction(data);
                showToast('تم الحفظ محلياً. سيتم المزامنة عند عودة الاتصال', 'info');
                $('#paymentModal').modal('hide');
                cart = [];
                updateCartDisplay();
                updatePendingCount();
                return;
            } catch (err) {
                showToast('حدث خطأ أثناء الحفظ المحلي', 'error');
                return;
            }
        }

        // إذا كان online، محاولة الحفظ على الـ server
        $.ajax({
            url: '{{ route("pos.api.store") }}',
            method: 'POST',
            data: data,
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function(response) {
                showToast('تم الحفظ بنجاح', 'success');
                if (print) {
                    // Open print window
                }
                $('#paymentModal').modal('hide');
                cart = [];
                updateCartDisplay();
                
                // إذا كان هناك local_id، تحديث IndexedDB بـ server_id
                if (response.server_id && data.local_id) {
                    db.updateTransactionServerId(data.local_id, response.server_id).then(() => {
                        console.log('Updated server_id for local_id:', data.local_id);
                    });
                }
            },
            error: function(xhr) {
                // في حالة الخطأ، حفظ محلياً
                db.saveTransaction(data).then(localId => {
                    showToast('تم الحفظ محلياً. سيتم المزامنة لاحقاً', 'warning');
                    $('#paymentModal').modal('hide');
                    cart = [];
                    updateCartDisplay();
                    updatePendingCount();
                }).catch(err => {
                    showToast('حدث خطأ أثناء الحفظ', 'error');
                });
            }
        });
    }

    // مزامنة المعاملات المعلقة
    async function syncPendingTransactions() {
        if (!isOnline) {
            console.log('Offline - cannot sync');
            return;
        }

        try {
            const pending = await db.getPendingTransactions();
            if (pending.length === 0) {
                console.log('No pending transactions');
                updatePendingCount();
                return;
            }

            console.log('Syncing', pending.length, 'transactions');

            // محاولة مزامنة كل معاملة على حدة
            let syncedCount = 0;
            let failedCount = 0;

            for (const transaction of pending) {
                try {
                    const response = await $.ajax({
                        url: '{{ route("pos.api.store") }}',
                        method: 'POST',
                        data: {
                            ...transaction,
                            local_id: transaction.local_id // إرسال local_id للمزامنة
                        },
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        }
                    });

                    // إذا تمت المزامنة بنجاح، حذف من IndexedDB
                    if (response.success && response.server_id) {
                        await db.deleteTransaction(transaction.local_id);
                        syncedCount++;
                        console.log('Synced transaction:', transaction.local_id, '-> server_id:', response.server_id);
                    } else {
                        throw new Error('Sync failed: ' + (response.message || 'Unknown error'));
                    }
                } catch (err) {
                    console.error('Failed to sync transaction:', transaction.local_id, err);
                    // تحديث حالة المعاملة
                    await db.updateTransactionStatus(transaction.local_id, 'failed');
                    failedCount++;
                }
            }

            updatePendingCount();
            
            if (syncedCount > 0) {
                showToast(`تمت مزامنة ${syncedCount} معاملة بنجاح`, 'success');
            }
            if (failedCount > 0) {
                showToast(`فشلت مزامنة ${failedCount} معاملة`, 'warning');
            }
        } catch (err) {
            console.error('Sync error:', err);
        }
    }

    // تحديث عدد المعاملات المعلقة
    async function updatePendingCount() {
        try {
            const pending = await db.getPendingTransactions();
            const count = pending.length;
            const badge = $('#pendingTransactionsBadge');
            
            if (count > 0) {
                badge.text(count).show();
            } else {
                badge.hide();
            }
        } catch (err) {
            console.error('Error updating pending count:', err);
        }
    }

    // Cart Quantity Update
    $(document).on('change', '.cart-quantity', function() {
        const index = $(this).data('index');
        const quantity = parseInt($(this).val());
        if (quantity > 0) {
            cart[index].quantity = quantity;
            updateCartDisplay();
        } else {
            $(this).val(1);
            cart[index].quantity = 1;
            updateCartDisplay();
        }
    });

    // Quantity Buttons
    $(document).on('click', '.quantity-btn', function() {
        const index = $(this).data('index');
        const action = $(this).data('action');
        
        if (action === 'increase') {
            cart[index].quantity++;
        } else if (action === 'decrease' && cart[index].quantity > 1) {
            cart[index].quantity--;
        }
        
        updateCartDisplay();
    });

    // Remove Item
    $(document).on('click', '.remove-item', function() {
        const index = $(this).data('index');
        if (confirm('هل تريد حذف هذا الصنف من السلة؟')) {
            cart.splice(index, 1);
            updateCartDisplay();
        }
    });

    // Notes Save
    $('#notesModal .btn-primary').on('click', function() {
        invoiceNotes = $('#invoiceNotes').val();
    });

    // Manual Sync Button
    $('#syncBtn').on('click', function() {
        const btn = $(this);
        const btnText = $('#syncBtnText');
        btn.prop('disabled', true);
        btnText.html('<i class="fas fa-spinner fa-spin"></i> جاري المزامنة...');
        
        syncPendingTransactions().then(() => {
            btn.prop('disabled', false);
            btnText.html('مزامنة');
        }).catch(() => {
            btn.prop('disabled', false);
            btnText.html('مزامنة');
        });
    });

    // عرض المعاملات المعلقة
    $('#pendingTransactionsModal').on('show.bs.modal', async function() {
        await loadPendingTransactionsList();
    });

    // تحميل قائمة المعاملات المعلقة
    async function loadPendingTransactionsList() {
        const listContainer = $('#pendingTransactionsList');
        listContainer.html('<div class="text-center py-4"><i class="fas fa-spinner fa-spin fa-2x text-muted"></i><p class="mt-2 text-muted">جاري التحميل...</p></div>');

        try {
            const pending = await db.getPendingTransactions();
            
            if (pending.length === 0) {
                listContainer.html('<div class="text-center py-4"><i class="fas fa-check-circle fa-2x text-success"></i><p class="mt-2 text-muted">لا توجد معاملات معلقة</p></div>');
                return;
            }

            let html = '<div class="list-group">';
            pending.forEach((transaction, index) => {
                const total = transaction.items ? transaction.items.reduce((sum, item) => sum + (item.quantity * item.price), 0) : 0;
                const date = new Date(transaction.created_at).toLocaleString('ar-SA');
                const statusBadge = transaction.sync_status === 'pending' ? '<span class="badge bg-warning">معلق</span>' : '<span class="badge bg-danger">فشل</span>';
                
                html += `
                    <div class="list-group-item">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <h6 class="mb-1">معاملة #${transaction.local_id}</h6>
                                <p class="mb-1 text-muted small">${date}</p>
                                <p class="mb-0"><strong>المجموع:</strong> ${total.toFixed(2)} ريال</p>
                                <p class="mb-0 small"><strong>عدد الأصناف:</strong> ${transaction.items ? transaction.items.length : 0}</p>
                            </div>
                            <div class="text-end">
                                ${statusBadge}
                            </div>
                        </div>
                    </div>
                `;
            });
            html += '</div>';
            listContainer.html(html);
        } catch (err) {
            console.error('Error loading pending transactions:', err);
            listContainer.html('<div class="alert alert-danger">حدث خطأ أثناء تحميل المعاملات</div>');
        }
    }

    // مزامنة الكل
    $('#syncAllPendingBtn').on('click', function() {
        const btn = $(this);
        btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin me-1"></i> جاري المزامنة...');
        
        syncPendingTransactions().then(() => {
            btn.prop('disabled', false).html('<i class="fas fa-sync-alt me-1"></i> مزامنة الكل');
            loadPendingTransactionsList();
            updatePendingCount();
        }).catch(() => {
            btn.prop('disabled', false).html('<i class="fas fa-sync-alt me-1"></i> مزامنة الكل');
        });
    });

    // تحسين showToast function
    function showToast(message, type = 'success') {
        const bgColor = type === 'success' ? '#28a745' : 
                        type === 'error' ? '#dc3545' : 
                        type === 'warning' ? '#ffc107' : '#17a2b8';
        const icon = type === 'success' ? 'fa-check-circle' : 
                     type === 'error' ? 'fa-exclamation-circle' : 
                     type === 'warning' ? 'fa-exclamation-triangle' : 'fa-info-circle';
        
        const toast = $(`
            <div class="toast-notification" style="position: fixed; top: 20px; left: 50%; transform: translateX(-50%); background: ${bgColor}; color: white; padding: 1rem 2rem; border-radius: 8px; z-index: 9999; box-shadow: 0 4px 6px rgba(0,0,0,0.2); display: flex; align-items: center; gap: 0.5rem; min-width: 300px;">
                <i class="fas ${icon}"></i>
                <span>${message}</span>
            </div>
        `);
        $('body').append(toast);
        setTimeout(function() {
            toast.fadeOut(function() {
                $(this).remove();
            });
        }, 3000);
    }
});
</script>
@endpush
@endsection
