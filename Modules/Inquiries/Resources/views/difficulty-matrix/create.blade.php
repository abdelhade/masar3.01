@extends('admin.dashboard')

@section('sidebar')
    @include('components.sidebar.inquiries')
@endsection
@section('content')
    @include('components.breadcrumb', [
        'title' => __('مصفوفة الصعوبة'),
        'items' => [['label' => __('الرئيسية'), 'url' => route('admin.dashboard')], ['label' => __('إنشاء')]],
    ])

    <livewire:inquiries::difficulty-matrix />
@endsection
