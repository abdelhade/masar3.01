@extends('admin.dashboard')

@section('sidebar')
    @include('components.sidebar.permissions')
@endsection

@section('content')
    @include('components.breadcrumb', [
        'title' => __('Edit User'),
        'items' => [
            ['label' => __('Home'), 'url' => route('admin.dashboard')],
            ['label' => __('Users'), 'url' => route('users.index')],
            ['label' => __('Edit')],
        ],
    ])

    <div class="container-fluid">
        <form action="{{ route('users.update', $user->id) }}" method="POST">
            @csrf
            @method('PUT')

            <div class="card shadow-sm border-0">
                <div class="card-header bg-white p-0 border-bottom">
                    <ul class="nav nav-tabs card-header-tabs" id="userTabs" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active px-4 py-3" id="basic-tab" data-bs-toggle="tab"
                                data-bs-target="#basic-data" type="button" role="tab">
                                <i class="fas fa-user me-2"></i>
                                {{ __('Basic Data') }}
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link px-4 py-3" id="permissions-tab" data-bs-toggle="tab"
                                data-bs-target="#permissions-data" type="button" role="tab">
                                <i class="fas fa-shield-alt me-2"></i>
                                {{ __('Permissions') }}
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link px-4 py-3" id="options-tab" data-bs-toggle="tab"
                                data-bs-target="#options-data" type="button" role="tab">
                                <i class="fas fa-cog me-2"></i>
                                {{ __('Options') }}
                            </button>
                        </li>
                    </ul>
                </div>

                <div class="card-body p-0">
                    <div class="tab-content" id="userTabsContent">
                        <!-- البيانات الأساسية -->
                        <div class="tab-pane fade show active" id="basic-data" role="tabpanel">
                            <div class="p-4">
                                    <div class="row">
                                        <!-- بيانات المستخدم -->
                                        <div class="col-lg-8">
                                            <div class="row">
                                                <div class="col-md-6 mb-3">
                                                    <label class="form-label fw-semibold">{{ __('Name') }}</label>
                                                    <input type="text" name="name" class="form-control"
                                                        value="{{ old('name', $user->name) }}" required>
                                                </div>

                                                <div class="col-md-6 mb-3">
                                                    <label class="form-label fw-semibold">{{ __('Email') }}</label>
                                                    <input type="email" name="email" class="form-control"
                                                        value="{{ old('email', $user->email) }}" required>
                                                </div>

                                                <div class="col-md-6 mb-3">
                                                    <label class="form-label fw-semibold">{{ __('Password') }}</label>
                                                    <span class="text-muted">{{ __('Leave blank if you do not want to change') }}</span>
                                                    <div class="input-group">
                                                        <input type="password" name="password" class="form-control"
                                                            id="password">
                                                        <button type="button" class="btn btn-outline-secondary"
                                                            onclick="togglePassword('password', this)">
                                                            <i class="fa fa-eye"></i>
                                                        </button>
                                                    </div>
                                                </div>

                                                <div class="col-md-6 mb-3">
                                                    <label class="form-label fw-semibold"> {{ __('Confirm Password') }}</label>
                                                    <div class="input-group">
                                                        <input type="password" name="password_confirmation"
                                                            class="form-control" id="password_confirmation">
                                                        <button type="button" class="btn btn-outline-secondary"
                                                            onclick="togglePassword('password_confirmation', this)">
                                                            <i class="fa fa-eye"></i>
                                                        </button>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- الفروع -->
                                        <div class="col-lg-4">
                                            <div class="card bg-light border-0 h-100">
                                                <div class="card-body">
                                                    <h6 class="fw-bold mb-3 text-success">
                                                        <i class="fas fa-code-branch me-2"></i>
                                                        {{ __('Select Branches') }}
                                                    </h6>
                                                    <div class="row g-2">
                                                        @foreach ($branches as $branch)
                                                            <div class="col-12">
                                                                <div class="form-check">
                                                                    <input class="form-check-input" type="checkbox"
                                                                        name="branches[]" value="{{ $branch->id }}"
                                                                        id="branch_{{ $branch->id }}"
                                                                        {{ in_array($branch->id, (array) old('branches', $userBranches)) ? 'checked' : '' }}>
                                                                    <label class="form-check-label"
                                                                        for="branch_{{ $branch->id }}">
                                                                        {{ $branch->name }}
                                                                    </label>
                                                                </div>
                                                            </div>
                                                        @endforeach
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                            </div>
                        </div>

                        <!-- الصلاحيات -->
                        <div class="tab-pane fade" id="permissions-data" role="tabpanel">
                            @php use Illuminate\Support\Str; @endphp
                            <div class="p-0">
                                <div class="card-header bg-white p-0 border-bottom">
                                    <ul class="nav nav-tabs card-header-tabs" id="permissionCategoryTabs" role="tablist">
                                        @foreach ($permissions as $category => $perms)
                                            @php
                                                $categoryName = is_string($category) && $category ? (string) $category : 'Uncategorized';
                                                $translatedCategory = __(ucfirst($categoryName));
                                                $translatedCategory = is_array($translatedCategory) ? $categoryName : (string) $translatedCategory;
                                                $categorySlug = Str::slug($categoryName);
                                            @endphp
                                            <li class="nav-item" role="presentation">
                                                <button class="nav-link {{ $loop->first ? 'active' : '' }} px-4 py-3" 
                                                    id="perm-tab-{{ $categorySlug }}" 
                                                    data-bs-toggle="tab"
                                                    data-bs-target="#perm-content-{{ $categorySlug }}" 
                                                    type="button" 
                                                    role="tab">
                                                    <i class="fas fa-folder me-2"></i>
                                                    {{ $translatedCategory }}
                                                    <span class="badge bg-primary ms-2">{{ $perms->count() }}</span>
                                                </button>
                                            </li>
                                        @endforeach
                                    </ul>
                                </div>

                                <div class="card-body p-0">
                                    <div class="tab-content" id="permissionCategoryTabsContent">
                                        @foreach ($permissions as $category => $perms)
                                            @php
                                                $categoryName = is_string($category) && $category ? (string) $category : 'Uncategorized';
                                                $translatedCategory = __(ucfirst($categoryName));
                                                $translatedCategory = is_array($translatedCategory) ? $categoryName : (string) $translatedCategory;
                                                $categorySlug = Str::slug($categoryName);
                                                
                                                $grouped = [];
                                                foreach ($perms as $perm) {
                                                    $parts = explode(' ', $perm->name, 2);
                                                    $action = $parts[0]; // view, create, edit, etc.
                                                    $target = $parts[1] ?? '';
                                                    $grouped[$target][$action] = $perm;
                                                }
                                            @endphp
                                            <div class="tab-pane fade {{ $loop->first ? 'show active' : '' }}" 
                                                id="perm-content-{{ $categorySlug }}" 
                                                role="tabpanel">
                                                <div class="p-4">
                                                    <div class="d-flex justify-content-between align-items-center mb-4">
                                                        <h6 class="m-0 fw-bold text-primary">
                                                            <i class="fas fa-shield-alt me-2"></i>
                                                            {{ $translatedCategory }}
                                                        </h6>
                                                        <div class="form-check form-switch">
                                                            <input class="form-check-input select-category-perm" 
                                                                type="checkbox" 
                                                                id="selectAll-{{ $categorySlug }}"
                                                                data-category="{{ $categorySlug }}">
                                                            <label class="form-check-label fw-semibold" for="selectAll-{{ $categorySlug }}">
                                                                {{ __('Select All') }}
                                                            </label>
                                                        </div>
                                                    </div>

                                                    <div class="table-responsive">
                                                        <table class="table table-hover table-bordered text-center align-middle mb-0">
                                                            <thead class="table-light">
                                                                <tr>
                                                                    <th class="text-start">{{ __('Permission') }}</th>
                                                                    <th><i class="fas fa-eye text-primary"></i> {{ __('View') }}</th>
                                                                    <th><i class="fas fa-plus text-success"></i> {{ __('Create') }}</th>
                                                                    <th><i class="fas fa-edit text-warning"></i> {{ __('Edit') }}</th>
                                                                    <th><i class="fas fa-trash text-danger"></i> {{ __('Delete') }}</th>
                                                                    <th><i class="fas fa-print text-info"></i> {{ __('Print') }}</th>
                                                                </tr>
                                                            </thead>
                                                            <tbody>
                                                                @foreach ($grouped as $title => $actions)
                                                                    @php
                                                                        $titleString = is_string($title) ? (string) $title : '';
                                                                        $translatedTitle = __(ucfirst($titleString));
                                                                        $translatedTitle = is_array($translatedTitle) ? $titleString : (string) $translatedTitle;
                                                                    @endphp
                                                                    <tr>
                                                                        <td class="text-start fw-semibold">{{ $translatedTitle }}</td>

                                                                        @php
                                                                            $actionOrder = ['view', 'create', 'edit', 'delete', 'print'];
                                                                        @endphp

                                                                        @foreach ($actionOrder as $action)
                                                                            <td>
                                                                                @if (isset($actions[$action]))
                                                                                    @php
                                                                                        $permId = is_object($actions[$action]) ? $actions[$action]->id : (is_array($actions[$action]) ? $actions[$action]['id'] : $actions[$action]);
                                                                                    @endphp
                                                                                    <input type="checkbox"
                                                                                        class="form-check-input permission-checkbox-{{ $categorySlug }}"
                                                                                        name="permissions[]"
                                                                                        value="{{ $permId }}"
                                                                                        {{ in_array($permId, (array) old('permissions', $userPermissions)) ? 'checked' : '' }}>
                                                                                @else
                                                                                    <span class="text-muted">-</span>
                                                                                @endif
                                                                            </td>
                                                                        @endforeach
                                                                    </tr>
                                                                @endforeach
                                                            </tbody>
                                                        </table>
                                                    </div>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- الخيارات -->
                        <div class="tab-pane fade" id="options-data" role="tabpanel">
                            <div class="p-4">
                                <div class="d-flex justify-content-between align-items-center mb-4">
                                    <h6 class="m-0 fw-bold text-primary">
                                        <i class="fas fa-cog me-2"></i>
                                        {{ __('Options') }}
                                    </h6>
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" id="selectAllSelective">
                                        <label class="form-check-label fw-semibold" for="selectAllSelective">
                                            {{ __('Select All') }}
                                        </label>
                                    </div>
                                </div>
                                    @if ($selectivePermissions->isNotEmpty())
                                        <div class="table-responsive">
                                            <table class="table table-hover table-bordered align-middle mb-0">
                                                <thead class="table-light">
                                                    <tr>
                                                        <th class="text-start">الوصف</th>
                                                        <th class="text-center">تحديد</th>
                                                    </tr>
                                                </thead>
                                                <tbody id="selectivePermissionsTableBody">
                                                    @foreach ($selectivePermissions as $category => $perms)
                                                        @foreach ($perms as $permission)
                                                            @php
                                                                $description = is_string($permission->description) ? (string) $permission->description : '';
                                                                $translatedDescription = __($description);
                                                                $translatedDescription = is_array($translatedDescription) ? $description : (string) $translatedDescription;
                                                            @endphp
                                                            <tr>
                                                                <td class="text-start">{{ $translatedDescription }}</td>
                                                                <td class="text-center">
                                                                    <input type="checkbox" name="permissions[]"
                                                                        class="form-check-input selective-permission-checkbox" value="{{ $permission->id }}"
                                                                        {{ in_array($permission->id, (array) old('permissions', $userPermissions)) ? 'checked' : '' }}>
                                                                </td>
                                                            </tr>
                                                        @endforeach
                                                    @endforeach
                                                </tbody>
                                            </table>
                                        </div>
                                    @else
                                        <p class="text-muted mb-0">لا توجد خيارات إضافية متاحة حالياً.</p>
                                    @endif
                            </div>
                        </div>
                    </div>
                </div>

                <!-- أزرار الحفظ -->
                <div class="card-footer bg-white border-top py-3">
                    <div class="d-flex justify-content-center gap-2">
                        <button type="submit" class="btn btn-main px-4">
                            <i class="fas fa-save me-1"></i> {{ __('Save') }}
                        </button>
                        <a href="{{ route('users.index') }}" class="btn btn-danger px-4">
                            <i class="fas fa-times me-1"></i> {{ __('Cancel') }}
                        </a>
                    </div>
                </div>
            </div>
        </form>
    </div>
@endsection


@push('scripts')
    <script>
        function togglePassword(fieldId, btn) {
            const input = document.getElementById(fieldId);
            const icon = btn.querySelector('i');

            if (input.type === "password") {
                input.type = "text";
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
            } else {
                input.type = "password";
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
            }
        }

        document.addEventListener('DOMContentLoaded', function() {
            // Select All for each permission category tab
            document.querySelectorAll('.select-category-perm').forEach(selectAllCheckbox => {
                const category = selectAllCheckbox.getAttribute('data-category');
                const checkboxes = document.querySelectorAll(`.permission-checkbox-${category}`);
                
                // Select All functionality
                selectAllCheckbox.addEventListener('change', function() {
                    checkboxes.forEach(cb => {
                        cb.checked = this.checked;
                    });
                });

                // Update Select All state when individual checkboxes change
                checkboxes.forEach(checkbox => {
                    checkbox.addEventListener('change', function() {
                        const allChecked = Array.from(checkboxes).every(cb => cb.checked);
                        selectAllCheckbox.checked = allChecked;
                    });
                });

                // Set initial state
                const allChecked = Array.from(checkboxes).every(cb => cb.checked);
                selectAllCheckbox.checked = allChecked;
            });

            // Select All للخيارات (Selective Permissions)
            const selectAllSelective = document.getElementById('selectAllSelective');
            const selectiveCheckboxes = document.querySelectorAll('.selective-permission-checkbox');

            if (selectAllSelective && selectiveCheckboxes.length > 0) {
                // تحديث حالة Select All بناءً على حالة جميع checkboxes
                function updateSelectAllSelectiveState() {
                    const allChecked = Array.from(selectiveCheckboxes).every(cb => cb.checked);
                    selectAllSelective.checked = allChecked;
                }

                // تحديد/إلغاء تحديد الكل
                selectAllSelective.addEventListener('change', function() {
                    selectiveCheckboxes.forEach(cb => {
                        cb.checked = this.checked;
                    });
                });

                // تحديث حالة Select All عند تغيير أي checkbox يدوياً
                selectiveCheckboxes.forEach(checkbox => {
                    checkbox.addEventListener('change', updateSelectAllSelectiveState);
                });

                // تحديث الحالة الأولية
                updateSelectAllSelectiveState();
            }

            // إصلاح مشكلة عدم إرسال checkboxes المخفية في tabs
            // نستخدم طريقة مختلفة: نجعل جميع الـ checkboxes مرئية قبل الإرسال
            const form = document.querySelector('form[action*="users"]');
            if (form) {
                form.addEventListener('submit', function(e) {
                    console.log('Form submit event triggered');
                    
                    // جعل جميع tab panes مرئية مؤقتاً
                    const tabPanes = document.querySelectorAll('.tab-pane');
                    tabPanes.forEach(pane => {
                        pane.style.display = 'block';
                        pane.style.visibility = 'visible';
                    });
                    
                    // جمع جميع checkboxes المحددة
                    const allCheckboxes = form.querySelectorAll('input[name="permissions[]"]:checked');
                    console.log('Found checked checkboxes:', allCheckboxes.length);
                    
                    // طباعة أول 10 IDs
                    allCheckboxes.forEach((checkbox, index) => {
                        if (index < 10) {
                            console.log(`Checkbox ${index}: ${checkbox.value}`);
                        }
                    });
                });
            } else {
                console.error('Form not found!');
            }
        });
    </script>
@endpush
