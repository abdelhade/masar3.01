@extends('admin.dashboard')

@section('sidebar')
    @include('components.sidebar.accounts')
@endsection

@section('content')
    @include('components.breadcrumb', [
        'title' => __('تعديل الدور'),
        'items' => [
            ['label' => __('الرئيسية'), 'url' => route('admin.dashboard')],
            ['label' => __('الأدوار'), 'url' => route('roles.index')],
            ['label' => __('تعديل')],
        ],
    ])

    <div class="container-fluid">
        <form method="POST" action="{{ route('roles.update', $role->id) }}">
            @csrf
            @method('PUT')

            <div class="card shadow-sm border-0">
                <div class="card-header bg-white p-0 border-bottom">
                    <ul class="nav nav-tabs card-header-tabs" id="roleTabs" role="tablist">
                        @foreach ($permissions as $category => $perms)
                            @php $categorySlug = \Illuminate\Support\Str::slug($category ?? 'uncategorized'); @endphp
                            <li class="nav-item" role="presentation">
                                <button class="nav-link {{ $loop->first ? 'active' : '' }} px-4 py-3" 
                                    id="tab-{{ $categorySlug }}" 
                                    data-bs-toggle="tab"
                                    data-bs-target="#content-{{ $categorySlug }}" 
                                    type="button" 
                                    role="tab">
                                    <i class="fas fa-folder me-2"></i>
                                    {{ $category ?? __('Uncategorized') }}
                                    <span class="badge bg-primary ms-2">{{ $perms->count() }}</span>
                                </button>
                            </li>
                        @endforeach
                    </ul>
                </div>

                <div class="card-body p-0">
                    <div class="tab-content" id="roleTabsContent">
                        @foreach ($permissions as $category => $perms)
                            @php $categorySlug = \Illuminate\Support\Str::slug($category ?? 'uncategorized'); @endphp
                            <div class="tab-pane fade {{ $loop->first ? 'show active' : '' }}" 
                                id="content-{{ $categorySlug }}" 
                                role="tabpanel">
                                <div class="p-4">
                                    <div class="d-flex justify-content-between align-items-center mb-4">
                                        <h6 class="m-0 fw-bold text-primary">
                                            <i class="fas fa-shield-alt me-2"></i>
                                            {{ $category ?? __('Uncategorized') }}
                                        </h6>
                                        <div class="form-check form-switch">
                                            <input type="checkbox" 
                                                class="form-check-input select-category" 
                                                id="category-{{ $categorySlug }}" 
                                                data-category="{{ $categorySlug }}">
                                            <label class="form-check-label fw-semibold" for="category-{{ $categorySlug }}">
                                                {{ __('Select All') }}
                                            </label>
                                        </div>
                                    </div>

                                    <div class="row">
                                        @foreach ($perms as $permission)
                                            <div class="col-md-6 col-lg-4 mb-3">
                                                <div class="form-check">
                                                    <input
                                                        class="form-check-input permission-checkbox category-{{ $categorySlug }}"
                                                        type="checkbox" 
                                                        name="permissions[]" 
                                                        value="{{ $permission->name }}"
                                                        id="perm-{{ $permission->id }}"
                                                        {{ in_array($permission->name, $rolePermissions) ? 'checked' : '' }}>
                                                    <label class="form-check-label" for="perm-{{ $permission->id }}">
                                                        {{ $permission->name }}
                                                    </label>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

                <div class="card-footer bg-white border-top py-3">
                    <div class="row mb-3">
                        <div class="col-md-5">
                            <label class="form-label fw-semibold">{{ __('Role Name') }}</label>
                            <input type="text" 
                                name="name" 
                                value="{{ old('name', $role->name) }}" 
                                class="form-control"
                                required>
                        </div>
                    </div>
                    <div class="d-flex justify-content-center gap-2">
                        <button type="submit" class="btn btn-primary px-4">
                            <i class="fas fa-save me-1"></i> {{ __('Save') }}
                        </button>
                        <a href="{{ route('roles.index') }}" class="btn btn-danger px-4">
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
        document.addEventListener('DOMContentLoaded', function() {
            // Select All for each category tab
            document.querySelectorAll('.select-category').forEach(categoryCheckbox => {
                categoryCheckbox.addEventListener('change', function() {
                    const category = this.getAttribute('data-category');
                    const checkboxes = document.querySelectorAll(`.category-${category}`);
                    checkboxes.forEach(cb => cb.checked = this.checked);
                });

                // Update category checkbox state when individual checkboxes change
                const category = categoryCheckbox.getAttribute('data-category');
                const checkboxes = document.querySelectorAll(`.category-${category}`);
                
                checkboxes.forEach(checkbox => {
                    checkbox.addEventListener('change', function() {
                        const allChecked = Array.from(checkboxes).every(cb => cb.checked);
                        categoryCheckbox.checked = allChecked;
                    });
                });

                // Set initial state
                const allChecked = Array.from(checkboxes).every(cb => cb.checked);
                categoryCheckbox.checked = allChecked;
            });
        });
    </script>
@endpush
