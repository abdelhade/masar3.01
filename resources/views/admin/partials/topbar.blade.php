<div class="topbar">
    <!-- Navbar -->
    <nav class="navbar-custom">
        <ul class="list-unstyled topbar-nav float-end mb-0">

            <x-notifications::notifications />

            <!-- مبدل اللغة -->
            <li class="me-3">
                @livewire('language-switcher')
            </li>

            <!-- مبدل الثيم -->
            <li class="me-3 dropdown">
                <a class="nav-link dropdown-toggle transition-base" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false" style="color: #34d3a3;" title="تبديل الثيم">
                    <i class="fas fa-palette fa-2x" style="color: #34d3a3;"></i>
                </a>
                <ul class="dropdown-menu dropdown-menu-end">
                    <li>
                        <a class="dropdown-item {{ session('theme') === '' || session('theme') === null ? 'active' : '' }}" href="{{ route('theme.switch') }}">
                            <i class="fas fa-circle me-2"></i> الافتراضي
                        </a>
                    </li>
                    <li>
                        <a class="dropdown-item {{ session('theme') === 'theme-neumorphism-lite' ? 'active' : '' }}" href="{{ route('theme.switch', 'theme-neumorphism-lite') }}">
                            <i class="fas fa-circle me-2"></i> Neumorphism Lite
                        </a>
                    </li>
                </ul>
            </li>

            @can('view Settings Control')
                <li>
                    <a title="{{ __('navigation.users') }}" href="{{ route('mysettings.index') }}" class="nav-link transition-base" style="color: #34d3a3;">
                        <i class="fas fa-cog fa-2x" style="color: #34d3a3;"></i>
                    </a>
                </li>
            @endcan
            <li>
                <form method="POST" action="{{ route('logout') }}" style="display: inline;">
                    @csrf
                    <button type="submit" class="btn btn-lg transition-base" title="{{ __('navigation.logout') }}"
                        style="background: none; border: none; color: #34d3a3;">
                        <i class="fas fa-sign-out-alt fa-2x" style="color: #34d3a3;"></i>
                    </button>
                </form>
            </li>
        </ul><!--end topbar-nav-->

        <ul class="list-unstyled topbar-nav mb-0">

            <li>
                <button class="nav-link button-menu-mobile transition-base" style="color: #34d3a3;">
                    <i class="fas fa-bars fa-2x align-self-center topbar-icon" style="color: #34d3a3;"></i>
                </button>
            </li>
            <li>
                <a title="help" href="https://www.updates.elhadeerp.com" class="nav-link transition-base" target="_blank" style="color: #34d3a3;">
                    <i class="fas fa-book fa-2x" style="color: #34d3a3;"></i>
                </a>
            </li>
            @can('view Users')
                <li>
                    <a title="{{ __('navigation.users') }}" href="{{ route('users.index') }}" class="nav-link transition-base" style="color: #34d3a3;">
                        <i class="fas fa-user fa-2x" style="color: #34d3a3;"></i>
                    </a>
                </li>
            @endcan


          
                <li>
                    <a title="{{ __('navigation.reports') }}" href="{{ route('reports.index') }}" class="nav-link transition-base" style="color: #34d3a3;">
                        <i class="fas fa-chart-pie fa-2x" style="color: #34d3a3;"></i>
                    </a>

                </li>
     

            <li>
                <a title="{{ __('Branches') }}" href="{{ route('branches.index') }}" class="nav-link transition-base" style="color: #34d3a3;">
                    <i class="fas fa-store fa-2x" style="color: #34d3a3;"></i>
                </a>

            </li>
        </ul>
    </nav>
    <!-- end navbar-->
</div>
