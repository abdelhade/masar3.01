@extends('admin.dashboard')

@section('sidebar')
    @include('components.sidebar.reports')
@endsection

@section('content')
<div class="container">
    <div class="card">
        <div class="card-head">
            <h2>قائمة الأصناف مع الأرصدة - مخزن محدد</h2>
        </div>
        <div class="card-body">
            <form method="GET" action="{{ request()->url() }}">
                <div class="row mb-3">
                    <div class="col-md-3">
                        <label for="warehouse_id">المخزن:</label>
                        <select id="warehouse_id" name="warehouse_id" class="form-control">
                            <option value="">اختر المخزن</option>
                            @foreach($warehouses as $warehouse)
                                <option value="{{ $warehouse->id }}" {{ request('warehouse_id') == $warehouse->id ? 'selected' : '' }}>{{ $warehouse->aname }}</option>
                            @endforeach
                        </select>
                    </div>
                    @foreach($notes as $note)
                    <div class="col-md-2">
                        <label for="note_{{ $note->id }}">{{ $note->name }}:</label>
                        <select id="note_{{ $note->id }}" name="note_{{ $note->id }}" class="form-control">
                            <option value="">{{ __('reports.all') }}</option>
                            @foreach($note->noteDetails as $detail)
                                <option value="{{ $detail->name }}" {{ request('note_' . $note->id) == $detail->name ? 'selected' : '' }}>{{ $detail->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    @endforeach
                    <div class="col-md-2">
                        <label for="search">{{ __('reports.search') }}:</label>
                        <input type="text" id="search" name="search" class="form-control" value="{{ request('search') }}" placeholder="{{ __('reports.search_item_or_code') }}">
                    </div>
                    <div class="col-md-2">
                        <button type="submit" class="btn btn-primary mt-4">توليد التقرير</button>
                    </div>
                </div>
            </form>

            @if($selectedWarehouse)
            <div class="row mb-3">
                <div class="col-12">
                    <div class="alert alert-info">
                        <strong>المخزن المحدد:</strong> {{ $selectedWarehouse->aname }}
                        <br>
                        <strong>العنوان:</strong> {{ $selectedWarehouse->address ?? '---' }}
                    </div>
                </div>
            </div>
            @endif

            <div class="table-responsive">
                <table class="table table-bordered table-striped">
                    <thead>
                        <tr>
                            <th>كود الصنف</th>
                            <th>اسم الصنف</th>
                            <th>الوحدة</th>
                            <th class="text-end">الرصيد الحالي</th>
                            <th class="text-end">الحد الأدنى</th>
                            <th class="text-end">الحد الأقصى</th>
                            <th class="text-end">القيمة</th>
                            <th>الحالة</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($inventoryBalances as $balance)
                        <tr>
                            <td>{{ $balance->code ?? '---' }}</td>
                            <td>{{ $balance->name ?? '---' }}</td>
                            <td>{{ $balance->units->first()->name ?? '---' }}</td>
                            <td class="text-end">{{ number_format($balance->current_balance, 2) }}</td>
                            <td class="text-end">{{ number_format($balance->min_balance ?? 0, 2) }}</td>
                            <td class="text-end">{{ number_format($balance->max_balance ?? 0, 2) }}</td>
                            <td class="text-end">{{ number_format($balance->value, 2) }}</td>
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
                            <th colspan="3">الإجمالي</th>
                            <th class="text-end">{{ number_format($totalBalance, 2) }}</th>
                            <th colspan="2"></th>
                            <th class="text-end">{{ number_format($totalValue, 2) }}</th>
                            <th></th>
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
                <div class="col-md-3">
                    <div class="alert alert-info">
                        <strong>إجمالي الأصناف:</strong> {{ $totalItems }}
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="alert alert-danger">
                        <strong>الأصناف منخفضة المخزون:</strong> {{ $lowStockItems }}
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="alert alert-warning">
                        <strong>الأصناف مرتفعة المخزون:</strong> {{ $highStockItems }}
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="alert alert-success">
                        <strong>الأصناف طبيعية المخزون:</strong> {{ $normalStockItems }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection 