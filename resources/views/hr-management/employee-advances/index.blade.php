@extends('admin.dashboard')

{{-- Dynamic Sidebar --}}
@section('sidebar')
    @include('components.sidebar.departments')
@endsection

@section('content')
    @include('components.breadcrumb', [
        'title' => __('navigation.employee_advances'),
        'items' => [
            ['label' => __('الرئيسية'), 'url' => route('admin.dashboard')],
            ['label' => __('الموارد البشريه')],
            ['label' => __('navigation.employee_advances')],
        ],
    ])

    <livewire:employee-advances.manage-employee-advances />

@endsection

