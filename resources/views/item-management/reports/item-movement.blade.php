@extends('admin.dashboard')

{{-- Dynamic Sidebar --}}
@section('sidebar')
    @include('components.sidebar.reports')
@endsection
@section('content')

    <livewire:item-management.reports.item-movement :itemId="$itemId" :warehouseId="$warehouseId" />
@endsection
