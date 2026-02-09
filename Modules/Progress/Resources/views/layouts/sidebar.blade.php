<div class="sidebar col-md-3 col-lg-2 d-md-block collapse" id="sidebarMenu">
    <div class="position-sticky pt-3">
        
        <div class="sidebar-header d-flex justify-content-between align-items-center px-3 pb-2 mb-2 border-bottom">
            <h6 class="mb-0">{{ __('general.menu') }}</h6>
            <button type="button" class="btn btn-sm btn-outline-secondary sidebar-close-btn" 
                    onclick="toggleSidebar()" 
                    title="{{ __('general.toggle_sidebar') }}">
                <i class="fas fa-times"></i>
            </button>
        </div>
        
        
        <div class="sidebar-expand-btn text-center py-2" style="display: none;">
            <button type="button" class="btn btn-sm btn-primary" 
                    onclick="toggleSidebar()" 
                    title="{{ __('general.toggle_sidebar') }}">
                <i class="fas fa-chevron-right"></i>
            </button>
        </div>
        
        <ul class="nav flex-column">

            
            @can('view progress-dashboard')
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('progress.dashboard') ? 'active' : '' }}"
                        href="{{ route('progress.dashboard') }}" data-title="{{ __('general.dashboard') }}">
                        <i class="fas fa-home me-2"></i> <span>{{ __('general.dashboard') }}</span>
                    </a>
                </li>
            @endcan

            @can('view progress-projects')
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('progress.projects.*') ? 'active' : '' }}"
                        href="{{ route('progress.projects.index') }}" data-title="{{ __('general.projects') }}">
                        <i class="fas fa-project-diagram me-2"></i> <span>{{ __('general.projects') }}</span>
                    </a>
                </li>
            @endcan

            @can('view progress-issues')
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('progress.issues.*') ? 'active' : '' }}"
                        href="{{ route('progress.issues.index') }}" data-title="{{ __('general.issues_management') }}">
                        <i class="fas fa-bug me-2"></i> <span>{{ __('general.issues') }}</span>
                    </a>
                </li>
            @endcan

            @can('view daily-progress')
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('progress.daily-progress.index') ? 'active' : '' }}"
                        href="{{ route('progress.daily-progress.index') }}" data-title="{{ __('general.daily_progress') }}">
                        <i class="fas fa-list-alt me-2"></i> <span>{{ __('general.daily_progress') }}</span>
                    </a>
                </li>
            @endcan

            @can('create daily-progress')
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('progress.daily-progress.create') ? 'active' : '' }}"
                        href="{{ route('progress.daily-progress.create') }}" data-title="{{ __('general.create_daily_progress') }}">
                        <i class="fas fa-plus-circle me-2"></i> <span>{{ __('general.create_daily_progress') }}</span>
                    </a>
                </li>
            @endcan
            @can('view progress-clients')
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('progress.clients.*') ? 'active' : '' }}"
                        href="{{ route('progress.clients.index') }}" data-title="{{ __('general.clients') }}">
                        <i class="fas fa-users me-2"></i> <span>{{ __('general.clients') }}</span>
                    </a>
                </li>
            @endcan

            @can('view progress-employees')
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('progress.employees.*') ? 'active' : '' }}"
                        href="{{ route('progress.employees.index') }}" data-title="{{ __('general.employees') }}">
                        <i class="fas fa-user-tie me-2"></i> <span>{{ __('general.employees') }}</span>
                    </a>
                </li>
            @endcan
            @can('view progress-work-item-categories')
            <li class="nav-item">
                <a href="{{ route('progress.categories.index') }}"
                    class="nav-link {{ request()->routeIs('progress.categories.*') ? 'active' : '' }}" data-title="{{ __('general.categories') }}">
                    <i class="fas fa-layer-group me-2"></i>
                    <span>{{ __('general.categories') }}</span>
                </a>
            </li>
            @endcan

            @can('view progress-work-items')
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('progress.work-items.*') ? 'active' : '' }}"
                        href="{{ route('progress.work-items.index') }}" data-title="{{ __('general.work_items') }}">
                        <i class="fas fa-tasks me-2"></i> <span>{{ __('general.work_items') }}</span>
                    </a>
                </li>
            @endcan

            @can('view progress-item-statuses')
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('progress.item-statuses.*') ? 'active' : '' }}"
                        href="{{ route('progress.item-statuses.index') }}" data-title="{{ __('general.item_statuses') }}">
                        <i class="fas fa-tags me-2"></i> <span>{{ __('general.item_statuses') }}</span>
                    </a>
                </li>
            @endcan

            @can('view progress-project-templates')
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('progress.project-templates.*') ? 'active' : '' }}"
                        href="{{ route('progress.project-templates.index') }}" data-title="{{ __('general.project_templates') }}">
                        <i class="fas fa-file-alt  me-2"></i> <span>{{ __('general.project_templates') }}</span>
                    </a>
                </li>
            @endcan
            @can('view progress-project-types')
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('progress.project_types.*') ? 'active' : '' }}"
                        href="{{ route('progress.project_types.index') }}" data-title="{{ __('general.project__type') }}">
                        <i class="fa-solid fa-diagram-next  me-2"></i> <span>{{ __('general.project__type') }}</span>
                    </a>
                </li>
            @endcan

            @can('view progress-activity-logs')
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('progress.activity-logs.*') ? 'active' : '' }}"
                        href="{{ route('progress.activity-logs.index') }}" data-title="{{ __('activity-logs.activity_logs_nav') }}">
                        <i class="fas fa-history me-2"></i> <span>{{ __('activity-logs.activity_logs_nav') }}</span>
                    </a>
                </li>
            @endcan
            @can('view progress-recycle-bin')
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('progress.recycle-bin.*') ? 'active' : '' }}"
                        href="{{ route('progress.recycle-bin.index') }}" data-title="{{ __('general.recycle_bin_title') }}">
                        <i class="fas fa-trash-alt me-2"></i><span>{{ __('general.recycle_bin_title') }}</span>
                    </a>
                </li>
            @endcan
            @can('view progress-backup')
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('progress.backup.*') ? 'active' : '' }}"
                        href="{{ route('progress.backup.index') }}" data-title="{{ __('general.backup_restore') }}">
                        <i class="fas fa-database me-2"></i> <span>{{ __('general.backup_restore') }}</span>
                    </a>
                </li>
            @endcan



        </ul>
    </div>
</div>
