@extends('admin.dash')

@section('content')
    @include('components.breadcrumb', [
        'title' => __('نقاط البيع'),
        'items' => [
            ['label' => __('الرئيسية'), 'url' => route('admin.dashboard')],
            ['label' => __('نقاط البيع'), 'url' => route('pos-vouchers.index')],
            ['label' => __('إنشاء عملية جديدة')],
        ],
    ])

    <div class="content-wrapper">
        <section class="content-header">
            <div class="container-fluid">
                <div class="row mb-2">
                    <div class="col-sm-6">
                        <h1 class="font-family-cairo fw-bold">
                            <i class="fas fa-cash-register me-2"></i>
                            نقاط البيع - إنشاء عملية جديدة
                        </h1>
                    </div>
                    <div class="col-sm-6 text-end">
                        <a href="{{ route('pos-vouchers.index') }}" class="btn btn-secondary">
                            <i class="fas fa-arrow-right me-1"></i>
                            العودة للقائمة
                        </a>
                    </div>
                </div>
            </div>
        </section>

        <section class="content">
            <div class="container-fluid">
                <!-- Alerts Section -->
                @if (session('success'))
                    <div class="alert alert-success alert-dismissible fade show">
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        <h5><i class="icon fas fa-check"></i> نجح!</h5>
                        {{ session('success') }}
                    </div>
                @endif

                @if (session('error'))
                    <div class="alert alert-danger alert-dismissible fade show">
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        <h5><i class="icon fas fa-ban"></i> خطأ!</h5>
                        {{ session('error') }}
                    </div>
                @endif

                @if ($errors->any())
                    <div class="alert alert-danger alert-dismissible fade show">
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        <h5><i class="icon fas fa-ban"></i> خطأ في البيانات!</h5>
                        <ul class="mb-0">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <!-- Livewire Component Section -->
                <div class="row g-3">
                    <!-- Left Side - Product Selection -->
                    <div class="col-lg-8">

        .product-card {
            transition: all 0.3s ease;
            border: 2px solid transparent;
            background: white;
            border-radius: 12px;
            min-height: 160px;
            position: relative;
            overflow: hidden;
        }

        .product-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 25px rgba(0,123,255,0.15);
            border-color: #28a745;
        }

        .product-card.in-cart {
            border-color: #28a745;
            background: linear-gradient(135deg, #f8fff8 0%, #ffffff 100%);
        }

        .quantity-badge {
            position: absolute;
            top: -5px;
            right: -5px;
            background: linear-gradient(45deg, #dc3545, #c82333);
            color: white;
            width: 25px;
            height: 25px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 12px;
            font-weight: bold;
            z-index: 10;
        }

        .category-btn {
            border-radius: 25px;
            transition: all 0.3s ease;
            margin: 2px;
        }

        .category-btn.active {
            background: linear-gradient(45deg, #28a745, #20c997);
            border-color: #28a745;
            transform: scale(1.05);
            box-shadow: 0 4px 15px rgba(40, 167, 69, 0.3);
        }

        .search-results {
            position: absolute;
            top: 100%;
            left: 0;
            right: 0;
            z-index: 1050;
            max-height: 400px;
            overflow-y: auto;
            background: white;
            border-radius: 8px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
        }

        .cart-section {
            background: white;
            border-radius: 15px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.08);
        }

        .cart-item {
            background: #f8f9fa;
            border-radius: 10px;
            transition: all 0.3s ease;
            border-left: 4px solid #28a745;
        }

        .cart-item:hover {
            background: #e9ecef;
            transform: translateX(-3px);
        }

        .quantity-controls {
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .quantity-btn {
            width: 30px;
            height: 30px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 14px;
        }

        .action-buttons {
            gap: 8px;
        }

        .action-btn {
            border-radius: 20px;
            transition: all 0.3s ease;
            font-size: 13px;
            padding: 8px 12px;
        }

        .action-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        }

        .pay-btn {
            background: linear-gradient(45deg, #28a745, #20c997);
            border: none;
            color: white;
            font-weight: bold;
            font-size: 16px;
            padding: 12px 20px;
        }

        .pay-btn:hover {
            background: linear-gradient(45deg, #218838, #1e7e34);
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(40, 167, 69, 0.4);
        }

        .totals-section {
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            border-radius: 12px;
        }

        .loading-overlay {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(255,255,255,0.9);
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 12px;
            z-index: 100;
        }

        .spinner {
            width: 40px;
            height: 40px;
            border: 4px solid #f3f3f3;
            border-top: 4px solid #28a745;
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        .empty-state {
            padding: 60px 20px;
            text-align: center;
            color: #6c757d;
        }

        .empty-state i {
            font-size: 4rem;
            margin-bottom: 20px;
            opacity: 0.5;
        }

        .alert {
            border-radius: 12px;
            border: none;
        }

        .price-display {
            background: linear-gradient(45deg, #28a745, #20c997);
            color: white;
            padding: 6px 12px;
            border-radius: 15px;
            font-weight: bold;
        }

        }
    }
</style>
@endpush

@push('scripts')
<script>
    // Auto-hide alerts after 5 seconds
    setTimeout(function() {
        $('.alert').fadeOut('slow');
    }, 5000);
    
    // Prevent form submission on Enter key except for search
    $(document).on('keydown', 'input:not([wire\\:model*="searchTerm"]), select', function(e) {
        if (e.keyCode === 13) {
            e.preventDefault();
            return false;
        }
    });
    
    // Focus on search input when page loads
    $(document).ready(function() {
        $('input[wire\\:model*="searchTerm"]').focus();
        
        // Keyboard shortcuts
        $(document).keydown(function(e) {
            // F1 - Focus search
            if (e.keyCode === 112) {
                e.preventDefault();
                $('input[wire\\:model*="searchTerm"]').focus().select();
            }
            // F2 - Clear cart (with confirmation)
            if (e.keyCode === 113) {
                e.preventDefault();
                if (confirm('هل تريد مسح السلة؟')) {
                    Livewire.find($('[wire\\:id]').first().attr('wire:id')).call('clearCart');
                }
            }
            // F3 - Save order
            if (e.keyCode === 114) {
                e.preventDefault();
                $('button[wire\\:click="save"]').click();
            }
        });
    });
    
    // Livewire event listeners
    document.addEventListener('livewire:init', () => {
        console.log('POS System initialized');
        
        // Listen for voucher saved event
        Livewire.on('voucher-saved', (voucherId) => {
            console.log('Voucher saved with ID:', voucherId);
            
            // Show success message with print option
            if (confirm('تم حفظ الطلب بنجاح! هل تريد طباعة الفاتورة؟')) {
                // Redirect to print page or open print dialog
                window.open(`/pos-vouchers/${voucherId}/print`, '_blank');
            }
        });
        
        // Listen for cart updates
        Livewire.on('cart-updated', () => {
            // Play sound or show animation
            console.log('Cart updated');
        });
        
        // Listen for errors
        Livewire.on('pos-error', (message) => {
            alert('خطأ: ' + message);
        });
    });
    
    // Custom functions for POS operations
    function addToCartByBarcode(barcode) {
        // Function to add items by barcode scanner
        Livewire.find($('[wire\\:id]').first().attr('wire:id')).call('addToCartByBarcode', barcode);
    }
    
    // Barcode scanner integration
    let barcodeBuffer = '';
    let barcodeTimeout;
    
    $(document).keypress(function(e) {
        // Only process if not focused on input fields
        if (!$('input, textarea, select').is(':focus')) {
            barcodeBuffer += String.fromCharCode(e.which);
            
            clearTimeout(barcodeTimeout);
            barcodeTimeout = setTimeout(function() {
                if (barcodeBuffer.length > 3) {
                    addToCartByBarcode(barcodeBuffer);
                }
                barcodeBuffer = '';
            }, 500);
        }
    });
    
    // Print functionality
    function printReceipt(voucherId) {
        const printWindow = window.open(`/pos-vouchers/${voucherId}/print`, '_blank');
        printWindow.onload = function() {
            printWindow.print();
        };
    }
    
    // Quick actions
    function quickPay() {
        $('button[wire\\:click="save"]').click();
    }
    
    function quickClear() {
        if (confirm('هل تريد مسح السلة؟')) {
            Livewire.find($('[wire\\:id]').first().attr('wire:id')).call('clearCart');
        }
    }
</script>
@endpush
    <div class="pos-container">
        <div class="container-fluid">
            <div class="row g-3">
                <!-- Left Side - Product Selection -->
                <div class="col-lg-8">
                    <div class="card border-0 shadow-sm">
                        <div class="card-header bg-white border-0 py-3">
                            <h5 class="mb-0 fw-bold text-dark">
                                <i class="fas fa-shopping-cart me-2 text-success"></i>
                                اختيار المنتجات
                            </h5>
                        </div>
                        <div class="card-body p-0">
                            <!-- Search Bar -->
                            <div class="p-3 border-bottom bg-light">
                                <div class="position-relative">
                                    <div class="input-group">
                                        <input type="text" 
                                               wire:model.live.debounce.300ms="searchTerm" 
                                               class="form-control border-0 shadow-none py-2" 
                                               placeholder="البحث عن منتج بالاسم أو الكود...">
                                        <button class="btn btn-outline-success border-0" type="button">
                                            <i class="fas fa-search"></i>
                                        </button>
                                    </div>
                                    
                                    @if(!empty($searchResults))
                                        <div class="search-results mt-2">
                                            <div class="p-3">
                                                <div class="row g-2">
                                                    @foreach($searchResults as $item)
                                                        <div class="col-md-6">
                                                            <div class="d-flex justify-content-between align-items-center p-2 border rounded cursor-pointer hover-bg-light" 
                                                                 wire:click="addToCart({{ $item['id'] }})" 
                                                                 style="cursor: pointer;">
                                                                <div>
                                                                    <strong>{{ \Str::limit($item['name'], 25) }}</strong>
                                                                    <br>
                                                                    <small class="text-muted">كود: {{ $item['code'] }}</small>
                                                                </div>
                                                                <div>
                                                                    @if(isset($item['prices'][0]['pivot']['price']))
                                                                        <span class="price-display">
                                                                            {{ number_format($item['prices'][0]['pivot']['price'], 2) }} ج.م
                                                                        </span>
                                                                    @endif
                                                                </div>
                                                            </div>
                                                        </div>
                                                    @endforeach
                                                </div>
                                            </div>
                                        </div>
                                    @endif
                                </div>
                            </div>

                            <!-- Categories -->
                            <div class="p-3 border-bottom">
                                <div class="d-flex flex-wrap justify-content-start">
                                    @foreach($notes as $note)
                                        <button class="btn btn-outline-success category-btn {{ $selectedNoteId == $note->id ? 'active' : '' }}"
                                                wire:click="selectNote({{ $note->id }})">
                                            <i class="fas fa-tag me-1"></i>
                                            {{ $note->name }}
                                        </button>
                                    @endforeach
                                </div>
                            </div>

                            <!-- Products Grid -->
                            <div class="p-3 position-relative">
                                @if($isLoading)
                                    <div class="loading-overlay">
                                        <div class="spinner"></div>
                                    </div>
                                @endif

                                @if(!empty($filteredItems))
                                    <div class="row g-3">
                                        @foreach($filteredItems as $item)
                                            @php
                                                $cartQuantity = $this->getCartQuantityForItem($item['id']);
                                            @endphp
                                            <div class="col-xl-2 col-lg-3 col-md-4 col-sm-6 col-6">
                                                <div class="product-card {{ $cartQuantity > 0 ? 'in-cart' : '' }}" 
                                                     wire:click="addToCart({{ $item['id'] }})"
                                                     style="cursor: pointer;">
                                                    
                                                    @if($cartQuantity > 0)
                                                        <div class="quantity-badge">
                                                            {{ $cartQuantity }}
                                                        </div>
                                                    @endif

                                                    <div class="card-body p-3 text-center d-flex flex-column justify-content-between h-100">
                                                        <div>
                                                            <h6 class="card-title fw-bold mb-2" style="font-size: 0.9rem; line-height: 1.2;">
                                                                {{ \Str::limit($item['name'], 30) }}
                                                            </h6>
                                                            <small class="text-muted d-block mb-2">{{ $item['code'] }}</small>
                                                        </div>
                                                        <div class="mt-auto">
                                                            @if(isset($item['prices'][0]['pivot']['price']))
                                                                <div class="price-display">
                                                                    {{ number_format($item['prices'][0]['pivot']['price'], 2) }} ج.م
                                                                </div>
                                                            @endif
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                @else
                                    <div class="empty-state">
                                        <i class="fas fa-box-open"></i>
                                        <h5>لا توجد منتجات</h5>
                                        <p class="text-muted">اختر تصنيفاً لعرض المنتجات</p>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Right Side - Cart -->
                <div class="col-lg-4">
                    <div class="cart-section h-100 d-flex flex-column">
                        <div class="card-header bg-white border-0 py-3">
                            <div class="d-flex justify-content-between align-items-center">
                                <h5 class="mb-0 fw-bold text-dark">
                                    <i class="fas fa-receipt me-2 text-primary"></i>
                                    ملخص الطلب
                                </h5>
                                @if(!empty($cartItems))
                                    <button wire:click="clearCart" 
                                            class="btn btn-sm btn-outline-danger"
                                            onclick="return confirm('هل تريد مسح السلة؟')">
                                        <i class="fas fa-trash me-1"></i>
                                        مسح
                                    </button>
                                @endif
                            </div>
                        </div>

                        <div class="card-body flex-grow-1 p-0 d-flex flex-column">
                            <!-- Customer Info -->
                            <div class="p-3 border-bottom">
                                <div class="row g-2">
                                    <div class="col-6">
                                        <input type="text" 
                                               wire:model="customer_name" 
                                               class="form-control form-control-sm" 
                                               placeholder="اسم العميل">
                                    </div>
                                    <div class="col-6">
                                        <input type="text" 
                                               wire:model="table_number" 
                                               class="form-control form-control-sm" 
                                               placeholder="رقم الطاولة">
                                    </div>
                                </div>
                            </div>

                            <!-- Cart Items -->
                            <div class="flex-grow-1 p-3" style="max-height: 400px; overflow-y: auto;">
                                @forelse($cartItems as $index => $item)
                                    <div class="cart-item p-3 mb-2">
                                        <div class="d-flex justify-content-between align-items-start mb-2">
                                            <div class="flex-grow-1">
                                                <h6 class="mb-1 fw-bold">{{ $item['name'] }}</h6>
                                                <small class="text-muted">كود: {{ $item['code'] }}</small>
                                            </div>
                                            <button wire:click="removeFromCart({{ $index }})" 
                                                    class="btn btn-sm btn-outline-danger ms-2">
                                                <i class="fas fa-times"></i>
                                            </button>
                                        </div>
                                        
                                        <div class="d-flex justify-content-between align-items-center">
                                            <div class="quantity-controls">
                                                <button wire:click="updateQuantity({{ $index }}, {{ $item['quantity'] - 1 }})" 
                                                        class="btn btn-sm btn-outline-secondary quantity-btn">
                                                    <i class="fas fa-minus"></i>
                                                </button>
                                                <span class="fw-bold px-2">{{ $item['quantity'] }}</span>
                                                <button wire:click="updateQuantity({{ $index }}, {{ $item['quantity'] + 1 }})" 
                                                        class="btn btn-sm btn-outline-secondary quantity-btn">
                                                    <i class="fas fa-plus"></i>
                                                </button>
                                            </div>
                                            <div class="text-end">
                                                <div class="fw-bold">{{ number_format($item['total_price'], 2) }} ج.م</div>
                                                <small class="text-muted">{{ number_format($item['price'], 2) }} × {{ $item['quantity'] }}</small>
                                            </div>
                                        </div>
                                    </div>
                                @empty
                                    <div class="empty-state">
                                        <i class="fas fa-shopping-cart"></i>
                                        <h6>السلة فارغة</h6>
                                        <p class="text-muted small">أضف منتجات لبدء الطلب</p>
                                    </div>
                                @endforelse
                            </div>

                            <!-- Totals -->
                            @if(!empty($cartItems))
                                <div class="totals-section p-3 m-3">
                                    <div class="d-flex justify-content-between mb-2">
                                        <span>المجموع الفرعي:</span>
                                        <span class="fw-bold">{{ number_format($subtotal, 2) }} ج.م</span>
                                    </div>
                                    <div class="d-flex justify-content-between mb-2">
                                        <span>الضرائب ({{ $tax_rate }}%):</span>
                                        <span class="fw-bold">{{ number_format($tax_value, 2) }} ج.م</span>
                                    </div>
                                    <hr>
                                    <div class="d-flex justify-content-between">
                                        <span class="fw-bold fs-5">الإجمالي:</span>
                                        <span class="fw-bold fs-5 text-success">{{ number_format($total, 2) }} ج.م</span>
                                    </div>
                                </div>
                            @endif

                            <!-- Notes -->
                            <div class="p-3 border-top">
                                <textarea wire:model="notes_field" 
                                          class="form-control form-control-sm" 
                                          rows="2" 
                                          placeholder="ملاحظات الطلب..."></textarea>
                            </div>

                            <!-- Action Buttons -->
                            <div class="p-3 border-top">
                                <div class="action-buttons d-grid gap-2">
                                    <div class="row g-2">
                                        <div class="col-6">
                                            <button wire:click="setTakeaway(false)" 
                                                    class="btn action-btn btn-outline-primary w-100 {{ !$is_takeaway ? 'active' : '' }}">
                                                <i class="fas fa-utensils me-1"></i>
                                                محلي
                                            </button>
                                        </div>
                                        <div class="col-6">
                                            <button wire:click="setTakeaway(true)" 
                                                    class="btn action-btn btn-outline-warning w-100 {{ $is_takeaway ? 'active' : '' }}">
                                                <i class="fas fa-shopping-bag me-1"></i>
                                                تيك أواي
                                            </button>
                                        </div>
                                    </div>
                                    
                                    <button wire:click="save" 
                                            class="btn pay-btn w-100 position-relative"
                                            {{ empty($cartItems) || $isSaving ? 'disabled' : '' }}>
                                        @if($isSaving)
                                            <div class="spinner-border spinner-border-sm me-2" role="status"></div>
                                            جاري الحفظ...
                                        @else
                                            <i class="fas fa-credit-card me-2"></i>
                                            دفع والطباعة
                                        @endif
                                    </button>
                </div>
            </div>
        </section>
    </div>
@endsection

@push('styles')
<style>
    .font-family-cairo {
        font-family: 'Cairo', sans-serif;
    }