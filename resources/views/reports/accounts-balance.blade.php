@extends('admin.dashboard')
@section('content')
    @include('components.breadcrumb', [
        'title' => __('Account Movement'),
            'items' => [['label' => __('Home'), 'url' => route('admin.dashboard')], ['label' => __('Accounts'), 'url' => route('accounts.index')], ['label' => __('Account Movement')]],
    ])

    <livewire:accounts.reports.account-movement :accountId="$accountId" />
@endsection
