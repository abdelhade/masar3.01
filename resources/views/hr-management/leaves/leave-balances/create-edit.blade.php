@extends('admin.dashboard')

{{-- Dynamic Sidebar --}}
@section('sidebar')
    @include('components.sidebar.departments')
@endsection
@section('content')
    @include('components.breadcrumb', [
        'title' => __('إدارة رصيد الإجازات'),
        'items' => [['label' => __('Home'), 'url' => route('admin.dashboard')], ['label' => __('إدارة رصيد الإجازات')]],
    ])


<livewire:leaves.leave-balances.create-edit :balanceId="request()->route('balanceId')" />
 
@endsection
