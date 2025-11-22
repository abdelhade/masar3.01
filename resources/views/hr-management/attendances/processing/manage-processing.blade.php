@extends('admin.dashboard')

{{-- Dynamic Sidebar --}}
@section('sidebar')
    @include('components.sidebar.departments')
@endsection
@section('content')
    @include('components.breadcrumb', [
        'title' => __('Attendance Processing'),
        'items' => [['label' => __('الرئيسية'), 'url' => route('admin.dashboard')], ['label' => __('الموارد البشريه')], ['label' => __('معالجة الحضور')]],
    ])

    <livewire:attendance-processing-manager />
 
@endsection