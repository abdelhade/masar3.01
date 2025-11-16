@extends('admin.dashboard')

@section('sidebar')
    @include('components.sidebar.reports')
@endsection

@section('content')
<div class="container">
    <div class="card">
        <div class="card-head">
            <h2>حركة الصنف</h2>
        </div>
        <div class="card-body">
            <div class="row mb-3">
                <div class="col-md-3">
                    <label for="item_id">الصنف:</label>
                    <select id="item_id" class="form-control" wire:model="itemId">
                        <option value="">اختر الصنف</option>
                        @foreach($items as $item)
                            <option value="{{ $item->id }}">{{ $item->code }} - {{ $item->aname }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label for="warehouse_id">المخزن:</label>
                    <select id="warehouse_id" class="form-control" wire:model="warehouseId">
                        <option value="all">كل المخازن</option>
                        @foreach($warehouses as $warehouse)
                                                                <option value="{{ $warehouse->id }}">{{ $warehouse->aname }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label for="from_date">من تاريخ:</label>
                    <input type="date" id="from_date" class="form-control" wire:model="fromDate">
                </div>
                <div class="col-md-3">
                    <label for="to_date">إلى تاريخ:</label>
                    <input type="date" id="to_date" class="form-control" wire:model="toDate">
                </div>
            </div>

            @if($selectedItem)
            <div class="row mb-3">
                <div class="col-12">
                    <div class="alert alert-info">
                        <strong>الصنف المحدد:</strong> {{ $selectedItem->code }} - {{ $selectedItem->aname }}
                        <br>
                        <strong>الوحدة:</strong> {{ $selectedItem->unit->aname ?? '---' }}
                        <br>
                        <strong>الرصيد الحالي:</strong> {{ number_format($currentBalance, 2) }}
                    </div>
                </div>
            </div>
            @endif

            <div class="table-responsive">
                <table class="table table-bordered table-striped">
                    <thead>
                        <tr>
                            <th>التاريخ</th>
                            <th>نوع العملية</th>
                            <th>رقم العملية</th>
                            <th>المخزن</th>
                            <th class="text-end">كمية وارد</th>
                            <th class="text-end">كمية صادر</th>
                            <th class="text-end">الرصيد</th>
                            <th>البيان</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($movements as $movement)
                        <tr>
                            <td>{{ $movement->created_at ? \Carbon\Carbon::parse($movement->created_at)->format('Y-m-d') : '---' }}</td>
                            <td>
                                <span class="badge bg-{{ $movement->qty_in > 0 ? 'success' : 'danger' }}">
                                    {{ $movement->getOperationTypeText() }}
                                </span>
                            </td>
                            <td>{{ $movement->pro_id ?? '---' }}</td>
                            <td>---</td>
                            <td class="text-end">{{ $movement->qty_in > 0 ? number_format($movement->qty_in, 2) : '---' }}</td>
                            <td class="text-end">{{ $movement->qty_out > 0 ? number_format($movement->qty_out, 2) : '---' }}</td>
                            <td class="text-end">{{ number_format($movement->running_balance, 2) }}</td>
                            <td>{{ $movement->details ?? '---' }}</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="8" class="text-center">لا توجد بيانات متاحة.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($movements->hasPages())
                <div class="d-flex justify-content-center">
                    {{ $movements->links() }}
                </div>
            @endif

            <!-- ملخص -->
            @if($selectedItem)
            <div class="row mt-3">
                <div class="col-md-3">
                    <div class="alert alert-success">
                        <strong>إجمالي الوارد:</strong> {{ number_format($totalIn, 2) }}
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="alert alert-danger">
                        <strong>إجمالي الصادر:</strong> {{ number_format($totalOut, 2) }}
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="alert alert-info">
                        <strong>صافي الحركة:</strong> {{ number_format($netMovement, 2) }}
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="alert alert-warning">
                        <strong>عدد العمليات:</strong> {{ $totalOperations }}
                    </div>
                </div>
            </div>
            @endif
        </div>
    </div>
</div>
@endsection 