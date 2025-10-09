@extends('admin.dashboard')

@section('content')
    @include('components.breadcrumb', [
        'title' => __('مراحل التصنيع'),
        'items' => [
            ['label' => __('الرئيسيه'), 'url' => route('admin.dashboard')],
            ['label' => __('مراحل التصنيع')],
        ],
    ])

    <div class="row">
        <div class="col-lg-12">

            {{-- @can('إضافة مرحلة تصنيع') --}}
            <a href="{{ route('manufacturing.stages.create') }}" type="button"
                class="btn btn-primary font-family-cairo fw-bold">
                {{ __('إضافة مرحلة جديدة') }}
                <i class="fas fa-plus me-2"></i>
            </a>
            {{-- @endcan --}}

            <br><br>

            <div class="card">
                <div class="card-body">
                    <div class="table-responsive" style="overflow-x: auto;">
                        <x-table-export-actions table-id="manufacturing-stages-table" filename="manufacturing-stages"
                            excel-label="تصدير Excel" pdf-label="تصدير PDF" print-label="طباعة" />

                        <table id="manufacturing-stages-table" class="table table-striped mb-0 text-center align-middle"
                            style="min-width: 1200px;">
                            <thead class="table-light">
                                <tr>
                                    <th>#</th>
                                    <th>{{ __('اسم المرحلة') }}</th>
                                    <th>{{ __('الوصف') }}</th>
                                    <th>{{ __('الترتيب') }}</th>
                                    <th>{{ __('المدة التقديرية (ساعة)') }}</th>
                                    <th>{{ __('التكلفة') }}</th>
                                    <th>{{ __('الحالة') }}</th>
                                    {{-- @canany(['عرض مرحلة تصنيع', 'تعديل مرحلة تصنيع', 'حذف مرحلة تصنيع']) --}}
                                    <th>{{ __('العمليات') }}</th>
                                    {{-- @endcanany --}}
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($stages as $stage)
                                    <tr>
                                        <td>{{ $loop->iteration }}</td>
                                        <td>{{ $stage->name }}</td>
                                        <td>{{ Str::limit($stage->description, 50) }}</td>
                                        <td>
                                            <span class="badge bg-info">{{ $stage->order }}</span>
                                        </td>
                                        <td>{{ $stage->estimated_duration ?? '-' }}</td>
                                        <td>{{ number_format($stage->cost, 2) }} {{ __('جنيه') }}</td>
                                        <td>
                                            <form action="{{ route('manufacturing-stages.toggle-status', $stage) }}"
                                                method="POST" class="d-inline">
                                                @csrf
                                                @method('PATCH')
                                                <button type="submit"
                                                    class="btn btn-sm {{ $stage->is_active ? 'btn-success' : 'btn-secondary' }}">
                                                    {{ $stage->is_active ? __('نشط') : __('غير نشط') }}
                                                </button>
                                            </form>
                                        </td>

                                        {{-- @canany(['عرض مرحلة تصنيع', 'تعديل مرحلة تصنيع', 'حذف مرحلة تصنيع']) --}}
                                        <td>
                                            <div role="group">
                                                {{-- @can('عرض مرحلة تصنيع') --}}
                                                <a href="{{ route('manufacturing.stages.show', $stage) }}"
                                                    class="btn btn-info btn-icon-square-sm" title="{{ __('عرض') }}">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                {{-- @endcan

                                                    @can('تعديل مرحلة تصنيع') --}}
                                                <a href="{{ route('manufacturing.stages.edit', $stage) }}"
                                                    class="btn btn-success btn-icon-square-sm" title="{{ __('تعديل') }}">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                {{-- @endcan

                                                    @can('حذف مرحلة تصنيع') --}}
                                                <form action="{{ route('manufacturing.stages.destroy', $stage) }}"
                                                    method="POST" style="display:inline-block;"
                                                    onsubmit="return confirm('{{ __('هل أنت متأكد من حذف هذه المرحلة؟') }}');">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-danger btn-icon-square-sm"
                                                        title="{{ __('حذف') }}">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </form>
                                                {{-- @endcan --}}
                                            </div>
                                        </td>
                                        {{-- @endcanany --}}
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="13" class="text-center">
                                            <div class="alert alert-info py-3 mb-0"
                                                style="font-size: 1.2rem; font-weight: 500;">
                                                <i class="las la-info-circle me-2"></i>
                                                {{ __('لا توجد مراحل تصنيع حالياً. قم بإضافة مرحلة جديدة.') }}
                                            </div>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>

                        <div class="mt-3">
                            {{ $stages->links() }}
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
@endsection
