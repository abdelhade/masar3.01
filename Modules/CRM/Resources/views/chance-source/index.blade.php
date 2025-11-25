@extends('admin.dashboard')

@section('sidebar')
    @include('components.sidebar.crm')
@endsection

@section('content')
    @include('components.breadcrumb', [
        'title' => __('Chance Sources'),
        'items' => [
            ['label' => __('Dashboard'), 'url' => route('admin.dashboard')],
            ['label' => __('Chance Sources')],
        ],
    ])
    <div class="row">
        <div class="col-lg-12">
            @can('create Chance Sources')
                <a href="{{ route('chance-sources.create') }}" type="button" class="btn btn-main font-family-cairo fw-bold">
                    Add New
                    <i class="fas fa-plus me-2"></i>
                </a>
            @endcan
            <br>
            <br>
            <div class="card">
                <div class="card-body">
                    <div class="table-responsive" style="overflow-x: auto;">

                        <x-table-export-actions table-id="chance-source-table" filename="chance-source-table"
                            excel-label="Export Excel" pdf-label="Export PDF" print-label="Print" />

                        <table id="chance-source-table" class="table table-striped mb-0" style="min-width: 1200px;">
                            <thead class="table-light text-center align-middle">
                                <tr>
                                    <th>#</th>
                                    <th>{{ __('Title') }}</th>
                                    @canany(['edit Chance Sources', 'delete Chance Sources'])
                                        <th>{{ __('Actions') }}</th>
                                    @endcanany
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($chanceSources as $chance)
                                    <tr class="text-center">
                                        <td> {{ $loop->iteration }} </td>
                                        <td>{{ $chance->title }}</td>
                                        @canany(['edit Chance Sources', 'delete Chance Sources'])
                                            <td>
                                                @can('edit Chance Sources')
                                                    <a class="btn btn-success btn-icon-square-sm"
                                                        href="{{ route('chance-sources.edit', $chance->id) }}">
                                                        <i class="las la-edit"></i>
                                                    </a>
                                                @endcan

                                                @can('delete Chance Sources')
                                                    <form action="{{ route('chance-sources.destroy', $chance->id) }}" method="POST"
                                                        style="display:inline-block;"
                                                        onsubmit="return confirm('Are you sure you want to delete this major?');">
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
