@extends('admin.dashboard')

@section('sidebar')
    @include('components.sidebar.reports')
@endsection

@section('content')
    <div class="container">
        <div class="card">
            <div class="card-head">
                <h2>{{ __('Customers Daily Report') }}</h2>
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
                        <label for="customer_id">{{ __('Customer') }}:</label>
                        <select id="customer_id" class="form-control" wire:model="customerId">
                            <option value="">{{ __('All Customers') }}</option>
                            @foreach ($customers as $customer)
                                <option value="{{ $customer->id }}">{{ $customer->aname }}</option>
                            @endforeach
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
                                <th>{{ __('Date') }}</th>
                                <th>{{ __('Customer') }}</th>
                                <th>{{ __('Operation Type') }}</th>
                                <th>{{ __('Operation Number') }}</th>
                                <th class="text-end">{{ __('Amount') }}</th>
                                <th class="text-end">{{ __('Balance') }}</th>
                                <th>{{ __('Description') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($customerTransactions as $transaction)
                                <tr>
                                    <td>{{ $transaction->pro_date ? \Carbon\Carbon::parse($transaction->pro_date)->format('Y-m-d') : '---' }}
                                    </td>
                                    <td>{{ $transaction->accHead->aname ?? '---' }}</td>
                                    <td>
                                        <span
                                            class="badge bg-{{ $transaction->type == 'sale' ? 'success' : ($transaction->type == 'payment' ? 'info' : 'warning') }}">
                                            {{ $transaction->type == 'sale' ? __('Sale') : ($transaction->type == 'payment' ? __('Payment') : __('Other')) }}
                                        </span>
                                    </td>
                                    <td>{{ $transaction->pro_num ?? '---' }}</td>
                                    <td class="text-end">{{ number_format($transaction->amount, 2) }}</td>
                                    <td class="text-end">{{ number_format($transaction->balance, 2) }}</td>
                                    <td>{{ $transaction->details ?? '---' }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="text-center">{{ __('No Data Available') }}</td>
                                </tr>
                            @endforelse
                        </tbody>
                        <tfoot>
                            <tr class="table-primary">
                                <th colspan="4">{{ __('Total') }}</th>
                                <th class="text-end">{{ number_format($totalAmount, 2) }}</th>
                                <th class="text-end">{{ number_format($finalBalance, 2) }}</th>
                                <th></th>
                            </tr>
                        </tfoot>
                    </table>
                </div>

                @if ($customerTransactions->hasPages())
                    <div class="d-flex justify-content-center">
                        {{ $customerTransactions->links() }}
                    </div>
                @endif

                <!-- Summary -->
                <div class="row mt-3">
                    <div class="col-md-3">
                        <div class="alert alert-info">
                            <strong>{{ __('Total Transactions') }}:</strong> {{ $totalTransactions }}
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="alert alert-success">
                            <strong>{{ __('Total Sales') }}:</strong> {{ number_format($totalSales, 2) }}
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="alert alert-warning">
                            <strong>{{ __('Total Payments') }}:</strong> {{ number_format($totalPayments, 2) }}
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="alert alert-primary">
                            <strong>{{ __('Final Balance') }}:</strong> {{ number_format($finalBalance, 2) }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
