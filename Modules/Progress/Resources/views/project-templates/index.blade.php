@extends('admin.dashboard')
@section('content')
    @include('components.breadcrumb', [
        'title' => __('المشروعات'),
        'items' => [['label' => __('الرئيسيه'), 'url' => route('admin.dashboard')], ['label' => __('المشروعات')]],
    ])
    <div class="row">
        <div class="col-lg-12">
            {{-- @can('إضافة مشروعات') --}}
            <a href="{{ route('project.template.create') }}" type="button" class="btn btn-primary font-family-cairo fw-bold">
                {{ __('قالب جديد') }}
                <i class="fas fa-plus me-2"></i>
            </a>
            {{-- @endcan --}}
            <br>
            <br>
            <div class="card">
                <div class="card-body">
                    <div class="table-responsive" style="overflow-x: auto;">
                        <x-table-export-actions table-id="projects-table" filename="projects-table"
                            excel-label="تصدير Excel" pdf-label="تصدير PDF" print-label="طباعة" />
                        <table id="projects-table" class="table table-striped mb-0" style="min-width: 1200px;">
                            <thead class="table-light text-center align-middle">
                                <tr>
                                    <th>#</th>
                                    <th>{{ __('نوع المشروع (القالب)') }}</th>
                                    <th>{{ __('عدد البنود') }}</th>
                                    <th>{{ __('أُنشئ في') }}</th>
                                    {{-- @canany(['تعديل مشروعات', 'حذف مشروعات']) --}}
                                    <th>{{ __('العمليات') }}</th>
                                    {{-- @endcanany --}}
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($templates as $template)
                                    <tr class="text-center">
                                        <td>{{ $loop->iteration }}</td>
                                        <td>{{ $template->name }}</td>
                                        <td><span class="badge bg-info">{{ $template->items_count }}</span></td>
                                        <td>{{ $template->created_at->format('Y-m-d') }}</td>
                                        {{-- @canany(['تعديل مشروعات', 'حذف مشروعات']) --}}
                                        <td>
                                            {{-- @can('project-templates-view') --}}
                                            <a href="{{ route('project.template.show', $template->id) }}"
                                                class="btn btn-primary btn-icon-square-sm" title="{{ __('general.view') }}">
                                                <i class="fa fa-eye"></i>
                                            </a>
                                            {{-- @endcan --}}
                                            {{-- @can('تعديل مشروعات') --}}
                                            <a class="btn btn-success btn-icon-square-sm"
                                                href="{{ route('project.template.edit', $template->id) }}">
                                                <i class="las la-edit"></i>
                                            </a>
                                            {{-- @endcan
                                                @can('حذف مشروعات') --}}
                                            <form action="{{ route('project.template.destroy', $template->id) }}"
                                                method="POST" style="display:inline-block;"
                                                onsubmit="return confirm('هل أنت متأكد من حذف هذا المشروع؟');">
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
                                        <td colspan="12" class="text-center">
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
