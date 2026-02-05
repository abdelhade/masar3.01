@extends('admin.dashboard')

@section('sidebar')
    @include('components.sidebar.purchases-invoices')
@endsection

@section('content')
    @include('components.breadcrumb', [
        'title' => __('Invoices'),
        'items' => [['label' => __('Home'), 'url' => route('admin.dashboard')], ['label' => __('Print Barcode')]],
    ])

    <br>

    <livewire:reports.barcode-printing-report :operationId="$id" />
@endsection
