@extends('admin.dashboard')
@section('content')
    @include('components.breadcrumb', [
        'title' => __('Balance Sheet'),
            'items' => [['label' => __('Home'), 'url' => route('admin.dashboard')], ['label' => __('Accounts'), 'url' => route('accounts.index')], ['label' => __('Balance Sheet')]],
    ])

    <livewire:accounts.reports.manage-balance-sheet />
@endsection
