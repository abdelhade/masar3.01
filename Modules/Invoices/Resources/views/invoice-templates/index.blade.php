@extends('admin.dashboard')

{{-- Dynamic Sidebar --}}
@section('sidebar')
    @include('components.sidebar.sales-invoices')
@endsection

@section('content')
    @include('components.breadcrumb', [
        'title' => __('نماذج الفواتير'),
        'items' => [
            ['label' => __('الرئيسيه'), 'url' => route('admin.dashboard')],
            ['label' => __('نماذج الفواتير')],
        ],
    ])

    <div class="row">
        <div class="col-lg-12">
            {{-- @can('إضافة نموذج فاتورة') --}}
            <a href="{{ route('invoice-templates.create') }}" type="button" class="btn btn-primary font-family-cairo fw-bold">
                {{ __('إضافة نموذج جديد') }}
                <i class="fas fa-plus me-2"></i>
            </a>
            {{-- @endcan --}}

            <br><br>

            <div class="card">
                <div class="card-body">
                    <div class="table-responsive" style="overflow-x: auto;">

                        <x-table-export-actions table-id="invoice-templates-table" filename="invoice-templates"
                            excel-label="تصدير Excel" pdf-label="تصدير PDF" print-label="طباعة" />

                        <table id="invoice-templates-table" class="table table-striped mb-0 text-center align-middle"
                            style="min-width: 1200px;">
                            <thead class="table-light">
                                <tr>
                                    <th>#</th>
                                    <th>{{ __('الاسم') }}</th>
                                    <th>{{ __('الكود') }}</th>
                                    <th>{{ __('الوصف') }}</th>
                                    <th>{{ __('أنواع الفواتير') }}</th>
                                    <th>{{ __('عدد الأعمدة') }}</th>
                                    <th>{{ __('الحالة') }}</th>
                                    <th>{{ __('الترتيب') }}</th>
                                    {{-- @canany(['تعديل نموذج فاتورة', 'حذف نموذج فاتورة']) --}}
                                    <th>{{ __('الإجراءات') }}</th>
                                    {{-- @endcanany --}}
                                </tr>
                            </thead>

                            <tbody>
                                @forelse($templates as $template)
                                    <tr>
                                        <td>{{ $loop->iteration }}</td>
                                        <td>{{ $template->name }}</td>
                                        <td><code>{{ $template->code }}</code></td>
                                        <td>{{ Str::limit($template->description, 60) }}</td>
                                        <td>
                                            @foreach ($template->invoiceTypes as $type)
                                                <span class="badge bg-info text-white">
                                                    {{ Modules\Invoices\Models\InvoiceTemplate::getInvoiceTypeName($type->invoice_type) }}
                                                    @if ($type->is_default)
                                                        <i class="fas fa-star text-warning ms-1"></i>
                                                    @endif
                                                </span>
                                            @endforeach
                                        </td>
                                        <td>
                                            <span class="badge bg-secondary">
                                                {{ count($template->visible_columns) }}
                                            </span>
                                        </td>
                                        <td>
                                            <form action="{{ route('invoice-templates.toggle-active', $template) }}"
                                                method="POST" class="d-inline">
                                                @csrf
                                                <button type="submit"
                                                    class="btn btn-sm btn-{{ $template->is_active ? 'success' : 'secondary' }}">
                                                    <i class="fas fa-{{ $template->is_active ? 'check' : 'times' }}"></i>
                                                    {{ $template->is_active ? __('نشط') : __('غير نشط') }}
                                                </button>
                                            </form>
                                        </td>
                                        <td>{{ $template->sort_order }}</td>

                                        {{-- @canany(['تعديل نموذج فاتورة', 'حذف نموذج فاتورة']) --}}
                                        <td>
                                            {{-- @can('تعديل نموذج فاتورة') --}}
                                            <a href="{{ route('invoice-templates.edit', $template) }}"
                                                class="btn btn-success btn-icon-square-sm">
                                                <i class="las la-edit"></i>
                                            </a>
                                            {{-- @endcan --}}

                                            {{-- @can('حذف نموذج فاتورة') --}}
                                            <form action="{{ route('invoice-templates.destroy', $template) }}"
                                                method="POST" style="display:inline-block;"
                                                onsubmit="return confirm('هل أنت متأكد من حذف هذا النموذج؟');">
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
                                        <td colspan="9" class="text-center">
                                            <div class="alert alert-info py-3 mb-0"
                                                style="font-size: 1.2rem; font-weight: 500;">
                                                <i class="las la-info-circle me-2"></i>
                                                {{ __('لا توجد نماذج متاحة') }}
                                            </div>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>

                        <div class="mt-3">
                            {{ $templates->links() }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
