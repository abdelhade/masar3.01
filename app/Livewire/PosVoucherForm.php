<?php

use Livewire\Volt\Component;
use App\Models\Note;
use App\Models\Item;
use App\Models\Price;
use Livewire\WithPagination;
use Illuminate\Support\Facades\Log;

new class extends Component {
    use WithPagination;

    public string $searchTerm = '';
    public array $searchResults = [];
    public array $cartItems = [];
    public int $selectedNoteId = 0;
    public float $tax_value = 0;
    public float $total = 0;
    public string $customer_name = '';
    public string $customer_phone = '';

    public function mount()
    {
        $this->loadNotes();
        $this->calculateTotals();
    }

    public function loadNotes()
    {
        $this->notes = Note::all();
        if ($this->notes->count() > 0) {
            $this->selectedNoteId = $this->notes->first()->id;
            $this->loadFilteredItems();
        }
    }

    public function selectNote($noteId)
    {
        $this->selectedNoteId = $noteId;
        $this->loadFilteredItems();
    }

    public function loadFilteredItems()
    {
        if ($this->selectedNoteId) {
            $this->filteredItems = Item::whereHas('notes', function($query) {
                $query->where('note_id', $this->selectedNoteId);
            })->with('prices')->get();
        } else {
            $this->filteredItems = collect();
        }
    }

    public function updatedSearchTerm()
    {
        if (strlen($this->searchTerm) >= 2) {
            $this->searchResults = Item::where('name', 'like', '%' . $this->searchTerm . '%')
                ->orWhere('code', 'like', '%' . $this->searchTerm . '%')
                ->with('prices')
                ->limit(8)
                ->get()
                ->toArray();
        } else {
            $this->searchResults = [];
        }
    }

    public function addToCart($itemId)
    {
        $item = Item::with('prices')->find($itemId);
        
        if (!$item) {
            return;
        }

        $price = $item->prices->first();
        if (!$price) {
            return;
        }

        $itemPrice = $price->pivot->price ?? 0;

        // Check if item already exists in cart
        $existingItemIndex = collect($this->cartItems)->search(function($cartItem) use ($itemId) {
            return $cartItem['item_id'] == $itemId;
        });

        if ($existingItemIndex !== false) {
            // Increment quantity if item exists
            $this->cartItems[$existingItemIndex]['quantity']++;
            $this->cartItems[$existingItemIndex]['total'] = $this->cartItems[$existingItemIndex]['quantity'] * $this->cartItems[$existingItemIndex]['price'];
        } else {
            // Add new item to cart
            $this->cartItems[] = [
                'item_id' => $item->id,
                'name' => $item->name,
                'code' => $item->code,
                'price' => $itemPrice,
                'quantity' => 1,
                'total' => $itemPrice
            ];
        }

        $this->calculateTotals();
        $this->searchResults = []; // Clear search results
        $this->searchTerm = ''; // Clear search term
    }

    public function removeFromCart($index)
    {
        if (isset($this->cartItems[$index])) {
            unset($this->cartItems[$index]);
            $this->cartItems = array_values($this->cartItems); // Reindex array
            $this->calculateTotals();
        }
    }

    public function updateQuantity($index, $quantity)
    {
        if (isset($this->cartItems[$index]) && $quantity > 0) {
            $this->cartItems[$index]['quantity'] = $quantity;
            $this->cartItems[$index]['total'] = $quantity * $this->cartItems[$index]['price'];
            $this->calculateTotals();
        }
    }

    public function calculateTotals()
    {
        $subtotal = collect($this->cartItems)->sum('total');
        $this->tax_value = $subtotal * 0.14; // 14% tax
        $this->total = $subtotal + $this->tax_value;
    }

    public function clearCart()
    {
        $this->cartItems = [];
        $this->calculateTotals();
    }

    public function save()
    {
        try {
            // Validate cart is not empty
            if (empty($this->cartItems)) {
                session()->flash('error', 'يجب إضافة منتجات إلى السلة أولاً');
                return;
            }

            // Store cart data in session for controller
            session([
                'pos_cart_items' => $this->cartItems,
                'pos_tax_value' => $this->tax_value,
                'pos_total' => $this->total,
                'pos_customer_name' => $this->customer_name,
                'pos_customer_phone' => $this->customer_phone
            ]);

            Log::info('POS Voucher Form - Cart data stored in session', [
                'cart_items' => $this->cartItems,
                'total' => $this->total
            ]);

            // Redirect to controller for processing
            return redirect()->route('pos-vouchers.store');

        } catch (\Exception $e) {
            Log::error('POS Voucher Form - Save error: ' . $e->getMessage());
            session()->flash('error', 'حدث خطأ أثناء حفظ الطلب: ' . $e->getMessage());
        }
    }

    public function with(): array
    {
        return [
            'notes' => Note::all(),
            'filteredItems' => $this->selectedNoteId ? 
                Item::whereHas('notes', function($query) {
                    $query->where('note_id', $this->selectedNoteId);
                })->with('prices')->get() : collect()
        ];
    }
}; ?>

<div>
    <div class="container-fluid px-2">
        <div class="row g-2">
            <!-- Left Side - Product Selection -->
            <div class="col-lg-8 col-md-7 col-12">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-header bg-primary text-white border-0 py-2">
                        <h6 class="mb-0 font-family-cairo fw-bold">
                            <i class="fas fa-shopping-cart me-2"></i>
                            اختيار المنتجات
                        </h6>
                    </div>
                    <div class="card-body p-0 d-flex flex-column">
                        <!-- Search Bar -->
                        <div class="p-2 border-bottom bg-light">
                            <div class="input-group input-group-sm">
                                <input type="text" wire:model.live="searchTerm" class="form-control border-0 shadow-none" placeholder="البحث عن منتج...">
                                <button class="btn btn-outline-secondary border-0" type="button">
                                    <i class="fas fa-search"></i>
                                </button>
                            </div>
                            @if(!empty($searchResults))
                                <div class="position-absolute bg-white border rounded p-2 shadow search-results" style="z-index: 1000; width: 100%; max-height: 300px; overflow-y: auto;">
                                    <div class="row g-1">
                                        @foreach($searchResults as $item)
                                            <div class="col-md-6 col-12 mb-1">
                                                <div class="d-flex justify-content-between align-items-center p-2 border rounded cursor-pointer search-item" 
                                                     wire:click="addToCart({{ $item['id'] }})" 
                                                     style="cursor: pointer; font-size: 0.8rem;">
                                                    <div>
                                                        <strong class="font-family-cairo">{{ Str::limit($item['name'], 20) }}</strong>
                                                        <br>
                                                        <small class="text-muted">كود: {{ $item['code'] }}</small>
                                                    </div>
                                                    <div class="text-end">
                                                        <span class="badge bg-success">LE {{ number_format($item['prices'][0]['pivot']['price'] ?? 0, 2) }}</span>
                                                    </div>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            @endif
                        </div>

                        <!-- Categories Tabs -->
                        <div class="p-2 border-bottom">
                            <div class="d-flex flex-wrap gap-1">
                                @foreach($notes as $note)
                                    <button class="btn btn-outline-success btn-sm {{ $selectedNoteId == $note->id ? 'active' : '' }} rounded-pill px-3"
                                            wire:click="selectNote({{ $note->id }})">
                                        {{ Str::limit($note->name, 15) }}
                                    </button>
                                @endforeach
                            </div>
                        </div>

                        <!-- Products Grid -->
                        <div class="p-2 flex-grow-1" style="overflow-y: auto;">
                            @if(!empty($filteredItems))
                                <div class="row g-2">
                                    @foreach($filteredItems as $item)
                                        @php
                                            $inCart = collect($cartItems)->where('item_id', $item->id)->first();
                                            $cartQuantity = $inCart ? $inCart['quantity'] : 0;
                                        @endphp
                                        <div class="col-xl-2 col-lg-3 col-md-4 col-sm-6 col-6">
                                            <div class="card border-0 shadow-sm product-card cursor-pointer" 
                                                 wire:click="addToCart({{ $item->id }})"
                                                 style="cursor: pointer; min-height: 120px; border-radius: 8px;">
                                                @if($cartQuantity > 0)
                                                    <div class="position-absolute top-0 start-0 m-1">
                                                        <span class="badge bg-dark rounded-pill">{{ $cartQuantity }}</span>
                                                    </div>
                                                @endif
                                                <div class="card-body p-2 text-center d-flex flex-column justify-content-between">
                                                    <div>
                                                        <h6 class="card-title font-family-cairo fw-bold mb-1" style="font-size: 0.8rem;">
                                                            {{ Str::limit($item->name, 20) }}
                                                        </h6>
                                                        <small class="text-muted d-block mb-1" style="font-size: 0.7rem;">كود: {{ $item->code }}</small>
                                                    </div>
                                                    <div>
                                                        @if($item->prices->first())
                                                            <span class="badge bg-success fs-7 px-2 py-1">
                                                                LE {{ number_format($item->prices->first()->pivot->price ?? 0, 2) }}
                                                            </span>
                                                        @endif
                                                    </div>
                                                </div>
                                                <div class="card-footer bg-transparent border-0 p-0">
                                                    <div class="bg-success" style="height: 3px; border-radius: 0 0 8px 8px;"></div>
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            @else
                                <div class="text-center text-muted py-4">
                                    <i class="fas fa-box fa-2x mb-2 text-muted"></i>
                                    <p class="font-family-cairo mb-0">اختر تصنيفاً لعرض المنتجات</p>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right Side - Order Summary/Cart -->
            <div class="col-lg-4 col-md-5 col-12">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-header bg-success text-white border-0 py-2">
                        <h6 class="mb-0 font-family-cairo fw-bold">
                            <i class="fas fa-receipt me-2"></i>
                            ملخص الطلب
                        </h6>
                    </div>
                    <div class="card-body p-0 d-flex flex-column">
                        <!-- Cart Items List -->
                        <div class="flex-grow-1 p-2" style="overflow-y: auto; max-height: 300px;">
                            @forelse($cartItems as $index => $item)
                                <div class="d-flex justify-content-between align-items-center mb-2 p-2 border-bottom bg-light rounded">
                                    <div class="flex-grow-1">
                                        <h6 class="mb-1 font-family-cairo fw-bold" style="font-size: 0.85rem;">{{ Str::limit($item['name'], 25) }}</h6>
                                        <div class="d-flex align-items-center gap-1">
                                            <input type="number" 
                                                   wire:change="updateQuantity({{ $index }}, $event.target.value)"
                                                   value="{{ $item['quantity'] }}" 
                                                   min="1" 
                                                   class="form-control form-control-sm" 
                                                   style="width: 50px; font-size: 0.8rem;">
                                            <button wire:click="removeFromCart({{ $index }})" 
                                                    class="btn btn-sm btn-outline-danger">
                                                <i class="fas fa-trash" style="font-size: 0.7rem;"></i>
                                            </button>
                                        </div>
                                    </div>
                                    <div class="text-end">
                                        <span class="fw-bold" style="font-size: 0.85rem;">LE {{ number_format($item['total'], 2) }}</span>
                                    </div>
                                </div>
                            @empty
                                <div class="text-center text-muted py-3">
                                    <i class="fas fa-shopping-cart fa-2x mb-2"></i>
                                    <p class="font-family-cairo mb-0">لا توجد منتجات في السلة</p>
                                </div>
                            @endforelse
                        </div>

                        <!-- Totals -->
                        <div class="p-2 border-top bg-light">
                            <div class="d-flex justify-content-between mb-1">
                                <span class="font-family-cairo" style="font-size: 0.85rem;">الضرائب:</span>
                                <span class="fw-bold" style="font-size: 0.85rem;">LE {{ number_format($tax_value, 2) }}</span>
                            </div>
                            <div class="d-flex justify-content-between mb-2">
                                <span class="font-family-cairo fw-bold" style="font-size: 1rem;">الإجمالي:</span>
                                <span class="fw-bold text-primary" style="font-size: 1rem;">LE {{ number_format($total, 2) }}</span>
                            </div>
                        </div>

                        <!-- Action Buttons -->
                        <div class="p-2 border-top">
                            <div class="row g-1">
                                <div class="col-6">
                                    <button class="btn btn-outline-primary btn-sm w-100 rounded-pill">
                                        <i class="fas fa-utensils me-1"></i>
                                        <span style="font-size: 0.75rem;">تناول الطعام</span>
                                    </button>
                                </div>
                                <div class="col-6">
                                    <button class="btn btn-outline-secondary btn-sm w-100 rounded-pill">
                                        <i class="fas fa-redo me-1"></i>
                                        <span style="font-size: 0.75rem;">دورة</span>
                                    </button>
                                </div>
                                <div class="col-6">
                                    <button class="btn btn-outline-secondary btn-sm w-100 rounded-pill">
                                        <i class="fas fa-sticky-note me-1"></i>
                                        <span style="font-size: 0.75rem;">الملاحظات</span>
                                    </button>
                                </div>
                                <div class="col-6">
                                    <button class="btn btn-outline-info btn-sm w-100 rounded-pill">
                                        <i class="fas fa-table me-1"></i>
                                        <span style="font-size: 0.75rem;">إعداد الطاولة</span>
                                    </button>
                                </div>
                                <div class="col-6">
                                    <button class="btn btn-outline-warning btn-sm w-100 rounded-pill">
                                        <i class="fas fa-tab me-1"></i>
                                        <span style="font-size: 0.75rem;">Set Tab</span>
                                    </button>
                                </div>
                                <div class="col-6">
                                    <button wire:click="save" class="btn btn-success btn-sm w-100 rounded-pill" id="saveButton">
                                        <i class="fas fa-credit-card me-1"></i>
                                        <span style="font-size: 0.75rem;">الدفع</span>
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- Error Messages -->
                        @if (session()->has('error'))
                            <div class="alert alert-danger m-2 py-2">
                                <i class="fas fa-exclamation-triangle me-2"></i>
                                <span style="font-size: 0.8rem;">{{ session('error') }}</span>
                            </div>
                        @endif

                        @if (session()->has('test'))
                            <div class="alert alert-info m-2 py-2">
                                <i class="fas fa-info-circle me-2"></i>
                                <span style="font-size: 0.8rem;">{{ session('test') }}</span>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.cursor-pointer {
    cursor: pointer;
}

.cursor-pointer:hover {
    background-color: #f8f9fa;
}

/* Product cards styling */
.product-card {
    transition: all 0.2s ease;
    border: 1px solid #e9ecef;
    background: white;
}

.product-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0,0,0,0.15);
    border-color: #28a745;
}

.product-card .card-body {
    background: white;
}

.product-card .card-footer {
    background: #28a745;
}

/* Category tabs styling */
.btn-outline-success.active {
    background-color: #28a745;
    border-color: #28a745;
    color: white;
}

.btn-outline-success:hover {
    background-color: #28a745;
    border-color: #28a745;
    color: white;
}

/* Badge styling */
.badge.bg-dark {
    background-color: #343a40 !important;
    color: white;
}

.badge.bg-success {
    background-color: #28a745 !important;
    color: white;
}

/* Card styling */
.card {
    border-radius: 8px;
    overflow: hidden;
}

.card-header {
    background-color: white;
    border-bottom: 1px solid #e9ecef;
}

/* Search results optimization */
.search-results {
    max-height: 300px;
    overflow-y: auto;
}

.search-item:hover {
    background-color: #f8f9fa;
}

/* Font family */
.font-family-cairo {
    font-family: 'Cairo', sans-serif;
}

/* Responsive font sizes */
.fs-7 {
    font-size: 0.75rem !important;
}

/* Mobile optimizations */
@media (max-width: 768px) {
    .container-fluid {
        padding-left: 5px;
        padding-right: 5px;
    }
    
    .card-body {
        padding: 0.5rem;
    }
    
    .btn {
        padding: 0.25rem 0.5rem;
        font-size: 0.75rem;
    }
    
    .form-control {
        padding: 0.25rem 0.5rem;
        font-size: 0.8rem;
    }
    
    .card-title {
        font-size: 0.75rem !important;
    }
    
    .badge {
        font-size: 0.7rem;
    }
}

/* Small screen optimizations */
@media (max-width: 576px) {
    .col-6 {
        padding-left: 2px;
        padding-right: 2px;
    }
    
    .row.g-2 {
        --bs-gutter-x: 0.25rem;
        --bs-gutter-y: 0.25rem;
    }
    
    .product-card {
        min-height: 100px !important;
    }
    
    .card-body {
        padding: 0.25rem !important;
    }
}

/* Tablet optimizations */
@media (min-width: 768px) and (max-width: 1024px) {
    .product-card {
        min-height: 110px !important;
    }
    
    .card-title {
        font-size: 0.8rem !important;
    }
}

/* Large screen optimizations */
@media (min-width: 1200px) {
    .product-card {
        min-height: 130px !important;
    }
    
    .card-title {
        font-size: 0.85rem !important;
    }
}
</style> 