<div>
    <!-- Header Section -->
    <div class="row mb-4">
        <div class="col-md-6">
            <h4 class="mb-0">{{ __('Varibals Management') }}</h4>
            <p class="text-muted mb-0">{{ __('Manage your varibals') }}</p>
        </div>
        <div class="col-md-6 text-end">
            <button wire:click="create" class="btn btn-primary">
                <i class="fas fa-plus"></i> {{ __('Add New Varibal') }}
            </button>
        </div>
    </div>

    <!-- Search and Filters -->
    <div class="row mb-4">
        <div class="col-md-6">
            <div class="input-group">
                <span class="input-group-text">
                    <i class="fas fa-search"></i>
                </span>
                <input type="text" wire:model.live.debounce.300ms="search" 
                       class="form-control" placeholder="{{ __('Search varibals...') }}">
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

    <!-- Table -->
    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead class="table-light">
                        <tr>
                            <th>{{ __('Name') }}</th>
                            <th>{{ __('Description') }}</th>
                            <th>{{ __('Created At') }}</th>
                            <th class="text-center">{{ __('Actions') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($varibals as $varibal)
                            <tr>
                                <td>
                                    <strong>{{ $varibal->name }}</strong>
                                </td>
                                <td>
                                    <span class="text-muted">
                                        {{ $varibal->description ? Str::limit($varibal->description, 50) : '-' }}
                                    </span>
                                </td>
                                <td>
                                    <small class="text-muted">
                                        {{ $varibal->created_at->format('Y-m-d H:i') }}
                                    </small>
                                </td>
                                <td class="text-center">
                                    <div class="btn-group" role="group">
                                        <button wire:click="edit({{ $varibal->id }})" 
                                                class="btn btn-sm btn-outline-primary">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <button wire:click="confirmDelete({{ $varibal->id }})" 
                                                class="btn btn-sm btn-outline-danger">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="text-center py-4">
                                    <div class="text-muted">
                                        <i class="fas fa-inbox fa-2x mb-2"></i>
                                        <p>{{ __('No varibals found') }}</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            @if($varibals->hasPages())
                <div class="d-flex justify-content-center mt-4">
                    {{ $varibals->links() }}
                </div>
            @endif
        </div>
    </div>

    <!-- Create/Edit Modal -->
    @if($showModal)
        <div class="modal fade show d-block" tabindex="-1" style="background-color: rgba(0,0,0,0.5);">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">
                            @if($editingId)
                                {{ __('Edit Varibal') }}
                            @else
                                {{ __('Create New Varibal') }}
                            @endif
                        </h5>
                        <button type="button" wire:click="resetForm" class="btn-close"></button>
                    </div>
                    <div class="modal-body">
                        <form wire:submit.prevent="save">
                            <div class="row">
                                <div class="col-md-12 mb-3">
                                    <label for="name" class="form-label">{{ __('Name') }} <span class="text-danger">*</span></label>
                                    <input type="text" 
                                           wire:model="name" 
                                           class="form-control @error('name') is-invalid @enderror"
                                           id="name" 
                                           placeholder="{{ __('Enter varibal name') }}">
                                    @error('name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-12 mb-3">
                                    <label for="description" class="form-label">{{ __('Description') }}</label>
                                    <textarea wire:model="description" 
                                              class="form-control @error('description') is-invalid @enderror"
                                              id="description" 
                                              rows="3"
                                              placeholder="{{ __('Enter varibal description (optional)') }}"></textarea>
                                    @error('description')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </form>
                    </div>
                    <div class="modal-footer">
                        <button type="button" wire:click="resetForm" class="btn btn-secondary">
                            {{ __('Cancel') }}
                        </button>
                        <button type="button" wire:click="save" class="btn btn-primary">
                            @if($editingId)
                                {{ __('Update') }}
                            @else
                                {{ __('Create') }}
                            @endif
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <!-- Delete Confirmation Modal -->
    @if($deleteId)
        <div class="modal fade show d-block" tabindex="-1" style="background-color: rgba(0,0,0,0.5);">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">{{ __('Confirm Delete') }}</h5>
                        <button type="button" wire:click="deleteId = null" class="btn-close"></button>
                    </div>
                    <div class="modal-body">
                        <p>{{ __('Are you sure you want to delete this varibal? This action cannot be undone.') }}</p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" wire:click="deleteId = null" class="btn btn-secondary">
                            {{ __('Cancel') }}
                        </button>
                        <button type="button" wire:click="delete" class="btn btn-danger">
                            {{ __('Delete') }}
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
