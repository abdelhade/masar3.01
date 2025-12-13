@extends('admin.dashboard')

@section('sidebar')
    @include('components.sidebar.permissions')
@endsection

@section('content')
    @include('components.breadcrumb', [
        'title' => __('User Details'),
        'items' => [
            ['label' => __('Home'), 'url' => route('admin.dashboard')],
            ['label' => __('Users'), 'url' => route('users.index')],
            ['label' => __('User Details')],
        ],
    ])

    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="page-title-box no-print">
                    <div class="d-flex justify-content-between align-items-center">
                        <h4 class="page-title">{{ __('User Details') }}: {{ $user->name }}</h4>
                        <div class="d-flex gap-2">
                            @can('edit Users')
                                <a href="{{ route('users.edit', $user) }}" class="btn btn-primary">
                                    <i class="fas fa-edit"></i> {{ __('Edit') }}
                                </a>
                            @endcan
                            <button onclick="window.print()" class="btn btn-info">
                                <i class="fas fa-print"></i> {{ __('Print') }}
                            </button>
                            <a href="{{ route('users.index') }}" class="btn btn-secondary">
                                <i class="fas fa-arrow-right"></i> {{ __('Back') }}
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-12">
                <div class="card printable-content">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0"><i class="fas fa-user"></i> {{ __('User Information') }}</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">{{ __('Name') }}:</label>
                                <div class="form-control-static">{{ $user->name }}</div>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">{{ __('Email') }}:</label>
                                <div class="form-control-static">{{ $user->email }}</div>
                            </div>
                        </div>

                        @if($user->branches->count() > 0)
                        <div class="row">
                            <div class="col-12 mb-3">
                                <label class="form-label fw-bold">{{ __('Branches') }}:</label>
                                <div class="form-control-static">
                                    @foreach($user->branches as $branch)
                                        <span class="badge bg-info me-1">{{ $branch->name }}</span>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                        @endif

                        @if($user->roles->count() > 0)
                        <div class="row">
                            <div class="col-12 mb-3">
                                <label class="form-label fw-bold">{{ __('Roles') }}:</label>
                                <div class="form-control-static">
                                    @foreach($user->roles as $role)
                                        <span class="badge bg-primary me-1">{{ $role->name }}</span>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                        @endif

                        @if($user->permissions->count() > 0)
                        <div class="row mt-4">
                            <div class="col-12 mb-3">
                                <label class="form-label fw-bold">{{ __('Permissions') }}:</label>
                                <div class="form-control-static">
                                    <div class="d-flex flex-wrap gap-2">
                                        @foreach($permissions as $category => $perms)
                                            <div class="mb-2">
                                                <strong>{{ $category }}:</strong>
                                                @foreach($perms as $perm)
                                                    @if($user->permissions->contains('id', $perm->id))
                                                        <span class="badge bg-success me-1">{{ $perm->name }}</span>
                                                    @endif
                                                @endforeach
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                        </div>
                        @endif

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">{{ __('Created At') }}:</label>
                                <div class="form-control-static">{{ $user->created_at ? \Carbon\Carbon::parse($user->created_at)->format('Y-m-d H:i') : __('N/A') }}</div>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">{{ __('Updated At') }}:</label>
                                <div class="form-control-static">{{ $user->updated_at ? \Carbon\Carbon::parse($user->updated_at)->format('Y-m-d H:i') : __('N/A') }}</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @push('styles')
    <style>
        .form-control-static {
            padding: 0.5rem;
            background-color: #f8f9fa;
            border: 1px solid #dee2e6;
            border-radius: 0.375rem;
            min-height: 2.5rem;
            display: flex;
            align-items: center;
        }

        @media print {
            .no-print { display: none !important; }
            .card { border: 1px solid #000 !important; box-shadow: none !important; }
            .card-header { background: #f1f1f1 !important; color: #000 !important; }
            body { font-size: 12px; }
            .form-control-static { background: #fff !important; border: 1px solid #000 !important; }
        }
    </style>
    @endpush
@endsection

