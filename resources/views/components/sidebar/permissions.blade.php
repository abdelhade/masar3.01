<div class="sidebar-header mb-3">
    <h6 class="text-muted fw-bold px-3 mb-2">
        <i class="fas fa-users-cog me-2"></i>
        إدارة المستخدمين والصلاحيات
    </h6>
</div>

{{-- إدارة المستخدمين --}}
@can('view Users')
    <li class="nav-item {{ request()->routeIs('users.*') ? 'active' : '' }}">
        <a class="nav-link" href="{{ route('users.index') }}">
            <i class="fas fa-users"></i>
            <span>المستخدمين</span>
            @can('create Users')
                <span class="badge bg-primary ms-auto">إدارة</span>
            @endcan
        </a>
    </li>
@endcan

{{-- إضافة مستخدم جديد --}}
@can('create Users')
    <li class="nav-item {{ request()->routeIs('users.create') ? 'active' : '' }}">
        <a class="nav-link" href="{{ route('users.create') }}">
            <i class="fas fa-user-plus"></i>
            <span>إضافة مستخدم</span>
        </a>
    </li>
@endcan

@can('view Roles')
    <li class="nav-item {{ request()->routeIs('roles.*') ? 'active' : '' }}">
        <a class="nav-link" href="{{ route('roles.index') }}">
            <i class="fas fa-user-shield"></i>
            <span>الأدوار والصلاحيات</span>
            @can('create Roles')
                <span class="badge bg-success ms-auto">إدارة</span>
            @endcan
        </a>
    </li>
@endcan

<hr class="my-3 border-secondary">

<div class="sidebar-header mb-2">
    <h6 class="text-muted fw-bold px-3 mb-2">
        <i class="fas fa-code-branch me-2"></i>
        إدارة الفروع
    </h6>
</div>

{{-- الفروع --}}
@can('view Branches')
    <li class="nav-item {{ request()->routeIs('branches.*') ? 'active' : '' }}">
        <a class="nav-link" href="{{ route('branches.index') }}">
            <i class="fas fa-building"></i>
            <span>الفروع</span>
        </a>
    </li>
@endcan

{{-- إعدادات النظام --}}
@can('عرض التحكم في الاعدادات')
    <li class="nav-item {{ request()->routeIs('mysettings.*') ? 'active' : '' }}">
        <a class="nav-link" href="{{ route('mysettings.index') }}">
            <i class="fas fa-cogs"></i>
            <span>إعدادات النظام</span>
            <span class="badge bg-warning ms-auto">متقدم</span>
        </a>
    </li>
@endcan

<hr class="my-3 border-secondary">

<div class="sidebar-header mb-2">
    <h6 class="text-muted fw-bold px-3 mb-2">
        <i class="fas fa-history me-2"></i>
        السجلات والمراقبة
    </h6>
</div>

{{-- سجل تسجيل الدخول --}}
@can('view Users')
    <li class="nav-item {{ request()->routeIs('users.login-history*') ? 'active' : '' }}">
        <a class="nav-link" href="{{ route('users.login-history') }}">
            <i class="fas fa-sign-in-alt"></i>
            <span>سجل تسجيل الدخول</span>
        </a>
    </li>
@endcan

{{-- إدارة الجلسات النشطة --}}
@can('view Users')
    <li class="nav-item {{ request()->routeIs('users.active-sessions*') ? 'active' : '' }}">
        <a class="nav-link" href="{{ route('users.active-sessions') }}">
            <i class="fas fa-desktop"></i>
            <span>الجلسات النشطة</span>
            <span class="badge bg-success ms-auto" id="active-sessions-count">{{ \App\Models\LoginSession::whereNull('logout_at')->count() }}</span>
        </a>
    </li>
@endcan

{{-- سجل النشاطات --}}
@can('view Users')
    <li class="nav-item {{ request()->routeIs('users.activity-log*') ? 'active' : '' }}">
        <a class="nav-link" href="{{ route('users.activity-log') }}">
            <i class="fas fa-list-alt"></i>
            <span>سجل النشاطات</span>
        </a>
    </li>
@endcan

<hr class="my-3 border-secondary">

<div class="sidebar-header mb-2">
    <h6 class="text-muted fw-bold px-3 mb-2">
        <i class="fas fa-chart-line me-2"></i>
        إحصائيات سريعة
    </h6>
</div>

{{-- إحصائيات النظام --}}
@can('view Users')
    <li class="nav-item">
        <a class="nav-link" href="javascript:void(0);" onclick="showUserStats()">
            <i class="fas fa-chart-pie"></i>
            <span>إحصائيات النظام</span>
        </a>
    </li>
@endcan

@push('styles')
<style>
    .sidebar-header h6 {
        font-size: 0.75rem;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }
    
    .nav-item.active .nav-link {
        background-color: rgba(13, 110, 253, 0.1);
        border-right: 3px solid #0d6efd;
        color: #0d6efd !important;
        font-weight: 600;
    }
    
    .nav-link {
        display: flex;
        align-items: center;
        padding: 0.75rem 1rem;
        color: #6c757d;
        transition: all 0.3s ease;
        border-radius: 0.5rem;
        margin: 0.25rem 0.5rem;
    }
    
    .nav-link:hover {
        background-color: rgba(13, 110, 253, 0.05);
        color: #0d6efd;
        transform: translateX(-5px);
    }
    
    .nav-link i {
        width: 25px;
        margin-left: 10px;
        text-align: center;
        font-size: 1.1rem;
    }
    
    .nav-link .badge {
        font-size: 0.7rem;
        padding: 0.25rem 0.5rem;
    }
</style>
@endpush

@push('scripts')
<script>
function showUserStats() {
    Swal.fire({
        title: '<i class="fas fa-chart-pie text-primary"></i> إحصائيات النظام',
        html: `
            <div class="text-start">
                <div class="row g-3">
                    <div class="col-6">
                        <div class="card border-0 shadow-sm bg-primary bg-gradient text-white">
                            <div class="card-body text-center">
                                <i class="fas fa-users fs-1 mb-2"></i>
                                <h3 class="mb-0">{{ \App\Models\User::count() }}</h3>
                                <small>إجمالي المستخدمين</small>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-6">
                        <div class="card border-0 shadow-sm bg-success bg-gradient text-white">
                            <div class="card-body text-center">
                                <i class="fas fa-user-check fs-1 mb-2"></i>
                                <h3 class="mb-0">{{ \App\Models\User::whereNotNull('email_verified_at')->count() }}</h3>
                                <small>مستخدمين نشطين</small>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-6">
                        <div class="card border-0 shadow-sm bg-info bg-gradient text-white">
                            <div class="card-body text-center">
                                <i class="fas fa-shield-alt fs-1 mb-2"></i>
                                <h3 class="mb-0">{{ \Modules\Authorization\Models\Permission::count() }}</h3>
                                <small>إجمالي الصلاحيات</small>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-6">
                        <div class="card border-0 shadow-sm bg-warning bg-gradient text-white">
                            <div class="card-body text-center">
                                <i class="fas fa-code-branch fs-1 mb-2"></i>
                                <h3 class="mb-0">{{ \Modules\Branches\Models\Branch::count() }}</h3>
                                <small>عدد الفروع</small>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-6">
                        <div class="card border-0 shadow-sm bg-secondary bg-gradient text-white">
                            <div class="card-body text-center">
                                <i class="fas fa-user-tag fs-1 mb-2"></i>
                                <h3 class="mb-0">{{ \Modules\Authorization\Models\Role::count() }}</h3>
                                <small>عدد الأدوار</small>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-6">
                        <div class="card border-0 shadow-sm bg-dark bg-gradient text-white">
                            <div class="card-body text-center">
                                <i class="fas fa-clock fs-1 mb-2"></i>
                                <h3 class="mb-0">{{ \App\Models\User::where('created_at', '>=', now()->subDays(7))->count() }}</h3>
                                <small>مستخدمين جدد (7 أيام)</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        `,
        showConfirmButton: true,
        confirmButtonText: '<i class="fas fa-times"></i> إغلاق',
        width: '700px',
        customClass: {
            popup: 'animated fadeInDown'
        }
    });
}

function showActiveSessions() {
    // Fetch active sessions via AJAX
    fetch('/api/active-sessions', {
        method: 'GET',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        }
    })
    .then(response => response.json())
    .then(data => {
        const sessions = data.sessions || [];
        let html = '<div class="text-start">';
        
        if (sessions.length === 0) {
            html += `
                <div class="alert alert-info">
                    <i class="fas fa-info-circle me-2"></i>
                    لا توجد جلسات نشطة حالياً
                </div>
            `;
        } else {
            html += '<div class="table-responsive"><table class="table table-hover mb-0">';
            html += `
                <thead class="table-light">
                    <tr>
                        <th><i class="fas fa-user"></i> المستخدم</th>
                        <th><i class="fas fa-laptop"></i> الجهاز</th>
                        <th><i class="fas fa-clock"></i> آخر نشاط</th>
                    </tr>
                </thead>
                <tbody>
            `;
            
            sessions.forEach(session => {
                html += `
                    <tr>
                        <td class="fw-bold">${session.user}</td>
                        <td><small class="text-muted">${session.device}</small></td>
                        <td><small class="text-muted">${session.last_activity}</small></td>
                    </tr>
                `;
            });
            
            html += '</tbody></table></div>';
        }
        
        html += '</div>';
        
        Swal.fire({
            title: '<i class="fas fa-desktop text-success"></i> الجلسات النشطة',
            html: html,
            confirmButtonText: '<i class="fas fa-times"></i> إغلاق',
            width: '600px',
            customClass: {
                popup: 'animated fadeInDown'
            }
        });
    })
    .catch(error => {
        Swal.fire({
            title: '<i class="fas fa-desktop text-info"></i> الجلسات النشطة',
            html: `
                <div class="text-center py-4">
                    <i class="fas fa-users fs-1 text-success mb-3"></i>
                    <h5 class="text-muted">{{ \App\Models\LoginSession::where('logout_at', null)->count() }} جلسة نشطة</h5>
                    <p class="text-muted mb-0">المستخدمون المتصلون حالياً بالنظام</p>
                </div>
            `,
            confirmButtonText: '<i class="fas fa-times"></i> إغلاق',
            width: '500px'
        });
    });
}
</script>
@endpush
