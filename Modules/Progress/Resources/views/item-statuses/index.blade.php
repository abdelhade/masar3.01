@extends('progress::layouts.daily-progress')
{{-- Sidebar is now handled by the layout itself --}}
@section('title', 'Item Statuses')

@section('content')
<div class="row mb-3">
    <div class="col-12 d-flex align-items-center justify-content-between">
        <div class="d-flex align-items-center">
       
            <h4 class="mb-0  fw-bold">Item Statuses</h4>
        </div>
        <div>
            @can('create progress-item-statuses')
            <a href="{{ route('item-statuses.create') }}" class="btn btn-success fw-bold rounded-pill px-4">
                <i class="las la-plus me-1"></i> Add Item Status
            </a>
            @endcan
        </div>
    </div>
</div>

<div class="row">
    <div class="col-lg-12">
        <div class="card shadow-sm border-0">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0" style="border-collapse: separate; border-spacing: 0 10px;">
                        <thead class="table-light">
                            <tr>
                                <th scope="col" class="ps-4">#</th>
                                <th scope="col">Name</th>
                                <th scope="col" class="text-center">Color</th>
                                <th scope="col" class="text-center">Icon</th>
                                <th scope="col" class="text-center">Order</th>
                                <th scope="col" class="text-center">Status</th>
                                <th scope="col" class="text-end pe-4">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($statuses as $status)
                            <tr>
                                <td class="ps-4 fw-bold text-muted">{{ $loop->iteration }}</td>
                                <td>
                                    <div class="d-flex flex-column">
                                        <span class="fw-bold fs-15 text-dark">{{ $status->name }}</span>
                                        @if($status->description)
                                            <span class="text-muted small">{{ Str::limit($status->description, 50) }}</span>
                                        @endif
                                    </div>
                                </td>
                                <td class="text-center">
                                    @php
                                        $isHex = Str::startsWith($status->color, '#');
                                        // Simple contrast check for hex/bootstrap
                                        $textColor = 'white'; 
                                        if($status->color == 'warning' || $status->color == 'light' || $status->color == 'yellow') {
                                            $textColor = 'dark';
                                        }
                                    @endphp
                                    
                                    @if($isHex)
                                        <span class="badge rounded-pill" 
                                              style="background-color: {{ $status->color }}; color: {{ $textColor }}; min-width: 80px;">
                                            {{ $status->color }}
                                        </span>
                                    @else
                                        <span class="badge rounded-pill bg-{{ $status->color }} text-{{ $textColor }}" style="min-width: 80px;">
                                            {{ $status->color }}
                                        </span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    @if($status->icon)
                                        <i class="{{ $status->icon }} fs-3" 
                                           style="color: {{ $isHex ? $status->color : 'inherit' }};"></i>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td class="text-center fw-medium">{{ $status->order }}</td>
                                <td class="text-center">
                                    @if($status->is_active)
                                        <span class="badge bg-success rounded-pill px-3">Active</span>
                                    @else
                                        <span class="badge bg-danger rounded-pill px-3">Inactive</span>
                                    @endif
                                </td>
                                <td class="text-end pe-4">
                                    <div class="d-inline-flex gap-2">
                                        @can('edit progress-item-statuses')
                                        <a href="{{ route('item-statuses.edit', $status->id) }}" class="btn btn-sm btn-outline-primary rounded-pill px-3">
                                            <i class="las la-pen me-1"></i> Edit
                                        </a>
                                        @endcan
                                        @can('delete progress-item-statuses')
                                        <form action="{{ route('item-statuses.destroy', $status->id) }}" method="POST" class="d-inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-outline-danger rounded-pill px-3" onclick="return confirm('Are you sure?')">
                                                <i class="las la-trash me-1"></i> Delete
                                            </button>
                                        </form>
                                        @endcan
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="7" class="text-center py-5 text-muted">
                                    <i class="las la-inbox fs-1 mb-3 d-block"></i>
                                    No item statuses found.
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
