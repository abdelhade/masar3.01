@extends('admin.dashboard')

{{-- Dynamic Sidebar --}}
@section('sidebar')
    @include('components.sidebar.departments')
@endsection
@section('content')
    @include('components.breadcrumb', [
        'title' => __('Departments'),
        'items' => [['label' => __('Home'), 'url' => route('admin.dashboard')], ['label' => __('Departments')]],
    ])


<livewire:hr-management.departments.manage-department />
 
@endsection
