@extends('admin.dashboard')

{{-- Dynamic Sidebar --}}
@section('sidebar')
    @include('components.sidebar.departments')
@endsection
@section('content')
    @include('components.breadcrumb', [
        'title' => __('إعدادات الموارد البشرية'),
        'items' => [['label' => __('Home'), 'url' => route('admin.dashboard')], ['label' => __('إعدادات الموارد البشرية')]],
    ])

<livewire:hr-management.hr-settings.index />
 
@endsection

