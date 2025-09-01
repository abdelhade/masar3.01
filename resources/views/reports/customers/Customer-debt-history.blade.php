@extends('admin.dashboard')
@section('content')
    @include('components.breadcrumb', [
        'title' => __('Customer Debt History'),
        'items' => [['label' => __('Home'), 'url' => route('admin.dashboard')], ['label' => __('Customer Debt History')]],
    ])


<livewire:reports.customers.customer-debt-history />
 
@endsection
