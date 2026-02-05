@extends('admin.dashboard')

@section('sidebar')
    @include('components.sidebar.reports')
@endsection
@section('content')
    @include('components.breadcrumb', [
        'title' => __('Item Purchase Report'),
        'items' => [
            ['label' => __('Home'), 'url' => route('admin.dashboard')],
            ['label' => __('Item Purchase Report')],
        ],
    ])
    <livewire:reports.purchase.manage-item-purchase-report />
@endsection
