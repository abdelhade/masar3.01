@extends('admin.dashboard')

{{-- Dynamic Sidebar --}}
@section('sidebar')
    @include('components.sidebar.journals')
@endsection
@section('content')
    <div class="div">
        @can('إضافة تسجيل الارصده الافتتاحيه للمخازن')
            <a href="{{ route('inventory-balance.create') }}" class="btn btn-main">
                <i class="fas fa-plus me-2"></i>
                Create
            </a>
        @endcan
    </div>
@endsection
