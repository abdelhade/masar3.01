<div>
    @extends('admin.dashboard')

    @section('content')
        <div class="container-fluid">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2>إدارة الفرص</h2>
                {{-- <a href="{{ route('leads.create') }}" class="btn btn-primary">
                <i class="fas fa-plus"></i> إضافة فرصة جديدة
            </a> --}}
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
