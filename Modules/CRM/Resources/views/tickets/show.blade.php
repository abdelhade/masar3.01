@extends('admin.dashboard')

@section('sidebar')
    @include('components.sidebar.crm')
@endsection

@section('content')
    @include('components.breadcrumb', [
        'title' => __('Ticket Details'),
        'items' => [
            ['label' => __('Dashboard'), 'url' => route('admin.dashboard')],
            ['label' => __('Tickets'), 'url' => route('tickets.index')],
            ['label' => __('Details')],
        ],
    ])

    <div class="row">
        <div class="col-lg-8">
            <!-- Main Ticket Card -->
            <div class="card border-0 shadow-sm mb-3">
                <div class="card-header bg-primary bg-opacity-10 border-0 py-3">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <h4 class="mb-1 fw-bold">{{ $ticket->subject }}</h4>
                            <small class="text-muted">
                                <i class="far fa-clock me-1"></i>
                                {{ __('Created At') }}: {{ $ticket->created_at->format('Y-m-d H:i') }}
                            </small>
                        </div>
                        <span class="badge bg-{{ $ticket->priority === 'high' ? 'danger' : ($ticket->priority === 'medium' ? 'warning' : 'info') }} fs-6">
                            {{ __(ucfirst($ticket->priority)) }}
                        </span>
                    </div>
                </div>
                <div class="card-body">
                    @if (session('message'))
                        <div class="alert alert-success alert-dismissible fade show">
                            <i class="fas fa-check-circle me-2"></i>
                            {{ session('message') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    <div class="row g-3 mb-4">
                        <div class="col-md-6">
                            <div class="d-flex align-items-center p-3 bg-light rounded">
                                <i class="fas fa-user fa-2x text-primary me-3"></i>
                                <div>
                                    <small class="text-muted d-block">{{ __('Client') }}</small>
                                    <strong>{{ $ticket->client->cname ?? 'N/A' }}</strong>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="d-flex align-items-center p-3 bg-light rounded">
                                <i class="fas fa-user-tie fa-2x text-success me-3"></i>
                                <div>
                                    <small class="text-muted d-block">{{ __('Assigned To') }}</small>
                                    <strong>{{ $ticket->assignedTo->name ?? __('Unassigned') }}</strong>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="d-flex align-items-center p-3 bg-light rounded">
                                <i class="fas fa-user-plus fa-2x text-info me-3"></i>
                                <div>
                                    <small class="text-muted d-block">{{ __('Created By') }}</small>
                                    <strong>{{ $ticket->createdBy->name ?? 'N/A' }}</strong>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="d-flex align-items-center p-3 bg-light rounded">
                                <i class="fas fa-info-circle fa-2x text-warning me-3"></i>
                                <div>
                                    <small class="text-muted d-block">{{ __('Status') }}</small>
                                    <span class="badge bg-{{ $ticket->status === 'open' ? 'primary' : ($ticket->status === 'in_progress' ? 'warning' : ($ticket->status === 'resolved' ? 'success' : 'secondary')) }}">
                                        {{ __(ucfirst(str_replace('_', ' ', $ticket->status))) }}
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="border-top pt-3">
                        <h6 class="fw-bold mb-2">{{ __('Description') }}</h6>
                        <p class="text-muted">{{ $ticket->description }}</p>
                    </div>

                    @can('edit Tickets')
                        <div class="d-flex gap-2 mt-4">
                            <a href="{{ route('tickets.edit', $ticket) }}" class="btn btn-primary">
                                <i class="las la-edit me-1"></i> {{ __('Edit Ticket') }}
                            </a>
                            @can('delete Tickets')
                                <form action="{{ route('tickets.destroy', $ticket) }}" method="POST"
                                    onsubmit="return confirm('{{ __('Are you sure you want to delete this ticket?') }}');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-danger">
                                        <i class="las la-trash me-1"></i> {{ __('Delete') }}
                                    </button>
                                </form>
                            @endcan
                        </div>
                    @endcan
                </div>
            </div>

            <!-- Comments Section -->
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-0 py-3">
                    <h5 class="mb-0 fw-bold">
                        <i class="far fa-comments me-2 text-primary"></i>
                        {{ __('Comments') }}
                        <span class="badge bg-secondary ms-2">{{ $ticket->comments->count() }}</span>
                    </h5>
                </div>
                <div class="card-body">
                    @forelse ($ticket->comments as $comment)
                        <div class="d-flex mb-3 pb-3 {{ !$loop->last ? 'border-bottom' : '' }}">
                            <div class="flex-shrink-0">
                                <div class="avatar-circle bg-primary text-white">
                                    {{ substr($comment->user->name, 0, 1) }}
                                </div>
                            </div>
                            <div class="flex-grow-1 ms-3">
                                <div class="d-flex justify-content-between align-items-start">
                                    <div>
                                        <strong>{{ $comment->user->name }}</strong>
                                        <small class="text-muted d-block">{{ $comment->created_at->diffForHumans() }}</small>
                                    </div>
                                </div>
                                <p class="mt-2 mb-0">{{ $comment->comment }}</p>
                            </div>
                        </div>
                    @empty
                        <div class="text-center py-4">
                            <i class="far fa-comment-dots text-muted" style="font-size: 3rem; opacity: 0.3;"></i>
                            <p class="text-muted mt-2">{{ __('No comments yet') }}</p>
                        </div>
                    @endforelse

                    @can('edit Tickets')
                        <hr>
                        <form action="{{ route('tickets.addComment', $ticket) }}" method="POST">
                            @csrf
                            <div class="mb-3">
                                <label for="comment" class="form-label fw-bold">{{ __('Add Comment') }}</label>
                                <textarea name="comment" id="comment" class="form-control" rows="3" 
                                          placeholder="{{ __('Write your comment here...') }}" required></textarea>
                                @error('comment')
                                    <small class="text-danger">{{ $message }}</small>
                                @enderror
                            </div>
                            <button type="submit" class="btn btn-primary">
                                <i class="las la-comment me-1"></i> {{ __('Add Comment') }}
                            </button>
                        </form>
                    @endcan
                </div>
            </div>
        </div>

        <!-- Sidebar -->
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-0 py-3">
                    <h5 class="mb-0 fw-bold">
                        <i class="fas fa-cog me-2 text-primary"></i>
                        {{ __('Update Status') }}
                    </h5>
                </div>
                <div class="card-body">
                    @can('edit Tickets')
                        <form action="{{ route('tickets.updateStatus') }}" method="POST">
                            @csrf
                            <input type="hidden" name="ticket_id" value="{{ $ticket->id }}">
                            <div class="mb-3">
                                <label for="new_status" class="form-label">{{ __('New Status') }}</label>
                                <select name="new_status" id="new_status" class="form-select" required>
                                    <option value="open" {{ $ticket->status === 'open' ? 'selected' : '' }}>
                                        {{ __('Open') }}
                                    </option>
                                    <option value="in_progress" {{ $ticket->status === 'in_progress' ? 'selected' : '' }}>
                                        {{ __('In Progress') }}
                                    </option>
                                    <option value="resolved" {{ $ticket->status === 'resolved' ? 'selected' : '' }}>
                                        {{ __('Resolved') }}
                                    </option>
                                    <option value="closed" {{ $ticket->status === 'closed' ? 'selected' : '' }}>
                                        {{ __('Closed') }}
                                    </option>
                                </select>
                            </div>
                            <button type="submit" class="btn btn-primary w-100">
                                <i class="las la-sync me-1"></i> {{ __('Update Status') }}
                            </button>
                        </form>
                    @endcan
                </div>
            </div>
        </div>
    </div>

    <style>
        .avatar-circle {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            font-size: 1.2rem;
        }
        .bg-opacity-10 {
            --bs-bg-opacity: 0.1;
        }
    </style>
@endsection
