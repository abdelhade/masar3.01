@extends('admin.dashboard')
@section('content')
    @include('components.breadcrumb', [
        'title' => __('السائقون'),
        'items' => [['label' => __('الرئيسية'), 'url' => route('admin.dashboard')], ['label' => __('السائقون')]],
    ])
    <div class="row">
        <div class="col-lg-12">
            {{-- @can('إضافة السين') --}}
            <a href="{{ route('drivers.create') }}" type="button" class="btn btn-primary font-family-cairo fw-bold">
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
                                    <th>{{ __('الاسم') }}</th>
                                    <th>{{ __('رقم الهاتف') }}</th>
                                    <th>{{ __('نوع المركبة') }}</th>
                                    <th>{{ __('الحالة') }}</th>
                                    {{-- @canany(['تعديل السين', 'حذف السين']) --}}
                                        <th>{{ __('العمليات') }}</th>
                                    {{-- @endcanany --}}
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($drivers as $driver)
                                    <tr class="text-center">
                                        <td>{{ $loop->iteration }}</td>
                                        <td>{{ $driver->name }}</td>
                                        <td>{{ $driver->phone }}</td>
                                        <td>{{ $driver->vehicle_type }}</td>
                                        <td>
                                            @if ($driver->is_available)
                                                <span class="badge bg-primary">{{ __('متاح') }}</span>
                                            @else
                                                <span class="badge bg-danger">{{ __('غير متاح') }}</span>
                                            @endif
                                        </td>
                                        {{-- @canany(['تعديل السين', 'حذف السين']) --}}
                                        <td>
                                            {{-- @can('تعديل السين') --}}
                                            <a class="btn btn-success btn-icon-square-sm"
                                                href="{{ route('drivers.edit', $driver) }}">
                                                <i class="las la-edit"></i>
                                            </a>
                                            {{-- @endcan
                                                @can('حذف السين') --}}
                                            <form action="{{ route('drivers.destroy', $driver) }}" method="POST"
                                                style="display:inline-block;"
                                                onsubmit="return confirm('هل أنت متأكد من حذف هذا السائق؟');">
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
                                        <td colspan="6" class="text-center">
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
    {{ $drivers->links() }}
@endsection
