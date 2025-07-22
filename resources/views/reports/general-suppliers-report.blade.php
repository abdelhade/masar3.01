@extends('admin.dashboard')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title">تقرير الموردين العام</h4>
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
                            <label for="supplier_id" class="form-label">المورد</label>
                            <select wire:model="supplier_id" class="form-select" id="supplier_id">
                                <option value="">جميع الموردين</option>
                                @foreach($suppliers as $supplier)
                                    <option value="{{ $supplier->id }}">{{ $supplier->aname }}</option>
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
                        <div class="col-md-2">
                            <div class="card bg-primary text-white">
                                <div class="card-body">
                                    <h6 class="card-title">إجمالي المبلغ</h6>
                                    <h4 class="card-text">{{ number_format($totalAmount, 2) }}</h4>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="card bg-success text-white">
                                <div class="card-body">
                                    <h6 class="card-title">إجمالي المشتريات</h6>
                                    <h4 class="card-text">{{ number_format($totalPurchases, 2) }}</h4>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="card bg-warning text-white">
                                <div class="card-body">
                                    <h6 class="card-title">إجمالي المدفوع</h6>
                                    <h4 class="card-text">{{ number_format($totalPayments, 2) }}</h4>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="card bg-info text-white">
                                <div class="card-body">
                                    <h6 class="card-title">الرصيد النهائي</h6>
                                    <h4 class="card-text">{{ number_format($finalBalance, 2) }}</h4>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="card bg-secondary text-white">
                                <div class="card-body">
                                    <h6 class="card-title">عدد المعاملات</h6>
                                    <h4 class="card-text">{{ $totalTransactions }}</h4>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="card bg-dark text-white">
                                <div class="card-body">
                                    <h6 class="card-title">متوسط المعاملة</h6>
                                    <h4 class="card-text">{{ $totalTransactions > 0 ? number_format($totalAmount / $totalTransactions, 2) : '0.00' }}</h4>
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
                                    <th>المورد</th>
                                    <th>الوصف</th>
                                    <th class="text-end">مدين</th>
                                    <th class="text-end">دائن</th>
                                    <th class="text-end">الرصيد</th>
                                    <th>النوع</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($supplierTransactions as $transaction)
                                <tr>
                                    <td>{{ $transaction->crtime ? \Carbon\Carbon::parse($transaction->crtime)->format('Y-m-d') : '---' }}</td>
                                    <td>{{ $transaction->journalHead->journal_num ?? '---' }}</td>
                                    <td>{{ $transaction->accountHead->aname ?? '---' }}</td>
                                    <td>{{ $transaction->description ?? '---' }}</td>
                                    <td class="text-end">{{ number_format($transaction->debit, 2) }}</td>
                                    <td class="text-end">{{ number_format($transaction->credit, 2) }}</td>
                                    <td class="text-end">
                                        @php
                                            $balance = $transaction->debit - $transaction->credit;
                                        @endphp
                                        @if($balance > 0)
                                            <span class="text-success">{{ number_format($balance, 2) }}</span>
                                        @elseif($balance < 0)
                                            <span class="text-danger">{{ number_format(abs($balance), 2) }}</span>
                                        @else
                                            <span class="text-muted">0.00</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($transaction->credit > 0)
                                            <span class="badge bg-success">مشتريات</span>
                                        @elseif($transaction->debit > 0)
                                            <span class="badge bg-warning">مدفوعات</span>
                                        @else
                                            <span class="badge bg-secondary">غير محدد</span>
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
                    @if($supplierTransactions->hasPages())
                    <div class="d-flex justify-content-center mt-3">
                        {{ $supplierTransactions->links() }}
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection 