@extends('admin.dashboard')

{{-- Dynamic Sidebar --}}
@section('sidebar')
    @include('components.sidebar.POS')
@endsection

@section('content')
    @include('components.breadcrumb', [
        'title' => __('عرض عملية نقاط البيع'),
        'items' => [
            ['label' => __('الرئيسية'), 'url' => route('admin.dashboard')],
            ['label' => __('نقاط البيع'), 'url' => route('pos-vouchers.index')],
            ['label' => __('عرض العملية')],
        ],
    ])

    <div class="content-wrapper">
        <section class="content-header">
            <div class="container-fluid">
                <div class="row mb-2">
                    <div class="col-sm-6">
                        <h1 class="font-hold fw-bold">
                            <i class="fas fa-eye me-2"></i>
                            عرض عملية نقاط البيع
                        </h1>
                    </div>
                    <div class="col-sm-6 text-end">
                        <a href="{{ route('pos-vouchers.index') }}" class="btn btn-secondary me-2">
                            <i class="fas fa-arrow-right me-1"></i>
                            العودة للقائمة
                        </a>
                        <a href="{{ route('pos-vouchers.edit', $posVoucher->id) }}" class="btn btn-warning me-2">
                            <i class="fas fa-edit me-1"></i>
                            تعديل
                        </a>
                        <button type="button" class="btn btn-info" onclick="window.print()">
                            <i class="fas fa-print me-1"></i>
                            طباعة
                        </button>
                    </div>
                </div>
            </div>
        </section>

        <section class="content">
            <div class="container-fluid">
                <!-- Voucher Header -->
                <div class="card">
                    <div class="card-header bg-primary text-white">
                        <h4 class="mb-0 font-hold fw-bold">
                            <i class="fas fa-receipt me-2"></i>
                            تفاصيل العملية
                        </h4>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-3">
                                <label class="form-label font-hold fw-bold">رقم العملية:</label>
                                <p class="form-control-static font-hold">{{ $posVoucher->pro_id }}</p>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label font-hold fw-bold">التاريخ:</label>
                                <p class="form-control-static font-hold">{{ $posVoucher->pro_date }}</p>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label font-hold fw-bold">الرقم الدفتري:</label>
                                <p class="form-control-static font-hold">{{ $posVoucher->pro_serial ?? '-' }}</p>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label font-hold fw-bold">رقم الإيصال:</label>
                                <p class="form-control-static font-hold">{{ $posVoucher->pro_num ?? '-' }}</p>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-4">
                                <label class="form-label font-hold fw-bold">العميل:</label>
                                <p class="form-control-static font-hold">{{ $posVoucher->account1->aname ?? '-' }}</p>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label font-hold fw-bold">الصندوق:</label>
                                <p class="form-control-static font-hold">{{ $posVoucher->account2->aname ?? '-' }}</p>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label font-hold fw-bold">الموظف:</label>
                                <p class="form-control-static font-hold">{{ $posVoucher->emp1->aname ?? '-' }}</p>
                            </div>
                        </div>
                        @if($posVoucher->details)
                            <div class="row">
                                <div class="col-12">
                                    <label class="form-label font-hold fw-bold">البيان:</label>
                                    <p class="form-control-static font-hold">{{ $posVoucher->details }}</p>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Items Table -->
                <div class="card">
                    <div class="card-header bg-success text-white">
                        <h4 class="mb-0 font-hold fw-bold">
                            <i class="fas fa-shopping-cart me-2"></i>
                            المنتجات
                        </h4>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-bordered table-striped">
                                <thead class="table-dark">
                                    <tr>
                                        <th class="font-hold fw-bold">م</th>
                                        <th class="font-hold fw-bold">المنتج</th>
                                        <th class="font-hold fw-bold">الوحدة</th>
                                        <th class="font-hold fw-bold">الكمية</th>
                                        <th class="font-hold fw-bold">السعر</th>
                                        <th class="font-hold fw-bold">الإجمالي</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($posVoucher->operationItems as $index => $item)
                                        <tr>
                                            <td class="text-center">{{ $index + 1 }}</td>
                                            <td>
                                                <strong class="font-hold">{{ $item->item->name ?? '-' }}</strong>
                                                <br>
                                                <small class="text-muted">كود: {{ $item->item->code ?? '-' }}</small>
                                            </td>
                                            <td class="text-center font-hold">{{ $item->unit->name ?? '-' }}</td>
                                            <td class="text-center font-hold">{{ number_format($item->qty_in, 2) }}</td>
                                            <td class="text-center font-hold">{{ number_format($item->item_price, 2) }}</td>
                                            <td class="text-center font-hold font-weight-bold text-success">
                                                {{ number_format($item->detail_value, 2) }}
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="6" class="text-center text-muted font-hold">
                                                لا توجد منتجات في هذه العملية
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Totals -->
                <div class="card">
                    <div class="card-header bg-info text-white">
                        <h4 class="mb-0 font-hold fw-bold">
                            <i class="fas fa-calculator me-2"></i>
                            الإجماليات
                        </h4>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6 offset-md-6">
                                <table class="table table-borderless">
                                    <tr>
                                        <td class="font-hold fw-bold h5">إجمالي العملية:</td>
                                        <td class="text-end h5 font-weight-bold text-success">
                                            {{ number_format($posVoucher->pro_value, 2) }}
                                        </td>
                                    </tr>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Journal Entry -->
                <div class="card">
                    <div class="card-header bg-warning text-dark">
                        <h4 class="mb-0 font-hold fw-bold">
                            <i class="fas fa-book me-2"></i>
                            القيد المحاسبي
                        </h4>
                    </div>
                    <div class="card-body">
                        @if($posVoucher->journalHead)
                            <div class="row">
                                <div class="col-md-3">
                                    <label class="form-label font-hold fw-bold">رقم القيد:</label>
                                    <p class="form-control-static font-hold">{{ $posVoucher->journalHead->journal_id }}</p>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label font-hold fw-bold">تاريخ القيد:</label>
                                    <p class="form-control-static font-hold">{{ $posVoucher->journalHead->date }}</p>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label font-hold fw-bold">المبلغ:</label>
                                    <p class="form-control-static font-hold">{{ number_format($posVoucher->journalHead->total, 2) }}</p>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label font-hold fw-bold">البيان:</label>
                                    <p class="form-control-static font-hold">{{ $posVoucher->journalHead->details }}</p>
                                </div>
                            </div>
                        @else
                            <p class="text-muted font-hold">لا يوجد قيد محاسبي مرتبط بهذه العملية</p>
                        @endif
                    </div>
                </div>
            </div>
        </section>
    </div>
@endsection

@push('styles')
<style>
    .font-hold {
        font-family: 'Cairo', sans-serif;
    }
    
    .table th, .table td {
        font-family: 'Cairo', sans-serif;
        vertical-align: middle;
    }
    
    .form-control-static {
        padding: 0.375rem 0.75rem;
        background-color: #f8f9fa;
        border: 1px solid #ced4da;
        border-radius: 0.25rem;
        margin-bottom: 0;
    }
    
    .card {
        box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
        border: 1px solid rgba(0, 0, 0, 0.125);
        margin-bottom: 1rem;
    }
    
    .card-header {
        border-bottom: 1px solid rgba(0, 0, 0, 0.125);
    }
    
    @media print {
        .btn, .content-header {
            display: none !important;
        }
        
        .card {
            border: 1px solid #000 !important;
            box-shadow: none !important;
        }
        
        .table {
            border: 1px solid #000 !important;
        }
        
        .table th, .table td {
            border: 1px solid #000 !important;
        }
    }
</style>
@endpush 