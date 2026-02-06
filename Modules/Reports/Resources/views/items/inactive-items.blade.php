@extends('admin.dashboard')

@section('sidebar')
    @include('components.sidebar.reports')
@endsection

@section('content')
    @include('components.breadcrumb', [
        'title' => __('Inactive Items Report'),
        'items' => [
            ['label' => __('Home'), 'url' => route('admin.dashboard')],
            ['label' => __('Reports'), 'url' => route('reports.index')],
            ['label' => __('Inactive Items Report')],
        ],
    ])

    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <x-table-export-actions table-id="inactive-items-table" filename="inactive-items"
                    excel-label="{{ __('Export Excel') }}" pdf-label="{{ __('Export PDF') }}"
                    print-label="{{ __('Print') }}" />

                <table id="inactive-items-table" class="table table-striped table-bordered text-center">
                    <thead class="table-light">
                        <tr>
                            <th>#</th>
                            <th>{{ __('Code') }}</th>
                            <th>{{ __('Name') }}</th>
                            <th>{{ __('Description') }}</th>
                            <th>{{ __('Branch') }}</th>
                            <th class="text-end">{{ __('Min Order Quantity') }}</th>
                            <th class="text-end">{{ __('Max Order Quantity') }}</th>
                            <th class="text-end">{{ __('Average Cost') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($items as $item)
                            <tr class="table-warning">
                                <td>{{ $loop->iteration }}</td>
                                <td>{{ $item->code }}</td>
                                <td>{{ $item->name }}</td>
                                <td>{{ $item->info }}</td>
                                <td>{{ $item->branch }}</td>
                                <td class="text-end">{{ $item->min_order_quantity ?? 0 }}</td>
                                <td class="text-end">{{ $item->max_order_quantity ?? 0 }}</td>
                                <td class="text-end">
                                    <span class="fw-bold text-primary">
                                        {{ number_format($item->average_cost ?? 0, 2) }}
                                    </span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center">
                                    <div class="alert alert-info mb-0">{{ __('No Inactive Items') }}</div>
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
