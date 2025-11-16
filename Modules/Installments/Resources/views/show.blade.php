@extends('admin.dashboard')

@section('sidebar')
    @include('components.sidebar.installments')
@endsection

@section('content')
    @include('components.breadcrumb', [
        'title' => __('View Installment Plan'),
        'items' => [
            ['label' => __('Dashboard'), 'url' => route('admin.dashboard')],
            ['label' => __('Installment Plans'), 'url' => route('installments.plans.index')],
            ['label' => __('View Plan No.') . ' ' . $plan->id],
        ],
    ])

    @livewire('installments::show-installment-plan', ['plan' => $plan])
@endsection
