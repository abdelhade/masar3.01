<div>
    <div class="card">
        <div class="card-header">
            <h4 class="card-title">{{ __('Edit Installment Plan') }}</h4>
        </div>
        <div class="card-body">
            @if ($paidPaymentsCount > 0)
                <div class="alert alert-warning" role="alert">
                    <i class="fas fa-exclamation-triangle"></i>
                    <strong>{{ __('Warning') }}:</strong> {{ __('This plan contains paid installments', ['count' => $paidPaymentsCount]) }}. 
                    {{ __('Editing will only affect unpaid installments') }}.
                </div>
            @endif

            <form wire:submit.prevent="update">
                <!-- Client Selection -->
                <div class="mb-3">
                    <label for="acc_head_id" class="form-label">العميل <span class="text-danger">*</span></label>
                    <select wire:model.live="acc_head_id" id="acc_head_id" class="form-select select2-client"
                        @error('acc_head_id') is-invalid @enderror>
                        <option value="">اختر العميل</option>
                        @foreach ($clients as $client)
                            <option value="{{ $client['id'] }}">{{ $client['code'] }} - {{ $client['aname'] }}</option>
                        @endforeach
                    </select>
                    @error('acc_head_id')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror
                </div>

                <!-- Account Balance Info -->
                @if ($acc_head_id)
                    <div class="alert alert-info">
                        <div class="row">
                            <div class="col-md-4">
                                <strong>رصيد الحساب الكلي:</strong> {{ number_format($accountBalance, 2) }}
                            </div>
                            <div class="col-md-4">
                                <strong>خطط موجودة:</strong> {{ number_format($existingPlansTotal, 2) }}
                            </div>
                            <div class="col-md-4">
                                <strong>الرصيد المتاح:</strong>
                                <span class="badge bg-{{ $availableBalance > 0 ? 'success' : 'danger' }}">
                                    {{ number_format($availableBalance, 2) }}
                                </span>
                            </div>
                        </div>
                    </div>
                @endif

                <div class="row">
                    <!-- Total Amount -->
                    <div class="col-md-6 mb-3">
                        <label for="total_amount" class="form-label">المبلغ الإجمالي <span
                                class="text-danger">*</span></label>
                        <input type="number" step="0.01" wire:model.live="total_amount" id="total_amount"
                            class="form-control @error('total_amount') is-invalid @enderror">
                        @error('total_amount')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Down Payment -->
                    <div class="col-md-6 mb-3">
                        <label for="down_payment" class="form-label">الدفعة المقدمة <span
                                class="text-danger">*</span></label>
                        <input type="number" step="0.01" wire:model.live="down_payment" id="down_payment"
                            class="form-control @error('down_payment') is-invalid @enderror">
                        @error('down_payment')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <!-- Amount to be Installed (Auto-calculated) -->
                <div class="mb-3">
                    <label for="amount_to_be_installed" class="form-label">المبلغ المطلوب تقسيطه <span
                            class="text-danger">*</span></label>
                    <input type="number" step="0.01" wire:model="amount_to_be_installed" id="amount_to_be_installed"
                        class="form-control @error('amount_to_be_installed') is-invalid @enderror" readonly>
                    @error('amount_to_be_installed')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                    @if ($amount_to_be_installed > $availableBalance && $acc_head_id)
                        <div class="text-danger mt-1">
                            <i class="fas fa-exclamation-circle"></i>
                            المبلغ المطلوب أكبر من الرصيد المتاح!
                        </div>
                    @endif
                </div>

                <div class="row">
                    <!-- Number of Installments -->
                    <div class="col-md-4 mb-3">
                        <label for="number_of_installments" class="form-label">عدد الأقساط <span
                                class="text-danger">*</span></label>
                        <input type="number" wire:model="number_of_installments" id="number_of_installments"
                            class="form-control @error('number_of_installments') is-invalid @enderror">
                        @error('number_of_installments')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Start Date -->
                    <div class="col-md-4 mb-3">
                        <label for="start_date" class="form-label">تاريخ البداية <span
                                class="text-danger">*</span></label>
                        <input type="date" wire:model="start_date" id="start_date"
                            class="form-control @error('start_date') is-invalid @enderror">
                        @error('start_date')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Interval Type -->
                    <div class="col-md-4 mb-3">
                        <label for="interval_type" class="form-label">نوع الفترة <span
                                class="text-danger">*</span></label>
                        <select wire:model="interval_type" id="interval_type"
                            class="form-select @error('interval_type') is-invalid @enderror">
                            <option value="daily">يومي</option>
                            <option value="weekly">أسبوعي</option>
                            <option value="monthly">شهري</option>
                            <option value="yearly">سنوي</option>
                        </select>
                        @error('interval_type')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="d-flex justify-content-between">
                    <a href="{{ route('installments.plans.show', $plan->id) }}" class="btn btn-secondary">
                        <i class="fas fa-arrow-right"></i> {{ __('Back') }}
                    </a>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> {{ __('Save Changes') }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
    <script>
        document.addEventListener('livewire:initialized', () => {
            // Initialize Select2
            $('.select2-client').select2({
                dir: 'rtl',
                language: 'ar',
                placeholder: 'ابحث عن العميل...',
                allowClear: true
            });

            // Update Livewire when Select2 changes
            $('.select2-client').on('change', function() {
                @this.set('acc_head_id', $(this).val());
            });

            // Listen for validation errors
            Livewire.on('validation-error', (data) => {
                const d = Array.isArray(data) ? data[0] : data;
                Swal.fire({
                    icon: 'error',
                    title: d.title || 'خطأ',
                    text: d.text,
                    toast: true,
                    position: 'top-end',
                    showConfirmButton: false,
                    timer: 5000,
                    timerProgressBar: true,
                    customClass: {
                        popup: 'text-end'
                    }
                });
            });

            // Listen for plan updated
            Livewire.on('plan-updated', (data) => {
                const d = Array.isArray(data) ? data[0] : data;
                Swal.fire({
                    icon: 'success',
                    title: d.title || 'نجح',
                    text: d.text,
                    showCancelButton: true,
                    confirmButtonText: '{{ __('View Plan') }}',
                    cancelButtonText: '{{ __('Stay Here') }}',
                    confirmButtonColor: '#28a745',
                    cancelButtonColor: '#6c757d',
                    customClass: {
                        popup: 'text-end'
                    }
                }).then((result) => {
                    if (result.isConfirmed) {
                        window.location.href = '/installments/plans/' + d.planId;
                    }
                });
            });
        });
    </script>
@endpush
