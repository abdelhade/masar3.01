<?php

use Livewire\Volt\Component;
use App\Models\{OperHead, OperationItems, AccHead, Item, Barcode, Price, ProType};
use Illuminate\Support\Collection;

new class extends Component {
    public $operationId;
    public $invoice;
    public $invoiceItems = [];
    public $acc1Role;
    public $acc2Role;
    public $titles = [
        10 => 'فاتوره مبيعات',
        11 => 'فاتورة مشتريات',
        12 => 'مردود مبيعات',
        13 => 'مردود مشتريات',
        14 => 'امر بيع',
        15 => 'امر شراء',
        16 => 'عرض سعر لعميل',
        17 => 'عرض سعر من مورد',
        18 => 'فاتورة توالف',
        19 => 'امر صرف',
        20 => 'امر اضافة',
        21 => 'تحويل من مخزن لمخزن',
        22 => 'امر حجز',
    ];

    public function mount($operationId)
    {
        $this->operationId = $operationId;
        $this->loadInvoice();
    }

    public function loadInvoice()
    {
        $this->invoice = OperHead::with(['operationItems.item.units', 'operationItems.unit', 'acc1Head', 'acc2Head', 'employee'])
            ->where('id', $this->operationId)
            ->firstOrFail();

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
                    'sub_value' => $detail->qty_in > 0 ? $detail->qty_in * $detail->item_price : $detail->qty_out * $detail->item_price,
                    'discount' => $detail->discount ?? 0,
                    'unit_name' => $unit->name,
                    'available_quantity' => $this->getItemAvailableQuantity($item->id, $this->invoice->acc2),
                ];
            })
            ->toArray();

        // تحديد أدوار الحسابات
        $this->setAccountRoles();
    }

    public function setAccountRoles()
    {
        $type = $this->invoice->pro_tybe;

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

    public function getItemAvailableQuantity($itemId, $storeId)
    {
        return OperationItems::where('item_id', $itemId)->where('detail_store', $storeId)->selectRaw('SUM(qty_in - qty_out) as total')->value('total') ?? 0;
    }

    public function getSubtotal()
    {
        return collect($this->invoiceItems)->sum('sub_value');
    }

    public function getDiscountValue()
    {
        return $this->invoice->discount_value ?? 0;
    }

    public function getAdditionalValue()
    {
        return $this->invoice->additional_value ?? 0;
    }

    public function getTotalAfterAdditional()
    {
        $subtotal = $this->getSubtotal();
        $discount = $this->getDiscountValue();
        $additional = $this->getAdditionalValue();

        return round($subtotal - $discount + $additional, 2);
    }

    public function getDiscountPercentage()
    {
        $subtotal = $this->getSubtotal();
        if ($subtotal > 0) {
            return round(($this->getDiscountValue() * 100) / $subtotal, 2);
        }
        return 0;
    }

    public function getAdditionalPercentage()
    {
        $subtotal = $this->getSubtotal();
        $discount = $this->getDiscountValue();
        $afterDiscount = $subtotal - $discount;

        if ($afterDiscount > 0) {
            return round(($this->getAdditionalValue() * 100) / $afterDiscount, 2);
        }
        return 0;
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

<div class="invoice-view-container">
    <div class="content-wrapper">
        <section class="content">
            <div class="container-fluid">
                <!-- رأس الفاتورة المحسن -->
                <div class="row mb-4">
                    <div class="col-12">
                        <div class="card shadow-sm border-0">
                            <div class="card-header bg-gradient-primary text-white border-0">
                                <div class="row align-items-center">
                                    <div class="col-md-6">
                                        <div class="d-flex align-items-center">
                                            <div class="invoice-icon me-3">
                                                <i class="fas fa-file-invoice fa-2x"></i>
                                            </div>
                                            <div>
                                                <h3 class="card-title mb-0 fw-bold">
                                                    {{ ProType::find($invoice->pro_type)->ptext ?? 'فاتورة' }}
                                                </h3>
                                                <small class="opacity-75">رقم الفاتورة: {{ $invoice->pro_id }}</small>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6 text-left d-flex justify-content-end align-items-center gap-2 ">
                                            <button type="button" class="btn btn-light btn-lg"
                                                wire:click="printInvoice">
                                                <i class="fas fa-print me-2"></i> طباعة
                                            </button>
                                            <button type="button" class="btn btn-warning btn-lg"
                                                wire:click="editInvoice">
                                                <i class="fas fa-edit me-2"></i> تعديل
                                            </button>
                                            <button type="button" class="btn btn-outline-secondary text-white btn-lg"
                                                wire:click="backToList">
                                                <i class="fas fa-arrow-right me-2"></i> عودة
                                            </button>
                                    </div>
                                </div>
                            </div>
                            <div class="card-body p-4">
                                <div class="row">
                                    <!-- معلومات الفاتورة الأساسية -->
                                    <div class="col-md-6">
                                        <div class="info-section">
                                            <h5 class="section-title mb-3">
                                                <i class="fas fa-info-circle text-primary me-2"></i>
                                                معلومات الفاتورة
                                            </h5>
                                            <div class="info-grid">
                                                <div class="info-item">
                                                    <span class="info-label">رقم الفاتورة:</span>
                                                    <span class="info-value">{{ $invoice->pro_id }}</span>
                                                </div>
                                                <div class="info-item">
                                                    <span class="info-label">تاريخ الفاتورة:</span>
                                                    <span
                                                        class="info-value">{{ \Carbon\Carbon::parse($invoice->pro_date)->format('Y-m-d') }}</span>
                                                </div>
                                                <div class="info-item">
                                                    <span class="info-label">تاريخ الاستحقاق:</span>
                                                    <span
                                                        class="info-value">{{ $invoice->accural_date ? \Carbon\Carbon::parse($invoice->accural_date)->format('Y-m-d') : 'غير محدد' }}</span>
                                                </div>
                                                <div class="info-item">
                                                    <span class="info-label">الموظف:</span>
                                                    <span
                                                        class="info-value">{{ $invoice->employee->aname ?? 'غير محدد' }}</span>
                                                </div>
                                                <div class="info-item">
                                                    <span class="info-label">فئه السعر:</span>
                                                    <span
                                                        class="info-value">{{ Price::find($invoice->price_list)->name ?? 'غير محدد' }}</span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- معلومات الحسابات -->
                                    <div class="col-md-6">
                                        <div class="info-section">
                                            <h5 class="section-title mb-3">
                                                <i class="fas fa-university text-success me-2"></i>
                                                معلومات الحسابات
                                            </h5>
                                            <div class="info-grid">
                                                <div class="info-item">
                                                    <span class="info-label">{{ $acc1Role }}:</span>
                                                    <span
                                                        class="info-value">{{ $invoice->acc1Head->aname ?? 'غير محدد' }}</span>
                                                </div>
                                                <div class="info-item">
                                                    <span class="info-label">{{ $acc2Role }}:</span>
                                                    <span
                                                        class="info-value">{{ $invoice->acc2Head->aname ?? 'غير محدد' }}</span>
                                                </div>
                                                <div class="info-item">
                                                    <span class="info-label">الصندوق:</span>
                                                    <span
                                                        class="info-value">{{ $invoice->cash_box_id ? AccHead::find($invoice->cash_box_id)->aname ?? 'غير محدد' : 'غير محدد' }}</span>
                                                </div>
                                                <div class="info-item">
                                                    <span class="info-label">المدفوع من العميل:</span>
                                                    <span
                                                        class="info-value text-success fw-bold">{{ number_format($invoice->received_from_client ?? 0, 2) }}
                                                        ريال</span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- جدول الأصناف المحسن -->
                <div class="row mb-4">
                    <div class="col-12">
                        <div class="card shadow-sm border-0">
                            <div class="card-header bg-light border-0">
                                <h4 class="card-title mb-0">
                                    <i class="fas fa-list text-primary me-2"></i>
                                    تفاصيل الفاتورة
                                    <span class="badge bg-primary ms-2">{{ count($invoiceItems) }} صنف</span>
                                </h4>
                            </div>
                            <div class="card-body p-0">
                                <div class="table-responsive">
                                    <table class="table table-hover mb-0">
                                        <thead class="table-dark">
                                            <tr>
                                                <th class="text-center" style="width: 50px;">#</th>
                                                <th>الصنف</th>
                                                <th>الباركود</th>
                                                <th>الوحدة</th>
                                                <th class="text-center">الكمية</th>
                                                <th class="text-left">السعر</th>
                                                <th class="text-left">الخصم</th>
                                                <th class="text-left">القيمة الفرعية</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @forelse($invoiceItems as $index => $item)
                                                <tr class="item-row">
                                                    <td class="text-center fw-bold">{{ $index + 1 }}</td>
                                                    <td>
                                                        <div class="item-info">
                                                            <strong class="item-name">{{ $item['name'] }}</strong>
                                                            @if ($item['code'])
                                                                <div class="item-code">كود: {{ $item['code'] }}</div>
                                                            @endif
                                                        </div>
                                                    </td>
                                                    <td>
                                                        @php
                                                            $barcode = Barcode::where('item_id', $item['item_id'])
                                                                ->where('unit_id', $item['unit_id'])
                                                                ->first();
                                                        @endphp
                                                        <span
                                                            class="barcode-text">{{ $barcode->barcode ?? 'غير محدد' }}</span>
                                                    </td>
                                                    <td>
                                                        <span class="unit-badge">{{ $item['unit_name'] }}</span>
                                                    </td>
                                                    <td class="text-center">
                                                        <span
                                                            class="quantity-display">{{ number_format($item['quantity'], 3) }}</span>
                                                    </td>
                                                    <td class="text-left">
                                                        <span
                                                            class="price-display">{{ number_format($item['price'], 2) }}
                                                            ريال</span>
                                                    </td>
                                                    <td class="text-left">
                                                        <span
                                                            class="discount-display">{{ number_format($item['discount'], 2) }}
                                                            ريال</span>
                                                    </td>
                                                    <td class="text-left">
                                                        <span
                                                            class="subtotal-display fw-bold">{{ number_format($item['sub_value'], 2) }}
                                                            ريال</span>
                                                    </td>
                                                </tr>
                                            @empty
                                                <tr>
                                                    <td colspan="9" class="text-center py-4">
                                                        <div class="empty-state">
                                                            <i class="fas fa-box-open fa-3x text-muted mb-3"></i>
                                                            <p class="text-muted">لا توجد أصناف في هذه الفاتورة</p>
                                                        </div>
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

                <!-- الإجماليات المحسنة -->
                <div class="row">
                    <div class="col-md-6 offset-md-6">
                        <div class="card shadow-sm border-0 totals-card">
                            <div class="card-header bg-gradient-success text-white border-0">
                                <h5 class="card-title mb-0">
                                    <i class="fas fa-calculator me-2"></i> الإجماليات
                                </h5>
                            </div>
                            <div class="card-body p-4">
                                <div class="totals-list">
                                    <div class="total-item">
                                        <span class="total-label">المجموع الفرعي:</span>
                                        <span class="total-value">{{ number_format($this->getSubtotal(), 2) }}
                                            ريال</span>
                                    </div>
                                    <div class="total-item discount-item">
                                        <span class="total-label">الخصم
                                            ({{ $this->getDiscountPercentage() }}%):</span>
                                        <span class="total-value text-danger">-
                                            {{ number_format($this->getDiscountValue(), 2) }} ريال</span>
                                    </div>
                                    <div class="total-item additional-item">
                                        <span class="total-label">الإضافي
                                            ({{ $this->getAdditionalPercentage() }}%):</span>
                                        <span class="total-value text-success">+
                                            {{ number_format($this->getAdditionalValue(), 2) }} ريال</span>
                                    </div>
                                    <div class="total-item final-total">
                                        <span class="total-label">الإجمالي النهائي:</span>
                                        <span
                                            class="total-value final-value">{{ number_format($this->getTotalAfterAdditional(), 2) }}
                                            ريال</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- الملاحظات المحسنة -->
                @if ($invoice->notes)
                    <div class="row mt-4">
                        <div class="col-12">
                            <div class="card shadow-sm border-0">
                                <div class="card-header bg-light border-0">
                                    <h5 class="card-title mb-0">
                                        <i class="fas fa-sticky-note text-warning me-2"></i> الملاحظات
                                    </h5>
                                </div>
                                <div class="card-body">
                                    <div class="notes-content">
                                        <p class="mb-0">{{ $invoice->notes }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                @endif
            </div>
        </section>
    </div>
    {{-- style --}}
    <style>
        /* Modern Invoice View Styles */
        .invoice-view-container {
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            min-height: 100vh;
            padding: 20px 0;
        }
    
        .card {
            border-radius: 15px;
            overflow: hidden;
            transition: all 0.3s ease;
        }
    
        .card:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1) !important;
        }
    
        .bg-gradient-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }
    
        .bg-gradient-success {
            background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);
        }
    
        /* Header Styles */
        .invoice-icon {
            background: rgba(255, 255, 255, 0.2);
            border-radius: 50%;
            width: 60px;
            height: 60px;
            display: flex;
            align-items: center;
            justify-content: center;
        }
    
        .btn-group .btn {
            border-radius: 8px;
            margin: 0 5px;
            transition: all 0.3s ease;
        }
    
        .btn-group .btn:hover {
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
        }
    
        /* Info Sections */
        .info-section {
            padding: 20px;
            background: #f8f9fa;
            border-radius: 12px;
            border-left: 4px solid #007bff;
        }
    
        .section-title {
            color: #495057;
            font-weight: 600;
            border-bottom: 2px solid #e9ecef;
            padding-bottom: 10px;
        }
    
        .info-grid {
            display: grid;
            gap: 15px;
        }
    
        .info-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 10px 0;
            border-bottom: 1px solid #e9ecef;
        }
    
        .info-item:last-child {
            border-bottom: none;
        }
    
        .info-label {
            font-weight: 600;
            color: #6c757d;
            min-width: 120px;
        }
    
        .info-value {
            font-weight: 500;
            color: #212529;
        }
    
        /* Table Styles */
        .table {
            border-collapse: separate;
            border-spacing: 0;
        }
    
        .table thead th {
            background: linear-gradient(135deg, #2c3e50 0%, #34495e 100%);
            color: white;
            font-weight: 600;
            border: none;
            padding: 15px 10px;
        }
    
        .table tbody tr {
            transition: all 0.3s ease;
        }
    
        .table tbody tr:hover {
            background-color: #f8f9fa;
            transform: scale(1.01);
        }
    
        .item-row {
            border-bottom: 1px solid #e9ecef;
        }
    
        .item-info {
            display: flex;
            flex-direction: column;
        }
    
        .item-name {
            color: #2c3e50;
            font-size: 1rem;
        }
    
        .item-code {
            color: #6c757d;
            font-size: 0.85rem;
            margin-top: 2px;
        }
    
        .barcode-text {
            font-family: 'Courier New', monospace;
            background: #f8f9fa;
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 0.9rem;
        }
    
        .unit-badge {
            background: #e3f2fd;
            color: #1976d2;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 0.85rem;
            font-weight: 500;
        }
    
        .quantity-display {
            font-weight: 600;
            color: #2c3e50;
            background: #f8f9fa;
            padding: 4px 8px;
            border-radius: 4px;
        }
    
        .price-display {
            color: #28a745;
            font-weight: 600;
        }
    
        .discount-display {
            color: #dc3545;
            font-weight: 500;
        }
    
        .subtotal-display {
            color: #007bff;
        }
    
        .stock-badge {
            font-size: 0.85rem;
            padding: 6px 12px;
        }
    
        /* Empty State */
        .empty-state {
            text-align: center;
            padding: 40px 20px;
        }
    
        /* Totals Card */
        .totals-card {
            background: linear-gradient(135deg, #ffffff 0%, #f8f9fa 100%);
        }
    
        .totals-list {
            display: flex;
            flex-direction: column;
            gap: 15px;
        }
    
        .total-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 12px 0;
            border-bottom: 1px solid #e9ecef;
        }
    
        .total-item:last-child {
            border-bottom: none;
        }
    
        .total-label {
            font-weight: 600;
            color: #495057;
        }
    
        .total-value {
            font-weight: 700;
            font-size: 1.1rem;
        }
    
        .final-total {
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            padding: 15px;
            border-radius: 8px;
            margin-top: 10px;
        }
    
        .final-value {
            font-size: 1.3rem;
            color: #28a745;
        }
    
        /* Notes Section */
        .notes-content {
            background: #fff3cd;
            border: 1px solid #ffeaa7;
            border-radius: 8px;
            padding: 15px;
            color: #856404;
        }
    
        /* Responsive Design */
        @media (max-width: 768px) {
            .invoice-view-container {
                padding: 10px;
            }
    
            .btn-group {
                flex-direction: column;
                gap: 10px;
            }
    
            .btn-group .btn {
                margin: 0;
            }
    
            .info-item {
                flex-direction: column;
                align-items: flex-start;
                gap: 5px;
            }
    
            .total-item {
                flex-direction: column;
                align-items: flex-start;
                gap: 5px;
            }
        }
    
        /* Animation */
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
    
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
    
        .card {
            animation: fadeInUp 0.6s ease-out;
        }
    
        /* Custom Scrollbar */
        .table-responsive::-webkit-scrollbar {
            height: 8px;
        }
    
        .table-responsive::-webkit-scrollbar-track {
            background: #f1f1f1;
            border-radius: 4px;
        }
    
        .table-responsive::-webkit-scrollbar-thumb {
            background: #c1c1c1;
            border-radius: 4px;
        }
    
        .table-responsive::-webkit-scrollbar-thumb:hover {
            background: #a8a8a8;
        }
    </style>
</div>



@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        document.addEventListener('livewire:init', () => {
            Livewire.on('open-print-window', (event) => {
                const url = event.url;
                console.log('JavaScript received print URL: ' + url);
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

        document.addEventListener('DOMContentLoaded', function() {
            document.body.classList.add('enlarge-menu');

            // Add smooth scrolling
            document.querySelectorAll('a[href^="#"]').forEach(anchor => {
                anchor.addEventListener('click', function(e) {
                    e.preventDefault();
                    document.querySelector(this.getAttribute('href')).scrollIntoView({
                        behavior: 'smooth'
                    });
                });
            });
        });
    </script>
@endpush
