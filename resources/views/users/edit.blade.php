@extends('admin.dashboard')

@section('sidebar')
    @include('components.sidebar.permissions')
@endsection

@section('content')
    @push('styles')
        <style>
            /* --- Sidebar Styling --- */
            .settings-nav {
                width: 260px;
                border-right: 1px solid #f1f5f9;
                min-height: 700px;
                padding: 20px 0;
                background: #fff;
            }

            .settings-content {
                flex: 1;
                padding: 30px;
                background: #fdfdfd;
            }

            .main-nav-link {
                display: flex;
                align-items: center;
                padding: 15px 25px;
                color: #64748b;
                font-weight: 600;
                font-size: 0.95rem;
                border-left: 4px solid transparent;
                transition: all 0.2s;
                cursor: pointer;
            }

            .main-nav-link:hover {
                background: #f8fafc;
                color: #1e293b;
            }

            .main-nav-link.active {
                background: #eff6ff;
                color: #3b82f6;
                border-left-color: #3b82f6;
            }

            .main-nav-link i {
                font-size: 1.2rem;
                width: 30px;
                margin-right: 10px;
            }

            /* --- Nested Tabs (Pills) --- */
            .category-pills {
                gap: 8px;
                flex-wrap: wrap;
                margin-bottom: 25px;
                padding-bottom: 15px;
                border-bottom: 1px solid #f1f5f9;
            }

            .category-pills .nav-link {
                border-radius: 20px;
                padding: 8px 18px;
                font-size: 0.85rem;
                font-weight: 600;
                color: #64748b;
                background: #fff;
                border: 1px solid #e2e8f0;
                transition: all 0.2s;
            }

            .category-pills .nav-link:hover {
                background: #f1f5f9;
                border-color: #cbd5e1;
            }

            .category-pills .nav-link.active {
                background: #3b82f6;
                color: #fff;
                border-color: #3b82f6;
                box-shadow: 0 2px 6px rgba(59, 130, 246, 0.3);
            }

            /* --- Table Styling --- */
            .perm-table thead th {
                background: #f8fafc;
                font-size: 0.75rem;
                text-transform: uppercase;
                letter-spacing: 0.5px;
                padding: 12px;
                color: #475569;
            }

            .perm-row:hover {
                background: #f8fafc;
            }

            .perm-label {
                font-weight: 500;
                color: #334155;
                font-size: 0.9rem;
            }

            /* Checkbox */
            .modern-check {
                width: 18px;
                height: 18px;
                border: 2px solid #cbd5e1;
                border-radius: 4px;
                cursor: pointer;
                transition: 0.2s;
            }

            .modern-check:checked {
                background: #3b82f6;
                border-color: #3b82f6;
            }
        </style>
    @endpush
    <!-- @include('components.breadcrumb', [
        'title' => __('Edit User'),
        'items' => [['label' => __('Users'), 'url' => route('users.index')], ['label' => __('Edit')]],
    ]) -->

    <div class="container-fluid pb-5">
        <div class="card border-0 shadow-sm overflow-hidden">
            <form action="{{ route('users.update', $user->id) }}" method="POST" id="userForm" class="d-flex">
                @csrf
                @method('PUT')
                <input type="hidden" name="permissions_list" id="permissions_list_input">

                <!-- 1. Left Main Sidebar -->
                <div class="settings-nav">
                    <div class="nav flex-column nav-pills" id="v-pills-tab" role="tablist">

                        <div class="main-nav-link active" onclick="switchMainTab('basic')">
                            <i class="fas fa-user-circle"></i>
                            <div>
                                <div>{{ __('Profile Info') }}</div>
                                <div class="small fw-normal opacity-75" style="font-size: 11px;">
                                    {{ __('Name, Email & Branch') }}</div>
                            </div>
                        </div>

                        <div class="main-nav-link" onclick="switchMainTab('permissions')">
                            <i class="fas fa-key"></i>
                            <div>
                                <div>{{ __('Permissions') }}</div>
                                <div class="small fw-normal opacity-75" style="font-size: 11px;">
                                    {{ __('Manage Modules Access') }}</div>
                            </div>
                        </div>

                        <div class="main-nav-link" onclick="switchMainTab('options')">
                            <i class="fas fa-sliders-h"></i>
                            <div>
                                <div>{{ __('System Options') }}</div>
                                <div class="small fw-normal opacity-75" style="font-size: 11px;">
                                    {{ __('Advanced Controls') }}</div>
                            </div>
                        </div>

                        <div class="mt-auto px-4 pt-4 border-top">
                            <button type="submit" class="btn btn-primary w-100 fw-bold py-2 shadow-sm">
                                <i class="fas fa-save me-2"></i> {{ __('Save Changes') }}
                            </button>
                        </div>
                    </div>
                </div>


                <!-- 2. Right Content Area -->
                <div class="settings-content position-relative">

                    <!-- A. Basic Info Section -->
                    <div id="content-basic" class="main-section">
                        <h5 class="fw-bold mb-4 text-dark">{{ __('Basic Information') }}</h5>
                        <div class="row g-4">
                            <div class="col-md-6">
                                <label class="small fw-bold text-muted mb-1">{{ __('Full Name') }}</label>
                                <input type="text" name="name" class="form-control"
                                    value="{{ old('name', $user->name) }}" required>
                            </div>
                            <div class="col-md-6">
                                <label class="small fw-bold text-muted mb-1">{{ __('Email Address') }}</label>
                                <input type="email" name="email" class="form-control"
                                    value="{{ old('email', $user->email) }}" required>
                            </div>
                            <div class="col-md-6">
                                <label class="small fw-bold text-muted mb-1">{{ __('Password') }}</label>
                                <input type="password" name="password" class="form-control"
                                    placeholder="{{ __('Leave blank to keep current password') }}">
                            </div>
                            <div class="col-md-6">
                                <label class="small fw-bold text-muted mb-1">{{ __('Confirm Password') }}</label>
                                <input type="password" name="password_confirmation" class="form-control">
                            </div>

                            <div class="col-12 mt-4">
                                <div class="card bg-light border-0 p-3">
                                    <h6 class="small fw-bold mb-3 text-dark">{{ __('Branch Access') }}</h6>
                                    <div class="d-flex flex-wrap gap-3">
                                        @foreach ($branches as $branch)
                                            <label
                                                class="d-flex align-items-center cursor-pointer bg-white px-3 py-2 rounded border shadow-sm">
                                                <input type="checkbox" name="branches[]" value="{{ $branch->id }}"
                                                    class="modern-check me-2"
                                                    {{ in_array($branch->id, (array) old('branches', $userBranches)) ? 'checked' : '' }}>
                                                <span class="small fw-semibold">{{ $branch->name }}</span>
                                            </label>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>


                    <!-- B. Permissions Section (With Nested Tabs!) -->
                    <div id="content-permissions" class="main-section" style="display: none;">
                        <div class="d-flex justify-content-between align-items-center mb-4">
                            <h5 class="fw-bold m-0">{{ __('Permissions Matrix') }}</h5>
                            <span class="badge bg-opacity-10 text-primary px-3 py-2 rounded-pill">
                                {{ count($permissions) }} {{ __('Categories') }}
                            </span>
                        </div>

                        <!-- 2.1 Nested Tabs (Categories) -->
                        <ul class="nav nav-pills category-pills" id="perm-pills-tab" role="tablist">
                            @foreach ($permissions as $category => $perms)
                                @php $catSlug = Str::slug($category ?: 'general'); @endphp
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link {{ $loop->first ? 'active' : '' }}"
                                        id="pills-{{ $catSlug }}-tab" data-bs-toggle="pill"
                                        data-bs-target="#pills-{{ $catSlug }}" type="button">
                                        {{ __(ucfirst($category ?: 'General')) }}
                                    </button>
                                </li>
                            @endforeach
                        </ul>

                        <!-- 2.2 Tab Contents (Tables) -->
                        <div class="tab-content" id="perm-pills-tabContent">
                            @foreach ($permissions as $category => $perms)
                                @php
                                    $catSlug = Str::slug($category ?: 'general');
                                    $grouped = [];
                                    foreach ($perms as $perm) {
                                        $parts = explode(' ', $perm->name, 2);
                                        $action = $parts[0];
                                        $target = $parts[1] ?? '';
                                        $grouped[$target][$action] = $perm;
                                    }
                                @endphp

                                <div class="tab-pane fade {{ $loop->first ? 'show active' : '' }}"
                                    id="pills-{{ $catSlug }}">
                                    <!-- Select All Header -->
                                    <div class="d-flex justify-content-end mb-2">
                                        <div class="form-check form-switch">
                                            <input class="form-check-input select-cat-all" type="checkbox"
                                                data-target=".group-{{ $catSlug }}">
                                            <label class="form-check-label small fw-bold">{{ __('Select All in') }}
                                                {{ __(ucfirst($category ?: 'General')) }}</label>
                                        </div>
                                    </div>

                                    <div class="table-responsive border rounded bg-white">
                                        <table class="table perm-table mb-0 align-middle">
                                            <thead>
                                                <tr>
                                                    <th class="ps-4 w-25">{{ __('Module Name') }}</th>
                                                    <th class="text-center">{{ __('View') }}</th>
                                                    <th class="text-center">{{ __('Create') }}</th>
                                                    <th class="text-center">{{ __('Edit') }}</th>
                                                    <th class="text-center">{{ __('Delete') }}</th>
                                                    <th class="text-center">{{ __('Print') }}</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach ($grouped as $target => $actions)
                                                    <tr class="perm-row">
                                                        <td class="ps-4 perm-label">
                                                            @php
                                                                $transVal = __(ucfirst($target));
                                                                if(is_array($transVal)) {
                                                                    \Illuminate\Support\Facades\Log::warning("Permission target '$target' translates to array.");
                                                                    echo ucfirst($target);
                                                                } else {
                                                                    echo $transVal;
                                                                }
                                                            @endphp
                                                        </td>
                                                        @foreach (['view', 'create', 'edit', 'delete', 'print'] as $act)
                                                            <td class="text-center">
                                                                @if (isset($actions[$act]))
                                                                    <input type="checkbox"
                                                                        class="modern-check group-{{ $catSlug }}"
                                                                        name="permissions[]"
                                                                        value="{{ $actions[$act]->id }}"
                                                                        {{ in_array($actions[$act]->id, (array) old('permissions', $userPermissions)) ? 'checked' : '' }}>
                                                                @else
                                                                    <span class="text-light">&bull;</span>
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


                    <!-- C. Options Section -->
                    <div id="content-options" class="main-section" style="display: none;">
                        <h5 class="fw-bold mb-4 text-dark">{{ __('Advanced Options') }}</h5>

                        <div class="row g-4">
                            @foreach ($selectivePermissions as $cat => $perms)
                                <div class="col-12">
                                    <div class="card border p-3">
                                        <h6 class="text-uppercase text-muted small fw-bold mb-3">{{ __($cat) }}</h6>
                                        <div class="d-flex flex-wrap gap-2">
                                            @foreach ($perms as $perm)
                                                <label
                                                    class="d-flex align-items-center px-3 py-2 border rounded bg-light cursor-pointer hover-shadow">
                                                    <input type="checkbox" name="permissions[]"
                                                        value="{{ $perm->id }}" class="modern-check me-2"
                                                        {{ in_array($perm->id, (array) old('permissions', $userPermissions)) ? 'checked' : '' }}>
                                                    <span
                                                        class="small fw-semibold">{{ __($perm->description ?? $perm->name) }}</span>
                                                </label>
                                            @endforeach
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>

                </div>
            </form>
        </div>
    </div>

    @push('scripts')
        <script>
            function switchMainTab(tabName) {
                document.querySelectorAll('.main-section').forEach(el => el.style.display = 'none');
                document.getElementById('content-' + tabName).style.display = 'block';

                document.querySelectorAll('.main-nav-link').forEach(el => el.classList.remove('active'));

                const icons = {
                    'basic': 'fa-user-circle',
                    'permissions': 'fa-key',
                    'options': 'fa-sliders-h'
                };

                const links = document.querySelectorAll('.main-nav-link');
                links.forEach(link => {
                    if (link.innerHTML.includes(icons[tabName])) {
                        link.classList.add('active');
                    }
                });
            }

            document.addEventListener('DOMContentLoaded', function() {
                // Select All Logic
                document.querySelectorAll('.select-cat-all').forEach(cb => {
                    cb.addEventListener('change', function() {
                        const target = this.getAttribute('data-target');
                        document.querySelectorAll(target).forEach(item => item.checked = this.checked);
                    });
                });

                // JSON Save Trick
                document.getElementById('userForm').addEventListener('submit', function(e) {
                    const checked = document.querySelectorAll('input[name="permissions[]"]:checked');
                    const ids = Array.from(checked).map(cb => cb.value);
                    document.getElementById('permissions_list_input').value = JSON.stringify(ids);
                    checked.forEach(cb => cb.removeAttribute('name'));
                });
            });
        </script>
    @endpush
@endsection
