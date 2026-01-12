@extends('admin.dashboard')

@section('sidebar')
    @include('components.sidebar.departments')
@endsection

@section('content')
    @include('components.breadcrumb', [
        'title' => __('hr.project_attendance_report'),
        'items' => [
            ['label' => __('general.home'), 'url' => route('admin.dashboard')],
            ['label' => __('hr.human_resources')],
            ['label' => __('hr.attendance')],
            ['label' => __('hr.project_attendance_report')]
        ],
    ])

    <div class="container-fluid">
        <livewire:hr::hr-management.attendances.reports.project-attendance-report />
    </div>
@endsection
