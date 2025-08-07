@extends('admin.dashboard')
@section('content')
    <livewire:create-invoice-form :type="$type" :hash="$hash" />
@endsection
