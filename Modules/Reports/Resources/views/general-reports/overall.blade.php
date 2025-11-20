@extends('admin.dashboard')

@section('sidebar')
    @include('components.sidebar.reports')
@endsection

@section('content')
<div class="container">
    <div class="card">
        <div class="card-head">
            <h2>{{ __('reports.daily_activity_analyzer') }}</h2>
            <div class="alert alert-info mb-3">
                <i class="las la-info-circle"></i> 
                {{ __('reports.total_operations_registered') }}: <strong>{{ $opers->total() }}</strong>
            </div>
        <form method="GET" class="row g-3 align-items-end mb-3" style="font-family: 'Cairo', sans-serif; direction: rtl;">
            <div class="col-md-3">
                <label for="user_id" class="form-label">{{ __('reports.user') }}</label>
                <select name="user_id" id="user_id" class="form-control">
                    <option value="">{{ __('reports.all_users') }}</option>
                    @foreach($users as $user)
                        <option value="{{ $user->id }}" @if(request('user_id') == $user->id) selected @endif>
                            {{ $user->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <label for="type_id" class="form-label">{{ __('reports.operation_type') }}</label>
                <select name="type_id" id="type_id" class="form-control">
                    <option value="">{{ __('reports.all_operations') }}</option>
                    @foreach($types as $type)
                        <option value="{{ $type->id }}" @if(request('type_id') == $type->id) selected @endif>
                            {{ $type->ptext }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <label for="date_from" class="form-label">{{ __('reports.from_date') }}</label>
                <input type="date" name="date_from" id="date_from" class="form-control" value="{{ request('date_from') ?? now()->startOfDay()->format('Y-m-d') }}">
            </div>
            <div class="col-md-3">
                <label for="date_to" class="form-label">{{ __('reports.to_date') }}</label>
                <input type="date" name="date_to" id="date_to" class="form-control" value="{{ request('date_to') ?? now()->endOfDay()->format('Y-m-d') }}">
            </div>
            <div class="col-md-12 text-end mt-2">
                <button type="submit" class="btn btn-primary">
                    <i class="las la-filter"></i> {{ __('reports.filtering') }}
                </button>
                <a href="{{ route('reports.overall') }}" class="btn btn-secondary">
                    <i class="las la-redo"></i> {{ __('reports.reset') }}
                </a>
            </div>
        </form>
        </div>
        <div class="card-body">
            <div class="table">
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>{{ __('reports.date') }}</th>
                            <th>{{ __('reports.time') }}</th>
                            <th>{{ __('reports.user') }}</th>
                            <th>{{ __('reports.operation_name') }}</th>
                            <th>{{ __('reports.description') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($opers as $log)
                        <tr>
                            <td>{{ $log->created_at->format('Y-m-d') }}</td>
                            <td>{{ $log->created_at->format('H:i') }}</td>
                            <td>{{ \App\Models\User::where('id', $log->user)->first()->name ?? '---' }}</td>
                            <td>
                                @if($log->pro_type && $log->id)
                                    @php
                                        $operationType = $log->type->ptext ?? __('reports.unspecified');
                                        $editRoute = $log->getEditRoute();
                                    @endphp

                                    @if(\Illuminate\Support\Facades\Route::has($editRoute))
                                        <a href="{{ route($editRoute, $log->id) }}"
                                           class="text-decoration-underline text-primary"
                                           title="{{ __('reports.edit_operation') }}"
                                           style="font-family: 'Cairo', sans-serif; direction: rtl;">
                                           {{ $operationType }}
                                        </a>
                                    @else
                                        <span class="text-muted"
                                              title="{{ __('reports.cannot_edit_operation_type') }}"
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
                            <td colspan="5" class="text-center">{{ __('reports.no_data_available') }}</td>
                        </tr>
                        @endforelse
                    </tbody>

                </table>
            </div>

            <!-- Pagination Links -->
            <div class="d-flex justify-content-center mt-4">
                {{ $opers->appends(request()->query())->links() }}
            </div>
        </div>
    </div>
</div>

@endsection
