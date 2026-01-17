<div class="sidebar progress-sidebar col-md-3 col-lg-2 d-md-block collapse" id="sidebarMenu">
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

            
                <li class="nav-item">
                    <a class="nav-link d-flex align-items-center gap-3"
                        href="{{ route('admin.dashboard') }}" data-title="{{ __('general.home') }}">
                        <i class="fas fa-home"></i> <span>{{ __('general.home') }}</span>
                    </a>
                </li>

                @can('view progress-dashboard')
                <li class="nav-item">
                    <a class="nav-link d-flex align-items-center gap-3 {{ request()->routeIs('progress.dashboard') ? 'active' : '' }}"
                        href="{{ route('progress.dashboard') }}" data-title="{{ __('progress::dashboard.title') }}">
                        <i class="fas fa-tachometer-alt"></i> <span>{{ __('progress::dashboard.title') }}</span>
                    </a>
                </li>
                @endcan
                


                @can('view progress-projects')
                <li class="nav-item">
                    <a class="nav-link d-flex align-items-center gap-3 {{ request()->routeIs('projects.*') ? 'active' : '' }}"
                        href="{{ route('progress.project.index') }}" data-title="{{ __('general.projects') }}">
                        <i class="fas fa-project-diagram"></i> <span>{{ __('general.projects') }}</span>
                    </a>
                </li>
                @endcan

                @can('view progress-issues')
                <li class="nav-item">
                    <a class="nav-link d-flex align-items-center gap-3 {{ request()->routeIs('issues.*') ? 'active' : '' }}"
                        href="{{ route('issues.index') }}" data-title="{{ __('general.issues_management') }}">
                        <i class="fas fa-bug"></i> <span>{{ __('general.issues') }}</span>
                    </a>
                </li>
                @endcan

                @can('view daily-progress')
                <li class="nav-item">
                    <a class="nav-link d-flex align-items-center gap-3 {{ request()->routeIs('daily_progress.index') ? 'active' : '' }}"
                        href="{{ route('daily_progress.index') }}" data-title="{{ __('general.daily_progress') }}">
                        <i class="fas fa-list-alt"></i> <span>{{ __('general.daily_progress') }}</span>
                    </a>
                </li>
                @endcan

                @can('create daily-progress')
                <li class="nav-item">
                    <a class="nav-link d-flex align-items-center gap-3 {{ request()->routeIs('daily_progress.create') ? 'active' : '' }}"
                        href="{{ route('daily_progress.create') }}" data-title="{{ __('general.create_daily_progress') }}">
                        <i class="fas fa-plus-circle"></i> <span>{{ __('general.create_daily_progress') }}</span>
                    </a>
                </li>
                @endcan 

                @can('view Clients')
                <li class="nav-item">
                    <a class="nav-link d-flex align-items-center gap-3 {{ request()->routeIs('clients.*') ? 'active' : '' }}"
                        href="{{ route('clients.index') }}" data-title="{{ __('general.clients') }}">
                        <i class="fas fa-users"></i> <span>{{ __('general.clients') }}</span>
                    </a>
                </li>
                @endcan

                @can('view Employees')
                <li class="nav-item">
                    <a class="nav-link d-flex align-items-center gap-3 {{ request()->routeIs('employees.*') ? 'active' : '' }}"
                        href="{{ route('employees.index') }}" data-title="{{ __('general.employees') }}">
                        <i class="fas fa-user-tie"></i> <span>{{ __('general.employees') }}</span>
                    </a>
                </li>
                @endcan

                @can('view progress-work-item-categories')
            <li class="nav-item">
                <a href="{{ route('work-item-categories.index') }}"
                    class="nav-link d-flex align-items-center gap-3 {{ request()->routeIs('work-item-categories.*') ? 'active' : '' }}" data-title="{{ __('general.categories') }}">
                    <i class="fas fa-layer-group"></i>
                    <span>{{ __('general.categories') }}</span>
                </a>
            </li>
                @endcan

                @can('view progress-work-items')
                <li class="nav-item">
                    <a class="nav-link d-flex align-items-center gap-3 {{ request()->routeIs('work.items.*') ? 'active' : '' }}"
                        href="{{ route('work.items.index') }}" data-title="{{ __('general.work_items') }}">
                        <i class="fas fa-tasks"></i> <span>{{ __('general.work_items') }}</span>
                    </a>
                </li>
                @endcan

                @can('view progress-item-statuses')
                <li class="nav-item">
                    <a class="nav-link d-flex align-items-center gap-3 {{ request()->routeIs('item-statuses.*') ? 'active' : '' }}"
                        href="{{ route('item-statuses.index') }}" data-title="{{ __('general.item_statuses') }}">
                        <i class="fas fa-tags"></i> <span>{{ __('general.item_statuses') }}</span>
                    </a>
                </li>
                @endcan

                @can('view progress-project-templates')
                <li class="nav-item">
                    <a class="nav-link d-flex align-items-center gap-3 {{ request()->routeIs('project.template.*') ? 'active' : '' }}"
                        href="{{ route('project.template.index') }}" data-title="{{ __('general.project_templates') }}">
                        <i class="fas fa-file-alt"></i> <span>{{ __('general.project_templates') }}</span>
                    </a>
                </li>
                @endcan

                @can('view progress-project-types')
                <li class="nav-item">
                    <a class="nav-link d-flex align-items-center gap-3 {{ request()->routeIs('project.types.*') ? 'active' : '' }}"
                        href="{{ route('project.types.index') }}" data-title="{{ __('general.project__type') }}">
                        <i class="fa-solid fa-diagram-next"></i> <span>{{ __('general.project__type') }}</span>
                    </a>
                </li>
                @endcan

                @can('view progress-recyclebin')
                <li class="nav-item">
                     <a class="nav-link d-flex align-items-center gap-3 {{ request()->routeIs('progress.recycle_bin.*') ? 'active' : '' }}"
                        href="{{ route('progress.recycle_bin.index') }}" data-title="{{ __('progress::dashboard.recycle_bin') }}">
                        <i class="las la-trash"></i> <span>{{ __('progress::dashboard.recycle_bin') }}</span>
                    </a>
                </li>
                @endcan
        </ul>
    </div>
</div>
