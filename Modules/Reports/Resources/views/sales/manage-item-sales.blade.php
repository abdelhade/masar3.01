@extends('admin.dashboard')

@section('sidebar')
    @include('components.sidebar.reports')
@endsection
@section('content')
    @include('components.breadcrumb', [
        'title' => __(__('{{ __('Item Sales') }}')),
        'items' => [
            ['label' => __(__('{{ __('Home') }}')), 'url' => route('admin.dashboard')],
            ['label' => __(__('{{ __('Item Sales') }}'))],
        ],
    ])
    <livewire:sales.manage-item-sales />
@endsection
