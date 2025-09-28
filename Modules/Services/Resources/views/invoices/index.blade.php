@extends('admin.dashboard')

@section('title', 'فواتير الخدمات')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            @livewire('services.service-invoice', ['type' => request('type', 'sell')])
        </div>
    </div>
</div>
@endsection
