@extends('admin.dashboard')

{{-- Dynamic Sidebar --}}
@section('sidebar')
    @include('components.sidebar.departments')
@endsection
@section('content')
    @include('components.breadcrumb', [
        'title' => __('hr.errands_management'),
        'items' => [['label' => __('Home'), 'url' => route('admin.dashboard')], ['label' => __('hr.errands_management')]],
    ])


<livewire:hr-management.errands.manage-errands />
 
@endsection