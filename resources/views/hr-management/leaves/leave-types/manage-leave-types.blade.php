@extends('admin.dashboard')
@section('content')
    @include('components.breadcrumb', [
        'title' => __('إدارة أنواع الإجازات'),
        'items' => [['label' => __('Home'), 'url' => route('admin.dashboard')], ['label' => __('إدارة أنواع الإجازات')]],
    ])


<livewire:hr-management.leaves.leave-types.manage-leave-types />
 
@endsection
