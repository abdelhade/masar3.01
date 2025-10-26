@extends('admin.dashboard')

@section('sidebar')
    @include('components.sidebar.inquiries')
@endsection

@section('content')
    @include('components.breadcrumb', [
        'title' => __('Inquiries'),
        'items' => [['label' => __('Home'), 'url' => route('admin.dashboard')], ['label' => __('Inquiries')]],
    ])

    <div class="row">
        <div class="col-lg-12">

            <a href="{{ route('inquiries.create') }}" type="button" class="btn btn-primary font-family-cairo fw-bold">
                {{ __('Add New Inquiry') }}
                <i class="fas fa-plus me-2"></i>
            </a>

            <br><br>

            <div class="card">
                <div class="card-body">
                    <div class="table-responsive" style="overflow-x: auto;">
                        <x-table-export-actions table-id="inquiries-table" filename="inquiries"
                            excel-label="{{ __('Export Excel') }}" pdf-label="{{ __('Export PDF') }}"
                            print-label="{{ __('Print') }}" />

                        <table id="inquiries-table" class="table table-striped mb-0" style="min-width: 1400px;">
                            <thead class="table-light text-center align-middle">
                                <tr>
                                    <th>#</th>
                                    <th>{{ __('Project') }}</th>
                                    <th>{{ __('Client') }}</th>
                                    <th>{{ __('Date') }}</th>
                                    <th>{{ __('Status') }}</th>
                                    <th>{{ __('Quotation Status') }}</th>
                                    <th>{{ __('Actions') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($inquiries as $inquiry)
                                    <tr class="text-center">
                                        <td>{{ $loop->iteration }}</td>
                                        <td>{{ $inquiry->project?->name ?? '-' }}</td>
                                        <td>{{ $inquiry->client?->cname }}</td>
                                        <td>{{ $inquiry->inquiry_date }}</td>
                                        <td>{{ $inquiry->status }}</td>
                                        <td>{{ $inquiry->quotation_state }}</td>
                                        <td>
                                            <a class="btn btn-primary btn-icon-square-sm"
                                                href="{{ route('inquiries.show', $inquiry->id) }}">
                                                <i class="las la-eye"></i>
                                            </a>

                                            <a class="btn btn-success btn-icon-square-sm"
                                                href="{{ route('inquiries.edit', $inquiry->id) }}">
                                                <i class="las la-edit"></i>
                                            </a>

                                            <form action="{{ route('inquiries.destroy', $inquiry->id) }}" method="POST"
                                                style="display:inline-block;"
                                                onsubmit="return confirm('{{ __('Are you sure you want to delete this item?') }}');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-danger btn-icon-square-sm">
                                                    <i class="las la-trash"></i>
                                                </button>
                                            </form>

                                            <button class="btn btn-info btn-icon-square-sm" data-bs-toggle="modal"
                                                data-bs-target="#commentModal-{{ $inquiry->id }}">
                                                <i class="las la-comment"></i>
                                            </button>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="8" class="text-center">
                                            <div class="alert alert-info py-3 mb-0">
                                                <i class="las la-info-circle me-2"></i>
                                                {{ __('No inquiries found') }}
                                            </div>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>

                    </div>
                </div>
            </div>

            <!-- Comments Modal -->
            @forelse ($inquiries as $inquiry)
                <div class="modal fade" id="commentModal-{{ $inquiry->id }}" tabindex="-1"
                    aria-labelledby="commentModalLabel-{{ $inquiry->id }}" aria-hidden="true">
                    <div class="modal-dialog modal-lg">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title" id="commentModalLabel-{{ $inquiry->id }}">
                                    <i class="fas fa-comments me-2"></i>
                                    {{ __('Inquiry Comments') }}: {{ $inquiry->project->name ?? '' }}
                                </h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"
                                    aria-label="{{ __('Close') }}"></button>
                            </div>
                            <div class="modal-body">
                                <livewire:inquiries::inquiry-comments :inquiryId="$inquiry->id" />
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                                    <i class="fas fa-times me-2"></i>
                                    {{ __('Close') }}
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            @empty
            @endforelse
        </div>
    </div>
@endsection
