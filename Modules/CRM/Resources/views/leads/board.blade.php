@extends('admin.dashboard')

@section('sidebar')
    @include('components.sidebar.crm')
@endsection

@section('content')
    @include('components.breadcrumb', [
        'title' => __('Leads'),
        'items' => [['label' => __('Dashboard'), 'url' => route('admin.dashboard')], ['label' => __('Leads')]],
    ])

    <div class="container-fluid">
        <div class="d-flex justify-content-between align-items-center mb-4">
            {{-- @can('create Leads')
                <a href="{{ route('leads.create') }}" class="btn btn-primary font-family-cairo fw-bold">
                    <i class="fas fa-plus me-2"></i> {{ __('Add New Lead') }}
                </a>
            @endcan --}}
        </div>

        @if (session('message'))
            <div class="alert alert-success alert-dismissible fade show">
                {{ session('message') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        @livewire('leads-board')
    </div>
@endsection
