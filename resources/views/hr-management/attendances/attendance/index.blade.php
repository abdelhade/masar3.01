@extends('admin.dashboard')

{{-- Dynamic Sidebar --}}
@section('sidebar')
    @include('components.sidebar.departments')
@endsection
@section('content')
    @include('components.breadcrumb', [
        'title' => __('Attendances'),
        'items' => [['label' => __('الرئيسية'), 'url' => route('admin.dashboard')], ['label' => __('الموارد البشرية')], ['label' => __('الحضور')]],
    ])

    <livewire:hr-management.attendances.attendance.index />
 
@endsection