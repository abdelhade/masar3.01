<!-- Quotation State Section -->
<div class="row mb-4">
    <div class="col-12">
        <div class="card border-info">
            <div class="card-header">
                <h6 class="card-title mb-0">
                    <i class="fas fa-file-invoice me-2"></i>
                    {{ __('Quotation State') }}
                </h6>
                <small class="d-block mt-1">{{ __('Select quotation state') }}</small>
            </div>
            <div class="card-body">
                <div class="row">

                    <div class="col-md-2 mb-3">
                        <label class="form-label fw-bold">{{ __('Project Size') }}</label>
                        <select wire:model="projectSize" class="form-select">
                            <option value="">{{ __('Select project size...') }}</option>
                            @foreach ($projectSizeOptions as $id => $name)
                                <option value="{{ $id }}">{{ $name }}</option>
                            @endforeach
                        </select>
                        @error('projectSize')
                            <span class="text-danger">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="col-md-2 mb-3">
                        <label class="form-label fw-bold">{{ __('KON Priority') }}</label>
                        <select wire:model="konPriority" class="form-select">
                            <option value="">{{ __('Select KON priority...') }}</option>
                            @foreach ($konPriorityOptions as $option)
                                <option value="{{ $option }}">
                                    {{ $option }}</option>
                            @endforeach
                        </select>
                        @error('konPriority')
                            <span class="text-danger">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="col-md-2 mb-3">
                        <label class="form-label fw-bold">{{ __('Client Priority') }}</label>
                        <select wire:model="clientPriority" class="form-select">
                            <option value="">{{ __('Select priority...') }}</option>
                            @foreach ($clientPriorityOptions as $option)
                                <option value="{{ $option }}">
                                    {{ $option }}</option>
                            @endforeach
                        </select>
                        @error('clientPriority')
                            <span class="text-danger">{{ $message }}</span>
                        @enderror
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
