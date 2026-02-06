@extends('admin.dashboard')

@section('sidebar')
    @include('components.sidebar.reports')
@endsection

@section('content')
    <div class="container">
        <div class="card">
            <div class="card-header">
                <h2>{{ __('Daily Activity Analyzer') }}</h2>
                <div class="alert alert-info mb-3">
                    <i class="fas fa-info-circle"></i>
                    {{ __('Total Operations Registered') }}: <strong>{{ $opers->total() }}</strong>
                </div>
            </div>

            <div class="card-body">
                <form method="GET" class="row g-3 align-items-end mb-3"
                    style="font-family: 'Cairo', sans-serif; direction: rtl;">
                    <div class="col-md-3">
                        <label for="user_id" class="form-label">{{ __('User') }}</label>
                        <select name="user_id" id="user_id" class="form-select">
                            <option value="">{{ __('All Users') }}</option>
                            @foreach ($users as $user)
                                <option value="{{ $user->id }}" {{ request('user_id') == $user->id ? 'selected' : '' }}>
                                    {{ $user->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-3">
                        <label for="type_id" class="form-label">{{ __('Operation Type') }}</label>
                        <select name="type_id" id="type_id" class="form-select">
                            <option value="">{{ __('All Operations') }}</option>
                            @foreach ($types as $type)
                                <option value="{{ $type->id }}"
                                    {{ request('type_id') == $type->id ? 'selected' : '' }}>
                                    {{ $type->ptext }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-3">
                        <label for="date_from" class="form-label">{{ __('From Date') }}</label>
                        <input type="date" name="date_from" id="date_from" class="form-control"
                            value="{{ request('date_from') ?? now()->startOfDay()->format('Y-m-d') }}">
                    </div>

                    <div class="col-md-3">
                        <label for="date_to" class="form-label">{{ __('To Date') }}</label>
                        <input type="date" name="date_to" id="date_to" class="form-control"
                            value="{{ request('date_to') ?? now()->format('Y-m-d') }}">
                    </div>

                    <div class="col-md-12 text-end mt-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-filter"></i> {{ __('Filter') }}
                        </button>
                        <a href="{{ route('reports.overall') }}" class="btn btn-secondary">
                            <i class="fas fa-redo"></i> {{ __('Reset') }}
                        </a>
                    </div>
                </form>

                <div class="table-responsive">
                    <table class="table table-bordered table-hover">
                        <thead>
                            <tr>
                                <th>{{ __('Date') }}</th>
                                <th>{{ __('Time') }}</th>
                                <th>{{ __('User') }}</th>
                                <th>{{ __('Operation Name') }}</th>
                                <th>{{ __('Description') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($opers as $log)
                                <tr>
                                    <td>{{ $log->created_at->format('Y-m-d') }}</td>
                                    <td>{{ $log->created_at->format('H:i') }}</td>
                                    <td>{{ \App\Models\User::where('id', $log->user)->first()?->name ?? '---' }}</td>
                                    <td>
                                        @if ($log->pro_type && $log->id)
                                            @php
                                                $operationType = $log->type->ptext ?? __('Unspecified');
                                                $editRoute = $log->getEditRoute();
                                            @endphp

                                            @if (\Illuminate\Support\Facades\Route::has($editRoute))
                                                <a href="{{ route($editRoute, $log->id) }}"
                                                    class="text-decoration-underline text-primary fw-bold"
                                                    title="{{ __('Edit Operation') }}"
                                                    style="font-family: 'Cairo', sans-serif; direction: rtl;">
                                                    {{ $operationType }}
                                                </a>
                                            @else
                                                <span class="text-muted" title="{{ __('Cannot Edit Operation Type') }}"
                                                    style="font-family: 'Cairo', sans-serif; direction: rtl;">
                                                    {{ $operationType }}
                                                </span>
                                            @endif
                                        @else
                                            {{ $log->type->ptext ?? '---' }}
                                        @endif
                                    </td>
                                    <td>{{ $log->details ?? '---' }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center py-4">
                                        <i class="fas fa-inbox text-muted fs-1 mb-3 d-block"></i>
                                        {{ __('No Data Available') }}
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <!-- Pagination Links -->
                @if ($opers->hasPages())
                    <div class="d-flex justify-content-center mt-4">
                        {{ $opers->appends(request()->query())->links() }}
                    </div>
                @endif
            </div>
        </div>
    </div>
@endsection
