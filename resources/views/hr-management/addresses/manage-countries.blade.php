@extends('admin.dashboard')

{{-- Dynamic Sidebar --}}
@section('sidebar')
    @include('components.sidebar.departments')
@endsection
@section('content')
    @include('components.breadcrumb', [
        'title' => __('Countries'),
        'items' => [['label' => __('Home'), 'url' => route('admin.dashboard')], ['label' => __('Countries')]],
    ])


<livewire:hr-management.addresses.manage-countries />

@endsection
