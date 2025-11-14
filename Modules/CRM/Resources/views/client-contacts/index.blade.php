@extends('admin.dashboard')


@section('sidebar')
    @include('components.sidebar.crm')
@endsection
@section('content')
    @include('components.breadcrumb', [
        'title' => __('Client Contacts'),
        'items' => [
            ['label' => __('Dashboard'), 'url' => route('admin.dashboard')],
            ['label' => __('Client Contacts')],
        ],
    ])
    <div class="row">
        <div class="col-lg-12">
            @can('create Client Contacts')
                <a href="{{ route('client-contacts.create') }}" type="button" class="btn btn-primary font-family-cairo fw-bold">
                    Add New
                    <i class="fas fa-plus me-2"></i>
                </a>
            @endcan
            <br>
            <br>
            <div class="card">
                <div class="card-body">
                    <div class="table-responsive" style="overflow-x: auto;">


                        <x-table-export-actions table-id="client-contact-table" filename="client-contact-table"
                            excel-label="Export Excel" pdf-label="Export PDF" print-label="Print" />


                        <table id="client-contact-table" class="table table-striped mb-0" style="min-width: 1200px;">
                            <thead class="table-light text-center align-middle">
                                <tr>
                                    <th>#</th>
                                    <th>{{ __('Company') }}</th>
                                    <th>{{ __('Name') }}</th>
                                    <th>{{ __('Email') }}</th>
                                    <th>{{ __('Phone') }}</th>
                                    <th>{{ __('Position') }}</th>
                                    @canany(['edit Client Contacts', 'delete Client Contacts'])
                                        <th>{{ __('Actions') }}</th>
                                    @endcanany
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($clientContacts as $contact)
                                    <tr class="text-center">
                                        <td> {{ $loop->iteration }} </td>
                                        <td>{{ $contact->client->cname }}</td>
                                        <td>{{ $contact->name }}</td>
                                        <td>{{ $contact->email }}</td>
                                        <td>{{ $contact->phone }}</td>
                                        <td>{{ $contact->position }}</td>
                                        @canany(['edit Client Contacts', 'delete Client Contacts'])
                                            <td>
                                                @can('edit Client Contacts')
                                                    <a class="btn btn-success btn-icon-square-sm"
                                                        href="{{ route('client-contacts.edit', $contact->id) }}">
                                                        <i class="las la-edit"></i>
                                                    </a>
                                                @endcan
                                                @can('delete Client Contacts')
                                                    <form action="{{ route('client-contacts.destroy', $contact->id) }}"
                                                        method="POST" style="display:inline-block;"
                                                        onsubmit="return confirm('Are you sure you want to delete this contact?');">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="btn btn-danger btn-icon-square-sm">
                                                            <i class="las la-trash"></i>
                                                        </button>
                                                    </form>
                                                @endcan


                                            </td>
                                        @endcanany
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="13" class="text-center">
                                            <div class="alert alert-info py-3 mb-0"
                                                style="font-size: 1.2rem; font-weight: 500;">
                                                <i class="las la-info-circle me-2"></i>
                                                {{ __('No data available') }}
                                            </div>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>


                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
