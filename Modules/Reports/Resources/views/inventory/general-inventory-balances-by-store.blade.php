@extends('admin.dashboard')

@section('sidebar')
    @include('components.sidebar.reports')
@endsection

@section('content')
    <div class="container">
        <div class="card">
            <div class="card-header">
                <h2 class="mb-0">{{ __('Items List With Balances - Specific Warehouse') }}</h2>
            </div>

            <div class="card-body">
                <form method="GET" action="{{ request()->url() }}" class="mb-4">
                    <div class="row g-3 align-items-end">
                        <div class="col-md-3">
                            <label for="warehouse_id" class="form-label">{{ __('Warehouse') }}</label>
                            <select name="warehouse_id" id="warehouse_id" class="form-select">
                                <option value="">{{ __('Select Warehouse') }}</option>
                                @foreach ($warehouses as $warehouse)
                                    <option value="{{ $warehouse->id }}"
                                        {{ request('warehouse_id') == $warehouse->id ? 'selected' : '' }}>
                                        {{ $warehouse->aname }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        @foreach ($notes as $note)
                            <div class="col-md-2">
                                <label for="note_{{ $note->id }}" class="form-label">
                                    {{ $note->name }}:
                                </label>
                                <select name="note_{{ $note->id }}" id="note_{{ $note->id }}" class="form-select">
                                    <option value="">{{ __('All') }}</option>
                                    @foreach ($note->noteDetails as $detail)
                                        <option value="{{ $detail->name }}"
                                            {{ request("note_{$note->id}") == $detail->name ? 'selected' : '' }}>
                                            {{ $detail->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        @endforeach

                        <div class="col-md-3">
                            <label for="search" class="form-label">{{ __('Search') }}</label>
                            <input type="text" name="search" id="search" class="form-control"
                                value="{{ request('search') }}" placeholder="{{ __('Search Item Or Code') }}">
                        </div>

                        <div class="col-md-2">
                            <button type="submit" class="btn btn-primary w-100 mt-4">
                                {{ __('Generate Report') }}
                            </button>
                        </div>
                    </div>
                </form>

                @if ($selectedWarehouse)
                    <div class="alert alert-info mb-4">
                        <strong>{{ __('Selected Warehouse') }}:</strong> {{ $selectedWarehouse->aname }}<br>
                        <strong>{{ __('Address') }}:</strong> {{ $selectedWarehouse->address ?? '—' }}
                    </div>
                @endif

                <div class="table-responsive">
                    <table class="table table-bordered table-striped table-hover">
                        <thead class="table-light">
                            <tr>
                                <th>{{ __('Item Code') }}</th>
                                <th>{{ __('Item Name') }}</th>
                                <th>{{ __('Unit') }}</th>
                                <th class="text-end">{{ __('Current Balance') }}</th>
                                <th class="text-end">{{ __('Minimum') }}</th>
                                <th class="text-end">{{ __('Maximum') }}</th>
                                <th class="text-end">{{ __('Value') }}</th>
                                <th>{{ __('Status') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($inventoryBalances as $balance)
                                <tr>
                                    <td>{{ $balance->code ?? '—' }}</td>
                                    <td>{{ $balance->name ?? '—' }}</td>
                                    <td>{{ $balance->units->first()?->name ?? '—' }}</td>
                                    <td class="text-end">{{ number_format($balance->current_balance ?? 0, 2) }}</td>
                                    <td class="text-end">{{ number_format($balance->min_balance ?? 0, 2) }}</td>
                                    <td class="text-end">{{ number_format($balance->max_balance ?? 0, 2) }}</td>
                                    <td class="text-end">{{ number_format($balance->value ?? 0, 2) }}</td>
                                    <td>
                                        @if ($balance->current_balance <= ($balance->min_balance ?? 0))
                                            <span class="badge bg-danger">{{ __('Low Stock') }}</span>
                                        @elseif($balance->current_balance >= ($balance->max_balance ?? 999999))
                                            <span class="badge bg-warning">{{ __('High Stock') }}</span>
                                        @else
                                            <span class="badge bg-success">{{ __('Normal') }}</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="text-center py-4">
                                        {{ __('No data available') }}
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                        <tfoot class="table-primary">
                            <tr>
                                <th colspan="3">{{ __('Total') }}</th>
                                <th class="text-end">{{ number_format($totalBalance ?? 0, 2) }}</th>
                                <th colspan="2"></th>
                                <th class="text-end">{{ number_format($totalValue ?? 0, 2) }}</th>
                                <th></th>
                            </tr>
                        </tfoot>
                    </table>
                </div>

                @if ($inventoryBalances->hasPages())
                    <div class="d-flex justify-content-center mt-4">
                        {{ $inventoryBalances->appends(request()->query())->links() }}
                    </div>
                @endif

                <!-- ملخص -->
                <div class="row mt-4 g-3">
                    <div class="col-md-3">
                        <div class="alert alert-info mb-0">
                            <strong>{{ __('Total Items') }}:</strong> {{ $totalItems ?? 0 }}
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="alert alert-danger mb-0">
                            <strong>{{ __('Low Stock Items') }}:</strong> {{ $lowStockItems ?? 0 }}
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="alert alert-warning mb-0">
                            <strong>{{ __('High Stock Items') }}:</strong> {{ $highStockItems ?? 0 }}
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="alert alert-success mb-0">
                            <strong>{{ __('Normal Stock Items') }}:</strong> {{ $normalStockItems ?? 0 }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
