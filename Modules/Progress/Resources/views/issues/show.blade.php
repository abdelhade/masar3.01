@extends('progress::layouts.app')

@section('breadcrumb')
    <li class="breadcrumb-item">
        <a href="{{ route('progress.dashboard') }}" class="text-muted text-decoration-none">{{ __('general.dashboard') }}</a>
    </li>
    <li class="breadcrumb-item">
        <a href="{{ route('progress.issues.index') }}" class="text-muted text-decoration-none">{{ __('general.issues') }}</a>
    </li>
@endsection

@section('title', $issue->title)

@push('styles')
<style>
    .issue-header {
        border-bottom: 2px solid #e9ecef;
        padding-bottom: 1.5rem;
        margin-bottom: 2rem;
    }
    .comment-card {
        border-left: 3px solid #007bff;
        margin-bottom: 1rem;
    }
    .attachment-item {
        padding: 0.75rem;
        border: 1px solid #dee2e6;
        border-radius: 8px;
        margin-bottom: 0.5rem;
    }
</style>
@endpush

@section('content')
<div class="container-fluid">
    <!-- Issue Header -->
    <div class="issue-header">
        <div class="d-flex justify-content-between align-items-start mb-3">
            <div>
                <h3 class="mb-2">{{ $issue->title }}</h3>
                <div class="d-flex gap-2 flex-wrap">
                    <span class="badge bg-{{ $issue->priority_color }}">{{ $issue->priority }}</span>
                    <span class="badge bg-{{ $issue->status_color }}">{{ $issue->status }}</span>
                    @if($issue->isOverdue())
                        <span class="badge bg-danger">{{ __('general.overdue') }}</span>
                    @endif
                </div>
            </div>
            <div class="btn-group">
                @can('edit progress-issues')
                <a href="{{ route('progress.issues.edit', $issue) }}" class="btn btn-warning">
                    <i class="fas fa-edit me-1"></i>{{ __('general.edit') }}
                </a>
                @endcan
                <a href="{{ route('progress.issues.index') }}" class="btn btn-secondary">
                    <i class="fas fa-arrow-left me-1"></i>{{ __('general.back') }}
                </a>
            </div>
        </div>

        <div class="row g-4">
            <div class="col-md-3">
                <strong><i class="fas fa-project-diagram me-2"></i>{{ __('general.project') }}:</strong>
                <p class="mb-0">{{ $issue->project->name ?? 'N/A' }}</p>
            </div>
            <div class="col-md-3">
                <strong><i class="fas fa-user me-2"></i>{{ __('general.reporter') }}:</strong>
                <p class="mb-0">{{ $issue->reporter->name ?? 'N/A' }}</p>
            </div>
            <div class="col-md-3">
                <strong><i class="fas fa-user-check me-2"></i>{{ __('general.assigned_to') }}:</strong>
                <p class="mb-0">{{ $issue->assignedUser->name ?? __('general.unassigned') }}</p>
            </div>
            <div class="col-md-3">
                <strong><i class="fas fa-calendar me-2"></i>{{ __('general.deadline') }}:</strong>
                <p class="mb-0">
                    @if($issue->due_date)
                        {{ $issue->due_date->format('Y-m-d') }}
                    @else
                        <span class="text-muted">-</span>
                    @endif
                </p>
            </div>
            @if($issue->module)
            <div class="col-md-3">
                <strong><i class="fas fa-puzzle-piece me-2"></i>{{ __('general.module') }}:</strong>
                <p class="mb-0">{{ $issue->module }}</p>
            </div>
            @endif
            <div class="col-md-3">
                <strong><i class="fas fa-clock me-2"></i>{{ __('general.created_at') }}:</strong>
                <p class="mb-0">{{ $issue->created_at->format('Y-m-d H:i') }}</p>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Main Content -->
        <div class="col-lg-8">
            <!-- Description -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-align-left me-2"></i>{{ __('general.description') }}</h5>
                </div>
                <div class="card-body">
                    <p>{{ $issue->description ?: __('general.no_description') }}</p>
                </div>
            </div>

            <!-- Reproduce Steps -->
            @if($issue->reproduce_steps)
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-list-ol me-2"></i>{{ __('general.reproduce_steps') }}</h5>
                </div>
                <div class="card-body">
                    <pre style="white-space: pre-wrap;">{{ $issue->reproduce_steps }}</pre>
                </div>
            </div>
            @endif

            <!-- Attachments -->
            @if($issue->attachments->count() > 0)
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-paperclip me-2"></i>{{ __('general.attachments') }} ({{ $issue->attachments->count() }})</h5>
                </div>
                <div class="card-body">
                    @foreach($issue->attachments as $attachment)
                    <div class="attachment-item d-flex justify-content-between align-items-center">
                        <div>
                            <i class="fas fa-file me-2"></i>
                            <a href="{{ route('progress.issues.attachments.download', $attachment) }}" target="_blank" class="text-decoration-none">
                                {{ $attachment->file_name }}
                            </a>
                            <small class="text-muted ms-2">({{ $attachment->human_readable_size }})</small>
                        </div>
                        @if($attachment->user_id == Auth::id() || $issue->reporter_id == Auth::id())
                        <form action="{{ route('progress.issues.attachments.destroy', $attachment) }}" method="POST" class="d-inline">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('{{ __('general.are_you_sure') }}')">
                                <i class="fas fa-trash"></i>
                            </button>
                        </form>
                        @endif
                    </div>
                    @endforeach
                </div>
            </div>
            @endif

            <!-- Comments Section -->
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-comments me-2"></i>{{ __('general.comments') }} ({{ $issue->comments->count() }})</h5>
                </div>
                <div class="card-body">
                    <!-- Add Comment Form -->
                    <form action="{{ route('progress.issues.comments.store', $issue) }}" method="POST" class="mb-4">
                        @csrf
                        <div class="mb-3">
                            <textarea name="comment" class="form-control @error('comment') is-invalid @enderror" 
                                      rows="3" placeholder="{{ __('general.add_comment') }}" required></textarea>
                            @error('comment')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-paper-plane me-1"></i>{{ __('general.add_comment') }}
                        </button>
                    </form>

                    <!-- Comments List -->
                    <div class="comments-list">
                        @forelse($issue->comments as $comment)
                        <div class="comment-card card mb-3">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-start mb-2">
                                    <div>
                                        <strong>{{ $comment->user->name }}</strong>
                                        <small class="text-muted ms-2">{{ $comment->created_at->diffForHumans() }}</small>
                                    </div>
                                    @if($comment->user_id == Auth::id() || $issue->reporter_id == Auth::id())
                                    <form action="{{ route('progress.issues.comments.destroy', $comment) }}" method="POST" class="d-inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('{{ __('general.are_you_sure') }}')">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                    @endif
                                </div>
                                <p class="mb-0">{{ $comment->comment }}</p>
                            </div>
                        </div>
                        @empty
                        <p class="text-muted text-center py-3">{{ __('general.no_comments') }}</p>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>

        <!-- Sidebar -->
        <div class="col-lg-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-info-circle me-2"></i>{{ __('general.issue_info') }}</h5>
                </div>
                <div class="card-body">
                    <p><strong>{{ __('general.issue_id') }}:</strong> #{{ $issue->id }}</p>
                    <p><strong>{{ __('general.created') }}:</strong> {{ $issue->created_at->format('Y-m-d H:i') }}</p>
                    <p><strong>{{ __('general.last_updated') }}:</strong> {{ $issue->updated_at->format('Y-m-d H:i') }}</p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

