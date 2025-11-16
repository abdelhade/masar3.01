@extends('admin.dashboard')

{{-- Dynamic Sidebar --}}
@section('sidebar')
    @include('components.sidebar.reports')
@endsection
@section('content')
    {{-- @include('components.breadcrumb', [
        'title' => __('Item Movement'),
            'items' => [['label' => __('Home'), 'url' => route('admin.dashboard')], ['label' => __('Items'), 'url' => route('items.index')], ['label' => __('Item Movement')]],
    ]) --}}

    <livewire:item-management.reports.item-movement :itemId="$itemId" :warehouseId="$warehouseId" />
@endsection
