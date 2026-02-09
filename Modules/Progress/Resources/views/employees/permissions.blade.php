@extends('progress::layouts.app')
@section('breadcrumb')
    <li class="breadcrumb-item">
        <a href="{{ route('progress.dashboard') }}" class="text-muted text-decoration-none">
            {{ __('general.dashboard') }}
        </a>
    </li>
    <li class="breadcrumb-item">
        <a href="{{ route('progress.employees.index') }}" class="text-muted text-decoration-none">
            {{ __('general.employees') }}
        </a>
    </li>
@endsection
@section('content')
    <div class="container">
        <h4> {{ __('general.employee_permissions') }} : {{ $employee->name }}</h4>

        <form method="POST" action="{{ route('progress.employees.updatePermissions', $employee->id) }}">
            @csrf

            {{-- Debug: عرض الصلاحيات الحالية --}}
            @if(config('app.debug'))
                <div class="alert alert-info mb-3">
                    <strong>Debug Info:</strong><br>
                    <strong>User ID:</strong> {{ $employee->id }}<br>
                    <strong>User Name:</strong> {{ $employee->name }}<br>
                    <strong>Current Permissions Count:</strong> {{ $employee->permissions->count() }}<br>
                    <strong>Current Permissions:</strong> 
                    <ul class="mb-0">
                        @foreach($employee->permissions as $perm)
                            <li>{{ $perm->name }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            
            <div class="mb-3">
                <button type="button" class="btn btn-primary btn-sm me-2" id="selectAll">
                    <i class="fas fa-check-square me-1"></i> Select All
                </button>
                <button type="button" class="btn btn-warning btn-sm" id="clearAll">
                    <i class="fas fa-times-circle me-1"></i> Clear All
                </button>
            </div>

            <div class="table-responsive">
                <table class="table table-bordered text-center align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>{{ __('general.feature') }}</th>
                            @php
                                use Spatie\Permission\Models\Permission;

                                // الصلاحيات بالصيغة الصحيحة المطابقة لقاعدة البيانات
                                $permissionsByCategory = [
                                    'progress-dashboard' => ['view', 'create', 'edit', 'delete'],
                                    'progress-projects' => ['view', 'create', 'edit', 'delete'],
                                    'daily-progress' => ['view', 'create', 'edit', 'delete'],
                                    'progress-issues' => ['view', 'create', 'edit', 'delete'],
                                    'progress-clients' => ['view', 'create', 'edit', 'delete'],
                                    'progress-employees' => ['view', 'create', 'edit', 'delete'],
                                    'progress-work-items' => ['view', 'create', 'edit', 'delete'],
                                    'progress-work-item-categories' => ['view', 'create', 'edit', 'delete'],
                                    'progress-categories' => ['view', 'create', 'edit', 'delete'],
                                    'progress-item-statuses' => ['view', 'create', 'edit', 'delete'],
                                    'progress-project-templates' => ['view', 'create', 'edit', 'delete'],
                                    'progress-project-types' => ['view', 'create', 'edit', 'delete'],
                                    'progress-activity-logs' => ['view'],
                                    'progress-recyclebin' => ['view', 'create', 'edit', 'delete', 'restore', 'force-delete'],
                                    'progress-recycle-bin' => ['view', 'edit'],
                                    'progress-backup' => ['view', 'create', 'download', 'delete'],
                                ];

                                $allActions = collect($permissionsByCategory)->flatten()->unique()->values()->toArray();
                            @endphp
                            @foreach ($allActions as $action)
                                <th>{{ __("general.$action") ?? ucfirst(str_replace('-', ' ', $action)) }}</th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($permissionsByCategory as $feature => $actions)
                            <tr>
                                <td>{{ __("general.$feature") ?? ucfirst(str_replace('-', ' ', $feature)) }}</td>
                                @foreach ($allActions as $actionKey)
                                    @php
                                        $permName = $actionKey . ' ' . $feature;
                                        $permExists = Permission::where('name', $permName)
                                            ->where('guard_name', 'web')
                                            ->exists();
                                        $hasPerm = $permExists ? $employee->hasPermissionTo($permName) : false;
                                    @endphp
                                    <td>
                                        @if (in_array($actionKey, $actions))
                                            <input type="checkbox" name="permissions[]" value="{{ $permName }}"
                                                @if ($hasPerm) checked @endif>
                                        @else
                                            <span class="text-muted">—</span>
                                        @endif
                                    </td>
                                @endforeach
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <button type="submit" class="btn btn-success mt-3">{{ __('general.save_changes') }}</button>
        </form>
    </div>

    
    <script>
        document.getElementById('selectAll').addEventListener('click', function() {
            document.querySelectorAll('input[type="checkbox"]').forEach(cb => cb.checked = true);
        });

        document.getElementById('clearAll').addEventListener('click', function() {
            document.querySelectorAll('input[type="checkbox"]').forEach(cb => cb.checked = false);
        });

        // Debug: Log form submission
        document.querySelector('form').addEventListener('submit', function(e) {
            const checkedPermissions = Array.from(document.querySelectorAll('input[name="permissions[]"]:checked'))
                .map(cb => cb.value);
            
            console.log('Form submitting with permissions:', checkedPermissions);
            console.log('Total permissions selected:', checkedPermissions.length);
            
            // Uncomment to prevent submission for testing
            // e.preventDefault();
        });
    </script>
@endsection
