<div class="topbar">
    <!-- Navbar -->
    <nav class="navbar-custom d-flex justify-content-between align-items-center">
        <ul class="list-unstyled topbar-nav mb-0 d-flex align-items-center">

            <x-notifications::notifications />

            <!-- مبدل اللغة -->
            <li class="me-3">
                @livewire('language-switcher')
            </li>

            {{-- Theme switcher dropdown --}}
            <li class="dropdown me-3" data-masar-theme-dropdown>
                <a class="nav-link dropdown-toggle" data-bs-toggle="dropdown" href="#" role="button"
                    aria-haspopup="false" aria-expanded="false" title="{{ __('Theme') }}" style="color: #34d3a3;">
                    <i class="fas fa-palette fa-2x" style="color: #34d3a3;"></i>
                </a>
                <div class="dropdown-menu dropdown-menu-end">
                    <a class="dropdown-item" href="#" data-masar-theme="classic"><i
                            class="fas fa-palette me-1"></i> Classic (Bootstrap)</a>
                    <a class="dropdown-item" href="#" data-masar-theme="mint-green"><i
                            class="fas fa-leaf me-1"></i> Mint Green</a>
                    <a class="dropdown-item" href="#" data-masar-theme="dark"><i class="fas fa-moon me-1"></i>
                        Dark Mode</a>
                    <a class="dropdown-item" href="#" data-masar-theme="monokai"><i class="fas fa-code me-1"></i>
                        Monokai</a>
                </div>
            </li>

            @can('view Settings Control')
                <li>
                    <a title="{{ __('navigation.users') }}" href="{{ route('mysettings.index') }}"
                        class="nav-link transition-base" style="color: #34d3a3;">
                        <i class="fas fa-cog fa-2x" style="color: #34d3a3;"></i>
                    </a>
                </li>
            @endcan
            <li>
                <button type="button" class="btn btn-lg transition-base logout-btn"
                    title="{{ __('navigation.logout') }}" onclick="confirmLogout()"
                    style="background: none; border: none; color: #34d3a3; cursor: pointer;">
                    <i class="fas fa-sign-out-alt fa-2x" style="color: #34d3a3;"></i>
                </button>

                {{-- الفورم المخفي --}}
                <form id="logout-form" method="POST" action="{{ route('logout') }}" style="display: none;">
                    @csrf
                </form>
            </li>

            <script>
                function confirmLogout() {
                    Swal.fire({
                        title: 'هل أنت متأكد؟',
                        text: "سيتم تسجيل خروجك من النظام",
                        icon: 'warning',
                        iconColor: '#34d3a3',
                        showCancelButton: true,
                        confirmButtonColor: '#34d3a3',
                        cancelButtonColor: '#d33',
                        confirmButtonText: '<i class="fas fa-sign-out-alt"></i> نعم، تسجيل الخروج',
                        cancelButtonText: '<i class="fas fa-times"></i> إلغاء',
                        reverseButtons: true,
                        customClass: {
                            popup: 'animated-popup',
                            confirmButton: 'btn-confirm-logout',
                            cancelButton: 'btn-cancel-logout'
                        },
                        showClass: {
                            popup: 'animate__animated animate__fadeInDown'
                        },
                        hideClass: {
                            popup: 'animate__animated animate__fadeOutUp'
                        }
                    }).then((result) => {
                        if (result.isConfirmed) {
                            // عرض رسالة تحميل
                            Swal.fire({
                                title: 'جاري تسجيل الخروج...',
                                html: '<div class="spinner-border text-success" role="status"><span class="visually-hidden">Loading...</span></div>',
                                showConfirmButton: false,
                                allowOutsideClick: false,
                                timer: 1000
                            });

                            // إرسال الفورم بعد ثانية
                            setTimeout(() => {
                                document.getElementById('logout-form').submit();
                            }, 1000);
                        }
                    });
                }
            </script>

            <style>
                .animated-popup {
                    border-radius: 15px !important;
                    box-shadow: 0 10px 40px rgba(52, 211, 163, 0.3) !important;
                }

                .btn-confirm-logout {
                    border-radius: 8px !important;
                    font-weight: bold !important;
                    padding: 10px 25px !important;
                }

                .btn-cancel-logout {
                    border-radius: 8px !important;
                    font-weight: bold !important;
                    padding: 10px 25px !important;
                }

                .logout-btn:hover i {
                    transform: scale(1.1);
                    transition: transform 0.2s ease;
                }

                .sidebar-toggle-btn:hover i {
                    transform: scale(1.1);
                    transition: transform 0.2s ease;
                }

                .sidebar-toggle-btn:active i {
                    transform: scale(0.95);
                }
            </style>

            <script>
                function toggleSidebarMenu() {
                    const sidebar = document.querySelector('.left-sidenav');
                    const pageWrapper = document.querySelector('.page-wrapper');
                    const toggleIcon = document.getElementById('sidebar-toggle-icon');

                    if (!sidebar || !pageWrapper) {
                        console.warn('Sidebar or page wrapper not found');
                        return;
                    }

                    // Get current state from localStorage or check DOM
                    const currentState = localStorage.getItem('sidebarHidden');
                    const isCurrentlyHidden = currentState === 'true' ||
                        sidebar.style.display === 'none' ||
                        window.getComputedStyle(sidebar).display === 'none';

                    // Toggle state
                    const newState = !isCurrentlyHidden;
                    localStorage.setItem('sidebarHidden', newState.toString());

                    // Apply new state
                    if (newState) {
                        // Hide sidebar
                        sidebar.style.display = 'none';
                        pageWrapper.style.marginLeft = '0';
                        pageWrapper.style.marginRight = '0';
                        if (toggleIcon) {
                            toggleIcon.classList.remove('fa-times');
                            toggleIcon.classList.add('fa-bars');
                        }
                    } else {
                        // Show sidebar
                        sidebar.style.display = '';
                        pageWrapper.style.marginLeft = '';
                        pageWrapper.style.marginRight = '';
                        if (toggleIcon) {
                            toggleIcon.classList.remove('fa-bars');
                            toggleIcon.classList.add('fa-times');
                        }
                    }
                }

                // Update icon state based on sidebar visibility
                function updateSidebarToggleIcon() {
                    const sidebar = document.querySelector('.left-sidenav');
                    const toggleIcon = document.getElementById('sidebar-toggle-icon');

                    if (!sidebar || !toggleIcon) {
                        return;
                    }

                    const isHidden = localStorage.getItem('sidebarHidden') === 'true' ||
                        sidebar.style.display === 'none' ||
                        window.getComputedStyle(sidebar).display === 'none';

                    if (isHidden) {
                        toggleIcon.classList.remove('fa-times');
                        toggleIcon.classList.add('fa-bars');
                    } else {
                        toggleIcon.classList.remove('fa-bars');
                        toggleIcon.classList.add('fa-times');
                    }
                }

                // Initialize icon state on page load
                if (document.readyState === 'loading') {
                    document.addEventListener('DOMContentLoaded', function() {
                        setTimeout(updateSidebarToggleIcon, 100);
                    });
                } else {
                    setTimeout(updateSidebarToggleIcon, 100);
                }

                // Also update on window load
                window.addEventListener('load', function() {
                    setTimeout(updateSidebarToggleIcon, 200);
                });
            </script>

        </ul><!--end topbar-nav-->

        <ul class="list-unstyled topbar-nav mb-0 d-flex align-items-center order-first">
            {{-- Sidebar Toggle Button --}}
            <li class="me-3">
                <button type="button" id="sidebar-toggle-btn" class="btn btn-lg transition-base sidebar-toggle-btn"
                    title="{{ __('إظهار/إخفاء القائمة الجانبية') }}" onclick="toggleSidebarMenu()"
                    style="background: none; border: none; color: #34d3a3; cursor: pointer; padding: 8px 12px;">
                    <i id="sidebar-toggle-icon" class="fas fa-bars fa-2x" style="color: #34d3a3;"></i>
                </button>
            </li>

            <li>
                <a title="help" href="https://www.updates.elhadeerp.com" class="nav-link transition-base"
                    target="_blank" style="color: #34d3a3;">
                    <i class="fas fa-book fa-2x" style="color: #34d3a3;"></i>
                </a>
            </li>
            @can('view Users')
                <li>
                    <a title="{{ __('navigation.users') }}" href="{{ route('users.index') }}"
                        class="nav-link transition-base" style="color: #34d3a3;">
                        <i class="fas fa-user fa-2x" style="color: #34d3a3;"></i>
                    </a>
                </li>
            @endcan



            <li>
                <a title="{{ __('navigation.reports') }}" href="{{ route('reports.index') }}"
                    class="nav-link transition-base" style="color: #34d3a3;">
                    <i class="fas fa-chart-pie fa-2x" style="color: #34d3a3;"></i>
                </a>

            </li>


            <li>
                <a title="{{ __('Branches') }}" href="{{ route('branches.index') }}" class="nav-link transition-base"
                    style="color: #34d3a3;">
                    <i class="fas fa-store fa-2x" style="color: #34d3a3;"></i>
                </a>

            </li>
        </ul>
    </nav>
    <!-- end navbar-->
</div>
