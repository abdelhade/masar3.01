@extends('admin.dashboard')

@section('sidebar')
    @include('components.sidebar.permissions')
@endsection

@section('content')
    @include('components.breadcrumb', [
        'title' => 'الجلسات النشطة',
        'items' => [
            ['label' => 'الرئيسية', 'url' => route('admin.dashboard')],
            ['label' => 'المستخدمين', 'url' => route('users.index')],
            ['label' => 'الجلسات النشطة'],
        ],
    ])

    <div class="row">
        <div class="col-12">
            {{-- إحصائيات سريعة --}}
            <div class="row mb-4">
                <div class="col-md-4">
                    <div class="card bg-success text-white">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h3 class="mb-0">{{ $activeSessions->count() }}</h3>
                                    <p class="mb-0">جلسة نشطة</p>
                                </div>
                                <i class="fas fa-users fa-3x opacity-50"></i>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card bg-info text-white">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h3 class="mb-0">{{ $activeSessions->unique('user_id')->count() }}</h3>
                                    <p class="mb-0">مستخدم متصل</p>
                                </div>
                                <i class="fas fa-user-check fa-3x opacity-50"></i>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card bg-primary text-white">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h3 class="mb-0">{{ $activeSessions->where('login_at', '>=', now()->subHour())->count() }}</h3>
                                    <p class="mb-0">خلال الساعة الأخيرة</p>
                                </div>
                                <i class="fas fa-clock fa-3x opacity-50"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-header bg-primary text-white">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">
                            <i class="fas fa-desktop me-2"></i>
                            الجلسات النشطة حالياً
                        </h5>
                        <button class="btn btn-light btn-sm" onclick="location.reload()">
                            <i class="fas fa-sync-alt"></i> تحديث
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover table-striped">
                            <thead class="table-light">
                                <tr>
                                    <th>#</th>
                                    <th><i class="fas fa-user"></i> المستخدم</th>
                                    <th><i class="fas fa-envelope"></i> البريد الإلكتروني</th>
                                    <th><i class="fas fa-sign-in-alt"></i> وقت الدخول</th>
                                    <th><i class="fas fa-hourglass-half"></i> منذ</th>
                                    <th><i class="fas fa-network-wired"></i> IP</th>
                                    <th><i class="fas fa-laptop"></i> الجهاز</th>
                                    @can('edit Users')
                                        <th><i class="fas fa-cog"></i> إجراءات</th>
                                    @endcan
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($activeSessions as $session)
                                    <tr>
                                        <td>{{ $loop->iteration }}</td>
                                        <td class="fw-bold">
                                            <i class="fas fa-circle text-success" style="font-size: 8px;"></i>
                                            {{ $session->user->name ?? 'غير معروف' }}
                                        </td>
                                        <td>{{ $session->user->email ?? 'N/A' }}</td>
                                        <td>
                                            @if($session->login_at)
                                                <span class="badge bg-info">
                                                    {{ $session->login_at->format('Y-m-d H:i') }}
                                                </span>
                                            @else
                                                <span class="text-muted">N/A</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($session->login_at)
                                                <span class="text-muted">
                                                    {{ $session->login_at->diffForHumans() }}
                                                </span>
                                            @else
                                                <span class="text-muted">N/A</span>
                                            @endif
                                        </td>
                                        <td>
                                            <code>{{ $session->ip_address ?? 'N/A' }}</code>
                                        </td>
                                        <td>
                                            <small class="text-muted">
                                                @if($session->user_agent)
                                                    @if(str_contains($session->user_agent, 'Mobile'))
                                                        <i class="fas fa-mobile-alt text-primary"></i> موبايل
                                                    @elseif(str_contains($session->user_agent, 'Tablet'))
                                                        <i class="fas fa-tablet-alt text-info"></i> تابلت
                                                    @else
                                                        <i class="fas fa-desktop text-secondary"></i> كمبيوتر
                                                    @endif
                                                @else
                                                    N/A
                                                @endif
                                            </small>
                                        </td>
                                        @can('edit Users')
                                            <td>
                                                <form action="{{ route('users.terminate-session', $session->id) }}"
                                                    method="POST" class="d-inline"
                                                    onsubmit="return confirm('هل أنت متأكد من إنهاء هذه الجلسة؟')">
                                                    @csrf
                                                    <button type="submit" class="btn btn-danger btn-sm">
                                                        <i class="fas fa-power-off"></i> إنهاء
                                                    </button>
                                                </form>
                                            </td>
                                        @endcan
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="8" class="text-center py-4">
                                            <div class="alert alert-info mb-0">
                                                <i class="fas fa-info-circle me-2"></i>
                                                لا توجد جلسات نشطة حالياً
                                            </div>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('styles')
    <style>
        .table th {
            white-space: nowrap;
        }

        code {
            background: #f8f9fa;
            padding: 0.25rem 0.5rem;
            border-radius: 0.25rem;
            font-size: 0.85rem;
        }

        .opacity-50 {
            opacity: 0.5;
        }
    </style>
@endpush

@push('scripts')
    <script>
        // Auto-refresh every 30 seconds
        setTimeout(function() {
            location.reload();
        }, 30000);
    </script>
@endpush

