@extends('admin.dashboard')
@section('content')
    @include('components.breadcrumb', [
        'title' => __('تصنيفات العملاء'),
        'items' => [
            ['label' => __('الرئيسيه'), 'url' => route('admin.dashboard')],
            ['label' => __('تصنيفات العملاء')],
        ],
    ])
    <div class="row">
        <div class="col-lg-12">
            {{-- @can('إضافة تصنيف عميل') --}}
            <a href="{{ route('client.categories.create') }}" type="button" class="btn btn-primary font-family-cairo fw-bold">
                اضافه جديده
                <i class="fas fa-plus me-2"></i>
            </a>
            {{-- @endcan --}}
            <br><br>
            <div class="card">
                <div class="card-body">
                    <div class="table-responsive" style="overflow-x: auto;">

                        <x-table-export-actions table-id="client-categories-table" filename="client-categories-table"
                            excel-label="تصدير Excel" pdf-label="تصدير PDF" print-label="طباعة" />

                        <table id="client-categories-table" class="table table-striped mb-0" style="min-width: 1200px;">
                            <thead class="table-light text-center align-middle">
                                <tr>
                                    <th>#</th>
                                    <th>{{ __('الاسم') }}</th>
                                    <th>{{ __('الوصف') }}</th>
                                    {{-- @canany(['تعديل تصنيف عميل', 'حذف تصنيف عميل']) --}}
                                    <th>{{ __('العمليات') }}</th>
                                    {{-- @endcanany --}}
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($categories as $category)
                                    <tr class="text-center">
                                        <td>{{ $loop->iteration }}</td>
                                        <td>{{ $category->name }}</td>
                                        <td>{{ $category->description ?? '-' }}</td>
                                        {{-- @canany(['تعديل تصنيف عميل', 'حذف تصنيف عميل']) --}}
                                        <td>
                                            {{-- @can('تعديل تصنيف عميل') --}}
                                            <a class="btn btn-success btn-icon-square-sm"
                                                href="{{ route('client.categories.edit', $category->id) }}">
                                                <i class="las la-edit"></i>
                                            </a>
                                            {{-- @endcan

                                                @can('حذف تصنيف عميل') --}}
                                            <form action="{{ route('client.categories.destroy', $category->id) }}"
                                                method="POST" style="display:inline-block;"
                                                onsubmit="return confirm('هل أنت متأكد من حذف هذا التصنيف؟');">
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
