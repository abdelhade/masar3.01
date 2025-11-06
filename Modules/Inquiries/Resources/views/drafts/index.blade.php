@extends('admin.dashboard')

@section('sidebar')
    @include('components.sidebar.inquiries')
@endsection

@section('content')
    @include('components.breadcrumb', [
        'title' => __('Drafts'),
        'items' => [['label' => __('Home'), 'url' => route('admin.dashboard')], ['label' => __('Drafts')]],
    ])

    <div class="container-fluid">
        <div class="row mb-4">
            <div class="col-12">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h2 class="mb-0">
                            <i class="fas fa-file-alt me-2"></i>
                            {{ __('Draft Inquiries') }}
                        </h2>
                    </div>
                    <div>
                        @can('Create Inquiries')
                            <a href="{{ route('inquiries.create') }}" class="btn btn-primary">
                                <i class="fas fa-plus me-2"></i>
                                {{ __('New Inquiry') }}
                            </a>
                        @endcan

                        @can('View Inquiries')
                            <a href="{{ route('inquiries.index') }}" class="btn btn-outline-secondary">
                                <i class="fas fa-list me-2"></i>
                                {{ __('View All Inquiries') }}
                            </a>
                        @endcan
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-12">
                <div class="card shadow-sm">
                    <div class="card-body">
                        @if ($drafts->count() > 0)
                            <div class="table-responsive">
                                <table class="table table-hover align-middle">
                                    <thead class="table-light">
                                        <tr>
                                            <th>{{ __('Tender No.') }}</th>
                                            <th>{{ __('Tender ID') }}</th>
                                            <th>{{ __('Work Type') }}</th>
                                            <th>{{ __('City/Town') }}</th>
                                            <th>{{ __('Project Size') }}</th>
                                            <th>{{ __('Created At') }}</th>
                                            <th class="text-end">{{ __('Actions') }}</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($drafts as $draft)
                                            <tr>
                                                <td>
                                                    <span class="badge bg-secondary">
                                                        {{ $draft->tender_number }}
                                                    </span>
                                                </td>
                                                <td>
                                                    @if ($draft->tender_id)
                                                        {{ Str::limit($draft->tender_id, 30) }}
                                                    @else
                                                        <span class="text-muted">{{ __('Not set') }}</span>
                                                    @endif
                                                </td>
                                                <td>
                                                    @if ($draft->final_work_type)
                                                        <small>{{ Str::limit($draft->final_work_type, 25) }}</small>
                                                    @else
                                                        <span class="text-muted">{{ __('Not selected') }}</span>
                                                    @endif
                                                </td>
                                                <td>
                                                    @if ($draft->city || $draft->town)
                                                        <small>
                                                            {{ $draft->city?->title }}
                                                            @if ($draft->town)
                                                                <br>{{ $draft->town->title }}
                                                            @endif
                                                        </small>
                                                    @else
                                                        <span class="text-muted">{{ __('Not set') }}</span>
                                                    @endif
                                                </td>
                                                <td>
                                                    @if ($draft->projectSize)
                                                        <span class="badge bg-info">{{ $draft->projectSize->name }}</span>
                                                    @else
                                                        <span class="text-muted">-</span>
                                                    @endif
                                                </td>
                                                <td>
                                                    <small>{{ $draft->created_at->format('Y-m-d') }}</small>
                                                </td>
                                                <td class="text-end">
                                                    <div class="btn-group" role="group">
                                                        @can('Edit Inquiries')
                                                            <a href="{{ route('inquiries.edit', $draft->id) }}"
                                                                class="btn btn-sm btn-primary"
                                                                title="{{ __('Continue Editing') }}">
                                                                <i class="fas fa-edit"></i>
                                                            </a>
                                                        @endcan

                                                        @can('Delete Inquiries')
                                                            <button type="button" class="btn btn-sm btn-danger"
                                                                onclick="confirmDelete({{ $draft->id }})"
                                                                title="{{ __('Delete Draft') }}">
                                                                <i class="fas fa-trash"></i>
                                                            </button>
                                                        @endcan
                                                    </div>

                                                    @can('Delete Inquiries')
                                                        <form id="delete-form-{{ $draft->id }}"
                                                            action="{{ route('inquiries.destroy', $draft->id) }}"
                                                            method="POST" class="d-none">
                                                            @csrf
                                                            @method('DELETE')
                                                        </form>
                                                    @endcan
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>

                            <div class="mt-3">
                                {{ $drafts->links() }}
                            </div>
                        @else
                            <div class="text-center py-5">
                                <i class="fas fa-file-alt fa-4x text-muted mb-3"></i>
                                <h4 class="text-muted">{{ __('No drafts found') }}</h4>
                                <p class="text-muted">{{ __('Start creating a new inquiry to save drafts') }}</p>

                                @can('Create Inquiries')
                                    <a href="{{ route('inquiries.create') }}" class="btn btn-primary mt-3">
                                        <i class="fas fa-plus me-2"></i>
                                        {{ __('Create New Inquiry') }}
                                    </a>
                                @endcan
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            function confirmDelete(draftId) {
                if (confirm('{{ __('Are you sure you want to delete this draft? This action cannot be undone.') }}')) {
                    document.getElementById('delete-form-' + draftId).submit();
                }
            }
        </script>
    @endpush
@endsection
