@extends('admin.dashboard')

{{-- Dynamic Sidebar --}}
@section('sidebar')
    @include('components.sidebar.departments')
@endsection
@section('content')
    @include('components.breadcrumb', [
        'title' => __('hr.covenants_management'),
        'items' => [['label' => __('Home'), 'url' => route('admin.dashboard')], ['label' => __('hr.covenants_management')]],
    ])


<livewire:hr-management.covenants.manage-covenant />
 
@endsection