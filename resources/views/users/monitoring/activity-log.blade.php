@extends('admin.dashboard')

@section('sidebar')
    @include('components.sidebar.permissions')
@endsection

@section('content')
    @include('components.breadcrumb', [
        'title' => 'سجل النشاطات',
        'items' => [
            ['label' => 'الرئيسية', 'url' => route('admin.dashboard')],
            ['label' => 'المستخدمين', 'url' => route('users.index')],
            ['label' => 'سجل النشاطات'],
        ],
    ])

    <div class="row">
        <div class="col-12">
            {{-- Filters --}}
            <div class="card mb-3">
                <div class="card-body">
                    <form action="{{ route('users.activity-log') }}" method="GET">
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label class="form-label">المستخدم</label>
                                <select name="user_id" class="form-select">
                                    <option value="">الكل</option>
                                    @foreach ($users as $user)
                                        <option value="{{ $user->id }}"
                                            {{ request('user_id') == $user->id ? 'selected' : '' }}>
                                            {{ $user->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">من تاريخ</label>
                                <input type="date" name="date_from" class="form-control"
                                    value="{{ request('date_from') }}">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">إلى تاريخ</label>
                                <input type="date" name="date_to" class="form-control"
                                    value="{{ request('date_to') }}">
                            </div>
                            <div class="col-md-2 d-flex align-items-end">
                                <button type="submit" class="btn btn-primary w-100">
                                    <i class="fas fa-search"></i> بحث
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">
                        <i class="fas fa-list-alt me-2"></i>
                        سجل نشاطات المستخدمين
                    </h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover table-striped">
                            <thead class="table-light">
                                <tr>
                                    <th>#</th>
                                    <th>المستخدم</th>
                                    <th>وقت الدخول</th>
                                    <th>وقت الخروج</th>
                                    <th>مدة الجلسة</th>
                                    <th>IP</th>
                                    <th>الجهاز</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($activities as $activity)
                                    <tr>
                                        <td>{{ $loop->iteration }}</td>
                                        <td class="fw-bold">{{ $activity->user->name ?? 'غير معروف' }}</td>
                                        <td>
                                            @if($activity->login_at)
                                                <small>{{ $activity->login_at->format('Y-m-d H:i') }}</small>
                                            @else
                                                <small class="text-muted">N/A</small>
                                            @endif
                                        </td>
                                        <td>
                                            @if($activity->logout_at)
                                                <small>{{ $activity->logout_at->format('Y-m-d H:i') }}</small>
                                            @else
                                                <small class="text-muted">N/A</small>
                                            @endif
                                        </td>
                                        <td>
                                            @if ($activity->session_duration)
                                                <span class="badge bg-primary">
                                                    {{ $activity->session_duration }} دقيقة
                                                </span>
                                            @else
                                                -
                                            @endif
                                        </td>
                                        <td><code>{{ $activity->ip_address ?? 'N/A' }}</code></td>
                                        <td>
                                            <small class="text-muted">
                                                {{ $activity->user_agent ? Str::limit($activity->user_agent, 30) : 'N/A' }}
                                            </small>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="text-center py-4">
                                            <div class="alert alert-info mb-0">
                                                <i class="fas fa-info-circle me-2"></i>
                                                لا توجد نشاطات
                                            </div>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <div class="mt-3">
                        {{ $activities->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

