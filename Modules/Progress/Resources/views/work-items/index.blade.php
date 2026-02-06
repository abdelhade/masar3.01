@extends('progress::layouts.app')
@section('breadcrumb')
    <li class="breadcrumb-item">
        <a href="{{ route('progress.dashboard') }}" class="text-muted text-decoration-none">
            {{ __('general.dashboard') }}
        </a>
    </li>
    <li class="breadcrumb-item active">{{ __('general.work_items') }}</li>
@endsection
@section('title', __('general.work_items'))

@section('content')
    @php
        $itemsData = $workItems->map(function ($item) {
            return [
                'id' => $item->id,
                'name' => $item->name,
                'unit' => $item->unit,
                'total_quantity' => $item->total_quantity ?? 0,
                'daily_quantity' => $item->expected_quantity_per_day ?? 0,
                'duration' => $item->duration ?? 0,
                'shift' => $item->shift ?? 'morning',
                'status' => $item->status ?? 'not_started',
                'progress' => $item->progress ?? 0,
                'predecessor' => $item->predecessor ? $item->predecessor->name : null,
                'start_date' => $item->start_date ?? now()->format('Y-m-d'),
                'end_date' =>
                    $item->end_date ??
                    now()
                        ->addDays($item->duration ?? 0)
                        ->format('Y-m-d'),
                'description' => $item->description,
            ];
        });
    @endphp

    <style>
        :root {
            --primary-color: #2c7be5;
            --secondary-color: #6c757d;
            --success-color: #28a745;
            --warning-color: #ffc107;
            --danger-color: #dc3545;
            --card-shadow: 0 0.5rem 1.5rem rgba(0, 0, 0, 0.08);
        }

    


        .pagination .page-link {
            padding: 0.4rem 0.65rem;
            font-size: 0.875rem;
            border-radius: 0.25rem;
            margin: 0 0.125rem;
            min-width: 35px;
            text-align: center;
        }

        .pagination .page-item:first-child .page-link,
        .pagination .page-item:last-child .page-link {
            font-size: 0.75rem;
            padding: 0.4rem 0.5rem;
        }

        .pagination .page-item.active .page-link {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
        }

        .pagination .page-link:hover {
            background-color: #e9ecef;
        }

        
        .pagination .page-item:first-child .page-link svg,
        .pagination .page-item:last-child .page-link svg {
            display: none !important;
            
        }

        .pagination .page-item:first-child .page-link,
        .pagination .page-item:last-child .page-link {
            font-size: 0 !important;
            position: relative;
            width: 35px;
            height: 35px;
        }

        .pagination .page-item:first-child .page-link::after {
            content: "‹";
            font-size: 0.1rem;
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            color: currentColor;
        }

        .pagination .page-item:last-child .page-link::after {
            content: "›";
            font-size: 0.9rem;
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            color: currentColor;
        }

        .pagination .page-item.disabled .page-link::after {
            opacity: 0.5;
        }

        .view-toggle {
            background: #f8f9fa;
            border-radius: 0.5rem;
            padding: 0.25rem;
            display: inline-flex;
        }

        .view-toggle button {
            border: none;
            background: transparent;
            padding: 0.5rem 1rem;
            border-radius: 0.375rem;
            transition: all 0.3s ease;
            font-weight: 500;
        }

        .view-toggle button.active {
            background: var(--primary-color);
            color: white;
            box-shadow: 0 2px 4px rgba(44, 123, 229, 0.3);
        }

        .gantt-container {
            background: white;
            border-radius: 0.75rem;
            box-shadow: var(--card-shadow);
            overflow: hidden;
        }

        .table-container {
            background: white;
            border-radius: 0.75rem;
            box-shadow: var(--card-shadow);
            overflow: hidden;
        }

        .status-badge {
            padding: 0.25rem 0.75rem;
            border-radius: 1rem;
            font-size: 0.75rem;
            font-weight: 600;
            text-transform: uppercase;
        }

        .status-not-started {
            background: #f8f9fa;
            color: #6c757d;
        }

        .status-pending {
            background: #fff3cd;
            color: #856404;
        }

        .status-in-progress {
            background: #d1ecf1;
            color: #0c5460;
        }

        .status-completed {
            background: #d4edda;
            color: #155724;
        }

        .progress-bar-container {
            height: 0.5rem;
            background: #e9ecef;
            border-radius: 0.25rem;
            overflow: hidden;
        }

        .progress-bar {
            height: 100%;
            background: linear-gradient(90deg, #28a745 0%, #20c997 100%);
            transition: width 0.3s ease;
        }

        .action-btn {
            padding: 0.375rem 0.75rem;
            border-radius: 0.375rem;
            border: 1px solid #dee2e6;
            background: white;
            color: #6c757d;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 0.25rem;
            font-size: 0.875rem;
        }

        .action-btn:hover {
            background: #f8f9fa;
            border-color: #adb5bd;
            color: #495057;
            text-decoration: none;
        }

        .btn-edit:hover {
            border-color: #ffc107;
            color: #ffc107;
        }

        .btn-delete:hover {
            border-color: #dc3545;
            color: #dc3545;
        }

        .btn-view:hover {
            border-color: #17a2b8;
            color: #17a2b8;
        }

        .stats-card {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border-radius: 1rem;
            padding: 1.5rem;
            margin-bottom: 1.5rem;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1rem;
        }

        .stat-item {
            text-align: center;
        }

        .stat-number {
            font-size: 2rem;
            font-weight: 700;
            margin-bottom: 0.25rem;
        }

        .stat-label {
            font-size: 0.875rem;
            opacity: 0.9;
        }

        .filters-panel {
            background: #f8f9fa;
            border-radius: 0.75rem;
            padding: 1rem;
            margin-bottom: 1.5rem;
        }

        .export-dropdown {
            position: relative;
            display: inline-block;
        }

        .export-menu {
            position: absolute;
            top: 100%;
            right: 0;
            background: white;
            border: 1px solid #dee2e6;
            border-radius: 0.375rem;
            box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
            z-index: 1000;
            min-width: 200px;
            display: none;
        }

        .export-menu.show {
            display: block;
        }

        .export-menu a {
            display: block;
            padding: 0.75rem 1rem;
            color: #495057;
            text-decoration: none;
            transition: background 0.3s ease;
        }

        .export-menu a:hover {
            background: #f8f9fa;
        }
    </style>

    <div class="container-fluid">
        
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h2 class="mb-0 text-dark fw-bold">{{ __('general.work_items') }}</h2>
                <p class="text-muted mb-0">{{ __('general.manage_work_items_description') }}</p>
            </div>

            <div class="d-flex gap-3 align-items-center">



                @can('create progress-work-items')
                    <a href="{{ route('progress.work-items.create') }}" class="btn btn-primary">
                        <i class="fas fa-plus me-1"></i> {{ __('general.add_new') }}
                    </a>
                @endcan

            </div>
        </div>

        
        <div class="card mb-3">
            <div class="card-body">
                <form action="{{ route('progress.work-items.index') }}" method="GET" class="row g-3">
                    <div class="col-md-4">
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-search"></i></span>
                            <input type="text" name="search" class="form-control"
                                placeholder="{{ __('general.search_by_name_unit_category') }}"
                                value="{{ request('search') }}">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-search"></i> {{ __('general.search') }}
                            </button>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-folder"></i></span>
                            <select name="category_id" class="form-select" onchange="this.form.submit()">
                                <option value="">{{ __('general.all_categories') }}</option>
                                @foreach($categories as $category)
                                    <option value="{{ $category->id }}" {{ request('category_id') == $category->id ? 'selected' : '' }}>
                                        {{ $category->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-list"></i></span>
                            <select name="per_page" class="form-select" onchange="this.form.submit()">
                                <option value="100" {{ request('per_page', 100) == 100 ? 'selected' : '' }}>100
                                    {{ __('general.items_per_page') }}</option>
                                <option value="200" {{ request('per_page') == 200 ? 'selected' : '' }}>200
                                    {{ __('general.items_per_page') }}</option>
                                <option value="500" {{ request('per_page') == 500 ? 'selected' : '' }}>500
                                    {{ __('general.items_per_page') }}</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="d-flex align-items-center justify-content-between">
                            <div class="text-muted">
                                <i class="fas fa-info-circle"></i>
                                {{ __('general.showing') }} {{ $workItems->firstItem() ?? 0 }} -
                                {{ $workItems->lastItem() ?? 0 }}
                                {{ __('general.of') }} {{ $workItems->total() }}
                            </div>
                            @if (request('search') || request('category_id'))
                                <a href="{{ route('progress.work-items.index') }}" class="btn btn-sm btn-secondary">
                                    <i class="fas fa-times"></i> {{ __('general.clear') }}
                                </a>
                            @endif
                        </div>
                    </div>
                </form>
            </div>
        </div>

        
        <div id="tableView" class="table-container">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-primary">
                        <tr>
                            <th>{{ __('general.item_name') }}</th>
                            <th>{{ __('general.category') }}</th>
                            <th>{{ __('general.unit') }}</th>
                            @canany(['items-edit', 'items-delete'])
                                <th>{{ __('general.actions') }}</th>
                            @endcanany
                        </tr>
                    </thead>
                    <tbody id="itemsTableBody">
                        @foreach ($workItems as $item)
                            <tr class="item-row"
                                data-id="{{ $item->id }} data-status="{{ $item->status ?? 'not_started' }}"
                                data-shift="{{ $item->shift ?? 'morning' }}">
                                
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="me-3">
                                            <i class="fas fa-tasks text-primary"></i>
                                        </div>
                                        <div>
                                            <div class="fw-semibold">{{ $item->name }}</div>
                                            @if ($item->description)
                                                <small class="text-muted">{{ Str::limit($item->description, 50) }}</small>
                                            @endif
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <span class="badge bg-light text-dark">
                                        {{ $item->category->name ?? __('general.uncategorized') }}
                                    </span>
                                </td>

                                
                                <td>
                                    <span class="badge bg-light text-dark">{{ $item->unit }}</span>
                                </td>

                                
                                @canany(['items-edit', 'items-delete'])
                                    <td>
                                        <div class="d-flex gap-1">
                                            @can('edit progress-work-items')
                                                <a href="{{ route('progress.work-items.edit', $item->id) }}" class="action-btn btn-edit"
                                                    title="تعديل">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                            @endcan
                                            @can('delete progress-work-items')
                                                <form action="{{ route('progress.work-items.destroy', $item->id) }}" method="POST"
                                                    class="d-inline" onsubmit="return confirm('هل أنت متأكد من الحذف؟')">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="action-btn btn-delete" title="حذف">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </form>
                                            @endcan
                                        </div>
                                    </td>
                                @endcanany
                            </tr>
                        @endforeach
                    </tbody>
                </table>

            </div>

            @if ($workItems->isEmpty())
                <div class="text-center py-5">
                    <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                    <h5 class="text-muted">{{ __('general.no_work_items_found') }}</h5>
                    <p class="text-muted">{{ __('general.start_by_adding_work_item') }}</p>
                    @can('create progress-work-items')
                        <a href="{{ route('progress.work-items.create') }}" class="btn btn-primary">
                            <i class="fas fa-plus me-1"></i> {{ __('general.add_new_work_item') }}
                        </a>
                    @endcan
                </div>
            @endif
        </div>

        
        @if ($workItems->hasPages())
            <div class="d-flex flex-column flex-md-row justify-content-between align-items-center mt-4 small">
                <div class="text-muted mb-2 mb-md-0">
                    {{ __('general.showing') }}
                    <strong>{{ $workItems->firstItem() }}</strong> -
                    <strong>{{ $workItems->lastItem() }}</strong>
                    {{ __('general.of') }}
                    <strong>{{ $workItems->total() }}</strong>
                    {{ __('general.results') }}
                </div>

                <div class="pagination-wrapper">
                    {{ $workItems->links('pagination::bootstrap-5') }}
                </div>
            </div>
        @endif


        <script>
            document.addEventListener('DOMContentLoaded', function() {
                const tableBody = document.getElementById('itemsTableBody');

                new Sortable(tableBody, {
                    animation: 150,
                    handle: '.item-row',
                    ghostClass: 'table-active',
                    onEnd: function(evt) {
                        const order = [];
                        document.querySelectorAll('.item-row').forEach((row, index) => {
                            order.push({
                                id: row.getAttribute('data-id'),
                                position: index + 1
                            });
                        });

                        fetch("{{ route('progress.work-items.reorder') }}", {
                                method: "POST",
                                headers: {
                                    "Content-Type": "application/json",
                                    "X-CSRF-TOKEN": "{{ csrf_token() }}"
                                },
                                body: JSON.stringify({
                                    order
                                })
                            })
                            .then(res => res.json())
                            .then(data => {
                                if (data.success) {
                                    console.log("Order updated!");
                                }
                            })
                            .catch(err => console.error(err));
                    }
                });
            });
        </script>



    @endsection
