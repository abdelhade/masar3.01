@extends('admin.dashboard')

@section('sidebar')
    @include('components.sidebar.reports')
@endsection

@section('content')
    @include('components.breadcrumb', [
        'title' => __('Item Purchase Report'),
        'items' => [
            ['label' => __('Home'), 'url' => route('admin.dashboard')],
            ['label' => __('Item Purchase Report')],
        ],
    ])

    <div class="container-fluid">
        <div class="card shadow-sm">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0">
                    <i class="fas fa-shopping-cart me-2"></i>{{ __('Item Purchase Report') }}
                </h5>
            </div>
            <div class="card-body">
                <div class="alert alert-info">
                    <i class="fas fa-info-circle me-2"></i>
                    {{ __('This report is under development. Please check back later.') }}
                </div>
                
                {{-- Placeholder for future implementation --}}
                <div class="row">
                    <div class="col-md-12">
                        <p class="text-muted">
                            {{ __('This report will show purchase data for individual items.') }}
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
