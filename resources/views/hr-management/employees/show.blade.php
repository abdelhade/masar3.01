@extends('admin.dashboard')

{{-- Dynamic Sidebar --}}
@section('sidebar')
    @include('components.sidebar.departments')
@endsection

@section('content')
    <livewire:hr-management.employees.show-employee :employeeId="$employeeId" />
@endsection

