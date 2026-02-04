@extends('admin.dashboard')

@section('sidebar')
    @include('components.sidebar.reports')
@endsection

@section('content')
    <div class="container">
        <div class="card">
            <div class="card-head">
                <h2>{{ __('Customers Total Report') }}</h2>
            </div>
            <div class="card-body">
                <div class="row mb-3">
                    <div class="col-md-3">
                        <label for="from_date">{{ __('From Date') }}:</label>
                        <input type="date" id="from_date" class="form-control" wire:model="fromDate">
                    </div>
                    <div class="col-md-3">
                        <label for="to_date">{{ __('To Date') }}:</label>
                        <input type="date" id="to_date" class="form-control" wire:model="toDate">
                    </div>
                    <div class="col-md-3">
                        <label for="group_by">{{ __('Group By') }}:</label>
                        <select id="group_by" class="form-control" wire:model="groupBy">
                            <option value="customer">{{ __('Customer') }}</option>
                            <option value="day">{{ __('Day') }}</option>
                            <option value="week">{{ __('Week') }}</option>
                            <option value="month">{{ __('Month') }}</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <button class="btn btn-primary mt-4"
                            wire:click="generateReport">{{ __('Generate Report') }}</button>
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th>{{ $groupBy == 'customer' ? __('Customer') : __('Period') }}</th>
                                <th class="text-end">{{ __('Transactions Count') }}</th>
                                <th class="text-end">{{ __('Total Sales') }}</th>
                                <th class="text-end">{{ __('Total Payments') }}</th>
                                <th class="text-end">{{ __('Balance') }}</th>
                                <th class="text-end">{{ __('Average Transaction') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($customerTotals as $total)
                                <tr>
                                    <td>
                                        @if ($groupBy == 'customer')
                                            {{ $total->customer_name ?? '---' }}
                                        @else
                                            {{ $total->period_name ?? '---' }}
                                        @endif
                                    </td>
                                    <td class="text-end">{{ $total->transactions_count }}</td>
                                    <td class="text-end">{{ number_format($total->total_sales, 2) }}</td>
                                    <td class="text-end">{{ number_format($total->total_payments, 2) }}</td>
                                    <td class="text-end">{{ number_format($total->balance, 2) }}</td>
                                    <td class="text-end">{{ number_format($total->average_transaction, 2) }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center">{{ __('No Data Available') }}</td>
                                </tr>
                            @endforelse
                        </tbody>
                        <tfoot>
                            <tr class="table-primary">
                                <th>{{ __('Total') }}</th>
                                <th class="text-end">{{ $grandTotalTransactions }}</th>
                                <th class="text-end">{{ number_format($grandTotalSales, 2) }}</th>
                                <th class="text-end">{{ number_format($grandTotalPayments, 2) }}</th>
                                <th class="text-end">{{ number_format($grandTotalBalance, 2) }}</th>
                                <th class="text-end">{{ number_format($grandAverageTransaction, 2) }}</th>
                            </tr>
                        </tfoot>
                    </table>
                </div>

                @if ($customerTotals->hasPages())
                    <div class="d-flex justify-content-center">
                        {{ $customerTotals->links() }}
                    </div>
                @endif

                <!-- Summary -->
                <div class="row mt-3">
                    <div class="col-md-3">
                        <div class="alert alert-info">
                            <strong>{{ __('Total Customers') }}:</strong> {{ $totalCustomers }}
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="alert alert-success">
                            <strong>{{ __('Top Customer Sales') }}:</strong> {{ $topCustomer ?? '---' }}
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="alert alert-warning">
                            <strong>{{ __('Average Sales Per Customer') }}:</strong>
                            {{ number_format($averageSalesPerCustomer, 2) }}
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="alert alert-primary">
                            <strong>{{ __('Average Balance Per Customer') }}:</strong>
                            {{ number_format($averageBalancePerCustomer, 2) }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
