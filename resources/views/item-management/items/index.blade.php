@extends('admin.dashboard')

{{-- Dynamic Sidebar --}}
@section('sidebar')
    @if(str_contains(request()->path(), 'reports') || str_contains(request()->url(), 'reports'))
        @include('components.sidebar.reports')
    @else
        @include('components.sidebar.items')
    @endif
@endsection
@section('content')
    {{-- @include('components.breadcrumb', [
        'title' => __('Items'),
        'items' => [['label' => __('Home'), 'url' => route('admin.dashboard')], ['label' => __('Items')]],
    ]) --}}


<livewire:item-management.items.index />
 
@endsection
