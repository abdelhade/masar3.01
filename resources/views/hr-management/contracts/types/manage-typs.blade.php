@extends('admin.dashboard')

{{-- Dynamic Sidebar --}}
@section('sidebar')
    @include('components.sidebar.departments')
@endsection
@section('content')
    @include('components.breadcrumb', [
        'title' => __('Contract Types'),
        'items' => [['label' => __('الرئيسيه'), 'url' => route('admin.dashboard')], ['label' => __('الموارد البشرية')], ['label' => __('انواع العقود')]],
    ])


<livewire:hr-management.contracts.types.manage-typs />
 
@endsection