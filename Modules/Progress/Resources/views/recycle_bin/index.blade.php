@extends('progress::layouts.daily-progress')

@section('content')
<div class="containers-fluid p-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="h4 text-primary">{{ __('progress::dashboard.recycle_bin') }}</h2>
    </div>

    <!-- Tabs -->
    <ul class="nav nav-tabs mb-4">
        <li class="nav-item">
            <a class="nav-link {{ $tab == 'projects' ? 'active' : '' }}" href="{{ route('progress.recycle_bin.index', ['tab' => 'projects']) }}">{{ __('progress::dashboard.projects') }}</a>
        </li>
        <li class="nav-item">
            <a class="nav-link {{ $tab == 'issues' ? 'active' : '' }}" href="{{ route('progress.recycle_bin.index', ['tab' => 'issues']) }}">{{ __('progress::dashboard.issues') }}</a>
        </li>
        <li class="nav-item">
            <a class="nav-link {{ $tab == 'daily_progress' ? 'active' : '' }}" href="{{ route('progress.recycle_bin.index', ['tab' => 'daily_progress']) }}">{{ __('progress::dashboard.daily_progress') }}</a>
        </li>
        <li class="nav-item">
            <a class="nav-link {{ $tab == 'project_types' ? 'active' : '' }}" href="{{ route('progress.recycle_bin.index', ['tab' => 'project_types']) }}">{{ __('progress::dashboard.project_types') }}</a>
        </li>
        <li class="nav-item">
            <a class="nav-link {{ $tab == 'project_templates' ? 'active' : '' }}" href="{{ route('progress.recycle_bin.index', ['tab' => 'project_templates']) }}">{{ __('progress::dashboard.project_templates') }}</a>
        </li>
        <li class="nav-item">
            <a class="nav-link {{ $tab == 'work_items' ? 'active' : '' }}" href="{{ route('progress.recycle_bin.index', ['tab' => 'work_items']) }}">{{ __('progress::dashboard.work_items') }}</a>
        </li>
        <li class="nav-item">
            <a class="nav-link {{ $tab == 'categories' ? 'active' : '' }}" href="{{ route('progress.recycle_bin.index', ['tab' => 'categories']) }}">{{ __('progress::dashboard.categories') }}</a>
        </li>
        <li class="nav-item">
            <a class="nav-link {{ $tab == 'statuses' ? 'active' : '' }}" href="{{ route('progress.recycle_bin.index', ['tab' => 'statuses']) }}">{{ __('progress::dashboard.statuses') }}</a>
        </li>
        <li class="nav-item">
            <a class="nav-link {{ $tab == 'subprojects' ? 'active' : '' }}" href="{{ route('progress.recycle_bin.index', ['tab' => 'subprojects']) }}">{{ __('progress::dashboard.subprojects') }}</a>
        </li>
    </ul>

    <!-- Table -->
    <div class="card border-0 shadow-sm">
        <div class="card-body">
            @if(session('success'))
                <div class="alert alert-success">{{ session('success') }}</div>
            @endif
            @if(session('error'))
                <div class="alert alert-danger">{{ session('error') }}</div>
            @endif

            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="bg-light">
                        <tr>
                            <th>{{ __('progress::dashboard.id') }}</th>
                            <th>{{ __('progress::dashboard.name_title') }}</th>
                            <th>{{ __('progress::dashboard.deleted_at') }}</th>
                            <th class="text-end">{{ __('progress::dashboard.actions') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($items as $item)
                            <tr>
                                <td>{{ $item->id }}</td>
                                <td>{{ $item->name ?? $item->title ?? $item->id }}</td>
                                <td>{{ $item->deleted_at->format('Y-m-d H:i') }}</td>
                                <td class="text-end">
                                    <form action="{{ route('progress.recycle_bin.restore', ['type' => $tab, 'id' => $item->id]) }}" method="POST" class="d-inline">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-success" title="{{ __('progress::dashboard.restore') }}">
                                            <i class="las la-trash-restore"></i> {{ __('progress::dashboard.restore') }}
                                        </button>
                                    </form>
                                    <form action="{{ route('progress.recycle_bin.force_delete', ['type' => $tab, 'id' => $item->id]) }}" method="POST" class="d-inline" onsubmit="return confirm('{{ __('progress::dashboard.confirm_delete') }}');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-danger" title="{{ __('progress::dashboard.delete_forever') }}">
                                            <i class="las la-trash"></i> {{ __('progress::dashboard.delete_forever') }}
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="text-center py-4 text-muted">{{ __('progress::dashboard.no_deleted_items') }}</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($items->hasPages())
                <div class="mt-3">
                    {{ $items->links() }}
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
