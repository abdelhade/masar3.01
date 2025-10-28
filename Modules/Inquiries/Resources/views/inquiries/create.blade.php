@extends('admin.dashboard')

@section('sidebar')
    @include('components.sidebar.inquiries')
@endsection

@section('content')
    @include('components.breadcrumb', [
        'title' => __('Create New Inquiry'),
        'items' => [
            ['label' => __('Home'), 'url' => route('admin.dashboard')],
            ['label' => __('Inquiries'), 'url' => route('inquiries.index')],
            ['label' => __('Create New')],
        ],
    ])
    <livewire:inquiries::create-inquiry />
@endsection
