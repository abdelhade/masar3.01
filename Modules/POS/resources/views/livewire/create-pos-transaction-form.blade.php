<div class="pos-container">
    {{-- رأس النظام --}}
    <div class="pos-header">
        <div class="pos-header-content">
            <div class="pos-logo">
                <i class="fas fa-cash-register"></i>
                <h2>نظام نقاط البيع</h2>
            </div>
            <div class="pos-info">
                <span class="invoice-number">فاتورة رقم: {{ $pro_id }}</span>
                <span class="current-date">{{ now()->format('Y-m-d H:i') }}</span>
                <span class="cashier">الكاشير: {{ auth()->user()->name }}</span>
            </div>
        </div>
    </div>

    {{-- معلومات الفاتورة --}}
    <div class="invoice-info-section">
        <div class="invoice-info-grid">
            <div class="info-group">
                <label>العميل:</label>
                <select wire:model.live="acc1_id" class="pos-select">
                    @foreach ($acc1List as $client)
                        <option value="{{ $client->id }}">{{ $client->aname }}</option>
                    @endforeach
                </select>
            </div>
            
            <div class="info-group">
                <label>المخزن:</label>
                <select wire:model.live="acc2_id" class="pos-select">
                    @foreach ($acc2List as $store)
                        <option value="{{ $store->id }}">{{ $store->aname }}</option>
                    @endforeach
                </select>
            </div>
            
            <div class="info-group">
                <label>الموظف:</label>
                <select wire:model.live="emp_id" class="pos-select">
                    @foreach ($employees as $employee)
                        <option value="{{ $employee->id }}">{{ $employee->aname }}</option>
                    @endforeach
                </select>
            </div>
            
            <div class="info-group">
                <label>طريقة الدفع:</label>
                <select wire:model.live="cash_box_id" class="pos-select">
                    @foreach ($cashAccounts as $account)
                        <option value="{{ $account->id }}">{{ $account->aname }}</option>
                    @endforeach
                </select>
            </div>
            
            @if ($showBalance)
                <div class="info-group balance-info">
                    <label>رصيد العميل:</label>
                    <div class="balance-display {{ $currentBalance >= 0 ? 'positive' : 'negative' }}">
                        {{ number_format($currentBalance, 2) }} ريال
                    </div>
                </div>
                
                <div class="info-group balance-info">
                    <label>الرصيد بعد الفاتورة:</label>
                    <div class="balance-display {{ $balanceAfterInvoice >= 0 ? 'positive' : 'negative' }}">
                        {{ number_format($balanceAfterInvoice, 2) }} ريال
                    </div>
                </div>
            @endif
        </div>
    </div>

    <div class="pos-main-layout">
        {{-- الجانب الأيسر - قائمة الأصناف والبحث --}}
        <div class="pos-left-panel">
            {{-- بحث سريع --}}
            <div class="pos-search-section">
                <div class="search-box">
                    <input type="text" 
                           wire:model.live="searchTerm" 
                           class="pos-search-input"
                           placeholder="ابحث عن صنف..."
                           autocomplete="off">
                    <i class="fas fa-search search-icon"></i>
                </div>
                
                {{-- نتائج البحث --}}
                @if (strlen($searchTerm) > 0 && $searchResults->count())
                    <div class="search-results">
                        @foreach ($searchResults as $item)
                            <div class="search-result-item" wire:click="addItemFromSearch({{ $item->id }})">
                                <div class="item-info">
                                    <span class="item-name">{{ $item->name }}</span>
                                    <span class="item-code">{{ $item->code }}</span>
                                </div>
                                <div class="item-price">
                                    {{ number_format($item->prices->where('id', $selectedPriceType)->first()->price ?? 0) }} ريال
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>

            {{-- بحث بالباركود --}}
            <div class="barcode-section">
                <div class="barcode-input-group">
                    <input type="text" 
                           wire:model.live="barcodeTerm" 
                           wire:keydown.enter="addItemByBarcode"
                           class="barcode-input"
                           placeholder="امسح الباركود أو اكتبه..."
                           autocomplete="off">
                    <button type="button" wire:click="addItemByBarcode" class="barcode-btn">
                        <i class="fas fa-barcode"></i>
                    </button>
                </div>
            </div>

            {{-- التصنيفات والمجموعات --}}
            <div class="categories-section">
                <h4>التصنيفات</h4>
                <div class="categories-grid">
                    <button type="button" 
                            wire:click="clearCategoryFilter"
                            class="category-btn {{ is_null($selectedCategory) ? 'active' : '' }}">
                        <i class="fas fa-th-large"></i>
                        <span>جميع الأصناف</span>
                    </button>
                    @foreach ($categories as $category)
                        <button type="button" 
                                wire:click="selectCategory({{ $category->id }})"
                                class="category-btn {{ $selectedCategory == $category->id ? 'active' : '' }}">
                            <i class="fas fa-tag"></i>
                            <span>{{ $category->name }}</span>
                        </button>
                    @endforeach
                </div>
            </div>

            {{-- الأصناف سريعة الوصول --}}
            <div class="quick-access-section">
                <h4>
                    @if($selectedCategory)
                        أصناف {{ $categories->where('id', $selectedCategory)->first()?->name }}
                    @else
                        أصناف سريعة
                    @endif
                </h4>
                <div class="quick-items-grid">
                    @foreach ($filteredQuickItems as $quickItem)
                        <button type="button" 
                                wire:click="addQuickItem({{ $quickItem['id'] }})"
                                class="quick-item-btn">
                            <span class="quick-item-name">{{ Str::limit($quickItem['name'], 15) }}</span>
                            <span class="quick-item-code">{{ $quickItem['code'] }}</span>
                        </button>
                    @endforeach
                </div>
                
                @if(empty($filteredQuickItems) && $selectedCategory)
                    <div class="no-items-message">
                        <i class="fas fa-info-circle"></i>
                        <p>لا توجد أصناف في هذا التصنيف</p>
                    </div>
                @endif
            </div>

            {{-- معلومات الصنف المختار --}}
            @if ($currentSelectedItem)
                <div class="selected-item-info">
                    <h4>معلومات الصنف</h4>
                    <div class="item-details">
                        <div class="detail-row">
                            <span>الاسم:</span>
                            <span>{{ $selectedItemData['name'] }}</span>
                        </div>
                        <div class="detail-row">
                            <span>المتاح:</span>
                            <span class="stock-qty">{{ $selectedItemData['available_quantity_in_store'] }}</span>
                        </div>
                        <div class="detail-row">
                            <span>السعر:</span>
                            <span class="price">{{ number_format($selectedItemData['price']) }} ريال</span>
                        </div>
                    </div>
                </div>
            @endif
        </div>

        {{-- الجانب الأيمن - سلة التسوق والمعاملة --}}
        <div class="pos-right-panel">
            {{-- سلة التسوق --}}
            <div class="shopping-cart">
                <div class="cart-header">
                    <h3>سلة التسوق</h3>
                    <span class="items-count">{{ count($invoiceItems) }} صنف</span>
                </div>

                <div class="cart-items">
                    @forelse ($invoiceItems as $index => $item)
                        <div class="cart-item" wire:key="item-{{ $index }}">
                            <div class="item-main-info">
                                <div class="item-name-code">
                                    <span class="name">{{ $item['name'] }}</span>
                                </div>
                                <button type="button" 
                                        wire:click="removeRow({{ $index }})"
                                        class="remove-item-btn">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                            
                            <div class="item-controls">
                                {{-- اختيار الوحدة --}}
                                @if(isset($item['available_units']) && count($item['available_units']) > 1)
                                    <div class="unit-selection">
                                        <label>الوحدة:</label>
                                        <select wire:model.live="invoiceItems.{{ $index }}.unit_id" 
                                                class="unit-select">
                                            @foreach($item['available_units'] as $unit)
                                                <option value="{{ $unit->id }}">{{ $unit->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                @elseif(isset($item['available_units']) && count($item['available_units']) == 1)
                                    <div class="unit-display">
                                        <span class="unit-label">الوحدة: {{ $item['available_units'][0]->name }}</span>
                                    </div>
                                @endif
                                
                                <div class="quantity-controls">
                                    <button type="button" 
                                            wire:click="decrementQuantity({{ $index }})"
                                            class="qty-btn minus">
                                        <i class="fas fa-minus"></i>
                                    </button>
                                    <input type="number" 
                                           wire:model.live="invoiceItems.{{ $index }}.quantity"
                                           wire:change="updateQuantity({{ $index }}, $event.target.value)"
                                           class="qty-input"
                                           min="1">
                                    <button type="button" 
                                            wire:click="incrementQuantity({{ $index }})"
                                            class="qty-btn plus">
                                        <i class="fas fa-plus"></i>
                                    </button>
                                </div>
                                
                                <div class="price-info">
                                    <span class="unit-price">{{ number_format($item['price']) }}</span>
                                    <span class="total-price">{{ number_format($item['sub_value']) }} ريال</span>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="empty-cart">
                            <i class="fas fa-shopping-cart"></i>
                            <p>السلة فارغة</p>
                            <small>ابحث عن الأصناف وأضفها للسلة</small>
                        </div>
                    @endforelse
                </div>
            </div>

            {{-- ملخص المعاملة --}}
            <div class="transaction-summary">
                <div class="summary-row subtotal">
                    <span>المجموع الفرعي:</span>
                    <span>{{ number_format($subtotal) }} ريال</span>
                </div>

                @if ($discount_value > 0)
                    <div class="summary-row discount">
                        <span>الخصم:</span>
                        <span>-{{ number_format($discount_value) }} ريال</span>
                    </div>
                @endif

                @if ($additional_value > 0)
                    <div class="summary-row additional">
                        <span>الإضافي:</span>
                        <span>+{{ number_format($additional_value) }} ريال</span>
                    </div>
                @endif

                <div class="summary-row total">
                    <span>الإجمالي:</span>
                    <span>{{ number_format($total_after_additional) }} ريال</span>
                </div>
            </div>

            {{-- طرق الدفع --}}
            <div class="payment-section">
                <h4>طريقة الدفع</h4>
                
                <div class="payment-methods">
                    <button type="button" 
                            wire:click="setPaymentMethod('cash')"
                            class="payment-method-btn {{ $paymentMethod === 'cash' ? 'active' : '' }}">
                        <i class="fas fa-money-bill-wave"></i>
                        نقدي
                    </button>
                    <button type="button" 
                            wire:click="setPaymentMethod('card')"
                            class="payment-method-btn {{ $paymentMethod === 'card' ? 'active' : '' }}">
                        <i class="fas fa-credit-card"></i>
                        بطاقة
                    </button>
                    <button type="button" 
                            wire:click="setPaymentMethod('mixed')"
                            class="payment-method-btn {{ $paymentMethod === 'mixed' ? 'active' : '' }}">
                        <i class="fas fa-coins"></i>
                        مختلط
                    </button>
                </div>

                <div class="payment-inputs">
                    @if ($paymentMethod === 'cash' || $paymentMethod === 'mixed')
                        <div class="payment-input-group">
                            <label>المبلغ النقدي:</label>
                            <input type="number" 
                                   wire:model.live="cashAmount" 
                                   class="payment-input"
                                   step="0.01"
                                   min="0">
                        </div>
                    @endif

                    @if ($paymentMethod === 'card' || $paymentMethod === 'mixed')
                        <div class="payment-input-group">
                            <label>مبلغ البطاقة:</label>
                            <input type="number" 
                                   wire:model.live="cardAmount" 
                                   class="payment-input"
                                   step="0.01"
                                   min="0">
                        </div>
                    @endif

                    @if ($changeAmount > 0)
                        <div class="change-amount">
                            <span>المبلغ المتبقي للعميل:</span>
                            <span class="change-value">{{ number_format($changeAmount) }} ريال</span>
                        </div>
                    @endif
                </div>
            </div>

            {{-- أزرار العمليات --}}
            <div class="pos-actions">
                <button type="button" 
                        wire:click="saveAndPrint"
                        class="pos-btn primary large"
                        {{ empty($invoiceItems) ? 'disabled' : '' }}>
                    <i class="fas fa-print"></i>
                    دفع وطباعة
                </button>

                <div class="pos-actions-row">
                    <button type="button" 
                            wire:click="saveForm"
                            class="pos-btn secondary"
                            {{ empty($invoiceItems) ? 'disabled' : '' }}>
                        <i class="fas fa-save"></i>
                        حفظ فقط
                    </button>

                    <button type="button" 
                            onclick="window.open('{{ route('pos.index') }}', '_blank')"
                            class="pos-btn info">
                        <i class="fas fa-edit"></i>
                        تعديل فاتورة
                    </button>
                </div>

                <button type="button" 
                        wire:click="resetForm"
                        class="pos-btn danger">
                    <i class="fas fa-times"></i>
                    إلغاء
                </button>
            </div>
        </div>
    </div>

    {{-- شاشة العميل (اختيارية) --}}
    @if ($customerDisplay)
        <div class="customer-display">
            <div class="customer-total">
                <span class="total-label">المجموع:</span>
                <span class="total-amount">{{ number_format($total_after_additional) }} ريال</span>
            </div>
        </div>
    @endif
</div>
