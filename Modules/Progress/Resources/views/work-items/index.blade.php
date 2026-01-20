@extends('progress::layouts.daily-progress')

{{-- Sidebar is now handled by the layout itself --}}

@section('content')
    @include('components.breadcrumb', [
        'title' => __('general.work_items_management'),
        'items' => [
            ['label' => __('general.home'), 'url' => route('admin.dashboard')],
            ['label' => __('general.work_items_management')],
        ],
    ])

    <div class="row">
        <div class="col-lg-12">

            {{-- زرار الإضافة --}}
           @can('create progress-work-items')
            <a href="{{ route('work.items.create') }}" type="button" class="btn btn-primary font-hold fw-bold">
                {{ __('general.add_new_work_item') }}
                <i class="fas fa-plus me-2"></i>
            </a>
            @endcan 
            
            <form action="{{ route('work.items.index') }}" method="GET" class="mt-4 mb-4">
                <div class="row">
                    <!-- Search Filter -->
                    <div class="col-md-6">
                        <div class="input-group">
                            <input type="text" name="search" class="form-control" placeholder="Search by name, category, unit..." value="{{ request('search') }}">
                            <button type="submit" class="btn btn-secondary">
                                <i class="las la-search"></i>
                            </button>
                        </div>
                    </div>

                    <!-- Category Filter -->
                    <div class="col-md-4">
                        <select name="category_id" class="form-control" onchange="this.form.submit()">
                            <option value="">-- Filter by Category --</option>
                            @foreach($categories as $category)
                                <option value="{{ $category->id }}" {{ request('category_id') == $category->id ? 'selected' : '' }}>
                                    {{ $category->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Items Per Page -->
                    <div class="col-md-2">
                        <select name="per_page" class="form-control p-1" onchange="this.form.submit()">
                            <option value="100" {{ request('per_page') == 100 ? 'selected' : '' }}>100</option>
                            <option value="200" {{ request('per_page') == 200 ? 'selected' : '' }}>200</option>
                            <option value="500" {{ request('per_page') == 500 ? 'selected' : '' }}>500</option>
                        </select>
                    </div>
                </div>
            </form>
            {{-- الجدول --}}
            <div class="card">
                <div class="card-body">
                    <div class="table-responsive" style="overflow-x: auto;">

                     
                        <table id="project-types-table" class="table table-striped mb-0" style="min-width: 800px;">
                            <thead class="table-light align-middle text-start">
                                <tr>
                                    <th style="width: 50px;"></th>
                                    <th>#</th>
                                    <th>{{ __('general.item_name') }}</th>
                                    <th>{{ __('general.category') }}</th>
                                    <th>{{ __('general.unit_of_measurement') }}</th>
                                     @canany(['edit progress-work-items', 'delete progress-work-items'])
                                    <th>{{ __('general.actions') }}</th>
                                    @endcanany 
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($workItems as $workItem)
                                    <tr class="align-middle text-start" data-id="{{ $workItem->id }}">
                                        <td class="drag-handle" style="cursor: move; color: #888;">
                                            <i class="las la-arrows-alt fa-lg"></i>
                                        </td>
                                        <td>{{ $loop->iteration }}</td>
                                        <td>{{ $workItem->name }}</td>
                                        <td>{{ $workItem->category?->name ?? '---' }}</td>
                                        <td>{{ $workItem->unit }}</td>
                                         @canany(['edit progress-work-items', 'delete progress-work-items'])
                                        <td>
                                            @can('edit progress-work-items')
                                            <a class="btn btn-success btn-icon-square-sm"
                                                href="{{ route('work.items.edit', $workItem->id) }}">
                                                <i class="las la-edit"></i>
                                            </a>
                                            @endcan
                                                @can('delete progress-work-items') 
                                            <form action="{{ route('work.items.destroy', $workItem->id) }}" method="POST"
                                                style="display:inline-block;"
                                                onsubmit="return confirm('{{ __('general.confirm_delete') }}');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-danger btn-icon-square-sm">
                                                    <i class="las la-trash"></i>
                                                </button>
                                            </form>
                                              @endcan 
                                        </td>
                                        @endcanany                 
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="10" class="text-center">
                                            <div class="alert alert-info py-3 mb-0"
                                                style="font-size: 1.2rem; font-weight: 500;">
                                                <i class="las la-info-circle me-2"></i>
                                                {{ __('general.no_project_types') }}
                                            </div>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>

                    </div>
                    <!-- Pagination -->
                    <div class="d-flex justify-content-center mt-3">
                        {{ $workItems->appends(request()->query())->links() }}
                    </div>
                </div>
            </div>

        </div>
    </div>
@endsection

@section('script')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Sortable/1.14.0/Sortable.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            var el = document.querySelector('tbody');
            var sortable = Sortable.create(el, {
                animation: 150,
                handle: '.drag-handle',
                onEnd: function () {
                    var ids = [];
                    document.querySelectorAll('tbody tr').forEach(function (row) {
                        ids.push(row.getAttribute('data-id'));
                    });

                    fetch('{{ route('work.items.reorder') }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        body: JSON.stringify({ ids: ids })
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            // Optional: Show toast
                            // console.log('Order updated');
                        } else {
                            alert('Something went wrong!');
                        }
                    })
                    .catch(error => console.error('Error:', error));
                }
            });
        });
    </script>
@endsection
