@extends('admin.dashboard')

@section('sidebar')
    @include('components.sidebar.sales-invoices')
@endsection

@section('content')
    {{-- ✅ Breadcrumb --}}
    @include('components.breadcrumb', [
        'title' => __('تعديل النموذج'),
        'items' => [
            ['label' => __('الرئيسيه'), 'url' => route('admin.dashboard')],
            ['label' => __('نماذج الفواتير'), 'url' => route('invoice-templates.index')],
            ['label' => __('تعديل النموذج')],
        ],
    ])

    <div class="row">
        <div class="col-lg-12">
            <div class="card">
                <form action="{{ route('invoice-templates.update', $template) }}" method="POST">
                    @csrf
                    @method('PUT')

                    <div class="card-body">
                        @include('invoices::invoice-templates.partials.form-fields', [
                            'template' => $template,
                        ])
                    </div>

                    <div class="card-footer text-end">
                        <button type="submit" class="btn btn-primary font-family-cairo fw-bold">
                            <i class="fas fa-save me-1"></i> {{ __('حفظ التغييرات') }}
                        </button>
                        <a href="{{ route('invoice-templates.index') }}"
                            class="btn btn-secondary font-family-cairo fw-bold">
                            <i class="fas fa-times me-1"></i> {{ __('إلغاء') }}
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- ✅ سكريبتات خاصة بالنموذج --}}
    @include('invoices::invoice-templates.partials.scripts')
@endsection
