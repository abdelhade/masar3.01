@extends('admin.dashboard')
@section('content')
    @include('components.breadcrumb', [
        'title' => __('شركات الشحن'),
        'items' => [
            ['label' => __('الرئيسية'), 'url' => route('admin.dashboard')],
            ['label' => __('شركات الشحن')],
        ],
    ])
    <div class="row">
        <div class="col-lg-12">
            {{-- @can('إضافة شركات الشحن') --}}
            <a href="{{ route('companies.create') }}" type="button" class="btn btn-primary font-family-cairo fw-bold">
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
                                    <th>{{ __('البريد الإلكتروني') }}</th>
                                    <th>{{ __('الهاتف') }}</th>
                                    <th>{{ __('العنوان') }}</th>
                                    <th>{{ __('السعر الأساسي') }}</th>
                                    <th>{{ __('الحالة') }}</th>
                                    {{-- @canany(['تعديل شركات الشحن', 'حذف شركات الشحن']) --}}
                                    <th>{{ __('العمليات') }}</th>
                                    {{-- @endcanany --}}
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($companies as $company)
                                    <tr class="text-center">
                                        <td>{{ $loop->iteration }}</td>
                                        <td>{{ $company->name }}</td>
                                        <td>{{ $company->email }}</td>
                                        <td>{{ $company->phone }}</td>
                                        <td>{{ $company->address }}</td>
                                        <td>{{ $company->base_rate }}</td>
                                        <td>
                                            @if ($company->is_active)
                                                <span class="badge bg-primary">{{ __('نشط') }}</span>
                                            @else
                                                <span class="badge bg-danger">{{ __('غير نشط') }}</span>
                                            @endif
                                        </td>
                                        {{-- @canany(['تعديل شركات الشحن', 'حذف شركات الشحن']) --}}
                                        <td>
                                            {{-- @can('تعديل شركات الشحن') --}}
                                            <a
                                                class="btn btn-success"
                                                        href="{{ route('companies.edit', $company) }}">
                                                <i class="las la-edit"></i>
                                            </a>
                                            {{-- @endcan
                                                @can('حذف شركات الشحن') --}}
                                            <form action="{{ route('companies.destroy', $company) }}" method="POST"
                                                style="display:inline-block;"
                                                onsubmit="return confirm('هل أنت متأكد من حذف هذه الشركة؟');">
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
    {{ $companies->links() }}
@endsection
