@extends('admin.dashboard')

{{-- Dynamic Sidebar --}}
@section('sidebar')
    @include('components.sidebar.departments')
@endsection
@section('content')
    @include('components.breadcrumb', [
        'title' => __('Towns'),
        'items' => [['label' => __('Home'), 'url' => route('admin.dashboard')], ['label' => __('Towns')]],
    ])


<livewire:hr-management.addresses.manage-towns />

@endsection
