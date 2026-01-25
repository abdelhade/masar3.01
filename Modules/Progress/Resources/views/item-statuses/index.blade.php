@extends('progress::layouts.daily-progress')
{{-- Sidebar is now handled by the layout itself --}}
@section('title', __('general.item_statuses'))

@section('content')
<div class="row mb-3">
    <div class="col-12 d-flex align-items-center justify-content-between">
        <div class="d-flex align-items-center">
       
            <h4 class="mb-0  fw-bold">{{ __('general.item_statuses') }}</h4>
        </div>
        <div>
            @can('create progress-item-statuses')
            <a href="{{ route('item-statuses.create') }}" class="btn btn-success fw-bold rounded-pill px-4">
                <i class="las la-plus me-1"></i> {{ __('general.add_item_status') }}
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
                                <th scope="col">#</th>
                                <th scope="col">{{ __('general.name') }}</th>
                                <th scope="col" class="text-center">{{ __('general.color') }}</th>
                                <th scope="col" class="text-center">{{ __('general.icon') }}</th>
                                <th scope="col" class="text-center">{{ __('general.order') }}</th>
                                <th scope="col" class="text-center">{{ __('general.status') }}</th>
                                @canany(['edit progress-item-statuses' ,'delete progress-item-statuses'])
                                <th scope="col" class="text-end pe-4">{{ __('general.actions') }}</th>
                                @endcanany
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
                                        // Check if it's a standard bootstrap class or custom named color that maps to a class
                                        // The user wants "CSS colors", so we prioritize style attribute if it's not a known bootstrap utility
                                        $bootstrapColors = ['primary', 'secondary', 'success', 'danger', 'warning', 'info', 'light', 'dark', 'white', 'black'];
                                        $isBootstrap = in_array($status->color, $bootstrapColors);
                                        
                                        // Simple contrast check
                                        $textColor = 'white'; 
                                        if(in_array($status->color, ['warning', 'light', 'yellow', 'white', '#ffffff', '#fff'])) {
                                            $textColor = 'dark';
                                        }
                                    @endphp
                                    
                                    @if($isBootstrap)
                                        <span class="badge rounded-pill bg-{{ $status->color }} text-{{ $textColor }}" style="min-width: 80px;">
                                            {{ $status->color }}
                                        </span>
                                    @else
                                        {{-- CSS Color (Hex, RGB, Name) --}}
                                        <span class="badge rounded-pill" 
                                              style="background-color: {{ $status->color }}; color: {{ $textColor }}; min-width: 80px; text-shadow: 0 0 2px rgba(0,0,0,0.3);">
                                            {{ $status->color }}
                                        </span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    @if($status->icon)
                                        @if(Str::startsWith($status->icon, ['las', 'la-', 'fa-', 'fas', 'fab', 'bi-']))
                                            <i class="{{ $status->icon }} fs-3" 
                                               style="color: {{ !$isBootstrap ? $status->color : 'inherit' }};"></i>
                                        @else
                                            {{-- Emoji --}}
                                            <span class="fs-3">{{ $status->icon }}</span>
                                        @endif
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td class="text-center fw-medium">{{ $status->order }}</td>
                                <td class="text-center">
                                    @if($status->is_active)
                                        <span class="badge bg-success rounded-pill px-3">{{ __('general.status_active') }}</span>
                                    @else
                                        <span class="badge bg-danger rounded-pill px-3">{{ __('general.status_inactive') }}</span>
                                    @endif
                                </td>
                                <td class="text-end pe-4">
                                    <div class="d-inline-flex gap-2">
                                        @can('edit progress-item-statuses')
                                        <a href="{{ route('item-statuses.edit', $status->id) }}" class="btn btn-sm btn-outline-primary rounded-pill px-3">
                                            <i class="las la-pen me-1"></i> {{ __('general.edit') }}
                                        </a>
                                        @endcan
                                        @can('delete progress-item-statuses')
                                        <form action="{{ route('item-statuses.destroy', $status->id) }}" method="POST" class="d-inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-outline-danger rounded-pill px-3" onclick="return confirm('{{ __('general.are_you_sure') }}')">
                                                <i class="las la-trash me-1"></i> {{ __('general.delete') }}
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
                                    {{ __('general.no_item_statuses_found') }}
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
