@extends('admin.dashboard')

@section('sidebar')
    @include('components.sidebar.manufacturing')
@endsection

@section('content')
    <div class="container-fluid">
        <div class="row mb-3">
            <div class="col-12">
                <div class="card shadow-sm">
                    <div class="card-header bg-warning text-white">
                        <div class="d-flex justify-content-between align-items-center">
                            <h4 class="mb-0">
                                <i class="fas fa-edit me-2"></i>
                                {{ __('Edit Manufacturing Invoice') }}
                            </h4>
                            @can('view Manufacturing Invoices')
                                <a href="{{ route('manufacturing.index') }}" class="btn btn-light btn-sm">
                                    <i class="fas fa-arrow-left me-1"></i> {{ __('Back to List') }}
                                </a>
                            @endcan
                        </div>
                    </div>
                </div>
            </div>
        </div>

        @livewire('edit-manufacturing-invoice', ['invoiceId' => $id])
    </div>
@endsection
