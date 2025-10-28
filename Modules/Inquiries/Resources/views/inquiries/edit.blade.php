@extends('admin.dashboard')

@section('sidebar')
    @include('components.sidebar.inquiries')
@endsection

@section('content')
    @include('components.breadcrumb', [
        'title' => __('Edit Inquiry'),
        'items' => [
            ['label' => __('Home'), 'url' => route('admin.dashboard')],
            ['label' => __('Inquiries'), 'url' => route('inquiries.index')],
            ['label' => __('Edit')],
        ],
    ])
    <livewire:inquiries::edit-inquiry :id="$id" />
@endsection
