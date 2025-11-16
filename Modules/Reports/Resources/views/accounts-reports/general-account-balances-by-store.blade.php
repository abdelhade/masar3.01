@extends('admin.dashboard')

@section('sidebar')
    @include('components.sidebar.accounts')
    @include('components.sidebar.sales-invoices')
    @include('components.sidebar.purchases-invoices')
    @include('components.sidebar.items')
@endsection

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title">قائمة الحسابات مع الأرصدة</h4>
                </div>
                <div class="card-body">
                    <!-- Filters -->
                    <div class="row mb-3">
                        <div class="col-md-3">
                            <label for="warehouse_id" class="form-label">المخزن</label>
                            <select wire:model="warehouse_id" class="form-select" id="warehouse_id">
                                <option value="">اختر المخزن</option>
                                @foreach($warehouses as $warehouse)
                                    <option value="{{ $warehouse->id }}">{{ $warehouse->aname }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label for="as_of_date" class="form-label">تاريخ الجرد</label>
                            <input type="date" wire:model="as_of_date" class="form-control" id="as_of_date" value="{{ $asOfDate }}">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">&nbsp;</label>
                            <button wire:click="generateReport" class="btn btn-primary d-block">توليد التقرير</button>
                        </div>
                    </div>

                    @if($selectedWarehouse)
                    <!-- Summary Cards -->
                    <div class="row mb-4">
                        <div class="col-md-3">
                            <div class="card bg-primary text-white">
                                <div class="card-body">
                                    <h5 class="card-title">إجمالي المدين</h5>
                                    <h3 class="card-text">{{ number_format($totalDebit, 2) }}</h3>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card bg-success text-white">
                                <div class="card-body">
                                    <h5 class="card-title">إجمالي الدائن</h5>
                                    <h3 class="card-text">{{ number_format($totalCredit, 2) }}</h3>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card bg-info text-white">
                                <div class="card-body">
                                    <h5 class="card-title">الرصيد الصافي</h5>
                                    <h3 class="card-text">{{ number_format($totalBalance, 2) }}</h3>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card bg-warning text-white">
                                <div class="card-body">
                                    <h5 class="card-title">عدد الحسابات</h5>
                                    <h3 class="card-text">{{ $accountBalances->count() }}</h3>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Data Table -->
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped">
                            <thead>
                                <tr>
                                    <th>رقم الحساب</th>
                                    <th>اسم الحساب</th>
                                    <th class="text-end">مدين</th>
                                    <th class="text-end">دائن</th>
                                    <th class="text-end">الرصيد</th>
                                    <th>الحالة</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($accountBalances as $account)
                                <tr>
                                    <td>{{ $account->code ?? '---' }}</td>
                                    <td>{{ $account->aname ?? '---' }}</td>
                                    <td class="text-end">{{ number_format($account->debit, 2) }}</td>
                                    <td class="text-end">{{ number_format($account->credit, 2) }}</td>
                                    <td class="text-end">
                                        @if($account->balance > 0)
                                            <span class="text-success">{{ number_format($account->balance, 2) }}</span>
                                        @elseif($account->balance < 0)
                                            <span class="text-danger">{{ number_format(abs($account->balance), 2) }}</span>
                                        @else
                                            <span class="text-muted">0.00</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($account->balance > 0)
                                            <span class="badge bg-success">مدين</span>
                                        @elseif($account->balance < 0)
                                            <span class="badge bg-danger">دائن</span>
                                        @else
                                            <span class="badge bg-secondary">متوازن</span>
                                        @endif
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="6" class="text-center">لا توجد بيانات متاحة.</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    @if($accountBalances->hasPages())
                    <div class="d-flex justify-content-center mt-3">
                        {{ $accountBalances->links() }}
                    </div>
                    @endif
                    @else
                    <div class="alert alert-info text-center">
                        <h5>يرجى اختيار مخزن لعرض البيانات</h5>
                        <p>اختر مخزن من القائمة أعلاه لتوليد تقرير الأرصدة</p>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection 