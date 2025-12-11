{{-- Accounting Tab --}}
<div>
    {{--  Accounting --}}
    <div class="row mt-3">
        <div class="col-lg-6">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-gradient-primary text-white py-2">
                    <h6 class="card-title mb-0 font-hold fw-bold">
                        <i class="fas fa-money-bill-wave me-2"></i>{{ __('الحسابات') }}
                    </h6>
                </div>
                <div class="card-body py-3">
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label fw-bold text-dark">{{ __('يتبع للحساب') }}</label>
                            <select class="form-select" wire:model.defer="salary_basic_account_id"
                                aria-label="{{ __('يتبع للحساب') }}">
                                <option value="">{{ __('اختر الحساب الرئيسي للمرتب') }}</option>
                                @foreach ($salary_basic_accounts as $key => $account)
                                    <option value="{{ $account['id'] }}">{{ $account['code'] }} - {{ $account['aname'] }}</option>
                                @endforeach
                            </select>
                            @error('salary_basic_account_id')
                                <div class="text-danger small mt-1">
                                    <i class="fas fa-exclamation-triangle me-1"></i>{{ $message }}
                                </div>
                            @enderror
                        </div>
                    </div>
                </div>
            </div>
        </div>
        {{-- accounting balance --}}
        <div class="col-lg-6">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-gradient-primary text-white py-2">
                    <h6 class="card-title mb-0 font-hold fw-bold">
                        <i class="fas fa-money-bill-wave me-2"></i>{{ __('الأرصده') }}
                    </h6>
                </div>
                <div class="card-body py-3">
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label fw-bold text-dark">{{ __('الرصيد الأفتتاحى') }}</label>
                            <div class="input-group">
                                <span class="input-group-text">ر.س</span>
                                <input type="number" class="form-control" wire:model.defer="opening_balance" onclick="this.select()"
                                    placeholder="0.00" step="0.5">
                            </div>
                            @error('opening_balance')
                                <div class="text-danger small mt-1">
                                    <i class="fas fa-exclamation-triangle me-1"></i>{{ $message }}
                                </div>
                            @enderror
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

