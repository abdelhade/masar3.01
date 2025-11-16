@extends('admin.dashboard')

@section('sidebar')
    @include('components.sidebar.reports')
@endsection

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title">تقرير مراكز التكلفة العام</h4>
                </div>
                <div class="card-body">
                    <!-- Filters -->
                    <div class="row mb-3">
                        <div class="col-md-3">
                            <label for="from_date" class="form-label">من تاريخ</label>
                            <input type="date" wire:model="from_date" class="form-control" id="from_date">
                        </div>
                        <div class="col-md-3">
                            <label for="to_date" class="form-label">إلى تاريخ</label>
                            <input type="date" wire:model="to_date" class="form-control" id="to_date">
                        </div>
                        <div class="col-md-3">
                            <label for="cost_center_id" class="form-label">مركز التكلفة</label>
                            <select wire:model="cost_center_id" class="form-select" id="cost_center_id">
                                <option value="">جميع مراكز التكلفة</option>
                                @foreach($costCenters as $center)
                                    <option value="{{ $center->id }}">{{ $center->aname }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">&nbsp;</label>
                            <button wire:click="generateReport" class="btn btn-primary d-block">توليد التقرير</button>
                        </div>
                    </div>

                    <!-- Summary Cards -->
                    <div class="row mb-4">
                        <div class="col-md-3">
                            <div class="card bg-primary text-white">
                                <div class="card-body">
                                    <h6 class="card-title">إجمالي المصروفات</h6>
                                    <h4 class="card-text">{{ number_format($totalExpenses, 2) }}</h4>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card bg-success text-white">
                                <div class="card-body">
                                    <h6 class="card-title">إجمالي الإيرادات</h6>
                                    <h4 class="card-text">{{ number_format($totalRevenues, 2) }}</h4>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card bg-info text-white">
                                <div class="card-body">
                                    <h6 class="card-title">صافي التكلفة</h6>
                                    <h4 class="card-text">{{ number_format($netCost, 2) }}</h4>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card bg-secondary text-white">
                                <div class="card-body">
                                    <h6 class="card-title">عدد المعاملات</h6>
                                    <h4 class="card-text">{{ $totalTransactions }}</h4>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Data Table -->
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped">
                            <thead>
                                <tr>
                                    <th>التاريخ</th>
                                    <th>رقم القيد</th>
                                    <th>مركز التكلفة</th>
                                    <th>الحساب</th>
                                    <th>الوصف</th>
                                    <th class="text-end">مدين</th>
                                    <th class="text-end">دائن</th>
                                    <th class="text-end">الرصيد</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($costCenterTransactions as $transaction)
                                <tr>
                                    <td>{{ $transaction->crtime ? \Carbon\Carbon::parse($transaction->crtime)->format('Y-m-d') : '---' }}</td>
                                    <td>{{ $transaction->journalHead->journal_num ?? '---' }}</td>
                                    <td>{{ $transaction->costCenter->name ?? '---' }}</td>
                                    <td>{{ $transaction->accountHead->aname ?? '---' }}</td>
                                    <td>{{ $transaction->description ?? '---' }}</td>
                                    <td class="text-end">{{ number_format($transaction->debit, 2) }}</td>
                                    <td class="text-end">{{ number_format($transaction->credit, 2) }}</td>
                                    <td class="text-end">
                                        @php
                                            $balance = $transaction->debit - $transaction->credit;
                                        @endphp
                                        @if($balance > 0)
                                            <span class="text-danger">{{ number_format($balance, 2) }}</span>
                                        @elseif($balance < 0)
                                            <span class="text-success">{{ number_format(abs($balance), 2) }}</span>
                                        @else
                                            <span class="text-muted">0.00</span>
                                        @endif
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="8" class="text-center">لا توجد بيانات متاحة.</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    @if($costCenterTransactions->hasPages())
                    <div class="d-flex justify-content-center mt-3">
                        {{ $costCenterTransactions->links() }}
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection 