@extends('admin.dashboard')
@section('content')
    @include('components.breadcrumb', [
        'title' => __('المدراء '),
        'items' => [['label' => __('الرئيسيه'), 'url' => route('admin.dashboard')], ['label' => __('المدراء')]],
    ])
    <div class="row">
        <div class="col-lg-12">
            @can('إنشاء - العملاء')
                <a href="{{ route('users.create') }}" type="button" class="btn btn-primary font-family-cairo fw-bold">
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
                                    <th class="font-family-cairo fw-bold font-14 text-center">#</th>
                                    <th class="font-family-cairo fw-bold font-14 text-center">{{ __('الاسم') }}</th>
                                    <th class="font-family-cairo fw-bold font-14 text-center">{{ __('البريد الالكتروني ') }}
                                    </th>
                                    <th class="font-family-cairo fw-bold font-14 text-center">{{ __('الادوار') }}</th>
                                    <th class="font-family-cairo fw-bold font-14 text-center">{{ __('تم الانشاء في ') }}
                                    </th>
                                    <th class="font-family-cairo fw-bold font-14 text-center">{{ __('العمليات') }}</th>
                                    <th>#</th>
                                    <th>{{ __('الاسم') }}</th>
                                    <th>{{ __('البريد الالكتروني ') }}</th>
                                    <th>{{ __('الصلاحيات') }}</th>
                                    <th>{{ __('تم الانشاء في ') }}</th>
                                    @can('عرض - تفاصيل دور')
                                        <th>{{ __('العمليات') }}</th>
                                    @endcan

                                </tr>
                            </thead>
                            <tbody>
                                @forelse($users as $user)
                                    <tr class="text-center">
                                        <td class="font-family-cairo fw-bold font-14 text-center"> {{ $loop->iteration }}
                                        </td>
                                        <td class="font-family-cairo fw-bold font-14 text-center">{{ $user->name }}</td>
                                        <td class="font-family-cairo fw-bold font-14 text-center">{{ $user->email }}</td>
                                        <td class="font-family-cairo fw-bold font-14 text-center">
                                            @foreach ($user->roles as $role)
                                                <span class="badge bg-primary">{{ $role->name }}</span>
                                            @endforeach
                                        </td>
                                        <td class="font-family-cairo fw-bold font-14 text-center">
                                            {{ $user->created_at->format('Y-m-d') }}</td>

                                        <td class="font-family-cairo fw-bold font-14 text-center">

                                        <td> {{ $loop->iteration }} </td>
                                        <td>{{ $user->name }}</td>
                                        <td>{{ $user->email }}</td>
                                        <td>{{ $user->permissions->count() }}</td>
                                        <td>{{ $user->created_at->format('Y-m-d') }}</td>
                                        <td>
                                            @can('تعديل - الأدوار')
                                                <a class="btn btn-success btn-icon-square-sm"
                                                    href="{{ route('users.edit', $user->id) }}">
                                                    <i class="las la-edit"></i>
                                                </a>
                                            @endcan
                                            @can('حذف - الأدوار')
                                                <form action="{{ route('users.destroy', $user->id) }}" method="POST"
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
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="13" class="text-center">
                                            <div class="alert alert-info py-3 mb-0"
                                                style="font-size: 1.2rem; font-weight: 500;">
                                                <i class="las la-info-circle me-2"></i>
                                                لا توجد بيانات
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
