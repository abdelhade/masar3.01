@extends('admin.dashboard')

{{-- Dynamic Sidebar --}}
@section('sidebar')
    @include('components.sidebar.departments')
@endsection
@section('content')
    @include('components.breadcrumb', [
        'title' => __('Jobs'),
        'items' => [['label' => __('Home'), 'url' => route('admin.dashboard')], ['label' => __('Jobs')]],
    ])


<livewire:hr-management.jobs.manage-jobs />
 
@endsection
