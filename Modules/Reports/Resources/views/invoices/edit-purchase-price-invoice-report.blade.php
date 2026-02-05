@extends('admin.dashboard')

@section('sidebar')
    @include('components.sidebar.purchases-invoices')
@endsection

@section('content')
    @include('components.breadcrumb', [
        'title' => __('Invoices'),
        'items' => [
            ['label' => __('Home'), 'url' => route('admin.dashboard')],
            ['label' => __('Edit Selling Price for Invoice Items')],
        ],
    ])
    <br>
    <livewire:reports.purchase-invoice-items-pricing :operationId="$id" />
@endsection
