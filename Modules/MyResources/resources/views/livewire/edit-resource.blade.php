<div>
    <form wire:submit.prevent="save">
        <div class="row">
            <!-- Same fields as create-resource.blade.php -->
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
                            <option value="{{ $category->id }}">{{ $category->name_ar ?? $category->name }}</option>
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
                            <option value="{{ $type->id }}">{{ $type->name_ar ?? $type->name }}</option>
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
                            <option value="{{ $status->id }}">{{ $status->name_ar ?? $status->name }}</option>
                        @endforeach
                    </select>
                    @error('resource_status_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
            </div>

            <div class="col-md-12">
                <div class="mb-3">
                    <label class="form-label">{{ __('Description') }}</label>
                    <textarea wire:model="description" class="form-control" rows="3"></textarea>
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
            <button type="submit" class="btn btn-primary">{{ __('Update') }}</button>
        </div>
    </form>
</div>

