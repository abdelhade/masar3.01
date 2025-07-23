@extends('admin.dashboard')
@section('content')
    @include('components.breadcrumb', [
        'title' => __('Attendance Processing'),
        'items' => [['label' => __('الرئيسية'), 'url' => route('admin.dashboard')], ['label' => __('الموارد البشريه')], ['label' => __('معالجة الحضور')]],
    ])

    <livewire:hr-management.attendances.processing.manage-processing />
 
@endsection