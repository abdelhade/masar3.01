@extends('admin.dashboard')

@section('sidebar')
    @include('components.sidebar.inquiries')
@endsection

@section('content')
    @include('components.breadcrumb', [
        'title' => __('Project Documents'),
        'items' => [
            ['label' => __('Home'), 'url' => route('admin.dashboard')],
            ['label' => __('Project Documents')],
        ],
    ])

    <div class="row">
        <div class="col-lg-12">

            @can('Create Documents')
                <a href="{{ route('inquiry.documents.create') }}" type="button" class="btn btn-primary font-family-cairo fw-bold">
                    {{ __('Add New') }}
                    <i class="fas fa-plus me-2"></i>
                </a>
            @endcan

            <br><br>

            <div class="card">
                <div class="card-body">
                    <div class="table-responsive" style="overflow-x: auto;">

                        <x-table-export-actions table-id="project-documents-table" filename="project-documents"
                            excel-label="{{ __('Export Excel') }}" pdf-label="{{ __('Export PDF') }}"
                            print-label="{{ __('Print') }}" />

                        <table id="project-documents-table" class="table table-striped mb-0 text-center align-middle"
                            style="min-width: 1000px;">
                            <thead class="table-light">
                                <tr>
                                    <th>#</th>
                                    <th>{{ __('Document Name') }}</th>

                                    @canany(['Edit Documents', 'Delete Documents'])
                                        <th>{{ __('Actions') }}</th>
                                    @endcanany
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($documents as $doc)
                                    <tr>
                                        <td>{{ $loop->iteration }}</td>
                                        <td>{{ $doc->name }}</td>

                                        @canany(['Edit Documents', 'Delete Documents'])
                                            <td>
                                                @can('Edit Documents')
                                                    <a href="{{ route('inquiry.documents.edit', $doc->id) }}"
                                                        class="btn btn-success btn-icon-square-sm">
                                                        <i class="las la-edit"></i>
                                                    </a>
                                                @endcan

                                                @can('Delete Documents')
                                                    <form action="{{ route('inquiry.documents.destroy', $doc->id) }}"
                                                        method="POST" style="display:inline-block;"
                                                        onsubmit="return confirm('{{ __('Are you sure you want to delete this document?') }}');">
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
                                        <td colspan="3" class="text-center">
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
