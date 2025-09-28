@extends('admin.dashboard')

@section('title', 'تعديل فاتورة الخدمة')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            @livewire('services.service-invoice-form', ['invoiceId' => $invoice->id])
        </div>
    </div>
</div>
@endsection
