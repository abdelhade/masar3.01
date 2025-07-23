@extends('admin.dashboard')
@section('content')
    <div class="div">
        @can('إضافة تسجيل الارصده الافتتاحيه للمخازن')
            <a href="{{ route('inventory-balance.create') }}" class="btn btn-primary">Create</a>
        @endcan
    </div>
@endsection
