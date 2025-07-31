<?php

use Livewire\Volt\Component;
use App\Models\Cv;
use Livewire\WithPagination;
use Livewire\WithFileUploads;
use Livewire\Attributes\Rule;
use Livewire\Attributes\On;
use Illuminate\Support\Facades\Storage;

new class extends Component {
    use WithPagination, WithFileUploads;

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
    public $cv_file;
    public $search = '';
    public $filter_gender = '';
    public $filter_marital_status = '';
    public $filter_nationality = '';
    public $showModal = false;
    public $editingCv = null;
    public $deleteId = null;
    public $showDeleteModal = false;
    public $viewCv = null;
    public $showViewModal = false;

    protected $queryString = ['search'];

    public function mount()
    {
        $this->resetForm();
    }

    public function resetForm()
    {
        $this->reset([
            'name', 'email', 'phone', 'country', 'state', 'city', 'address',
            'birth_date', 'gender', 'marital_status', 'nationality', 'religion',
            'summary', 'skills', 'experience', 'education', 'projects',
            'certifications', 'languages', 'interests', 'references',
            'cover_letter', 'portfolio', 'cv_file', 'editingCv'
        ]);
    }

    public function create()
    {
        $this->resetForm();
        $this->showModal = true;
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

        if ($this->cv_file) {
            $cv->addMedia($this->cv_file)
                ->toMediaCollection('HR_Cvs');
        }

        $this->showModal = false;
        $this->resetForm();
        $this->dispatch('cv-created');
        session()->flash('message', 'CV created successfully.');
    }

    public function edit($id)
    {
        $cv = Cv::findOrFail($id);
        $this->editingCv = $cv;
        
        $this->name = $cv->name;
        $this->email = $cv->email;
        $this->phone = $cv->phone;
        $this->country = $cv->country;
        $this->state = $cv->state;
        $this->city = $cv->city;
        $this->address = $cv->address;
        $this->birth_date = $cv->birth_date;
        $this->gender = $cv->gender;
        $this->marital_status = $cv->marital_status;
        $this->nationality = $cv->nationality;
        $this->religion = $cv->religion;
        $this->summary = $cv->summary;
        $this->skills = $cv->skills;
        $this->experience = $cv->experience;
        $this->education = $cv->education;
        $this->projects = $cv->projects;
        $this->certifications = $cv->certifications;
        $this->languages = $cv->languages;
        $this->interests = $cv->interests;
        $this->references = $cv->references;
        $this->cover_letter = $cv->cover_letter;
        $this->portfolio = $cv->portfolio;
        
        $this->showModal = true;
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

        if ($this->cv_file) {
            $this->editingCv->clearMediaCollection('HR_Cvs');
            $this->editingCv->addMedia($this->cv_file)
                ->toMediaCollection('HR_Cvs');
        }

        $this->showModal = false;
        $this->resetForm();
        $this->dispatch('cv-updated');
        session()->flash('message', 'CV updated successfully.');
    }

    public function delete($id)
    {
        $this->deleteId = $id;
        $this->showDeleteModal = true;
    }

    public function confirmDelete()
    {
        $cv = Cv::findOrFail($this->deleteId);
        $cv->delete();
        
        $this->showDeleteModal = false;
        $this->deleteId = null;
        $this->dispatch('cv-deleted');
        session()->flash('message', 'CV deleted successfully.');
    }

    public function view($id)
    {
        $this->viewCv = Cv::findOrFail($id);
        $this->showViewModal = true;
    }

    public function downloadCv($id)
    {
        $cv = Cv::findOrFail($id);
        $media = $cv->getFirstMedia('HR_Cvs');
        
        if ($media) {
            return response()->download($media->getPath(), $media->file_name);
        }
        
        session()->flash('error', 'No CV file found.');
    }

    public function updatedSearch()
    {
        $this->resetPage();
    }

    public function updatedFilterGender()
    {
        $this->resetPage();
    }

    public function updatedFilterMaritalStatus()
    {
        $this->resetPage();
    }

    public function updatedFilterNationality()
    {
        $this->resetPage();
    }

    public function clearFilters()
    {
        $this->reset(['search', 'filter_gender', 'filter_marital_status', 'filter_nationality']);
        $this->resetPage();
    }

    public function getCvsProperty()
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
            ->orderBy('created_at', 'desc')
            ->paginate(10);
    }

    public function getGenderOptionsProperty()
    {
        return [
            'male' => 'Male',
            'female' => 'Female',
            'other' => 'Other'
        ];
    }

    public function getMaritalStatusOptionsProperty()
    {
        return [
            'single' => 'Single',
            'married' => 'Married',
            'divorced' => 'Divorced',
            'widowed' => 'Widowed'
        ];
    }

    public function getNationalityOptionsProperty()
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
                    <h4 class="mb-1">CV Management</h4>
                    <p class="text-muted mb-0">Manage and review candidate CVs</p>
                </div>
            </div>
        </div>
        <div class="col-md-6 text-end">
            <button wire:click="create" class="btn btn-primary btn-lg shadow-sm" onclick="setTimeout(() => { const modal = new bootstrap.Modal(document.getElementById('cvModal')); modal.show(); }, 100);">
                <i class="mdi mdi-plus me-2"></i> Add New CV
            </button>
        </div>
    </div>

    <!-- Filters -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-white border-0">
            <h6 class="mb-0 text-primary">
                <i class="mdi mdi-filter-outline me-2"></i>Search & Filters
            </h6>
        </div>
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-3">
                    <label for="search" class="form-label fw-semibold">Search</label>
                    <div class="input-group">
                        <span class="input-group-text bg-light border-end-0">
                            <i class="mdi mdi-magnify"></i>
                        </span>
                        <input wire:model.live="search" type="text" class="form-control border-start-0" placeholder="Search by name, email, phone...">
                    </div>
                </div>
                <div class="col-md-3">
                    <label for="filter_gender" class="form-label fw-semibold">Gender</label>
                    <select wire:model.live="filter_gender" class="form-select">
                        <option value="">All Genders</option>
                        @foreach($this->genderOptions as $value => $label)
                            <option value="{{ $value }}">{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label for="filter_marital_status" class="form-label fw-semibold">Marital Status</label>
                    <select wire:model.live="filter_marital_status" class="form-select">
                        <option value="">All Status</option>
                        @foreach($this->maritalStatusOptions as $value => $label)
                            <option value="{{ $value }}">{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label for="filter_nationality" class="form-label fw-semibold">Nationality</label>
                    <select wire:model.live="filter_nationality" class="form-select">
                        <option value="">All Nationalities</option>
                        @foreach($this->nationalityOptions as $nationality)
                            <option value="{{ $nationality }}">{{ $nationality }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="row mt-3">
                <div class="col-12">
                    <button wire:click="clearFilters" class="btn btn-outline-secondary">
                        <i class="mdi mdi-refresh me-1"></i> Clear Filters
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- CVs Table -->
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white border-0">
            <h6 class="mb-0 text-primary">
                <i class="mdi mdi-table me-2"></i>CV Records
            </h6>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="border-0">Name</th>
                            <th class="border-0">Email</th>
                            <th class="border-0">Phone</th>
                            <th class="border-0">Nationality</th>
                            <th class="border-0">Gender</th>
                            <th class="border-0">CV File</th>
                            <th class="border-0">Created</th>
                            <th class="border-0 text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($this->cvs as $cv)
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="avatar-sm me-3">
                                            <div class="avatar-title bg-light rounded">
                                                {{ strtoupper(substr($cv->name, 0, 2)) }}
                                            </div>
                                        </div>
                                        <div>
                                            <h6 class="mb-0">{{ $cv->name }}</h6>
                                            <small class="text-muted">{{ $cv->city }}, {{ $cv->country }}</small>
                                        </div>
                                    </div>
                                </td>
                                <td>{{ $cv->email ?: 'N/A' }}</td>
                                <td>{{ $cv->phone }}</td>
                                <td>{{ $cv->nationality }}</td>
                                <td>
                                    <span class="badge bg-{{ $cv->gender === 'male' ? 'primary' : ($cv->gender === 'female' ? 'danger' : 'secondary') }}">
                                        {{ ucfirst($cv->gender) }}
                                    </span>
                                </td>
                                <td>
                                    @if($cv->getFirstMedia('HR_Cvs'))
                                        <button wire:click="downloadCv({{ $cv->id }})" class="btn btn-sm btn-outline-primary">
                                            <i class="mdi mdi-download"></i> Download
                                        </button>
                                    @else
                                        <span class="text-muted">No file</span>
                                    @endif
                                </td>
                                <td>{{ $cv->created_at->format('M d, Y') }}</td>
                                <td class="text-center">
                                    <div class="d-flex justify-content-center gap-1">
                                        <button wire:click="view({{ $cv->id }})" class="btn btn-sm btn-outline-info" title="View Details" onclick="setTimeout(() => { const modal = new bootstrap.Modal(document.getElementById('viewCvModal')); modal.show(); }, 100);">
                                            <i class="mdi mdi-eye"></i>
                                        </button>
                                        <button wire:click="edit({{ $cv->id }})" class="btn btn-sm btn-outline-warning" title="Edit CV" onclick="setTimeout(() => { const modal = new bootstrap.Modal(document.getElementById('cvModal')); modal.show(); }, 100);">
                                            <i class="mdi mdi-pencil"></i>
                                        </button>
                                        <button wire:click="delete({{ $cv->id }})" class="btn btn-sm btn-outline-danger" title="Delete CV" onclick="setTimeout(() => { const modal = new bootstrap.Modal(document.getElementById('deleteModal')); modal.show(); }, 100);">
                                            <i class="mdi mdi-delete"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center py-5">
                                    <div class="text-muted">
                                        <i class="mdi mdi-file-document-outline text-muted" style="font-size: 4rem;"></i>
                                        <h5 class="mt-3 mb-2">No CVs Found</h5>
                                        <p class="mb-3">Start by adding your first CV record</p>
                                        <button wire:click="create" class="btn btn-primary" onclick="setTimeout(() => { const modal = new bootstrap.Modal(document.getElementById('cvModal')); modal.show(); }, 100);">
                                            <i class="mdi mdi-plus me-2"></i> Add First CV
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div class="d-flex justify-content-between align-items-center p-3 border-top bg-light">
                <div class="text-muted small">
                    <i class="mdi mdi-information-outline me-1"></i>
                    Showing {{ $this->cvs->firstItem() ?? 0 }} to {{ $this->cvs->lastItem() ?? 0 }} of {{ $this->cvs->total() }} entries
                </div>
                <div>
                    {{ $this->cvs->links() }}
                </div>
            </div>
        </div>
    </div>

    <!-- Create/Edit Modal -->
    <div class="modal fade" id="cvModal" tabindex="-1" wire:ignore.self>
        <div class="modal-dialog modal-fullscreen">
            <div class="modal-content border-0 shadow-lg">
                <div class="modal-header bg-primary text-white border-0">
                    <div class="d-flex align-items-center">
                        <i class="mdi mdi-file-document-outline me-2 fs-4"></i>
                        <h5 class="modal-title mb-0">{{ $editingCv ? 'Edit CV' : 'Add New CV' }}</h5>
                    </div>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form wire:submit.prevent="{{ $editingCv ? 'update' : 'store' }}">
                    <div class="modal-body bg-light">
                        <div class="row g-4">
                            <!-- Personal Information -->
                            <div class="col-md-12">
                                <div class="card border-0 shadow-sm">
                                    <div class="card-header bg-white border-0">
                                        <h6 class="mb-0 text-primary">
                                            <i class="mdi mdi-account-outline me-2"></i>Personal Information
                                        </h6>
                                    </div>
                                    <div class="card-body">
                                
                                <div class="mb-3">
                                    <label for="name" class="form-label">Full Name *</label>
                                    <input wire:model="name" type="text" class="form-control @error('name') is-invalid @enderror" required>
                                    @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>

                                <div class="mb-3">
                                    <label for="email" class="form-label">Email</label>
                                    <input wire:model="email" type="email" class="form-control @error('email') is-invalid @enderror">
                                    @error('email') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>

                                <div class="mb-3">
                                    <label for="phone" class="form-label">Phone *</label>
                                    <input wire:model="phone" type="text" class="form-control @error('phone') is-invalid @enderror" required>
                                    @error('phone') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>

                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="birth_date" class="form-label">Birth Date *</label>
                                            <input wire:model="birth_date" type="date" class="form-control @error('birth_date') is-invalid @enderror" required>
                                            @error('birth_date') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="gender" class="form-label">Gender *</label>
                                            <select wire:model="gender" class="form-select @error('gender') is-invalid @enderror" required>
                                                <option value="">Select Gender</option>
                                                @foreach($this->genderOptions as $value => $label)
                                                    <option value="{{ $value }}">{{ $label }}</option>
                                                @endforeach
                                            </select>
                                            @error('gender') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="marital_status" class="form-label">Marital Status *</label>
                                            <select wire:model="marital_status" class="form-select @error('marital_status') is-invalid @enderror" required>
                                                <option value="">Select Status</option>
                                                @foreach($this->maritalStatusOptions as $value => $label)
                                                    <option value="{{ $value }}">{{ $label }}</option>
                                                @endforeach
                                            </select>
                                            @error('marital_status') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="nationality" class="form-label">Nationality *</label>
                                            <input wire:model="nationality" type="text" class="form-control @error('nationality') is-invalid @enderror" required>
                                            @error('nationality') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                        </div>
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label for="religion" class="form-label">Religion *</label>
                                    <input wire:model="religion" type="text" class="form-control @error('religion') is-invalid @enderror" required>
                                    @error('religion') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                    </div>
                                </div>
                            </div>

                            <!-- Address Information -->
                            <div class="col-md-12">
                                <div class="card border-0 shadow-sm">
                                    <div class="card-header bg-white border-0">
                                        <h6 class="mb-0 text-primary">
                                            <i class="mdi mdi-map-marker-outline me-2"></i>Address Information
                                        </h6>
                                    </div>
                                    <div class="card-body">
                                
                                <div class="mb-3">
                                    <label for="country" class="form-label">Country</label>
                                    <input wire:model="country" type="text" class="form-control @error('country') is-invalid @enderror">
                                    @error('country') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>

                                <div class="mb-3">
                                    <label for="state" class="form-label">State/Province</label>
                                    <input wire:model="state" type="text" class="form-control @error('state') is-invalid @enderror">
                                    @error('state') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>

                                <div class="mb-3">
                                    <label for="city" class="form-label">City</label>
                                    <input wire:model="city" type="text" class="form-control @error('city') is-invalid @enderror">
                                    @error('city') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>

                                <div class="mb-3">
                                    <label for="address" class="form-label">Full Address</label>
                                    <textarea wire:model="address" class="form-control @error('address') is-invalid @enderror" rows="3"></textarea>
                                    @error('address') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>

                                <div class="mb-3">
                                    <label for="cv_file" class="form-label">CV File</label>
                                    <input wire:model="cv_file" type="file" class="form-control @error('cv_file') is-invalid @enderror" accept=".pdf,.doc,.docx">
                                    <small class="form-text text-muted">Accepted formats: PDF, DOC, DOCX</small>
                                    @error('cv_file') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                    @if($editingCv && $editingCv->getFirstMedia('HR_Cvs'))
                                        <div class="mt-2">
                                            <small class="text-muted">Current file:</small>
                                            <div class="d-flex align-items-center mt-1">
                                                <i class="mdi mdi-file-document me-2"></i>
                                                <span class="text-primary">{{ $editingCv->getFirstMedia('HR_Cvs')->file_name }}</span>
                                                <button type="button" wire:click="downloadCv({{ $editingCv->id }})" class="btn btn-sm btn-outline-primary ms-2">
                                                    <i class="mdi mdi-download"></i> Download
                                                </button>
                                            </div>
                                        </div>
                                    @endif
                                    <div class="mt-2">
                                        <small class="text-muted">Upload a new file to replace the current one.</small>
                                    </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Professional Information -->
                        <div class="row g-4">
                            <div class="col-12">
                                <div class="card border-0 shadow-sm">
                                    <div class="card-header bg-white border-0">
                                        <h6 class="mb-0 text-primary">
                                            <i class="mdi mdi-briefcase-outline me-2"></i>Professional Information
                                        </h6>
                                    </div>
                                    <div class="card-body">
                                
                                <div class="mb-3">
                                    <label for="summary" class="form-label">Professional Summary</label>
                                    <textarea wire:model="summary" class="form-control @error('summary') is-invalid @enderror" rows="3" placeholder="Brief professional summary..."></textarea>
                                    @error('summary') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>

                                <div class="mb-3">
                                    <label for="skills" class="form-label">Skills</label>
                                    <textarea wire:model="skills" class="form-control @error('skills') is-invalid @enderror" rows="3" placeholder="Technical and soft skills..."></textarea>
                                    @error('skills') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>

                                <div class="mb-3">
                                    <label for="experience" class="form-label">Work Experience</label>
                                    <textarea wire:model="experience" class="form-control @error('experience') is-invalid @enderror" rows="4" placeholder="Previous work experience..."></textarea>
                                    @error('experience') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>

                                <div class="mb-3">
                                    <label for="education" class="form-label">Education</label>
                                    <textarea wire:model="education" class="form-control @error('education') is-invalid @enderror" rows="4" placeholder="Educational background..."></textarea>
                                    @error('education') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>

                                <div class="mb-3">
                                    <label for="projects" class="form-label">Projects</label>
                                    <textarea wire:model="projects" class="form-control @error('projects') is-invalid @enderror" rows="3" placeholder="Notable projects..."></textarea>
                                    @error('projects') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>

                                <div class="mb-3">
                                    <label for="certifications" class="form-label">Certifications</label>
                                    <textarea wire:model="certifications" class="form-control @error('certifications') is-invalid @enderror" rows="3" placeholder="Professional certifications..."></textarea>
                                    @error('certifications') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>

                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="languages" class="form-label">Languages</label>
                                            <input wire:model="languages" type="text" class="form-control @error('languages') is-invalid @enderror" placeholder="e.g., English, Arabic">
                                            @error('languages') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="portfolio" class="form-label">Portfolio URL</label>
                                            <input wire:model="portfolio" type="url" class="form-control @error('portfolio') is-invalid @enderror" placeholder="https://...">
                                            @error('portfolio') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                        </div>
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label for="interests" class="form-label">Interests</label>
                                    <textarea wire:model="interests" class="form-control @error('interests') is-invalid @enderror" rows="2" placeholder="Personal interests and hobbies..."></textarea>
                                    @error('interests') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>

                                <div class="mb-3">
                                    <label for="references" class="form-label">References</label>
                                    <textarea wire:model="references" class="form-control @error('references') is-invalid @enderror" rows="3" placeholder="Professional references..."></textarea>
                                    @error('references') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>

                                <div class="mb-3">
                                    <label for="cover_letter" class="form-label">Cover Letter</label>
                                    <textarea wire:model="cover_letter" class="form-control @error('cover_letter') is-invalid @enderror" rows="4" placeholder="Cover letter content..."></textarea>
                                    @error('cover_letter') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer bg-white border-0">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">
                            <i class="mdi mdi-close me-1"></i>Cancel
                        </button>
                        <button type="submit" class="btn btn-primary">
                            <i class="mdi mdi-content-save me-1"></i>{{ $editingCv ? 'Update CV' : 'Create CV' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- View CV Modal -->
    <div class="modal fade" id="viewCvModal" tabindex="-1" wire:ignore.self>
        <div class="modal-dialog modal-fullscreen">
            <div class="modal-content border-0 shadow-lg">
                <div class="modal-header bg-info text-white border-0">
                    <div class="d-flex align-items-center">
                        <i class="mdi mdi-eye-outline me-2 fs-4"></i>
                        <h5 class="modal-title mb-0">CV Details</h5>
                    </div>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body bg-light">
                    @if($viewCv)
                        <div class="row g-4">
                            <div class="col-md-8">
                                <div class="card border-0 shadow-sm">
                                    <div class="card-header bg-white border-0">
                                        <div class="d-flex align-items-center">
                                            <div class="avatar-lg me-3">
                                                <div class="avatar-title bg-primary rounded-circle">
                                                    {{ strtoupper(substr($viewCv->name, 0, 2)) }}
                                                </div>
                                            </div>
                                            <div>
                                                <h4 class="mb-1">{{ $viewCv->name }}</h4>
                                                <p class="text-muted mb-0">
                                                    <i class="mdi mdi-email-outline me-1"></i>{{ $viewCv->email }} | 
                                                    <i class="mdi mdi-phone-outline me-1"></i>{{ $viewCv->phone }}
                                                </p>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="card-body">
                                
                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <strong>Nationality:</strong> {{ $viewCv->nationality }}<br>
                                        <strong>Gender:</strong> {{ ucfirst($viewCv->gender) }}<br>
                                        <strong>Marital Status:</strong> {{ ucfirst($viewCv->marital_status) }}<br>
                                        <strong>Religion:</strong> {{ $viewCv->religion }}
                                    </div>
                                    <div class="col-md-6">
                                        <strong>Birth Date:</strong> {{ $viewCv->birth_date }}<br>
                                        <strong>Location:</strong> {{ $viewCv->city }}, {{ $viewCv->country }}<br>
                                        <strong>Address:</strong> {{ $viewCv->address ?: 'N/A' }}
                                    </div>
                                </div>

                                @if($viewCv->summary)
                                    <div class="mb-3">
                                        <h6>Professional Summary</h6>
                                        <p>{{ $viewCv->summary }}</p>
                                    </div>
                                @endif

                                @if($viewCv->skills)
                                    <div class="mb-3">
                                        <h6>Skills</h6>
                                        <p>{{ $viewCv->skills }}</p>
                                    </div>
                                @endif

                                @if($viewCv->experience)
                                    <div class="mb-3">
                                        <h6>Work Experience</h6>
                                        <p>{{ $viewCv->experience }}</p>
                                    </div>
                                @endif

                                @if($viewCv->education)
                                    <div class="mb-3">
                                        <h6>Education</h6>
                                        <p>{{ $viewCv->education }}</p>
                                    </div>
                                @endif

                                @if($viewCv->projects)
                                    <div class="mb-3">
                                        <h6>Projects</h6>
                                        <p>{{ $viewCv->projects }}</p>
                                    </div>
                                @endif

                                @if($viewCv->certifications)
                                    <div class="mb-3">
                                        <h6>Certifications</h6>
                                        <p>{{ $viewCv->certifications }}</p>
                                    </div>
                                @endif

                                <div class="row">
                                    @if($viewCv->languages)
                                        <div class="col-md-6">
                                            <h6>Languages</h6>
                                            <p>{{ $viewCv->languages }}</p>
                                        </div>
                                    @endif
                                    @if($viewCv->interests)
                                        <div class="col-md-6">
                                            <h6>Interests</h6>
                                            <p>{{ $viewCv->interests }}</p>
                                        </div>
                                    @endif
                                </div>

                                @if($viewCv->references)
                                    <div class="mb-3">
                                        <h6>References</h6>
                                        <p>{{ $viewCv->references }}</p>
                                    </div>
                                @endif

                                @if($viewCv->cover_letter)
                                    <div class="mb-3">
                                        <h6>Cover Letter</h6>
                                        <p>{{ $viewCv->cover_letter }}</p>
                                    </div>
                                @endif

                                @if($viewCv->portfolio)
                                    <div class="mb-3">
                                        <h6>Portfolio</h6>
                                        <a href="{{ $viewCv->portfolio }}" target="_blank" class="btn btn-sm btn-outline-primary">
                                            <i class="mdi mdi-link"></i> View Portfolio
                                        </a>
                                    </div>
                                @endif
                            </div>
                            <div class="col-md-4">
                                <div class="card">
                                    <div class="card-body">
                                        <h6>CV File</h6>
                                        @if($viewCv->getFirstMedia('HR_Cvs'))
                                            <button wire:click="downloadCv({{ $viewCv->id }})" class="btn btn-primary w-100 mb-2">
                                                <i class="mdi mdi-download"></i> Download CV
                                            </button>
                                        @else
                                            <p class="text-muted">No CV file uploaded</p>
                                        @endif
                                        
                                        <hr>
                                        <h6>Actions</h6>
                                        <button wire:click="edit({{ $viewCv->id }})" class="btn btn-warning w-100 mb-2" data-bs-dismiss="modal" onclick="setTimeout(() => { const modal = new bootstrap.Modal(document.getElementById('cvModal')); modal.show(); }, 100);">
                                            <i class="mdi mdi-pencil"></i> Edit CV
                                        </button>
                                        <button wire:click="delete({{ $viewCv->id }})" class="btn btn-danger w-100" data-bs-dismiss="modal" onclick="setTimeout(() => { const modal = new bootstrap.Modal(document.getElementById('deleteModal')); modal.show(); }, 100);">
                                            <i class="mdi mdi-delete"></i> Delete CV
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div class="modal fade" id="deleteModal" tabindex="-1" wire:ignore.self>
        <div class="modal-dialog modal-fullscreen">
            <div class="modal-content border-0 shadow-lg">
                <div class="modal-header bg-danger text-white border-0">
                    <div class="d-flex align-items-center">
                        <i class="mdi mdi-delete-alert-outline me-2 fs-4"></i>
                        <h5 class="modal-title mb-0">Confirm Delete</h5>
                    </div>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body bg-light d-flex align-items-center justify-content-center">
                    <div class="text-center">
                        <div class="mb-4">
                            <i class="mdi mdi-delete-alert text-danger" style="font-size: 4rem;"></i>
                        </div>
                        <h4 class="mb-3">Are you sure you want to delete this CV?</h4>
                        <p class="text-muted mb-4">This action cannot be undone. All CV data and associated files will be permanently removed.</p>
                        <div class="d-flex justify-content-center gap-3">
                            <button type="button" class="btn btn-light btn-lg" data-bs-dismiss="modal">
                                <i class="mdi mdi-close me-2"></i>Cancel
                            </button>
                            <button wire:click="confirmDelete" class="btn btn-danger btn-lg">
                                <i class="mdi mdi-delete me-2"></i>Delete CV
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

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
        
        .table-hover tbody tr:hover {
            background-color: rgba(0, 123, 255, 0.05);
        }
        
        .btn-group .btn {
            border-radius: 0.375rem !important;
        }
        
        .card {
            transition: all 0.3s ease;
        }
        
        .card:hover {
            transform: translateY(-2px);
            box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15) !important;
        }
        
        .form-control:focus,
        .form-select:focus {
            border-color: #0d6efd;
            box-shadow: 0 0 0 0.2rem rgba(13, 110, 253, 0.25);
        }
        
        .badge {
            font-size: 0.75rem;
            padding: 0.375rem 0.75rem;
        }
    </style>

    <script>
        document.addEventListener('livewire:init', () => {
            console.log('Livewire initialized');
            
            // Handle CV created event
            Livewire.on('cv-created', () => {
                console.log('CV created event triggered');
                const modal = bootstrap.Modal.getInstance(document.getElementById('cvModal'));
                if (modal) modal.hide();
            });

            // Handle CV updated event
            Livewire.on('cv-updated', () => {
                console.log('CV updated event triggered');
                const modal = bootstrap.Modal.getInstance(document.getElementById('cvModal'));
                if (modal) modal.hide();
            });

            // Handle CV deleted event
            Livewire.on('cv-deleted', () => {
                console.log('CV deleted event triggered');
                const modal = bootstrap.Modal.getInstance(document.getElementById('deleteModal'));
                if (modal) modal.hide();
            });

            // Function to show modal
            function showModal(modalId) {
                console.log('Attempting to show modal:', modalId);
                const modalElement = document.getElementById(modalId);
                if (modalElement) {
                    console.log('Modal element found, creating Bootstrap modal');
                    const modal = new bootstrap.Modal(modalElement);
                    modal.show();
                } else {
                    console.error('Modal element not found:', modalId);
                }
            }

            // Watch for showModal property changes
            @this.watch('showModal', value => {
                console.log('showModal changed to:', value);
                if (value) {
                    showModal('cvModal');
                }
            });

            // Watch for showViewModal property changes
            @this.watch('showViewModal', value => {
                console.log('showViewModal changed to:', value);
                if (value) {
                    showModal('viewCvModal');
                }
            });

            // Watch for showDeleteModal property changes
            @this.watch('showDeleteModal', value => {
                console.log('showDeleteModal changed to:', value);
                if (value) {
                    showModal('deleteModal');
                }
            });
        });

        // Handle modal closing and reset Livewire properties
        document.addEventListener('DOMContentLoaded', function() {
            console.log('DOM loaded, setting up modal event listeners');
            const modals = document.querySelectorAll('.modal');
            modals.forEach(modal => {
                modal.addEventListener('hidden.bs.modal', function () {
                    console.log('Modal hidden, resetting Livewire properties');
                    // Reset Livewire properties when modal is closed
                    @this.set('showModal', false);
                    @this.set('showViewModal', false);
                    @this.set('showDeleteModal', false);
                });

            });
        });
    </script>
</div> 
</div> 