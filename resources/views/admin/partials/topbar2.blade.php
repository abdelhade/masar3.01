<div class="">
    <!-- Navbar -->
    <nav class="navbar-custom">
        <ul class="list-unstyled topbar-nav float-end mb-0">

            {{-- Theme switcher dropdown --}}
            <li class="dropdown" data-masar-theme-dropdown>
                <a class="nav-link dropdown-toggle" data-bs-toggle="dropdown" href="#" role="button" aria-haspopup="false" aria-expanded="false" title="{{ __('Theme') }}">
                    <i data-feather="droplet" class="align-self-center"></i>
                </a>
                <div class="dropdown-menu dropdown-menu-end">
                    <a class="dropdown-item" href="#" data-masar-theme="classic"><i class="fas fa-palette me-1"></i> Classic (Bootstrap)</a>
                    <a class="dropdown-item" href="#" data-masar-theme="mint-green"><i class="fas fa-leaf me-1"></i> Mint Green</a>
                    <a class="dropdown-item" href="#" data-masar-theme="dark"><i class="fas fa-moon me-1"></i> Dark Mode</a>
                    <a class="dropdown-item" href="#" data-masar-theme="monokai"><i class="fas fa-code me-1"></i> Monokai</a>
                </div>
            </li>

            @can('عرض الاعدادات')
                <li>
                    <a title="الإعدادات" href="{{ route('mysettings.index') }}" class="nav-link">
                        <i data-feather="settings"></i>
                    </a>
                </li>
            @endcan

            <li>
                <a title="المستخدمين" href="{{ route('settings.index') }}" class="nav-link">
                    <i data-feather="settings"></i>
                </a>
            </li>


            <li class="dropdown">
                <a class="nav-link dropdown-toggle waves-effect waves-light nav-user" data-bs-toggle="dropdown" href="#"
                    role="button" aria-haspopup="false" aria-expanded="false">

                    <img src="{{ asset('assets/images/users/user-5.jpg') }}" alt="profile-user"
                        class="rounded-circle thumb-xs" />
                </a>


                <div class="dropdown-menu dropdown-menu-end">
                    <a class="dropdown-item" href="#"><i data-feather="user"
                            class="align-self-center icon-xs icon-dual me-1"></i> Profile</a>
                    <a class="dropdown-item" href="{{ route('my-settings.profile') }}"><i data-feather="settings"
                            class="align-self-center icon-xs icon-dual me-1"></i> Settings</a>

                    <a class="dropdown-item" href="#"><i data-feather="user"
                            class="align-self-center icon-xs icon-dual me-1"></i> Profile</a>
                    <a class="dropdown-item" href="{{ route('my-settings.profile') }}"><i data-feather="settings"
                            class="align-self-center icon-xs icon-dual me-1"></i> Settings</a>

                    <div class="dropdown-divider mb-0"></div>
                    <form method="POST" action="{{ route('logout') }}" class="d-inline">
                        @csrf
                        <button type="submit" class="dropdown-item">
                            <i data-feather="power" class="align-self-center icon-xs icon-dual me-1"></i> Logout
                        </button>
                    </form>
                </div>
            </li>

        </ul><!--end topbar-nav-->

        <ul class="list-unstyled topbar-nav mb-0">

            <li>
                <button class="nav-link button-menu-mobile">
                    <i data-feather="menu" class="align-self-center topbar-icon fa-2x text-primary"></i>
                </button>
            </li>
            @can('view Users')
                <li>
                    <a title="المستخدمين" href="{{ route('users.index') }}" class="nav-link">
                        <i data-feather="user"></i>.
                    </a>
                </li>
            @endcan


            <li>
                <a title="المستخدمين" href="{{ route('users.index') }}" class="nav-link">
                    <i class="fas fa-user fa-2x text-primary"></i>
                </a>
            </li>
            <li>
                <a title="المستخدمين" href="{{ route('reports.index') }}" class="nav-link">
                    <i class="fas fa-chart-pie fa-2x text-primary"></i>
                </a>

            </li>

        </ul>
    </nav>
    <!-- end navbar-->
</div>
