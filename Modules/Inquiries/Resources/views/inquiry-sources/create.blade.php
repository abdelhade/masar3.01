@extends('admin.dashboard')

@section('sidebar')
    @include('components.sidebar.inquiries')
@endsection

@section('content')
    @include('components.breadcrumb', [
        'title' => __('Add Inquiry Source'),
        'items' => [
            ['label' => __('Home'), 'url' => route('admin.dashboard')],
            ['label' => __('Inquiry Sources'), 'url' => route('inquiry.sources.index')],
            ['label' => __('Add')],
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
        <!-- Breadcrumb -->
        <nav aria-label="breadcrumb" class="mb-4">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="#" class="text-decoration-none">{{ __('Home') }}</a></li>
                <li class="breadcrumb-item active">{{ __('Inquiry Sources') }}</li>
            </ol>
        </nav>

        <!-- Path Display -->
        <div class="mb-3" id="pathDisplay" style="display: none;">
            <div class="d-flex align-items-center flex-wrap">
                <strong class="me-2">{{ __('Current Path:') }}</strong>
                <div id="pathBreadcrumb"></div>
            </div>
        </div>

        <div class="row">
            <!-- Tree View -->
            <div class="col-lg-6">
                <div class="tree-container">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h5 class="mb-0"><i class="fas fa-sitemap me-2"></i>{{ __('Sources Tree') }}</h5>
                        <button class="btn btn-primary btn-sm" onclick="addRootSource()">
                            <i class="fas fa-plus me-1"></i>{{ __('Add Main Source') }}
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
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h5 class="mb-0"><i class="fas fa-list me-2"></i>{{ __('Sources List') }}</h5>
                        <div>
                            <button class="btn btn-success btn-sm me-2" onclick="exportData('excel')">
                                <i class="fas fa-file-excel me-1"></i>{{ __('Excel') }}
                            </button>
                            <button class="btn btn-danger btn-sm" onclick="exportData('pdf')">
                                <i class="fas fa-file-pdf me-1"></i>{{ __('PDF') }}
                            </button>
                        </div>
                    </div>

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
                            <tbody id="tableBody">
                                <!-- Table content will be generated here -->
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
    <script>
        // Sample data structure
        let sourcesData = [{
                id: 1,
                name: "وسائل التواصل الاجتماعي",
                parent_id: null,
                is_active: true,
                children: [{
                        id: 2,
                        name: "فيسبوك",
                        parent_id: 1,
                        is_active: true,
                        children: [{
                                id: 5,
                                name: "الصفحة الرسمية",
                                parent_id: 2,
                                is_active: true,
                                children: []
                            },
                            {
                                id: 6,
                                name: "المجموعات",
                                parent_id: 2,
                                is_active: true,
                                children: []
                            }
                        ]
                    },
                    {
                        id: 3,
                        name: "واتساب",
                        parent_id: 1,
                        is_active: true,
                        children: [{
                            id: 7,
                            name: "الدعم الفني",
                            parent_id: 3,
                            is_active: true,
                            children: []
                        }]
                    }
                ]
            },
            {
                id: 4,
                name: "الاتصال المباشر",
                parent_id: null,
                is_active: true,
                children: []
            }
        ];

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
                    <div class="col-md-3">
                        <label class="form-label">الحالة</label>
                        <select class="form-select" id="newSourceStatus">
                            <option value="true">مفعل</option>
                            <option value="false">غير مفعل</option>
                        </select>
                    </div>
                    <div class="col-md-3 d-flex align-items-end">
                        <button class="btn btn-success btn-sm me-2" onclick="saveNewSource(${parent ? parent.id : null})">
                            <i class="fas fa-check me-1"></i>حفظ
                        </button>
                        <button class="btn btn-secondary btn-sm" onclick="cancelForm()">
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

        function saveNewSource(parentId) {
            const name = document.getElementById('newSourceName').value.trim();
            const isActive = document.getElementById('newSourceStatus').value === 'true';

            if (!name) {
                alert('يرجى إدخال اسم المصدر');
                return;
            }

            const newSource = {
                id: Date.now(), // في التطبيق الحقيقي، سيأتي من الخادم
                name: name,
                parent_id: parentId,
                is_active: isActive,
                children: []
            };

            if (parentId) {
                const parent = findSourceById(parentId);
                if (parent) {
                    parent.children.push(newSource);
                }
            } else {
                sourcesData.push(newSource);
            }

            renderTree();
            cancelForm();

            // Show success message
            showToast('تم إضافة المصدر بنجاح', 'success');
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
                        <button class="btn btn-secondary btn-sm" onclick="cancelForm()">
                            <i class="fas fa-times me-1"></i>إلغاء
                        </button>
                    </div>
                </div>
            `;

            sourceElement.parentNode.insertBefore(form, sourceElement.nextSibling);
            document.getElementById('editSourceName').focus();
        }

        function updateSource() {
            if (!editingSource) return;

            const name = document.getElementById('editSourceName').value.trim();
            const isActive = document.getElementById('editSourceStatus').value === 'true';

            if (!name) {
                alert('يرجى إدخال اسم المصدر');
                return;
            }

            editingSource.name = name;
            editingSource.is_active = isActive;

            renderTree();
            cancelForm();
            editingSource = null;

            showToast('تم تحديث المصدر بنجاح', 'success');
        }

        function deleteSource(sourceId) {
            if (!confirm('هل أنت متأكد من حذف هذا المصدر؟ سيتم حذف جميع الفروع التابعة له.')) {
                return;
            }

            function removeFromArray(array, id) {
                for (let i = 0; i < array.length; i++) {
                    if (array[i].id === id) {
                        array.splice(i, 1);
                        return true;
                    }
                    if (array[i].children && removeFromArray(array[i].children, id)) {
                        return true;
                    }
                }
                return false;
            }

            removeFromArray(sourcesData, sourceId);
            renderTree();

            if (selectedSource && selectedSource.id === sourceId) {
                selectedSource = null;
                document.getElementById('pathDisplay').style.display = 'none';
            }

            showToast('تم حذف المصدر بنجاح', 'success');
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
                        <td>
                            ${source.is_active ?
                                '<span class="status-badge status-active">مفعل</span>' :
                                '<span class="status-badge status-inactive">غير مفعل</span>'
                            }
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

        function exportData(type) {
            // في التطبيق الحقيقي، سيتم إرسال طلب للخادم
            showToast(`سيتم تصدير البيانات بصيغة ${type.toUpperCase()}`, 'info');
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

        // Initialize the application
        document.addEventListener('DOMContentLoaded', function() {
            renderTree();
        });
    </script>
@endsection
