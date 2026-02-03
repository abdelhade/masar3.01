@extends('progress::layouts.daily-progress')

{{-- Sidebar is now handled by the layout itself --}}

@section('title', __('general.new_daily_progress_entry'))

@section('content')
<style>
    /* Custom polish to match reference image exactly */
    .bg-header-blue {
        background-color: #2c7be5; /* Adjust to match the specific blue in image */
        color: white;
    }
    .form-control:focus, .form-select:focus {
        border-color: #2c7be5;
        box-shadow: 0 0 0 0.2rem rgba(44, 123, 229, 0.25);
    }
    .input-group-text {
        background-color: #f8f9fa;
        color: #6c757d;
        border-color: #ced4da;
    }
    .quantity-input {
        font-size: 1.1rem;
        font-weight: bold;
        color: #495057;
    }
</style>

<div class="container-fluid px-4" x-data="dailyProgressForm({{ $selectedProjectId ? (int) $selectedProjectId : 'null' }}, {{ $selectedItemId ? (int) $selectedItemId : 'null' }})">
    
    <!-- Main Card Container -->
    <div class="card border-0 shadow-sm mb-4">
        <!-- Blue Header -->
        <div class="card-header bg-primary text-white py-3 d-flex justify-content-between align-items-center">
            <h5 class="mb-0 fw-bold">
                <i class="fas fa-list-alt me-2"></i> {{ __('general.new_daily_progress_entry') }}
            </h5>
            <a href="{{ route('daily_progress.index') }}" class="btn btn-light btn-sm fw-bold text-primary">
                <i class="fas fa-arrow-left me-1"></i> {{ __('general.back_to_list') }}
            </a>
        </div>

        <div class="card-body p-4 bg-light">
            @if ($errors->any())
                <div class="alert alert-danger shadow-sm border-0 mb-4">
                    <div class="d-flex align-items-center mb-2">
                        <i class="fas fa-exclamation-circle fa-2x me-3"></i>
                        <div>
                            <h5 class="mb-1 fw-bold">{{ __('general.error_title') }}</h5>
                            <p class="mb-0">{{ __('general.error_description') }}</p>
                        </div>
                    </div>
                    <ul class="mb-0 small text-danger-emphasis">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form action="{{ route('daily_progress.store') }}" method="POST" id="progressForm" class="needs-validation" novalidate>
                @csrf
                
                <!-- Filters Row (White Box) -->
                <div class="bg-white p-3 rounded border shadow-sm mb-4">
                    <div class="row g-3">
                        <!-- Project -->
                        <div class="col-md-5 border-end"> <!-- Added border-end for separation -->
                            <label class="form-label fw-bold small text-uppercase text-dark mb-1">
                                <i class="fas fa-project-diagram me-1 text-primary"></i> {{ __('general.project') }}
                            </label>
                            <select name="project_id" x-model="projectId" @change="loadItems()" class="form-select form-select-lg border-0 bg-light" required>
                                <option value="">{{ __('general.select_project') }}...</option>
                                @foreach ($projects as $project)
                                    <option value="{{ $project->id }}" @if(isset($selectedProjectId) && $selectedProjectId == $project->id) selected @endif>{{ $project->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Subproject -->
                        <div class="col-md-4 border-end">
                            <label class="form-label fw-bold small text-uppercase text-dark mb-1">
                                <i class="fas fa-layer-group me-1 text-primary"></i> {{ __('general.subproject') }}
                            </label>
                            <select x-model="subprojectFilter" class="form-select form-select-lg border-0 bg-light" :disabled="!subprojects.length">
                                <option value="">{{ __('general.all_subprojects') }}</option>
                                <template x-for="sub in subprojects" :key="sub">
                                    <option :value="sub" x-text="sub"></option>
                                </template>
                            </select>
                        </div>

                        <!-- Date -->
                        <div class="col-md-3">
                            <label class="form-label fw-bold small text-uppercase text-dark mb-1">
                                <i class="far fa-calendar-alt me-1 text-primary"></i> {{ __('general.date') }}
                            </label>
                            <input type="date" name="progress_date" class="form-control form-control-lg border-0 bg-light" required value="{{ date('Y-m-d') }}">
                        </div>
                    </div>
                </div>

                <!-- Search & Filters Bar -->
                <div class="d-flex gap-3 mb-3 align-items-center" x-show="items.length > 0">
                    <div class="flex-grow-1 position-relative">
                        <div class="input-group shadow-sm">
                            <span class="input-group-text bg-white border-end-0 ps-3"><i class="fas fa-search text-muted"></i></span>
                            <input type="text" x-model="search" class="form-control border-start-0 py-2" 
                                   placeholder="{{ __('general.search_placeholder_items') }}">
                            <button class="btn btn-white border border-start-0 text-muted" type="button" x-show="search" @click="search = ''">
                                <i class="fas fa-times"></i>
                            </button>
                        </div>
                    </div>
                    
                    <div class="btn-group shadow-sm">
                        <button type="button" class="btn" :class="viewMode === 'list' ? 'btn-primary' : 'btn-outline-primary bg-white'" @click="viewMode = 'list'">
                            <i class="fas fa-list me-1"></i> {{ __('general.view_normal') }}
                        </button>
                        <button type="button" class="btn" :class="viewMode === 'subproject' ? 'btn-primary' : 'btn-outline-primary bg-white'" @click="viewMode = 'subproject'">
                            <i class="fas fa-folder me-1"></i> {{ __('general.view_subproject') }}
                        </button>
                    </div>
                </div>

                <!-- List Header (Blue Bar) -->
                <div class="card border-0 shadow-sm mb-0 bg-primary-subtle text-primary fw-bold" x-show="items.length > 0">
                    <div class="card-body py-2 px-3">
                        <div class="row align-items-center">
                            <div class="col-md-6">{{ __('general.work_item') }}</div>
                            <div class="col-md-3 text-center">{{ __('general.subproject') }}</div>
                            <div class="col-md-3 px-4">{{ __('general.enter_quantity') }}</div>
                        </div>
                    </div>
                </div>

                <!-- Items Container -->
                <div x-show="items.length > 0" class="d-flex flex-column gap-2 mt-2 pb-5">
                    
                    <!-- Normal List -->
                    <template x-if="viewMode === 'list'">
                        <div class="d-flex flex-column gap-2">
                             <template x-for="item in filteredItems" :key="item.id">
                                @include('progress::daily-progress.partials.item-card')
                            </template>
                            <div x-show="filteredItems.length === 0" class="text-center py-4 text-muted bg-white rounded shadow-sm border border-dashed">
                                <i class="fas fa-search fa-2x mb-2 text-warning"></i><br>{{ __('general.no_items_found') }}
                            </div>
                        </div>
                    </template>

                    <!-- Grouped List -->
                    <template x-if="viewMode === 'subproject'">
                         <div class="d-flex flex-column gap-3">
                            <template x-for="group in groupedItems" :key="group.name">
                                <div class="card border-0 shadow-sm">
                                    <div class="card-header bg-light border-bottom py-2">
                                        <h6 class="mb-0 fw-bold text-dark"><i class="fas fa-folder-open me-2 text-warning"></i> <span x-text="group.name || '{{ __('general.general_items') }}'"></span></h6>
                                    </div>
                                    <div class="card-body p-0">
                                         <template x-for="item in group.items" :key="item.id">
                                            <div class="border-bottom last-border-0">
                                                @include('progress::daily-progress.partials.item-card')
                                            </div>
                                        </template>
                                    </div>
                                </div>
                            </template>
                        </div>
                    </template>

                    <!-- Notes Section -->
                    <div class="card border-0 shadow-sm mt-3">
                        <div class="card-body">
                            <label class="form-label fw-bold"><i class="fas fa-sticky-note me-1 text-muted"></i> {{ __('general.notes') }}</label>
                            <textarea name="notes" class="form-control bg-light" rows="3" form="progressForm" placeholder="{{ __('general.add_notes_here') }}..."></textarea>
                        </div>
                    </div>

                    <!-- Save Action -->
                    <div class="mt-3 text-end">
                         <button type="submit" form="progressForm" class="btn btn-primary btn-lg px-5 shadow">
                            <i class="fas fa-save me-2"></i> {{ __('general.save_progress') }}
                        </button>
                    </div>

                </div>

                 <!-- Loading State -->
                <div x-show="isLoading" class="text-center py-5">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                </div>

                <!-- Empty State -->
                <div x-show="!projectId && !isLoading" class="text-center py-5 text-muted opacity-50">
                    <i class="fas fa-arrow-up fa-3x mb-3"></i>
                    <h4>{{ __('general.select_project_to_start') }}</h4>
                </div>

            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('dailyProgressForm', (selectedProjectId = null, selectedItemId = null) => ({
            projectId: selectedProjectId ? String(selectedProjectId) : '',
            selectedItemId: selectedItemId,
            items: [],
            subprojects: [],
            subprojectFilter: '',
            search: '',
            viewMode: 'list',
            isLoading: false,

            init() {
                if (this.projectId) this.$nextTick(() => this.loadItems());
            },

            loadItems() {
                if (!this.projectId) { 
                    this.resetData(); 
                    return; 
                }
                
                this.isLoading = true;
                // Reset data while loading
                this.items = [];
                this.subprojects = [];

                fetch(`/api/v1/project-items/${this.projectId}`)
                    .then(r => r.json())
                    .then(data => {
                        this.items = data;
                        this.subprojects = [...new Set(data.map(i => i.subproject_name).filter(Boolean))];
                        this.isLoading = false;
                    })
                    .catch(e => {
                        console.error(e);
                        this.isLoading = false;
                    });
            },

            resetData() {
                this.items = [];
                this.subprojects = [];
                this.search = '';
                this.subprojectFilter = '';
            },

            get filteredItems() {
                return this.items.filter(item => {
                    const matchSub = !this.subprojectFilter || item.subproject_name === this.subprojectFilter;
                    const s = this.search.toLowerCase();
                    const matchSearch = !this.search || 
                                        item.work_item.name.toLowerCase().includes(s) || 
                                        (item.work_item.category && item.work_item.category.toLowerCase().includes(s));
                    return matchSub && matchSearch;
                });
            },

            get groupedItems() {
                const groups = {};
                this.filteredItems.forEach(item => {
                    const k = item.subproject_name || '{{ __('general.general_items') }}';
                    if (!groups[k]) groups[k] = { name: item.subproject_name, items: [] };
                    groups[k].items.push(item);
                });
                return Object.values(groups);
            },

            focusNext(el) {
                const inputs = Array.from(document.querySelectorAll('.quantity-input:not([disabled])'));
                const index = inputs.indexOf(el);
                if (index > -1 && index < inputs.length - 1) {
                    inputs[index + 1].focus();
                    inputs[index + 1].select();
                }
            }
        }));
    });
</script>
@endpush
@endsection
