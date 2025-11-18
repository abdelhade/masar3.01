@extends('admin.dashboard')

@section('sidebar')
    @include('components.sidebar.sales-invoices')
@endsection

@section('content')
    @include('components.breadcrumb', [
        'title' => __('Edit Template'),
        'items' => [
            ['label' => __('Dashboard'), 'url' => route('admin.dashboard')],
            ['label' => __('Invoice Templates'), 'url' => route('invoice-templates.index')],
            ['label' => __('Edit Template')],
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
                            <i class="fas fa-save me-1"></i> {{ __('Save Changes') }}
                        </button>
                        <a href="{{ route('invoice-templates.index') }}"
                            class="btn btn-secondary font-family-cairo fw-bold">
                            <i class="fas fa-times me-1"></i> {{ __('Cancel') }}
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>

    @include('invoices::invoice-templates.partials.scripts')
@endsection
