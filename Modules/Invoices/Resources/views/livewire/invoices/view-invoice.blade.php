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

    public function mount($operationId)
    {
        $this->operationId = $operationId;
        $this->loadInvoice();
    }

    public function loadInvoice()
    {
        $this->invoice = OperHead::with(['operationItems.item.units', 'operationItems.unit', 'acc1Head', 'acc2Head', 'employee'])->findOrFail($this->operationId);

        // تحميل تفاصيل الفاتورة
        $this->invoiceItems = $this->invoice->operationItems
            ->map(function ($detail) {
                $item = $detail->item;
                $unit = $detail->unit;

                return [
                    'item_id' => $item->id,
                    'unit_id' => $unit->id,
                    'name' => $item->name,
                    'code' => $item->code,
                    'quantity' => $detail->qty_in > 0 ? $detail->qty_in : $detail->qty_out,
                    'price' => $detail->item_price,
                    'sub_value' => $detail->detail_value ?? ($detail->qty_in > 0 ? $detail->qty_in * $detail->item_price : $detail->qty_out * $detail->item_price),
                    'discount' => $detail->item_discount ?? 0,
                    'unit_name' => $unit->name,
                ];
            })
            ->toArray();

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
                                                <th>الصنف</th>
                                                <th>الباركود</th>
                                                <th>الوحدة</th>
                                                <th class="text-center">الكمية</th>
                                                <th class="text-left">السعر</th>
                                                <th class="text-left">الخصم</th>
                                                <th class="text-left">القيمة</th>
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
                                                            class="text-dark">{{ $barcode->barcode ?? 'غير محدد' }}</code>
                                                    </td>
                                                    <td>
                                                        <span>{{ $item['unit_name'] }}</span>
                                                    </td>
                                                    <td class="text-center">
                                                        <span>{{ number_format($item['quantity']) }}</span>
                                                    </td>
                                                    <td class="text-left">
                                                        <span
                                                            class="text-success"><strong>{{ number_format($item['price'], 2) }}</strong>
                                                            جنيه</span>
                                                    </td>
                                                    <td class="text-left">
                                                        <span
                                                            class="text-danger">{{ number_format($item['discount'], 2) }}
                                                            جنيه</span>
                                                    </td>
                                                    <td class="text-left">
                                                        <strong
                                                            class="text-primary">{{ number_format($item['sub_value'], 2) }}
                                                            جنيه</strong>
                                                    </td>
                                                </tr>
                                            @empty
                                                <tr>
                                                    <td colspan="8" class="text-center py-4">
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
