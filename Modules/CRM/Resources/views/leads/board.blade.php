<div>
    @extends('admin.dashboard')

    @section('sidebar')
        @include('components.sidebar.crm')
    @endsection

    @section('content')
        <div class="container-fluid">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2>{{ __('Add New Lead') }}</h2>

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
</div>
