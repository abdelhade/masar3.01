@extends('admin.dashboard')
@section('content')
    @include('components.breadcrumb', [
        'title' => __('الادوار'),
        'items' => [['label' => __('الرئيسيه'), 'url' => route('admin.dashboard')], ['label' => __('الادوار')]],
    ])
    <div class="row">
        <div class="col-lg-12">
            @can('إضافة الادوار')
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
                                    @canany(['تعديل الادوار', 'حذف الادوار'])
                                        <th>{{ __('العمليات') }}</th>
                                    @endcanany
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($roles as $role)
                                    <tr class="text-center">
                                        <td> {{ $loop->iteration }} </td>
                                        <td>{{ $role->name }}</td>
                                        <td>{{ $role->permissions_count }}</td>

                                        @canany(['تعديل الادوار', 'حذف الادوار'])
                                            <td>
                                                @can('تعديل الادوار')
                                                    <a class="btn btn-success btn-icon-square-sm"
                                                        href="{{ route('roles.edit', $role->id) }}">
                                                        <i class="las la-edit"></i>
                                                    </a>
                                                @endcan

                                                @can('حذف الادوار')
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
                                        @endcanany
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
