@extends('admin.dashboard')

@section('sidebar')
    @include('components.sidebar.inquiries')
@endsection

@section('content')
    @include('components.breadcrumb', [
        'title' => __('إضافة مصدر استعلام'),
        'items' => [
            ['label' => __('الرئيسية'), 'url' => route('admin.dashboard')],
            ['label' => __('مصادر الاستعلام'), 'url' => route('inquiry.sources.index')],
            ['label' => __('إضافة')],
        ],
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
                <strong class="me-2">المسار الحالي:</strong>
                <div id="pathBreadcrumb"></div>
            </div>
        </div>

        <div class="row">
            <div class="col-lg-6">
                <div class="tree-container">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h5 class="mb-0"><i class="fas fa-sitemap me-2"></i>شجرة المصادر</h5>
                        <button class="btn btn-primary btn-sm" onclick="addRootSource()">
                            <i class="fas fa-plus me-1"></i>إضافة مصدر رئيسي
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
                                    <th>الاسم</th>
                                    <th>المستوى</th>
                                    <th>الحالة</th>
                                    <th>العمليات</th>
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

    <script>
        let sourcesData = @json($sourcesTree);

        let selectedSource = null;
        let editingSource = null;

        function renderTree() {
            const container = document.getElementById('treeContainer');
            container.innerHTML = '';
            sourcesData.forEach(source => {
                container.appendChild(createTreeItem(source, 0));
            });
            renderTable();
        }

        function createTreeItem(source, level) {
            const item = document.createElement('div');
            item.className = 'tree-item';
            item.dataset.id = source.id;

            if (selectedSource && selectedSource.id === source.id) {
                item.classList.add('selected');
            }

            item.innerHTML = `
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <i class="fas fa-${source.children.length > 0 ? 'folder' : 'file'} me-2"></i>
                        <span class="source-name">${source.name}</span>
                        ${source.is_active ?
                            '<span class="status-badge status-active ms-2">مفعل</span>' :
                            '<span class="status-badge status-inactive ms-2">غير مفعل</span>'
                        }
                    </div>
                    <div class="action-buttons">
                        <button class="add-child-btn" onclick="addChild(${source.id})" title="إضافة فرع">
                            <i class="fas fa-plus"></i>
                        </button>
                    </div>
                </div>
            `;

            item.addEventListener('click', (e) => {
                if (!e.target.closest('.add-child-btn')) {
                    selectSource(source);
                    updatePath(source);
                }
            });

            const container = document.createElement('div');
            container.appendChild(item);

            if (source.children && source.children.length > 0) {
                const childrenContainer = document.createElement('div');
                childrenContainer.className = 'tree-children';

                source.children.forEach(child => {
                    childrenContainer.appendChild(createTreeItem(child, level + 1));
                });

                container.appendChild(childrenContainer);
            }

            return container;
        }

        function selectSource(source) {
            selectedSource = source;

            // Remove previous selection
            document.querySelectorAll('.tree-item.selected').forEach(item => {
                item.classList.remove('selected');
            });

            // Add selection to current item
            document.querySelector(`[data-id="${source.id}"]`).classList.add('selected');
        }

        function updatePath(source) {
            const path = getSourcePath(source);
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

        function getSourcePath(source) {
            const path = [source];
            let current = source;

            while (current.parent_id) {
                const parent = findSourceById(current.parent_id);
                if (parent) {
                    path.unshift(parent);
                    current = parent;
                } else {
                    break;
                }
            }

            return path;
        }

        function findSourceById(id, sources = sourcesData) {
            for (const source of sources) {
                if (source.id === id) {
                    return source;
                }
                if (source.children) {
                    const found = findSourceById(id, source.children);
                    if (found) return found;
                }
            }
            return null;
        }

        function addRootSource() {
            showInlineForm(null);
        }

        function addChild(parentId) {
            event.stopPropagation();
            const parent = findSourceById(parentId);
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
                        <label class="form-label">اسم المصدر</label>
                        <input type="text" class="form-control" id="newSourceName" placeholder="أدخل اسم المصدر">
                    </div>
                    <div class="col-md-3 d-flex justify-content-center align-items-center">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" id="newSourceStatus" checked>
                            <label class="form-check-label ms-2" for="newSourceStatus">الحالة</label>
                        </div>
                    </div>

                    <div class="col-md-3 d-flex align-items-end">
                        <button class="btn btn-success btn-sm me-2" onclick="saveNewSource(${parent ? parent.id : null})">
                            <i class="fas fa-check me-1"></i>حفظ
                        </button>
                        <button class="btn btn-danger btn-sm" onclick="cancelForm()">
                            <i class="fas fa-times me-1"></i>إلغاء
                        </button>
                    </div>
                </div>
                ${parent ? `<small class="text-muted">سيتم إضافة هذا المصدر كفرع من: ${parent.name}</small>` : ''}
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

            document.getElementById('newSourceName').focus();
        }

        async function saveNewSource(parentId) {
            const name = document.getElementById('newSourceName').value.trim();
            const isActive = document.getElementById('newSourceStatus').checked;
            if (!name) {
                alert('يرجى إدخال اسم المصدر');
                return;
            }

            const formData = {
                name: name,
                parent_id: parentId,
                is_active: isActive,
                _token: '{{ csrf_token() }}'
            };

            try {
                const response = await fetch('{{ route('inquiry.sources.store') }}', {
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
                    await fetchSources();
                    renderTree();
                } else {
                    showToast(result.message, 'error');
                }

            } catch (error) {
                showToast('حدث خطأ غير متوقع.', 'error');
            }

            cancelForm();
        }

        async function fetchSources() {
            try {
                const response = await fetch('/inquiry-sources/tree');
                // تحقق من حالة الاستجابة
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }

                const result = await response.json();

                // تحقق من وجود البيانات في الاستجابة
                if (result.success) {
                    sourcesData = result.data;
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

        function editSource(sourceId) {
            const source = findSourceById(sourceId);
            if (!source) return;

            editingSource = source;

            // Remove any existing forms
            document.querySelectorAll('.inline-form, .edit-form').forEach(form => form.remove());

            const sourceElement = document.querySelector(`[data-id="${sourceId}"]`);
            const form = document.createElement('div');
            form.className = 'edit-form';
            form.innerHTML = `
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">اسم المصدر</label>
                        <input type="text" class="form-control" id="editSourceName" value="${source.name}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">الحالة</label>
                        <select class="form-select" id="editSourceStatus">
                            <option value="true" ${source.is_active ? 'selected' : ''}>مفعل</option>
                            <option value="false" ${!source.is_active ? 'selected' : ''}>غير مفعل</option>
                        </select>
                    </div>
                    <div class="col-md-3 d-flex align-items-end">
                        <button class="btn btn-warning btn-sm me-2" onclick="updateSource()">
                            <i class="fas fa-save me-1"></i>تحديث
                        </button>
                        <button class="btn btn-danger btn-sm" onclick="cancelForm()">
                            <i class="fas fa-times me-1"></i>إلغاء
                        </button>
                    </div>
                </div>
            `;

            sourceElement.parentNode.insertBefore(form, sourceElement.nextSibling);
            document.getElementById('editSourceName').focus();
        }

        async function updateSource() {
            if (!editingSource) return;

            const name = document.getElementById('editSourceName').value.trim();
            const isActive = document.getElementById('editSourceStatus').value === 'true';

            if (!name) {
                alert('يرجى إدخال اسم المصدر');
                return;
            }

            const formData = {
                name: name,
                is_active: isActive,
                _token: '{{ csrf_token() }}'
            };

            // استخدم route التحديث وأرسل طلب PUT
            const url = '{{ route('inquiry.sources.update', ['inquiry_source' => ':id']) }}'.replace(':id',
                editingSource.id);

            try {
                const response = await fetch(url, {
                    method: 'PUT', // أو PATCH
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
                    await fetchSources(); // أعد تحميل البيانات من الخادم
                    renderTree(); // أعد رسم الشجرة
                } else {
                    showToast(result.message || 'فشل التحديث', 'error');
                }
            } catch (error) {
                showToast('حدث خطأ غير متوقع.', 'error');
            }

            cancelForm();
            editingSource = null;
        }

        async function deleteSource(sourceId) {
            if (!confirm('هل أنت متأكد من حذف هذا المصدر؟ سيتم حذف جميع الفروع التابعة له.')) {
                return;
            }

            // استخدم route الحذف وأرسل طلب DELETE
            const url = '{{ route('inquiry.sources.destroy', ['inquiry_source' => ':id']) }}'.replace(':id', sourceId);

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
                    await fetchSources(); // أعد تحميل البيانات
                    renderTree(); // أعد رسم الشجرة
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

            function addToTable(sources, level = 0) {
                sources.forEach(source => {
                    const row = document.createElement('tr');
                    row.innerHTML = `
                        <td>${counter++}</td>
                        <td>
                            ${'—'.repeat(level)}
                            <i class="fas fa-${source.children.length > 0 ? 'folder' : 'file'} me-1"></i>
                            ${source.name}
                        </td>
                        <td>
                            <span class="badge bg-info">المستوى ${level + 1}</span>
                        </td>
                        <td class="text-center align-middle">
                            <div class="d-flex justify-content-center align-items-center" style="height: 100%;">
                                <div class="form-check form-switch m-0">
                                    <input class="form-check-input toggle-status" type="checkbox"
                                        data-id="${source.id}" ${source.is_active ? 'checked' : ''}>
                                </div>
                            </div>
                        </td>
                        <td>
                            <button class="btn btn-success btn-sm me-1" onclick="editSource(${source.id})" title="تعديل">
                                <i class="fas fa-edit"></i>
                            </button>
                            <button class="btn btn-danger btn-sm" onclick="deleteSource(${source.id})" title="حذف">
                                <i class="fas fa-trash"></i>
                            </button>
                        </td>
                    `;
                    tbody.appendChild(row);

                    if (source.children.length > 0) {
                        addToTable(source.children, level + 1);
                    }
                });
            }

            addToTable(sourcesData);
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
                const sourceId = e.target.dataset.id;

                try {
                    const response = await fetch(`/inquiry-sources/${sourceId}/toggle-status`, {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'X-Requested-With': 'XMLHttpRequest'
                        }
                    });

                    const result = await response.json();
                    if (result.success) {
                        showToast('تم تغيير الحالة بنجاح', 'success');
                        await fetchSources();
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
@endsection
