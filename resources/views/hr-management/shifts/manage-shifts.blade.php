@extends('admin.dashboard')

{{-- Dynamic Sidebar --}}
@section('sidebar')
    @include('components.sidebar.departments')
@endsection
@section('content')
    @include('components.breadcrumb', [
        'title' => __('Shifts'),
        'items' => [['label' => __('Home'), 'url' => route('admin.dashboard')], ['label' => __('Shifts')]],
    ])


<livewire:hr-management.shifts.manage-shifts />
 
@endsection
