
<div class="row g-4 mb-5">
    <div class="col-12">
        <div class="stat-card">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h4 class="gradient-text fw-bold mb-0">
                    <i class="fas fa-list-check me-2"></i>{{ __('general.work_items_progress') }}
                </h4>
                <span class="badge bg-primary">{{ $totalItems }} {{ __('general.items') }}</span>
            </div>

            <div class="row">
                @foreach ($project->items as $item)
                    @php
                        $completionPercentage =
                            $item->total_quantity > 0
                                ? ($item->completed_quantity / $item->total_quantity) * 100
                                : 0;
                    @endphp
                    <div class="col-md-6 mb-4">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <div>
                                <div class="fw-bold">{{ $item->workItem->name }}</div>
                                @if($item->workItem->category ?? null)
                                    <small class="text-muted d-block"><i class="fas fa-folder me-1"></i>{{ $item->workItem->category->name }}</small>
                                @endif
                                @if($item->workItem->unit ?? null)
                                    <small class="text-muted d-block"><i class="fas fa-ruler me-1"></i>{{ $item->workItem->unit }}</small>
                                @endif
                                @if($item->notes ?? null)
                                    <small class="text-muted d-block"><i class="fas fa-sticky-note me-1"></i>{{ Str::limit($item->notes, 30) }}</small>
                                @endif
                            </div>
                            <span
                                class="fw-bold text-primary">{{ number_format($completionPercentage, 1) }}%</span>
                        </div>
                        <div class="progress" style="height: 12px; border-radius: 10px;">
                            <div class="progress-bar
                                @if ($completionPercentage >= 80) bg-success
                                @elseif($completionPercentage >= 50) bg-primary
                                @elseif($completionPercentage >= 30) bg-warning
                                @else bg-danger @endif"
                                role="progressbar" style="width: {{ $completionPercentage }}%">
                            </div>
                        </div>
                        <div class="d-flex justify-content-between mt-2 text-muted small">
                            <span>{{ number_format($item->completed_quantity) }}
                                {{ $item->workItem->unit }}</span>
                            <span>{{ number_format($item->remaining_quantity) }}
                                {{ $item->workItem->unit }}</span>
                            <span>{{ number_format($item->total_quantity) }}
                                {{ $item->workItem->unit }}</span>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
</div>

