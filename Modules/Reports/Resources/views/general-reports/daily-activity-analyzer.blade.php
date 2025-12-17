@extends('admin.dashboard')

@section('sidebar')
    @include('components.sidebar.reports')
@endsection

@section('content')
    @livewire('general-reports.daily-activity-analyzer')
@endsection
