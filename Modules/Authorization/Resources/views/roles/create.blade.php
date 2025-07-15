@extends('admin.dashboard')
@section('content')
    @include('components.breadcrumb', [
        'title' => __('الادوار'),
        'items' => [
            ['label' => __('الرئيسيه'), 'url' => route('admin.dashboard')],
            ['label' => __('الادوار'), 'url' => route('roles.index')],
            ['label' => __('انشاء')],
        ],
    ])


    <div class="card ">
        <div class="card-body">
            <form method="POST" action="{{ isset($role) ? route('roles.update', $role->id) : route('roles.store') }}">
                @csrf
                @isset($role)
                    @method('PUT')
                @endisset

                <div class="row mb-4">
                    <div class="col-md-5">
                        <label class="form-label">اسم الدور</label>
                        <input type="text" name="name" value="{{ $role->name ?? old('name') }}" class="form-control"
                            required>
                    </div>
                </div>

                <div class="mb-4 card">
                    <br>
                    <div class="d-flex justify-content-center align-items-center mb-3">
                        <div class="form-check">
                            <input type="checkbox" class="form-check-input" id="selectAll">
                            <label class="form-check-label fw-bold" for="selectAll">تحديد الكل</label>
                        </div>
                    </div>

                    <div class="row">
                        @foreach ($permissions as $category => $perms)
                            @php $categorySlug = Str::slug($category); @endphp
                            <div class="col-md-6 col-lg-3 mb-4">
                                <div class="card shadow-sm h-100">
                                    <div class="card-header d-flex justify-content-between align-items-center">
                                        <label class="form-check-label fw-bold">
                                            <input type="checkbox" class="form-check-input select-category"
                                                id="category-{{ $categorySlug }}" data-category="{{ $categorySlug }}">
                                            {{ $category }}
                                        </label>
                                    </div>
                                    <div class="card-body" id="permissions-{{ $categorySlug }}">
                                        @foreach ($perms as $permission)
                                            <div class="form-check mb-2">
                                                <input
                                                    class="form-check-input permission-checkbox category-{{ $categorySlug }}"
                                                    type="checkbox" name="permissions[]" value="{{ $permission->name }}"
                                                    id="perm-{{ $permission->id }}"
                                                    {{ isset($rolePermissions) && in_array($permission->name, $rolePermissions) ? 'checked' : '' }}>
                                                <label class="form-check-label" for="perm-{{ $permission->id }}">
                                                    {{ $permission->name }}
                                                </label>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
                <div class="d-flex justify-content-center align-items-center mb-3">
                    <button type="submit" class="btn btn-primary me-2">
                        <i class="fas fa-save me-1"></i> تأكيد
                    </button>
                    <a href="{{ route('roles.index') }}" class="btn btn-danger">
                        <i class="fas fa-times me-1"></i> {{ __('الغاء') }}
                    </a>
                </div>
            </form>
        </div>


    </div>
@endsection
@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const selectAll = document.getElementById('selectAll');

            selectAll.addEventListener('change', function() {
                const allCheckboxes = document.querySelectorAll('.permission-checkbox');
                allCheckboxes.forEach(cb => cb.checked = this.checked);
                document.querySelectorAll('.select-category').forEach(cb => cb.checked = this.checked);
            });

            document.querySelectorAll('.select-category').forEach(categoryCheckbox => {
                categoryCheckbox.addEventListener('change', function() {
                    const category = this.getAttribute('data-category');
                    const checkboxes = document.querySelectorAll(`.category-${category}`);
                    checkboxes.forEach(cb => cb.checked = this.checked);
                });
            });
        });
    </script>
@endpush
