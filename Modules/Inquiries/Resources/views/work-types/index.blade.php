@extends('admin.dashboard')

@section('sidebar')
    @include('components.sidebar.inquiries')
@endsection

@section('content')
    @include('components.breadcrumb', [
        'title' => __('Work Types'),
        'items' => [['label' => __('Home'), 'url' => route('admin.dashboard')], ['label' => __('Work Types')]],
    ])

    <style>
        body {
            font-family: 'Cairo', sans-serif;
            background-color: #f8f9fa;
        }

        .tree-container {
            background: white;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            padding: 20px;
            height: fit-content;
        }

        .tree-item {
            border: 2px solid #e9ecef;
            border-radius: 8px;
            padding: 12px 15px;
            margin: 8px 0;
            background: #fff;
            cursor: pointer;
            transition: all 0.3s ease;
            position: relative;
        }

        .tree-item:hover {
            border-color: #007bff;
            box-shadow: 0 2px 8px rgba(0, 123, 255, 0.2);
        }

        .tree-item.active {
            border-color: #007bff;
            background: #e7f3ff;
        }

        .tree-item.selected {
            border-color: #28a745;
            background: #d4edda;
        }

        .tree-children {
            margin-right: 25px;
            margin-top: 10px;
            border-right: 2px dashed #dee2e6;
            padding-right: 15px;
        }

        .add-child-btn {
            position: absolute;
            left: 10px;
            top: 50%;
            transform: translateY(-50%);
            background: #28a745;
            border: none;
            color: white;
            width: 25px;
            height: 25px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 12px;
            opacity: 0;
            transition: opacity 0.3s ease;
        }

        .tree-item:hover .add-child-btn {
            opacity: 1;
        }

        .inline-form {
            background: #f8f9fa;
            border: 2px dashed #007bff;
            border-radius: 8px;
            padding: 15px;
            margin: 8px 0;
            animation: fadeIn 0.3s ease;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(-10px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .table-container {
            background: white;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            padding: 20px;
        }

        .form-check-input {
            cursor: pointer;
            width: 2.3rem;
            height: 1.2rem;
            margin: 0 !important;
        }

        .status-badge {
            padding: 5px 10px;
            border-radius: 15px;
            font-size: 12px;
            font-weight: 600;
        }

        .status-active {
            background: #d4edda;
            color: #155724;
        }

        .status-inactive {
            background: #f8d7da;
            color: #721c24;
        }

        .breadcrumb-item {
            background: #e9ecef;
            padding: 5px 10px;
            border-radius: 15px;
            margin: 2px;
            font-size: 12px;
        }

        .edit-form {
            background: #fff3cd;
            border: 2px solid #ffc107;
            border-radius: 8px;
            padding: 15px;
            margin: 8px 0;
            animation: fadeIn 0.3s ease;
        }
    </style>

    <div class="container-fluid mt-4">

        <div class="mb-3" id="pathDisplay" style="display: none;">
            <div class="d-flex align-items-center flex-wrap">
                <strong class="me-2">{{ __('Current Path:') }}</strong>
                <div id="pathBreadcrumb"></div>
            </div>
        </div>

        <div class="row">
            <div class="col-lg-6">
                <div class="tree-container">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h5 class="mb-0"><i class="fas fa-hard-hat me-2"></i>{{ __('Work Types Tree') }}</h5>
                        <button class="btn btn-primary btn-sm" onclick="addRootWorkType()">
                            <i class="fas fa-plus me-1"></i>{{ __('Add Main Work Type') }}
                        </button>
                    </div>

                    <div id="treeContainer">
                        <!-- Tree will be generated here -->
                    </div>
                </div>
            </div>

            <!-- Table View -->
            <div class="col-lg-6">
                <div class="table-container">
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead class="table-light">
                                <tr>
                                    <th>#</th>
                                    <th>{{ __('Name') }}</th>
                                    <th>{{ __('Level') }}</th>
                                    <th>{{ __('Status') }}</th>
                                    <th>{{ __('Actions') }}</th>
                                </tr>
                            </thead>
                            <tbody id="tableBody" class="text-center">
                                <!-- Table content will be generated here -->
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            let workTypesData = @json($workTypesTree);
            let selectedWorkType = null;
            let editingWorkType = null;

            function renderTree() {
                const container = document.getElementById('treeContainer');
                container.innerHTML = '';
                workTypesData.forEach(workType => {
                    container.appendChild(createTreeItem(workType, 0));
                });
                renderTable();
            }

            function createTreeItem(workType, level) {
                const item = document.createElement('div');
                item.className = 'tree-item';
                item.dataset.id = workType.id;

                if (selectedWorkType && selectedWorkType.id === workType.id) {
                    item.classList.add('selected');
                }

                item.innerHTML = `
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <i class="fas fa-${workType.children.length > 0 ? 'hard-hat' : 'tools'} me-2"></i>
                        <span class="worktype-name">${workType.name}</span>
                        ${workType.is_active ?
                            '<span class="status-badge status-active ms-2">مفعل</span>' :
                            '<span class="status-badge status-inactive ms-2">غير مفعل</span>'
                        }
                    </div>
                    <div class="action-buttons">
                        <button class="add-child-btn" onclick="addChild(${workType.id})" title="إضافة فرع">
                            <i class="fas fa-plus"></i>
                        </button>
                    </div>
                </div>
            `;

                item.addEventListener('click', (e) => {
                    if (!e.target.closest('.add-child-btn')) {
                        selectWorkType(workType);
                        updatePath(workType);
                    }
                });

                const container = document.createElement('div');
                container.appendChild(item);

                if (workType.children && workType.children.length > 0) {
                    const childrenContainer = document.createElement('div');
                    childrenContainer.className = 'tree-children';

                    workType.children.forEach(child => {
                        childrenContainer.appendChild(createTreeItem(child, level + 1));
                    });

                    container.appendChild(childrenContainer);
                }

                return container;
            }

            function selectWorkType(workType) {
                selectedWorkType = workType;

                // Remove previous selection
                document.querySelectorAll('.tree-item.selected').forEach(item => {
                    item.classList.remove('selected');
                });

                // Add selection to current item
                document.querySelector(`[data-id="${workType.id}"]`).classList.add('selected');
            }

            function updatePath(workType) {
                const path = getWorkTypePath(workType);
                const pathDisplay = document.getElementById('pathDisplay');
                const pathBreadcrumb = document.getElementById('pathBreadcrumb');

                if (path.length > 1) {
                    pathDisplay.style.display = 'block';
                    pathBreadcrumb.innerHTML = path.map((item, index) => {
                        return `<span class="breadcrumb-item">${item.name}</span>`;
                    }).join('<i class="fas fa-chevron-left mx-2"></i>');
                } else {
                    pathDisplay.style.display = 'none';
                }
            }

            function getWorkTypePath(workType) {
                const path = [workType];
                let current = workType;

                while (current.parent_id) {
                    const parent = findWorkTypeById(current.parent_id);
                    if (parent) {
                        path.unshift(parent);
                        current = parent;
                    } else {
                        break;
                    }
                }

                return path;
            }

            function findWorkTypeById(id, workTypes = workTypesData) {
                for (const workType of workTypes) {
                    if (workType.id === id) {
                        return workType;
                    }
                    if (workType.children) {
                        const found = findWorkTypeById(id, workType.children);
                        if (found) return found;
                    }
                }
                return null;
            }

            function addRootWorkType() {
                showInlineForm(null);
            }

            function addChild(parentId) {
                event.stopPropagation();
                const parent = findWorkTypeById(parentId);
                showInlineForm(parent);
            }

            function showInlineForm(parent) {
                // Remove any existing forms
                document.querySelectorAll('.inline-form, .edit-form').forEach(form => form.remove());

                const form = document.createElement('div');
                form.className = 'inline-form';
                form.innerHTML = `
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">اسم نوع العمل</label>
                        <input type="text" class="form-control" id="newWorkTypeName" placeholder="أدخل اسم نوع العمل">
                    </div>
                    <div class="col-md-3 d-flex justify-content-center align-items-center">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" id="newWorkTypeStatus" checked>
                            <label class="form-check-label ms-2" for="newWorkTypeStatus">الحالة</label>
                        </div>
                    </div>

                    <div class="col-md-3 d-flex align-items-end">
                        <button class="btn btn-success btn-sm me-2" onclick="saveNewWorkType(${parent ? parent.id : null})">
                            <i class="fas fa-check me-1"></i>حفظ
                        </button>
                        <button class="btn btn-danger btn-sm" onclick="cancelForm()">
                            <i class="fas fa-times me-1"></i>إلغاء
                        </button>
                    </div>
                </div>
                ${parent ? `<small class="text-muted">سيتم إضافة نوع العمل كفرع من: ${parent.name}</small>` : ''}
            `;

                if (parent) {
                    const parentElement = document.querySelector(`[data-id="${parent.id}"]`).closest('div');
                    const childrenContainer = parentElement.querySelector('.tree-children') ||
                        (() => {
                            const container = document.createElement('div');
                            container.className = 'tree-children';
                            parentElement.appendChild(container);
                            return container;
                        })();
                    childrenContainer.appendChild(form);
                } else {
                    document.getElementById('treeContainer').insertBefore(form, document.getElementById('treeContainer')
                        .firstChild);
                }

                document.getElementById('newWorkTypeName').focus();
            }

            async function saveNewWorkType(parentId) {
                const name = document.getElementById('newWorkTypeName').value.trim();
                const isActive = document.getElementById('newWorkTypeStatus').checked;

                if (!name) {
                    alert('يرجى إدخال اسم نوع العمل');
                    return;
                }

                const formData = {
                    name: name,
                    parent_id: parentId,
                    is_active: isActive,
                    _token: '{{ csrf_token() }}'
                };

                try {
                    const response = await fetch('{{ route('work.types.store') }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        body: JSON.stringify(formData)
                    });

                    const result = await response.json();

                    if (result.success) {
                        showToast(result.message, 'success');
                        await fetchWorkTypes();
                        renderTree();
                    } else {
                        showToast(result.message, 'error');
                    }

                } catch (error) {
                    showToast('حدث خطأ غير متوقع.', 'error');
                }

                cancelForm();
            }

            async function fetchWorkTypes() {
                try {
                    const response = await fetch('/work-types/tree');
                    if (!response.ok) {
                        throw new Error(`HTTP error! status: ${response.status}`);
                    }

                    const result = await response.json();

                    if (result.success) {
                        workTypesData = result.data;
                    } else {
                        throw new Error(result.message || 'Failed to load data');
                    }
                } catch (error) {
                    showToast('فشل في تحميل البيانات: ' + error.message, 'error');
                }
            }

            function cancelForm() {
                document.querySelectorAll('.inline-form, .edit-form').forEach(form => form.remove());
            }

            function editWorkType(workTypeId) {
                const workType = findWorkTypeById(workTypeId);
                if (!workType) return;

                editingWorkType = workType;

                // Remove any existing forms
                document.querySelectorAll('.inline-form, .edit-form').forEach(form => form.remove());

                const workTypeElement = document.querySelector(`[data-id="${workTypeId}"]`);
                const form = document.createElement('div');
                form.className = 'edit-form';
                form.innerHTML = `
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">اسم نوع العمل</label>
                        <input type="text" class="form-control" id="editWorkTypeName" value="${workType.name}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">الحالة</label>
                        <select class="form-select" id="editWorkTypeStatus">
                            <option value="true" ${workType.is_active ? 'selected' : ''}>مفعل</option>
                            <option value="false" ${!workType.is_active ? 'selected' : ''}>غير مفعل</option>
                        </select>
                    </div>
                    <div class="col-md-3 d-flex align-items-end">
                        <button class="btn btn-warning btn-sm me-2" onclick="updateWorkType()">
                            <i class="fas fa-save me-1"></i>تحديث
                        </button>
                        <button class="btn btn-danger btn-sm" onclick="cancelForm()">
                            <i class="fas fa-times me-1"></i>إلغاء
                        </button>
                    </div>
                </div>
            `;

                workTypeElement.parentNode.insertBefore(form, workTypeElement.nextSibling);
                document.getElementById('editWorkTypeName').focus();
            }

            async function updateWorkType() {
                if (!editingWorkType) return;

                const name = document.getElementById('editWorkTypeName').value.trim();
                const isActive = document.getElementById('editWorkTypeStatus').value === 'true';

                if (!name) {
                    alert('يرجى إدخال اسم نوع العمل');
                    return;
                }

                const formData = {
                    name: name,
                    is_active: isActive,
                    _token: '{{ csrf_token() }}'
                };

                const url = '{{ route('work.types.update', ['work_type' => ':id']) }}'.replace(':id', editingWorkType.id);

                try {
                    const response = await fetch(url, {
                        method: 'PUT',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        body: JSON.stringify(formData)
                    });

                    const result = await response.json();
                    if (result.success) {
                        showToast(result.message, 'success');
                        await fetchWorkTypes();
                        renderTree();
                    } else {
                        showToast(result.message || 'فشل التحديث', 'error');
                    }
                } catch (error) {
                    showToast('حدث خطأ غير متوقع.', 'error');
                }

                cancelForm();
                editingWorkType = null;
            }

            async function deleteWorkType(workTypeId) {
                if (!confirm('هل أنت متأكد من حذف نوع العمل هذا؟ سيتم حذف جميع الفروع التابعة له.')) {
                    return;
                }

                const url = '{{ route('work.types.destroy', ['work_type' => ':id']) }}'.replace(':id', workTypeId);

                try {
                    const response = await fetch(url, {
                        method: 'DELETE',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        }
                    });

                    const result = await response.json();
                    if (result.success) {
                        showToast(result.message, 'success');
                        await fetchWorkTypes();
                        renderTree();
                    } else {
                        showToast(result.message || 'فشل الحذف', 'error');
                    }
                } catch (error) {
                    showToast('حدث خطأ غير متوقع.', 'error');
                }
            }

            function renderTable() {
                const tbody = document.getElementById('tableBody');
                tbody.innerHTML = '';

                let counter = 1;

                function addToTable(workTypes, level = 0) {
                    workTypes.forEach(workType => {
                        const row = document.createElement('tr');
                        row.innerHTML = `
                        <td>${counter++}</td>
                        <td>
                            ${'—'.repeat(level)}
                            <i class="fas fa-${workType.children.length > 0 ? 'hard-hat' : 'tools'} me-1"></i>
                            ${workType.name}
                        </td>
                        <td>
                            <span class="badge bg-info">المستوى ${level + 1}</span>
                        </td>
                        <td class="text-center align-middle">
                            <div class="d-flex justify-content-center align-items-center" style="height: 100%;">
                                <div class="form-check form-switch m-0">
                                    <input class="form-check-input toggle-status" type="checkbox"
                                        data-id="${workType.id}" ${workType.is_active ? 'checked' : ''}>
                                </div>
                            </div>
                        </td>
                        <td>
                            <button class="btn btn-success btn-sm me-1" onclick="editWorkType(${workType.id})" title="تعديل">
                                <i class="fas fa-edit"></i>
                            </button>
                            <button class="btn btn-danger btn-sm" onclick="deleteWorkType(${workType.id})" title="حذف">
                                <i class="fas fa-trash"></i>
                            </button>
                        </td>
                    `;
                        tbody.appendChild(row);

                        if (workType.children.length > 0) {
                            addToTable(workType.children, level + 1);
                        }
                    });
                }

                addToTable(workTypesData);
            }

            function showToast(message, type) {
                const toast = document.createElement('div');
                toast.className =
                    `alert alert-${type === 'success' ? 'success' : type === 'error' ? 'danger' : 'info'} position-fixed`;
                toast.style.cssText = 'top: 20px; left: 20px; z-index: 9999; min-width: 300px;';
                toast.innerHTML = `
                <i class="fas fa-${type === 'success' ? 'check-circle' : type === 'error' ? 'exclamation-circle' : 'info-circle'} me-2"></i>
                ${message}
            `;

                document.body.appendChild(toast);

                setTimeout(() => {
                    toast.remove();
                }, 3000);
            }

            document.addEventListener('DOMContentLoaded', function() {
                renderTree();
            });

            document.addEventListener('change', async function(e) {
                if (e.target.classList.contains('toggle-status')) {
                    const workTypeId = e.target.dataset.id;

                    try {
                        const response = await fetch(`/work-types/${workTypeId}/toggle-status`, {
                            method: 'POST',
                            headers: {
                                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                'X-Requested-With': 'XMLHttpRequest'
                            }
                        });

                        const result = await response.json();
                        if (result.success) {
                            showToast('تم تغيير الحالة بنجاح', 'success');
                            await fetchWorkTypes();
                            renderTree();
                        } else {
                            showToast('فشل في تحديث الحالة', 'error');
                        }
                    } catch (error) {
                        showToast('حدث خطأ أثناء تحديث الحالة', 'error');
                    }
                }
            });
        </script>
    @endpush
@endsection
