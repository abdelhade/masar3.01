@extends('admin.dashboard')

@section('content')
    @include('components.breadcrumb', [
        'title' => __('تعديل مستخدم'),
        'items' => [
            ['label' => __('الرئيسيه'), 'url' => route('admin.dashboard')],
            ['label' => __('المستخدمين'), 'url' => route('users.index')],
            ['label' => __('تعديل')],
        ],
    ])

    <div class="row">
        <div class="col-sm-2">
            <div class="nav flex-column nav-pills text-center" id="v-pills-tab" role="tablist" aria-orientation="vertical">
                <a class="nav-link active" id="v-pills-home-tab" data-bs-toggle="pill" href="#v-pills-home" role="tab"
                    aria-controls="v-pills-home" aria-selected="true">البيانات الاساسيه</a>

                <a class="nav-link" id="v-pills-profile-tab" data-bs-toggle="pill" href="#v-pills-profile" role="tab"
                    aria-controls="v-pills-profile" aria-selected="false">الصلاحيات</a>

                <a class="nav-link" id="v-pills-settings-tab" data-bs-toggle="pill" href="#v-pills-settings" role="tab"
                    aria-controls="v-pills-settings" aria-selected="false">الثوابت</a>
            </div>
        </div>

        <div class="col-sm-10">
            <form action="{{ route('users.update', $user->id) }}" method="POST" class="bg-white card">
                @csrf
                @method('PUT')
                <div class="tab-content" id="v-pills-tabContent">

                    {{-- البيانات الأساسية --}}
                    <div class="tab-pane fade show active" id="v-pills-home" role="tabpanel"
                        aria-labelledby="v-pills-home-tab">
                        <div class="card-body">
                            <div class="">
                                <div class="row">
                                    <div class="mb-3 col-md-4">
                                        <label>الاسم</label>
                                        <input type="text" name="name" class="form-control"
                                            value="{{ old('name', $user->name) }}" required>
                                    </div>

                                    <div class="mb-3 col-md-4">
                                        <label>البريد الإلكتروني</label>
                                        <input type="email" name="email" class="form-control"
                                            value="{{ old('email', $user->email) }}" required>
                                    </div>

                                    <div class="mb-3 col-md-4">
                                        <label>كلمة المرور (اتركها فارغة إن لم ترغب في التغيير)</label>
                                        <div class="input-group">
                                            <input type="password" name="password" class="form-control" id="password">
                                            <button type="button" class="btn btn-primary"
                                                onclick="togglePassword('password', this)">
                                                <i class="fa fa-eye"></i>
                                            </button>
                                        </div>
                                    </div>

                                    <div class="mb-3 col-md-4">
                                        <label>تأكيد كلمة المرور</label>
                                        <div class="input-group">
                                            <input type="password" name="password_confirmation" class="form-control"
                                                id="password_confirmation">
                                            <button type="button" class="btn btn-primary"
                                                onclick="togglePassword('password_confirmation', this)">
                                                <i class="fa fa-eye"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- الصلاحيات --}}
                    <div class="tab-pane fade" id="v-pills-profile" role="tabpanel" aria-labelledby="v-pills-profile-tab">
                        <div class="card">
                            <div class="card-body">

                                <div class="d-flex justify-content-center align-items-center mb-3">
                                    <div class="form-check">
                                        <input type="checkbox" class="form-check-input" id="selectAll">
                                        <label class="form-check-label fw-bold" for="selectAll">تحديد الكل</label>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-sm-2">
                                        <div class="nav flex-column nav-pills text-center" id="perm-tab" role="tablist"
                                            aria-orientation="vertical">
                                            @foreach ($permissions as $category => $perms)
                                                @php $categorySlug = Str::slug($category); @endphp
                                                <a class="nav-link @if ($loop->first) active @endif"
                                                    id="tab-{{ $categorySlug }}-tab" data-bs-toggle="pill"
                                                    href="#tab-{{ $categorySlug }}" role="tab"
                                                    aria-controls="tab-{{ $categorySlug }}"
                                                    aria-selected="{{ $loop->first ? 'true' : 'false' }}">
                                                    {{ $category }}
                                                </a>
                                            @endforeach
                                        </div>
                                    </div>

                                    <div class="col-sm-10">
                                        <div class="tab-content" id="perm-tabContent">
                                            @foreach ($permissions as $category => $perms)
                                                @php $categorySlug = Str::slug($category); @endphp
                                                <div class="tab-pane fade @if ($loop->first) show active @endif"
                                                    id="tab-{{ $categorySlug }}" role="tabpanel"
                                                    aria-labelledby="tab-{{ $categorySlug }}-tab">
                                                    <div class="card shadow-sm mb-3">
                                                        <div class="card-header">
                                                            <label class="form-check-label fw-bold">
                                                                <input type="checkbox"
                                                                    class="form-check-input select-category"
                                                                    data-category="{{ $categorySlug }}">
                                                                تحديد الكل - {{ $category }}
                                                            </label>
                                                        </div>
                                                        <div class="card-body">
                                                            <div class="row">
                                                                @foreach ($perms as $permission)
                                                                    <div class="col-md-4 mb-2">
                                                                        <div class="form-check">
                                                                            <input
                                                                                class="form-check-input permission-checkbox category-{{ $categorySlug }}"
                                                                                type="checkbox" name="permissions[]"
                                                                                value="{{ $permission->name }}"
                                                                                id="perm-{{ $permission->id }}"
                                                                                {{ in_array($permission->name, $userPermissions) ? 'checked' : '' }}>
                                                                            <label class="form-check-label"
                                                                                for="perm-{{ $permission->id }}">
                                                                                {{ $permission->name }}
                                                                            </label>
                                                                        </div>
                                                                    </div>
                                                                @endforeach
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>

                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- الثوابت --}}
                    <div class="tab-pane fade" id="v-pills-settings" role="tabpanel"
                        aria-labelledby="v-pills-settings-tab">
                        <div class="card">
                            <div class="card-body">
                                <p class="text-muted mb-0">
                                    يمكنك إضافة الثوابت أو إعدادات إضافية هنا لاحقاً.
                                </p>
                            </div>
                        </div>
                    </div>

                </div>

                {{-- أزرار الحفظ --}}
                <div class="d-flex justify-content-center mt-4">
                    <button type="submit" class="btn btn-primary me-2">
                        <i class="fas fa-save me-1"></i> حفظ التعديلات
                    </button>
                    <a href="{{ route('users.index') }}" class="btn btn-danger m-1">
                        <i class="fas fa-times me-1"></i> رجوع
                    </a>
                </div>
            </form>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        function togglePassword(fieldId, btn) {
            const input = document.getElementById(fieldId);
            if (input.type === "password") {
                input.type = "text";
                btn.querySelector('i').classList.remove('fa-eye');
                btn.querySelector('i').classList.add('fa-eye-slash');
            } else {
                input.type = "password";
                btn.querySelector('i').classList.remove('fa-eye-slash');
                btn.querySelector('i').classList.add('fa-eye');
            }
        }

        document.addEventListener('DOMContentLoaded', function() {
            document.getElementById('selectAll').addEventListener('change', function() {
                const allCheckboxes = document.querySelectorAll('.permission-checkbox');
                const allCategoryToggles = document.querySelectorAll('.select-category');
                allCheckboxes.forEach(cb => cb.checked = this.checked);
                allCategoryToggles.forEach(cb => cb.checked = this.checked);
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
