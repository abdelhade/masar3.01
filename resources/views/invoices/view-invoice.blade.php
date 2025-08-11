@extends('admin.dashboard')
@section('content')
    <livewire:invoices.view-invoice :operationId="$operationId" />
@endsection
