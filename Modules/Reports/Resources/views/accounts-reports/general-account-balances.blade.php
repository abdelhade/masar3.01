@extends('admin.dashboard')

{{-- Dynamic Sidebar --}}
@section('sidebar')
    @include('components.sidebar.reports')
@endsection

@section('content')
    <div class="container">
        <div class="card">
            <div class="card-head">
                <h2>{{ __('Trial Balance') }}</h2>
                <div class="text-muted">{{ __('As of Date:') }}
                    {{ $asOfDate ? \Carbon\Carbon::parse($asOfDate)->format('Y-m-d') : now()->format('Y-m-d') }}</div>
            </div>
            <div class="card-body">
                <div class="row mb-3">
                    <div class="col-md-3">
                        <label for="as_of_date">{{ __('Until Date:') }}</label>
                        <input type="date" id="as_of_date" class="form-control" wire:model="asOfDate">
                    </div>
                    <div class="col-md-3">
                        <label for="account_group">{{ __('Account Group:') }}</label>
                        <select id="account_group" class="form-control" wire:model="accountGroup">
                            <option value="">{{ __('All') }}</option>
                            <option value="1">{{ __('Assets') }}</option>
                            <option value="2">{{ __('Liabilities') }}</option>
                            <option value="3">{{ __('Equity') }}</option>
                            <option value="4">{{ __('Revenue') }}</option>
                            <option value="5">{{ __('Expenses') }}</option>
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
                                <th>{{ __('Account Number') }}</th>
                                <th>{{ __('Account Name') }}</th>
                                <th class="text-end">{{ __('Debit') }}</th>
                                <th class="text-end">{{ __('Credit') }}</th>
                                <th class="text-end">{{ __('Balance') }}</th>
                                <th>{{ __('Balance Type') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($accountBalances as $balance)
                                <tr>
                                    <td>{{ $balance->code }}</td>
                                    <td>{{ $balance->aname }}</td>
                                    <td class="text-end">
                                        {{ $balance->debit > 0 ? number_format($balance->debit, 2) : '---' }}</td>
                                    <td class="text-end">
                                        {{ $balance->credit > 0 ? number_format($balance->credit, 2) : '---' }}</td>
                                    <td class="text-end">{{ number_format($balance->balance, 2) }}</td>
                                    <td>
                                        @if ($balance->balance > 0)
                                            <span class="badge bg-primary">{{ __('Debit') }}</span>
                                        @elseif($balance->balance < 0)
                                            <span class="badge bg-success">{{ __('Credit') }}</span>
                                        @else
                                            <span class="badge bg-secondary">{{ __('Zero') }}</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center">{{ __('No data available.') }}</td>
                                </tr>
                            @endforelse
                        </tbody>
                        <tfoot>
                            <tr class="table-primary">
                                <th colspan="2">{{ __('Total') }}</th>
                                <th class="text-end">{{ number_format($totalDebit, 2) }}</th>
                                <th class="text-end">{{ number_format($totalCredit, 2) }}</th>
                                <th class="text-end">{{ number_format($totalBalance, 2) }}</th>
                                <th></th>
                            </tr>
                        </tfoot>
                    </table>
                </div>

                @if ($accountBalances->hasPages())
                    <div class="d-flex justify-content-center">
                        {{ $accountBalances->links() }}
                    </div>
                @endif

                <!-- ملخص -->
                <div class="row mt-3">
                    <div class="col-12">
                        <div class="alert {{ $totalDebit == $totalCredit ? 'alert-success' : 'alert-warning' }}">
                            <strong>{{ __('Result:') }}</strong>
                            @if ($totalDebit == $totalCredit)
                                {{ __('Trial balance is balanced') }} ✓
                            @else
                                {{ __('Trial balance is not balanced - Difference:') }}
                                {{ number_format(abs($totalDebit - $totalCredit), 2) }}
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
