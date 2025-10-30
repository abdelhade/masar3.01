<!-- Project Data Section -->
<div class="row mb-4 ">
    <div class="col-12 ">
        <div class="card border-success ">
            <div class="card-header">
                <h2 class="card-title mb-0">
                    <i class="fas fa-project-diagram me-2"></i>
                    {{ __('Project Data') }}
                </h2>
                <small class="d-block mt-1">{{ __('Basic project information and important dates') }}</small>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-3 mb-3 d-flex flex-column">
                        <label class="form-label fw-bold">{{ __('Project') }}</label>
                        <livewire:app::searchable-select :model="Modules\Progress\Models\ProjectProgress::class" label-field="name" wire-model="projectId"
                            placeholder="{{ __('Search for project or add new...') }}" :key="'project-select'"
                            :selected-id="$projectId" />
                    </div>

                    <div class="col-md-2 mb-3">
                        <label class="form-label fw-bold">{{ __('Tender Number') }}</label>
                        <input type="text" wire:model="tenderNo" class="form-control">
                        @error('tenderNo')
                            <span class="text-danger">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="col-md-4 mb-3">
                        <label class="form-label fw-bold">{{ __('Tender ID') }}</label>
                        <input type="text" wire:model="tenderId" class="form-control">
                        @error('tenderId')
                            <span class="text-danger">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="col-md-3 mb-3">
                        <label class="form-label fw-bold">{{ __('Attach File') }}</label>
                        <input type="file" wire:model="projectImage" id="projectImage"
                            class="form-control @error('projectImage') is-invalid @enderror">
                        @error('projectImage')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-2 mb-3">
                        <label class="form-label fw-bold">{{ __('Inquiry Status') }}</label>
                        <select wire:model="status" class="form-select">
                            <option value="">{{ __('Select status...') }}</option>
                            @foreach ($statusOptions as $status)
                                <option value="{{ $status->value }}">
                                    {{ $status->label() }}</option>
                            @endforeach
                        </select>
                        @error('status')
                            <span class="text-danger">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="col-md-2 mb-3">
                        <label class="form-label fw-bold">{{ __('KON Status') }}</label>
                        <select wire:model="statusForKon" class="form-select">
                            <option value="">{{ __('Select...') }}</option>
                            @foreach ($statusForKonOptions as $status)
                                <option value="{{ $status->value }}">
                                    {{ $status->label() }}</option>
                            @endforeach
                        </select>
                        @error('statusForKon')
                            <span class="text-danger">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="col-md-2 mb-3">
                        <label class="form-label fw-bold">{{ __('KON Title') }}</label>
                        <select wire:model="konTitle" class="form-select">
                            <option value="">{{ __('Select title...') }}</option>
                            @foreach ($konTitleOptions as $title)
                                <option value="{{ $title->value }}">{{ $title->label() }}</option>
                            @endforeach
                        </select>
                        @error('konTitle')
                            <span class="text-danger">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="row">

                        <div class="col-md-2 mb-3">
                            <label class="form-label fw-bold">{{ __('Inquiry Date') }}</label>
                            <input type="date" wire:model="inquiryDate" class="form-control">
                            @error('inquiryDate')
                                <span class="text-danger">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="col-md-2 mb-3">
                            <label class="form-label fw-bold">{{ __('Delivery Date') }}</label>
                            <input type="date" wire:model="reqSubmittalDate" class="form-control">
                            @error('reqSubmittalDate')
                                <span class="text-danger">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="col-md-2 mb-3">
                            <label class="form-label fw-bold">{{ __('Project Start Date') }}</label>
                            <input type="date" wire:model="projectStartDate" class="form-control">
                            @error('projectStartDate')
                                <span class="text-danger">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
