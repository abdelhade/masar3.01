@extends('admin.dashboard')

@section('sidebar')
    @include('components.sidebar.reports')
@endsection

@section('content')
<div class="container">
    <div class="card">
        <div class="card-head">
            <h2>قائمة الأصناف مع الأرصدة - كل المخازن</h2>
        </div>
        <div class="card-body">
            <div class="row mb-3">
                <div class="col-md-2">
                    <label for="item_category">التصنيف:</label>
                    <select id="item_category" class="form-control" wire:model="itemCategory">
                        <option value="">الكل</option>
                        @foreach($notes as $note)
                            <option value="{{ $note->id }}">{{ $note->aname }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label for="note_detail_id">التفاصيل:</label>
                    <select id="note_detail_id" class="form-control" wire:model="noteDetailId">
                        <option value="">الكل</option>
                        @foreach($notes as $note)
                            @foreach($note->noteDetails as $detail)
                                <option value="{{ $detail->id }}">{{ $note->aname }} - {{ $detail->aname }}</option>
                            @endforeach
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label for="warehouse_id">المخزن:</label>
                    <select id="warehouse_id" class="form-control" wire:model="warehouseId">
                        <option value="">الكل</option>
                        @foreach($warehouses as $warehouse)
                                                                <option value="{{ $warehouse->id }}">{{ $warehouse->aname }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label for="search">بحث:</label>
                    <input type="text" id="search" class="form-control" wire:model="search" placeholder="اسم الصنف أو الكود">
                </div>
                <div class="col-md-2">
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
                            <th>المخزن</th>
                            <th class="text-end">الرصيد الحالي</th>
                            <th class="text-end">الحد الأدنى</th>
                            <th class="text-end">الحد الأقصى</th>
                            <th>الحالة</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($inventoryBalances as $balance)
                        <tr>
                            <td>{{ $balance->code ?? '---' }}</td>
                            <td>{{ $balance->aname ?? '---' }}</td>
                            <td>{{ $balance->main_unit->aname ?? '---' }}</td>
                            <td>---</td>
                            <td class="text-end">{{ number_format($balance->current_balance, 2) }}</td>
                            <td class="text-end">{{ number_format($balance->min_balance ?? 0, 2) }}</td>
                            <td class="text-end">{{ number_format($balance->max_balance ?? 0, 2) }}</td>
                            <td>
                                @if($balance->current_balance <= ($balance->min_balance ?? 0))
                                    <span class="badge bg-danger">منخفض</span>
                                @elseif($balance->current_balance >= ($balance->max_balance ?? 999999))
                                    <span class="badge bg-warning">مرتفع</span>
                                @else
                                    <span class="badge bg-success">طبيعي</span>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="8" class="text-center">لا توجد بيانات متاحة.</td>
                        </tr>
                        @endforelse
                    </tbody>
                    <tfoot>
                        <tr class="table-primary">
                            <th colspan="4">الإجمالي</th>
                            <th class="text-end">{{ number_format($totalBalance, 2) }}</th>
                            <th colspan="3"></th>
                        </tr>
                    </tfoot>
                </table>
            </div>

            @if($inventoryBalances->hasPages())
                <div class="d-flex justify-content-center">
                    {{ $inventoryBalances->links() }}
                </div>
            @endif

            <!-- ملخص -->
            <div class="row mt-3">
                <div class="col-md-4">
                    <div class="alert alert-info">
                        <strong>إجمالي الأصناف:</strong> {{ $totalItems }}
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="alert alert-warning">
                        <strong>الأصناف منخفضة المخزون:</strong> {{ $lowStockItems }}
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="alert alert-success">
                        <strong>الأصناف طبيعية المخزون:</strong> {{ $normalStockItems }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection 