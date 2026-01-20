@extends('progress::layouts.daily-progress')

@section('content')
    @include('components.breadcrumb', [
        'title' => __('general.project_templates'),
        'items' => [
            ['label' => __('general.home'), 'url' => route('admin.dashboard')],
            ['label' => __('general.project_templates'), 'url' => route('project.template.index')],
            ['label' => __('general.edit')],
        ],
    ])

    @if ($errors->any())
        <div class="alert alert-danger">
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="col-12">
        <div class="mx-auto" style="max-width: 1100px;" x-data="templateForm()" x-init="initData({{ json_encode($workItems) }}, {{ $initialItems }})">
            <div class="row">
                <div class="col-lg-12">
                    <div class="card border-0 shadow-sm mb-4">
                        <div class="card-header bg-primary text-white py-3">
                            <h5 class="mb-0 text-white fw-bold"><i class="las la-edit me-2"></i>{{ __('general.edit_project_template') }}: {{ $projectTemplate->name }}</h5>
                        </div>
                        <div class="card-body p-4">
                            <form action="{{ route('project.template.update', $projectTemplate->id) }}" method="POST" id="templateForm" @submit.prevent="submitForm">
                                @csrf
                                @method('PUT')
                                
                                {{-- Header Data --}}
                                <div class="row mb-4">
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label fw-bold" for="name">{{ __('general.template_name') }} <span class="text-danger">*</span></label>
                                        <div class="input-group">
                                            <input type="text" class="form-control form-control-lg" id="name" name="name"
                                                placeholder="{{ __('general.enter_template_name') }}" value="{{ old('name', $projectTemplate->name) }}" required>
                                            <span class="input-group-text bg-light text-muted"><i class="las la-file-alt"></i></span>
                                        </div>
                                        @error('name')
                                            <small class="text-danger">{{ $message }}</small>
                                        @enderror
                                    </div>

                                    <div class="col-md-6 mb-3">
                                        <label class="form-label fw-bold" for="project_type_id">{{ __('general.project_type') }}</label>
                                        <div class="input-group">
                                            <select class="form-select" id="project_type_id" name="project_type_id">
                                                <option value="">{{ __('general.project_type') }}</option>
                                                @foreach ($projectTypes as $type)
                                                    <option value="{{ $type->id }}" {{ old('project_type_id', $projectTemplate->project_type_id) == $type->id ? 'selected' : '' }}>
                                                        {{ $type->name }}
                                                    </option>
                                                @endforeach
                                            </select>
                                            <span class="input-group-text bg-light text-muted"><i class="las la-shapes"></i></span>
                                        </div>
                                    </div>
                                </div>

                                <div class="mb-4">
                                 <div class="mb-4">
                                     <label class="form-label fw-bold" for="description">{{ __('general.template_description') }}</label>
                                     <textarea class="form-control" id="description" name="description" rows="3">{{ old('description', $projectTemplate->description) }}</textarea>
                                </div>

                                <hr>

                                {{-- Items Section --}}
                                <div class="card mb-4 border-0 shadow-sm">
                                    <div class="card-header bg-primary text-white py-3 d-flex justify-content-between align-items-center">
                                        <h5 class="mb-0 text-white fw-bold"><i class="las la-tasks me-2"></i>{{ __('general.template_items') }}</h5>
                                        <span class="badge bg-white text-primary rounded-pill px-3 py-2" x-text="items.length + ' {{ __('general.items') }}'"></span>
                                    </div>
                                    
                                    {{-- Search Bar --}}
                                    <div class="card-body p-3 border-bottom bg-white">
                                        <div class="position-relative" style="max-width: 600px;">
                                            <div class="input-group">
                                                <span class="input-group-text bg-white border-end-0 ps-3"><i class="las la-search text-muted"></i></span>
                                                <input type="text" class="form-control border-start-0" 
                                                    placeholder="{{ __('general.search_item_placeholder') }}" 
                                                    x-model="searchQuery" 
                                                    @input.debounce.300ms="searchItems()"
                                                    @keydown.escape="searchResults = []"
                                                >
                                                <button class="btn btn-outline-secondary border-start-0" type="button" x-show="searchQuery.length > 0" @click="searchQuery = ''; searchResults = []">
                                                    <i class="las la-times"></i>
                                                </button>
                                            </div>

                                            {{-- Search Results Dropdown --}}
                                            <div class="card position-absolute w-100 shadow-lg mt-1 border-0" 
                                                 style="z-index: 1050; max-height: 300px; overflow-y: auto;" 
                                                 x-show="searchResults.length > 0" 
                                                 @click.outside="searchResults = []" 
                                                 x-transition>
                                                <div class="card-header bg-light py-2 small fw-bold text-muted d-flex justify-content-between">
                                                    <span>{{ __('general.search_results') }}</span>
                                                    <span class="text-primary cursor-pointer" @click="searchResults = []">{{ __('general.close') }}</span>
                                                </div>
                                                <ul class="list-group list-group-flush">
                                                    <template x-for="item in searchResults" :key="item.id">
                                                        <li class="list-group-item list-group-item-action d-flex justify-content-between align-items-center p-2 cursor-pointer border-bottom" 
                                                            @click="addItem(item)">
                                                            <div>
                                                                <div class="fw-bold text-dark" x-text="item.name"></div>
                                                                <div class="d-flex align-items-center gap-2 mt-1">
                                                                    <span class="badge bg-light text-dark border fw-normal" x-text="item.unit"></span>
                                                                </div>
                                                            </div>
                                                            <button type="button" class="btn btn-sm btn-primary rounded-circle shadow-sm">
                                                                <i class="las la-plus"></i>
                                                            </button>
                                                        </li>
                                                    </template>
                                                </ul>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="card-body p-0 table-responsive">
                                        <table class="table table-bordered table-hover mb-0 align-middle text-center" style="min-width: 1500px">
                                            <thead class="table-secondary">
                                                <tr>
                                                    <th style="width: 50px">#</th>
                                                    <th style="width: 250px">{{ __('general.item') }}</th>
                                                    <th style="width: 150px">{{ __('general.subproject') }}</th>
                                                    <th style="width: 200px">{{ __('general.notes') }}</th>
                                                    <th style="width: 80px">{{ __('general.measurable') }}</th>
                                                    <th style="width: 100px">{{ __('general.quantity') }}</th>
                                                    <th style="width: 100px">{{ __('general.daily_rate') }}</th>
                                                    <th style="width: 80px">{{ __('general.duration_days') }}</th>
                                                    <th style="width: 200px">{{ __('general.predecessor') }}</th>
                                                    <th style="width: 120px">{{ __('general.dependency_type') }}</th>
                                                    <th style="width: 80px">Lag</th>
                                                    <th style="width: 60px">{{ __('general.delete') }}</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <template x-for="(row, index) in items" :key="row.id">
                                                    <tr>
                                                        <input type="hidden" :name="'items['+index+'][work_item_id]'" :value="row.work_item_id">
                                                        <input type="hidden" :name="'items['+index+'][item_order]'" :value="index">
                                                        
                                                        <td x-text="index + 1"></td>
                                                        
                                                        <td>
                                                            <div class="fw-bold text-start" x-text="row.name"></div>
                                                            <div class="text-muted small text-start" x-text="'Unit: ' + row.unit"></div>
                                                        </td>

                                                        <td>
                                                            <input type="text" class="form-control form-control-sm" 
                                                                   :name="'items['+index+'][subproject_name]'" 
                                                                   x-model="row.subproject_name" 
                                                                   placeholder="{{ __('general.subproject') }}" list="subprojects_list">
                                                        </td>
                                                        
                                                        <td>
                                                            <textarea class="form-control form-control-sm" 
                                                                      :name="'items['+index+'][notes]'" 
                                                                      x-model="row.notes" rows="1"></textarea>
                                                        </td>

                                                        <td>
                                                            <input type="checkbox" class="form-check-input" 
                                                                   :name="'items['+index+'][is_measurable]'" 
                                                                   x-model="row.is_measurable" value="1">
                                                        </td>

                                                        <td>
                                                            <input type="number" step="0.01" class="form-control form-control-sm" 
                                                                   :name="'items['+index+'][default_quantity]'" 
                                                                   x-model.number="row.default_quantity" 
                                                                   @input="calculateDuration(row)" min="0">
                                                        </td>
                                                        
                                                        <td>
                                                            <input type="number" step="0.01" class="form-control form-control-sm" 
                                                                   :name="'items['+index+'][estimated_daily_qty]'" 
                                                                   x-model.number="row.estimated_daily_qty" 
                                                                   @input="calculateDuration(row)" min="0">
                                                        </td>

                                                        <td>
                                                            <input type="number" class="form-control form-control-sm" 
                                                                   :name="'items['+index+'][duration]'" 
                                                                   x-model="row.duration" readonly>
                                                        </td>

                                                        <td>
                                                            <!-- Keep ID for predecessor value -->
                                                            <input type="hidden" :name="'items['+index+'][predecessor]'" :value="getPredecessorIndex(row.predecessor)">
                                                            <select class="form-select form-select-sm" :value="row.predecessor" @change="row.predecessor = $event.target.value">
                                                                <option value="">--</option>
                                                                <template x-for="(p, i) in items" :key="p.id">
                                                                    <option :value="p.id" x-text="(i + 1) + '. ' + p.name" :disabled="p.id == row.id" :selected="p.id == row.predecessor"></option>
                                                                </template>
                                                            </select>
                                                        </td>
                                                        
                                                        <td>
                                                            <select class="form-select form-select-sm" :name="'items['+index+'][dependency_type]'" :value="row.dependency_type" @change="row.dependency_type = $event.target.value">
                                                                <option value="end_to_start" :selected="row.dependency_type == 'end_to_start'">End to Start</option>
                                                                <option value="start_to_start" :selected="row.dependency_type == 'start_to_start'">Start to Start</option>
                                                            </select>
                                                        </td>
                                                        
                                                        <td>
                                                            <input type="number" class="form-control form-control-sm" 
                                                                   :name="'items['+index+'][lag]'" 
                                                                   x-model.number="row.lag">
                                                        </td>

                                                        <td class="text-center">
                                                            <button type="button" class="btn btn-sm btn-outline-danger rounded-circle" @click="removeItem(index)">
                                                                <i class="las la-trash"></i>
                                                            </button>
                                                        </td>
                                                    </tr>
                                                </template>
                                                
                                                <tr x-show="items.length === 0">
                                                    <td colspan="12" class="text-center py-4 text-muted">
                                                        <i class="las la-box-open fs-1 mb-2"></i>
                                                        <p>{{ __('general.no_items_added') }}</p>
                                                    </td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>

                                <div class="d-flex justify-content-start mt-4">
                                    <button type="submit" class="btn btn-primary me-2">
                                        <i class="las la-save"></i> {{ __('general.save_changes') }}
                                    </button>
                                    <a href="{{ route('project.template.index') }}" class="btn btn-danger">
                                        <i class="las la-times"></i> {{ __('general.cancel') }}
                                    </a>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
            
            <datalist id="subprojects_list">
                @if(isset($subprojects))
                    @foreach($subprojects as $spName)
                        <option value="{{ $spName }}">
                    @endforeach
                @endif
            </datalist>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('templateForm', () => ({
                allWorkItems: [],
                items: [],
                searchQuery: '',
                searchResults: [],
                
                initData(workItems, initialItems = []) {
                    this.allWorkItems = workItems;
                    
                    // Priority: 1. Old Input (Validation Fail), 2. Initial DB Items, 3. Empty
                    let oldItemsInput = @json(old('items'));

                    if (oldItemsInput && oldItemsInput.length > 0) {
                        // Restore form state after validation error
                         this.items = oldItemsInput.map((old, idx) => {
                             let wi = this.allWorkItems.find(w => w.id == old.work_item_id);
                             return {
                                id: 'item_old_' + idx + '_' + Date.now(),
                                work_item_id: old.work_item_id,
                                name: wi ? wi.name : 'Unknown Item',
                                unit: wi ? wi.unit : '-',
                                subproject_name: old.subproject_name,
                                notes: old.notes,
                                is_measurable: old.is_measurable ? true : false,
                                default_quantity: parseFloat(old.default_quantity) || 0,
                                estimated_daily_qty: parseFloat(old.estimated_daily_qty) || 0,
                                duration: parseInt(old.duration) || 0,
                                predecessor: '', 
                                dependency_type: old.dependency_type || 'end_to_start',
                                lag: parseInt(old.lag) || 0
                             };
                        });
                    } else if (initialItems && initialItems.length > 0) {
                        // Load DB Items
                         this.items = initialItems.map(item => {
                             let wi = this.allWorkItems.find(w => w.id == item.work_item_id);
                             
                             return {
                                id: String(item.id), // Ensure String for Select matching
                                work_item_id: item.work_item_id,
                                name: wi ? wi.name : ('Item #' + item.work_item_id),
                                unit: wi ? wi.unit : '-',
                                subproject_name: item.subproject_name || '',
                                notes: item.notes || '',
                                is_measurable: (item.is_measurable == 1),
                                default_quantity: parseFloat(item.total_quantity || item.default_quantity) || 0, 
                                estimated_daily_qty: parseFloat(item.estimated_daily_qty) || 0,
                                duration: parseInt(item.duration) || 0,
                                predecessor: item.predecessor ? String(item.predecessor) : '', // Ensure String
                                dependency_type: item.dependency_type || 'end_to_start',
                                lag: parseInt(item.lag) || 0
                             };
                        });
                    }
                },

                searchItems() {
                    if (this.searchQuery.length < 2) {
                        this.searchResults = [];
                        return;
                    }
                    const q = this.searchQuery.toLowerCase();
                    this.searchResults = this.allWorkItems.filter(i => 
                        i.name.toLowerCase().includes(q)
                    ).slice(0, 50);
                },

                addItem(item) {
                     const newItem = {
                        id: 'item_' + Date.now() + Math.random().toString(36).substr(2, 9),
                        work_item_id: item.id,
                        name: item.name,
                        unit: item.unit,
                        subproject_name: '',
                        notes: '',
                        is_measurable: true,
                        default_quantity: 1,
                        estimated_daily_qty: 1,
                        duration: 1,
                        predecessor: '',
                        dependency_type: 'end_to_start',
                        lag: 0
                    };
                    
                    this.items.push(newItem);
                    this.calculateDuration(newItem);
                    this.searchQuery = '';
                    this.searchResults = [];
                },

                removeItem(index) {
                     // Check if other items depend on this one?
                     // Visual feedback maybe?
                    this.items.splice(index, 1);
                },

                calculateDuration(row) {
                    const qty = parseFloat(row.default_quantity) || 0;
                    const daily = parseFloat(row.estimated_daily_qty) || 0;
                    if (daily > 0 && qty > 0) {
                        row.duration = Math.ceil(qty / daily);
                    } else {
                        row.duration = 0;
                    }
                },

                getPredecessorIndex(predId) {
                    if (!predId) return '';
                    // We need to return the INDEX of the item in the list, because Controller expects array index
                    
                    const idx = this.items.findIndex(i => i.id == predId);
                    return idx === -1 ? '' : idx;
                },
                
                submitForm(e) {
                     if (this.items.length === 0) {
                        alert('{{ __('general.add_at_least_one_item') }}');
                        return;
                    }
                    e.target.submit();
                }
            }));
        });
    </script>
@endpush
