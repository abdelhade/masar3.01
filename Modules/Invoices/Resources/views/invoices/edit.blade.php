@extends('admin.dashboard')

{{-- Dynamic Sidebar --}}
@section('sidebar')
    @if (in_array($invoice->type->id, [10, 12, 14, 16, 22]))
        @include('components.sidebar.sales-invoices')
    @elseif (in_array($invoice->type->id, [11, 13, 15, 17, 24, 25]))
        @include('components.sidebar.purchases-invoices')
    @elseif (in_array($invoice->type->id, [18, 19, 20, 21]))
        @include('components.sidebar.inventory-invoices')
    @endif
@endsection
@section('content')
    <div class="container">
        <livewire:invoices.edit-invoice-form :operationId="$invoice->id" />
    </div>
@endsection
