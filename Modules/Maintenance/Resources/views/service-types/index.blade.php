@extends('admin.dashboard')

@section('content')
    @include('components.breadcrumb', [
        'title' => __('أنواع الصيانة'),
        'items' => [
            ['label' => __('الرئيسية'), 'url' => route('admin.dashboard')],
            ['label' => __('أنواع الصيانة')],
        ],
    ])

    <div class="row">
        <div class="col-lg-12">
            {{-- @can('إضافة نوع صيانة') --}}
            <a href="{{ route('service.types.create') }}" type="button" class="btn btn-primary font-family-cairo fw-bold">
                {{ __('إضافة جديد') }}
                <i class="fas fa-plus me-2"></i>
            </a>
            {{-- @endcan --}}
            <br><br>

            <div class="card">
                <div class="card-body">
                    <div class="table-responsive" style="overflow-x: auto;">

                        <x-table-export-actions table-id="service-types-table" filename="service-types"
                            excel-label="{{ __('تصدير Excel') }}" pdf-label="{{ __('تصدير PDF') }}"
                            print-label="{{ __('طباعة') }}" />

                        <table id="service-types-table" class="table table-striped mb-0" style="min-width: 1200px;">
                            <thead class="table-light text-center align-middle">
                                <tr>
                                    <th>#</th>
                                    <th>{{ __('الاسم') }}</th>
                                    <th>{{ __('الوصف') }}</th>
                                    {{-- @canany(['تعديل نوع صيانة', 'حذف نوع صيانة']) --}}
                                    <th>{{ __('العمليات') }}</th>
                                    {{-- @endcanany --}}
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($types as $type)
                                    <tr class="text-center">
                                        <td>{{ $loop->iteration }}</td>
                                        <td>{{ $type->name }}</td>
                                        <td>{{ $type->description }}</td>

                                        {{-- @canany(['تعديل نوع صيانة', 'حذف نوع صيانة']) --}}
                                        <td>
                                            {{-- @can('تعديل نوع صيانة') --}}
                                            <a class="btn btn-success btn-icon-square-sm"
                                                href="{{ route('service.types.edit', $type->id) }}">
                                                <i class="las la-edit"></i>
                                            </a>
                                            {{-- @endcan --}}

                                            {{-- @can('حذف نوع صيانة') --}}
                                            <form action="{{ route('service.types.destroy', $type->id) }}" method="POST"
                                                style="display:inline-block;"
                                                onsubmit="return confirm('هل أنت متأكد من حذف نوع الصيانة هذا؟');">
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
                                        <td colspan="13" class="text-center">
                                            <div class="alert alert-info py-3 mb-0"
                                                style="font-size: 1.2rem; font-weight: 500;">
                                                <i class="las la-info-circle me-2"></i>
                                                {{ __('لا توجد بيانات') }}
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
@endsection
