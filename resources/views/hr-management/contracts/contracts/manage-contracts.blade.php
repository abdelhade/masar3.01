@extends('admin.dashboard')

{{-- Dynamic Sidebar --}}
@section('sidebar')
    @include('components.sidebar.departments')
@endsection
@section('content')
    @include('components.breadcrumb', [
        'title' => __('Contracts'),
        'items' => [['label' => __('الرئيسيه'), 'url' => route('admin.dashboard')], ['label' => __('الموارد البشرية')], ['label' => __('العقود')]],
    ])


<livewire:hr-management.contracts.contracts.manage-contracts />
 
@endsection