
<div class="col-lg-6">
    <div class="stat-card">
        <h4 class="gradient-text fw-bold mb-4">
            <i class="fas fa-history me-2"></i>{{ __('general.daily_progress_record') }}
        </h4>

        <div class="timeline">
            @foreach ($recentProgress as $date => $progresses)
                <div class="timeline-item">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span class="fw-bold">{{ \Carbon\Carbon::parse($date)->format('d/m/Y') }}</span>
                        <span class="badge bg-light">
                            {{ $progresses->sum('quantity') }} {{ __('general.units') }}
                        </span>
                    </div>
                    @foreach ($progresses as $progress)
                        <div class="d-flex align-items-center mb-2 p-2 bg-light rounded">
                            <div class="flex-shrink-0">
                                <img src="https://ui-avatars.com/api/?name={{ urlencode($progress->employee->name) }}&background=4f46e5&color=fff"
                                    alt="{{ $progress->employee->name }}" class="employee-avatar">
                            </div>
                            <div class="flex-grow-1 ms-3">
                                <div class="fw-medium">{{ $progress->employee->name }}</div>
                                <div class="fw-semibold">{{ $progress->projectItem->workItem->name }}</div>
                                @if($progress->projectItem->workItem->category ?? null)
                                    <small class="text-muted d-block"><i class="fas fa-folder me-1"></i>{{ $progress->projectItem->workItem->category->name }}</small>
                                @endif
                                @if($progress->projectItem->notes ?? null)
                                    <small class="text-muted d-block"><i class="fas fa-sticky-note me-1"></i>{{ Str::limit($progress->projectItem->notes, 25) }}</small>
                                @endif
                            </div>
                            <div class="text-end">
                                <div class="fw-bold text-primary">{{ $progress->quantity }}</div>
                                <small
                                    class="text-muted">{{ $progress->projectItem->workItem->unit }}</small>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endforeach
        </div>
    </div>
</div>

