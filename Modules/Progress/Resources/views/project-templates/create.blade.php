@extends('progress::layouts.daily-progress')

@section('content')
    @include('components.breadcrumb', [
        'title' => __('قوالب المشاريع'),
        'items' => [
            ['label' => __('الرئيسيه'), 'url' => route('admin.dashboard')],
            ['label' => __('قوالب المشاريع'), 'url' => route('project.template.index')],
            ['label' => __('انشاء')],
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
        <div class="mx-auto" style="max-width: 1100px;" x-data="templateForm()" x-init="initData({{ json_encode($workItems) }})">
            <div class="row">
                <div class="col-lg-12">
                    <div class="card border-0 shadow-sm mb-4">
                        <div class="card-header bg-primary text-white py-3">
                            <h5 class="mb-0 text-white fw-bold"><i class="las la-plus-circle me-2"></i>اضافة قالب مشروع جديد</h5>
                        </div>
                        <div class="card-body p-4">
                            <form action="{{ route('project.template.store') }}" method="POST" id="templateForm" @submit.prevent="submitForm">
                                @csrf
                                
                                {{-- Header Data --}}
                                <div class="row mb-4">
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label fw-bold" for="name">اسم القالب <span class="text-danger">*</span></label>
                                        <div class="input-group">
                                            <input type="text" class="form-control form-control-lg" id="name" name="name"
                                                placeholder="ادخل اسم القالب" value="{{ old('name') }}" required>
                                            <span class="input-group-text bg-light text-muted"><i class="las la-file-alt"></i></span>
                                        </div>
                                        @error('name')
                                            <small class="text-danger">{{ $message }}</small>
                                        @enderror
                                    </div>

                                    <div class="col-md-6 mb-3">
                                        <label class="form-label fw-bold" for="project_type_id">نوع المشروع</label>
                                        <div class="input-group">
                                            <select class="form-select" id="project_type_id" name="project_type_id">
                                                <option value="">اختر نوع المشروع</option>
                                                @foreach ($projectTypes as $type)
                                                    <option value="{{ $type->id }}" {{ old('project_type_id') == $type->id ? 'selected' : '' }}>
                                                        {{ $type->name }}
                                                    </option>
                                                @endforeach
                                            </select>
                                            <span class="input-group-text bg-light text-muted"><i class="las la-shapes"></i></span>
                                        </div>
                                    </div>
                                </div>

                                <div class="mb-4">
                                     <label class="form-label fw-bold" for="description">وصف القالب</label>
                                     <textarea class="form-control" id="description" name="description" rows="3" placeholder="وصف مختصر للقالب...">{{ old('description') }}</textarea>
                                </div>

                                <hr>

                                {{-- Items Section --}}
                                <div class="card mb-4 border-0 shadow-sm">
                                    <div class="card-header bg-primary text-white py-3 d-flex justify-content-between align-items-center">
                                        <h5 class="mb-0 text-white fw-bold"><i class="las la-tasks me-2"></i>{{ __('بنود القالب') }}</h5>
                                        <span class="badge bg-white text-primary rounded-pill px-3 py-2" x-text="items.length + ' بنود'"></span>
                                    </div>
                                    
                                    {{-- Search Bar --}}
                                    <div class="card-body p-3 border-bottom bg-white">
                                        <div class="position-relative" style="max-width: 600px;">
                                            <div class="input-group">
                                                <span class="input-group-text bg-white border-end-0 ps-3"><i class="las la-search text-muted"></i></span>
                                                <input type="text" class="form-control border-start-0" 
                                                    placeholder="ابحث عن بند لإضافته (بالاسم أو الكود)..." 
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
                                                    <span>نتائج البحث</span>
                                                    <span class="text-primary cursor-pointer" @click="searchResults = []">إغلاق</span>
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
                                                    <th style="width: 250px">البند</th>
                                                    <th style="width: 150px">مشروع فرعي</th>
                                                    <th style="width: 200px">ملاحظات</th>
                                                    <th style="width: 80px">قابل للقياس</th>
                                                    <th style="width: 100px">الكمية</th>
                                                    <th style="width: 100px">المعدل اليومي</th>
                                                    <th style="width: 80px">المدة (أيام)</th>
                                                    <th style="width: 200px">يعتمد على (Predecessor)</th>
                                                    <th style="width: 120px">نوع الاعتمادية</th>
                                                    <th style="width: 80px">Lag</th>
                                                    <th style="width: 60px">حذف</th>
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
                                                                   placeholder="مشروع فرعي" list="subprojects_list">
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
                                                            {{-- We submit the INDEX of the predecessor for simplicity in Controller --}}
                                                            <input type="hidden" :name="'items['+index+'][predecessor]'" :value="getPredecessorIndex(row.predecessor)">
                                                            <select class="form-select form-select-sm" x-model="row.predecessor">
                                                                <option value="">--</option>
                                                                <template x-for="(p, i) in items" :key="p.id">
                                                                    <option :value="p.id" x-text="(i + 1) + '. ' + p.name" x-show="p.id !== row.id"></option>
                                                                </template>
                                                            </select>
                                                        </td>
                                                        
                                                        <td>
                                                            <select class="form-select form-select-sm" :name="'items['+index+'][dependency_type]'" x-model="row.dependency_type">
                                                                <option value="end_to_start">End to Start</option>
                                                                <option value="start_to_start">Start to Start</option>
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
                                                        <p>لا توجد بنود مضافة. استخدم البحث أعلاه لإضافة بنود.</p>
                                                    </td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>

                                <div class="d-flex justify-content-start mt-4">
                                    <button type="submit" class="btn btn-primary me-2">
                                        <i class="las la-save"></i> حفظ القالب
                                    </button>
                                    <a href="{{ route('project.template.index') }}" class="btn btn-danger">
                                        <i class="las la-times"></i> إلغاء
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
                
                initData(workItems) {
                    this.allWorkItems = workItems;
                    
                    // If old input exists (validation error), we should reload them.
                    // For now, start empty or implemented if needed.
                    let oldItems = @json(old('items', []));
                    if (oldItems && oldItems.length > 0) {
                        // Reconstruct items from old input...
                        // This is complex because we need names/units which might be in allWorkItems.
                        // Simplified: User re-enters or we map partially.
                        // Let's rely on standard re-fill if possible, but Alpine overrides DOM.
                        // Ideally we map `oldItems` to our structure.
                        
                        this.items = oldItems.map((old, idx) => {
                             let wi = this.allWorkItems.find(w => w.id == old.work_item_id);
                             return {
                                id: 'item_' + idx + '_' + Date.now(),
                                work_item_id: old.work_item_id,
                                name: wi ? wi.name : 'Unknown Item',
                                unit: wi ? wi.unit : '-',
                                subproject_name: old.subproject_name,
                                notes: old.notes,
                                is_measurable: old.is_measurable,
                                default_quantity: old.default_quantity,
                                estimated_daily_qty: old.estimated_daily_qty,
                                duration: old.duration,
                                predecessor: '', // Losing predecessor linkage on validation fail is acceptable trade-off for now vs complexity
                                dependency_type: old.dependency_type,
                                lag: old.lag
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
                    // Handle dependencies cleanup?
                    // Just delete
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
                    const idx = this.items.findIndex(i => i.id === predId);
                    return idx === -1 ? '' : idx;
                },
                
                submitForm(e) {
                     if (this.items.length === 0) {
                        alert('يرجى إضافة بند واحد على الأقل');
                        return;
                    }
                    e.target.submit();
                }
            }));
        });
    </script>
@endpush
