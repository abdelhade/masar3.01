<div class="topbar">
    <!-- Navbar -->
    <nav class="navbar-custom d-flex justify-content-between align-items-center">
        <ul class="list-unstyled topbar-nav mb-0 d-flex align-items-center">

            <x-notifications::notifications />

            <!-- مبدل اللغة -->
            <li class="me-3">
                @livewire('language-switcher')
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
            </style>

        </ul><!--end topbar-nav-->

        <ul class="list-unstyled topbar-nav mb-0 d-flex align-items-center order-first">

            <li>
                <button 
                    x-data="{ sidebarHidden: localStorage.getItem('sidebarHidden') === 'true' }"
                    @click="sidebarHidden = !sidebarHidden; localStorage.setItem('sidebarHidden', sidebarHidden); toggleSidebar()"
                    class="nav-link transition-base" 
                    style="color: #34d3a3;"
                    title="{{ __('navigation.toggle_sidebar') }}">
                    <i x-show="!sidebarHidden" class="fas fa-bars fa-2x align-self-center topbar-icon" style="color: #34d3a3;"></i>
                    <i x-show="sidebarHidden" class="fas fa-x fa-2x align-self-center topbar-icon" style="color: #34d3a3;"></i>
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
