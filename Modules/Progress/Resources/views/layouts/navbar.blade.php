<nav class="main-header navbar navbar-expand-sm">
    <div class="container-fluid">
        <button class="btn btn-outline-primary me-3 d-md-none" onclick="toggleSidebar()" title="{{ __('general.toggle_sidebar') }}">
            <i class="fas fa-bars"></i>
        </button>

        <a class="navbar-brand" href="{{ url('/') }}">
            <i class="fas fa-hard-hat me-2"></i> {{ __('general.system_name') }}
        </a>

        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav"
            aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse " id="navbarNav">
            <ul
                class="navbar-nav {{ ($currentLocale ?? session('locale', app()->getLocale())) == 'ar' ? 'me-auto' : 'ms-auto' }}">
                @can('create daily-progress')
                <li class="nav-item">
                    <a class="nav-link" href="{{ route('daily_progress.create') }}">
                        <i class="fas fa-calendar-day me-2"></i> {{ __('general.daily_progress') }}
                    </a>
                </li>
                @endcan
            </ul>

            <ul class="navbar-nav">
                @auth
                    
                    <li class="nav-item">
                        <a class="nav-link">
                            <i class="fas fa-user me-1"></i> {{ Auth::user()->name }}
                        </a>
                    </li>

                    
                    <li class="nav-item">
                        <form method="POST" action="{{ route('logout') }}" style="display:inline;">
                            @csrf
                            <button type="submit" class="btn btn-danger nav-link">
                                <i class="fas fa-sign-out-alt me-1"></i> {{ __('general.logout') }}
                            </button>
                        </form>
                    </li>
                @endauth

                
                <li class="nav-item">
                    <button id="darkModeToggle" class="btn btn-sm" title="Toggle Dark Mode" aria-label="Toggle Dark Mode">
                        <i class="fas fa-moon" id="darkModeIcon"></i>
                    </button>
                </li>

                
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" id="languageDropdown" role="button"
                        data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="fas fa-globe"></i>
                        {{ ($currentLocale ?? session('locale', app()->getLocale())) == 'ar' ? 'العربية' : 'English' }}
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="languageDropdown">
                        <li>
                            <a class="dropdown-item {{ ($currentLocale ?? session('locale', app()->getLocale())) == 'ar' ? 'active' : '' }}"
                                href="{{ route('locale.switch', 'ar') }}">
                                <i class="fas fa-flag me-2"></i>العربية
                            </a>
                        </li>
                        <li>
                            <a class="dropdown-item {{ ($currentLocale ?? session('locale', app()->getLocale())) == 'en' ? 'active' : '' }}"
                                href="{{ route('locale.switch', 'en') }}">
                                <i class="fas fa-flag me-2"></i>English
                            </a>
                        </li>
                        <li>
                            <a class="dropdown-item {{ ($currentLocale ?? session('locale', app()->getLocale())) == 'ur' ? 'active' : '' }}"
                                href="{{ route('locale.switch', 'ur') }}">
                                <i class="fas fa-flag me-2"></i>اردو
                            </a>
                        </li>
                        <li>
                            <a class="dropdown-item {{ ($currentLocale ?? session('locale', app()->getLocale())) == 'hi' ? 'active' : '' }}"
                                href="{{ route('locale.switch', 'hi') }}">
                                <i class="fas fa-flag me-2"></i>हिन्दी
                            </a>
                        </li>

                    </ul>
                </li>
            </ul>
        </div>
    </div>
</nav>
