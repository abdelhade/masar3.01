@extends('admin.dashboard')

@section('content')
    @include('components.breadcrumb', [
        'title' => __('إنشاء مستخدم'),
        'items' => [
            ['label' => __('الرئيسيه'), 'url' => route('admin.dashboard')],
            ['label' => __('المستخدمين'), 'url' => route('users.index')],
            ['label' => __('إنشاء')],
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
            <form action="{{ route('users.store') }}" method="POST">
                @csrf
                <div class="tab-content" id="v-pills-tabContent">

                    {{-- البيانات الأساسية --}}
                    <div class="tab-pane fade show active" id="v-pills-home" role="tabpanel"
                         aria-labelledby="v-pills-home-tab">
                        <div class="card">
                            <div class="card-body">
                                <div class="row">
                                    <div class="mb-3 col-md-4">
                                        <label>الاسم</label>
                                        <input type="text" name="name" class="form-control" required>
                                    </div>

                                    <div class="mb-3 col-md-4">
                                        <label>البريد الإلكتروني</label>
                                        <input type="email" name="email" class="form-control" required>
                                    </div>

                                    <div class="mb-3 col-md-4">
                                        <label>كلمة المرور</label>
                                        <div class="input-group">
                                            <input type="password" name="password" class="form-control" id="password" required>
                                            <button type="button" class="btn btn-primary" onclick="togglePassword('password', this)">
                                                <i class="fa fa-eye"></i>
                                            </button>
                                        </div>
                                    </div>

                                    <div class="mb-3 col-md-4">
                                        <label>تأكيد كلمة المرور</label>
                                        <div class="input-group">
                                            <input type="password" name="password_confirmation" class="form-control" id="password_confirmation" required>
                                            <button type="button" class="btn btn-primary" onclick="togglePassword('password_confirmation', this)">
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
                        @php use Illuminate\Support\Str; @endphp
                        <div class="card">
                            <div class="card-body row">
                                <div class="col-md-3">
                                    <ul class="list-group">
                                        @foreach ($permissions as $category => $perms)
                                            <li class="list-group-item list-group-item-action permission-tab {{ $loop->first ? 'active' : '' }}" data-target="{{ Str::slug($category) }}">
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

                                    @foreach ($permissions as $category => $perms)
                                        @php
                                            $grouped = [];
                                            foreach ($perms as $perm) {
                                                $parts = explode(' ', $perm->name, 2);
                                                $action = $parts[0];
                                                $target = $parts[1] ?? '';
                                                $grouped[$target][$action] = $perm->name;
                                            }
                                        @endphp

                                        <div class="permissions-table {{ !$loop->first ? 'd-none' : '' }}" id="{{ Str::slug($category) }}">
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
                                                        @foreach ($grouped as $title => $actions)
                                                            <tr>
                                                                <td class="text-start">{{ $title }}</td>
                                                                @foreach (['عرض', 'إضافة', 'تعديل', 'حذف', 'طباعة'] as $act)
                                                                    <td>
                                                                        @if (isset($actions[$act]))
                                                                            <input type="checkbox" class="form-check-input" name="permissions[]" value="{{ $actions[$act] }}">
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

                    {{-- الثوابت --}}
                    <div class="tab-pane fade" id="v-pills-settings" role="tabpanel" aria-labelledby="v-pills-settings-tab">
                        <div class="card">
                            <div class="card-body">
                                <p class="text-muted mb-0">يمكنك إضافة الثوابت أو إعدادات إضافية هنا لاحقاً.</p>
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

    document.addEventListener('DOMContentLoaded', function () {
        const selectAll = document.getElementById('selectAll');
        const allCheckboxes = document.querySelectorAll('input[type="checkbox"]:not(#selectAll)');

        if (selectAll) {
            selectAll.addEventListener('change', function () {
                allCheckboxes.forEach(cb => cb.checked = this.checked);
            });
        }

        document.querySelectorAll('.permission-tab').forEach(tab => {
            tab.addEventListener('click', function () {
                document.querySelectorAll('.permission-tab').forEach(t => t.classList.remove('active'));
                this.classList.add('active');

                document.querySelectorAll('.permissions-table').forEach(table => table.classList.add('d-none'));
                const targetId = this.getAttribute('data-target');
                document.getElementById(targetId).classList.remove('d-none');
            });
        });
    });
</script>
@endpush
