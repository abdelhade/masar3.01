@extends('progress::layouts.daily-progress')

{{-- Sidebar is now handled by the layout itself --}}

@section('title', __('general.edit_daily_progress'))

@section('content')
<div class="container container-sm" style="max-width: 800px;">
    <!-- Header -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-primary text-white py-3 d-flex justify-content-between align-items-center">
             <h5 class="mb-0 text-white fw-bold"><i class="fas fa-edit me-2"></i> {{ __('general.edit_daily_progress') }}</h5>
             <a href="{{ route('daily_progress.index') }}" class="btn btn-light btn-sm fw-bold text-primary">
                <i class="fas fa-arrow-left me-1"></i> {{ __('general.back_to_list') }}
            </a>
        </div>
        <div class="card-body p-4">
            <form action="{{ route('daily_progress.update', $dailyProgress) }}" method="POST">
                @csrf
                @method('PUT')

                <!-- Read-Only Context (Gray Box) -->
                <div class="bg-light rounded p-3 mb-4 border">
                    <div class="row g-3">
                        <div class="col-md-6 border-end">
                            <label class="small text-uppercase text-muted fw-bold mb-1">{{ __('general.project') }}</label>
                            <div class="d-flex align-items-center">
                                <i class="fas fa-project-diagram text-muted me-2"></i>
                                <span class="fw-bold text-dark">{{ $dailyProgress->project->name }}</span>
                            </div>
                        </div>
                        <div class="col-md-6 px-3">
                            <label class="small text-uppercase text-muted fw-bold mb-1">{{ __('general.work_item') }}</label>
                            <div class="d-flex align-items-center">
                                <i class="fas fa-tasks text-muted me-2"></i>
                                <span class="fw-bold text-dark">{{ $dailyProgress->projectItem->workItem->name }}</span>
                            </div>
                        </div>
                    </div>
                    <hr class="my-3 text-muted opacity-25">
                     <div class="row g-3">
                        <div class="col-md-6 border-end">
                             <label class="small text-uppercase text-muted fw-bold mb-1">{{ __('general.employee') }}</label>
                             <div class="d-flex align-items-center">
                                @if($dailyProgress->employee)
                                    <div class="avatar-xs rounded-circle bg-soft-primary text-primary d-flex align-items-center justify-content-center me-2 fw-bold" style="width: 24px; height: 24px;">
                                        {{ substr($dailyProgress->employee->name, 0, 1) }}
                                    </div>
                                    <span class="text-dark">{{ $dailyProgress->employee->name }}</span>
                                @else
                                    <div class="avatar-xs rounded-circle bg-light text-muted d-flex align-items-center justify-content-center me-2 fw-bold" style="width: 24px; height: 24px;">
                                        <i class="fas fa-user-slash" style="font-size: 0.7rem;"></i>
                                    </div>
                                    <span class="text-muted">{{ __('general.no_employee') }}</span>
                                @endif
                            </div>
                        </div>
                        <div class="col-md-6 px-3">
                             <label class="small text-uppercase text-muted fw-bold mb-1">{{ __('general.current_completion') }}</label>
                             <span class="badge bg-success rounded-pill">{{ $dailyProgress->completion_percentage }}%</span>
                        </div>
                    </div>
                </div>

                <!-- Editable Fields -->
                <div class="row g-4 mb-4">
                    <div class="col-md-6">
                        <label class="form-label fw-bold">{{ __('general.date') }}</label>
                        <div class="input-group">
                            <span class="input-group-text bg-white border-end-0"><i class="far fa-calendar-alt text-muted"></i></span>
                            <input type="date" name="progress_date" class="form-control border-start-0 ps-0"
                                   value="{{ $dailyProgress->progress_date->format('Y-m-d') }}" required>
                        </div>
                    </div>

                    <div class="col-md-6" 
                         x-data="{ 
                            qty: {{ $dailyProgress->quantity }}, 
                            max: {{ $dailyProgress->projectItem->total_quantity - ($dailyProgress->projectItem->completed_quantity - $dailyProgress->quantity) }} 
                         }">
                        <label class="form-label fw-bold">{{ __('general.quantity') }}</label>
                        <div class="input-group">
                            <span class="input-group-text bg-white border-end-0"><i class="fas fa-sort-numeric-up text-muted"></i></span>
                            <input type="number" name="quantity" step="0.01" min="0" 
                                   x-model="qty"
                                   class="form-control border-start-0 ps-0 fw-bold text-primary fs-5"
                                   required>
                        </div>
                        <div class="d-flex justify-content-between align-items-start mt-1">
                            <small class="text-muted">{{ __('general.unit') }}: {{ $dailyProgress->projectItem->workItem->unit ?? '-' }}</small>
                            <template x-if="max > 0 && Number(qty) > max">
                                <small class="text-danger fw-bold animate__animated animate__fadeIn text-end ms-2">
                                    <i class="fas fa-exclamation-triangle me-1"></i>
                                    <span x-text="'{{ __('general.quantity_exceeds_remaining', ['remaining' => 'PLACEHOLDER']) }}'.replace('PLACEHOLDER', parseFloat(max.toFixed(2)))"></span>
                                </small>
                            </template>
                        </div>
                    </div>
                </div>

                <div class="mb-4">
                    <label class="form-label fw-bold">{{ __('general.notes') }}</label>
                    <textarea name="notes" class="form-control" rows="3" placeholder="{{ __('general.add_notes_here') }}...">{{ $dailyProgress->notes }}</textarea>
                </div>

                <!-- Actions -->
                <div class="d-flex justify-content-end gap-2 pt-3 border-top">
                     <a href="{{ route('daily_progress.index') }}" class="btn btn-light px-4">
                        {{ __('general.cancel') }}
                    </a>
                    <button type="submit" class="btn btn-primary px-5 shadow-sm">
                        <i class="fas fa-save me-2"></i> {{ __('general.save_changes') }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
