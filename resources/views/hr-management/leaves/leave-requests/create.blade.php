@extends('admin.dashboard')

{{-- Dynamic Sidebar --}}
@section('sidebar')
    @include('components.sidebar.departments')
@endsection
@section('content')
    @include('components.breadcrumb', [
        'title' => __('إدارة طلب الإجازات'),
        'items' => [['label' => __('Home'), 'url' => route('admin.dashboard')], ['label' => __('إدارة طلب الإجازات')]],
    ])


<livewire:leaves.leave-requests.create />
 
@endsection
