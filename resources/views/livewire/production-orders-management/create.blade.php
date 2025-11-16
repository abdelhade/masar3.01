<?php

use Livewire\Volt\Component;
use App\Models\ProductionOrder;
use Modules\Accounts\Models\AccHead;
use App\Models\Item;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Validate;

new class extends Component {
    #[Validate('required|integer|min:1')]
    public $order_number;
    
    #[Validate('required|date')]
    public $order_date;
    
    #[Validate('required|exists:acc_head,id')]
    public $customer_id;
    
    #[Validate('nullable|string|max:1000')]
    public $notes = '';
    
    public $items = [];
    public $customers;
    public $availableItems;
    
    public function mount()
    {
        $this->order_date = now()->format('Y-m-d');
        $this->order_number = ProductionOrder::max('order_number') + 1;
        
        $this->customers = AccHead::where('code', 'like', '1103%')
            ->where('is_basic', 0)
            ->orderBy('aname')
            ->get(['id', 'aname', 'code']);
            
        $this->availableItems = Item::orderBy('name')->get(['id', 'name', 'code']);
        
        $this->addItem();
    }
    
    public function addItem()
    {
        $this->items[] = [
            'item_id' => '',
            'quantity' => 1,
            'note' => ''
        ];
    }
    
    public function removeItem($index)
    {
        unset($this->items[$index]);
        $this->items = array_values($this->items);
    }
    
    public function save()
    {
        $this->validate();
        
        // Validate items
        $this->validate([
            'items' => 'required|array|min:1',
            'items.*.item_id' => 'required|exists:items,id',
            'items.*.quantity' => 'required|numeric|min:0.01',
        ], [
            'items.required' => 'ÙŠØ¬Ø¨ Ø¥Ø¶Ø§ÙØ© ØµÙ†Ù ÙˆØ§Ø­Ø¯ Ø¹Ù„Ù‰ Ø§Ù„Ø£Ù‚Ù„',
            'items.*.item_id.required' => 'ÙŠØ¬Ø¨ Ø§Ø®ØªÙŠØ§Ø± Ø§Ù„ØµÙ†Ù',
            'items.*.item_id.exists' => 'Ø§Ù„ØµÙ†Ù Ø§Ù„Ù…Ø®ØªØ§Ø± ØºÙŠØ± Ù…ÙˆØ¬ÙˆØ¯',
            'items.*.quantity.required' => 'ÙŠØ¬Ø¨ Ø¥Ø¯Ø®Ø§Ù„ Ø§Ù„ÙƒÙ…ÙŠØ©',
            'items.*.quantity.numeric' => 'Ø§Ù„ÙƒÙ…ÙŠØ© ÙŠØ¬Ø¨ Ø£Ù† ØªÙƒÙˆÙ† Ø±Ù‚Ù…',
            'items.*.quantity.min' => 'Ø§Ù„ÙƒÙ…ÙŠØ© ÙŠØ¬Ø¨ Ø£Ù† ØªÙƒÙˆÙ† Ø£ÙƒØ¨Ø± Ù…Ù† ØµÙØ±',
        ]);
        
        try {
            DB::beginTransaction();
            
            $order = ProductionOrder::create([
                'order_number' => $this->order_number,
                'order_date' => $this->order_date,
                'customer_id' => $this->customer_id,
                'total_amount' => 0, // Will be calculated
                'status' => 'pending',
                'notes' => $this->notes,
                'created_by' => Auth::id(),
                'updated_by' => Auth::id(),
            ]);
            
            // Attach items
            foreach ($this->items as $item) {
                $order->items()->attach($item['item_id'], [
                    'quantity' => $item['quantity'],
                    'note' => $item['note'] ?? null,
                ]);
            }
            
            DB::commit();
            
            session()->flash('success', 'ØªÙ… Ø¥Ù†Ø´Ø§Ø¡ Ø£Ù…Ø± Ø§Ù„Ø¥Ù†ØªØ§Ø¬ Ø¨Ù†Ø¬Ø§Ø­');
            return redirect()->route('production-orders.index');
            
        } catch (\Exception $e) {
            DB::rollBack();
            session()->flash('error', 'Ø­Ø¯Ø« Ø®Ø·Ø£ Ø£Ø«Ù†Ø§Ø¡ Ø¥Ù†Ø´Ø§Ø¡ Ø£Ù…Ø± Ø§Ù„Ø¥Ù†ØªØ§Ø¬: ' . $e->getMessage());
        }
    }
    
    public function cancel()
    {
        return redirect()->route('production-orders.index');
    }
}; ?>

<div class="container-fluid">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-md-6">
            <h2 class="mb-1">Ø¥Ù†Ø´Ø§Ø¡ Ø£Ù…Ø± Ø¥Ù†ØªØ§Ø¬ Ø¬Ø¯ÙŠØ¯</h2>
            <p class="text-muted">Ø¥Ø¶Ø§ÙØ© Ø£Ù…Ø± Ø¥Ù†ØªØ§Ø¬ Ø¬Ø¯ÙŠØ¯ Ù„Ù„Ù†Ø¸Ø§Ù…</p>
        </div>
        <div class="col-md-6 text-end mt-4">
            <a href="{{ route('production-orders.index') }}" class="btn btn-secondary font-family-cairo fw-bold">
                <i class="fas fa-arrow-left"></i>
                Ø§Ù„Ø¹ÙˆØ¯Ø©
            </a>
        </div>
    </div>

    <form wire:submit="save">
        <!-- Basic Information -->
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0">Ø§Ù„Ù…Ø¹Ù„ÙˆÙ…Ø§Øª Ø§Ù„Ø£Ø³Ø§Ø³ÙŠØ©</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-3">
                        <div class="form-group mb-3">
                            <label for="order_number">Ø±Ù‚Ù… Ø§Ù„Ø£Ù…Ø±</label>
                            <input type="number" id="order_number" class="form-control @error('order_number') is-invalid @enderror" wire:model="order_number">
                            @error('order_number')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group mb-3">
                            <label for="customer_id">Ø§Ù„Ø¹Ù…ÙŠÙ„</label>
                            <select id="customer_id" class="form-control @error('customer_id') is-invalid @enderror" wire:model="customer_id">
                                <option value="">Ø§Ø®ØªØ± Ø§Ù„Ø¹Ù…ÙŠÙ„</option>
                                @foreach($this->customers as $customer)
                                    <option value="{{ $customer->id }}">{{ $customer->aname }} ({{ $customer->code }})</option>
                                @endforeach
                            </select>
                            @error('customer_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    
                    <div class="col-md-3">
                        <div class="form-group mb-3">
                            <label for="order_date">ØªØ§Ø±ÙŠØ® Ø§Ù„Ø£Ù…Ø±</label>
                            <input type="date" id="order_date" class="form-control @error('order_date') is-invalid @enderror" wire:model="order_date">
                            @error('order_date')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    
                    
                    
                    <div class="col-md-12">
                        <div class="form-group mb-3">
                            <label for="notes">Ù…Ù„Ø§Ø­Ø¸Ø§Øª</label>
                            <textarea id="notes" class="form-control @error('notes') is-invalid @enderror" wire:model="notes" rows="3" placeholder="Ø£ÙŠ Ù…Ù„Ø§Ø­Ø¸Ø§Øª Ø¥Ø¶Ø§ÙÙŠØ©..."></textarea>
                            @error('notes')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Items -->
        <div class="card mb-4">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Ø§Ù„Ø£ØµÙ†Ø§Ù Ø§Ù„Ù…Ø·Ù„ÙˆØ¨Ø©</h5>
                <button type="button" wire:click="addItem" class="btn btn-sm btn-outline-primary font-family-cairo fw-bold font-14">
                    <i class="fas fa-plus"></i>
                    Ø¥Ø¶Ø§ÙØ© ØµÙ†Ù
                </button>
            </div>
            <div class="card-body">
                @if(count($this->items) > 0)
                    <div class="space-y-3">
                        @foreach($this->items as $index => $item)
                            <div class="row border rounded p-3 mb-3">
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label>Ø§Ù„ØµÙ†Ù</label>
                                        <select class="form-control @error("items.{$index}.item_id") is-invalid @enderror" wire:model="items.{{ $index }}.item_id">
                                            <option value="">Ø§Ø®ØªØ± Ø§Ù„ØµÙ†Ù</option>
                                            @foreach($this->availableItems as $availableItem)
                                                <option value="{{ $availableItem->id }}">{{ $availableItem->name }} ({{ $availableItem->code }})</option>
                                            @endforeach
                                        </select>
                                        @error("items.{$index}.item_id")
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label>Ø§Ù„ÙƒÙ…ÙŠØ©</label>
                                        <input type="number" class="form-control @error("items.{$index}.quantity") is-invalid @enderror" wire:model="items.{{ $index }}.quantity" step="0.01" min="0.01">
                                        @error("items.{$index}.quantity")
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label>Ù…Ù„Ø§Ø­Ø¸Ø©</label>
                                        <input type="text" class="form-control" wire:model="items.{{ $index }}.note" placeholder="Ù…Ù„Ø§Ø­Ø¸Ø© Ø§Ø®ØªÙŠØ§Ø±ÙŠØ©...">
                                    </div>
                                </div>
                                
                                <div class="col-md-1 d-flex align-items-end mb-4">
                                    @if(count($this->items) > 1)
                                        <button type="button" wire:click="removeItem({{ $index }})" class="btn btn-sm btn-outline-danger">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="text-center py-5">
                        <i class="fas fa-box fa-3x text-muted mb-3"></i>
                        <h5>Ù„Ø§ ØªÙˆØ¬Ø¯ Ø£ØµÙ†Ø§Ù</h5>
                        <p class="text-muted">Ø§Ø¶ØºØ· Ø¹Ù„Ù‰ "Ø¥Ø¶Ø§ÙØ© ØµÙ†Ù" Ù„Ø¨Ø¯Ø¡ Ø¥Ø¶Ø§ÙØ© Ø§Ù„Ø£ØµÙ†Ø§Ù Ø§Ù„Ù…Ø·Ù„ÙˆØ¨Ø©</p>
                    </div>
                @endif
            </div>
        </div>

        <!-- Actions -->
        <div class="row">
            <div class="col-12 d-flex justify-content-center">
                <button type="button" wire:click="cancel" class="btn btn-secondary me-2 font-family-cairo fw-bold">
                    Ø¥Ù„ØºØ§Ø¡
                </button>
                <button type="submit" class="btn btn-primary font-family-cairo fw-bold">
                    <i class="fas fa-check"></i>
                    Ø­ÙØ¸ Ø£Ù…Ø± Ø§Ù„Ø¥Ù†ØªØ§Ø¬
                </button>
            </div>
        </div>
    </form>
</div>  
