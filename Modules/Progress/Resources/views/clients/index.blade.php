@extends('progress::layouts.app')
@section('breadcrumb')
    <li class="breadcrumb-item">
        <a href="{{ route('progress.dashboard') }}" class="text-muted text-decoration-none">
            {{ __('general.dashboard') }}
        </a>
    </li>
@endsection
@section('title', __('general.clients_management'))

@section('content')
<div class=" m-3  d-flex justify-content-between align-items-center">
    <h5 class="mb-0">{{ __('general.clients_list') }}</h5>
    @can('create progress-clients')
    <a href="{{ route('progress.clients.create') }}" class="btn btn-primary">
        <i class="fas fa-plus me-1"></i> {{ __('general.new_client') }}
    </a>
    @endcan

</div>
<div class="card">

    <div class="card-body">
        <div class="table-responsive">
            <table id="myTable" class="table table-striped mb-0 w-100" style="min-width: 100%;">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>{{ __('general.client_name') }}</th>
                        <th>{{ __('general.contact_person') }}</th>
                        <th>{{ __('general.phone') }}</th>
                        <th>{{ __('general.email') }}</th>
                        @canany(['edit progress-clients', 'delete progress-clients'])
                        <th>{{ __('general.actions') }}</th>
                        @endcanany
                    </tr>
                </thead>
                <tbody>
                    @foreach($clients as $client)
                    <tr>
                        <td>{{ $loop->iteration }}</td>
                        <td>{{ $client->cname }}</td>
                        <td>{{ $client->contact_person }}</td>
                        <td>{{ $client->phone }}</td>
                        <td>{{ $client->email }}</td>
                        @canany(['edit progress-clients', 'delete progress-clients'])
                        <td>
                            @can('edit progress-clients')
                            <a href="{{ route('progress.clients.edit', $client) }}" class="btn btn-sm btn-success">
                                <i class="fas fa-edit"></i>
                            </a>
                            @endcan
                            @can('delete progress-clients')
                            <form action="{{ route('progress.clients.destroy', $client) }}" method="POST" class="d-inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('{{ __('general.are_you_sure') }}')">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </form>
                            @endcan
                        </td>
                        @endcanany
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
