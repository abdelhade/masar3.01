<div>
    <form wire:submit.prevent="save">
        <div class="row">
            <!-- Basic Information -->
            <div class="col-md-6">
                <div class="mb-3">
                    <label class="form-label">{{ __('Name') }} <span class="text-danger">*</span></label>
                    <input type="text" wire:model="name" class="form-control @error('name') is-invalid @enderror">
                    @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
            </div>

            <div class="col-md-6">
                <div class="mb-3">
                    <label class="form-label">{{ __('Main Category') }} <span class="text-danger">*</span></label>
                    <select wire:model.live="resource_category_id" class="form-control @error('resource_category_id') is-invalid @enderror">
                        <option value="">{{ __('Select Category') }}</option>
                        @foreach($categories as $category)
                            <option value="{{ $category->id }}">{{ $category->name }}</option>
                        @endforeach
                    </select>
                    @error('resource_category_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
            </div>

            <div class="col-md-6">
                <div class="mb-3">
                    <label class="form-label">{{ __('Type') }} <span class="text-danger">*</span></label>
                    <select wire:model="resource_type_id" class="form-control @error('resource_type_id') is-invalid @enderror">
                        <option value="">{{ __('Select Type') }}</option>
                        @foreach($availableTypes as $type)
                            <option value="{{ $type->id }}">{{ $type->name }}</option>
                        @endforeach
                    </select>
                    @error('resource_type_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
            </div>

            <div class="col-md-6">
                <div class="mb-3">
                    <label class="form-label">{{ __('Status') }} <span class="text-danger">*</span></label>
                    <select wire:model="resource_status_id" class="form-control @error('resource_status_id') is-invalid @enderror">
                        <option value="">{{ __('Select Status') }}</option>
                        @foreach($statuses as $status)
                            <option value="{{ $status->id }}">{{ $status->name }}</option>
                        @endforeach
                    </select>
                    @error('resource_status_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
            </div>

            <div class="col-md-6">
                <div class="mb-3">
                    <label class="form-label">{{ __('Branch') }}</label>
                    <select wire:model="branch_id" class="form-control">
                        <option value="">{{ __('Select Branch') }}</option>
                        @foreach($branches as $branch)
                            <option value="{{ $branch->id }}">{{ $branch->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="col-md-6">
                <div class="mb-3">
                    <label class="form-label">{{ __('Serial Number') }}</label>
                    <input type="text" wire:model="serial_number" class="form-control">
                </div>
            </div>

            <div class="col-md-6">
                <div class="mb-3">
                    <label class="form-label">{{ __('Model Number') }}</label>
                    <input type="text" wire:model="model_number" class="form-control">
                </div>
            </div>

            <div class="col-md-6">
                <div class="mb-3">
                    <label class="form-label">{{ __('Manufacturer') }}</label>
                    <input type="text" wire:model="manufacturer" class="form-control">
                </div>
            </div>

            <div class="col-md-12">
                <div class="mb-3">
                    <label class="form-label">{{ __('Description') }}</label>
                    <textarea wire:model="description" class="form-control" rows="3"></textarea>
                </div>
            </div>

            <div class="col-md-12">
                <div class="mb-3">
                    <label class="form-label">{{ __('Notes') }}</label>
                    <textarea wire:model="notes" class="form-control" rows="2"></textarea>
                </div>
            </div>

            <div class="col-md-12">
                <div class="mb-3 form-check">
                    <input type="checkbox" wire:model="is_active" class="form-check-input" id="is_active">
                    <label class="form-check-label" for="is_active">{{ __('Active') }}</label>
                </div>
            </div>
        </div>

        <div class="d-flex justify-content-end gap-2">
            <a href="{{ route('myresources.index') }}" class="btn btn-secondary">{{ __('Cancel') }}</a>
            <button type="submit" class="btn btn-primary">{{ __('Save') }}</button>
        </div>
    </form>
</div>

