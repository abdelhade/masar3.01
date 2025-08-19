@extends('admin.dashboard')
@section('content')
    @include('components.breadcrumb', [
        'title' => __('الشحنات'),
        'items' => [['label' => __('الرئيسية'), 'url' => route('admin.dashboard')], ['label' => __('الشحنات')]],
    ])
    <div class="row">
        <div class="col-lg-12">
            {{-- @can('إضافة الشحنات') --}}
            <a href="{{ route('shipments.create') }}" type="button" class="btn btn-primary font-family-cairo fw-bold">
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
                                    <th>{{ __('رقم التتبع') }}</th>
                                    <th>{{ __('شركة الشحن') }}</th>
                                    <th>{{ __('اسم العميل') }}</th>
                                    <th>{{ __('العنوان') }}</th>
                                    <th>{{ __('الوزن') }}</th>
                                    <th>{{ __('الحالة') }}</th>
                                    {{-- @canany(['تعديل الشحنات', 'حذف الشحنات']) --}}
                                    <th>{{ __('العمليات') }}</th>
                                    {{-- @endcanany --}}
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($shipments as $shipment)
                                    <tr class="text-center">
                                        <td>{{ $loop->iteration }}</td>
                                        <td>{{ $shipment->tracking_number }}</td>
                                        <td>{{ $shipment->shippingCompany->name }}</td>
                                        <td>{{ $shipment->customer_name }}</td>
                                        <td>{{ $shipment->customer_address }}</td>
                                        <td>{{ $shipment->weight }} {{ __('كجم') }}</td>
                                        <td>
                                            @if ($shipment->status == 'pending')
                                                <span class="badge bg-warning">{{ __('معلق') }}</span>
                                            @elseif ($shipment->status == 'in_transit')
                                                <span class="badge bg-primary">{{ __('في الطريق') }}</span>
                                            @else
                                                <span class="badge bg-success">{{ __('تم التسليم') }}</span>
                                            @endif
                                        </td>
                                        {{-- @canany(['تعديل الشحنات', 'حذف الشحنات']) --}}
                                        <td>
                                            {{-- @can('تعديل الشحنات') --}}
                                            <a class="btn btn-success btn-icon-square-sm"
                                                href="{{ route('shipments.edit', $shipment) }}">
                                                <i class="las la-edit"></i>
                                            </a>
                                            {{-- @endcan
                                                @can('حذف الشحنات') --}}
                                            <form action="{{ route('shipments.destroy', $shipment) }}"
                                                method="POST" style="display:inline-block;"
                                                onsubmit="return confirm('هل أنت متأكد من حذف هذه الشحنة؟');">
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
    {{ $shipments->links() }}
@endsection
