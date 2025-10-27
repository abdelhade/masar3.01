@extends('admin.dashboard')

{{-- Dynamic Sidebar --}}
@section('sidebar')
    @include('components.sidebar.crm')
@endsection

@section('content')
    @include('components.breadcrumb', [
        'title' => __('أنواع العملاء'),
        'items' => [
            ['label' => __('الرئيسيه'), 'url' => route('admin.dashboard')],
            ['label' => __('أنواع العملاء')],
        ],
    ])
    <div class="row">
        <div class="col-lg-12">
            {{-- @can('إضافة انواع العملاء') --}}
            <a href="{{ route('client-types.create') }}" type="button" class="btn btn-primary font-family-cairo fw-bold">
                اضافه جديده
                <i class="fas fa-plus me-2"></i>
            </a>
            {{-- @endcan --}}
            <br><br>

            <div class="card">
                <div class="card-body">
                    <div class="table-responsive" style="overflow-x: auto;">

                        <x-table-export-actions table-id="customer-type-table" filename="customer-type-table"
                            excel-label="تصدير Excel" pdf-label="تصدير PDF" print-label="طباعة" />

                        <table id="customer-type-table" class="table table-striped mb-0" style="min-width: 1200px;">
                            <thead class="table-light text-center align-middle">
                                <tr>
                                    <th>#</th>
                                    <th>{{ __('العنوان') }}</th>
                                    <th>{{ __('الفرع') }}</th>
                                    {{-- @canany(['تعديل انواع العملاء', 'حذف انواع العملاء']) --}}
                                    <th>{{ __('العمليات') }}</th>
                                    {{-- @endcanany --}}
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($customerTypes as $type)
                                    <tr class="text-center">
                                        <td>{{ $loop->iteration }}</td>
                                        <td>{{ $type->title }}</td>
                                        <td>{{ $type->branch->name ?? '-' }}</td>

                                        {{-- @canany(['تعديل انواع العملاء', 'حذف انواع العملاء']) --}}
                                        <td>
                                            {{-- @can('تعديل انواع العملاء') --}}
                                            <a class="btn btn-success btn-icon-square-sm"
                                                href="{{ route('client-types.edit', $type->id) }}">
                                                <i class="las la-edit"></i>
                                            </a>
                                            {{-- @endcan

                                                @can('حذف انواع العملاء') --}}
                                            <form action="{{ route('client-types.destroy', $type->id) }}" method="POST"
                                                style="display:inline-block;"
                                                onsubmit="return confirm('هل أنت متأكد من حذف هذا النوع؟');">
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
