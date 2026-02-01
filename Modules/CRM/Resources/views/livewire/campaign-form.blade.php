<div>
    <div class="card">
        <div class="card-body">
            <form wire:submit.prevent="save">
                <!-- معلومات الحملة الأساسية -->
                <div class="row mb-4">
                    <div class="col-md-12">
                        <h5 class="mb-3">{{ __('Campaign Information') }}</h5>
                    </div>

                    <div class="col-md-4 mb-3">
                        <label class="form-label">{{ __('Campaign Title') }} <span class="text-danger">*</span></label>
                        <input type="text" wire:model="title"
                            class="form-control @error('title') is-invalid @enderror"
                            placeholder="{{ __('Enter campaign title') }}">
                        @error('title')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-4 mb-3">
                        <label class="form-label">{{ __('Email Subject') }} <span class="text-danger">*</span></label>
                        <input type="text" wire:model="subject"
                            class="form-control @error('subject') is-invalid @enderror"
                            placeholder="{{ __('Enter email subject') }}">
                        @error('subject')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <small class="text-muted">
                            {{ __('You can use variables like') }}: {اسم_العميل}, {العنوان}, {البريد}, {الهاتف},
                            {الشركة}
                        </small>
                    </div>

                    <div class="col-md-12 mb-3">
                        <label class="form-label">{{ __('Campaign Message') }} <span
                                class="text-danger">*</span></label>
                        <textarea wire:model="message" rows="6" class="form-control @error('message') is-invalid @enderror"
                            placeholder="{{ __('Enter campaign message') }}"></textarea>
                        @error('message')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <small class="text-muted">
                            {{ __('You can use variables like') }}: {اسم_العميل}, {العنوان}, {الهاتف}, {البريد},
                            {الشركة}
                        </small>
                    </div>
                </div>

                <hr>

                <!-- فلاتر الاستهداف -->
                <div class="row mb-4">
                    <div class="col-md-12">
                        <h5 class="mb-3">{{ __('Target Customers') }}</h5>
                        <p class="text-muted">{{ __('Select filters to target specific customers') }}</p>
                    </div>

                    <div class="col-md-3 mb-3">
                        <label class="form-label">{{ __('Address') }}</label>
                        <input type="text" wire:model="address" class="form-control"
                            placeholder="{{ __('Enter address') }}">
                    </div>

                    <div class="col-md-3 mb-3">
                        <label class="form-label">{{ __('Client Type') }}</label>
                        <select wire:model="clientTypeId" class="form-select">
                            <option value="">{{ __('All Types') }}</option>
                            @foreach ($clientTypes as $type)
                                <option value="{{ $type->id }}">{{ $type->title }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-3 mb-3">
                        <label class="form-label">{{ __('Client Category') }}</label>
                        <select wire:model="clientCategoryId" class="form-select">
                            <option value="">{{ __('All Categories') }}</option>
                            @foreach ($clientCategories as $category)
                                <option value="{{ $category->id }}">{{ $category->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-3 mb-3">
                        <label class="form-label">{{ __('Last Purchase (Days)') }}</label>
                        <select wire:model="lastPurchaseDays" class="form-select">
                            <option value="">{{ __('Any Time') }}</option>
                            <option value="30">{{ __('Last 30 days') }}</option>
                            <option value="60">{{ __('Last 60 days') }}</option>
                            <option value="90">{{ __('Last 90 days') }}</option>
                            <option value="180">{{ __('Last 6 months') }}</option>
                            <option value="365">{{ __('Last year') }}</option>
                        </select>
                    </div>

                    <div class="col-md-3 mb-3">
                        <label class="form-label">{{ __('Minimum Total Purchases') }}</label>
                        <input type="number" wire:model="totalPurchasesMin" class="form-control" step="0.01"
                            placeholder="{{ __('Enter minimum amount') }}">
                    </div>

                    <div class="col-md-3 mb-3">
                        <label class="form-label">{{ __('Client Status') }}</label>
                        <select wire:model="isActive" class="form-select">
                            <option value="">{{ __('All Clients') }}</option>
                            <option value="1">{{ __('Active Only') }}</option>
                            <option value="0">{{ __('Inactive Only') }}</option>
                        </select>
                    </div>

                    <div class="col-md-3">
                        <button type="button" wire:click="previewCustomers" class="btn btn-outline-primary">
                            <i class="las la-eye me-2"></i>
                            {{ __('Preview Target Customers') }}
                        </button>
                    </div>
                </div>

                <hr>

                <!-- أزرار الحفظ -->
                <div class="row">
                    <div class="col-md-12">
                        <button type="submit" class="btn btn-success" wire:loading.attr="disabled">
                            <span wire:loading.remove>
                                <i class="las la-save me-2"></i>
                                {{ $isEdit ? __('Update Campaign') : __('Save as Draft') }}
                            </span>
                            <span wire:loading>
                                <i class="las la-spinner la-spin me-2"></i>
                                {{ __('Saving...') }}
                            </span>
                        </button>

                        <a href="{{ route('campaigns.index') }}" class="btn btn-secondary">
                            <i class="las la-times me-2"></i>
                            {{ __('Cancel') }}
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal معاينة العملاء -->
    @if ($showPreview)
        <div class="modal fade show d-block" tabindex="-1" style="background: rgba(0,0,0,0.5);">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">{{ __('Target Customers Preview') }}</h5>
                        <button type="button" class="btn-close" wire:click="closePreview"></button>
                    </div>
                    <div class="modal-body">
                        <div class="alert alert-info">
                            <i class="las la-info-circle me-2"></i>
                            {{ __('Total customers matching filters') }}: <strong>{{ $previewTotal }}</strong>
                        </div>

                        @if (count($previewClients) > 0)
                            <h6>{{ __('First 10 customers') }}:</h6>
                            <div class="table-responsive">
                                <table class="table table-sm table-striped">
                                    <thead>
                                        <tr>
                                            <th>#</th>
                                            <th>{{ __('Name') }}</th>
                                            <th>{{ __('Email') }}</th>
                                            <th>{{ __('Address') }}</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($previewClients as $client)
                                            <tr>
                                                <td>{{ $loop->iteration }}</td>
                                                <td>{{ $client['name'] }}</td>
                                                <td>{{ $client['email'] ?? '-' }}</td>
                                                <td>{{ $client['address'] ?? '-' }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @else
                            <div class="alert alert-warning">
                                {{ __('No customers found matching the selected filters') }}
                            </div>
                        @endif
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" wire:click="closePreview">
                            {{ __('Close') }}
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
