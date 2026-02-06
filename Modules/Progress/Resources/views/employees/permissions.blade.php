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

                                $permissionsByCategory = [
                                    'users' => ['list', 'create', 'edit', 'delete'],
                                    'projects' => ['list', 'view-all', 'create', 'edit', 'delete', 'view', 'progress','gantt', 'save-as-template', 'copy'],
                                    'project-templates' => ['list', 'create', 'edit', 'delete', 'view'],
                                    'employees' => ['list', 'create', 'permissions', 'edit', 'delete'],
                                    'dailyprogress' => ['list', 'create', 'edit', 'delete'],
                                    'items' => ['list', 'create', 'edit', 'delete'],
                                    'categories' => ['list', 'create', 'edit', 'delete'],
                                    'dashboard' => ['view'],
                                    'project-types' => ['list', 'create', 'edit', 'delete'],
                                    'activity-logs' => ['list', 'view', 'delete'],
                                    'recycle-bin' => ['list', 'restore', 'permanent-delete'],
                                    'backup'=>['view', 'create'],
                                    'item-statuses' => ['list', 'create', 'edit', 'delete', 'view'],
                                    'issues' => ['list', 'create', 'edit', 'delete', 'view']
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
                                <td>{{ __("general.$feature") ?? ucfirst($feature) }}</td>
                                @foreach ($allActions as $actionKey)
                                    @php
                                        $permName = $feature . '-' . $actionKey;
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
    </script>
@endsection
