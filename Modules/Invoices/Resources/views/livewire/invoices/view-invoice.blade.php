<?php

use Livewire\Volt\Component;
use Modules\Accounts\Models\AccHead;
use App\Models\{OperHead, OperationItems, Item, Barcode, Price, ProType};

new class extends Component {
    public $operationId;
    public $invoice;
    public $invoiceItems = [];
    public $acc1Role;
    public $acc2Role;
    public $visibleColumns = [];
    public $availableColumns = [];

    public function mount($operationId)
    {
        $this->operationId = $operationId;
        $this->loadInvoice();
    }

    public function loadInvoice()
    {
        $this->invoice = OperHead::with(['operationItems.item.units', 'operationItems.unit', 'operationItems.fat_unit', 'acc1Head', 'acc2Head', 'employee', 'invoiceTemplate'])->findOrFail($this->operationId);

        // تحميل تفاصيل الفاتورة
        $this->invoiceItems = $this->invoice->operationItems
            ->map(function ($detail) {
                $item = $detail->item;
                $unit = $detail->fat_unit ?: $detail->unit;

                // 1. Get Base Quantity and Price (Stored in DB)
                $baseQty = $detail->qty_in > 0 ? $detail->qty_in : $detail->qty_out;
                $qty = $baseQty;
                $price = $detail->item_price; // Base price by default

                $unitName = $unit?->name ?? '---';
                $unitId = $detail->fat_unit_id ?: $detail->unit_id;

                // 2. Logic to Determine Display Quantity/Price
                // Priority 1: Check if 'fat_quantity' is saved (Display Qty)
                if (isset($detail->fat_quantity) && $detail->fat_quantity > 0) {
                     $qty = $detail->fat_quantity; // Use saved display qty
                     // fat_price is NOW the display price (already multiplied by uVal during save)
                     $price = $detail->fat_price ?? ($detail->item_price * ($baseQty > 0 ? ($baseQty / $qty) : 1));
                } 
                // Priority 2: Check if 'unit_value' column exists and is valid
                elseif (isset($detail->unit_value) && $detail->unit_value > 0 && $detail->unit_value != 1) {
                     $uVal = $detail->unit_value;
                     $qty = $baseQty / $uVal;
                     // fat_price IS display price, or calculate from item_price (base)
                     $price = $detail->fat_price ?? ($detail->item_price * $uVal);
                }
                // Priority 3: Fallback to Relation (if Item/Unit exists)
                elseif ($unit && $item) {
                     $pivotUnit = $item->units->find($unit->id);
                     $uVal = $pivotUnit?->pivot?->u_val ?? 1;
                     if ($uVal > 0) {
                         $qty = $baseQty / $uVal;
                         $price = $detail->fat_price ?? ($detail->item_price * $uVal);
                     }
                }

                
                // --- Dynamic Columns Logic ---
                $extraData = [];
                // Initialize default columns to simplify checks in array
                $defaults = ['length' => null, 'width' => null, 'height' => null, 'density' => null];

                if ($this->invoice->template_id && $this->invoice->invoiceTemplate) {
                     $cols = $this->invoice->invoiceTemplate->visible_columns ?? [];
                     // Check if specific spatial columns are enabled in the template
                     if (in_array('length', $cols)) $extraData['length'] = $detail->length;
                     if (in_array('width', $cols)) $extraData['width'] = $detail->width;
                     if (in_array('height', $cols)) $extraData['height'] = $detail->height;
                     if (in_array('density', $cols)) $extraData['density'] = $detail->density;
                }

                return array_merge([
                    'item_id' => $item?->id,
                    'unit_id' => $unitId,
                    'name' => $item?->name ?? 'Unknown Item',
                    'code' => $item?->code,
                    'quantity' => $qty,
                    'price' => $price,
                    'sub_value' => $detail->detail_value ?? ($qty * $price),
                    'discount' => $detail->item_discount ?? 0,
                    'unit_name' => $unitName,
                    'unit' => $unitName, // Map for dynamic column 'unit'
                ], $extraData);
            })
            ->toArray();
        
        // Determine Visible Columns for the View
        if ($this->invoice->template_id && $this->invoice->invoiceTemplate) {
            $this->visibleColumns = $this->invoice->invoiceTemplate->visible_columns ?? [];
        } else {
             // Default columns if no template is saved
            $this->visibleColumns = ['item_name', 'unit', 'quantity', 'price', 'discount', 'sub_value'];
        }
        
        $this->availableColumns = \Modules\Invoices\Models\InvoiceTemplate::availableColumns();

        // تحديد أدوار الحسابات
        $this->setAccountRoles();
    }

    public function setAccountRoles()
    {
        $type = $this->invoice->pro_type;

        $map = [
            10 => ['acc1_role' => 'مدين', 'acc2_role' => 'دائن'], // فاتورة مبيعات
            11 => ['acc1_role' => 'دائن', 'acc2_role' => 'مدين'], // فاتورة مشتريات
            12 => ['acc1_role' => 'دائن', 'acc2_role' => 'مدين'], // مردود مبيعات
            13 => ['acc1_role' => 'مدين', 'acc2_role' => 'دائن'], // مردود مشتريات
            14 => ['acc1_role' => 'مدين', 'acc2_role' => 'دائن'], // أمر بيع
            15 => ['acc1_role' => 'دائن', 'acc2_role' => 'مدين'], // أمر شراء
            16 => ['acc1_role' => 'مدين', 'acc2_role' => 'دائن'],
            17 => ['acc1_role' => 'دائن', 'acc2_role' => 'مدين'],
            18 => ['acc1_role' => 'مدين', 'acc2_role' => 'دائن'],
            19 => ['acc1_role' => 'مدين', 'acc2_role' => 'دائن'],
            20 => ['acc1_role' => 'دائن', 'acc2_role' => 'مدين'],
            21 => ['acc1_role' => 'مدين', 'acc2_role' => 'دائن'],
            22 => ['acc1_role' => 'مدين', 'acc2_role' => 'دائن'],
        ];

        $this->acc1Role = $map[$type]['acc1_role'] ?? 'مدين';
        $this->acc2Role = $map[$type]['acc2_role'] ?? 'دائن';
    }

    public function getSubtotal()
    {
        return $this->invoice->fat_total ?? 0;
    }

    public function getDiscountValue()
    {
        return $this->invoice->fat_disc ?? 0;
    }

    public function getAdditionalValue()
    {
        return $this->invoice->fat_plus ?? 0;
    }

    public function getTotalAfterAdditional()
    {
        return $this->invoice->fat_net ?? 0;
    }

    public function printInvoice()
    {
        $printUrl = route('invoice.print', ['operation_id' => $this->operationId]);
        $this->dispatch('open-print-window', url: $printUrl);
    }

    public function editInvoice()
    {
        return redirect()->route('invoices.edit', ['invoice' => $this->operationId]);
    }

    public function backToList()
    {
        return redirect()->route('invoices.index');
    }
}; ?>

<div>
    <div class="content-wrapper">
        <section class="content-header">
            <div class="container-fluid">
                <div class="row mb-2">
                    <div class="col-sm-6">
                        <h1 class="m-0">
                            <i class="fas fa-file-invoice me-2"></i>
                            {{ $this->invoice->pro_type ? ProType::find($this->invoice->pro_type)->ptext ?? 'فاتورة' : 'فاتورة' }}
                        </h1>
                    </div>
                    <div class="col-sm-6">
                        <div class="float-left">
                            <button type="button" class="btn btn-info" wire:click="printInvoice">
                                <i class="fas fa-print"></i> طباعة
                            </button>
                            <button type="button" class="btn btn-warning" wire:click="editInvoice">
                                <i class="fas fa-edit"></i> تعديل
                            </button>
                            <button type="button" class="btn btn-secondary" wire:click="backToList">
                                <i class="fas fa-arrow-right"></i> عودة
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </section>
        <section class="content">
            <div class="container-fluid">
                <!-- بطاقة معلومات الفاتورة -->
                <div class="row">
                    <div class="col-md-6">
                        <div class="card card-primary card-outline">
                            <div class="card-header">
                                <h3 class="card-title">
                                    <i class="fas fa-info-circle"></i> معلومات الفاتورة
                                </h3>
                            </div>
                            <div class="card-body">
                                <dl class="row mb-0">
                                    <dt class="col-sm-5">رقم الفاتورة:</dt>
                                    <dd class="col-sm-7">
                                        <span
                                            class="badge badge-primary">{{ $this->invoice->pro_id ?? 'غير محدد' }}</span>
                                    </dd>

                                    <dt class="col-sm-5">تاريخ الفاتورة:</dt>
                                    <dd class="col-sm-7">
                                        {{ $this->invoice->pro_date ? \Carbon\Carbon::parse($this->invoice->pro_date)->format('Y-m-d') : 'غير محدد' }}
                                    </dd>

                                    <dt class="col-sm-5">تاريخ الاستحقاق:</dt>
                                    <dd class="col-sm-7">
                                        {{ $this->invoice->accural_date ? \Carbon\Carbon::parse($this->invoice->accural_date)->format('Y-m-d') : 'غير محدد' }}
                                    </dd>

                                    <dt class="col-sm-5">الموظف:</dt>
                                    <dd class="col-sm-7">{{ $this->invoice->employee->aname ?? 'غير محدد' }}</dd>

                                    <dt class="col-sm-5">فئة السعر:</dt>
                                    <dd class="col-sm-7">
                                        {{ $this->invoice->price_list ? Price::find($this->invoice->price_list)->name ?? 'غير محدد' : 'غير محدد' }}
                                    </dd>
                                </dl>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="card card-success card-outline">
                            <div class="card-header">
                                <h3 class="card-title">
                                    <i class="fas fa-university"></i> معلومات الحسابات
                                </h3>
                            </div>
                            <div class="card-body">
                                <dl class="row mb-0">
                                    <dt class="col-sm-5">{{ $this->acc1Role ?? 'الحساب الأول' }}:</dt>
                                    <dd class="col-sm-7">
                                        <span>{{ $this->invoice->acc1Head->aname ?? 'غير محدد' }}</span>
                                    </dd>

                                    <dt class="col-sm-5">{{ $this->acc2Role ?? 'الحساب الثاني' }}:</dt>
                                    <dd class="col-sm-7">
                                        <span>{{ $this->invoice->acc2Head->aname ?? 'غير محدد' }}</span>
                                    </dd>

                                    <dt class="col-sm-5">الصندوق:</dt>
                                    <dd class="col-sm-7">
                                        {{ $this->invoice->acc_fund ? AccHead::find($this->invoice->acc_fund)->aname ?? 'غير محدد' : 'غير محدد' }}
                                    </dd>

                                    <dt class="col-sm-5">
                                        @if (in_array($this->invoice->pro_type, [10, 12, 14, 16, 18, 21, 22]))
                                            المدفوع من العميل:
                                        @elseif(in_array($this->invoice->pro_type, [11, 13, 15, 17, 20]))
                                            المدفوع للمورد:
                                        @else
                                            المدفوع:
                                        @endif
                                    </dt>
                                    <dd class="col-sm-7">
                                        <span>{{ number_format($this->invoice->pro_value ?? 0, 2) }}
                                            جنيه</span>
                                    </dd>
                                </dl>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- جدول الأصناف -->
                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header">
                                <h3 class="card-title">
                                    <i class="fas fa-list"></i> تفاصيل الفاتورة
                                    <span class="badge badge-primary">{{ count($this->invoiceItems) }} صنف</span>
                                </h3>
                            </div>
                            <div class="card-body p-0">
                                <div class="table-responsive">
                                    <table class="table table-striped table-hover mb-0">
                                        <thead>
                                            <tr>
                                                <th class="text-center" width="50">#</th>
                                                <th>الصنف</th> <!-- item_name is always shown with extra info -->
                                                <th>الباركود</th>
                                                @foreach($visibleColumns as $col)
                                                    @continue($col === 'item_name') <!-- skip item_name as it is handled separately -->
                                                    <th class="text-center">{{ $availableColumns[$col] ?? $col }}</th>
                                                @endforeach
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @forelse($this->invoiceItems as $index => $item)
                                                <tr>
                                                    <td class="text-center"><strong>{{ $index + 1 }}</strong></td>
                                                    <td>
                                                        <strong>{{ $item['name'] }}</strong>
                                                        @if ($item['code'])
                                                            <br><small class="text-muted">كود:
                                                                {{ $item['code'] }}</small>
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @php
                                                            $barcode = Barcode::where('item_id', $item['item_id'])
                                                                ->where('unit_id', $item['unit_id'])
                                                                ->first();
                                                        @endphp
                                                        <code
                                                            class="text-dark">{{ $barcode?->barcode ?? 'غير محدد' }}</code>
                                                    </td>
                                                    
                                                    @foreach($visibleColumns as $col)
                                                        @continue($col === 'item_name')
                                                        @php
                                                            $val = $item[$col] ?? '';
                                                            if(is_numeric($val) && in_array($col, ['price', 'sub_value', 'discount'])) $val = number_format($val, 2);
                                                            elseif(is_numeric($val)) $val = number_format($val);
                                                        @endphp
                                                        <td class="text-center">
                                                            <span>{{ $val }}</span>
                                                        </td>
                                                    @endforeach

                                                </tr>
                                            @empty
                                                <tr>
                                                    <td colspan="{{ count($visibleColumns) + 3 }}" class="text-center py-4">
                                                        <i class="fas fa-box-open fa-3x text-muted mb-3"></i>
                                                        <p class="text-muted">لا توجد أصناف في هذه الفاتورة</p>
                                                    </td>
                                                </tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- الإجماليات -->
                <div class="row">
                    <div class="col-md-6 offset-md-6">
                        <div class="card card-warning">
                            <div class="card-header">
                                <h3 class="card-title">
                                    <i class="fas fa-calculator"></i> الإجماليات
                                </h3>
                            </div>
                            <div class="card-body">
                                <dl class="row mb-0">
                                    <dt class="col-sm-7">المجموع الفرعي:</dt>
                                    <dd class="col-sm-5 text-left">
                                        <strong>{{ number_format($this->getSubtotal(), 2) }} جنيه</strong>
                                    </dd>

                                    <dt class="col-sm-7">الخصم:</dt>
                                    <dd class="col-sm-5 text-left text-danger">
                                        <strong>- {{ number_format($this->getDiscountValue(), 2) }} جنيه</strong>
                                    </dd>

                                    <dt class="col-sm-7">الإضافي:</dt>
                                    <dd class="col-sm-5 text-left text-success">
                                        <strong>+ {{ number_format($this->getAdditionalValue(), 2) }} جنيه</strong>
                                    </dd>

                                    <dt class="col-sm-12">
                                        <hr class="my-2">
                                    </dt>

                                    <dt class="col-sm-7 h5">الإجمالي النهائي:</dt>
                                    <dd class="col-sm-5 text-left h4 text-primary">
                                        <strong>{{ number_format($this->getTotalAfterAdditional(), 2) }} جنيه</strong>
                                    </dd>
                                </dl>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- الملاحظات -->
                @if ($this->invoice->info)
                    <div class="row">
                        <div class="col-12">
                            <div class="card card-info">
                                <div class="card-header">
                                    <h3 class="card-title">
                                        <i class="fas fa-sticky-note"></i> الملاحظات
                                    </h3>
                                </div>
                                <div class="card-body">
                                    <p class="mb-0">{{ $this->invoice->info }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                @endif
            </div>
        </section>
    </div>

    @push('scripts')
        <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
        <script>
            document.addEventListener('livewire:init', () => {
                Livewire.on('open-print-window', (event) => {
                    const url = event.url;
                    const printWindow = window.open(url, '_blank');
                    if (printWindow) {
                        printWindow.onload = function() {
                            printWindow.print();
                        };
                    } else {
                        alert('يرجى السماح بفتح النوافذ المنبثقة في المتصفح للطباعة.');
                    }
                });
            });
        </script>
    @endpush
</div>
