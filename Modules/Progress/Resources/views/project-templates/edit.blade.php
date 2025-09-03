@extends('admin.dashboard')

@section('content')
    @include('components.breadcrumb', [
        'title' => __('قوالب المشاريع'),
        'items' => [
            ['label' => __('الرئيسيه'), 'url' => route('admin.dashboard')],
            ['label' => __('قوالب المشاريع'), 'url' => route('project.template.index')],
            ['label' => __('تعديل')],
        ],
    ])

    <div class="row">
        <div class="col-lg-12">
            <div class="card">
                <div class="card-header">
                    <h2>تعديل قالب مشروع</h2>
                </div>
                <div class="card-body">
                    <form action="{{ route('project.template.update', $projectTemplate->id) }}" method="POST">
                        @csrf
                        @method('PUT')

                        <div class="row">
                            {{-- اسم القالب --}}
                            <div class="mb-3 col-lg-6">
                                <label class="form-label" for="name">اسم القالب</label>
                                <input type="text" class="form-control" id="name" name="name"
                                    placeholder="ادخل اسم القالب" value="{{ old('name', $projectTemplate->name) }}">
                                @error('name')
                                    <small class="text-danger">{{ $message }}</small>
                                @enderror
                            </div>

                            {{-- الوصف --}}
                            <div class="mb-3 col-lg-6">
                                <label class="form-label" for="description">الوصف</label>
                                <textarea class="form-control" id="description" name="description" rows="3" placeholder="ادخل وصف القالب">{{ old('description', $projectTemplate->description) }}</textarea>
                                @error('description')
                                    <small class="text-danger">{{ $message }}</small>
                                @enderror
                            </div>
                        </div>

                        {{-- عناصر القالب --}}
                        <div class="card mb-4 border-0 shadow-sm rounded-3">
                            <div
                                class="card-header bg-light d-flex justify-content-between align-items-center rounded-top-3">
                                <h6 class="mb-0 fw-bold">{{ __('general.template_items') }}</h6>
                                <button type="button" id="add-row" class="btn btn-sm btn-primary rounded-3">
                                    <i class="fa-solid fa-plus me-1"></i> {{ __('general.add_item') }}
                                </button>
                            </div>
                            <div class="card-body p-0">
                                <table class="table table-bordered table-hover table-striped mb-0 align-middle text-center">
                                    <thead class="table-secondary">
                                        <tr>
                                            <th>{{ __('general.item') }}</th>
                                            <th>{{ __('general.unit') }}</th>
                                            <th>{{ __('general.default_quantity') }}</th>
                                            <th class="text-center">{{ __('general.action') }}</th>
                                        </tr>
                                    </thead>
                                    <tbody id="items-table">
                                        {{-- العناصر هتترندر بالـ JS --}}
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        {{-- الأزرار --}}
                        <div class="d-flex justify-content-start mt-4">
                            <button type="submit" class="btn btn-primary me-2">
                                <i class="las la-save"></i> تحديث
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
@endsection

@push('scripts')
    <script>
        let rowCount = 0;
        let workItems = @json($workItems);
        let initialItems = {!! $initialItems !!};

        // دالة لإنشاء صف
        function renderRow(index, item = null) {
            let selectedId = item ? item.work_item_id : '';
            let quantity = item ? item.default_quantity : 1;

            let options = `<option value="">{{ __('general.choose_item') }}</option>`;
            workItems.forEach(wi => {
                options += `<option value="${wi.id}" ${wi.id == selectedId ? 'selected' : ''}>
                                ${wi.name}
                            </option>`;
            });

            return `
                <tr>
                    <td>
                        <select name="items[${index}][work_item_id]" class="form-select rounded-3 work-item-select" required>
                            ${options}
                        </select>
                    </td>
                    <td>
                        <input type="text" class="form-control bg-light rounded-3 unit-field" value="" disabled>
                    </td>
                    <td>
                        <input type="number" name="items[${index}][default_quantity]" class="form-control rounded-3"
                               value="${quantity}" min="1">
                    </td>
                    <td class="text-center">
                        <button type="button" class="btn btn-outline-danger btn-sm rounded-3 removeRow">
                            <i class="fa-solid fa-trash"></i>
                        </button>
                    </td>
                </tr>
            `;
        }

        // تحميل العناصر القديمة
        function loadInitialItems() {
            let table = document.querySelector('#items-table');
            initialItems.forEach((item, i) => {
                table.insertAdjacentHTML('beforeend', renderRow(rowCount, item));
                rowCount++;
            });
            refreshUnits();
        }

        // تحديث حقل الوحدة تلقائيًا عند اختيار WorkItem
        function refreshUnits() {
            document.querySelectorAll('.work-item-select').forEach(select => {
                select.addEventListener('change', function() {
                    let selectedId = this.value;
                    let unitField = this.closest('tr').querySelector('.unit-field');
                    let wi = workItems.find(w => w.id == selectedId);
                    unitField.value = wi ? wi.unit : 'غير محدد';
                });

                // تعبئة الوحدة في البداية
                if (select.value) {
                    let wi = workItems.find(w => w.id == select.value);
                    let unitField = select.closest('tr').querySelector('.unit-field');
                    unitField.value = wi ? wi.unit : 'غير محدد';
                }
            });
        }

        // عند تحميل الصفحة
        document.addEventListener('DOMContentLoaded', function() {
            loadInitialItems();

            // إضافة صف جديد
            document.getElementById('add-row').addEventListener('click', function() {
                let table = document.querySelector('#items-table');
                table.insertAdjacentHTML('beforeend', renderRow(rowCount));
                rowCount++;
                refreshUnits();
            });

            // حذف صف
            document.addEventListener('click', function(e) {
                if (e.target.closest('.removeRow')) {
                    e.target.closest('tr').remove();
                }
            });
        });
    </script>
@endpush
