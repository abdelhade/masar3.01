@extends('admin.dashboard')

@section('sidebar')
    @include('components.sidebar.reports')
@endsection

@section('content')
   @livewire('cash-box-bank-reports.cash-bank')
@endsection 