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
                        <h4 class="card-title">{{ __('General Customers Report') }}</h4>
                    </div>
                    <div class="card-body">
                        <!-- Filters -->
                        <div class="row mb-3">
                            <div class="col-md-3">
                                <label for="from_date" class="form-label">{{ __('From Date') }}</label>
                                <input type="date" wire:model="from_date" class="form-control" id="from_date">
                            </div>
                            <div class="col-md-3">
                                <label for="to_date" class="form-label">{{ __('To Date') }}</label>
                                <input type="date" wire:model="to_date" class="form-control" id="to_date">
                            </div>
                            <div class="col-md-3">
                                <label for="customer_id" class="form-label">{{ __('Customer') }}</label>
                                <select wire:model="customer_id" class="form-select" id="customer_id">
                                    <option value="">{{ __('All Customers') }}</option>
                                    @foreach ($customers as $customer)
                                        <option value="{{ $customer->id }}">{{ $customer->aname }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">&nbsp;</label>
                                <button wire:click="generateReport"
                                    class="btn btn-primary d-block">{{ __('Generate Report') }}</button>
                            </div>
                        </div>

                        <!-- Summary Cards -->
                        <div class="row mb-4">
                            <div class="col-md-2">
                                <div class="card bg-primary text-white">
                                    <div class="card-body">
                                        <h6 class="card-title">{{ __('Total Amount') }}</h6>
                                        <h4 class="card-text">{{ number_format($totalAmount, 2) }}</h4>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="card bg-success text-white">
                                    <div class="card-body">
                                        <h6 class="card-title">{{ __('Total Sales') }}</h6>
                                        <h4 class="card-text">{{ number_format($totalSales, 2) }}</h4>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="card bg-warning text-white">
                                    <div class="card-body">
                                        <h6 class="card-title">{{ __('Total Payments') }}</h6>
                                        <h4 class="card-text">{{ number_format($totalPayments, 2) }}</h4>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="card bg-info text-white">
                                    <div class="card-body">
                                        <h6 class="card-title">{{ __('Final Balance') }}</h6>
                                        <h4 class="card-text">{{ number_format($finalBalance, 2) }}</h4>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="card bg-secondary text-white">
                                    <div class="card-body">
                                        <h6 class="card-title">{{ __('Total Transactions') }}</h6>
                                        <h4 class="card-text">{{ $totalTransactions }}</h4>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="card">
                                    <div class="card-body">
                                        <h6 class="card-title">{{ __('Average Transaction') }}</h6>
                                        <h4 class="card-text">
                                            {{ $totalTransactions > 0 ? number_format($totalAmount / $totalTransactions, 2) : '0.00' }}
                                        </h4>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Data Table -->
                        <div class="table-responsive">
                            <table class="table table-bordered table-striped">
                                <thead>
                                    <tr>
                                        <th>{{ __('Date') }}</th>
                                        <th>{{ __('Journal Number') }}</th>
                                        <th>{{ __('Customer') }}</th>
                                        <th>{{ __('Description') }}</th>
                                        <th class="text-end">{{ __('Debit') }}</th>
                                        <th class="text-end">{{ __('Credit') }}</th>
                                        <th class="text-end">{{ __('Balance') }}</th>
                                        <th>{{ __('Type') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($customerTransactions as $transaction)
                                        <tr>
                                            <td>{{ $transaction->crtime ? \Carbon\Carbon::parse($transaction->crtime)->format('Y-m-d') : '---' }}
                                            </td>
                                            <td>{{ $transaction->head->journal_num ?? '---' }}</td>
                                            <td>{{ $transaction->accHead->aname ?? '---' }}</td>
                                            <td>{{ $transaction->description ?? '---' }}</td>
                                            <td class="text-end">{{ number_format($transaction->debit, 2) }}</td>
                                            <td class="text-end">{{ number_format($transaction->credit, 2) }}</td>
                                            <td class="text-end">
                                                @php
                                                    $balance = $transaction->debit - $transaction->credit;
                                                @endphp
                                                @if ($balance > 0)
                                                    <span class="text-success">{{ number_format($balance, 2) }}</span>
                                                @elseif($balance < 0)
                                                    <span class="text-danger">{{ number_format(abs($balance), 2) }}</span>
                                                @else
                                                    <span class="text-muted">0.00</span>
                                                @endif
                                            </td>
                                            <td>
                                                @if ($transaction->debit > 0)
                                                    <span class="badge bg-success">{{ __('Sales') }}</span>
                                                @elseif($transaction->credit > 0)
                                                    <span class="badge bg-warning">{{ __('Payments') }}</span>
                                                @else
                                                    <span class="badge bg-secondary">{{ __('Unspecified') }}</span>
                                                @endif
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="8" class="text-center">{{ __('No Data Available') }}</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>

                        <!-- Pagination -->
                        @if ($customerTransactions->hasPages())
                            <div class="d-flex justify-content-center mt-3">
                                {{ $customerTransactions->links() }}
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
