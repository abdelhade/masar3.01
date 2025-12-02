@extends('admin.dashboard')

@section('sidebar')
    @include('components.sidebar.crm')
@endsection

@section('content')
    @include('components.breadcrumb', [
        'title' => __('Tickets'),
        'items' => [['label' => __('Dashboard'), 'url' => route('admin.dashboard')], ['label' => __('Tickets')]],
    ])

    <div class="row">
        <div class="col-lg-12">
            <div class="d-flex justify-content-between align-items-center mb-3">
                @can('create Tickets')
                    <a href="{{ route('tickets.create') }}" class="btn btn-main font-hold fw-bold">
                        <i class="fas fa-plus me-2"></i>
                        {{ __('Add New Ticket') }}
                    </a>
                @endcan
                
                <!-- Statistics Cards -->
                <div class="d-flex gap-2">
                    <div class="card border-0 shadow-sm" style="min-width: 120px;">
                        <div class="card-body p-2 text-center">
                            <h6 class="mb-0 text-muted small">{{ __('Total') }}</h6>
                            <h4 class="mb-0 fw-bold">{{ collect($tickets)->flatten()->count() }}</h4>
                        </div>
                    </div>
                    <div class="card border-0 shadow-sm bg-warning bg-opacity-10" style="min-width: 120px;">
                        <div class="card-body p-2 text-center">
                            <h6 class="mb-0 text-muted small">{{ __('Pending') }}</h6>
                            <h4 class="mb-0 fw-bold text-warning">{{ isset($tickets['open']) ? count($tickets['open']) : 0 }}</h4>
                        </div>
                    </div>
                </div>
            </div>

            @if (session('message'))
                <div class="alert alert-success alert-dismissible fade show">
                    {{ session('message') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            <!-- Enhanced Kanban Board -->
            <div class="row g-3">
                @foreach (['open', 'in_progress', 'resolved', 'closed'] as $status)
                    @php
                        $statusConfig = [
                            'open' => ['color' => 'primary', 'icon' => 'fa-folder-open'],
                            'in_progress' => ['color' => 'warning', 'icon' => 'fa-spinner'],
                            'resolved' => ['color' => 'success', 'icon' => 'fa-check-circle'],
                            'closed' => ['color' => 'secondary', 'icon' => 'fa-times-circle']
                        ];
                        $config = $statusConfig[$status];
                        $count = isset($tickets[$status]) ? count($tickets[$status]) : 0;
                    @endphp
                    
                    <div class="col-md-6 col-lg-3">
                        <div class="card border-0 shadow-sm h-100">
                            <div class="card-header bg-{{ $config['color'] }} bg-opacity-10 border-0">
                                <div class="d-flex justify-content-between align-items-center">
                                    <h6 class="mb-0 text-{{ $config['color'] }} fw-bold">
                                        <i class="fas {{ $config['icon'] }} me-2"></i>
                                        {{ __(ucfirst(str_replace('_', ' ', $status))) }}
                                    </h6>
                                    <span class="badge bg-{{ $config['color'] }} rounded-pill">{{ $count }}</span>
                                </div>
                            </div>
                            <div class="card-body p-2 status-column" data-status="{{ $status }}" style="min-height: 450px; max-height: 450px; overflow-y: auto;">
                                @if (isset($tickets[$status]) && count($tickets[$status]) > 0)
                                    @foreach ($tickets[$status] as $ticket)
                                        <div class="card mb-2 border-0 shadow-sm hover-shadow transition ticket-card" draggable="true" data-ticket-id="{{ $ticket->id }}">
                                            <div class="card-body p-3">
                                                <div class="d-flex justify-content-between align-items-start mb-2">
                                                    <h6 class="card-title mb-0 flex-grow-1">
                                                        <a href="{{ route('tickets.show', $ticket->id) }}" 
                                                           class="text-decoration-none text-dark fw-semibold">
                                                            {{ Str::limit($ticket->subject, 40) }}
                                                        </a>
                                                    </h6>
                                                    <span class="badge bg-{{ $ticket->priority === 'high' ? 'danger' : ($ticket->priority === 'medium' ? 'warning' : 'info') }} badge-sm">
                                                        {{ __(ucfirst($ticket->priority)) }}
                                                    </span>
                                                </div>
                                                
                                                <div class="d-flex align-items-center text-muted small mb-2">
                                                    <i class="fas fa-user me-1"></i>
                                                    <span>{{ Str::limit($ticket->client->cname ?? 'N/A', 25) }}</span>
                                                </div>
                                                
                                                @if($ticket->assignedTo)
                                                    <div class="d-flex align-items-center text-muted small mb-2">
                                                        <i class="fas fa-user-tie me-1"></i>
                                                        <span>{{ Str::limit($ticket->assignedTo->name, 25) }}</span>
                                                    </div>
                                                @endif
                                                
                                                <div class="d-flex justify-content-between align-items-center mt-2 pt-2 border-top">
                                                    <div class="d-flex align-items-center">
                                                        <small class="text-muted me-3">
                                                            <i class="far fa-clock me-1"></i>
                                                            {{ $ticket->created_at->diffForHumans() }}
                                                        </small>
                                                        @if($ticket->comments_count ?? 0 > 0)
                                                            <small class="text-muted">
                                                                <i class="far fa-comments me-1"></i>
                                                                {{ $ticket->comments_count }}
                                                            </small>
                                                        @endif
                                                    </div>
                                                    
                                                    <div class="btn-group btn-group-sm opacity-0 hover-opacity-100 transition">
                                                        @can('edit Tickets')
                                                            <a href="{{ route('tickets.edit', $ticket->id) }}" class="btn btn-light text-primary" data-bs-toggle="tooltip" title="{{ __('Edit') }}">
                                                                <i class="las la-edit"></i>
                                                            </a>
                                                        @endcan
                                                        @can('delete Tickets')
                                                            <form action="{{ route('tickets.destroy', $ticket->id) }}" method="POST" class="d-inline" onsubmit="return confirm('{{ __('Are you sure?') }}');">
                                                                @csrf
                                                                @method('DELETE')
                                                                <button type="submit" class="btn btn-light text-danger" data-bs-toggle="tooltip" title="{{ __('Delete') }}">
                                                                    <i class="las la-trash"></i>
                                                                </button>
                                                            </form>
                                                        @endcan
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                @else
                                    <div class="text-center py-5">
                                        <i class="fas fa-inbox text-muted" style="font-size: 3rem; opacity: 0.3;"></i>
                                        <p class="text-muted mt-2 mb-0">{{ __('No tickets') }}</p>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    <style>
        .hover-shadow {
            transition: all 0.3s ease;
        }
        .hover-shadow:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0,0,0,0.15) !important;
        }
        .badge-sm {
            font-size: 0.7rem;
            padding: 0.25rem 0.5rem;
        }
        .bg-opacity-10 {
            --bs-bg-opacity: 0.1;
        }
        .opacity-0 { opacity: 0; }
        .card:hover .opacity-0 { opacity: 1; }
        .transition { transition: all 0.3s ease; }
        
        /* Drag and Drop Styles */
        .ticket-card {
            cursor: grab;
        }
        .ticket-card.dragging {
            opacity: 0.5;
            cursor: grabbing;
        }
        .status-column.dragover {
            background-color: rgba(0,0,0,0.05);
            border-radius: 0.5rem;
        }
    </style>

    @push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const draggables = document.querySelectorAll('.ticket-card');
            const containers = document.querySelectorAll('.status-column');

            draggables.forEach(draggable => {
                draggable.addEventListener('dragstart', () => {
                    draggable.classList.add('dragging');
                });

                draggable.addEventListener('dragend', () => {
                    draggable.classList.remove('dragging');
                });
            });

            containers.forEach(container => {
                container.addEventListener('dragover', e => {
                    e.preventDefault();
                    const afterElement = getDragAfterElement(container, e.clientY);
                    const draggable = document.querySelector('.dragging');
                    if (afterElement == null) {
                        container.appendChild(draggable);
                    } else {
                        container.insertBefore(draggable, afterElement);
                    }
                    container.classList.add('dragover');
                });

                container.addEventListener('dragleave', () => {
                    container.classList.remove('dragover');
                });

                container.addEventListener('drop', e => {
                    e.preventDefault();
                    container.classList.remove('dragover');
                    const draggable = document.querySelector('.dragging');
                    const ticketId = draggable.getAttribute('data-ticket-id');
                    const newStatus = container.getAttribute('data-status');
                    
                    updateTicketStatus(ticketId, newStatus);
                });
            });

            function getDragAfterElement(container, y) {
                const draggableElements = [...container.querySelectorAll('.ticket-card:not(.dragging)')];

                return draggableElements.reduce((closest, child) => {
                    const box = child.getBoundingClientRect();
                    const offset = y - box.top - box.height / 2;
                    if (offset < 0 && offset > closest.offset) {
                        return { offset: offset, element: child };
                    } else {
                        return closest;
                    }
                }, { offset: Number.NEGATIVE_INFINITY }).element;
            }

            function updateTicketStatus(ticketId, newStatus) {
                fetch('{{ route("tickets.updateStatus") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        ticket_id: ticketId,
                        new_status: newStatus
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Optional: Show success toast
                        // console.log('Status updated');
                    } else {
                        alert('Failed to update status');
                        location.reload(); // Revert changes
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('An error occurred');
                    location.reload(); // Revert changes
                });
            }
        });
    </script>
    @endpush
@endsection
