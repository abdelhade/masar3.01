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

        {{-- Center: Product Search --}}
        <div class="flex-grow-1 mx-4" style="max-width: 500px;">
            <div class="position-relative">
                <input type="text" 
                       id="productSearch"
                       class="form-control form-control-lg"
                       placeholder="البحث عن المنتجات... (F2)"
                       style="border-radius: 25px; padding-right: 45px;">
                <i class="fas fa-search position-absolute" 
                   style="right: 15px; top: 50%; transform: translateY(-50%); color: #999;"></i>
            </div>
        </div>

        {{-- Right Side: Order Number, Orders, Register Button, Barcode Search --}}
        <div class="d-flex align-items-center gap-3">
            {{-- Dark Mode Toggle --}}
            <label class="dark-mode-switch" title="تبديل الوضع الداكن">
                <input type="checkbox" id="darkModeToggle">
                <span class="slider"></span>
            </label>
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
            <button type="button" 
                    id="pendingTransactionsBtn"
                    class="btn btn-link text-dark position-relative" 
                    style="text-decoration: none;">
                <i class="fas fa-list me-1"></i> الطلبات
                <span id="pendingTransactionsBadge" 
                      class="badge bg-danger position-absolute top-0 start-100 translate-middle" 
                      style="display: none; font-size: 0.7rem; padding: 0.25rem 0.5rem;">0</span>
            </button>
            <button type="button" 
                    id="heldOrdersBtn"
                    class="btn btn-link text-dark position-relative" 
                    style="text-decoration: none;"
                    title="الفواتير المعلقة">
                <i class="fas fa-pause-circle me-1"></i> الفواتير المعلقة
                <span id="heldOrdersBadge" 
                      class="badge bg-warning position-absolute top-0 start-100 translate-middle" 
                      style="display: none; font-size: 0.7rem; padding: 0.25rem 0.5rem;">0</span>
            </button>
            <button type="button" 
                    id="recentTransactionsBtn"
                    class="btn btn-link text-dark" 
                    style="text-decoration: none;"
                    title="آخر 50 عملية">
                <i class="fas fa-history me-1"></i> آخر العمليات
            </button>
            <a href="{{ route('pos.price-check') }}" 
               class="btn btn-link text-dark" 
               style="text-decoration: none;"
               title="فحص السعر بالباركود">
                <i class="fas fa-search-dollar me-1"></i> فحص السعر
            </a>
            <button type="button" 
                    id="refreshItemsBtn"
                    class="btn btn-link text-success" 
                    style="text-decoration: none;"
                    title="تحديث الأصناف من السيرفر">
                <i class="fas fa-sync-alt me-1"></i> تحديث الأصناف
            </button>
            <button type="button" 
                    id="registerBtn"
                    class="btn btn-primary"
                    style="border-radius: 25px; padding: 0.5rem 1.5rem;">
                <i class="fas fa-cash-register me-2"></i> تسجيل
            </button>
            {{-- Barcode Search - في أقصى اليمين --}}
            <div class="position-relative" style="width: 250px;">
                <input type="text" 
                       id="barcodeSearch"
                       class="form-control form-control-lg"
                       placeholder="البحث بالباركود... (F1)"
                       style="border-radius: 25px; padding-right: 45px;">
                <i class="fas fa-barcode position-absolute" 
                   style="right: 15px; top: 50%; transform: translateY(-50%); color: #999;"></i>
            </div>
        </div>
    </div>
</div>
