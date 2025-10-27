@extends('admin.dashboard')

@section('sidebar')
    @include('components.sidebar.sales-invoices')
@endsection

@section('content')
    {{-- ✅ Breadcrumb --}}
    @include('components.breadcrumb', [
        'title' => __('إنشاء نموذج فاتورة جديد'),
        'items' => [
            ['label' => __('الرئيسيه'), 'url' => route('admin.dashboard')],
            ['label' => __('نماذج الفواتير'), 'url' => route('invoice-templates.index')],
            ['label' => __('إنشاء نموذج جديد')],
        ],
    ])

    <div class="row">
        <div class="col-lg-12">
            <div class="card">
                <form action="{{ route('invoice-templates.store') }}" method="POST">
                    @csrf

                    <div class="card-body">
                        @include('invoices::invoice-templates.partials.form-fields', [
                            'template' => null,
                        ])
                    </div>

                    <div class="card-footer text-end">
                        <button type="submit" class="btn btn-primary font-family-cairo fw-bold">
                            <i class="fas fa-save me-1"></i> {{ __('حفظ') }}
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
