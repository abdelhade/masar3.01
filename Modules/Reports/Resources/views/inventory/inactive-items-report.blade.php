@extends('admin.dashboard')

@section('sidebar')
    @include('components.sidebar.reports')
@endsection

@section('content')
    <div class="container">
        <div class="card">
            <div class="card-header">
                <h2>{{ __('Inactive Items Report') }}</h2>
                <div class="alert alert-info mb-3">
                    <i class="fas fa-info-circle"></i>
                    {{ __('Total Items') }}: <strong>{{ $items->total() }}</strong>
                </div>
            </div>

            <div class="card-body">
                {{-- Filter Form --}}
                <form method="GET" class="row g-3 align-items-end mb-3"
                    style="font-family: 'Cairo', sans-serif; direction: rtl;">

                    <div class="col-md-4">
                        <label for="search" class="form-label">{{ __('Search by Item Name or Code') }}</label>
                        <input type="text" name="search" id="search" class="form-control"
                            placeholder="{{ __('Search by Item Name or Code') }}" value="{{ request('search') }}">
                    </div>

                    <div class="col-md-4">
                        <label for="warehouse_id" class="form-label">{{ __('Warehouse') }}</label>
                        <select name="warehouse_id" id="warehouse_id" class="form-select">
                            <option value="">{{ __('All Warehouses') }}</option>
                            @foreach ($warehouses as $warehouse)
                                <option value="{{ $warehouse->id }}"
                                    {{ request('warehouse_id') == $warehouse->id ? 'selected' : '' }}>
                                    {{ $warehouse->aname }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-4 text-end mt-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-filter"></i> {{ __('Filter') }}
                        </button>
                        <a href="{{ route('reports.inactive-items') }}" class="btn btn-secondary">
                            <i class="fas fa-redo"></i> {{ __('Reset') }}
                        </a>
                    </div>
                </form>

                {{-- Items Table --}}
                <div class="table-responsive">
                    <table class="table table-bordered table-hover">
                        <thead>
                            <tr>
                                <th>{{ __('Item Code') }}</th>
                                <th>{{ __('Item Name') }}</th>
                                <th>{{ __('Category') }}</th>
                                <th>{{ __('Cost') }}</th>
                                <th>{{ __('Price') }}</th>
                                <th>{{ __('Updated Date') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($items as $item)
                                <tr>
                                    <td>{{ $item->code ?? '---' }}</td>
                                    <td>{{ $item->name }}</td>
                                    <td>{{ $item->category ?? '---' }}</td>
                                    <td>{{ number_format($item->cost ?? 0, 2) }}</td>
                                    <td>{{ number_format($item->price ?? 0, 2) }}</td>
                                    <td>{{ $item->updated_at->format('Y-m-d H:i') }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center py-4">
                                        <i class="fas fa-inbox text-muted fs-1 mb-3 d-block"></i>
                                        {{ __('No Inactive Items') }}
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                {{-- Pagination Links --}}
                @if ($items->hasPages())
                    <div class="d-flex justify-content-center mt-4">
                        {{ $items->appends(request()->query())->links() }}
                    </div>
                @endif
            </div>
        </div>
    </div>
@endsection
