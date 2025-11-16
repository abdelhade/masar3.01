@extends('admin.dashboard')

@section('sidebar')
    @include('components.sidebar.reports')
@endsection

@section('content')
    @include('components.breadcrumb', [
        'title' => __('تقرير الأصناف غير المفعلة'),
        'items' => [
            ['label' => __('الرئيسية'), 'url' => route('admin.dashboard')],
            ['label' => __('تقرير الأصناف غير المفعلة')],
        ],
    ])

    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <x-table-export-actions table-id="inactive-items-table" filename="inactive-items" excel-label="تصدير Excel"
                    pdf-label="تصدير PDF" print-label="طباعة" />

                <table id="inactive-items-table" class="table table-striped table-bordered text-center">
                    <thead class="table-light">
                        <tr>
                            <th>#</th>
                            <th>الكود</th>
                            <th>الاسم</th>
                            <th>الوصف</th>
                            <th>الفرع</th>
                            <th>الحد الأدنى للطلب</th>
                            <th>الحد الأقصى للطلب</th>
                            <th>متوسط التكلفة</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($items as $item)
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td>{{ $item->code }}</td>
                                <td>{{ $item->name }}</td>
                                <td>{{ $item->info }}</td>
                                <td>{{ $item->branch }}</td>
                                <td>{{ $item->min_order_quantity }}</td>
                                <td>{{ $item->max_order_quantity }}</td>
                                <td>{{ number_format($item->average_cost, 2) }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center">
                                    <div class="alert alert-info mb-0">لا توجد أصناف غير مفعلة</div>
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
