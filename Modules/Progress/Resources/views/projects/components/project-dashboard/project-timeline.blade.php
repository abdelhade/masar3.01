<div class="card border-0 shadow-sm mb-4" x-data="{ expanded: false }">
    <div class="card-header bg-transparent py-3 d-flex justify-content-between align-items-center"
         @click="expanded = !expanded" 
         style="cursor: pointer;">
        <h5 class="card-title mb-0 fw-bold text-primary">
            <i class="fas fa-stream me-2"></i>{{ __('general.timeline') }}
        </h5>
        <i class="fas" :class="expanded ? 'fa-chevron-up' : 'fa-chevron-down'"></i>
    </div>
    <div class="card-body" x-show="expanded" x-collapse style="display: none;">
        <div class="timeline-container ps-2">
            <!-- Start Date -->
            <div class="timeline-item pb-4 position-relative border-start border-2 border-primary ps-4">
                <div class="position-absolute top-0 start-0 translate-middle bg-primary rounded-circle" style="width: 12px; height: 12px; border: 2px solid #fff;"></div>
                <h6 class="fw-bold mb-1 text-primary">{{ __('general.start_date') }}</h6>
                <p class="text-muted small mb-0">
                    <i class="far fa-calendar me-1"></i>
                    {{ $project->start_date ? \Carbon\Carbon::parse($project->start_date)->format('d M, Y') : 'N/A' }}
                </p>
            </div>

            <!-- Current Date Marker -->
             <div class="timeline-item pb-4 position-relative border-start border-2 border-primary ps-4">
                <div class="position-absolute top-0 start-0 translate-middle bg-info rounded-circle" style="width: 12px; height: 12px; border: 2px solid #fff;"></div>
                <h6 class="fw-bold mb-1 text-info">{{ __('general.today') }}</h6>
                <p class="text-muted small mb-0">
                    <i class="far fa-clock me-1"></i>
                    {{ now()->format('d M, Y') }}
                </p>
                <div class="mt-2">
                    <span class="badge bg-light text-dark border">{{ floor($daysPassed)  }} {{ __('general.days_passed') }}</span>
                </div>
            </div>

            <!-- End Date -->
            <div class="timeline-item position-relative ps-4">
                <div class="position-absolute top-0 start-0 translate-middle bg-success rounded-circle" style="width: 12px; height: 12px; border: 2px solid #fff;"></div>
                <h6 class="fw-bold mb-1 text-success">{{ __('general.end_date') }}</h6>
                <p class="text-muted small mb-0">
                    <i class="far fa-calendar-check me-1"></i>
                    {{ $project->end_date ? \Carbon\Carbon::parse($project->end_date)->format('d M, Y') : 'N/A' }}
                </p>
                 <div class="mt-2">
                    <span class="badge bg-light text-dark border">{{ floor($daysRemaining ) }} {{ __('general.days_left') }}</span>
                </div>
            </div>
        </div>

        <hr class="my-4">

        <div class="row g-2">
             <div class="col-6">
                 <div class="p-3 bg-light rounded text-center">
                     <small class="text-muted d-block mb-1">{{ __('general.working_zone') }}</small>
                     <span class="fw-bold text-dark">{{ $project->working_zone ?? 'N/A' }}</span>
                 </div>
             </div>
              <div class="col-6">
                 <div class="p-3 bg-light rounded text-center">
                     <small class="text-muted d-block mb-1">{{ __('general.status') }}</small>
                     <span class="badge bg-{{ $projectStatus['color'] }}">{{ $projectStatus['message'] }}</span>
                 </div>
             </div>
        </div>
    </div>
</div>
