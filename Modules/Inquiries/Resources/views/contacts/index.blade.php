@extends('admin.dashboard')

@section('sidebar')
    @include('components.sidebar.inquiries')
@endsection

@section('content')
    @include('components.breadcrumb', [
        'title' => __('Contacts'),
        'items' => [['label' => __('Dashboard'), 'url' => route('admin.dashboard')], ['label' => __('Contacts')]],
    ])

    <div class="row">
        <div class="col-lg-12">
            @can('create Contacts')
                <a href="{{ route('contacts.create') }}" type="button" class="btn btn-main font-hold fw-bold">
                    <i class="fas fa-plus me-2"></i>
                    Add New
                </a>
            @endcan
            <br>
            <br>
            <div class="card">
                <div class="card-body">
                    <div class="table-responsive" style="overflow-x: auto;">

                        <x-table-export-actions table-id="contacts-table" filename="contacts-table"
                            excel-label="Export Excel" pdf-label="Export PDF" print-label="Print" />

                        <table id="contacts-table" class="table table-striped mb-0" style="min-width: 1200px;">
                            <thead class="table-light text-center align-middle">
                                <tr>
                                    <th>#</th>
                                    <th>{{ __('Name') }}</th>
                                    <th>{{ __('Type') }}</th>
                                    <th>{{ __('Email') }}</th>
                                    <th>{{ __('Phone') }}</th>
                                    <th>{{ __('Main Role') }}</th>
                                    <th>{{ __('Parent Contact') }}</th>
                                    @canany(['edit Contacts', 'delete Contacts'])
                                        <th>{{ __('Actions') }}</th>
                                    @endcanany
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($contacts as $contact)
                                    <tr class="text-center">
                                        <td>{{ $loop->iteration }}</td>
                                        <td>{{ $contact->name }}</td>
                                        <td>
                                            @if ($contact->type === 'person')
                                                <span class="badge badge-info">{{ __('Person') }}</span>
                                            @else
                                                <span class="badge badge-success">{{ __('Company') }}</span>
                                            @endif
                                        </td>
                                        <td>{{ $contact->email ?? '-' }}</td>
                                        <td>{{ $contact->phone_1 ?? '-' }}</td>
                                        <td>{{ $contact->role->name ?? '-' }}</td>
                                        <td>{{ $contact->parent->name ?? '-' }}</td>
                                        @canany(['edit Contacts', 'delete Contacts'])
                                            <td>
                                                @can('edit Contacts')
                                                    <a class="btn btn-success btn-icon-square-sm"
                                                        href="{{ route('contacts.edit', $contact->id) }}">
                                                        <i class="las la-edit"></i>
                                                    </a>
                                                @endcan

                                                @can('delete Contacts')
                                                    <form action="{{ route('contacts.destroy', $contact->id) }}" method="POST"
                                                        style="display:inline-block;"
                                                        onsubmit="return confirm('{{ __('Are you sure you want to delete?') }}');">
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
                                        <td colspan="8" class="text-center">
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
