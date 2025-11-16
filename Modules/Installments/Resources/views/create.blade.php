@extends('admin.dashboard')

@section('sidebar')
    @include('components.sidebar.installments')
@endsection

@section('content')
    @include('components.breadcrumb', [
        'title' => __('Setup New Installment Plan'),
        'items' => [
            ['label' => __('Dashboard'), 'url' => route('admin.dashboard')],
            ['label' => __('Installment Plans'), 'url' => route('installments.plans.index')],
            ['label' => __('New Setup')],
        ],
    ])

    <div class="row">
        <div class="col-12">
            @livewire('installments::create-installment-plan')
        </div>
    </div>
@endsection
