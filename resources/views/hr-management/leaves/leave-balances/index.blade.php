@extends('admin.dashboard')
@section('content')
    @include('components.breadcrumb', [
        'title' => __('إدارة رصيد الإجازات'),
        'items' => [['label' => __('Home'), 'url' => route('admin.dashboard')], ['label' => __('إدارة رصيد الإجازات')]],
    ])

<livewire:leaves.leave-balances.index />
 
@endsection
