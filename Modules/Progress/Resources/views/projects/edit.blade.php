@extends('progress::layouts.daily-progress')

@section('title', 'تعديل مشروع: ' . $project->name)

@section('content')
<!-- Inline styles removed in favor of public/css/progress-theme.css -->

<div class="container-fluid" x-data="projectForm()" x-init="setData({{ json_encode($templates) }}, {{ json_encode($workItems) }}); loadProjectData({{ json_encode($project) }}, {{ json_encode($initialItems) }})">
    <form action="{{ route('progress.project.update', $project->id) }}" method="POST" @submit.prevent="submitForm">
        @csrf
        @method('PUT')
        
        <!-- Header -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h4 class="mb-1 text-primary fw-bold">تعديل مشروع: {{ $project->name }}</h4>
                <p class="text-muted mb-0 small">قم بتعديل البيانات التالية</p>
            </div>
            <div>
                <a href="{{ route('progress.project.index') }}" class="btn btn-outline-secondary btn-sm"><i class="las la-arrow-right me-1"></i> رجوع للقائمة</a>
            </div>
        </div>

        <div class="row justify-content-center">
            <div class="col-lg-10" style="max-width: 100%; min-width: 0;">
                
                <!-- Section 1: Basic Info -->
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-primary text-white py-3">
                        <h5 class="mb-0 text-white fw-bold"><i class="las la-info-circle me-2"></i>البيانات الأساسية</h5>
                    </div>
                    <div class="card-body p-4">
                        <div class="row g-3">
                            <div class="col-md-8">
                                <label class="form-label fw-bold">اسم المشروع <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <input type="text" name="name" class="form-control form-control-lg" required x-model="form.name" placeholder="أدخل اسم المشروع المميز..." autofocus>
                                    <span class="input-group-text bg-light text-muted"><i class="las la-project-diagram"></i></span>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-bold">الحالة</label>
                                <div class="input-group">
                                    <select name="status" class="form-select form-select-lg" x-model="form.status">
                                        <option value="pending">معلق (Pending)</option>
                                        <option value="active">نشط (Active)</option>
                                        <option value="completed">مكتمل (Completed)</option>
                                    </select>
                                    <span class="input-group-text bg-light text-muted"><i class="las la-flag"></i></span>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold">العميل <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <select name="client_id" class="form-select" required x-model="form.client_id">
                                        <option value="">-- اختر العميل --</option>
                                        @foreach($clients as $client)
                                            <option value="{{ $client->id }}">{{ $client->name }}</option>
                                        @endforeach
                                    </select>
                                    <span class="input-group-text bg-light text-muted"><i class="las la-user-tie"></i></span>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold">الحساب</label>
                                <div class="input-group">
                                    <select name="account_id" class="form-select" x-model="form.account_id">
                                        <option value="">-- اختر الحساب --</option>
                                        @foreach($accounts as $account)
                                            <option value="{{ $account->id }}" @selected(old('account_id', $project->account_id ?? '') == $account->id)>
                                                {{ $account->code }} - {{ $account->aname }}
                                            </option>
                                        @endforeach
                                    </select>
                                    <span class="input-group-text bg-light text-muted"><i class="las la-wallet"></i></span>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold">نوع المشروع</label>
                                <div class="input-group">
                                    <select name="project_type_id" class="form-select" x-model="form.project_type_id">
                                        <option value="">-- اختر النوع --</option>
                                        @foreach($projectTypes as $type)
                                            <option value="{{ $type->id }}">{{ $type->name }}</option>
                                        @endforeach
                                    </select>
                                    <span class="input-group-text bg-light text-muted"><i class="las la-shapes"></i></span>
                                </div>
                            </div>
                            <div class="col-12">
                                <label class="form-label fw-bold">وصف المشروع</label>
                                <textarea name="description" class="form-control" rows="4" x-model="form.description" placeholder="اكتب وصفاً مختصراً للمشروع وأهدافه..."></textarea>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Section 2: Schedule -->
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-primary text-white py-3">
                        <h5 class="mb-0 text-white fw-bold"><i class="las la-calendar-alt me-2"></i>الجدول الزمني وإعدادات الدوام</h5>
                    </div>
                    <div class="card-body p-4">
                        <div class="row g-4">
                            <div class="col-md-6">
                                <div class="card bg-light border-0 h-100">
                                    <div class="card-body">
                                        <h6 class="fw-bold mb-3 text-dark">تكوين التواريخ</h6>
                                        <div class="mb-3">
                                            <label class="form-label">تاريخ البدء <span class="text-danger">*</span></label>
                                            <div class="input-group">
                                                <input type="date" name="start_date" class="form-control" required x-model="form.start_date" @change="calculateAllDates()">
                                                <span class="input-group-text bg-white text-muted"><i class="las la-calendar"></i></span>
                                            </div>
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label">تاريخ الانتهاء المتوقع</label>
                                            <div class="input-group">
                                                <input type="text" class="form-control bg-white" readonly :value="calculatedEndDate || '-'" disabled>
                                                <span class="input-group-text"><i class="las la-calculator"></i></span>
                                            </div>
                                            <small class="text-muted d-block mt-1">يُحسب تلقائياً بناءً على مدة البنود التالية.</small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="card bg-light border-0 h-100">
                                    <div class="card-body">
                                        <h6 class="fw-bold mb-3 text-dark">ساعات وأيام العمل</h6>
                                        <div class="mb-3">
                                            <label class="form-label">ساعات العمل اليومية</label>
                                            <div class="input-group">
                                                <input type="number" name="daily_work_hours" class="form-control" min="1" max="24" x-model="form.daily_work_hours">
                                                <span class="input-group-text bg-white text-muted"><i class="las la-clock"></i></span>
                                            </div>
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label">أيام العمل في الأسبوع</label>
                                            <div class="input-group">
                                                <input type="text" class="form-control" readonly :value="7 - form.holidays.length + ' أيام'">
                                                <span class="input-group-text bg-white text-muted"><i class="las la-calendar-week"></i></span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="col-12">
                                <label class="form-label fw-bold mb-3">حدد أيام الإجازة الأسبوعية:</label>
                                <div class="row g-2">
                                    <template x-for="(day, index) in daysOfWeek" :key="index">
                                        <div class="col">
                                            <div class="day-card" :class="{'active': form.holidays.includes(index.toString())}" 
                                                    @click="toggleHoliday(index.toString())">
                                                <i class="las fs-3 mb-1" :class="form.holidays.includes(index.toString()) ? 'la-coffee' : 'la-check-circle'"></i>
                                                <div class="small fw-bold" x-text="day"></div>
                                            </div>
                                        </div>
                                    </template>
                                </div>
                                <input type="hidden" name="weekly_holidays" :value="form.holidays.join(',')">
                            </div>

                            <div class="col-12">
                                <hr>
                                <h6 class="fw-bold mb-3"><i class="las la-copy me-2"></i>استخدام قوالب جاهزة (اختياري)</h6>
                                <div class="row g-2">
                                    @forelse($templates as $template)
                                        <div class="col-md-4">
                                            <div class="form-check card p-3 h-100">
                                                <input class="form-check-input me-2" type="checkbox" id="template_{{ $template->id }}" 
                                                    @change="toggleTemplate({{ $template->id }}, $event.target.checked)">
                                                <label class="form-check-label fw-bold stretched-link" for="template_{{ $template->id }}">
                                                    {{ $template->name }}
                                                </label>
                                            </div>
                                        </div>
                                    @empty
                                        <div class="col-12 text-muted fst-italic">لا توجد قوالب محفوظة حالياً.</div>
                                    @endforelse
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Section 3: Items -->
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-primary text-white py-3 d-flex justify-content-between align-items-center">
                        <h5 class="mb-0 text-white fw-bold"><i class="las la-tasks me-2"></i>بنود ونطاق العمل</h5>
                        <span class="badge bg-white text-primary rounded-pill" x-text="items.length + ' بنود'"></span>
                    </div>
                    
                    <div class="card-body p-0">
                        <!-- Toolbar -->
                        <div class="p-3 bg-white border-bottom d-flex flex-wrap gap-3 align-items-center justify-content-between">
                            <!-- Search -->
                            <div class="position-relative" style="min-width: 350px;">
                                <div class="input-group">
                                    <span class="input-group-text bg-white border-end-0 ps-3"><i class="las la-search text-muted"></i></span>
                                    <input type="text" class="form-control border-start-0" placeholder="بحث لإضافة بنود (Search using name or code)..." 
                                        x-model="searchQuery" @input.debounce.300ms="searchItems()" autofocus>
                                    <button class="btn btn-outline-secondary border-start-0 rounded-end" type="button" @click="searchQuery = ''; searchResults = []" x-show="searchQuery.length > 0"><i class="las la-times"></i></button>
                                </div>
                                <!-- Dropdown for Search Results -->
                                <div class="card position-absolute w-100 shadow-lg mt-1 border-0" style="z-index: 1050; max-height: 350px; overflow-y: auto;" x-show="searchResults.length > 0" @click.outside="searchResults = []" x-transition>
                                    <div class="card-header bg-light py-2 small fw-bold text-muted">نتائج البحث</div>
                                    <ul class="list-group list-group-flush">
                                        <template x-for="item in searchResults" :key="item.id">
                                            <li class="list-group-item list-group-item-action d-flex justify-content-between align-items-center p-3 cursor-pointer border-bottom" @click="addItem(item)">
                                                <div>
                                                    <div class="fw-bold text-dark" x-text="item.name"></div>
                                                    <div class="d-flex align-items-center gap-2 mt-1">
                                                        <span class="badge bg-light text-dark border fw-normal" x-text="item.unit"></span>
                                                        <small class="text-muted" x-show="item.code" x-text="item.code"></small>
                                                    </div>
                                                </div>
                                                <button class="btn btn-sm btn-primary rounded-circle shadow-sm"><i class="las la-plus"></i></button>
                                            </li>
                                        </template>
                                    </ul>
                                </div>
                            </div>

                            <!-- View Toggles (Visual Only for now) -->
                            <div class="btn-group" role="group">
                                <button type="button" class="btn btn-sm" :class="!isGrouped ? 'btn-primary' : 'btn-outline-primary'" @click="toggleGroupedMode(false)"><i class="las la-list"></i> عادي</button>
                                <button type="button" class="btn btn-sm" :class="isGrouped ? 'btn-primary' : 'btn-outline-primary'" @click="toggleGroupedMode(true)"><i class="las la-layer-group"></i> مجمع</button>
                            </div>

                            <!-- Bulk Actions -->
                            <div class="d-flex align-items-center gap-2">
                                <select class="form-select form-select-sm" style="width: 200px;" x-model="bulkAction">
                                    <option value="">-- عمليات جماعية --</option>
                                    <option value="duplicate">نسخ المحدد (Duplicate)</option>
                                    <option value="move">نقل لمشروع فرعي (Move)</option>
                                    <option value="export_csv">تصدير CSV</option>
                                    <option value="delete">حذف المحدد (Delete)</option>
                                </select>
                                <button type="button" class="btn btn-primary btn-sm" @click="executeBulkAction()" :disabled="selectedItems.length === 0">
                                    <i class="las la-play"></i> تنفيذ
                                </button>
                            </div>
                        </div>

                        <!-- Items Table -->
                        <div class="table-responsive" style="width: 100%; max-width: 85vw; overflow-x: auto;">
                            <table class="table table-premium table-bordered table-hover align-middle mb-0 text-center custom-table" style="min-width: 2400px;">
                                <thead class="bg-light text-dark fw-bold">
                                    <tr style="border-bottom: 2px solid #dee2e6;">
                                        <th style="width: 40px;"><input type="checkbox" class="form-check-input" @change="toggleAll($event.target.checked)"></th>
                                        <th style="width: 40px;"><i class="las la-th"></i></th>
                                        <th style="width: 50px;">#</th>
                                        <th style="width: 350px;" class="text-start">Item Name</th>
                                        <th style="width: 200px;">المشروع الفرعي</th>
                                        <th style="width: 250px;">Notes</th>
                                        <th style="width: 100px;">قابل للقياس</th>
                                        <th style="width: 140px;">Total Quantity</th>
                                        <th style="width: 140px;">Estimated Daily Qty</th>
                                        <th style="width: 140px;">Estimated Duration (Days)</th>
                                        <th style="width: 220px;">Predecessor</th>
                                        <th style="width: 160px;">Dependency Type</th>
                                        <th style="width: 120px;">Lag (Days)</th>
                                    <th style="width: 150px;">Start Date</th>
                                        <th style="width: 150px;">End Date</th>
                                        <th style="width: 130px;">Actions</th>
                                    </tr>
                                </thead>
                                <tbody class="drag-container" x-ref="sortableList" x-init="Sortable.create($el, { handle: '.drag-handle', onEnd: (evt) => { let item = items.splice(evt.oldIndex, 1)[0]; items.splice(evt.newIndex, 0, item); updateItemOrders(); calculateAllDates(); } })">
                            <template x-if="!isGrouped">
                            <template x-for="(row, index) in items" :key="row.id">
                                        <tr class="bg-white" :class="{'table-active': selectedItems.includes(index)}">
                                            <!-- Hidden Inputs -->
                                            <input type="hidden" :name="'items['+index+'][id]'" :value="row.id">
                                            <input type="hidden" :name="'items['+index+'][work_item_id]'" :value="row.work_item_id">
                                            <input type="hidden" :name="'items['+index+'][start_date]'" :value="row.start_date">
                                            <input type="hidden" :name="'items['+index+'][end_date]'" :value="row.end_date">
                                            <input type="hidden" :name="'items['+index+'][duration]'" :value="row.duration">
                                            <input type="hidden" :name="'items['+index+'][is_measurable]'" :value="row.is_measurable ? 1 : 0">
                                            <input type="hidden" :name="'items['+index+'][item_order]'" :value="row.item_order">

                                            <td>
                                                <input type="checkbox" class="form-check-input" :value="index" x-model="selectedItems">
                                            </td>
                                            <td class="drag-handle text-muted" style="cursor: move;"><i class="las la-braille"></i></td>
                                            <td class="fw-bold text-primary bg-light" x-text="index + 1"></td>
                                            
                                            <!-- Item Name -->
                                            <td class="text-start">
                                                <div class="fw-bold" x-text="row.name"></div>
                                                <div class="text-muted small d-flex align-items-center gap-1">
                                                    <i class="las la-folder-open text-warning"></i>
                                                    <span x-text="row.subproject_name || 'غير محدد'"></span> 
                                                </div>
                                                <div class="text-muted small">
                                                    <i class="las la-ruler-combined"></i> <span x-text="row.unit"></span>
                                                </div>
                                            </td>

                                            <!-- Subproject -->
                                            <td>
                                                <input type="text" class="form-control form-control-premium" :name="'items['+index+'][subproject_name]'" x-model="row.subproject_name" placeholder="اختر أو أدخل مشروع فرعي">
                                            </td>

                                            <!-- Notes -->
                                            <td>
                                                <input type="text" class="form-control form-control-premium" :name="'items['+index+'][notes]'" x-model="row.notes" placeholder="">
                                            </td>

                                            <!-- Measurable -->
                                            <td>
                                                    <input class="form-check-input" type="checkbox" :name="'items['+index+'][is_measurable]'" x-model="row.is_measurable" value="1" @change="calculateDuration(row)">
                                            </td>

                                            <!-- Qty -->
                                            <td>
                                                <input type="number" class="form-control form-control-premium" :name="'items['+index+'][total_quantity]'" 
                                                    x-model.number="row.total_quantity" @input="calculateDuration(row)" step="0.01">
                                            </td>
                                            <td>
                                                <input type="number" class="form-control form-control-premium" :name="'items['+index+'][estimated_daily_qty]'" 
                                                    x-model.number="row.estimated_daily_qty" @input="calculateDuration(row)" step="0.01">
                                            </td>
                                            
                                            <!-- Duration -->
                                            <td>
                                                <input type="number" class="form-control bg-white form-control-premium" readonly :value="row.duration">
                                            </td>

                                            <!-- Predecessor -->
                                            <td>
                                                <select class="form-select form-control-premium" x-model="row.predecessor" @change="calculateAllDates()">
                                                    <option value="">بدون</option>
                                                    <template x-for="(p, i) in items" :key="p.id">
                                                        <option :value="p.id" :selected="String(p.id) === String(row.predecessor)" x-text="(i + 1) + '. ' + p.name"></option>
                                                    </template>
                                                </select>
                                            </td>
                                            
                                            <!-- Dependency -->
                                            <td>
                                                <select class="form-select form-control-premium" :name="'items['+index+'][dependency_type]'" x-model="row.dependency_type" @change="calculateAllDates()">
                                                    <option value="end_to_start">توالي (FS)</option>
                                                    <option value="start_to_start">توازي (SS)</option>
                                                </select>
                                            </td>

                                            <!-- Lag -->
                                            <td>
                                                <input type="number" class="form-control form-control-premium" :name="'items['+index+'][lag]'" x-model.number="row.lag" placeholder="0" @input="calculateAllDates()">
                                            </td>

                                            <!-- Dates -->
                                            <td><input type="date" class="form-control bg-white border-0 p-0 text-center" readonly :value="row.start_date" style=""></td>
                                            <td><input type="date" class="form-control bg-white border-0 p-0 text-center" readonly :value="row.end_date" style=""></td>

                                            <!-- Actions -->
                                            <td>
                                                <div class="btn-group">
                                                    <button type="button" class="btn btn-outline-success" @click="duplicateItem(row)" title="نسخ">
                                                        <i class="las la-copy"></i>
                                                    </button>
                                                    <button type="button" class="btn btn-outline-danger" @click="removeItem(index)" title="حذف">
                                                        <i class="las la-trash"></i>
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    </template>
                                    
                                    <tr x-show="items.length === 0">
                                        <td colspan="16" class="text-center py-5">
                                            <div class="text-muted opacity-50">
                                                <i class="las la-search display-4 mb-2"></i>
                                                <p class="mb-0 fw-bold">لا توجد بنود</p>
                                                <small>استخدم شريط البحث أعلاه لإضافة بنود</small>
                                            </div>
                                        </td>
                                    </tr>
                                </template>
                                </tbody>

                            <!-- Grouped View -->
                            <template x-if="isGrouped">
                                <tbody class="border-0">
                                    <template x-for="group in subprojectSummary" :key="group.name">
                                        <tr class="border-0">
                                            <td colspan="16" class="p-0 border-0">
                                                <!-- Group Header -->
                                                <div class="card mb-3 border border-primary shadow-sm mt-3">
                                                    <div class="card-header bg-primary text-white py-2">
                                                        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                                                            <div class="d-flex align-items-center gap-3">
                                                                <div class="d-flex align-items-center gap-2">
                                                                    <i class="las la-folder-open fs-4 text-warning"></i>
                                                                    <span class="fw-bold fs-5" x-text="group.name"></span>
                                                                </div>
                                                                <span class="badge bg-light text-primary rounded-pill px-3">
                                                                    بند <span x-text="group.count"></span>
                                                                </span>
                                                            </div>

                                                            <div class="d-flex align-items-center gap-3 flex-wrap small">
                                                                <div class="bg-white bg-opacity-25 px-3 py-1 rounded">
                                                                    <i class="las la-cubes"></i> الكمية: 
                                                                    <span class="fw-bold" x-text="group.total_quantity.toFixed(2)"></span>
                                                                </div>
                                                                
                                                                <div class="bg-white bg-opacity-25 px-3 py-1 rounded">
                                                                    <i class="las la-calendar-day"></i> يوم <span class="fw-bold" x-text="group.duration"></span>
                                                                </div>

                                                                <div class="bg-white bg-opacity-25 px-3 py-1 rounded">
                                                                    <i class="las la-calendar"></i> من: <span dir="ltr" x-text="group.formattedStart"></span>
                                                                    إلى: <span dir="ltr" x-text="group.formattedEnd"></span>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <!-- Group Items Table -->
                                                    <div class="card-body p-0">
                                                        <div class="table-responsive">
                                                            <table class="table table-hover mb-0">
                                                                <thead class="bg-light">
                                                                    <tr>
                                                                        <th style="width: 40px">
                                                                            <div class="form-check">
                                                                                <!-- Bulk select for group could be added here -->
                                                                            </div>
                                                                        </th>
                                                                        <th style="width: 50px">#</th>
                                                                        <th style="min-width: 200px">البند</th>
                                                                        <th style="width: 100px">الوحدة</th>
                                                                        <th style="width: 120px">المشروع الفرعي</th>
                                                                        <th style="width: 100px">الكمية</th>
                                                                        <th style="width: 100px">اليومية</th>
                                                                        <th style="width: 80px">المدة</th>
                                                                        <th style="width: 130px">ت. البداية</th>
                                                                        <th style="width: 130px">ت. النهاية</th>
                                                                        <th style="width: 150px">يعتمد على</th>
                                                                        <th style="width: 120px">نوع الاعتماد</th>
                                                                        <th style="width: 80px">Lag</th>
                                                                        <th style="width: 50px">قياسي</th>
                                                                        <th style="min-width: 150px">ملاحظات</th>
                                                                        <th style="width: 60px"></th>
                                                                    </tr>
                                                                </thead>
                                                                <tbody>
                                                                    <template x-for="(wrapper, groupIdx) in group.items" :key="wrapper.data.id">
                                                                        <tr>
                                                                            <td>
                                                                                <div class="form-check">
                                                                                    <input class="form-check-input" type="checkbox" :value="wrapper.originalIndex" x-model="selectedItems">
                                                                                </div>
                                                                            </td>
                                                                            <td class="text-center" x-text="wrapper.originalIndex + 1"></td>
                                                                            <td>
                                                                                <input type="hidden" :name="'items['+wrapper.originalIndex+'][id]'" :value="wrapper.data.id">
                                                                                <input type="hidden" :name="'items['+wrapper.originalIndex+'][work_item_id]'" x-model="wrapper.data.work_item_id">
                                                                                <input type="text" class="form-control form-control-premium" :name="'items['+wrapper.originalIndex+'][name]'" x-model="wrapper.data.name" readonly>
                                                                            </td>
                                                                            <td>
                                                                                <input type="text" class="form-control form-control-premium" :name="'items['+wrapper.originalIndex+'][unit]'" x-model="wrapper.data.unit" readonly>
                                                                            </td>
                                                                            <td>
                                                                                <input type="text" class="form-control form-control-premium" :name="'items['+wrapper.originalIndex+'][subproject_name]'" x-model="wrapper.data.subproject_name">
                                                                            </td>
                                                                            <td>
                                                                                <input type="number" step="0.01" class="form-control form-control-premium" :name="'items['+wrapper.originalIndex+'][total_quantity]'" x-model="wrapper.data.total_quantity" @input="calculateDuration(wrapper.data)">
                                                                            </td>
                                                                            <td>
                                                                                <input type="number" step="0.01" class="form-control form-control-premium" :name="'items['+wrapper.originalIndex+'][estimated_daily_qty]'" x-model="wrapper.data.estimated_daily_qty" @input="calculateDuration(wrapper.data)">
                                                                            </td>
                                                                            <td>
                                                                                <input type="number" class="form-control form-control-premium" :name="'items['+wrapper.originalIndex+'][duration]'" x-model="wrapper.data.duration" readonly>
                                                                            </td>
                                                                            <td>
                                                                                <input type="date" class="form-control form-control-premium" :name="'items['+wrapper.originalIndex+'][start_date]'" x-model="wrapper.data.start_date" @change="calculateAllDates()">
                                                                            </td>
                                                                            <td>
                                                                                <input type="date" class="form-control form-control-premium" :name="'items['+wrapper.originalIndex+'][end_date]'" x-model="wrapper.data.end_date" readonly>
                                                                            </td>
                                                                            <td>
                                                                                <select class="form-select" x-model="wrapper.data.predecessor" @change="calculateAllDates()">
                                                                                    <option value="">بدون</option>
                                                                                    <template x-for="(p, i) in items" :key="p.id">
                                                                                        <option :value="p.id" :selected="String(p.id) === String(wrapper.data.predecessor)" x-text="(i + 1) + '. ' + p.name"></option>
                                                                                    </template>
                                                                                </select>
                                                                            </td>
                                                                            <td>
                                                                                <select class="form-select" :name="'items['+wrapper.originalIndex+'][dependency_type]'" x-model="wrapper.data.dependency_type" @change="calculateAllDates()">
                                                                                    <option value="end_to_start">End to Start</option>
                                                                                    <option value="start_to_start">Start to Start</option>
                                                                                </select>
                                                                            </td>
                                                                            <td>
                                                                                <input type="number" class="form-control form-control-premium" :name="'items['+wrapper.originalIndex+'][lag]'" x-model="wrapper.data.lag" @change="calculateAllDates()">
                                                                            </td>
                                                                            <td class="text-center">
                                                                                <div class="form-check form-switch d-flex justify-content-center">
                                                                                    <input class="form-check-input" type="checkbox" :name="'items['+wrapper.originalIndex+'][is_measurable]'" x-model="wrapper.data.is_measurable" value="1">
                                                                                </div>
                                                                            </td>
                                                                            <td>
                                                                                <input type="text" class="form-control form-control-premium" :name="'items['+wrapper.originalIndex+'][notes]'" x-model="wrapper.data.notes">
                                                                            </td>
                                                                            <td class="text-center">
                                                                                <button type="button" class="btn btn-outline-danger btn-sm rounded-circle" @click="removeItem(wrapper.originalIndex)">
                                                                                    <i class="las la-trash"></i>
                                                                                </button>
                                                                            </td>
                                                                        </tr>
                                                                    </template>
                                                                </tbody>
                                                            </table>
                                                        </div>
                                                    </div>
                                                </div>
                                            </td>
                                        </tr>
                                    </template>
                                </tbody>
                            </template>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Section 4: Team & Submit -->
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-primary text-white py-3">
                        <h5 class="mb-0 text-white fw-bold"><i class="las la-users me-2"></i>فريق العمل والمكان</h5>
                    </div>
                    <div class="card-body p-4">
                        <div class="mb-4">
                            <label class="form-label fw-bold">منطقة العمل (Working Zone)</label>
                            <input type="text" name="working_zone" class="form-control form-control-lg" value="{{ old('working_zone', $project->working_zone) }}" x-model="form.working_zone" placeholder="مثال: المنطقة الشمالية، المبنى الرئيسي...">
                        </div>
                        
                        <hr class="my-4">
                        
                        <label class="form-label fw-bold mb-3 d-block">تعيين الموظفين للمشروع</label>
                        <div class="row g-3" style="max-height: 400px; overflow-y: auto;">
                            @foreach($employees as $employee)
                                <div class="col-md-4 col-lg-3">
                                    <label class="employee-card h-100">
                                        <input type="checkbox" name="employees[]" value="{{ $employee->id }}" class="form-check-input me-3 mt-0 rounded-circle" style="width: 1.25em; height: 1.25em;" @if($project->employees->contains($employee->id)) checked @endif>
                                        <div class="d-flex align-items-center w-100 p-2 rounded">
                                            <div class="avatar avatar-sm me-3 bg-gradient-primary text-white rounded-circle d-flex justify-content-center align-items-center fw-bold shadow-sm" style="width: 40px; height: 40px; font-size: 1rem;">
                                                {{ substr($employee->name, 0, 1) }}
                                            </div>
                                            <div>
                                                <div class="fw-bold text-dark">{{ $employee->name }}</div>
                                                <small class="text-muted">{{ $employee->job_title ?? 'Employee' }}</small>
                                            </div>
                                        </div>
                                    </label>
                                </div>
                            @endforeach
                        </div>

                        <hr class="my-4">
                        
                        <label class="form-label fw-bold mb-3 d-block">تعيين المستخدمين للمشروع (Users)</label>
                        <div class="row g-3" style="max-height: 400px; overflow-y: auto;">
                            @foreach($users as $user)
                                <div class="col-md-4 col-lg-3">
                                    <label class="employee-card h-100">
                                        <input type="checkbox" name="users[]" value="{{ $user->id }}" class="form-check-input me-3 mt-0 rounded-circle" style="width: 1.25em; height: 1.25em;" @if($project->users->contains($user->id)) checked @endif>
                                        <div class="d-flex align-items-center w-100 p-2 rounded">
                                            <div class="avatar avatar-sm me-3 bg-gradient-info text-white rounded-circle d-flex justify-content-center align-items-center fw-bold shadow-sm" style="width: 40px; height: 40px; font-size: 1rem;">
                                                {{ substr($user->name, 0, 1) }}
                                            </div>
                                            <div>
                                                <div class="fw-bold text-dark">{{ $user->name }}</div>
                                                <small class="text-muted">{{ $user->email }}</small>
                                            </div>
                                        </div>
                                    </label>
                                </div>
                            @endforeach
                        </div>
                    </div>
                    <div class="card-footer bg-white p-3 d-flex justify-content-end align-items-center">
                        <div class="d-flex gap-2 align-items-center">
                            <div class="form-check form-switch me-3">
                                <input class="form-check-input" type="checkbox" id="saveAsTemplate" name="save_as_template" x-model="form.save_as_template" value="1">
                                <label class="form-check-label fw-bold cursor-pointer" for="saveAsTemplate">حفظ كقالب جديد (Save as Template)</label>
                            </div>
                            <div x-show="form.save_as_template" x-transition.opacity>
                                <input type="text" name="template_name" class="form-control form-control-premium" placeholder="اسم القالب (اختياري)" x-model="form.template_name">
                            </div>
                            
                            <div class="d-flex gap-2 ms-3">
                                <button type="submit" name="save_action" value="draft" class="btn btn-warning px-4 btn-lg shadow-sm text-dark me-2" formnovalidate>
                                    <i class="las la-save me-2"></i> Save as Draft
                                </button>
                                
                                <button type="submit" name="status" value="active" class="btn btn-success px-5 btn-lg shadow-sm">
                                    <i class="las la-check-circle me-2"></i> الحفظ وتفعيل التعديلات
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </form>

    <!-- Move to Subproject Modal -->
    <div style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; z-index: 1070; background: rgba(0,0,0,0.5);" x-show="showMoveModal" x-transition.opacity>
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">نقل البنود المحددة لمشروع فرعي</h5>
                    <button type="button" class="btn-close" @click="showMoveModal = false"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">اسم المشروع الفرعي</label>
                        <input type="text" class="form-control form-control-premium" x-model="targetSubproject" placeholder="أدخل اسم المشروع الفرعي...">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" @click="showMoveModal = false">إلغاء</button>
                    <button type="button" class="btn btn-primary" @click="applyMoveToSubproject()">نقل</button>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script src="https://cdnjs.cloudflare.com/ajax/libs/Sortable/1.14.0/Sortable.min.js"></script>
<script src="{{ asset('js/project-form.js') }}?v=1.0.2"></script>
@endpush
@endsection
