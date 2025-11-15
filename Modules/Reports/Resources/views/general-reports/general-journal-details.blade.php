@extends('admin.dashboard')

@section('sidebar')
@include('components.sidebar.reports')
@endsection

@section('content')
    @livewire('general-reports.general-journal-details')
@endsection 