@extends('admin.dashboard')

{{-- Dynamic Sidebar --}}
@section('sidebar')
    @include('components.sidebar.departments')
@endsection
@section('content')
    @include('components.breadcrumb', [
        'title' => __('تعديل طلب الإجازة'),
        'items' => [
            ['label' => __('Home'), 'url' => route('admin.dashboard')], 
            ['label' => __('إدارة طلب الإجازات'), 'url' => route('leaves.requests.index')],
            ['label' => __('تعديل طلب الإجازة')]
        ],
    ])

<livewire:leaves.leave-requests.edit :requestId="$requestId" />
 
@endsection
