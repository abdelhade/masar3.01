@extends('admin.dashboard')

@section('sidebar')
    @include('components.sidebar.myresources')
@endsection

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="page-title-box">
                <h4 class="page-title">{{ __('Resources Management') }}</h4>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h4 class="header-title">{{ __('Resources List') }}</h4>
                        <div>
                            @can('create MyResources')
                            <a href="{{ route('myresources.create') }}" class="btn btn-primary">
                                <i class="fas fa-plus"></i> {{ __('Add New Resource') }}
                            </a>
                            @endcan
                            @can('view MyResources Dashboard')
                            <a href="{{ route('myresources.dashboard') }}" class="btn btn-info">
                                <i class="fas fa-chart-bar"></i> {{ __('Dashboard') }}
                            </a>
                            @endcan
                        </div>
                    </div>

                    @livewire('myresources-index')
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

