@extends('admin.dashboard')

@section('sidebar')
    @include('components.sidebar.reports')
@endsection

@section('content')
    @include('components.breadcrumb', [
        'title' => __('Most Expensive Items Report'),
        'items' => [
            ['label' => __('Home'), 'url' => route('admin.dashboard')],
            ['label' => __('Reports'), 'url' => route('reports.index')],
            ['label' => __('Most Expensive Items Report')],
        ],
    ])

    <div class="card mb-3">
        <div class="card-body">
            <form action="{{ route('reports.items.most-expensive') }}" method="GET" class="row g-3 align-items-end">
                <div class="col-auto">
                    <label for="limit" class="form-label">{{ __('Limit') }}:</label>
                    <select name="limit" id="limit" class="form-select form-select-sm" style="width: auto;">
                        <option value="25" {{ ($limit ?? 25) == 25 ? 'selected' : '' }}>25</option>
                        <option value="50" {{ ($limit ?? 25) == 50 ? 'selected' : '' }}>50</option>
                        <option value="100" {{ ($limit ?? 25) == 100 ? 'selected' : '' }}>100</option>
                        <option value="200" {{ ($limit ?? 25) == 200 ? 'selected' : '' }}>200</option>
                        <option value="500" {{ ($limit ?? 25) == 500 ? 'selected' : '' }}>500</option>
                    </select>
                </div>
                <div class="col-auto">
                    <button type="submit" class="btn btn-primary btn-sm">{{ __('Filter') }}</button>
                </div>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <x-table-export-actions table-id="most-expensive-items-table" filename="most-expensive-items"
                    excel-label="{{ __('Export Excel') }}" pdf-label="{{ __('Export PDF') }}"
                    print-label="{{ __('Print') }}" />

                <table id="most-expensive-items-table" class="table table-striped table-bordered">
                    <thead class="table-light">
                        <tr>
                            <th class="text-center">#</th>
                            <th>{{ __('Code') }}</th>
                            <th>{{ __('Name') }}</th>
                            <th class="text-end">{{ __('Average Cost') }}</th>
                            <th class="text-end">{{ __('Current Balance') }}</th>
                            <th class="text-end">{{ __('Balance Value') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($items as $item)
                            @php $b = $balances->get($item->id); @endphp
                            <tr class="table-danger">
                                <td class="text-center">
                                    {{ $loop->iteration + ($items->currentPage() - 1) * $items->perPage() }}</td>
                                <td>{{ $item->code }}</td>
                                <td>{{ $item->name }}</td>
                                <td class="text-end">
                                    <span class="fw-bold text-danger">
                                        {{ number_format($item->average_cost ?? 0, 2) }}
                                    </span>
                                </td>
                                <td class="text-end">
                                    <span class="fw-semibold {{ $b && $b->balance > 0 ? 'text-success' : 'text-muted' }}">
                                        {{ $b ? number_format((float) $b->balance, 2) : '—' }}
                                    </span>
                                </td>
                                <td class="text-end">
                                    <span class="fw-bold text-primary">
                                        {{ $b ? number_format((float) $b->balance_value, 2) : '—' }}
                                    </span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center">
                                    <div class="alert alert-info mb-0">{{ __('No Data Available') }}</div>
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
