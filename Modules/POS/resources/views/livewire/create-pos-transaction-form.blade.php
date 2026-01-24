{{-- POS Create Transaction Form - New Design --}}
<div class="pos-create-container" style="height: 100vh; display: flex; flex-direction: column; background: #f5f5f5; overflow: hidden;">
    
    {{-- Top Navigation Bar --}}
    <div class="pos-top-nav bg-white shadow-sm" style="padding: 1rem; border-bottom: 1px solid #e0e0e0;">
        <div class="d-flex align-items-center justify-content-between">
            {{-- Left Side: Menu & Logo --}}
            <div class="d-flex align-items-center gap-3">
                <button type="button" class="btn btn-link p-0" style="font-size: 1.5rem;">
                    <i class="fas fa-bars"></i>
                </button>
                <div class="logo" style="font-size: 1.5rem; font-weight: bold; color: #00695C;">Z</div>
            </div>

            {{-- Center: Search Bar --}}
            <div class="flex-grow-1 mx-4" style="max-width: 500px;">
                <div class="position-relative">
                    <input type="text" 
                           wire:model.live.debounce.300ms="searchTerm"
                           class="form-control form-control-lg"
                           placeholder="البحث عن المنتجات..."
                           style="border-radius: 25px; padding-right: 45px;">
                    <i class="fas fa-search position-absolute" 
                       style="right: 15px; top: 50%; transform: translateY(-50%); color: #999;"></i>
                </div>
            </div>

            {{-- Right Side: Order Number, Orders, Register Button --}}
            <div class="d-flex align-items-center gap-3">
                <div class="d-flex align-items-center gap-2">
                    <span style="font-size: 1.2rem; font-weight: bold; color: #00695C;">{{ $pro_id }}</span>
                    <button type="button" class="btn btn-sm btn-outline-primary" style="border-radius: 50%; width: 30px; height: 30px; padding: 0;">
                        <i class="fas fa-plus"></i>
                    </button>
                </div>
                <button type="button" class="btn btn-link text-dark" style="text-decoration: none;">
                    <i class="fas fa-list me-1"></i> الطلبات
                </button>
                <button type="button" 
                        wire:click="openPaymentModal"
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
                    wire:click="selectCategory(null)"
                    class="btn category-btn {{ $selectedCategory === null ? 'active' : '' }}"
                    style="border-radius: 20px; padding: 0.5rem 1.5rem; border: 2px solid #e0e0e0; background: white; color: #333;">
                الكل
            </button>
            @foreach($categories as $category)
                <button type="button" 
                        wire:click="selectCategory({{ $category['id'] }})"
                        class="btn category-btn {{ $selectedCategory == $category['id'] ? 'active' : '' }}"
                        style="border-radius: 20px; padding: 0.5rem 1.5rem; border: 2px solid #e0e0e0; background: {{ $selectedCategory == $category['id'] ? '#FFD700' : 'white' }}; color: #333;">
                    {{ $category['name'] }}
                </button>
            @endforeach
        </div>
    </div>

    {{-- Main Content: Product Grid --}}
    <div class="pos-product-grid flex-grow-1" style="overflow-y: auto; padding: 1.5rem; background: #f5f5f5;">
        <div class="row g-3">
            @if($searchTerm && count($searchResults) > 0)
                {{-- Search Results --}}
                @foreach($searchResults as $item)
                    <div class="col-lg-3 col-md-4 col-sm-6">
                        <div class="product-card card h-100" 
                             wire:click="addItemFromSearch({{ $item['id'] }})"
                             style="cursor: pointer; border: none; border-radius: 15px; overflow: hidden; transition: transform 0.2s;"
                             onmouseover="this.style.transform='scale(1.02)'"
                             onmouseout="this.style.transform='scale(1)'">
                            <div class="product-image" style="height: 200px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); display: flex; align-items: center; justify-content: center;">
                                <i class="fas fa-box fa-4x text-white opacity-50"></i>
                            </div>
                            <div class="card-body p-3">
                                <h6 class="card-title mb-2" style="font-size: 0.95rem; font-weight: 600; color: #333;">
                                    {{ $item['name'] ?? '' }}
                                </h6>
                                <div class="product-footer" style="height: 4px; background: #FFD700; border-radius: 2px;"></div>
                            </div>
                        </div>
                    </div>
                @endforeach
            @elseif($selectedCategory && count($categoryItems) > 0)
                {{-- Category Items --}}
                @foreach($categoryItems as $item)
                    <div class="col-lg-3 col-md-4 col-sm-6">
                        <div class="product-card card h-100" 
                             wire:click="addItemFromSearch({{ $item['id'] }})"
                             style="cursor: pointer; border: none; border-radius: 15px; overflow: hidden; transition: transform 0.2s;"
                             onmouseover="this.style.transform='scale(1.02)'"
                             onmouseout="this.style.transform='scale(1)'">
                            <div class="product-image" style="height: 200px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); display: flex; align-items: center; justify-content: center;">
                                <i class="fas fa-box fa-4x text-white opacity-50"></i>
                            </div>
                            <div class="card-body p-3">
                                <h6 class="card-title mb-2" style="font-size: 0.95rem; font-weight: 600; color: #333;">
                                    {{ $item['name'] ?? '' }}
                                </h6>
                                <div class="product-footer" style="height: 4px; background: #90EE90; border-radius: 2px;"></div>
                            </div>
                        </div>
                    </div>
                @endforeach
            @else
                {{-- Default: Show All Items --}}
                @foreach($items as $item)
                    <div class="col-lg-3 col-md-4 col-sm-6">
                        <div class="product-card card h-100" 
                             wire:click="addItemFromSearch({{ $item->id }})"
                             style="cursor: pointer; border: none; border-radius: 15px; overflow: hidden; transition: transform 0.2s;"
                             onmouseover="this.style.transform='scale(1.02)'"
                             onmouseout="this.style.transform='scale(1)'">
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
            @endif
        </div>
    </div>

    {{-- Bottom Action Bar --}}
    <div class="pos-bottom-bar bg-white shadow-lg" style="padding: 1rem; border-top: 1px solid #e0e0e0;">
        <div class="d-flex align-items-center justify-content-end gap-3">
            <button type="button" 
                    wire:click="openCustomerModal"
                    class="btn btn-outline-primary"
                    style="border-radius: 25px; padding: 0.75rem 1.5rem;">
                <i class="fas fa-user me-2"></i> العميل
            </button>
            <button type="button" 
                    wire:click="openNotesModal"
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
                    <li><a class="dropdown-item" href="#" wire:click="openTableModal"><i class="fas fa-table me-2"></i> اختيار الطاولة</a></li>
                    <li><a class="dropdown-item" href="#" wire:click="resetForm"><i class="fas fa-redo me-2"></i> إعادة تعيين</a></li>
                    <li><hr class="dropdown-divider"></li>
                    <li><a class="dropdown-item" href="{{ route('pos.index') }}"><i class="fas fa-home me-2"></i> العودة للرئيسية</a></li>
                </ul>
            </div>
        </div>
    </div>

    {{-- Shopping Cart Sidebar (Hidden by default, can be toggled) --}}
    <div class="pos-cart-sidebar" 
         x-data="{ open: false }"
         x-show="open"
         style="position: fixed; top: 0; right: 0; width: 400px; height: 100vh; background: white; box-shadow: -2px 0 10px rgba(0,0,0,0.1); z-index: 1000; overflow-y: auto;">
        <div class="p-3">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h5>سلة التسوق</h5>
                <button type="button" @click="open = false" class="btn btn-sm btn-outline-secondary">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            {{-- Cart Items --}}
            <div class="cart-items">
                @foreach($invoiceItems as $index => $item)
                    <div class="card mb-2">
                        <div class="card-body p-2">
                            <div class="d-flex justify-content-between align-items-start">
                                <div class="flex-grow-1">
                                    <h6 class="mb-1" style="font-size: 0.9rem;">{{ $item['name'] ?? '' }}</h6>
                                    <div class="d-flex align-items-center gap-2">
                                        <input type="number" 
                                               wire:model="invoiceItems.{{ $index }}.quantity"
                                               wire:change="updateQuantity({{ $index }}, $event.target.value)"
                                               class="form-control form-control-sm" 
                                               style="width: 60px;"
                                               min="1">
                                        <span class="text-muted">x {{ $item['price'] ?? 0 }} ريال</span>
                                    </div>
                                </div>
                                <div class="text-end">
                                    <div class="fw-bold text-success">{{ ($item['quantity'] ?? 1) * ($item['price'] ?? 0) }} ريال</div>
                                    <button type="button" 
                                            wire:click="removeRow({{ $index }})"
                                            class="btn btn-sm btn-outline-danger mt-1">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
            {{-- Cart Total --}}
            <div class="cart-total mt-3 p-3 bg-light rounded">
                <div class="d-flex justify-content-between mb-2">
                    <span>المجموع الفرعي:</span>
                    <span>{{ number_format($subtotal, 2) }} ريال</span>
                </div>
                <div class="d-flex justify-content-between mb-2">
                    <span>الخصم:</span>
                    <span class="text-danger">- {{ number_format($discount_value, 2) }} ريال</span>
                </div>
                <hr>
                <div class="d-flex justify-content-between">
                    <strong>الإجمالي:</strong>
                    <strong class="text-primary" style="font-size: 1.2rem;">{{ number_format($total_after_additional, 2) }} ريال</strong>
                </div>
            </div>
        </div>
    </div>

    {{-- Bootstrap Modals --}}
    
    {{-- Payment Modal --}}
    <div class="modal fade" id="paymentModal" tabindex="-1" wire:ignore.self>
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">الدفع</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">الإجمالي</label>
                        <input type="text" class="form-control" value="{{ number_format($total_after_additional, 2) }} ريال" readonly>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">طريقة الدفع</label>
                        <select wire:model="paymentMethod" class="form-select">
                            <option value="cash">نقدي</option>
                            <option value="card">بطاقة</option>
                            <option value="mixed">مختلط</option>
                        </select>
                    </div>
                    @if($paymentMethod === 'cash' || $paymentMethod === 'mixed')
                        <div class="mb-3">
                            <label class="form-label">المبلغ النقدي</label>
                            <input type="number" wire:model="cashAmount" class="form-control" step="0.01" min="0">
                        </div>
                    @endif
                    @if($paymentMethod === 'card' || $paymentMethod === 'mixed')
                        <div class="mb-3">
                            <label class="form-label">مبلغ البطاقة</label>
                            <input type="number" wire:model="cardAmount" class="form-control" step="0.01" min="0">
                        </div>
                    @endif
                    @if($changeAmount > 0)
                        <div class="alert alert-success">
                            المبلغ المتبقي للعميل: {{ number_format($changeAmount, 2) }} ريال
                        </div>
                    @endif
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إلغاء</button>
                    <button type="button" wire:click="saveAndPrint" class="btn btn-primary">
                        <i class="fas fa-print me-2"></i> دفع وطباعة
                    </button>
                    <button type="button" wire:click="saveForm" class="btn btn-success">
                        <i class="fas fa-save me-2"></i> حفظ فقط
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- Customer Modal --}}
    <div class="modal fade" id="customerModal" tabindex="-1" wire:ignore.self>
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">اختيار العميل</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">العميل</label>
                        <select wire:model="acc1_id" wire:change="updateCustomerBalance" class="form-select">
                            @foreach($acc1List as $client)
                                <option value="{{ $client->id }}">{{ $client->aname }}</option>
                            @endforeach
                        </select>
                    </div>
                    @if($showBalance)
                        <div class="alert alert-info">
                            <strong>رصيد العميل:</strong> {{ number_format($currentBalance, 2) }} ريال
                        </div>
                    @endif
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إلغاء</button>
                    <button type="button" class="btn btn-primary" data-bs-dismiss="modal">حفظ</button>
                </div>
            </div>
        </div>
    </div>

    {{-- Notes Modal --}}
    <div class="modal fade" id="notesModal" tabindex="-1" wire:ignore.self>
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">الملاحظات</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">ملاحظات الفاتورة</label>
                        <textarea wire:model="notes" class="form-control" rows="5" placeholder="أدخل ملاحظات الفاتورة..."></textarea>
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
    <div class="modal fade" id="tableModal" tabindex="-1" wire:ignore.self>
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
                                        wire:click="selectTable({{ $i }})"
                                        class="btn btn-outline-primary w-100"
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

</div>

@push('scripts')
<script>
    document.addEventListener('livewire:init', function() {
        Livewire.on('openPaymentModal', () => {
            const modal = new bootstrap.Modal(document.getElementById('paymentModal'));
            modal.show();
        });

        Livewire.on('openCustomerModal', () => {
            const modal = new bootstrap.Modal(document.getElementById('customerModal'));
            modal.show();
        });

        Livewire.on('openNotesModal', () => {
            const modal = new bootstrap.Modal(document.getElementById('notesModal'));
            modal.show();
        });

        Livewire.on('openTableModal', () => {
            const modal = new bootstrap.Modal(document.getElementById('tableModal'));
            modal.show();
        });
    });
</script>
@endpush
