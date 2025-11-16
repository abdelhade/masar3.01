@extends('admin.dashboard')

@section('sidebar')
    @include('components.sidebar.reports')
@endsection
@section('content')
    @include('components.breadcrumb', [
        'title' => __('Sales Report By Address'),
        'items' => [['label' => __('Home'), 'url' => route('admin.dashboard')], ['label' => __('Sales Report By Address')]],
    ])


<livewire:reports.sales.manage-sales-report-by-adress />
 
@endsection
