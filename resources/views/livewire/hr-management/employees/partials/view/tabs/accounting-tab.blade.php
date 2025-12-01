{{-- Accounting Information Tab --}}
<div x-show="activeViewTab === 'accounting'" 
     x-transition:enter="transition ease-out duration-200"
     x-transition:enter-start="opacity-0"
     x-transition:enter-end="opacity-100"
     style="display: none;">
    <div class="row g-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm animate-on-scroll">
                <div class="card-header bg-gradient-primary text-white py-2">
                    <h6 class="card-title mb-0 fw-bold font-hold">
                        <i class="fas fa-calculator me-2"></i>{{ __('حسابات الموظف') }}
                    </h6>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <div class="info-item">
                                <label class="form-label fw-bold text-muted small mb-1">
                                    <i class="fas fa-wallet me-1 text-primary"></i>{{ __('الحساب الرئيسي للمرتب') }}
                                </label>
                                <p class="form-control-plaintext mb-0">
                                    @if($viewEmployee->account?->haveParent)
                                        <span class="badge bg-primary fs-5">
                                            {{ $viewEmployee->account->haveParent->code }} - {{ e($viewEmployee->account->haveParent->aname) }}
                                        </span>
                                    @else
                                        <span class="text-muted fs-5">{{ __('غير محدد') }}</span>
                                    @endif
                                </p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="info-item">
                                <label class="form-label fw-bold text-muted small mb-1">
                                    <i class="fas fa-coins me-1 text-primary"></i>{{ __('الرصيد الأفتتاحي') }}
                                </label>
                                <p class="form-control-plaintext mb-0 fw-bold text-success fs-4">
                                    {{ $viewEmployee->account?->start_balance ? number_format($viewEmployee->account->start_balance, 2) . ' ر.س' : __('غير محدد') }}
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

