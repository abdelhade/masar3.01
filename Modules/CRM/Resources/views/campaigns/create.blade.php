@extends('admin.dashboard')

@section('sidebar')
    @include('components.sidebar.crm')
@endsection

@section('content')
    @include('components.breadcrumb', [
        'title' => __('New Campaign'),
        'items' => [
            ['label' => __('Dashboard'), 'url' => route('admin.dashboard')],
            ['label' => __('Marketing Campaigns'), 'url' => route('campaigns.index')],
            ['label' => __('New Campaign')]
        ],
    ])

    @livewire('crm::campaign-form')
@endsection
