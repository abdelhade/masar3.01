@extends('admin.dashboard')

{{-- Dynamic Sidebar --}}
@section('sidebar')
    @include('components.sidebar.items')
@endsection
@section('content')
    @include('components.breadcrumb', [
        'title' => __('navigation.varibals'),
        'items' => [['label' => __('navigation.home'), 'url' => route('admin.dashboard')], ['label' => __('navigation.varibals')]],
    ])

    <livewire:varibal-management />
@endsection
