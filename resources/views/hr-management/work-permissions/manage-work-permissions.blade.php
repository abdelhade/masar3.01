@extends('admin.dashboard')

{{-- Dynamic Sidebar --}}
@section('sidebar')
    @include('components.sidebar.departments')
@endsection
@section('content')
    @include('components.breadcrumb', [
        'title' => __('hr.work_permissions_management'),
        'items' => [['label' => __('Home'), 'url' => route('admin.dashboard')], ['label' => __('hr.work_permissions_management')]],
    ])


<livewire:hr-management.work-permissions.manage-work-permissions />
 
@endsection