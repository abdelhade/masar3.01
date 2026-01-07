<div class="container-fluid">
    <!-- الإحصائيات السريعة -->
    <div class="row mb-4">
        <div class="col-lg-3 col-md-6">
            <div class="card bg-primary text-white shadow-sm">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-white-50 mb-1">{{ __('Total Invoices') }}</h6>
                            <h3 class="mb-0">{{ number_format($statistics['total']) }}</h3>
                        </div>
                        <div class="fs-1 opacity-50">
                            <i class="fas fa-file-invoice"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-3 col-md-6">
            <div class="card bg-success text-white shadow-sm">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-white-50 mb-1">{{ __('This Month') }}</h6>
                            <h3 class="mb-0">{{ number_format($statistics['thisMonth']) }}</h3>
                        </div>
                        <div class="fs-1 opacity-50">
                            <i class="fas fa-calendar-check"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-3 col-md-6">
            <div class="card bg-info text-white shadow-sm">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-white-50 mb-1">{{ __('Total Value') }}</h6>
                            <h3 class="mb-0">{{ number_format($statistics['totalValue'], 2) }}</h3>
                            <small>{{ __('EGP') }}</small>
                        </div>
                        <div class="fs-1 opacity-50">
                            <i class="fas fa-money-bill-wave"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-3 col-md-6">
            <div class="card bg-warning text-white shadow-sm">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-white-50 mb-1">{{ __('Average Cost') }}</h6>
                            <h3 class="mb-0">{{ number_format($statistics['avgValue'], 2) }}</h3>
                            <small>{{ __('EGP') }}</small>
                        </div>
                        <div class="fs-1 opacity-50">
                            <i class="fas fa-chart-line"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- الفلاتر والبحث -->
    <div class="card shadow-sm mb-4">
        <div class="card-header bg-white">
            <div class="d-flex justify-content-between align-items-center">
                <h5 class="mb-0">
                    <i class="fas fa-filter me-2"></i>
                    {{ __('Manufacturing Invoices') }}
                </h5>
                @can('create Manufacturing Invoices')
                    <a href="{{ route('manufacturing.create') }}" class="btn btn-primary">
                        <i class="fas fa-plus me-2"></i>
                        {{ __('New Invoice') }}
                    </a>
                @endcan
            </div>
        </div>

        <div class="card-body">
            <div class="row g-3 mb-3">
                <div class="col-md-3">
                    <input type="text" wire:model.live.debounce.300ms="search" class="form-control"
                        placeholder="{{ __('Search by invoice number or description...') }}">
                </div>

                <div class="col-md-2">
                    <input type="date" wire:model.live="dateFrom" class="form-control"
                        placeholder="{{ __('From Date') }}">
                </div>

                <div class="col-md-2">
                    <input type="date" wire:model.live="dateTo" class="form-control"
                        placeholder="{{ __('To Date') }}">
                </div>

                <div class="col-md-2">
                    <select wire:model.live="branchFilter" class="form-select">
                        <option value="">{{ __('All Branches') }}</option>
                        @foreach ($branches as $branch)
                            <option value="{{ $branch->id }}">{{ $branch->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-2">
                    <select wire:model.live="perPage" class="form-select">
                        <option value="15">15</option>
                        <option value="25">25</option>
                        <option value="50">50</option>
                        <option value="100">100</option>
                    </select>
                </div>

                <div class="col-md-1">
                    <button wire:click="$refresh" class="btn btn-secondary w-100">
                        <i class="fas fa-sync-alt"></i>
                    </button>
                </div>
            </div>

            <!-- الجدول -->
            <div class="table-responsive">
                <table class="table table-hover table-bordered align-middle">
                    <thead class="table-light">
                        <tr class="text-center">
                            <th style="width: 5%">#</th>
                            <th style="width: 10%" wire:click="sortBy('pro_id')" class="cursor-pointer">
                                {{ __('Invoice Number') }}
                                @if ($sortField === 'pro_id')
                                    <i class="fas fa-sort-{{ $sortDirection === 'asc' ? 'up' : 'down' }}"></i>
                                @endif
                            </th>
                            <th style="width: 10%" wire:click="sortBy('pro_date')" class="cursor-pointer">
                                {{ __('Date') }}
                                @if ($sortField === 'pro_date')
                                    <i class="fas fa-sort-{{ $sortDirection === 'asc' ? 'up' : 'down' }}"></i>
                                @endif
                            </th>
                            <th style="width: 15%">{{ __('Product') }}</th>
                            <th style="width: 15%">{{ __('Raw Materials') }}</th>
                            <th style="width: 10%">{{ __('Employee') }}</th>
                            <th style="width: 10%">{{ __('Branch') }}</th>
                            <th style="width: 10%" wire:click="sortBy('pro_value')" class="cursor-pointer">
                                {{ __('Value') }}
                                @if ($sortField === 'pro_value')
                                    <i class="fas fa-sort-{{ $sortDirection === 'asc' ? 'up' : 'down' }}"></i>
                                @endif
                            </th>
                            <th style="width: 15%">{{ __('Actions') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($invoices as $invoice)
                            <tr>
                                <td class="text-center">
                                    {{ $loop->iteration + ($invoices->currentPage() - 1) * $invoices->perPage() }}
                                </td>
                                <td class="text-center">
                                    <span class="badge bg-primary">{{ $invoice->pro_id }}</span>
                                </td>
                                <td class="text-center">{{ $invoice->pro_date }}</td>
                                <td>{{ $invoice->acc1Head->aname ?? '-' }}</td>
                                <td>{{ $invoice->acc2Head->aname ?? '-' }}</td>
                                <td>{{ $invoice->employee->aname ?? '-' }}</td>
                                <td>{{ $invoice->branch->name ?? '-' }}</td>
                                <td class="text-end">
                                    <strong class="text-success">{{ number_format($invoice->pro_value, 2) }}</strong>
                                    <small class="text-muted d-block">{{ __('EGP') }}</small>
                                </td>
                                <td class="text-center">
                                    <div class="btn-group btn-group-sm" role="group">
                                        @can('view Manufacturing Invoices')
                                            <a href="{{ route('manufacturing.show', $invoice->id) }}"
                                                class="btn btn-info" title="{{ __('View') }}">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                        @endcan
                                        @can('edit Manufacturing Invoices')
                                            <a href="{{ route('manufacturing.edit', $invoice->id) }}"
                                                class="btn btn-warning" title="{{ __('Edit') }}">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                        @endcan
                                        @can('delete Manufacturing Invoices')
                                            <button wire:click="confirmDelete({{ $invoice->id }})"
                                                class="btn btn-danger" title="{{ __('Delete') }}">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        @endcan

                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="text-center py-5">
                                    <i class="fas fa-inbox fa-3x text-muted mb-3 d-block"></i>
                                    <h5 class="text-muted">{{ __('No Manufacturing Invoices') }}</h5>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div class="d-flex justify-content-between align-items-center mt-3">
                <div class="text-muted">
                    {{ __('Showing') }} {{ $invoices->firstItem() ?? 0 }} {{ __('to') }}
                    {{ $invoices->lastItem() ?? 0 }}
                    {{ __('of') }} {{ $invoices->total() }} {{ __('invoices') }}
                </div>
                <div>
                    {{ $invoices->links() }}
                </div>
            </div>
        </div>
    </div>
    @push('scripts')
        <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
        <script>
            document.addEventListener('livewire:init', () => {
                // Success Alert
                Livewire.on('success-swal', (data) => {
                    const d = Array.isArray(data) ? data[0] : data;
                    Swal.fire({
                        title: d.title || '{{ __('Done!') }}',
                        text: d.text || '{{ __('Operation completed successfully') }}',
                        icon: d.icon || 'success',
                        confirmButtonText: '{{ __('OK') }}'
                    });
                });

                // Error Alert
                Livewire.on('error-swal', (data) => {
                    const d = Array.isArray(data) ? data[0] : data;
                    Swal.fire({
                        title: d.title || '{{ __('Error!') }}',
                        text: d.text || '{{ __('An unexpected error occurred') }}',
                        icon: d.icon || 'error',
                        confirmButtonText: '{{ __('OK') }}'
                    });
                });

                // Confirm Delete
                Livewire.on('confirm-delete', (data) => {
                    const d = Array.isArray(data) ? data[0] : data;
                    Swal.fire({
                        title: d.title,
                        text: d.text,
                        icon: d.icon,
                        showCancelButton: true,
                        confirmButtonColor: '#d33',
                        cancelButtonColor: '#3085d6',
                        confirmButtonText: d.confirmButtonText,
                        cancelButtonText: d.cancelButtonText
                    }).then((result) => {
                        if (result.isConfirmed) {
                            Livewire.dispatch('deleteInvoice', {
                                invoiceId: d.invoiceId
                            });
                        }
                    });
                });
            });
        </script>
    @endpush

    <style>
        .cursor-pointer {
            cursor: pointer;
            user-select: none;
        }

        .cursor-pointer:hover {
            background-color: #f8f9fa;
        }
    </style>
</div>
