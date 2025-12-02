<?php

declare(strict_types=1);

use Livewire\Volt\Component;
use App\Models\Covenant;
use App\Models\Employee;
use Livewire\WithPagination;
use Livewire\WithFileUploads;
use Livewire\Attributes\Rule;
use Livewire\Attributes\Computed;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;

new class extends Component {
    use WithPagination, WithFileUploads;
    protected string $paginationTheme = 'bootstrap';

    // Form fields
    #[Rule('required|string|max:255')]
    public string $name = '';

    #[Rule('nullable|string|max:1000')]
    public ?string $description = null;

    #[Rule('nullable|file|mimes:jpg,jpeg,png,gif|max:2048')]
    public mixed $image = null;

    #[Rule('nullable|exists:employees,id')]
    public ?int $employee_id = null;

    // UI state
    public string $search = '';
    public ?int $filter_employee_id = null;
    public bool $showModal = false;
    public bool $showViewModal = false;
    public ?int $editingCovenantId = null;
    public ?int $viewCovenantId = null;
    public ?int $deleteId = null;
    public bool $showDeleteModal = false;
    public ?string $currentImageUrl = null;
    public array $employeesList = [];

    protected array $queryString = ['search'];

    public function mount(): void
    {
        $this->resetForm();
        $this->loadEmployees();
    }

    public function loadEmployees(): void
    {
        $this->employeesList = Employee::select('id', 'name')
            ->orderBy('name')
            ->get()
            ->map(fn($emp) => ['id' => $emp->id, 'name' => $emp->name])
            ->toArray();
    }

    public function resetForm(): void
    {
        $this->reset([
            'name', 'description', 'image', 'employee_id',
            'editingCovenantId', 'currentImageUrl'
        ]);
        $this->resetValidation();
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingFilterEmployeeId(): void
    {
        $this->resetPage();
    }

    private function checkPermission(string $permission): void
    {
        abort_unless(auth()->user()->can($permission), 403, __('hr.unauthorized_action'));
    }

    public function create(): void
    {
        $this->checkPermission('create Covenants');
        $this->resetForm();
        $this->showModal = true;
    }

    public function save(): void
    {
        if ($this->editingCovenantId) {
            $this->update();
        } else {
            $this->store();
        }
    }

    public function store(): void
    {
        $this->checkPermission('create Covenants');
        $this->validate();

        $covenant = Covenant::create([
            'name' => $this->name,
            'description' => $this->description,
            'employee_id' => $this->employee_id,
        ]);

        // Handle file upload
        if ($this->image) {
            $covenant->addMediaFromStream($this->image->readStream())
                ->usingName($this->image->getClientOriginalName())
                ->usingFileName($this->image->getClientOriginalName())
                ->toMediaCollection('HR_Covenants');
        }

        $this->resetForm();
        $this->showModal = false;
        session()->flash('message', __('hr.covenant_created_successfully'));
    }

    public function edit(int $id): void
    {
        $this->checkPermission('edit Covenants');
        $covenant = Covenant::with('employee')->findOrFail($id);
        $this->editingCovenantId = $id;
        $this->name = $covenant->name;
        $this->description = $covenant->description ?? '';
        $this->employee_id = $covenant->employee_id;
        $this->currentImageUrl = $covenant->image_url;
        $this->showModal = true;
    }

    public function update(): void
    {
        $this->checkPermission('edit Covenants');
        
        if (!$this->editingCovenantId) {
            session()->flash('error', __('hr.covenant_not_found'));
            return;
        }
        
        $this->validate();

        $covenant = Covenant::findOrFail($this->editingCovenantId);
        $covenant->update([
            'name' => $this->name,
            'description' => $this->description,
            'employee_id' => $this->employee_id,
        ]);

        // Handle file upload
        if ($this->image) {
            // Remove old file
            $covenant->clearMediaCollection('HR_Covenants');
            
            // Add new file
            $covenant->addMediaFromStream($this->image->readStream())
                ->usingName($this->image->getClientOriginalName())
                ->usingFileName($this->image->getClientOriginalName())
                ->toMediaCollection('HR_Covenants');
        }

        $this->resetForm();
        $this->showModal = false;
        session()->flash('message', __('hr.covenant_updated_successfully'));
    }

    public function view(int $id): void
    {
        $this->checkPermission('view Covenants');
        $this->viewCovenantId = $id;
        $this->showViewModal = true;
    }
    
    #[Computed]
    public function viewCovenant(): ?Covenant
    {
        if (!$this->viewCovenantId) {
            return null;
        }
        return Covenant::with(['employee', 'media'])->find($this->viewCovenantId);
    }

    public function delete(int $id): void
    {
        $this->checkPermission('delete Covenants');
        $this->deleteId = $id;
        $this->showDeleteModal = true;
    }

    public function confirmDelete(): void
    {
        $this->checkPermission('delete Covenants');
        $covenant = Covenant::findOrFail($this->deleteId);
        $covenant->clearMediaCollection('HR_Covenants');
        $covenant->delete();
        
        $this->showDeleteModal = false;
        $this->deleteId = null;
        session()->flash('message', __('hr.covenant_deleted_successfully'));
    }

    public function downloadImage(int $id)
    {
        $covenant = Covenant::findOrFail($id);
        $media = $covenant->getFirstMedia('HR_Covenants');
        
        if ($media) {
            return response()->download($media->getPath(), $media->file_name);
        }
        
        session()->flash('error', __('hr.covenant_image_not_found'));
    }

    public function clearFilters(): void
    {
        $this->reset(['search', 'filter_employee_id']);
    }

    #[Computed]
    public function covenants(): LengthAwarePaginator
    {
        return Covenant::query()
            ->with(['employee', 'media'])
            ->when($this->search, function ($query) {
                $query->where(function ($q) {
                    $q->where('name', 'like', '%' . $this->search . '%')
                      ->orWhere('description', 'like', '%' . $this->search . '%')
                      ->orWhereHas('employee', function ($empQuery) {
                          $empQuery->where('name', 'like', '%' . $this->search . '%');
                      });
                });
            })
            ->when($this->filter_employee_id, function ($query) {
                $query->where('employee_id', $this->filter_employee_id);
            })
            ->latest()
            ->paginate(10);
    }


}; ?>

<div>
    @if (session()->has('message'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('message') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if (session()->has('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <!-- Header -->
    <div class="row mb-4">
        <div class="col-md-6">
            <div class="d-flex align-items-center">
                <div class="me-3">
                    <i class="las la-file-contract text-primary" style="font-size: 2.5rem;"></i>
                </div>
                <div>
                    <h4 class="mb-1">{{ __('hr.covenants_management') }}</h4>
                    <p class="text-muted mb-0">{{ __('hr.manage_covenants') }}</p>
                </div>
            </div>
        </div>
        <div class="col-md-6 text-end">
            @can('create Covenants')
                <button wire:click="create" 
                        class="btn btn-main btn-lg shadow-sm"
                        wire:loading.attr="disabled"
                        wire:target="create">
                    <span wire:loading.remove wire:target="create">
                        <i class="las la-plus me-2"></i> {{ __('hr.add_new_covenant') }}
                    </span>
                    <span wire:loading wire:target="create">
                        <i class="las la-spinner la-spin me-2"></i> {{ __('hr.opening') }}...
                    </span>
                </button>
            @endcan
        </div>
    </div>

    <!-- Filters -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-4">
                    <div class="position-relative">
                        <i class="las la-search position-absolute top-50 start-0 translate-middle-y ms-3 text-muted"></i>
                        <input wire:model.live.debounce.300ms="search" type="text" class="form-control ps-5" placeholder="{{ __('hr.search_covenants') }}">
                    </div>
                </div>
                <div class="col-md-3">
                    <select wire:model.live.debounce.500ms="filter_employee_id" class="form-select">
                        <option value="">{{ __('hr.all_employees') }}</option>
                        @foreach($employeesList ?? [] as $employee)
                            <option value="{{ $employee['id'] }}">{{ $employee['name'] }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <button wire:click="clearFilters" class="btn btn-outline-secondary w-100">
                        <i class="las la-filter me-1"></i> {{ __('hr.clear') }}
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Covenants Table -->
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white border-0 py-3">
            <div class="d-flex justify-content-between align-items-center">
                <h6 class="mb-0">
                    <i class="las la-list me-2"></i>{{ __('hr.covenants_list') }}
                    <span class="badge bg-primary ms-2">{{ $this->covenants->total() }}</span>
                </h6>
                <div class="d-flex align-items-center text-muted">
                    <small>{{ __('hr.showing') }} {{ $this->covenants->firstItem() ?? 0 }} {{ __('hr.to') }} {{ $this->covenants->lastItem() ?? 0 }} {{ __('hr.of') }} {{ $this->covenants->total() }} {{ __('hr.results') }}</small>
                </div>
            </div>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="border-0 fw-semibold">{{ __('hr.name') }}</th>
                            <th class="border-0 fw-semibold">{{ __('hr.description') }}</th>
                            <th class="border-0 fw-semibold">{{ __('hr.employee') }}</th>
                            <th class="border-0 fw-semibold">{{ __('hr.image') }}</th>
                            <th class="border-0 fw-semibold">{{ __('hr.created_at') }}</th>
                            @canany(['view Covenants', 'edit Covenants', 'delete Covenants'])
                                <th class="border-0 fw-semibold text-center">{{ __('hr.actions') }}</th>
                            @endcanany
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($this->covenants as $covenant)
                            <tr wire:key="covenant-{{ $covenant->id }}">
                                <td>
                                    <div class="fw-semibold">{{ $covenant->name }}</div>
                                </td>
                                <td>
                                    <div class="text-muted">
                                        {{ Str::limit($covenant->description ?? __('hr.no_description'), 50) }}
                                    </div>
                                </td>
                                <td>
                                    @if($covenant->employee)
                                        <span class="badge bg-info-subtle text-info">
                                            {{ $covenant->employee->name }}
                                        </span>
                                    @else
                                        <span class="text-muted">{{ __('hr.no_employee') }}</span>
                                    @endif
                                </td>
                                <td>
                                    @if($covenant->image_url)
                                        <div class="d-flex align-items-center gap-2">
                                            <img src="{{ $covenant->image_url }}" 
                                                 alt="{{ $covenant->name }}" 
                                                 class="img-thumbnail" 
                                                 style="max-width: 50px; max-height: 50px; object-fit: cover;"
                                                 onerror="
                                                     this.style.display = 'none';
                                                     if (this.nextElementSibling) this.nextElementSibling.style.display = 'inline-block';
                                                 ">
                                            <span class="text-muted" style="display: none;">
                                                <i class="las la-file-image"></i> {{ __('hr.no_file') }}
                                            </span>
                                            <button wire:click="downloadImage({{ $covenant->id }})" 
                                                    class="btn btn-sm btn-outline-primary" 
                                                    title="{{ __('hr.download_image') }}">
                                                <i class="las la-download"></i>
                                            </button>
                                        </div>
                                    @else
                                        <span class="text-muted">
                                            <i class="las la-file-image"></i> {{ __('hr.no_file') }}
                                        </span>
                                    @endif
                                </td>
                                <td>{{ $covenant->created_at->format('M d, Y') }}</td>
                                @canany(['view Covenants', 'edit Covenants', 'delete Covenants'])
                                    <td class="text-center">
                                        <div class="d-flex justify-content-center gap-1">
                                            @can('view Covenants')
                                                <button wire:click="view({{ $covenant->id }})" 
                                                        class="btn btn-sm btn-outline-info" 
                                                        title="{{ __('hr.view') }}"
                                                        wire:key="view-btn-{{ $covenant->id }}"
                                                        wire:loading.attr="disabled"
                                                        wire:target="view({{ $covenant->id }})">
                                                    <span wire:loading.remove wire:target="view({{ $covenant->id }})">
                                                        <i class="las la-eye"></i>
                                                    </span>
                                                    <span wire:loading wire:target="view({{ $covenant->id }})">
                                                        <i class="las la-spinner la-spin"></i>
                                                    </span>
                                                </button>
                                            @endcan
                                            @can('edit Covenants')
                                                <button wire:click="edit({{ $covenant->id }})" 
                                                        class="btn btn-sm btn-outline-warning" 
                                                        title="{{ __('hr.edit') }}"
                                                        wire:key="edit-btn-{{ $covenant->id }}"
                                                        wire:loading.attr="disabled"
                                                        wire:target="edit({{ $covenant->id }})">
                                                    <span wire:loading.remove wire:target="edit({{ $covenant->id }})">
                                                        <i class="las la-edit"></i>
                                                    </span>
                                                    <span wire:loading wire:target="edit({{ $covenant->id }})">
                                                        <i class="las la-spinner la-spin"></i>
                                                    </span>
                                                </button>
                                            @endcan
                                            @can('delete Covenants')
                                                <button wire:click="delete({{ $covenant->id }})" 
                                                        class="btn btn-sm btn-outline-danger" 
                                                        title="{{ __('hr.delete') }}"
                                                        wire:key="delete-btn-{{ $covenant->id }}"
                                                        wire:loading.attr="disabled"
                                                        wire:target="delete({{ $covenant->id }})">
                                                    <span wire:loading.remove wire:target="delete({{ $covenant->id }})">
                                                        <i class="las la-trash"></i>
                                                    </span>
                                                    <span wire:loading wire:target="delete({{ $covenant->id }})">
                                                        <i class="las la-spinner la-spin"></i>
                                                    </span>
                                                </button>
                                            @endcan
                                        </div>
                                    </td>
                                @endcanany
                            </tr>
                        @empty
                            <tr>
                                <td colspan="{{ auth()->user()->canany(['view Covenants', 'edit Covenants', 'delete Covenants']) ? 6 : 5 }}" class="text-center py-5">
                                    <div class="text-muted">
                                        <i class="las la-file-contract text-muted" style="font-size: 4rem;"></i>
                                        <h5 class="mt-3 mb-2">{{ __('hr.no_covenants_found') }}</h5>
                                        <p class="mb-3">{{ __('hr.start_by_adding_first_covenant') }}</p>
                                        @can('create Covenants')
                                            <button wire:click="create" 
                                                    class="btn btn-main"
                                                    wire:loading.attr="disabled"
                                                    wire:target="create">
                                                <span wire:loading.remove wire:target="create">
                                                    <i class="las la-plus me-2"></i> {{ __('hr.add_first_covenant') }}
                                                </span>
                                                <span wire:loading wire:target="create">
                                                    <i class="las la-spinner la-spin me-2"></i> {{ __('hr.opening') }}...
                                                </span>
                                            </button>
                                        @endcan
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if($this->covenants->hasPages())
            <div class="card-footer bg-white border-0">
                {{ $this->covenants->links() }}
            </div>
        @endif
    </div>

    <!-- Create/Edit Modal - Alpine.js -->
    <div x-data="{ show: @entangle('showModal') }" 
         x-show="show"
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         x-cloak
         class="fixed inset-0 z-50 overflow-y-auto"
         style="display: none;">
        <!-- Backdrop -->
        <div class="fixed inset-0" style="background-color: rgba(0, 0, 0, 0.5);"
             @click="show = false"></div>
        
        <!-- Modal -->
        <div class="d-flex align-items-center justify-content-center" style="min-height: 100vh; padding: 1rem;">
            <div class="bg-white rounded-lg shadow-xl max-w-2xl w-full relative z-10"
                 x-transition:enter="transition ease-out duration-300"
                 x-transition:enter-start="opacity-0 transform scale-95"
                 x-transition:enter-end="opacity-100 transform scale-100"
                 x-transition:leave="transition ease-in duration-200"
                 x-transition:leave-start="opacity-100 transform scale-100"
                 x-transition:leave-end="opacity-0 transform scale-95"
                 @click.stop>
                <div class="modal-header border-bottom p-3">
                    <h5 class="modal-title mb-0">
                        @if($this->editingCovenantId)
                            {{ __('hr.edit_covenant') }}
                        @else
                            {{ __('hr.add_new_covenant') }}
                        @endif
                    </h5>
                    <button type="button" class="btn-close" @click="show = false" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <form wire:submit.prevent="save">
                        <div class="mb-3">
                            <label for="name" class="form-label">{{ __('hr.name') }} <span class="text-danger">*</span></label>
                            <input type="text" 
                                   class="form-control @error('name') is-invalid @enderror" 
                                   id="name" 
                                   wire:model.blur="name" 
                                   required>
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="description" class="form-label">{{ __('hr.description') }}</label>
                            <textarea class="form-control @error('description') is-invalid @enderror" 
                                      id="description" 
                                      wire:model.blur="description" 
                                      rows="3"></textarea>
                            @error('description')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="employee_id" class="form-label">{{ __('hr.employee') }}</label>
                            <select class="form-select @error('employee_id') is-invalid @enderror" 
                                    id="employee_id" 
                                    wire:model.blur="employee_id">
                                <option value="">{{ __('hr.select_employee') }}</option>
                                @foreach($employeesList ?? [] as $employee)
                                    <option value="{{ $employee['id'] }}">{{ $employee['name'] }}</option>
                                @endforeach
                            </select>
                            @error('employee_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3" x-data="{ imagePreview: null, handleImageChange(event) { const file = event.target.files[0]; if (file) { const reader = new FileReader(); reader.onload = (e) => { this.imagePreview = e.target.result; }; reader.readAsDataURL(file); } else { this.imagePreview = null; } } }">
                            <label for="image" class="form-label">{{ __('hr.image') }}</label>
                            
                            <!-- Show preview of newly uploaded image -->
                            <template x-if="imagePreview">
                                <div class="mb-2 position-relative">
                                    <div style="position: relative; display: inline-block;">
                                        <img :src="imagePreview" 
                                             alt="{{ __('hr.new_image_preview') }}" 
                                             class="img-thumbnail" 
                                             style="max-height: 150px; max-width: 100%; object-fit: contain;">
                                    </div>
                                    <p class="text-muted small mt-1">{{ __('hr.new_image_preview') }}</p>
                                </div>
                            </template>
                            
                            <!-- Show current image (when editing) -->
                            @if($currentImageUrl)
                                <div class="mb-2 position-relative" x-show="!imagePreview">
                                    <div style="position: relative; display: inline-block;">
                                        <img src="{{ $currentImageUrl }}" 
                                             alt="{{ __('hr.current_image') }}" 
                                             class="img-thumbnail" 
                                             style="max-height: 150px; max-width: 100%; object-fit: contain; display: none;"
                                             onload="
                                                 this.style.display = 'block';
                                                 if (this.nextElementSibling) this.nextElementSibling.style.display = 'none';
                                                 if (this.nextElementSibling && this.nextElementSibling.nextElementSibling) this.nextElementSibling.nextElementSibling.style.display = 'none';
                                             "
                                             onerror="
                                                 this.style.display = 'none';
                                                 if (this.nextElementSibling) this.nextElementSibling.style.display = 'none';
                                                 if (this.nextElementSibling && this.nextElementSibling.nextElementSibling) this.nextElementSibling.nextElementSibling.style.display = 'block';
                                             ">
                                        <!-- Loading indicator -->
                                        <div style="display: block; text-align: center; padding: 20px; min-height: 150px; display: flex; align-items: center; justify-content: center;">
                                            <div class="spinner-border text-primary" role="status">
                                                <span class="visually-hidden">{{ __('hr.loading') }}...</span>
                                            </div>
                                        </div>
                                        <!-- Placeholder (shown on error) -->
                                        <div style="display: none; text-align: center; padding: 20px; min-height: 150px; display: flex; align-items: center; justify-content: center; background-color: #f8f9fa; border: 1px solid #dee2e6; border-radius: 0.25rem;">
                                            <div>
                                                <i class="las la-image text-muted" style="font-size: 3rem;"></i>
                                                <p class="text-muted mb-0 mt-2 small">{{ __('hr.image_not_available') }}</p>
                                            </div>
                                        </div>
                                    </div>
                                    <p class="text-muted small mt-1">{{ __('hr.current_image') }}</p>
                                </div>
                            @endif
                            
                            <input type="file" 
                                   class="form-control @error('image') is-invalid @enderror" 
                                   id="image" 
                                   wire:model="image" 
                                   x-on:change="handleImageChange($event)"
                                   accept="image/*">
                            @error('image')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="text-muted d-block mt-1">
                                <i class="las la-info-circle me-1"></i>
                                {{ __('hr.image_upload_hint') }}
                            </small>
                            <!-- Upload Progress -->
                            <div wire:loading wire:target="image" class="progress mt-2" style="height: 20px;">
                                <div class="progress-bar progress-bar-striped progress-bar-animated bg-success" 
                                     role="progressbar" style="width: 100%">
                                    <span class="fw-bold">{{ __('hr.uploading') }}...</span>
                                </div>
                            </div>
                        </div>

                        <div class="modal-footer border-top p-3">
                            <button type="button" class="btn btn-secondary" @click="show = false" wire:loading.attr="disabled" wire:target="image,save">{{ __('hr.cancel') }}</button>
                            <button type="submit" class="btn btn-main" wire:loading.attr="disabled" wire:target="image,save">
                                <span wire:loading.remove wire:target="image,save">
                                    @if($this->editingCovenantId)
                                        {{ __('hr.update') }}
                                    @else
                                        {{ __('hr.save') }}
                                    @endif
                                </span>
                                <span wire:loading wire:target="image">
                                    <i class="las la-spinner la-spin me-1"></i> {{ __('hr.uploading') }}...
                                </span>
                                <span wire:loading wire:target="save">
                                    <i class="las la-spinner la-spin me-1"></i> {{ __('hr.saving') }}...
                                </span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- View Modal - Alpine.js -->
    <div x-data="{ show: @entangle('showViewModal') }" 
         x-show="show"
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         x-cloak
         class="fixed inset-0 z-50 overflow-y-auto"
         style="display: none;">
        <!-- Backdrop -->
        <div class="fixed inset-0" style="background-color: rgba(0, 0, 0, 0.5);"
             @click="show = false"></div>
        
        <!-- Modal -->
        <div class="d-flex align-items-center justify-content-center" style="min-height: 100vh; padding: 1rem;">
            <div class="bg-white rounded-lg shadow-xl max-w-2xl w-full relative z-10"
                 x-transition:enter="transition ease-out duration-300"
                 x-transition:enter-start="opacity-0 transform scale-95"
                 x-transition:enter-end="opacity-100 transform scale-100"
                 x-transition:leave="transition ease-in duration-200"
                 x-transition:leave-start="opacity-100 transform scale-100"
                 x-transition:leave-end="opacity-0 transform scale-95"
                 @click.stop>
                <div class="modal-header border-bottom p-3">
                    <h5 class="modal-title mb-0">{{ __('hr.covenant_details') }}</h5>
                    <button type="button" class="btn-close" @click="show = false" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    @php
                        $covenant = $this->viewCovenant;
                    @endphp
                    @if($covenant)
                        <div class="row g-3">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label fw-semibold text-muted mb-1">{{ __('hr.name') }}</label>
                                    <div class="p-2 bg-light rounded">
                                        {{ $covenant->name }}
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label fw-semibold text-muted mb-1">{{ __('hr.employee') }}</label>
                                    <div class="p-2 bg-light rounded">
                                        @if($covenant->employee)
                                            <span class="badge bg-info-subtle text-info">{{ $covenant->employee->name }}</span>
                                        @else
                                            <span class="text-muted">{{ __('hr.no_employee') }}</span>
                                        @endif
                                    </div>
                                </div>
                            </div>
                            <div class="col-12">
                                <div class="mb-3">
                                    <label class="form-label fw-semibold text-muted mb-1">{{ __('hr.description') }}</label>
                                    <div class="p-2 bg-light rounded min-h-100">
                                        {{ $covenant->description ?? __('hr.no_description') }}
                                    </div>
                                </div>
                            </div>
                            @php
                                $media = $covenant->getFirstMedia('HR_Covenants');
                            @endphp
                            <div class="col-12">
                                <div class="mb-3">
                                    <label class="form-label fw-semibold text-muted mb-1">{{ __('hr.image') }}</label>
                                    @if($covenant->image_url && $media)
                                        <div class="mt-2">
                                            <div class="text-center p-3 bg-light rounded position-relative">
                                                <img src="{{ $covenant->image_url }}" 
                                                     alt="{{ $covenant->name }}" 
                                                     class="img-fluid rounded shadow-sm" 
                                                     style="max-height: 400px; max-width: 100%; object-fit: contain; border: 1px solid #dee2e6; display: none;"
                                                     onload="
                                                         this.style.display = 'block';
                                                         if (this.nextElementSibling) this.nextElementSibling.style.display = 'none';
                                                         if (this.nextElementSibling && this.nextElementSibling.nextElementSibling) this.nextElementSibling.nextElementSibling.style.display = 'none';
                                                     "
                                                     onerror="
                                                         this.style.display = 'none';
                                                         if (this.nextElementSibling) this.nextElementSibling.style.display = 'none';
                                                         if (this.nextElementSibling && this.nextElementSibling.nextElementSibling) this.nextElementSibling.nextElementSibling.style.display = 'block';
                                                     ">
                                                <!-- Loading indicator -->
                                                <div style="display: block; text-align: center; padding: 40px; min-height: 200px; display: flex; align-items: center; justify-content: center;">
                                                    <div class="spinner-border text-primary" role="status">
                                                        <span class="visually-hidden">{{ __('hr.loading') }}...</span>
                                                    </div>
                                                </div>
                                                <!-- Placeholder (shown on error) -->
                                                <div style="display: none; text-align: center; padding: 40px; min-height: 200px; display: flex; align-items: center; justify-content: center; flex-direction: column;">
                                                    <i class="las la-image text-muted" style="font-size: 3rem;"></i>
                                                    <p class="text-muted mb-0 mt-2">{{ __('hr.image_not_available') }}</p>
                                                </div>
                                            </div>
                                            <div class="mt-3 d-flex align-items-center gap-2">
                                                <button wire:click="downloadImage({{ $covenant->id }})" 
                                                        class="btn btn-sm btn-primary">
                                                    <i class="las la-download me-1"></i> {{ __('hr.download_image') }}
                                                </button>
                                                <small class="text-muted">
                                                    <i class="las la-file me-1"></i> {{ $media->file_name }}
                                                </small>
                                            </div>
                                        </div>
                                    @else
                                        <div class="p-3 bg-light rounded text-center">
                                            <i class="las la-image text-muted" style="font-size: 3rem;"></i>
                                            <p class="text-muted mb-0 mt-2">{{ __('hr.no_image_uploaded') }}</p>
                                        </div>
                                    @endif
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label fw-semibold text-muted mb-1">{{ __('hr.created_at') }}</label>
                                    <div class="p-2 bg-light rounded">
                                        <i class="las la-calendar me-1"></i> {{ $covenant->created_at->format('Y-m-d H:i') }}
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label fw-semibold text-muted mb-1">{{ __('hr.updated_at') }}</label>
                                    <div class="p-2 bg-light rounded">
                                        <i class="las la-calendar me-1"></i> {{ $covenant->updated_at->format('Y-m-d H:i') }}
                                    </div>
                                </div>
                            </div>
                        </div>
                    @else
                        <div class="text-center py-5">
                            <div class="spinner-border text-primary" role="status">
                                <span class="visually-hidden">{{ __('hr.loading') }}...</span>
                            </div>
                            <p class="text-muted mt-3">{{ __('hr.loading_data') }}...</p>
                        </div>
                    @endif
                </div>
                <div class="modal-footer border-top p-3">
                    <button type="button" class="btn btn-secondary" @click="show = false">{{ __('hr.close') }}</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Delete Confirmation Modal - Alpine.js -->
    <div x-data="{ show: @entangle('showDeleteModal') }" 
         x-show="show"
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         x-cloak
         class="fixed inset-0 z-50 overflow-y-auto"
         style="display: none;"
         wire:ignore>
        <!-- Backdrop -->
        <div class="fixed inset-0" style="background-color: rgba(0, 0, 0, 0.5);"
             @click="show = false"></div>
        
        <!-- Modal -->
        <div class="d-flex align-items-center justify-content-center" style="min-height: 100vh; padding: 1rem;">
            <div class="bg-white rounded-lg shadow-xl max-w-md w-full relative z-10"
                 x-transition:enter="transition ease-out duration-300"
                 x-transition:enter-start="opacity-0 transform scale-95"
                 x-transition:enter-end="opacity-100 transform scale-100"
                 x-transition:leave="transition ease-in duration-200"
                 x-transition:leave-start="opacity-100 transform scale-100"
                 x-transition:leave-end="opacity-0 transform scale-95"
                 @click.stop>
                <div class="modal-header border-bottom p-3">
                    <h5 class="modal-title mb-0">{{ __('hr.confirm_delete_covenant') }}</h5>
                    <button type="button" class="btn-close" @click="show = false" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <p>{{ __('hr.confirm_delete_covenant_message') }}</p>
                </div>
                <div class="modal-footer border-top p-3">
                    <button type="button" class="btn btn-secondary" @click="show = false">{{ __('hr.cancel') }}</button>
                    <button type="button" class="btn btn-danger" wire:click="confirmDelete" wire:loading.attr="disabled">
                        <span wire:loading.remove>
                            <i class="las la-trash me-1"></i> {{ __('hr.delete') }}
                        </span>
                        <span wire:loading>
                            <i class="las la-spinner la-spin me-1"></i> {{ __('hr.deleting') }}...
                        </span>
                    </button>
                </div>
            </div>
        </div>
    </div>

</div>

