@extends('progress::layouts.daily-progress')


@section('content')
<div class="row">
    <div class="col-12">
        <div class="page-title-box d-sm-flex align-items-center justify-content-between">
            <h4 class="mb-sm-0">{{ __('general.issue_information') }} #{{ $issue->id }}</h4>
            <div class="page-title-right">
                <ol class="breadcrumb m-0">
                    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">{{ __('general.dashboard') }}</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('issues.index') }}">{{ __('general.issues') }}</a></li>
                    <li class="breadcrumb-item active">{{ $issue->title }}</li>
                </ol>
            </div>
        </div>
    </div>
</div>
<!--  -->

<div class="row">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-body">
                <div class="d-flex align-items-center mb-4">
                    <h5 class="card-title flex-grow-1 mb-0">{{ $issue->title }}</h5>
                    <div class="flex-shrink-0">
                        @can('edit progress-issues')
                        <a href="{{ route('issues.edit', $issue->id) }}" class="btn btn-soft-primary btn-sm"><i class="las la-pen"></i> {{ __('general.edit') }}</a>
                        @endcan
                        <a href="{{ route('issues.index') }}" class="btn btn-light btn-sm"><i class="las la-arrow-left"></i> {{ __('general.back') }}</a>
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-6">
                        <p class="text-muted mb-1">{{ __('general.project') }}:</p>
                        <h6 class="fs-14">{{ $issue->project->name ?? 'N/A' }}</h6>
                    </div>
                     <div class="col-md-6">
                        <p class="text-muted mb-1">{{ __('general.module') }}:</p>
                        <h6 class="fs-14">{{ $issue->module ?? 'N/A' }}</h6>
                    </div>
                </div>
                 <div class="row mb-3">
                    <div class="col-md-6">
                        <p class="text-muted mb-1">{{ __('general.reporter') }}:</p>
                        <h6 class="fs-14">{{ $issue->reporter->name ?? 'N/A' }}</h6>
                    </div>
                    <div class="col-md-6">
                         <p class="text-muted mb-1">{{ __('general.created_at') }}:</p>
                        <h6 class="fs-14">{{ $issue->created_at->format('d M, Y h:i A') }}</h6>
                    </div>
                </div>

                <div class="mt-4">
                    <h6 class="fw-semibold text-uppercase fs-12">{{ __('general.description') }}</h6>
                    <p class="text-muted">{!! nl2br(e($issue->description)) !!}</p>
                </div>

                @if($issue->reproduce_steps)
                <div class="mt-4">
                    <h6 class="fw-semibold text-uppercase fs-12">{{ __('general.reproduce_steps') }}</h6>
                    <div class="text-muted bg-light p-3 rounded">
                        {!! nl2br(e($issue->reproduce_steps)) !!}
                    </div>
                </div>
                @endif

                <div class="mt-4">
                    <h6 class="fw-semibold text-uppercase fs-12">{{ __('general.attachments') }}</h6>
                    @if($issue->attachments->count() > 0)
                    <div class="row g-3">
                        @foreach($issue->attachments as $attachment)
                        <div class="col-xxl-4 col-lg-6">
                            <div class="border rounded border-dashed p-2">
                                <div class="d-flex align-items-center">
                                    <div class="flex-shrink-0 me-3">
                                        <div class="avatar-sm">
                                            <div class="avatar-title bg-light text-secondary rounded fs-24">
                                                <i class="las la-file"></i>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="flex-grow-1 overflow-hidden">
                                        <h5 class="fs-13 mb-1"><a href="{{ asset('storage/' . $attachment->file_path) }}" target="_blank" class="text-body text-truncate d-block">{{ $attachment->file_name }}</a></h5>
                                        <div class="text-muted fs-11">{{ number_format($attachment->file_size / 1024, 2) }} KB</div>
                                    </div>
                                    <div class="flex-shrink-0 ms-2">
                                        <a href="{{ asset('storage/' . $attachment->file_path) }}" download class="btn btn-icon btn-sm btn-ghost-primary"><i class="las la-download"></i></a>
                                    </div>
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                    @else
                    <p class="text-muted">{{ __('general.no_records_found') }}</p>
                    @endif
                </div>
                
                {{-- Comments Section --}}
                <div class="mt-5">
                    <h5 class="card-title mb-4">{{ __('general.comments') }}</h5>
                    <div class="vstack gap-3">
                        @forelse($issue->comments as $comment)
                        <div class="d-flex mb-4">
                            <div class="flex-shrink-0 me-3">
                                <div class="avatar-xs">
                                     <span class="avatar-title rounded-circle bg-soft-primary text-primary fs-10">
                                        {{ substr($comment->user->name ?? 'U', 0, 2) }}
                                    </span>
                                </div>
                            </div>
                            <div class="flex-grow-1">
                                <h5 class="fs-14 mb-1">{{ $comment->user->name ?? 'Unknown' }} <small class="text-muted ms-1">{{ $comment->created_at->diffForHumans() }}</small></h5>
                                <p class="text-muted">{{ $comment->comment }}</p>
                                
                                @if(auth()->id() == $comment->user_id)
                                <form action="{{ route('issues.comments.destroy', $comment->id) }}" method="POST" class="d-inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-danger border-0 bg-transparent p-0 fs-12" onclick="return confirm('{{ __('general.confirm_delete') }}')">{{ __('general.delete') }}</button>
                                </form>
                                @endif
                            </div>
                        </div>
                        @empty
                        <p class="text-muted text-center">{{ __('general.no_comments') }}</p>
                        @endforelse
                    </div>

                    <form action="{{ route('issues.comments.store', $issue->id) }}" method="POST" class="mt-4">
                        @csrf
                        <div class="row g-3">
                            <div class="col-12">
                                <label class="form-label">{{ __('general.add_comment') }}</label>
                                <textarea name="comment" class="form-control bg-light border-light" rows="3" placeholder="{{ __('general.add_comment') }}..." required></textarea>
                            </div>
                            <div class="col-12 text-end">
                                <button type="submit" class="btn btn-primary">{{ __('general.add_comment') }}</button>
                            </div>
                        </div>
                    </form>
                </div>

            </div>
        </div>
    </div>
    
    <div class="col-lg-4">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">{{ __('general.issue_information') }}</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-borderless mb-0">
                        <tbody>
                            <tr>
                                <th class="ps-0" scope="row">{{ __('general.issue_id') }}:</th>
                                <td class="text-muted">#{{ $issue->id }}</td>
                            </tr>
                            <tr>
                                <th class="ps-0" scope="row">{{ __('general.project') }}:</th>
                                <td class="text-muted">{{ $issue->project->name ?? 'N/A' }}</td>
                            </tr>
                            <tr>
                                <th class="ps-0" scope="row">{{ __('general.status') }}:</th>
                                <td>
                                     @php
                                        $statusClass = match($issue->status) {
                                            'New' => 'bg-primary',
                                            'In Progress' => 'bg-info',
                                            'Testing' => 'bg-warning',
                                            'Closed' => 'bg-success',
                                            default => 'bg-secondary'
                                        };
                                    @endphp
                                    <span class="badge {{ $statusClass }}">{{ $issue->status }}</span>
                                </td>
                            </tr>
                            <tr>
                                <th class="ps-0" scope="row">{{ __('general.priority') }}:</th>
                                <td>
                                    @php
                                        $priorityClass = match($issue->priority) {
                                            'Low' => 'bg-info',
                                            'Medium' => 'bg-warning',
                                            'High' => 'bg-danger',
                                            'Urgent' => 'bg-dark',
                                            default => 'bg-secondary'
                                        };
                                    @endphp
                                    <span class="badge {{ $priorityClass }}">{{ $issue->priority }}</span>
                                </td>
                            </tr>
                            <tr>
                                <th class="ps-0" scope="row">{{ __('general.assigned_to') }}:</th>
                                <td class="text-muted">
                                    <div class="d-flex align-items-center">
                                       @if($issue->assignee)
                                            <div class="avatar-xs me-2">
                                                <span class="avatar-title rounded-circle bg-soft-primary text-primary fs-10">
                                                    {{ substr($issue->assignee->name, 0, 2) }}
                                                </span>
                                            </div>
                                            {{ $issue->assignee->name }}
                                        @else
                                            {{ __('general.unassigned') }}
                                        @endif
                                    </div>
                                </td>
                            </tr>
                            <tr>
                                <th class="ps-0" scope="row">{{ __('general.deadline') }}:</th>
                                <td class="text-muted">{{ $issue->deadline ? date('d M, Y', strtotime($issue->deadline)) : 'N/A' }}</td>
                            </tr>
                            <tr>
                                <th class="ps-0" scope="row">{{ __('general.created') }}:</th>
                                <td class="text-muted">{{ $issue->created_at->diffForHumans() }}</td>
                            </tr>
                             <tr>
                                <th class="ps-0" scope="row">{{ __('general.last_updated') }}:</th>
                                <td class="text-muted">{{ $issue->updated_at->diffForHumans() }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
