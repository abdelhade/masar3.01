@extends('admin.dashboard')

@section('sidebar')
    @include('components.sidebar.departments')
@endsection

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="page-title-box no-print">
                <div class="d-flex justify-content-between align-items-center">
                    <h4 class="page-title">{{ __('recruitment.onboarding_details') }}: #{{ $onboarding->id }}</h4>
                    <div class="d-flex gap-2">
                        <button onclick="window.print()" class="btn btn-info">
                            <i class="fas fa-print"></i> {{ __('Print') }}
                        </button>
                        <a href="{{ route('recruitment.onboardings.index') }}" class="btn btn-secondary">
                            <i class="fas fa-arrow-right"></i> {{ __('Back') }}
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card printable-content">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="fas fa-user-check"></i> {{ __('recruitment.onboarding_information') }}</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        @foreach($onboarding->getAttributes() as $key => $value)
                            @if(!in_array($key, ['id', 'created_at', 'updated_at', 'deleted_at']))
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">{{ ucfirst(str_replace('_', ' ', $key)) }}:</label>
                                <div class="form-control-static">
                                    @if($value)
                                        {{ is_array($value) ? json_encode($value) : $value }}
                                    @else
                                        {{ __('N/A') }}
                                    @endif
                                </div>
                            </div>
                            @endif
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('styles')
<style>
    .form-control-static {
        padding: 0.5rem;
        background-color: #f8f9fa;
        border: 1px solid #dee2e6;
        border-radius: 0.375rem;
        min-height: 2.5rem;
        display: flex;
        align-items: center;
    }

    @media print {
        .no-print { display: none !important; }
        .card { border: 1px solid #000 !important; box-shadow: none !important; }
        .card-header { background: #f1f1f1 !important; color: #000 !important; }
        body { font-size: 12px; }
        .form-control-static { background: #fff !important; border: 1px solid #000 !important; }
    }
</style>
@endpush
@endsection

