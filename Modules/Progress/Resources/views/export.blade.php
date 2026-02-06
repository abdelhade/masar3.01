@extends('progress::layouts.app') 

@section('content')
    <div class="container py-5 ">
        <a href="{{ url()->previous() }}" class="btn btn-outline-secondary shadow-sm justify-content-end mb-4">
            <i class="fas fa-arrow-right me-1"></i> {{ __('general.back') }}
        </a>
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card shadow-lg border-0 rounded-4">
                    <div class="card-header bg-gradient-primary text-white text-center py-4 rounded-top-4">
                        <h2 class="mb-1">🔒 {{ __('general.backup_title') }}</h2>
                        <p class="mb-0 small">{{ __('general.backup_subtitle') }}</p>

                    </div>
                    <div class="card-body text-center py-5">

                        <div class="d-flex flex-column flex-md-row justify-content-center align-items-center gap-3">
                            
                            <a href="{{ route('progress.export.data') }}"
                                class="btn btn-lg btn-outline-primary px-5 py-3 fw-bold shadow-sm rounded-3 hover-scale">
                                <i class="fas fa-file-archive me-2"></i> {{ __('general.download_json_csv') }}
                            </a>

                            
                            <a href="{{ route('progress.export.sql') }}"
                                class="btn btn-lg btn-outline-success px-5 py-3 fw-bold shadow-sm rounded-3 hover-scale">
                                <i class="fas fa-database me-2"></i> {{ __('general.download_sql') }}
                            </a>
                        </div>

                    </div>

                </div>
            </div>
        </div>
    </div>

    
    <style>
        .bg-gradient-primary {
            background: linear-gradient(135deg, #0061f2, #4e9efc);
        }

        .hover-scale {
            transition: all 0.2s ease-in-out;
        }

        .hover-scale:hover {
            transform: scale(1.05);
        }
    </style>
@endsection
