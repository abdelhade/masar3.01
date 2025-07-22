@extends('admin.dashboard')

@section('content')
<div class="container">
    <div class="card">
        <div class="card-head">
            <h2>تقرير الموردين أصناف</h2>
        </div>
        <div class="card-body">
            <div class="row mb-3">
                <div class="col-md-3">
                    <label for="from_date">من تاريخ:</label>
                    <input type="date" id="from_date" class="form-control" wire:model="fromDate">
                </div>
                <div class="col-md-3">
                    <label for="to_date">إلى تاريخ:</label>
                    <input type="date" id="to_date" class="form-control" wire:model="toDate">
                </div>
                <div class="col-md-3">
                    <label for="supplier_id">المورد:</label>
                    <select id="supplier_id" class="form-control" wire:model="supplierId">
                        <option value="">الكل</option>
                        @foreach($suppliers as $supplier)
                            <option value="{{ $supplier->id }}">{{ $supplier->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <button class="btn btn-primary mt-4" wire:click="generateReport">توليد التقرير</button>
                </div>
            </div>

            <div class="table-responsive">
                <table class="table table-bordered table-striped">
                    <thead>
                        <tr>
                            <th>كود الصنف</th>
                            <th>اسم الصنف</th>
                            <th>الوحدة</th>
                            <th class="text-end">الكمية المشتراة</th>
                            <th class="text-end">إجمالي المشتريات</th>
                            <th class="text-end">متوسط السعر</th>
                            <th class="text-end">عدد الفواتير</th>
                            <th class="text-end">نسبة المشتريات</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($supplierItems as $item)
                        <tr>
                            <td>{{ $item->item_code ?? '---' }}</td>
                            <td>{{ $item->item_name ?? '---' }}</td>
                            <td>{{ $item->unit_name ?? '---' }}</td>
                            <td class="text-end">{{ number_format($item->total_quantity, 2) }}</td>
                            <td class="text-end">{{ number_format($item->total_purchases, 2) }}</td>
                            <td class="text-end">{{ number_format($item->average_price, 2) }}</td>
                            <td class="text-end">{{ $item->invoices_count }}</td>
                            <td class="text-end">{{ number_format($item->purchases_percentage, 2) }}%</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="8" class="text-center">لا توجد بيانات متاحة.</td>
                        </tr>
                        @endforelse
                    </tbody>
                    <tfoot>
                        <tr class="table-primary">
                            <th colspan="3">الإجمالي</th>
                            <th class="text-end">{{ number_format($totalQuantity, 2) }}</th>
                            <th class="text-end">{{ number_format($totalPurchases, 2) }}</th>
                            <th class="text-end">{{ number_format($averagePrice, 2) }}</th>
                            <th class="text-end">{{ $totalInvoices }}</th>
                            <th class="text-end">100%</th>
                        </tr>
                    </tfoot>
                </table>
            </div>

            @if($supplierItems->hasPages())
                <div class="d-flex justify-content-center">
                    {{ $supplierItems->links() }}
                </div>
            @endif

            <!-- ملخص -->
            <div class="row mt-3">
                <div class="col-md-3">
                    <div class="alert alert-info">
                        <strong>إجمالي الأصناف المشتراة:</strong> {{ $totalItems }}
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="alert alert-success">
                        <strong>أعلى صنف مشترى:</strong> {{ $topPurchasedItem ?? '---' }}
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="alert alert-warning">
                        <strong>متوسط الكمية للصنف:</strong> {{ number_format($averageQuantityPerItem, 2) }}
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="alert alert-primary">
                        <strong>متوسط المشتريات للصنف:</strong> {{ number_format($averagePurchasesPerItem, 2) }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection 