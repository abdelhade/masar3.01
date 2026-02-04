@extends('admin.dashboard')

@section('sidebar')
    @include('components.sidebar.reports')
@endsection

@section('content')
    @include('components.breadcrumb', [
        'title' => __(__('{{ __('Customer Debt History') }}')),
        'items' => [
            ['label' => __(__('{{ __('Home') }}')), 'url' => route('admin.dashboard')],
            ['label' => __(__('{{ __('Customer Debt History') }}'))],
        ],
    ])
    @livewire('customers.customer-debt-history')
@endsection
