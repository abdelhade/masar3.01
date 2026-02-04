@extends('admin.dashboard')

@section('sidebar')
    @include('components.sidebar.projects')
@endsection

@section('content')
    <livewire:projects.create />
@endsection
