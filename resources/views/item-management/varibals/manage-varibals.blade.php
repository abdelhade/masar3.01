@extends('admin.dashboard')
@section('content')
    @include('components.breadcrumb', [
        'title' => __('Varibals'),
        'items' => [['label' => __('Home'), 'url' => route('admin.dashboard')], ['label' => __('Varibals')]],
    ])

    <livewire:varibal-management />
@endsection
