@extends('admin.dashboard')
@section('content')
    @include('components.breadcrumb', [
        'title' => __('الادوار'),
        'items' => [['label' => __('الرئيسيه'), 'url' => route('admin.dashboard')], ['label' => __('الادوار')]],
    ])
    <div class="row">
        <div class="col-lg-12">
            @can('إنشاء - الأدوار')
            <a href="{{ route('roles.create') }}" type="button" class="btn btn-primary font-family-cairo fw-bold">
                اضافه جديده
                <i class="fas fa-plus me-2"></i>
            </a>                
            @endcan

            <br>
            <br>
            <div class="card">
                <div class="card-body">
                    <div class="table-responsive" style="overflow-x: auto;">
                        <table class="table table-striped mb-0" style="min-width: 1200px;">
                            <thead class="table-light text-center align-middle">
                                <tr>
                                    <th>#</th>
                                    <th>{{ __('الاسم') }}</th>
                                    <th>{{ __('عدد الصلاحيات') }}</th>
                                    @can('عرض - الأدوار')
                                    <th>{{ __('العمليات') }}</th>
                                   @endcan
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($roles as $role)
                                    <tr class="text-center">
                                        <td> {{ $loop->iteration }} </td>
                                        <td>{{ $role->name }}</td>
                                        <td>{{ $role->permissions_count }}</td>

                                    @can('عرض - الأدوار')
                                        <td>
                                            @can('تعديل - الأدوار')
                                            <a class="btn btn-success btn-icon-square-sm"
                                                href="{{ route('roles.edit', $role->id) }}">
                                                <i class="las la-edit"></i>
                                            </a>                                                
                                            @endcan

                                            @can('حذف - الأدوار')
                                            <form action="{{ route('roles.destroy', $role->id) }}" method="POST"
                                                style="display:inline-block;"
                                                onsubmit="return confirm('هل أنت متأكد من حذف هذا التخصص؟');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-danger btn-icon-square-sm">
                                                    <i class="las la-trash"></i>
                                                </button>
                                            </form>                                                
                                            @endcan

                                        </td>
                                        @endcan
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="13" class="text-center">
                                            <div class="alert alert-info py-3 mb-0"
                                                style="font-size: 1.2rem; font-weight: 500;">
                                                <i class="las la-info-circle me-2"></i>
                                                لا توجد بيانات مضافة حتى الآن
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
