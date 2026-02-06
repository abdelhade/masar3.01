@extends('admin.dashboard')

@section('body_class', 'invoice-page')
@section('hide_footer')
@endsection
@push('styles')
    @include('admin.partials.invoice-page-styles')
@endpush
@section('sidebar')
    @if (in_array($invoice->type->id, [10, 12, 14, 16, 22, 26]))
        @include('components.sidebar.sales-invoices')
    @elseif (in_array($invoice->type->id, [11, 13, 15, 17, 24, 25]))
        @include('components.sidebar.purchases-invoices')
    @elseif (in_array($invoice->type->id, [18, 19, 20, 21]))
        @include('components.sidebar.inventory-invoices')
    @endif
@endsection
@section('content')
    <livewire:invoices.edit-invoice-form :operationId="$invoice->id" />
@endsection
