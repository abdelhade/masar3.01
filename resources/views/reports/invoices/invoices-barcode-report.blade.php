@extends('admin.dashboard')
@section('content')
    @include('components.breadcrumb', [
        'title' => __('الفواتير'),
        'items' => [
            ['label' => __('الرئيسيه'), 'url' => route('admin.dashboard')],
            ['label' => __('طباعة باركرد ')],
        ],
    ])
    <br>
    <livewire:reports.barcode-printing-report :operationId="$id" />
@endsection
