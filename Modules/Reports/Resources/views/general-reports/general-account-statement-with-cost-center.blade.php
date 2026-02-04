@extends('admin.dashboard')

@section('sidebar')
    @include('components.sidebar.reports')
@endsection

@section('content')
    <div class="container">
        <div class="card">
            <div class="card-head">
                <h2>{{ __('General Account Statement with Cost Center') }}</h2>
            </div>
            <div class="card-body">
                <div class="row mb-3">
                    <div class="col-md-3">
                        <label for="account_id">{{ __('Account') }}:</label>
                        <select id="account_id" class="form-control" wire:model="accountId">
                            <option value="">{{ __('Select Account') }}</option>
                            @foreach ($accounts as $account)
                                <option value="{{ $account->id }}">{{ $account->code }} - {{ $account->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label for="cost_center_id">{{ __('Cost Center') }}:</label>
                        <select id="cost_center_id" class="form-control" wire:model="costCenterId">
                            <option value="">{{ __('All') }}</option>
                            @foreach ($costCenters as $center)
                                <option value="{{ $center->id }}">{{ $center->code }} - {{ $center->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label for="from_date">{{ __('From Date') }}:</label>
                        <input type="date" id="from_date" class="form-control" wire:model="fromDate">
                    </div>
                    <div class="col-md-3">
                        <label for="to_date">{{ __('To Date') }}:</label>
                        <input type="date" id="to_date" class="form-control" wire:model="toDate">
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-3">
                        <button class="btn btn-primary" wire:click="generateReport">{{ __('Generate Report') }}</button>
                    </div>
                </div>

                @if ($selectedAccount)
                    <div class="row mb-3">
                        <div class="col-12">
                            <div class="alert alert-info">
                                <strong>{{ __('Selected Account') }}:</strong> {{ $selectedAccount->code }} -
                                {{ $selectedAccount->name }}
                                <br>
                                <strong>{{ __('Opening Balance') }}:</strong> {{ number_format($openingBalance, 2) }}
                            </div>
                        </div>
                    </div>
                @endif

                <div class="table-responsive">
                    <table class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th>{{ __('Date') }}</th>
                                <th>{{ __('Operation Number') }}</th>
                                <th>{{ __('Cost Center') }}</th>
                                <th>{{ __('Description') }}</th>
                                <th class="text-end">{{ __('Debit') }}</th>
                                <th class="text-end">{{ __('Credit') }}</th>
                                <th class="text-end">{{ __('Balance') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($accountTransactions as $transaction)
                                <tr>
                                    <td>{{ $transaction->crtime ? \Carbon\Carbon::parse($transaction->crtime)->format('Y-m-d') : '---' }}
                                    </td>
                                    <td>{{ $transaction->journalHead->journal_id ?? '---' }}</td>
                                    <td>{{ $transaction->costCenter->name ?? '---' }}</td>
                                    <td>{{ $transaction->info ?? '---' }}</td>
                                    <td class="text-end">
                                        @if ($transaction->debit > 0)
                                            <span class="text-danger">{{ number_format($transaction->debit, 2) }}</span>
                                        @else
                                            <span class="text-muted">---</span>
                                        @endif
                                    </td>
                                    <td class="text-end">
                                        @if ($transaction->credit > 0)
                                            <span class="text-success">{{ number_format($transaction->credit, 2) }}</span>
                                        @else
                                            <span class="text-muted">---</span>
                                        @endif
                                    </td>
                                    <td class="text-end">
                                        <span
                                            class="{{ $transaction->running_balance < 0 ? 'text-success' : 'text-danger' }} fw-bold">
                                            {{ number_format($transaction->running_balance, 2) }}
                                        </span>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="text-center">{{ __('No Data Available') }}</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if ($accountTransactions->hasPages())
                    <div class="d-flex justify-content-center">
                        {{ $accountTransactions->links() }}
                    </div>
                @endif

                @if ($selectedAccount)
                    <div class="row mt-3">
                        <div class="col-12">
                            <div class="alert alert-success">
                                <strong>{{ __('Closing Balance') }}:</strong> {{ number_format($closingBalance, 2) }}
                            </div>
                        </div>
                    </div>
                @endif

                <!-- Cost Center Summary -->
                @if ($costCenterSummary->count() > 0)
                    <div class="row mt-3">
                        <div class="col-12">
                            <h4>{{ __('Cost Center Summary') }}</h4>
                            <div class="table-responsive">
                                <table class="table table-bordered table-striped">
                                    <thead>
                                        <tr>
                                            <th>{{ __('Cost Center') }}</th>
                                            <th class="text-end">{{ __('Debit') }}</th>
                                            <th class="text-end">{{ __('Credit') }}</th>
                                            <th class="text-end">{{ __('Net Amount') }}</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($costCenterSummary as $summary)
                                            <tr>
                                                <td>{{ $summary->cost_center_name ?? __('No Cost Center') }}</td>
                                                <td class="text-end">{{ number_format($summary->total_debit, 2) }}</td>
                                                <td class="text-end">{{ number_format($summary->total_credit, 2) }}</td>
                                                <td class="text-end">
                                                    <span
                                                        class="{{ $summary->net_amount < 0 ? 'text-success' : 'text-danger' }}">
                                                        {{ number_format($summary->net_amount, 2) }}
                                                    </span>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>
@endsection
