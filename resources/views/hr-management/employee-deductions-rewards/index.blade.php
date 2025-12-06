@extends('admin.dashboard')

{{-- Dynamic Sidebar --}}
@section('sidebar')
    @include('components.sidebar.departments')
@endsection

@section('content')
    @include('components.breadcrumb', [
        'title' => __('navigation.employee_deductions_rewards'),
        'items' => [
            ['label' => __('الرئيسية'), 'url' => route('admin.dashboard')],
            ['label' => __('الموارد البشريه')],
            ['label' => __('navigation.employee_deductions_rewards')],
        ],
    ])

    <livewire:employee-deductions-rewards.manage-employee-deductions-rewards />

@endsection

