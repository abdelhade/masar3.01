@extends('admin.dashboard')
@section('content')
    @include('components.breadcrumb', [
        'title' => __('Account Movement'),
        'items' => [['label' => __('الرئيسيه'), 'url' => route('admin.dashboard')], ['label' => __('كميه الاصناف ')]],
    ])

    <div class="container mx-auto px-4">
        <h1 class="text-2xl font-bold mb-6">مراقبة كميات الأصناف</h1>

        <table class="min-w-full bg-white border">
            <thead>
                <tr class="bg-gray-100">
                    <th class="py-2 px-4 border">الكود</th>
                    <th class="py-2 px-4 border">الاسم</th>
                    <th class="py-2 px-4 border">الكمية الحالية</th>
                    <th class="py-2 px-4 border">الحد الأدنى</th>
                    <th class="py-2 px-4 border">الحد الأقصى</th>
                    <th class="py-2 px-4 border">الحالة</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($items as $item)
                    <tr
                        class="@if ($item['status'] == 'below_min') bg-red-50 @elseif($item['status'] == 'above_max') bg-blue-50 @endif hover:bg-gray-50">
                        <td class="py-2 px-4 border">{{ $item['code'] }}</td>
                        <td class="py-2 px-4 border">{{ $item['name'] }}</td>
                        <td class="py-2 px-4 border text-center">{{ number_format($item['current_quantity'], 2) }}</td>
                        <td class="py-2 px-4 border text-center">{{ $item['min_order_quantity'] }}</td>
                        <td class="py-2 px-4 border text-center">{{ $item['max_order_quantity'] }}</td>
                        <td class="py-2 px-4 border">
                            @if ($item['status'] == 'below_min')
                                <span class="text-danger font-bold">▼ أقل من الحد الأدنى</span>
                            @elseif($item['status'] == 'above_max')
                                <span class="text-blue font-bold">▲ أعلى من الحد الأقصى</span>
                            @else
                                <span class="text-green-600">● ضمن الحدود</span>
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@endsection
