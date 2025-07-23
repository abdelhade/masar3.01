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
            </div>
        </div>

        <div class="col-sm-10">
            <form action="{{ route('users.update', $user->id) }}" method="POST" class="card bg-white">
                @csrf
                @method('PUT')
                <div class="tab-content" id="v-pills-tabContent">

                    {{-- Basic Information Tab --}}
                    <div class="tab-pane fade show active" id="v-pills-home" role="tabpanel"
                        aria-labelledby="v-pills-home-tab">
                        <div class="card">
                            <div class="card-body">
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

                    {{-- Permissions Tab --}}
                    <div class="tab-pane fade" id="v-pills-profile" role="tabpanel" aria-labelledby="v-pills-profile-tab">
                        @php use Illuminate\Support\Str; @endphp
                        <div class="card">
                            <div class="card-body row">
                                <div class="col-md-3">
                                    <ul class="list-group">
                                        @php
                                            $categories = \Modules\Authorization\Models\Permission::distinct()->pluck(
                                                'category',
                                            );
                                        @endphp
                                        @foreach ($categories as $category)
                                            <li class="list-group-item permission-category {{ $loop->first ? 'active' : '' }}"
                                                data-category="{{ Str::slug($category) }}">
                                                {{ $category }}
                                            </li>
                                        @endforeach
                                    </ul>
                                </div>

                                <div class="col-md-9">
                                    <div class="form-check mb-3">
                                        <input type="checkbox" class="form-check-input" id="selectAll">
                                        <label class="form-check-label fw-bold" for="selectAll">تحديد كل الصلاحيات</label>
                                    </div>

                                    <div class="permissions-container">
                                        @foreach ($categories as $category)
                                            @php
                                                $permissions = \Modules\Authorization\Models\Permission::where(
                                                    'category',
                                                    $category,
                                                )
                                                    ->orderBy('name')
                                                    ->get()
                                                    ->groupBy(function ($item) {
                                                        return explode(' ', $item->name, 2)[1]; // Group by permission target
                                                    });
                                            @endphp

                                            <div class="permission-category-content {{ !$loop->first ? 'd-none' : '' }}"
                                                id="{{ Str::slug($category) }}">
                                                <div class="table-responsive">
                                                    <table class="table table-bordered text-center align-middle">
                                                        <thead class="table-light">
                                                            <tr>
                                                                <th>الصلاحية</th>
                                                                <th>عرض</th>
                                                                <th>إضافة</th>
                                                                <th>تعديل</th>
                                                                <th>حذف</th>
                                                                <th>طباعة</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody>
                                                            @foreach ($permissions as $target => $perms)
                                                                <tr>
                                                                    <td class="text-start">{{ $target }}</td>
                                                                    @foreach (['عرض', 'إضافة', 'تعديل', 'حذف', 'طباعة'] as $action)
                                                                        <td>
                                                                            @php
                                                                                $permission = $perms->first(function (
                                                                                    $p,
                                                                                ) use ($action, $target) {
                                                                                    return str_starts_with(
                                                                                        $p->name,
                                                                                        $action . ' ' . $target,
                                                                                    );
                                                                                });
                                                                            @endphp
                                                                            @if ($permission)
                                                                                <input type="checkbox"
                                                                                    class="form-check-input"
                                                                                    name="permissions[]"
                                                                                    value="{{ $permission->id }}"
                                                                                    {{ $user->hasPermissionTo($permission->name) ? 'checked' : '' }}>
                                                                            @endif
                                                                        </td>
                                                                    @endforeach
                                                                </tr>
                                                            @endforeach
                                                        </tbody>
                                                    </table>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="d-flex justify-content-center mt-4">
                        <button type="submit" class="btn btn-primary me-2">
                            <i class="fas fa-save me-1"></i> حفظ
                        </button>
                        <a href="{{ route('users.index') }}" class="btn btn-danger">
                            <i class="fas fa-times me-1"></i> رجوع
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>
@endsection

@push('styles')
    <style>
        .permission-category {
            cursor: pointer;
            text-align: right;
            padding: 10px 15px;
            transition: all 0.3s;
        }

        .permission-category:hover {
            background-color: #f8f9fa;
        }

        .permission-category.active {
            background-color: #0d6efd;
            color: white;
        }

        .permissions-container {
            max-height: 500px;
            overflow-y: auto;
            padding: 10px;
        }

        .table th {
            white-space: nowrap;
        }

        .table td {
            vertical-align: middle;
        }
    </style>
@endpush

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

    document.addEventListener('DOMContentLoaded', function () {
        const selectAll = document.getElementById('selectAll');
        const allCheckboxes = document.querySelectorAll('input[type="checkbox"]:not(#selectAll)');

        if (selectAll) {
            selectAll.addEventListener('change', function () {
                allCheckboxes.forEach(cb => cb.checked = this.checked);
            });
        }

        document.addEventListener('DOMContentLoaded', function() {
            // Select All functionality
            const selectAll = document.getElementById('selectAll');
            if (selectAll) {
                selectAll.addEventListener('change', function() {
                    const activeCategory = document.querySelector(
                        '.permission-category-content:not(.d-none)');
                    activeCategory.querySelectorAll('input[type="checkbox"]').forEach(cb => {
                        cb.checked = this.checked;
                    });
                });
            }

            // Permission category tabs
            document.querySelectorAll('.permission-category').forEach(tab => {
                tab.addEventListener('click', function() {
                    // Update active tab
                    document.querySelectorAll('.permission-category').forEach(t => {
                        t.classList.remove('active');
                    });
                    this.classList.add('active');

                    // Show corresponding content
                    const categoryId = this.getAttribute('data-category');
                    document.querySelectorAll('.permission-category-content').forEach(content => {
                        content.classList.add('d-none');
                    });
                    document.getElementById(categoryId).classList.remove('d-none');

                    // Uncheck select all when switching categories
                    selectAll.checked = false;
                });
            });

            // Update "Select All" checkbox when individual permissions change
            document.querySelectorAll('.permission-category-content').forEach(container => {
                container.addEventListener('change', function(e) {
                    if (e.target.type === 'checkbox' && e.target.id !== 'selectAll') {
                        const allChecked = Array.from(this.querySelectorAll(
                                'input[type="checkbox"]'))
                            .every(cb => cb.checked);
                        selectAll.checked = allChecked;
                    }
                });
            });
        });
    });
</script>
@endpush
