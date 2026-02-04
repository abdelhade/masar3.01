@extends('admin.dashboard')

@section('sidebar')
    @include('components.sidebar.reports')
@endsection

@section('content')
    @include('components.breadcrumb', [
        'title' => __('Items Quantities by Stores Report'),
        'items' => [
            ['label' => __('Home'), 'url' => route('admin.dashboard')],
            ['label' => __('Reports'), 'url' => route('reports.index')],
            ['label' => __('Items Quantities by Stores Report')],
        ],
    ])

    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-striped text-center">
                    <thead class="table-light">
                        <tr>
                            <th>{{ __('Item') }}</th>
                            @foreach ($stores as $store)
                                <th class="text-nowrap">{{ $store->aname }}</th>
                            @endforeach
                            <th class="text-end"><strong>{{ __('Total') }}</strong></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($items as $item)
                            <tr>
                                <td class="text-start">{{ $item->name }}</td>
                                @php $rowTotal = 0; @endphp
                                @foreach ($stores as $store)
                                    @php
                                        $storeBalance =
                                            optional($balances[$item->id] ?? collect())->firstWhere(
                                                'detail_store',
                                                $store->id,
                                            )->balance ?? 0;
                                        $rowTotal += $storeBalance;
                                    @endphp
                                    <td class="text-end">
                                        <span class="{{ $storeBalance > 0 ? 'text-success' : 'text-muted' }} fw-semibold">
                                            {{ number_format($storeBalance, 3) }}
                                        </span>
                                    </td>
                                @endforeach
                                <td class="text-end">
                                    <span class="fw-bold text-primary">
                                        {{ number_format($rowTotal, 3) }}
                                    </span>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>

                @if (isset($items))
                    <div class="mt-3">
                        {{ $items->links() }}
                    </div>
                @endif
            </div>
        </div>
    </div>
@endsection
