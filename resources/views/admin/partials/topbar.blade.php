<div class="topbar">
    <!-- Navbar -->
    <nav class="navbar-custom">
        <ul class="list-unstyled topbar-nav float-end mb-0">

            <x-notifications::notifications />

            <!-- مبدل اللغة -->
            <li class="me-3">
                @livewire('language-switcher')
            </li>

            @can('عرض التحكم في الاعدادات')
                <li>
                    <a title="{{ __('navigation.users') }}" href="{{ route('mysettings.index') }}" class="nav-link">
                        <i data-feather="settings" class="text-primary fa-3x"></i>
                    </a>
                </li>
            @endcan
            <li>
                <form method="POST" action="{{ route('logout') }}" style="display: inline;">
                    @csrf
                    <button type="submit" class="btn btn-lg" title="{{ __('navigation.logout') }}"
                        style="background: none; border: none; ">
                        <i class="fas fa-sign-out-alt fa-3x text-primary"></i>
                    </button>
                </form>
            </li>
        </ul><!--end topbar-nav-->

        <ul class="list-unstyled topbar-nav mb-0">

            <li>
                <button class="nav-link button-menu-mobile">
                    <i data-feather="menu" class="align-self-center topbar-icon fa-2x text-primary"></i>
                </button>
            </li>
            <li>
                <a title="help" href="https://www.updates.elhadeerp.com" class="nav-link" target="_blank">
                    <i class="fas fa-book fa-2x text-primary"></i>
                </a>
            </li>
            @can('عرض المدراء')
                <li>
                    <a title="{{ __('navigation.users') }}" href="{{ route('users.index') }}" class="nav-link">
                        <i class="fas fa-user fa-2x text-primary"></i>
                    </a>
                </li>
            @endcan


            @can('عرض التقارير')
                <li>
                    <a title="{{ __('navigation.reports') }}" href="{{ route('reports.index') }}" class="nav-link">
                        <i class="fas fa-chart-pie fa-2x text-primary"></i>
                    </a>

                </li>
            @endcan
        </ul>
    </nav>
    <!-- end navbar-->
</div>
