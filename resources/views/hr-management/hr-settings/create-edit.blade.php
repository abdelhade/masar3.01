@extends('admin.dashboard')

{{-- Dynamic Sidebar --}}
@section('sidebar')
    @include('components.sidebar.departments')
@endsection
@section('content')
    @include('components.breadcrumb', [
        'title' => __('hr.hr_settings'),
        'items' => [['label' => __('Home'), 'url' => route('admin.dashboard')], ['label' => __('hr.hr_settings'), 'url' => route('hr.settings.index')], ['label' => __('hr.edit')]],
    ])

<livewire:hr-management.hr-settings.create-edit :settingId="request()->get('settingId')" />
 
@endsection

