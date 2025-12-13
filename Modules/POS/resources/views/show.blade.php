@extends('pos::layouts.master')

@section('content')
<div class="container py-4">
    <div class="card shadow-sm mb-4 no-print">
        <div class="card-body d-flex justify-content-between align-items-start gap-3 flex-wrap">
            <div class="transaction-info">
                <h1>عرض معاملة POS</h1>
                <div class="transaction-meta">
                    <span class="invoice-number">فاتورة رقم: {{ $transaction->pro_id }}</span>
                    <span class="transaction-date">{{ \Carbon\Carbon::parse($transaction->pro_date)->format('Y-m-d H:i') }}</span>
                    <span class="transaction-status">
                        <i class="fas fa-check-circle text-success"></i>
                        مكتملة
                    </span>
                </div>
            </div>
            
            <div class="d-flex gap-2">
                <a href="{{ route('pos.print', $transaction->id) }}" 
                   class="btn btn-success" 
                   target="_blank">
                    <i class="fas fa-print"></i>
                    طباعة
                </a>
                
                <button onclick="window.print()" class="btn btn-info">
                    <i class="fas fa-print"></i>
                    طباعة الصفحة
                </button>
                
                <a href="{{ route('pos.index') }}" 
                   class="btn btn-secondary">
                    <i class="fas fa-arrow-right"></i>
                    عودة
                </a>
            </div>
        </div>
    </div>

    <div class="transaction-details">
        <div class="row g-3">
            <!-- معلومات أساسية -->
            <div class="col-lg-4">
                <div class="card h-100 shadow-sm">
                    <div class="card-header bg-dark text-white">
                        <h6 class="mb-0"><i class="fas fa-info-circle me-1"></i> معلومات أساسية</h6>
                    </div>
                    <div class="card-body">
                    <div class="detail-row">
                        <span class="label">رقم الفاتورة:</span>
                        <span class="value">{{ $transaction->pro_id }}</span>
                    </div>
                    <div class="detail-row">
                        <span class="label">تاريخ المعاملة:</span>
                        <span class="value">{{ \Carbon\Carbon::parse($transaction->pro_date)->format('Y-m-d') }}</span>
                    </div>
                    <div class="detail-row">
                        <span class="label">وقت المعاملة:</span>
                        <span class="value">{{ $transaction->created_at->format('H:i:s') }}</span>
                    </div>
                    <div class="detail-row">
                        <span class="label">الكاشير:</span>
                        <span class="value">{{ $transaction->employee->aname ?? 'غير محدد' }}</span>
                    </div>
                    <div class="detail-row">
                        <span class="label">المخزن:</span>
                        <span class="value">{{ $transaction->acc2Head->aname ?? 'غير محدد' }}</span>
                    </div>
                    </div>
                </div>
            </div>

            <!-- معلومات العميل -->
            <div class="col-lg-4">
                <div class="card h-100 shadow-sm">
                    <div class="card-header bg-dark text-white">
                        <h6 class="mb-0"><i class="fas fa-user me-1"></i> معلومات العميل</h6>
                    </div>
                    <div class="card-body">
                    <div class="detail-row">
                        <span class="label">اسم العميل:</span>
                        <span class="value">{{ $transaction->acc1Head->aname ?? 'عميل نقدي' }}</span>
                    </div>
                    @if($transaction->acc1Head && $transaction->acc1Head->phone)
                    <div class="detail-row">
                        <span class="label">الهاتف:</span>
                        <span class="value">{{ $transaction->acc1Head->phone }}</span>
                    </div>
                    @endif
                    @if($transaction->acc1Head && $transaction->acc1Head->address)
                    <div class="detail-row">
                        <span class="label">العنوان:</span>
                        <span class="value">{{ $transaction->acc1Head->address }}</span>
                    </div>
                    @endif
                    </div>
                </div>
            </div>

            <!-- معلومات مالية -->
            <div class="col-lg-4">
                <div class="card h-100 shadow-sm">
                    <div class="card-header bg-dark text-white">
                        <h6 class="mb-0"><i class="fas fa-calculator me-1"></i> الملخص المالي</h6>
                    </div>
                    <div class="card-body">
                    <div class="detail-row">
                        <span class="label">المجموع الفرعي:</span>
                        <span class="value">{{ number_format($transaction->fat_total, 2) }} ريال</span>
                    </div>
                    @if($transaction->fat_disc > 0)
                    <div class="detail-row">
                        <span class="label">الخصم:</span>
                        <span class="value text-danger">-{{ number_format($transaction->fat_disc, 2) }} ريال</span>
                    </div>
                    @endif
                    @if($transaction->fat_plus > 0)
                    <div class="detail-row">
                        <span class="label">الإضافي:</span>
                        <span class="value text-success">+{{ number_format($transaction->fat_plus, 2) }} ريال</span>
                    </div>
                    @endif
                    <div class="detail-row total">
                        <span class="label">الإجمالي:</span>
                        <span class="value">{{ number_format($transaction->fat_net, 2) }} ريال</span>
                    </div>
                    <div class="detail-row">
                        <span class="label">المدفوع:</span>
                        <span class="value">{{ number_format($transaction->paid_from_client ?? $transaction->fat_net, 2) }} ريال</span>
                    </div>
                    @if(($transaction->paid_from_client ?? $transaction->fat_net) > $transaction->fat_net)
                    <div class="detail-row">
                        <span class="label">المتبقي:</span>
                        <span class="value text-info">{{ number_format(($transaction->paid_from_client ?? $transaction->fat_net) - $transaction->fat_net, 2) }} ريال</span>
                    </div>
                    @endif
                    </div>
                </div>
            </div>
        </div>

        <!-- تفاصيل الأصناف -->
        <div class="card shadow-sm">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h6 class="mb-0"><i class="fas fa-shopping-cart me-1"></i> تفاصيل الأصناف</h6>
                <span class="badge bg-primary">{{ $transaction->operationItems->count() }} صنف</span>
            </div>
            
            <div class="table-responsive">
                <table class="table table-striped mb-0">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>الصنف</th>
                            <th>الكمية</th>
                            <th>الوحدة</th>
                            <th>السعر</th>
                            <th>الخصم</th>
                            <th>المجموع</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($transaction->operationItems as $index => $item)
                        <tr>
                            <td>{{ $index + 1 }}</td>
                            <td>
                                <div class="item-info">
                                    <span class="item-name">{{ $item->item->name ?? 'غير محدد' }}</span>
                                    @if($item->item && $item->item->code)
                                    <small class="item-code">كود: {{ $item->item->code }}</small>
                                    @endif
                                </div>
                            </td>
                            <td>
                                <span class="quantity">{{ number_format($item->qty_out, 0) }}</span>
                            </td>
                            <td>
                                <span class="unit">{{ $item->unit->name ?? 'قطعة' }}</span>
                            </td>
                            <td>
                                <span class="price">{{ number_format($item->item_price, 2) }}</span>
                            </td>
                            <td>
                                <span class="discount">{{ number_format($item->item_discount ?? 0, 2) }}</span>
                            </td>
                            <td>
                                <span class="total">{{ number_format($item->detail_value, 2) }} ريال</span>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <!-- ملاحظات -->
        @if($transaction->info)
        <div class="card shadow-sm">
            <div class="card-header">
                <h6 class="mb-0"><i class="fas fa-sticky-note me-1"></i> ملاحظات</h6>
            </div>
            <div class="card-body bg-warning bg-opacity-10 border border-warning">
                <p class="mb-0">{{ $transaction->info }}</p>
            </div>
        </div>
        @endif
    </div>
</div>


@push('styles')
<style>
    @media print {
        .no-print { display: none !important; }
        .card { border: 1px solid #000 !important; box-shadow: none !important; }
        .card-header { background: #f1f1f1 !important; color: #000 !important; }
        body { font-size: 12px; }
        .detail-row { border-bottom: 1px solid #ddd; padding: 0.5rem 0; }
        .table { font-size: 10px; }
    }
</style>
@endpush

<script>
    // دعم اختصارات لوحة المفاتيح
    document.addEventListener('keydown', function(e) {
        // P للطباعة
        if (e.key === 'p' || e.key === 'P') {
            e.preventDefault();
            window.open('{{ route("pos.print", $transaction->id) }}', '_blank');
        }
        
        // ESC للعودة
        if (e.key === 'Escape') {
            e.preventDefault();
            window.location.href = '{{ route("pos.index") }}';
        }
    });
</script>
@endsection
