<div class="card stat-card border-0 shadow-sm mb-4" 
     x-data="{ 
        expanded: true,
        searchQuery: '',
        selectedSubproject: '',
        selectedCategory: '',
        selectedStatus: '',
        showPlanned: false,
        
        matchesFilter(item) {
            // Search
            const searchLower = this.searchQuery.toLowerCase();
            const nameMatch = item.name.toLowerCase().includes(searchLower);
            const unitMatch = item.unit.toLowerCase().includes(searchLower);
            const catMatch = item.category.toLowerCase().includes(searchLower);
            
            if (this.searchQuery && !nameMatch && !unitMatch && !catMatch) return false;
            
            // Subproject
            if (this.selectedSubproject && item.subproject !== this.selectedSubproject) return false;
            
            // Category
            if (this.selectedCategory && item.category !== this.selectedCategory) return false;
            
            // Status
            // item.statusId might be null, handle string comparison
            if (this.selectedStatus && String(item.statusId) !== String(this.selectedStatus)) return false;
            
            return true;
        },
        
        clearFilters() {
            this.searchQuery = '';
            this.selectedSubproject = '';
            this.selectedCategory = '';
            this.selectedStatus = '';
            this.showPlanned = false;
        }
     }">
     
    <!-- Header With Filters -->
    <div class="card-header bg-white py-4 border-bottom">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h5 class="card-title mb-0 fw-bold text-primary" style="font-size: 1.25rem;">
                <i class="fas fa-list-alt me-2"></i>{{ __('general.work_items_progress') }}
            </h5>
            <div class="d-flex align-items-center gap-2">
                 <button class="btn btn-primary btn-sm rounded-pill px-3">
                    <i class="fas fa-layer-group me-1"></i> {{ __('general.group_by_item') }}
                </button>
                <span class="badge bg-primary rounded-pill px-3 py-2" style="font-size: 0.9rem;">
                    {{ count($project->items) }} items
                </span>
            </div>
        </div>
        
        <!-- Filter Bar -->
        <div class="p-3 bg-light rounded-3 border">
            <div class="row g-3">
                <!-- Search -->
                <div class="col-md-4">
                    <label class="form-label small text-muted fw-bold mb-1">{{ __('general.search') }}</label>
                    <div class="input-group">
                        <span class="input-group-text bg-white border-end-0"><i class="fas fa-search text-muted"></i></span>
                        <input type="text" class="form-control border-start-0 ps-0" 
                               x-model="searchQuery" 
                               placeholder="Search by name, unit, or category...">
                    </div>
                </div>
                
                <!-- Subproject Filter -->
                <div class="col-md-2">
                    <label class="form-label small text-muted fw-bold mb-1">{{ __('general.subproject') }}</label>
                    <select class="form-select form-select-sm" x-model="selectedSubproject">
                        <option value="">{{ __('general.all_subprojects') }}</option>
                        @foreach($subprojectsList as $sub)
                            <option value="{{ $sub }}">{{ $sub }}</option>
                        @endforeach
                    </select>
                </div>
                
                <!-- Category Filter -->
                <div class="col-md-2">
                    <label class="form-label small text-muted fw-bold mb-1">{{ __('general.category') }}</label>
                    <select class="form-select form-select-sm" x-model="selectedCategory">
                        <option value="">{{ __('general.all_categories') }}</option>
                        @foreach($categoriesList as $cat)
                            <option value="{{ $cat }}">{{ $cat }}</option>
                        @endforeach
                    </select>
                </div>
                
                <!-- Status Filter -->
                <div class="col-md-2">
                    <label class="form-label small text-muted fw-bold mb-1">{{ __('general.status') }}</label>
                    <select class="form-select form-select-sm" x-model="selectedStatus">
                        <option value="">{{ __('general.all_statuses') }}</option>
                        @foreach($itemStatuses as $status)
                            <option value="{{ $status->id }}">{{ $status->name }}</option>
                        @endforeach
                    </select>
                </div>
                
                <!-- Clear Filters -->
                <div class="col-md-2 d-flex align-items-end">
                     <button @click="clearFilters()" class="btn btn-outline-secondary btn-sm w-100" x-show="searchQuery || selectedSubproject || selectedCategory || selectedStatus">
                        <i class="fas fa-times me-1"></i> {{ __('general.clear_filters') }}
                    </button>
                </div>
            </div>
            
            <div class="row mt-3">
               <div class="col-12">
                   <div class="form-check">
                      <input class="form-check-input" type="checkbox" id="showPlannedCheck" x-model="showPlanned">
                      <label class="form-check-label fw-bold small user-select-none" for="showPlannedCheck">
                        <i class="fas fa-chart-line me-1"></i> {{ __('Show Planned Progress') }}
                      </label>
                    </div>
               </div>
            </div>
        </div>
    </div>
    
    <!-- Items Grid -->
    <div class="card-body bg-light">
        <div class="row g-4">
            @forelse($project->items as $item)
                <div class="col-lg-6" 
                     x-show="matchesFilter({
                        name: '{{ addslashes($item->workItem->name ?? '') }}',
                        unit: '{{ $item->workItem->unit ?? '' }}',
                        category: '{{ addslashes(($item->workItem && $item->workItem->category) ? $item->workItem->category->name : 'Uncategorized') }}',
                        subproject: '{{ addslashes($item->subproject_name ?? 'Main Project') }}',
                        statusId: '{{ $item->item_status_id }}'
                     })"
                     x-transition.duration.300ms>
                     
                    <div class="card h-100 border-0 shadow-sm item-card hover-shadow transition-all">
                        <div class="card-body p-4">
                            <!-- Header: Name & Status Badge -->
                            <div class="d-flex justify-content-between align-items-start mb-3">
                                <div>
                                    <h6 class="card-title text-dark fw-bold mb-1" style="font-size: 1rem;">
                                        {{ $item->workItem->name ?? 'Unknown' }}
                                    </h6>
                                    
                                    <div class="d-flex flex-wrap gap-3 mt-2 text-muted small">
                                        <div title="Category">
                                            <i class="fas fa-folder me-1 text-primary"></i> 
                                            {{ ($item->workItem && $item->workItem->category) ? $item->workItem->category->name : 'Uncategorized' }}
                                        </div>
                                        <div title="Subproject">
                                            <i class="fas fa-layer-group me-1 text-info"></i> 
                                            {{ $item->subproject_name ?? 'Main Project' }}
                                        </div>
                                        @if($item->workItem && $item->workItem->unit)
                                        <div title="Unit">
                                            <i class="fas fa-ruler-combined me-1 text-warning"></i> 
                                            {{ $item->workItem->unit }}
                                        </div>
                                        @endif
                                    </div>
                                </div>
                                
                                <div class="text-end">
                                    <div class="h4 mb-0 fw-bold text-primary">
                                        {{ round($item->completion_percentage, 1) }}%
                                    </div>
                                    @if($item->completion_percentage >= 100)
                                        <span class="badge bg-success rounded-pill">Completed</span>
                                    @elseif($item->planned_percentage > $item->completion_percentage)
                                         <span class="badge bg-danger rounded-pill">Delayed</span>
                                    @else
                                         <span class="badge bg-info text-dark rounded-pill">On Track</span>
                                    @endif
                                </div>
                            </div>

                            <!-- Selector for Status -->
                            <div class="mb-4">
                                <label class="small text-muted fw-bold mb-1"><i class="fas fa-tag me-1"></i> Item Status</label>
                                <div class="position-relative">
                                     <select class="form-select form-select-sm item-status-select border-0 shadow-sm py-2" 
                                            data-item-id="{{ $item->id }}"
                                            data-project-id="{{ $project->id }}"
                                            data-original-value="{{ $item->item_status_id }}"
                                            style="background-color: {{ $item->status ? $item->status->color . '15' : '#f8f9fa' }}; color: {{ $item->status ? $item->status->color : '#495057' }}; font-weight: 600;">
                                        <option value="">{{ __('general.no_status') }}</option>
                                        @foreach($itemStatuses as $status)
                                            <option value="{{ $status->id }}" 
                                                    style="color: {{ $status->color }}"
                                                    {{ ($item->item_status_id == $status->id) ? 'selected' : '' }}>
                                                {{ $status->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    <div class="item-status-loading position-absolute top-50 end-0 translate-middle-y me-3 d-none">
                                        <i class="fas fa-spinner fa-spin text-primary"></i>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Progress Bars -->
                            <div class="space-y-3">
                                <!-- Actual Progress -->
                                <div>
                                    <div class="d-flex justify-content-between small mb-1">
                                        <span class="fw-bold text-success"><i class="fas fa-check-circle me-1"></i> Actual Progress</span>
                                        <span class="fw-bold text-dark">{{ round($item->completion_percentage, 1) }}%</span>
                                    </div>
                                    <div class="progress" style="height: 8px; border-radius: 4px; background-color: #e9ecef;">
                                        <div class="progress-bar bg-success" 
                                             role="progressbar" 
                                             style="width: {{ $item->completion_percentage }}%; border-radius: 4px;"></div>
                                    </div>
                                </div>
                                
                                <!-- Planned Progress (Toggleable) -->
                                <div x-show="showPlanned" x-transition>
                                    <div class="d-flex justify-content-between small mb-1 mt-3">
                                        <span class="fw-bold text-info"><i class="far fa-calendar-check me-1"></i> Planned Progress</span>
                                        <span class="fw-bold text-dark">{{ round($item->planned_percentage, 1) }}%</span>
                                    </div>
                                    <div class="progress" style="height: 8px; border-radius: 4px; background-color: #e9ecef;">
                                        <div class="progress-bar bg-info" 
                                             role="progressbar" 
                                             style="width: {{ $item->planned_percentage }}%; border-radius: 4px;"></div>
                                    </div>
                                    
                                     <!-- Delay Indicator -->
                                    <div class="text-center mt-2">
                                        @php
                                            $diff = $item->completion_percentage - $item->planned_percentage;
                                        @endphp
                                        @if($diff < 0)
                                            <span class="badge bg-danger">
                                                <i class="fas fa-arrow-down me-1"></i> Behind by {{ abs(round($diff, 1)) }}%
                                            </span>
                                        @elseif($diff > 0)
                                            <span class="badge bg-success">
                                                 <i class="fas fa-arrow-up me-1"></i> Ahead by {{ round($diff, 1) }}%
                                            </span>
                                        @else
                                            <span class="badge bg-secondary">On Schedule</span>
                                        @endif
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Footer Stats -->
                            <div class="d-flex justify-content-between align-items-center mt-4 pt-3 border-top text-muted small">
                                <div>
                                    <span class="fw-bold text-dark fs-6">{{ round($item->completed_quantity, 1) }}</span> 
                                    {{ $item->workItem->unit ?? '' }}
                                </div>
                                <div>
                                    Target: <span class="fw-bold">{{ round($item->total_quantity, 1) }}</span>
                                </div>
                                <div>
                                    Remaining: <span class="fw-bold">{{ round($item->total_quantity - $item->completed_quantity, 1) }}</span>
                                </div>
                            </div>

                        </div>
                    </div>
                </div>
            @empty
                <div class="col-12 text-center py-5 text-muted">
                    <div class="mb-3">
                         <i class="las la-inbox fa-4x text-light-gray"></i>
                    </div>
                    <h5>{{ __('general.no_items_found') }}</h5>
                    <p class="mb-0">Try adjusting your filters or add new work items.</p>
                </div>
            @endforelse
        </div>
    </div>
</div>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Handle Item Status Change with Debouncing prevention
        let pendingRequests = new Set();

        document.querySelectorAll('.item-status-select').forEach(select => {
            select.addEventListener('change', function() {
                const itemId = this.dataset.itemId;
                const projectId = this.dataset.projectId;
                const statusId = this.value;
                const originalValue = this.dataset.originalValue;
                const container = this.closest('.position-relative');
                const loadingIndicator = container.querySelector('.item-status-loading');
                const selectElement = this;

                if (pendingRequests.has(itemId)) return;
                pendingRequests.add(itemId);

                // Show loading
                loadingIndicator.classList.remove('d-none');
                selectElement.disabled = true;
                selectElement.style.opacity = '0.7';

                fetch(`/projects/${projectId}/items/${itemId}/status`, {
                    method: 'PATCH',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({ item_status_id: statusId })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Success toast
                        if (typeof showToast === 'function') {
                            showToast(data.message, 'success');
                        } else if (typeof Swal !== 'undefined') {
                             const Toast = Swal.mixin({
                                toast: true,
                                position: 'top-end',
                                showConfirmButton: false,
                                timer: 3000,
                                timerProgressBar: true
                            });
                            Toast.fire({
                                icon: 'success',
                                title: data.message
                            });
                        }
                        
                        // Update color
                        const selectedOption = selectElement.options[selectElement.selectedIndex];
                        if (selectedOption && selectedOption.style.color) {
                             selectElement.style.color = selectedOption.style.color;
                             selectElement.style.backgroundColor = selectedOption.style.color + '15'; 
                        } else {
                             selectElement.style.color = '#495057';
                             selectElement.style.backgroundColor = '#f8f9fa';
                        }
                        
                        selectElement.dataset.originalValue = statusId;

                    } else {
                        // Revert
                        selectElement.value = originalValue;
                        if (typeof showToast === 'function') showToast(data.message || 'Error', 'error');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    selectElement.value = originalValue;
                })
                .finally(() => {
                    loadingIndicator.classList.add('d-none');
                    selectElement.disabled = false;
                    selectElement.style.opacity = '1';
                    pendingRequests.delete(itemId);
                });
            });
        });
    });
</script>
<style>
    .item-card {
        transition: transform 0.2s ease, box-shadow 0.2s ease;
    }
    .item-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 .5rem 1rem rgba(0,0,0,.08) !important;
    }
    .hover-shadow:hover {
        box-shadow: 0 .5rem 1rem rgba(0,0,0,.15) !important;
    }
</style>
@endpush
