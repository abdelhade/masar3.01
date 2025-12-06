@extends('admin.dashboard')

{{-- Dynamic Sidebar --}}
@section('sidebar')
    @include('components.sidebar.departments')
@endsection

@section('content')
    @include('components.breadcrumb', [
        'title' => __('navigation.flexible_salary_processing'),
        'items' => [
            ['label' => __('الرئيسية'), 'url' => route('admin.dashboard')],
            ['label' => __('الموارد البشريه')],
            ['label' => __('navigation.flexible_salary_processing')],
        ],
    ])

    <livewire:flexible-salary-processing.index />

@endsection

