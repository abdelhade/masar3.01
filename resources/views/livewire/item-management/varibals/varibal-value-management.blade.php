<div>
    <!-- Header Section -->
    <div class="row mb-4">
        <div class="col-md-6">
            <h4 class="mb-0">{{ $varibal->name ?? __('Not Specified') }}</h4>
        </div>
        <div class="col-md-6 text-end">
            @can('create varibalsValues')
                <button wire:click="create" class="btn btn-primary">
                    <i class="fas fa-plus"></i> {{ __('Add') }}
                </button>
            @endcan
        </div>
    </div>

    <!-- Search Section -->
    <div class="row mb-3">
        <div class="col-md-6">
            <div class="input-group">
                <input type="text" wire:model.live="search" class="form-control"
                    placeholder="{{ __('Search in') }} {{ $varibal->name ?? '' }}...">
                <span class="input-group-text">
                    <i class="fas fa-search"></i>
                </span>
            </div>
        </div>
    </div>

    <!-- Flash Messages -->
    @if (session()->has('message'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('message') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <!-- Form Modal -->
    @if ($showForm)
        <div class="modal fade show d-block" tabindex="-1" style="background-color: rgba(0,0,0,0.5);" aria-modal="true"
            role="dialog">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">
                            {{ $editingId ? __('Edit Value') : __('Add New Value') }}
                        </h5>
                        <button type="button" wire:click="cancel" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <form wire:submit.prevent="save">
                            <div class="mb-3">
                                <label for="value" class="form-label">{{ __('Value') }} <span
                                        class="text-danger">*</span></label>
                                <input type="text" wire:model="value"
                                    class="form-control @error('value') is-invalid @enderror" id="value"
                                    placeholder="{{ __('Enter value') }}">
                                @error('value')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </form>
                    </div>
                    <div class="modal-footer">
                        <button type="button" wire:click="cancel" class="btn btn-secondary" data-bs-dismiss="modal">
                            {{ __('Cancel') }}
                        </button>
                        <button type="button" wire:click="save" class="btn btn-primary">
                            {{ $editingId ? __('Update') : __('Save') }}
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <!-- Data Table -->
    <div class="card">
        <div class="card-body">
            @if ($varibalValues->count() > 0)
                <div class="table-responsive">
                    <table class="table table-striped table-hover">
                        <thead>
                            <tr>
                                <th>{{ $varibal->name ?? __('Variable Name') }}</th>
                                @canany(['edit varibalsValues', 'delete varibalsValues'])
                                    <th>{{ __('Actions') }}</th>
                                @endcanany
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($varibalValues as $varibalValue)
                                <tr>
                                    <td>{{ $varibalValue->value }}</td>
                                    @canany(['edit varibalsValues', 'delete varibalsValues'])
                                        <td>
                                            <div class="btn-group" role="group">
                                                @can('edit varibalsValues')
                                                    <button wire:click="edit({{ $varibalValue->id }})"
                                                        class="btn btn-sm btn-outline-primary" title="{{ __('Edit') }}">
                                                        <i class="fas fa-edit"></i>
                                                    </button>
                                                @endcan
                                                @can('delete varibalsValues')
                                                    <button wire:click="delete({{ $varibalValue->id }})"
                                                        class="btn btn-sm btn-outline-danger" title="{{ __('Delete') }}"
                                                        onclick="return confirm('{{ __('Are you sure you want to delete this value?') }}')">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                @endcan
                                            </div>
                                        </td>
                                    @endcanany
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div class="d-flex justify-content-center mt-3">
                    {{ $varibalValues->links() }}
                </div>
            @else
                <div class="text-center py-5">
                    <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                    <h5 class="text-muted">{{ __('No Values') }}</h5>
                    <p class="text-muted">{{ __('No values have been added for this variable yet') }}</p>
                    @if (!$search)
                        @can('create varibalsValues')
                            <button wire:click="create" class="btn btn-primary">
                                <i class="fas fa-plus"></i> {{ __('Add First Value') }}
                            </button>
                        @endcan
                    @endif
                </div>
            @endif
        </div>
    </div>

    <!-- Loading Indicator -->
    <div wire:loading class="position-fixed top-50 start-50 translate-middle z-50">
        <div class="spinner-border text-primary" role="status">
            <span class="visually-hidden">{{ __('Loading...') }}</span>
        </div>
    </div>
</div>
