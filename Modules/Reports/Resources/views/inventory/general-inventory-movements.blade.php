@extends('admin.dashboard')

@section('sidebar')
    @include('components.sidebar.reports')
@endsection

@section('content')
    <div class="container">
        <div class="card">
            <div class="card-head">
                <h2>{{ __('Item Movement') }}</h2>
            </div>
            <div class="card-body">
                <form method="GET" action="{{ request()->url() }}">
                    <div class="row mb-3">
                        <div class="col-md-3">
                            <label for="item_id">{{ __('Item') }}:</label>
                            <select id="item_id" name="item_id" class="form-control">
                                <option value="">{{ __('Select Item') }}</option>
                                @foreach ($items as $item)
                                    <option value="{{ $item->id }}"
                                        {{ request('item_id') == $item->id ? 'selected' : '' }}>
                                        {{ $item->code }} - {{ $item->aname }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label for="warehouse_id">{{ __('Warehouse') }}:</label>
                            <select id="warehouse_id" name="warehouse_id" class="form-control">
                                <option value="all" {{ request('warehouse_id', 'all') == 'all' ? 'selected' : '' }}>
                                    {{ __('All Warehouses') }}
                                </option>
                                @foreach ($warehouses as $warehouse)
                                    <option value="{{ $warehouse->id }}"
                                        {{ request('warehouse_id') == $warehouse->id ? 'selected' : '' }}>
                                        {{ $warehouse->aname }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label for="from_date">{{ __('From Date') }}:</label>
                            <input type="date" id="from_date" name="from_date" class="form-control"
                                value="{{ request('from_date') }}">
                        </div>
                        <div class="col-md-2">
                            <label for="to_date">{{ __('To Date') }}:</label>
                            <input type="date" id="to_date" name="to_date" class="form-control"
                                value="{{ request('to_date') }}">
                        </div>
                        <div class="col-md-2">
                            <label>&nbsp;</label>
                            <button type="submit" class="btn btn-primary form-control">
                                {{ __('Generate Report') }}
                            </button>
                        </div>
                    </div>
                </form>

                @if ($selectedItem)
                    <div class="row mb-3">
                        <div class="col-12">
                            <div class="alert alert-info">
                                <strong>{{ __('Selected Item') }}:</strong> {{ $selectedItem->code }} -
                                {{ $selectedItem->aname }}
                                <br>
                                <strong>{{ __('Unit') }}:</strong> {{ $selectedItem->units->first()->aname ?? '---' }}
                                <br>
                                <strong>{{ __('Current Balance') }}:</strong> {{ number_format($currentBalance, 2) }}
                            </div>
                        </div>
                    </div>
                @endif

                <div class="table-responsive">
                    <table class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th>{{ __('Date') }}</th>
                                <th>{{ __('Operation Type') }}</th>
                                <th>{{ __('Operation Number') }}</th>
                                <th>{{ __('Warehouse') }}</th>
                                <th class="text-end">{{ __('Inbound Quantity') }}</th>
                                <th class="text-end">{{ __('Outbound Quantity') }}</th>
                                <th class="text-end">{{ __('Balance') }}</th>
                                <th>{{ __('Description') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php
                                use App\Enums\OperationTypeEnum;
                                use Modules\Accounts\Models\AccHead;
                            @endphp
                            @forelse($movements as $movement)
                                @php
                                    $operationType = $movement->pro_tybe
                                        ? OperationTypeEnum::tryFrom($movement->pro_tybe)
                                        : null;
                                    $operationTypeText = $operationType ? $operationType->getArabicName() : '---';
                                    $warehouse = $movement->detail_store
                                        ? AccHead::find($movement->detail_store)
                                        : null;
                                    $warehouseName = $warehouse ? $warehouse->aname : '---';
                                @endphp
                                <tr>
                                    <td>{{ $movement->created_at ? \Carbon\Carbon::parse($movement->created_at)->format('Y-m-d') : '---' }}
                                    </td>
                                    <td>
                                        <span class="badge bg-{{ $movement->qty_in > 0 ? 'success' : 'danger' }}">
                                            {{ $operationTypeText }}
                                        </span>
                                    </td>
                                    <td>{{ $movement->pro_id ?? '---' }}</td>
                                    <td>{{ $warehouseName }}</td>
                                    <td class="text-end">
                                        @if ($movement->qty_in > 0)
                                            <span
                                                class="text-success fw-bold">{{ number_format($movement->qty_in, 2) }}</span>
                                        @else
                                            <span class="text-muted">---</span>
                                        @endif
                                    </td>
                                    <td class="text-end">
                                        @if ($movement->qty_out > 0)
                                            <span
                                                class="text-danger fw-bold">{{ number_format($movement->qty_out, 2) }}</span>
                                        @else
                                            <span class="text-muted">---</span>
                                        @endif
                                    </td>
                                    <td class="text-end">
                                        <span
                                            class="{{ isset($movement->running_balance) && $movement->running_balance < 0 ? 'text-warning' : 'text-primary' }} fw-bold">
                                            {{ isset($movement->running_balance) ? number_format($movement->running_balance, 2) : '---' }}
                                        </span>
                                    </td>
                                    <td>{{ $movement->notes ?? '---' }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="text-center">{{ __('No Data Available') }}</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if ($movements->hasPages())
                    <div class="d-flex justify-content-center">
                        {{ $movements->links() }}
                    </div>
                @endif

                @if ($selectedItem)
                    <div class="row mt-3">
                        <div class="col-md-3">
                            <div class="alert alert-success">
                                <strong>{{ __('Total Inbound') }}:</strong> {{ number_format($totalIn, 2) }}
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="alert alert-danger">
                                <strong>{{ __('Total Outbound') }}:</strong> {{ number_format($totalOut, 2) }}
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="alert alert-info">
                                <strong>{{ __('Net Movement') }}:</strong> {{ number_format($netMovement, 2) }}
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="alert alert-warning">
                                <strong>{{ __('Total Operations') }}:</strong> {{ $totalOperations }}
                            </div>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>
@endsection
