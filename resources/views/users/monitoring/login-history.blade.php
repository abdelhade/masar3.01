@extends('admin.dashboard')

@section('sidebar')
    @include('components.sidebar.permissions')
@endsection

@section('content')
    @include('components.breadcrumb', [
        'title' => __('Login History'),
        'items' => [
            ['label' => __('Home'), 'url' => route('admin.dashboard')],
            ['label' => __('Users'), 'url' => route('users.index')],
            ['label' => __('Login History')],
        ],
    ])

    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">
                        <i class="fas fa-sign-in-alt me-2"></i>
                        {{ __("Login History") }}
                    </h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <x-table-export-actions table-id="login-history-table" filename="login-history"
                            :excel-label="__('Export Excel')" :pdf-label="__('Export PDF')" :print-label="__('Print')" />

                        <table id="login-history-table" class="table table-hover table-striped">
                            <thead class="table-light">
                                <tr>
                                    <th>#</th>
                                    <th><i class="fas fa-user"></i> {{ __("User") }}</th>
                                    <th><i class="fas fa-envelope"></i> {{ __("Email") }}</th>
                                    <th><i class="fas fa-sign-in-alt"></i> {{ __("Login Time") }}</th>
                                    <th><i class="fas fa-sign-out-alt"></i> {{ __("Logout Time") }}</th>
                                    <th><i class="fas fa-clock"></i> {{ __("Session Duration") }}</th>
                                    <th><i class="fas fa-network-wired"></i> IP</th>
                                    <th><i class="fas fa-desktop"></i> {{ __("Device") }}</th>
                                    <th><i class="fas fa-map-marker-alt"></i> {{ __("Location") }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($loginSessions as $session)
                                    <tr>
                                        <td>{{ $loop->iteration }}</td>
                                        <td class="fw-bold">{{ $session->user->name ?? __('Unknown') }}</td>
                                        <td>{{ $session->user->email ?? 'N/A' }}</td>
                                        <td>
                                            @if($session->login_at)
                                                <span class="badge bg-info">
                                                    {{ $session->login_at->format('Y-m-d H:i:s') }}
                                                </span>
                                            @else
                                                <span class="text-muted">N/A</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if ($session->logout_at)
                                                <span class="badge bg-secondary">
                                                    {{ $session->logout_at->format('Y-m-d H:i:s') }}
                                                </span>
                                            @else
                                                <span class="badge bg-success">{{ __("Active Now") }}</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if ($session->session_duration)
                                                <span class="badge bg-primary">
                                                    {{ $session->session_duration }} {{ __("minutes") }}
                                                </span>
                                            @else
                                                <span class="text-muted">-</span>
                                            @endif
                                        </td>
                                        <td>
                                            <code class="text-sm">{{ $session->ip_address ?? 'N/A' }}</code>
                                        </td>
                                        <td>
                                            <small class="text-muted">
                                                {{ $session->user_agent ? Str::limit($session->user_agent, 30) : 'N/A' }}
                                            </small>
                                        </td>
                                        <td>
                                            @if ($session->location)
                                                <small>{{ $session->location }}</small>
                                            @else
                                                <span class="text-muted">-</span>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="9" class="text-center py-4">
                                            <div class="alert alert-info mb-0">
                                                <i class="fas fa-info-circle me-2"></i>
                                                {{ __("No login records") }}
                                            </div>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <div class="mt-3">
                        {{ $loginSessions->links() }}
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
    </style>
@endpush

