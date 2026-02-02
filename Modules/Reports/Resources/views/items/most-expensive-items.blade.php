@extends('admin.dashboard')

@section('sidebar')
    @include('components.sidebar.reports')
@endsection

@section('content')
    @include('components.breadcrumb', [
        'title' => __('reports.most_expensive_items_report'),
        'items' => [
            ['label' => __('general.home'), 'url' => route('admin.dashboard')],
            ['label' => __('reports.most_expensive_items_report')],
        ],
    ])

    <div class="card mb-3">
        <div class="card-body">
            <form action="{{ route('reports.items.most-expensive') }}" method="GET" class="row g-3 align-items-end">
                <div class="col-auto">
                    <label for="limit" class="form-label">{{ __('reports.most_expensive_limit_label') }}</label>
                    <select name="limit" id="limit" class="form-select form-select-sm" style="width: auto;">
                        <option value="25" {{ $limit == 25 ? 'selected' : '' }}>25</option>
                        <option value="50" {{ $limit == 50 ? 'selected' : '' }}>50</option>
                        <option value="100" {{ $limit == 100 ? 'selected' : '' }}>100</option>
                        <option value="200" {{ $limit == 200 ? 'selected' : '' }}>200</option>
                        <option value="500" {{ $limit == 500 ? 'selected' : '' }}>500</option>
                    </select>
                </div>
                <div class="col-auto">
                    <button type="submit" class="btn btn-primary btn-sm">{{ __('general.filter') }}</button>
                </div>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <x-table-export-actions table-id="most-expensive-items-table" filename="most-expensive-items" excel-label="{{ __('general.export_excel') }}"
                    pdf-label="{{ __('general.export_pdf') }}" print-label="{{ __('general.print') }}" />

                <table id="most-expensive-items-table" class="table table-striped table-bordered text-center">
                    <thead class="table-light">
                        <tr>
                            <th>#</th>
                            <th>{{ __('items.code') }}</th>
                            <th>{{ __('items.name') }}</th>
                            <th>{{ __('items.average_cost') }}</th>
                            <th>{{ __('reports.current_balance') }}</th>
                            <th>{{ __('reports.balance_value') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($items as $item)
                            @php $b = $balances->get($item->id); @endphp
                            <tr>
                                <td>{{ $loop->iteration + ($items->currentPage() - 1) * $items->perPage() }}</td>
                                <td>{{ $item->code }}</td>
                                <td>{{ $item->name }}</td>
                                <td>{{ number_format($item->average_cost ?? 0, 2) }}</td>
                                <td>{{ $b ? number_format((float) $b->balance, 2) : '—' }}</td>
                                <td>{{ $b ? number_format((float) $b->balance_value, 2) : '—' }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center">
                                    <div class="alert alert-info mb-0">{{ __('reports.no_data_available') }}</div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>

                <div class="mt-3">
                    {{ $items->links() }}
                </div>
            </div>
        </div>
    </div>
@endsection
