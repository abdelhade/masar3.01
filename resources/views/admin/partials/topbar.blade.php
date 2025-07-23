<div class="topbar">
    <!-- Navbar -->
    <nav class="navbar-custom">
        <ul class="list-unstyled topbar-nav float-end mb-0">
            @can('عرض التحكم في الاعدادات')
                <li>
                    <a title="المستخدمين" href="{{ route('settings.index') }}" class="nav-link">
                        <i data-feather="settings"></i>
                    </a>
                </li>
            @endcan


            <li class="dropdown">
                <a class="nav-link dropdown-toggle waves-effect waves-light nav-user" data-bs-toggle="dropdown"
                    href="#" role="button" aria-haspopup="false" aria-expanded="false">

                    <img src="{{ asset('assets/images/users/user-5.jpg') }}" alt="profile-user"
                        class="rounded-circle thumb-xs" />
                </a>


                <div class="dropdown-menu dropdown-menu-end">
                    <a class="dropdown-item" href="#"><i data-feather="user"
                            class="align-self-center icon-xs icon-dual me-1"></i> Profile</a>
                    <a class="dropdown-item" href="{{ route('settings.profile') }}"><i data-feather="settings"
                            class="align-self-center icon-xs icon-dual me-1"></i> Settings</a>

                    <a class="dropdown-item" href="#"><i data-feather="user"
                            class="align-self-center icon-xs icon-dual me-1"></i> Profile</a>
                    <a class="dropdown-item" href="{{ route('settings.profile') }}"><i data-feather="settings"
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

            @can('عرض المدراء')
                <li>
                    <a title="المستخدمين" href="{{ route('users.index') }}" class="nav-link">
                        <i class="fas fa-user fa-2x text-primary"></i>
                    </a>
                </li>
            @endcan

            @can('عرض التقارير')
                <li>
                    <a title="التقارير" href="{{ route('reports.index') }}" class="nav-link">
                        <i class="fas fa-chart-pie fa-2x text-primary"></i>
                    </a>

                </li>
            @endcan


        </ul>
    </nav>
    <!-- end navbar-->
</div>
