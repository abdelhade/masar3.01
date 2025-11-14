@extends('admin.dashboard')

{{-- Dynamic Sidebar --}}
@section('sidebar')
    @include('components.sidebar.permissions')
@endsection
@section('content')
    @include('components.breadcrumb', [
        'title' => __('المدراء '),
        'items' => [['label' => __('الرئيسيه'), 'url' => route('admin.dashboard')], ['label' => __('المدراء')]],
    ])
    <div class="row">
        <div class="col-lg-12">
            @can('create Users')
                <a href="{{ route('users.create') }}" type="button" class="btn btn-primary font-family-cairo fw-bold">
             اضافه جديد
                    <i class="fas fa-plus me-2"></i>
                </a>
            @endcan
            <br>
            <br>
            <div class="card">
                <div class="card-body">
                    <div class="table-responsive" style="overflow-x: auto;">

                        <x-table-export-actions table-id="users-table" filename="users-table" excel-label="تصدير Excel"
                            pdf-label="تصدير PDF" print-label="طباعة" />

                        <table id="users-table" class="table table-striped mb-0" style="min-width: 1200px;">
                            <thead class="table-light text-center align-middle">
                                <tr>
                                    <th>#</th>
                                    <th>{{ __('الاسم') }}</th>
                                    <th>{{ __('البريد الالكتروني ') }}</th>
                                    <th>{{ __('الصلاحيات') }}</th>
                                    <th>{{ __('الفروع') }}</th>
                                    <th>{{ __('تم الانشاء في ') }}</th>
                                    @canany(['edit Users', 'delete Users'])
                                        <th>{{ __('العمليات') }}</th>
                                    @endcanany
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
                                            <span
                                                class="badge bg-primary">{{ $user->permissions->count() }}</span>
                                        </td>
                                        <td class="font-family-cairo fw-bold font-14 text-center">
                                            @foreach ($user->branches as $branch)
                                                <span class="badge bg-info text-dark">{{ $branch->name }}</span>
                                            @endforeach
                                        </td>
                                        <td>{{ $user->created_at->format('Y-m-d') }}</td>
                                        @canany(['edit Users', 'delete Users'])
                                            <td>
                                                @can('edit Users')
                                                    <a class="btn btn-success btn-icon-square-sm"
                                                        href="{{ route('users.edit', $user->id) }}">
                                                        <i class="las la-edit"></i>
                                                    </a>
                                                @endcan
                                                @can('delete Users')
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
                                        @endcanany
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
