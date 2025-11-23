<?php

declare(strict_types=1);

use Livewire\Volt\Component;
use App\Models\Cv;
use Livewire\WithPagination;
use Livewire\WithFileUploads;
use Livewire\Attributes\Rule;
use Livewire\Attributes\Computed;
use Livewire\Attributes\On;
use Illuminate\Support\Facades\Storage;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

new class extends Component {
    use WithPagination, WithFileUploads;
    protected string $paginationTheme = 'bootstrap';

    // Properties for form fields
    #[Rule('required|string|max:255')]
    public $name = '';

    #[Rule('nullable|email|max:255')]
    public $email = '';

    #[Rule('required|string|max:20')]
    public $phone = '';

    #[Rule('nullable|string|max:100')]
    public $country = '';

    #[Rule('nullable|string|max:100')]
    public $state = '';

    #[Rule('nullable|string|max:100')]
    public $city = '';

    #[Rule('nullable|string|max:500')]
    public $address = '';

    #[Rule('required|string|max:20')]
    public $birth_date = '';

    #[Rule('required|string|in:male,female,other')]
    public $gender = '';

    #[Rule('required|string|in:single,married,divorced,widowed')]
    public $marital_status = '';

    #[Rule('required|string|max:100')]
    public $nationality = '';

    #[Rule('required|string|max:100')]
    public $religion = '';

    #[Rule('nullable|string|max:1000')]
    public $summary = '';

    #[Rule('nullable|string|max:1000')]
    public $skills = '';

    #[Rule('nullable|string|max:2000')]
    public $experience = '';

    #[Rule('nullable|string|max:2000')]
    public $education = '';

    #[Rule('nullable|string|max:2000')]
    public $projects = '';

    #[Rule('nullable|string|max:1000')]
    public $certifications = '';

    #[Rule('nullable|string|max:500')]
    public $languages = '';

    #[Rule('nullable|string|max:500')]
    public $interests = '';

    #[Rule('nullable|string|max:1000')]
    public $references = '';

    #[Rule('nullable|string|max:2000')]
    public $cover_letter = '';

    #[Rule('nullable|string|max:255')]
    public $portfolio = '';

    // UI state properties
    #[Rule('nullable|file|mimes:pdf,doc,docx|max:10240')]
    public mixed $cv_file = null;
    public string $search = '';
    public string $filter_gender = '';
    public string $filter_marital_status = '';
    public string $filter_nationality = '';
    public bool $showModal = false;
    public ?Cv $editingCv = null;
    public ?int $deleteId = null;
    public bool $showDeleteModal = false;
    public ?Cv $viewCv = null;
    public bool $showViewModal = false;

    protected array $queryString = ['search'];

    public function mount(): void
    {
        $this->resetForm();
    }

    public function resetForm(): void
    {
        $this->reset([
            'name', 'email', 'phone', 'country', 'state', 'city', 'address',
            'birth_date', 'gender', 'marital_status', 'nationality', 'religion',
            'summary', 'skills', 'experience', 'education', 'projects',
            'certifications', 'languages', 'interests', 'references',
            'cover_letter', 'portfolio', 'cv_file', 'editingCv'
        ]);
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function create(): void
    {
        $this->resetForm();
        $this->showModal = true;
        $this->dispatch('openModal', 'createModal');
    }

    public function store()
    {
        $this->validate();

        $cv = Cv::create([
            'name' => $this->name,
            'email' => $this->email,
            'phone' => $this->phone,
            'country' => $this->country,
            'state' => $this->state,
            'city' => $this->city,
            'address' => $this->address,
            'birth_date' => $this->birth_date,
            'gender' => $this->gender,
            'marital_status' => $this->marital_status,
            'nationality' => $this->nationality,
            'religion' => $this->religion,
            'summary' => $this->summary,
            'skills' => $this->skills,
            'experience' => $this->experience,
            'education' => $this->education,
            'projects' => $this->projects,
            'certifications' => $this->certifications,
            'languages' => $this->languages,
            'interests' => $this->interests,
            'references' => $this->references,
            'cover_letter' => $this->cover_letter,
            'portfolio' => $this->portfolio,
        ]);

        // Handle file upload
        if ($this->cv_file) {
            $cv->addMediaFromStream($this->cv_file->readStream())
               ->usingName($this->cv_file->getClientOriginalName())
               ->usingFileName($this->cv_file->getClientOriginalName())
                ->toMediaCollection('HR_Cvs');
        }

        $this->resetForm();
        $this->showModal = false;
        session()->flash('message', __('hr.cv_created_successfully'));
        $this->dispatch('closeModal', 'createModal');
    }

    public function edit(int $id): void
    {
        $this->editingCv = Cv::findOrFail($id);
        $this->name = $this->editingCv->name;
        $this->email = $this->editingCv->email;
        $this->phone = $this->editingCv->phone;
        $this->country = $this->editingCv->country;
        $this->state = $this->editingCv->state;
        $this->city = $this->editingCv->city;
        $this->address = $this->editingCv->address;
        $this->birth_date = $this->editingCv->birth_date;
        $this->gender = $this->editingCv->gender;
        $this->marital_status = $this->editingCv->marital_status;
        $this->nationality = $this->editingCv->nationality;
        $this->religion = $this->editingCv->religion;
        $this->summary = $this->editingCv->summary;
        $this->skills = $this->editingCv->skills;
        $this->experience = $this->editingCv->experience;
        $this->education = $this->editingCv->education;
        $this->projects = $this->editingCv->projects;
        $this->certifications = $this->editingCv->certifications;
        $this->languages = $this->editingCv->languages;
        $this->interests = $this->editingCv->interests;
        $this->references = $this->editingCv->references;
        $this->cover_letter = $this->editingCv->cover_letter;
        $this->portfolio = $this->editingCv->portfolio;
        $this->showModal = true;
        $this->dispatch('openModal', 'editModal');
    }

    public function update()
    {
        $this->validate();

        $this->editingCv->update([
            'name' => $this->name,
            'email' => $this->email,
            'phone' => $this->phone,
            'country' => $this->country,
            'state' => $this->state,
            'city' => $this->city,
            'address' => $this->address,
            'birth_date' => $this->birth_date,
            'gender' => $this->gender,
            'marital_status' => $this->marital_status,
            'nationality' => $this->nationality,
            'religion' => $this->religion,
            'summary' => $this->summary,
            'skills' => $this->skills,
            'experience' => $this->experience,
            'education' => $this->education,
            'projects' => $this->projects,
            'certifications' => $this->certifications,
            'languages' => $this->languages,
            'interests' => $this->interests,
            'references' => $this->references,
            'cover_letter' => $this->cover_letter,
            'portfolio' => $this->portfolio,
        ]);

        // Handle file upload
        if ($this->cv_file) {
            // Remove old file
            $this->editingCv->clearMediaCollection('HR_Cvs');
            
            // Add new file
            $this->editingCv->addMediaFromStream($this->cv_file->readStream())
                           ->usingName($this->cv_file->getClientOriginalName())
                           ->usingFileName($this->cv_file->getClientOriginalName())
                ->toMediaCollection('HR_Cvs');
        }

        $this->resetForm();
        $this->showModal = false;
        session()->flash('message', __('hr.cv_updated_successfully'));
        $this->dispatch('closeModal', 'editModal');
    }

    public function view(int $id): void
    {
        $this->viewCv = Cv::findOrFail($id);
        $this->showViewModal = true;
        $this->dispatch('openModal', 'viewModal');
    }

    public function delete(int $id): void
    {
        $this->deleteId = $id;
        $this->showDeleteModal = true;
        $this->dispatch('openModal', 'deleteModal');
    }

    public function confirmDelete(): void
    {
        $cv = Cv::findOrFail($this->deleteId);
        $cv->clearMediaCollection('HR_Cvs');
        $cv->delete();
        
        $this->showDeleteModal = false;
        $this->deleteId = null;
        session()->flash('message', __('hr.cv_deleted_successfully'));
        $this->dispatch('closeModal', 'deleteModal');
    }

    public function downloadCv(int $id)
    {
        $cv = Cv::findOrFail($id);
        $media = $cv->getFirstMedia('HR_Cvs');
        
        if ($media) {
            return response()->download($media->getPath(), $media->file_name);
        }
        
        session()->flash('error', __('hr.cv_file_not_found'));
    }

    public function clearFilters(): void
    {
        $this->reset(['search', 'filter_gender', 'filter_marital_status', 'filter_nationality']);
    }

    #[Computed]
    public function cvs(): LengthAwarePaginator
    {
        return Cv::query()
            ->when($this->search, function ($query) {
                $query->where(function ($q) {
                    $q->where('name', 'like', '%' . $this->search . '%')
                      ->orWhere('email', 'like', '%' . $this->search . '%')
                      ->orWhere('phone', 'like', '%' . $this->search . '%')
                      ->orWhere('nationality', 'like', '%' . $this->search . '%');
                });
            })
            ->when($this->filter_gender, function ($query) {
                $query->where('gender', $this->filter_gender);
            })
            ->when($this->filter_marital_status, function ($query) {
                $query->where('marital_status', $this->filter_marital_status);
            })
            ->when($this->filter_nationality, function ($query) {
                $query->where('nationality', 'like', '%' . $this->filter_nationality . '%');
            })
            ->latest()
            ->paginate(10);
    }

    #[Computed]
    public function genderOptions(): array
    {
        return [
            'male' => __('hr.male'),
            'female' => __('hr.female'),
        ];
    }

    #[Computed]
    public function maritalStatusOptions(): array
    {
        return [
            'single' => __('hr.single'),
            'married' => __('hr.married'),
            'divorced' => __('hr.divorced'),
            'widowed' => __('hr.widowed')
        ];
    }

    #[Computed]
    public function nationalityOptions(): array
    {
        return Cv::distinct()->pluck('nationality')->filter()->values()->toArray();
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
                    <i class="mdi mdi-file-document-multiple text-primary" style="font-size: 2.5rem;"></i>
                </div>
                <div>
                    <h4 class="mb-1">{{ __('hr.cv_management') }}</h4>
                    <p class="text-muted mb-0">{{ __('hr.manage_and_review_candidate_cvs') }}</p>
                </div>
            </div>
        </div>
        <div class="col-md-6 text-end">
            @can('create CVs')
                <button wire:click="create" 
                        class="btn btn-primary btn-lg shadow-sm"
                        wire:loading.attr="disabled"
                        wire:target="create">
                    <span wire:loading.remove wire:target="create">
                    <i class="mdi mdi-plus me-2"></i> {{ __('hr.add_new_cv') }}
                    </span>
                    <span wire:loading wire:target="create">
                        <i class="mdi mdi-loading mdi-spin me-2"></i> {{ __('hr.opening') }}...
                    </span>
                </button>
            @endcan
        </div>
    </div>

    <!-- Filters -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-3">
                    <div class="position-relative">
                        <i class="mdi mdi-magnify position-absolute top-50 start-0 translate-middle-y ms-3 text-muted"></i>
                        <input wire:model.live.debounce.300ms="search" type="text" class="form-control ps-5" placeholder="{{ __('hr.search_cvs') }}">
                    </div>
                </div>
                <div class="col-md-2">
                    <select wire:model.live="filter_gender" class="form-select">
                        <option value="">{{ __('hr.all_genders') }}</option>
                        @foreach($this->genderOptions as $value => $label)
                            <option value="{{ $value }}">{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <select wire:model.live="filter_marital_status" class="form-select">
                        <option value="">{{ __('hr.all_status') }}</option>
                        @foreach($this->maritalStatusOptions as $value => $label)
                            <option value="{{ $value }}">{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <input wire:model.live="filter_nationality" type="text" class="form-control" placeholder="{{ __('hr.filter_by_nationality') }}">
                </div>
                <div class="col-md-2">
                    <button wire:click="clearFilters" class="btn btn-outline-secondary w-100">
                        <i class="mdi mdi-filter-remove me-1"></i> {{ __('hr.clear') }}
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- CVs Table -->
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white border-0 py-3">
            <div class="d-flex justify-content-between align-items-center">
                <h6 class="mb-0">
                    <i class="mdi mdi-format-list-bulleted me-2"></i>{{ __('hr.cvs_list') }}
                    <span class="badge bg-primary ms-2">{{ $this->cvs->total() }}</span>
            </h6>
                <div class="d-flex align-items-center text-muted">
                    <small>{{ __('hr.showing') }} {{ $this->cvs->firstItem() ?? 0 }} {{ __('hr.to') }} {{ $this->cvs->lastItem() ?? 0 }} {{ __('hr.of') }} {{ $this->cvs->total() }} {{ __('hr.results') }}</small>
                </div>
            </div>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="border-0 fw-semibold">{{ __('hr.name') }}</th>
                            <th class="border-0 fw-semibold">{{ __('hr.contact') }}</th>
                            <th class="border-0 fw-semibold">{{ __('hr.location') }}</th>
                            <th class="border-0 fw-semibold">{{ __('hr.personal_info') }}</th>
                            <th class="border-0 fw-semibold">{{ __('hr.cv_file') }}</th>
                            <th class="border-0 fw-semibold">{{ __('hr.created_at') }}</th>
                            @canany(['view CVs', 'edit CVs', 'delete CVs'])
                                <th class="border-0 fw-semibold text-center">{{ __('hr.actions') }}</th>
                            @endcanany
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($this->cvs as $cv)
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="avatar-sm me-3">
                                            <div class="avatar-title bg-primary text-white rounded-circle">
                                                {{ strtoupper(substr($cv->name, 0, 2)) }}
                                            </div>
                                        </div>
                                        <div>
                                            <h6 class="mb-0">{{ $cv->name }}</h6>
                                            <small class="text-muted">{{ ucfirst($cv->gender) }}</small>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <div>
                                        @if($cv->email)
                                            <div class="mb-1">
                                                <i class="mdi mdi-email-outline me-1 text-muted"></i>
                                                <small>{{ $cv->email }}</small>
                                            </div>
                                        @endif
                                        <div>
                                            <i class="mdi mdi-phone-outline me-1 text-muted"></i>
                                            <small>{{ $cv->phone }}</small>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <div>
                                        @if($cv->city || $cv->country)
                                            <div class="mb-1">
                                                <i class="mdi mdi-map-marker-outline me-1 text-muted"></i>
                                                <small>{{ $cv->city }}{{ $cv->city && $cv->country ? ', ' : '' }}{{ $cv->country }}</small>
                                            </div>
                                        @endif
                                        <div>
                                            <i class="mdi mdi-flag-outline me-1 text-muted"></i>
                                            <small>{{ $cv->nationality }}</small>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <div>
                                        <div class="mb-1">
                                            <small class="text-muted">{{ $cv->birth_date }}</small>
                                        </div>
                                        <div>
                                            <span class="badge bg-{{ $cv->marital_status === 'single' ? 'success' : ($cv->marital_status === 'married' ? 'info' : 'secondary') }}-subtle text-{{ $cv->marital_status === 'single' ? 'success' : ($cv->marital_status === 'married' ? 'info' : 'secondary') }}">
                                                {{ ucfirst($cv->marital_status) }}
                                    </span>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    @if($cv->getFirstMedia('HR_Cvs'))
                                        <button wire:click="downloadCv({{ $cv->id }})" class="btn btn-sm btn-outline-primary" title="{{ __('hr.download_cv') }}">
                                            <i class="mdi mdi-download"></i>
                                        </button>
                                    @else
                                        <span class="text-muted">
                                            <i class="mdi mdi-file-document-outline"></i> {{ __('hr.no_file') }}
                                        </span>
                                    @endif
                                </td>
                                <td>{{ $cv->created_at->format('M d, Y') }}</td>
                                @canany(['view CVs', 'edit CVs', 'delete CVs'])
                                    <td class="text-center">
                                        <div class="d-flex justify-content-center gap-1">
                                            @can('view CVs')
                                                <button wire:click="view({{ $cv->id }})" 
                                                        class="btn btn-sm btn-outline-info" 
                                                        title="{{ __('hr.view') }}"
                                                        wire:loading.attr="disabled"
                                                        wire:target="view({{ $cv->id }})">
                                                    <span wire:loading.remove wire:target="view({{ $cv->id }})">
                                                        <i class="mdi mdi-eye"></i>
                                                    </span>
                                                    <span wire:loading wire:target="view({{ $cv->id }})">
                                                        <i class="mdi mdi-loading mdi-spin"></i>
                                                    </span>
                                                </button>
                                            @endcan
                                            @can('edit CVs')
                                                <button wire:click="edit({{ $cv->id }})" 
                                                        class="btn btn-sm btn-outline-warning" 
                                                        title="{{ __('hr.edit') }}"
                                                        wire:loading.attr="disabled"
                                                        wire:target="edit({{ $cv->id }})">
                                                    <span wire:loading.remove wire:target="edit({{ $cv->id }})">
                                                        <i class="mdi mdi-pencil"></i>
                                                    </span>
                                                    <span wire:loading wire:target="edit({{ $cv->id }})">
                                                        <i class="mdi mdi-loading mdi-spin"></i>
                                                    </span>
                                                </button>
                                            @endcan
                                            @can('delete CVs')
                                                <button wire:click="delete({{ $cv->id }})" 
                                                        wire:confirm="{{ __('hr.confirm_delete_cv') }}"
                                                        class="btn btn-sm btn-outline-danger" 
                                                        title="{{ __('hr.delete') }}"
                                                        wire:loading.attr="disabled"
                                                        wire:target="delete({{ $cv->id }})">
                                                    <span wire:loading.remove wire:target="delete({{ $cv->id }})">
                                                        <i class="mdi mdi-delete"></i>
                                                    </span>
                                                    <span wire:loading wire:target="delete({{ $cv->id }})">
                                                        <i class="mdi mdi-loading mdi-spin"></i>
                                                    </span>
                                                </button>
                                            @endcan
                                        </div>
                                    </td>
                                @endcanany
                            </tr>
                        @empty
                            <tr>
                                <td colspan="@canany(['view CVs', 'edit CVs', 'delete CVs'])7@else6@endcanany" class="text-center py-5">
                                    <div class="text-muted">
                                        <i class="mdi mdi-file-document-outline text-muted" style="font-size: 4rem;"></i>
                                        <h5 class="mt-3 mb-2">{{ __('hr.no_cvs_found') }}</h5>
                                        <p class="mb-3">{{ __('hr.start_by_adding_first_cv') }}</p>
                                        @can('create CVs')
                                            <button wire:click="create" 
                                                    class="btn btn-primary"
                                                    wire:loading.attr="disabled"
                                                    wire:target="create">
                                                <span wire:loading.remove wire:target="create">
                                                <i class="mdi mdi-plus me-2"></i> {{ __('hr.add_first_cv') }}
                                                </span>
                                                <span wire:loading wire:target="create">
                                                    <i class="mdi mdi-loading mdi-spin me-2"></i> {{ __('hr.opening') }}...
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
        @if($this->cvs->hasPages())
            <div class="card-footer bg-white border-0">
                    {{ $this->cvs->links() }}
                                        </div>
                                    @endif
                        </div>

    <!-- Modal Components -->
    <x-modals.create-cv-modal />
    <x-modals.edit-cv-modal :editing-cv="$editingCv" />
    <x-modals.view-cv-modal :view-cv="$viewCv" />
    <x-modals.delete-cv-modal />

    <style>
        .avatar-sm {
            width: 40px;
            height: 40px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
            font-weight: 600;
            font-size: 14px;
        }
        
        .avatar-lg {
            width: 60px;
            height: 60px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
            font-weight: 600;
            font-size: 20px;
        }
        
        .avatar-title {
            width: 100%;
            height: 100%;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: inherit;
        }
        
        .modal-fullscreen .modal-body {
            overflow-y: auto;
            max-height: calc(100vh - 120px);
        }
        
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        
        .mdi-spin {
            animation: spin 1s linear infinite;
        }
        
        button[disabled] {
            opacity: 0.6;
            cursor: not-allowed;
        }
    </style>

    <script>
        document.addEventListener('livewire:init', () => {
            let openModals = new Set();
            
            // Listen for modal open events
            Livewire.on('openModal', (modalId) => {
                // Close any open modals first
                openModals.forEach(id => {
                    const existingModal = document.getElementById(id);
                    if (existingModal) {
                        const existingBsModal = bootstrap.Modal.getInstance(existingModal);
                        if (existingBsModal) {
                            existingBsModal.hide();
                        }
                    }
                });
                openModals.clear();
                
                // Small delay to ensure Livewire data is ready
                setTimeout(() => {
                    const modal = document.getElementById(modalId);
                    if (modal && typeof bootstrap !== 'undefined' && bootstrap.Modal) {
                        // Check if modal is already open
                        let bsModal = bootstrap.Modal.getInstance(modal);
                        if (!bsModal) {
                            bsModal = new bootstrap.Modal(modal, {
                                backdrop: 'static',
                                keyboard: false
                            });
                        }
                        
                        if (!modal.classList.contains('show')) {
                            bsModal.show();
                            openModals.add(modalId);
                        }
                    }
                }, 100);
            });

            // Listen for modal close events
            Livewire.on('closeModal', (modalId) => {
                const modal = document.getElementById(modalId);
                if (modal && typeof bootstrap !== 'undefined' && bootstrap.Modal) {
                    const bsModal = bootstrap.Modal.getInstance(modal);
                    if (bsModal && modal.classList.contains('show')) {
                        bsModal.hide();
                        openModals.delete(modalId);
                    }
                }
            });

            // Handle modal hidden events
            document.addEventListener('hidden.bs.modal', function (event) {
                const modalId = event.target.id;
                openModals.delete(modalId);
                
                // Reset any backdrop issues
                const backdrops = document.querySelectorAll('.modal-backdrop');
                backdrops.forEach(backdrop => backdrop.remove());
                document.body.classList.remove('modal-open');
                document.body.style.overflow = '';
                document.body.style.paddingRight = '';
            });
        });

        // Prevent double clicks on buttons
        document.addEventListener('click', function(e) {
            if (e.target.closest('button[wire\\:click]')) {
                const button = e.target.closest('button');
                if (button.disabled) return false;
                
                button.disabled = true;
                setTimeout(() => {
                    button.disabled = false;
                }, 1000);
            }
        });
    </script>

</div> 