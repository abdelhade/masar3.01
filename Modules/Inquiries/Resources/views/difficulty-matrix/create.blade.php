@extends('admin.dashboard')

@section('sidebar')
    @include('components.sidebar.inquiries')
@endsection
@section('content')
    @include('components.breadcrumb', [
        'title' => __('Difficulty Matrix'),
        'items' => [['label' => __('Home'), 'url' => route('admin.dashboard')], ['label' => __('Create')]],
    ])

    <livewire:inquiries::difficulty-matrix />
@endsection
