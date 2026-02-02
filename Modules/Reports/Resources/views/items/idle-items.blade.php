@extends('admin.dashboard')

@section('sidebar')
    @include('components.sidebar.reports')
@endsection

@section('content')
    @include('components.breadcrumb', [
        'title' => __('reports.idle_items_report'),
        'items' => [
            ['label' => __('general.home'), 'url' => route('admin.dashboard')],
            ['label' => __('reports.idle_items_report')],
        ],
    ])

    <div class="card mb-3">
        <div class="card-body">
            <form action="{{ route('reports.items.idle') }}" method="GET" class="row g-3 align-items-end">
                <div class="col-auto">
                    <label for="days" class="form-label">{{ __('reports.idle_days_label') }}</label>
                    <input type="number" name="days" id="days" class="form-control form-control-sm" value="{{ $days }}" min="1" max="365" style="width: 100px;">
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
                <x-table-export-actions table-id="idle-items-table" filename="idle-items" excel-label="{{ __('general.export_excel') }}"
                    pdf-label="{{ __('general.export_pdf') }}" print-label="{{ __('general.print') }}" />

                <table id="idle-items-table" class="table table-striped table-bordered text-center">
                    <thead class="table-light">
                        <tr>
                            <th>#</th>
                            <th>{{ __('items.code') }}</th>
                            <th>{{ __('items.name') }}</th>
                            <th>{{ __('reports.current_balance') }}</th>
                            <th>{{ __('reports.balance_value') }}</th>
                            <th>{{ __('items.average_cost') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($items as $item)
                            @php $b = $balances->get($item->id); @endphp
                            <tr>
                                <td>{{ $loop->iteration + ($items->currentPage() - 1) * $items->perPage() }}</td>
                                <td>{{ $item->code }}</td>
                                <td>{{ $item->name }}</td>
                                <td>{{ $b ? number_format((float) $b->balance, 2) : '—' }}</td>
                                <td>{{ $b ? number_format((float) $b->balance_value, 2) : '—' }}</td>
                                <td>{{ number_format($item->average_cost ?? 0, 2) }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center">
                                    <div class="alert alert-info mb-0">{{ __('reports.no_idle_items') }}</div>
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
