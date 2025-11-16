@extends('admin.dashboard')

@section('sidebar')
    @include('components.sidebar.reports')
@endsection

@section('content')
    @include('components.breadcrumb', [
        'title' => __('تقرير كميات الأصناف حسب المخازن'),
        'items' => [
            ['label' => __('الرئيسية'), 'url' => route('admin.dashboard')],
            ['label' => __('تقرير كميات الأصناف حسب المخازن')],
        ],
    ])

    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered text-center">
                    <thead>
                        <tr>
                            <th>الصنف</th>
                            @foreach ($stores as $store)
                                <th>{{ $store->aname }}</th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($items as $item)
                            <tr>
                                <td>{{ $item->name }}</td>
                                @foreach ($stores as $store)
                                    <td>
                                        {{ optional($balances[$item->id] ?? collect())->firstWhere('detail_store', $store->id)->balance ?? 0 }}
                                    </td>
                                @endforeach
                            </tr>
                        @endforeach
                    </tbody>
                </table>
                <div class="mt-3">
                    {{ $items->links() }}
                </div>
            </div>
        </div>
    </div>
@endsection
