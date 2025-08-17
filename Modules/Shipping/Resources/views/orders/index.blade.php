@extends('admin.dashboard')
@section('content')
    @include('components.breadcrumb', [
        'title' => __('الطلبات'),
        'items' => [['label' => __('الرئيسية'), 'url' => route('admin.dashboard')], ['label' => __('الطلبات')]],
    ])
    <div class="row">
        <div class="col-lg-12">
            {{-- @can('إضافة الطلبات') --}}
            <a href="{{ route('orders.create') }}" type="button" class="btn btn-primary font-family-cairo fw-bold">
                إضافة جديدة
                <i class="fas fa-plus me-2"></i>
            </a>
            {{-- @endcan --}}
            <br><br>
            <div class="card">
                <div class="card-body">
                    <div class="table-responsive" style="overflow-x: auto;">
                        <table class="table table-striped mb-0" style="min-width: 1200px;">
                            <thead class="table-light text-center align-middle">
                                <tr>
                                    <th>#</th>
                                    <th>{{ __('رقم الطلب') }}</th>
                                    <th>{{ __('السائق') }}</th>
                                    <th>{{ __('رقم الشحنة') }}</th>
                                    <th>{{ __('اسم العميل') }}</th>
                                    <th>{{ __('العنوان') }}</th>
                                    <th>{{ __('حالة التوصيل') }}</th>
                                    {{-- @canany(['تعديل الطلبات', 'حذف الطلبات']) --}}
                                    <th>{{ __('العمليات') }}</th>
                                    {{-- @endcanany --}}
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($orders as $order)
                                    <tr class="text-center">
                                        <td>{{ $loop->iteration }}</td>
                                        <td>{{ $order->order_number }}</td>
                                        <td>{{ $order->driver->name }}</td>
                                        <td>{{ $order->shipment->tracking_number }}</td>
                                        <td>{{ $order->customer_name }}</td>
                                        <td>{{ $order->customer_address }}</td>
                                        <td>
                                            @if ($order->delivery_status == 'pending')
                                                <span class="badge bg-warning">{{ __('معلق') }}</span>
                                            @elseif ($order->delivery_status == 'assigned')
                                                <span class="badge bg-info">{{ __('تم التعيين') }}</span>
                                            @elseif ($order->delivery_status == 'in_transit')
                                                <span class="badge bg-primary">{{ __('في الطريق') }}</span>
                                            @else
                                                <span class="badge bg-success">{{ __('تم التسليم') }}</span>
                                            @endif
                                        </td>
                                        {{-- @canany(['تعديل الطلبات', 'حذف الطلبات']) --}}
                                        <td>
                                            {{-- @can('تعديل الطلبات') --}}
                                            <a class="btn btn-success btn-icon-square-sm"
                                                href="{{ route('orders.edit', $order) }}">
                                                <i class="las la-edit"></i>
                                            </a>
                                            {{-- @endcan
                                                @can('حذف الطلبات') --}}
                                            <form action="{{ route('orders.destroy', $order) }}" method="POST"
                                                style="display:inline-block;"
                                                onsubmit="return confirm('هل أنت متأكد من حذف هذا الطلب؟');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-danger btn-icon-square-sm">
                                                    <i class="las la-trash"></i>
                                                </button>
                                            </form>
                                            {{-- @endcan --}}
                                        </td>
                                        {{-- @endcanany --}}
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="8" class="text-center">
                                            <div class="alert alert-info py-3 mb-0"
                                                style="font-size: 1.2rem; font-weight: 500;">
                                                <i class="las la-info-circle me-2"></i>
                                                {{ __('لا توجد بيانات مضافة حتى الآن') }}
                                            </div>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
    {{ $orders->links() }}
@endsection
