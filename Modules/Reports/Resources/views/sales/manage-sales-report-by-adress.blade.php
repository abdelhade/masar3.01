@extends('admin.dashboard')

@section('sidebar')
    @include('components.sidebar.reports')
@endsection
@section('content')
    @include('components.breadcrumb', [
        'title' => __(__('{{ __('Sales Report By Address') }}')),
        'items' => [
            ['label' => __(__('{{ __('Home') }}')), 'url' => route('admin.dashboard')],
            ['label' => __(__('{{ __('Sales Report By Address') }}'))],
        ],
    ])
    <livewire:reports.sales.manage-sales-report-by-adress />
@endsection
