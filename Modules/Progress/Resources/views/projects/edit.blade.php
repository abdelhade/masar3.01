@extends('admin.dashboard')

@section('title', __('general.edit_project'))

@section('content')
    <div class="card modern-card">
        <div class="card-header gradient-header text-white">
            <h5 class="mb-0"><i class="fas fa-edit me-2"></i>{{ __('general.edit_project') }}: {{ $project->name }}</h5>
        </div>
        <div class="card-body p-4">
            <form action="{{ route('projects.update', $project) }}" method="POST" id="projectForm">
                @csrf
                @method('PUT')

                {{-- Basic Project Info --}}
                <div class="row mb-4">
                    <div class="col-md-6">
                        <label class="form-label fw-semibold text-primary"><i
                                class="fas fa-folder-open me-1"></i>{{ __('general.project_name') }}</label>
                        <input type="text" name="name" class="form-control rounded-pill shadow-sm"
                            value="{{ old('name', $project->name ?? '') }}" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold text-primary"><i
                                class="fas fa-user-tie me-1"></i>{{ __('general.client') }}</label>
                        <select name="client_id" class="form-select rounded-pill shadow-sm" required>
                            <option value="">{{ __('general.select_client') }}</option>
                            @foreach ($clients as $client)
                                <option value="{{ $client->id }}" @selected(old('client_id', $project->client_id ?? '') == $client->id)>
                                    {{ $client->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="d-flex gap-3 mb-4">
                    <div class="flex-fill">
                        <label for="status" class="form-label fw-semibold text-primary"><i
                                class="fas fa-tasks me-1"></i>{{ __('general.status') }}</label>
                        <select name="status" id="status" class="form-select rounded-pill shadow-sm">
                            <option value="pending"
                                {{ old('status', $project->status ?? '') == 'pending' ? 'selected' : '' }}>
                                {{ __('general.status_pending') }}
                            </option>
                            <option value="active"
                                {{ old('status', $project->status ?? '') == 'active' ? 'selected' : '' }}>
                                {{ __('general.status_active') }}
                            </option>
                            <option value="completed"
                                {{ old('status', $project->status ?? '') == 'completed' ? 'selected' : '' }}>
                                {{ __('general.status_completed') }}
                            </option>
                        </select>
                    </div>
                    <div class="flex-fill">
                        <label for="project_type_id" class="form-label fw-semibold text-primary">
                            <i class="fas fa-diagram-project me-1"></i>{{ __('general.project__type') }}
                        </label>
                        <select name="project_type_id" id="project_type_id" class="form-select rounded-pill shadow-sm"
                            required>
                            <option value="">{{ __('general.select_project_type') }}</option>
                            @foreach ($projectTypes as $type)
                                <option value="{{ $type->id }}"
                                    {{ old('project_type_id', $project->project_type_id ?? '') == $type->id ? 'selected' : '' }}>
                                    {{ $type->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('project_type_id')
                            <div class="text-danger small mt-1">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="row mb-4">
                    <div class="col-md-6">
                        <label class="form-label fw-semibold text-primary"><i
                                class="fas fa-calendar-alt me-1"></i>{{ __('general.start_date') }}</label>
                        <input type="date" name="start_date" id="start_date" class="form-control rounded-pill shadow-sm"
                            value="{{ old('start_date', $project->start_date ? \Carbon\Carbon::parse($project->start_date)->format('Y-m-d') : '') }}"
                            required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold text-primary"><i
                                class="fas fa-calendar-check me-1"></i>{{ __('general.end_date') }}</label>
                        <input type="date" name="end_date" id="end_date" class="form-control rounded-pill shadow-sm"
                            value="{{ old('end_date', $project->end_date ? \Carbon\Carbon::parse($project->end_date)->format('Y-m-d') : '') }}">
                        <small class="text-muted">{{ __('general.calculated_automatically') }}</small>
                    </div>
                </div>

                <div class="mb-4">
                    <label class="form-label fw-semibold text-primary"><i
                            class="fas fa-align-left me-1"></i>{{ __('general.description') }}</label>
                    <textarea name="description" class="form-control shadow-sm rounded-3" rows="3">{{ old('description', $project->description ?? '') }}</textarea>
                </div>

                {{-- Work Settings --}}
                <div class="divider my-4"></div>
                <div class="row mb-4">
                    <div class="col-md-4">
                        <label
                            class="form-label fw-semibold text-primary">{{ __('general.working_days_per_week') }}</label>
                        <input type="number" name="working_days" id="working_days" class="form-control rounded-pill shadow-sm"
                            min="1" max="7"
                            value="{{ old('working_days', $project->working_days ?? 5) }}" required>
                        <small class="text-muted">{{ __('general.working_days_hint') }}</small>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold text-primary">{{ __('general.daily_work_hours') }}</label>
                        <input type="number" name="daily_work_hours" id="daily_work_hours" class="form-control rounded-pill shadow-sm"
                            min="1" max="24"
                            value="{{ old('daily_work_hours', $project->daily_work_hours ?? 8) }}" required>
                        <small class="text-muted">{{ __('general.daily_work_hours_hint') }}</small>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold text-primary">{{ __('general.holidays') }}</label>
                        <input type="number" name="holidays" id="holidays" class="form-control rounded-pill shadow-sm" min="0"
                            value="{{ old('holidays', $project->holidays ?? 0) }}">
                        <small class="text-muted">{{ __('general.holidays_hint') }}</small>
                    </div>
                </div>

                <div class="mb-4">
                    <div class="alert modern-alert">
                        <h6 class="fw-bold text-primary"><i
                                class="fas fa-clock me-1"></i>{{ __('general.actual_duration') }}</h6>
                        <div id="project-duration-calculation">
                            {{ __('general.duration_calculation_placeholder') }}
                        </div>
                    </div>
                </div>

                {{-- Template Selection --}}
                <div class="divider my-4"></div>
                <div class="mb-4">
                    <label for="template_id" class="form-label fw-semibold text-primary"><i
                            class="fas fa-layer-group me-1"></i>{{ __('general.select_template') }}</label>
                    <select id="template_id" class="form-select rounded-pill shadow-sm">
                        <option value="">-- {{ __('general.select') }} --</option>
                        @foreach ($templates as $template)
                            <option value="{{ $template['id'] }}" data-items='@json($template['items'])'
                                @if (old('template_id', $project->template_id ?? '') == $template['id']) selected @endif>
                                {{ $template['name'] }}
                            </option>
                        @endforeach
                    </select>
                    <input type="hidden" name="template_id" id="template_id_input"
                        value="{{ old('template_id', $project->template_id ?? '') }}">
                    <small class="text-muted d-block mt-1">{{ __('general.template_selection_hint') }}</small>
                </div>

                {{-- Work Items Selection --}}
                <div class="mb-4">
                    <label class="form-label fw-semibold text-primary"><i
                            class="fas fa-list-check me-1"></i>{{ __('general.select_items_for_project') }}</label>
                    <select id="work-items-select" class="form-select shadow-sm rounded-3" multiple>
                        @foreach ($workItems as $item)
                            <option value="{{ $item->id }}" data-unit="{{ $item->unit }}"
                                @if (in_array($item->id, old('items') ? array_keys(old('items')) : $project->items->pluck('work_item_id')->toArray() ?? [])) selected @endif>
                                {{ $item->name }} ({{ $item->unit }})
                            </option>
                        @endforeach
                    </select>
                    <small class="text-muted">{{ __('general.select_multiple_items') }}</small>
                </div>

                {{-- Selected Items Container --}}
                <div class="mt-3">
                    <table class="table table-bordered table-striped align-middle shadow-sm"
                        id="selected-items-table">
                        <thead class="table-primary">
                            <tr>
                                <th>{{ __('general.item_name') }}</th>
                                <th>{{ __('general.unit') }}</th>
                                <th>{{ __('general.total_quantity') }}</th>
                                <th>{{ __('general.estimated_daily_qty') }}</th>
                                <th>{{ __('general.estimated_duration') }} ({{ __('general.days') }})</th>
                                <th>{{ __('general.actions') }}</th>
                            </tr>
                        </thead>
                        <tbody id="selected-items-container"></tbody>
                    </table>
                </div>

                {{-- Working Zone and Employees --}}
                <div class="divider my-4"></div>
                <div class="mb-4">
                    <label for="working_zone" class="form-label fw-semibold text-primary"><i
                            class="fas fa-map-marker-alt me-1"></i>{{ __('general.working_zone') }}</label>
                    <input type="text" class="form-control rounded-pill shadow-sm" id="working_zone"
                        name="working_zone" value="{{ old('working_zone', $project->working_zone ?? '') }}" required>
                </div>

                <div class="mb-4">
                    <label class="form-label fw-semibold text-primary"><i
                            class="fas fa-users me-1"></i>{{ __('general.employees') }}</label>
                    <div class="border rounded-3 p-3 bg-light shadow-sm" style="max-height: 220px; overflow-y: auto;">
                        @foreach ($employees as $employee)
                            <div class="form-check py-2">
                                <input class="form-check-input" type="checkbox" name="employees[]"
                                    id="employee_{{ $employee->id }}" value="{{ $employee->id }}"
                                    @if (in_array($employee->id, old('employees', $project->employees->pluck('id')->toArray()))) checked @endif>
                                <label class="form-check-label" for="employee_{{ $employee->id }}">
                                    {{ $employee->name }}
                                    @if ($employee->position)
                                        <small class="text-muted">({{ $employee->position }})</small>
                                    @endif
                                </label>
                            </div>
                        @endforeach
                    </div>
                    <small class="text-muted">{{ __('general.select_multiple_by_clicking') }}</small>
                </div>

                <div class="text-end mt-4">
                    <button type="submit" class="btn btn-gradient me-2 px-4">
                        <i class="fas fa-save me-1"></i> {{ __('general.save_changes') }}
                    </button>
                    <a href="{{ route('projects.index') }}" class="btn btn-outline-secondary px-4">
                        <i class="fas fa-times me-1"></i> {{ __('general.cancel') }}
                    </a>
                </div>
            </form>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const workItems = @json($workItems);
            const workItemsSelect = document.getElementById('work-items-select');
            const container = document.getElementById('selected-items-container');
            const templateSelect = document.getElementById('template_id');
            const templateHiddenInput = document.getElementById('template_id_input');
            const startDateInput = document.getElementById('start_date');
            const endDateInput = document.getElementById('end_date');
            let currentTemplateId = templateHiddenInput.value || null;

            // إضافة بند للجدول
            function addItemToContainer(itemId, quantity = '', dailyQty = '', fallback = null, source = null) {
                if (!itemId) return;
                if (document.querySelector(`tr[data-item-id="${itemId}"]`)) return;

                const item = workItems.find(i => i.id == itemId) || fallback;
                if (!item) return;

                const name = item?.name || `Item #${itemId}`;
                const unit = item?.unit || '';

                const row = document.createElement('tr');
                row.dataset.itemId = itemId;
                if (source) row.dataset.templateSource = source;

                row.innerHTML = `
                    <td>
                        <input type="hidden" name="items[${itemId}][work_item_id]" value="${itemId}">
                        ${name}
                    </td>
                    <td>${unit}</td>
                    <td>
                        <input type="number" step="0.01" min="0"
                               name="items[${itemId}][total_quantity]"
                               class="form-control form-control-sm total-quantity"
                               value="${quantity || ''}" required>
                    </td>
                    <td>
                        <input type="number" step="0.01" min="0.01"
                               name="items[${itemId}][estimated_daily_qty]"
                               class="form-control form-control-sm estimated-daily-qty"
                               value="${dailyQty || ''}"
                               placeholder="{{ __('general.estimated_daily_qty') }}">
                    </td>
                    <td class="item-duration">0</td>
                    <td>
                        <button type="button" class="btn btn-sm btn-outline-danger remove-item">
                            <i class="fas fa-times"></i>
                        </button>
                    </td>
                `;

                container.appendChild(row);

                const totalQtyInput = row.querySelector('.total-quantity');
                const estimatedQtyInput = row.querySelector('.estimated-daily-qty');
                const durationCell = row.querySelector('.item-duration');

                function recalc() {
                    const totalQty = parseFloat(totalQtyInput.value) || 0;
                    const est = parseFloat(estimatedQtyInput.value) || 0;
                    durationCell.textContent = (totalQty > 0 && est > 0) ? Math.ceil(totalQty / est) : '0';
                    calculateProjectEndDate();
                }

                totalQtyInput.addEventListener('input', recalc);
                estimatedQtyInput.addEventListener('input', recalc);

                row.querySelector('.remove-item').addEventListener('click', function () {
                    row.remove();
                    calculateProjectEndDate();
                });

                // حساب أولي للصف المضاف
                recalc();
            }

            // حساب تاريخ نهاية المشروع
            function calculateProjectEndDate() {
                const startDate = new Date(startDateInput.value);
                if (isNaN(startDate)) return;

                let maxDurationDays = 0;
                document.querySelectorAll('.item-duration').forEach(cell => {
                    const d = parseInt(cell.textContent) || 0;
                    if (d > maxDurationDays) maxDurationDays = d;
                });

                if (maxDurationDays === 0) {
                    endDateInput.value = '';
                    calculateProjectDuration();
                    return;
                }

                let workingDaysPerWeek = parseInt(document.querySelector('input[name="working_days"]').value) || 5;
                let holidays = parseInt(document.querySelector('input[name="holidays"]').value || 0);

                // تحديد أيام العمل بناءً على عدد أيام العمل في الأسبوع
                let workDaysMap = getWorkDaysMap(workingDaysPerWeek);

                let remainingDays = maxDurationDays;
                let currentDate = new Date(startDate);

                while (remainingDays > 0) {
                    currentDate.setDate(currentDate.getDate() + 1);

                    const dayOfWeek = currentDate.getDay(); // 0 الأحد .. 6 السبت

                    // التحقق إذا كان اليوم من أيام العمل المحددة
                    if (!workDaysMap[dayOfWeek]) continue;

                    // التحقق إذا كان هناك إجازات متبقية
                    if (holidays > 0) {
                        holidays--;
                        continue;
                    }

                    remainingDays--;
                }

                endDateInput.value = currentDate.toISOString().split('T')[0];
                calculateProjectDuration();
            }

            // دالة مساعدة لتحديد أيام العمل بناءً على عدد أيام العمل في الأسبوع
            function getWorkDaysMap(workingDaysPerWeek) {
                let workDaysMap = {};
                // نعتبر أن أيام العمل هي من الأحد إلى الخميس (0-4) كافتراضي
                // ويمكن تعديل هذا المنطق حسب احتياجاتك
                for (let i = 0; i < 7; i++) {
                    workDaysMap[i] = i < workingDaysPerWeek;
                }
                return workDaysMap;
            }

            // حساب مدة المشروع
            function calculateProjectDuration() {
                const startDate = new Date(startDateInput.value);
                const endDate = new Date(endDateInput.value);
                const workingDays = parseInt(document.querySelector('input[name="working_days"]').value) || 5;
                const holidays = parseInt(document.querySelector('input[name="holidays"]').value) || 0;
                const dailyHours = parseInt(document.querySelector('input[name="daily_work_hours"]').value) || 8;

                if (isNaN(startDate) || isNaN(endDate)) {
                    document.getElementById('project-duration-calculation').innerHTML =
                        '{{ __('general.duration_calculation_placeholder') }}';
                    return;
                }
                if (startDate > endDate) {
                    document.getElementById('project-duration-calculation').innerHTML =
                        '{{ __('general.end_date_after_start_date') }}';
                    return;
                }

                const diffTime = endDate - startDate;
                const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));

                let weekends = 0;
                if (workingDays < 7) {
                    const weekendsPerWeek = 7 - workingDays;
                    const weeks = Math.floor(diffDays / 7);
                    weekends = weeks * weekendsPerWeek;
                }

                const actualDays = diffDays - weekends - holidays;
                const totalHours = actualDays * dailyHours;

                document.getElementById('project-duration-calculation').innerHTML = `
                    <p>{{ __('general.total_days') }}: ${diffDays} {{ __('general.days') }}</p>
                    <p>{{ __('general.actual_work_days') }}: ${actualDays} {{ __('general.days') }}</p>
                    <p>{{ __('general.total_work_hours') }}: ${totalHours} {{ __('general.hours') }}</p>
                `;
            }

            // لما تختار بند من القائمة
            workItemsSelect.addEventListener('change', function() {
                [...this.selectedOptions].forEach(option => {
                    addItemToContainer(option.value);
                });
            });

            // اختيار القوالب
            templateSelect.addEventListener('change', function () {
                const templateId = this.value;
                templateHiddenInput.value = templateId || '';

                if (currentTemplateId) {
                    document.querySelectorAll(`tr[data-template-source="template-${currentTemplateId}"]`).forEach(el => el.remove());
                }
                if (!templateId) { currentTemplateId = null; return; }

                try {
                    const selectedOption = this.options[this.selectedIndex];
                    const items = JSON.parse(selectedOption.getAttribute('data-items'));
                    items.forEach(item => {
                        addItemToContainer(
                            item.work_item_id,
                            item.default_quantity || '',
                            item.estimated_daily_qty || '',
                            { name: item.name || `Item #${item.work_item_id}`, unit: item.unit || '' },
                            `template-${templateId}`
                        );
                    });
                    currentTemplateId = templateId;
                } catch (e) { console.error('Error loading template:', e); }
            });

            // باقي الأكواد
            document.getElementById('projectForm').addEventListener('submit', function (e) {
                const checkedEmployees = document.querySelectorAll('input[name="employees[]"]:checked');
                if (checkedEmployees.length === 0) {
                    e.preventDefault();
                    alert('{{ __('general.select_at_least_one_employee') }}');
                }
            });

            // إضافة أحداث لحساب المدة
            startDateInput.addEventListener('change', calculateProjectEndDate);
            document.querySelector('input[name="working_days"]').addEventListener('input', calculateProjectEndDate);
            document.querySelector('input[name="holidays"]').addEventListener('input', calculateProjectEndDate);
            document.querySelector('input[name="daily_work_hours"]').addEventListener('input', calculateProjectDuration);

            // تحميل العناصر الموجودة مسبقاً في المشروع
            const initialItems = @json($initialItems);
            initialItems.forEach(item => {
                addItemToContainer(
                    item.work_item_id,
                    item.total_quantity,
                    item.estimated_daily_qty,
                    { name: item.name, unit: item.unit },
                    currentTemplateId ? `template-${currentTemplateId}` : null
                );
            });

            // حساب أولي عند التحميل
            calculateProjectEndDate();
        });
    </script>

    <style>
        .modern-card {
            border: none;
            border-radius: 1rem;
            box-shadow: 0 0.5rem 1.5rem rgba(0, 0, 0, 0.1);
            overflow: hidden;
        }

        .gradient-header {
            background: linear-gradient(135deg, #4f46e5, #3b82f6);
            padding: 1rem 1.5rem;
        }

        .divider {
            border-top: 2px dashed #e5e7eb;
        }

        .modern-alert {
            background: #f0f9ff;
            border: 1px solid #bae6fd;
            border-radius: 0.75rem;
            padding: 1rem;
        }

        .btn-gradient {
            background: linear-gradient(135deg, #3b82f6, #2563eb);
            color: #fff;
            border: none;
            border-radius: 2rem;
            transition: all 0.3s ease;
        }

        .btn-gradient:hover {
            background: linear-gradient(135deg, #2563eb, #1d4ed8);
            box-shadow: 0 0.5rem 1rem rgba(37, 99, 235, 0.3);
        }

        .form-control,
        .form-select {
            transition: all 0.3s ease;
        }

        .form-control:focus,
        .form-select:focus {
            box-shadow: 0 0 0 0.25rem rgba(59, 130, 246, 0.25);
            border-color: #3b82f6;
        }

        .item-card {
            border-radius: 0.75rem;
            border: 1px solid #e5e7eb;
            background: #ffffff;
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }

        .item-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 0.75rem 1.5rem rgba(0, 0, 0, 0.1);
        }

        #selected-items-table {
            font-size: 0.875rem;
        }

        #selected-items-table th {
            white-space: nowrap;
        }

        #selected-items-table .form-control-sm {
            padding: 0.25rem 0.5rem;
            font-size: 0.775rem;
        }

        .item-duration {
            font-weight: bold;
            text-align: center;
            vertical-align: middle;
        }
    </style>
@endsection
