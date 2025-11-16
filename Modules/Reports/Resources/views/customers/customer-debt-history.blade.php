@extends('admin.dashboard')

@section('sidebar')
    @include('components.sidebar.reports')
@endsection

@section('content')
    @include('components.breadcrumb', [
        'title' => __('Customer Debt History'),
        'items' => [['label' => __('Home'), 'url' => route('admin.dashboard')], ['label' => __('Customer Debt History')]],
    ])

    @livewire('customers.customer-debt-history')
@endsection
