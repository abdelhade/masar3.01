@extends('progress::layouts.app')

@section('title', __('general.edit_daily_progress'))

@section('content')
<style>
    :root {
        --primary-color: #2c7be5;
        --light-bg: #f8f9fa;
        --card-shadow: 0 0.5rem 1.5rem rgba(0, 0, 0, 0.08);
    }

    .main-card { border: none; border-radius: 0.75rem; box-shadow: var(--card-shadow); margin-top: 2rem; }
    .card-header { border-radius: 0.75rem 0.75rem 0 0 !important; padding: 1.2rem 1.5rem; background: linear-gradient(120deg, #4a3ef6 0%, #3936ff 100%) !important; border: none; }
    .card-body { padding: 2rem; }
    .form-label { font-weight: 600; margin-bottom: 0.5rem; color: #344050; }
    .form-control { border-radius: 0.5rem; padding: 0.75rem 1rem; border: 1px solid #e3ebf6; background-color: #fff; }
    .form-control:focus { border-color: var(--primary-color); box-shadow: 0 0 0 0.25rem rgba(44,123,229,0.15); }
    .input-group-text { background-color: #f5f7f9; border-radius: 0.5rem 0 0 0.5rem; border: 1px solid #e3ebf6; }
    .btn-primary { background: linear-gradient(120deg, #2c7be5 0%, #1a56ce 100%); border: none; border-radius: 0.5rem; padding: 0.75rem 2rem; font-weight: 600; }
    .btn-secondary { border-radius: 0.5rem; padding: 0.75rem 2rem; font-weight: 600; }
    .info-badge { display: inline-block; padding: 0.25rem 0.75rem; border-radius: 0.375rem; font-size: 0.875rem; font-weight: 600; }
</style>

<div class="container">
    <div class="main-card card">
        <div class="card-header text-white">
            <h5 class="mb-0"><i class="fas fa-calendar-edit me-2"></i> {{ __('general.edit_daily_progress') }}</h5>
        </div>
        <div class="card-body">
            
            @if(session('warning'))
                <div class="alert alert-warning alert-dismissible fade show" role="alert">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    {!! session('warning') !!}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            <form action="{{ route('progress.daily-progress.update', $dailyProgress) }}" method="POST" id="editForm">
                @csrf
                @method('PUT')

                <div class="row mb-4">
                    
                    <div class="col-md-4 mb-3">
                        <label class="form-label">{{ __('general.project') }}</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-project-diagram"></i></span>
                            <input type="text" class="form-control" value="{{ $dailyProgress->project->name }}" readonly>
                        </div>
                    </div>

                    
                    <div class="col-md-4 mb-3">
                        <label class="form-label">{{ __('general.work_item') }}</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-tasks"></i></span>
                            <div class="form-control" style="background-color: #f8f9fa;">
                                <div class="fw-semibold">{{ $dailyProgress->projectItem->workItem->name }}</div>
                                @if($dailyProgress->projectItem->workItem->category ?? null)
                                    <small class="text-muted d-block"><i class="fas fa-folder me-1"></i>{{ $dailyProgress->projectItem->workItem->category->name }}</small>
                                @endif
                                @if($dailyProgress->projectItem->workItem->unit ?? null)
                                    <small class="text-muted d-block"><i class="fas fa-ruler me-1"></i>{{ $dailyProgress->projectItem->workItem->unit }}</small>
                                @endif
                                @if($dailyProgress->projectItem->is_measurable ?? false)
                                    <small class="d-block mt-1">
                                        <span class="badge" style="background: linear-gradient(135deg, #28a745 0%, #20c997 100%); color: white;" title="قابل للقياس">
                                            <i class="fas fa-check-circle me-1"></i>قابل للقياس
                                        </span>
                                    </small>
                                @else
                                    <small class="d-block mt-1">
                                        <span class="badge" style="background: linear-gradient(135deg, #6c757d 0%, #495057 100%); color: white;" title="غير قابل للقياس">
                                            <i class="fas fa-times-circle me-1"></i>غير قابل للقياس
                                        </span>
                                    </small>
                                @endif
                                @if($dailyProgress->projectItem->notes ?? null)
                                    <small class="text-muted d-block"><i class="fas fa-sticky-note me-1"></i>{{ Str::limit($dailyProgress->projectItem->notes, 50) }}</small>
                                @endif
                            </div>
                        </div>
                    </div>

                    
                    <div class="col-md-4 mb-3">
                        <label class="form-label">{{ __('general.employee') }}</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-user"></i></span>
                            <input type="text" class="form-control" value="{{ $dailyProgress->employee->name }}" readonly>
                        </div>
                    </div>
                </div>

                
                

                <div class="row mb-4">
                    
                    <div class="col-md-4 mb-3">
                        <label class="form-label">{{ __('general.date') }}</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-calendar"></i></span>
                            <input type="date" name="progress_date" class="form-control"
                                   value="{{ $dailyProgress->progress_date->format('Y-m-d') }}" required>
                        </div>
                    </div>

                    
                    <div class="col-md-4 mb-3">
                        <label class="form-label">{{ __('general.quantity') }}</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-sort-numeric-up"></i></span>
                            <input type="number"
                                   name="quantity"
                                   id="quantityInput"
                                   step="0.01"
                                   min="0"
                                   class="form-control"
                                   value="{{ $dailyProgress->quantity }}"
                                   data-total="{{ $dailyProgress->projectItem->total_quantity }}"
                                   data-completed="{{ $dailyProgress->projectItem->completed_quantity - $dailyProgress->quantity }}"
                                   data-old-qty="{{ $dailyProgress->quantity }}"
                                   required>
                        </div>
                        <small class="text-muted" id="warningText" style="display:none;">
                            <i class="fas fa-exclamation-triangle text-warning"></i>
                            <span id="warningMessage"></span>
                        </small>
                    </div>
                </div>

                
                <div class="mb-4">
                    <label class="form-label">{{ __('general.notes') }}</label>
                    <textarea name="notes" class="form-control" rows="3">{{ $dailyProgress->notes }}</textarea>
                </div>

                <div class="text-end">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save me-2"></i> {{ __('general.save_changes') }}
                    </button>
                    <a href="{{ route('progress.daily-progress.index') }}" class="btn btn-secondary ms-2">
                        <i class="fas fa-times me-2"></i> {{ __('general.cancel') }}
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
    $(document).ready(function() {
        const quantityInput = $('#quantityInput');
        const totalQty = parseFloat(quantityInput.data('total')) || 0;
        const completedQty = parseFloat(quantityInput.data('completed')) || 0;
        const oldQty = parseFloat(quantityInput.data('old-qty')) || 0;

        // التحقق من الكمية المدخلة
        quantityInput.on('input', function() {
            const newQty = parseFloat($(this).val()) || 0;
            const newTotalCompleted = completedQty + newQty;
            const remaining = totalQty - completedQty;

            // تحديث العرض
            $('#completedQty').text((completedQty + newQty).toFixed(2));
            $('#remainingQty').text((totalQty - newTotalCompleted).toFixed(2));

            // إظهار التحذير إذا تجاوزت الكمية المتبقية
            if (newQty > remaining && remaining > 0) {
                $(this).addClass('border-warning');
                $('#warningText').show();
                $('#warningMessage').text(`الكمية المدخلة (${newQty.toFixed(2)}) تتجاوز الكمية المتبقية (${remaining.toFixed(2)})`);
            } else {
                $(this).removeClass('border-warning');
                $('#warningText').hide();
            }
        });

        // تحديد النص عند التركيز
        quantityInput.on('focus', function() {
            $(this).select();
        });
    });
</script>
@endpush
@endsection
