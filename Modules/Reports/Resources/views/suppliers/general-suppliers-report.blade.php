@extends('admin.dashboard')

{{-- Dynamic Sidebar --}}
@section('sidebar')
    @include('components.sidebar.reports')
@endsection

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title">{{ __('reports.general_suppliers_report') }}</h4>
                </div>
                <div class="card-body">
                    <!-- Filters -->
                    <div class="row mb-3">
                        <div class="col-md-3">
                            <label for="from_date" class="form-label">{{ __('reports.from_date') }}</label>
                            <input type="date" wire:model="from_date" class="form-control" id="from_date">
                        </div>
                        <div class="col-md-3">
                            <label for="to_date" class="form-label">{{ __('reports.to_date') }}</label>
                            <input type="date" wire:model="to_date" class="form-control" id="to_date">
                        </div>
                        <div class="col-md-3">
                            <label for="supplier_id" class="form-label">{{ __('reports.supplier') }}</label>
                            <select wire:model="supplier_id" class="form-select" id="supplier_id">
                                <option value="">{{ __('reports.all_suppliers') }}</option>
                                @foreach($suppliers as $supplier)
                                    <option value="{{ $supplier->id }}">{{ $supplier->aname }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">&nbsp;</label>
                            <button wire:click="generateReport" class="btn btn-primary d-block">{{ __('reports.generate_report') }}</button>
                        </div>
                    </div>

                    <!-- Summary Cards -->
                    <div class="row mb-4">
                        <div class="col-md-2">
                            <div class="card bg-primary text-white">
                                <div class="card-body">
                                    <h6 class="card-title">{{ __('reports.total_amount') }}</h6>
                                    <h4 class="card-text">{{ number_format($totalAmount, 2) }}</h4>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="card bg-success text-white">
                                <div class="card-body">
                                    <h6 class="card-title">{{ __('reports.total_purchases') }}</h6>
                                    <h4 class="card-text">{{ number_format($totalPurchases, 2) }}</h4>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="card bg-warning text-white">
                                <div class="card-body">
                                    <h6 class="card-title">{{ __('reports.total_payments') }}</h6>
                                    <h4 class="card-text">{{ number_format($totalPayments, 2) }}</h4>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="card bg-info text-white">
                                <div class="card-body">
                                    <h6 class="card-title">{{ __('reports.final_balance') }}</h6>
                                    <h4 class="card-text">{{ number_format($finalBalance, 2) }}</h4>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="card bg-secondary text-white">
                                <div class="card-body">
                                    <h6 class="card-title">{{ __('reports.total_transactions') }}</h6>
                                    <h4 class="card-text">{{ $totalTransactions }}</h4>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="card bg-dark text-white">
                                <div class="card-body">
                                    <h6 class="card-title">{{ __('reports.average_transaction') }}</h6>
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
                                    <th>{{ __('reports.date') }}</th>
                                    <th>{{ __('reports.journal_number') }}</th>
                                    <th>{{ __('reports.supplier') }}</th>
                                    <th>{{ __('reports.description') }}</th>
                                    <th class="text-end">{{ __('reports.debit') }}</th>
                                    <th class="text-end">{{ __('reports.credit') }}</th>
                                    <th class="text-end">{{ __('reports.balance') }}</th>
                                    <th>{{ __('reports.type') }}</th>
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
                                            <span class="badge bg-success">{{ __('reports.purchase_report') }}</span>
                                        @elseif($transaction->debit > 0)
                                            <span class="badge bg-warning">{{ __('reports.payments') }}</span>
                                        @else
                                            <span class="badge bg-secondary">{{ __('reports.unspecified') }}</span>
                                        @endif
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="8" class="text-center">{{ __('reports.no_data_available') }}</td>
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