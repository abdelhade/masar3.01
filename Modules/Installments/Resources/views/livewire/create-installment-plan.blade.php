<form wire:submit.prevent="save">
    <div class="card">
        <div class="card-header">
            <h4 class="card-title">{{ __('Installment Setup') }}</h4>
        </div>
        <div class="card-body">
            <div class="row">
                <!-- Client Selection with Select2 -->
                <div class="col-md-4 mb-3">
                    <label for="accHeadId" class="form-label">{{ __('Client (Account)') }}</label>
                    <select wire:model="accHeadId" id="accHeadId"
                        class="form-select select2-client @error('accHeadId') is-invalid @enderror">
                        <option value="">{{ __('Select Client') }}</option>
                        @foreach ($clients as $client)
                            <option value="{{ $client->id }}">{{ $client->code }} - {{ $client->aname }}</option>
                        @endforeach
                    </select>
                    @error('accHeadId')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <!-- Account Balance Display -->
                @if ($accHeadId)
                    <div class="col-md-8 mb-3">
                        <div class="alert {{ $showBalanceWarning ? 'alert-danger' : 'alert-info' }} mb-0">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <span class="fw-bold">{{ __('Total Balance') }}:</span>
                                        <span class="fs-5 fw-bold">{{ number_format($accountBalance, 2) }}
                                            {{ __('EGP') }}</span>
                                    </div>
                                    @if (count($existingPlans) > 0)
                                        <div class="d-flex justify-content-between align-items-center mb-2">
                                            <span class="fw-bold">{{ __('Existing Plans') }}
                                                ({{ count($existingPlans) }}):</span>
                                            <span class="fs-5 fw-bold text-warning">-
                                                {{ number_format($totalInstallmentPlans, 2) }}
                                                {{ __('EGP') }}</span>
                                        </div>
                                    @endif
                                </div>
                                <div class="col-md-6">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <span class="fw-bold">{{ __('Available for Installments') }}:</span>
                                        <span
                                            class="fs-4 fw-bold {{ $availableBalance > 0 ? 'text-success' : 'text-danger' }}">
                                            {{ number_format($availableBalance, 2) }} {{ __('EGP') }}
                                        </span>
                                    </div>
                                </div>
                            </div>

                            @if ($showBalanceWarning)
                                <div class="mt-2 text-danger">
                                    <i class="fas fa-exclamation-triangle"></i>
                                    {{ __('Cannot create installment plan - Insufficient balance') }}
                                </div>
                            @endif

                            @if (count($existingPlans) > 0)
                                <div class="mt-3">
                                    <button type="button" class="btn btn-sm btn-outline-info"
                                        onclick="showExistingPlans()">
                                        <i class="fas fa-info-circle"></i> {{ __('View Existing Plans') }}
                                    </button>
                                </div>
                            @endif
                        </div>
                    </div>
                @endif
            </div>
            <div class="row">
                <!-- Total Amount -->
                <div class="col-md-3 mb-3">
                    <label for="totalAmount" class="form-label">{{ __('Total Amount Before Interest') }}</label>
                    <input type="number" step="0.01" wire:model.live.debounce.300ms="totalAmount" id="totalAmount"
                        class="form-control">
                </div>
                <!-- Down Payment -->
                <div class="col-md-3 mb-3">
                    <label for="downPayment" class="form-label">{{ __('Down Payment Amount') }}</label>
                    <input type="number" step="0.01" wire:model.live.debounce.500ms="downPayment" id="downPayment"
                        class="form-control">
                </div>
                <!-- Amount to be Installed -->
                <div class="col-md-3 mb-3">
                    <label class="form-label">{{ __('Remaining Amount for Installment') }}</label>
                    <input type="text" value="{{ number_format($amountToBeInstalled, 2) }}" class="form-control"
                        readonly>
                </div>
            </div>
            <hr>
            <div class="row">
                <!-- Number of Installments -->
                <div class="col-md-3 mb-3">
                    <label for="numberOfInstallments" class="form-label">{{ __('Number of Installments') }}</label>
                    <input type="number" wire:model.live.debounce.500ms="numberOfInstallments"
                        id="numberOfInstallments" class="form-control">
                </div>
                <!-- Installment Amount -->
                <div class="col-md-3 mb-3">
                    <label class="form-label">{{ __('Single Installment Amount') }}</label>
                    <input type="text" value="{{ number_format($installmentAmount, 2) }}" class="form-control"
                        readonly>
                </div>
                <!-- Start Date -->
                <div class="col-md-3 mb-3">
                    <label for="startDate" class="form-label">{{ __('First Installment Date') }}</label>
                    <input type="date" wire:model="startDate" id="startDate" class="form-control">
                </div>
                <!-- Interval -->
                <div class="col-md-3 mb-3">
                    <label class="form-label">{{ __('Interval Between Installments') }}</label>
                    <select wire:model="intervalType" class="form-select">
                        <option value="monthly">{{ __('Monthly') }}</option>
                        <option value="daily">{{ __('Daily') }}</option>
                    </select>
                </div>
            </div>
        </div>
        <div class="card-footer text-end">
            <button type="submit" class="btn btn-primary">{{ __('Save and Generate Installments') }}</button>
        </div>
    </div>
</form>

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Initialize Select2 for client selection
            $('#accHeadId').select2({
                placeholder: '{{ __('Select Client') }}',
                allowClear: true,
                width: '100%',
                language: 'ar',
                dir: 'rtl'
            });

            // Listen for Select2 change and update Livewire
            $('#accHeadId').on('change', function(e) {
                @this.set('accHeadId', e.target.value);
            });

            // Listen for Livewire validation errors
            Livewire.on('validation-error', (data) => {
                const d = Array.isArray(data) ? data[0] : data;

                // Convert text with line breaks to HTML
                let htmlContent = d.text;
                if (d.html) {
                    htmlContent = d.text.replace(/\n/g, '<br>');
                }

                Swal.fire({
                    icon: 'error',
                    title: d.title || 'خطأ',
                    html: htmlContent,
                    confirmButtonText: 'حسناً',
                    confirmButtonColor: '#d33',
                    width: '500px',
                    customClass: {
                        popup: 'text-end'
                    }
                });
            });

            // Listen for amount exceeds balance warning
            Livewire.on('amount-exceeds-balance', (data) => {
                const d = Array.isArray(data) ? data[0] : data;

                let message =
                    `المبلغ المدخل (${parseFloat(d.amount).toFixed(2)} ريال) أكبر من المتاح (${parseFloat(d.balance).toFixed(2)} ريال)`;

                if (d.existingPlansCount > 0) {
                    message +=
                        `<br><small class="text-muted">يوجد ${d.existingPlansCount} خطة بإجمالي ${parseFloat(d.existingPlansTotal).toFixed(2)} ريال</small>`;
                }

                Swal.fire({
                    icon: 'warning',
                    title: 'تحذير',
                    html: message,
                    confirmButtonText: 'حسناً',
                    confirmButtonColor: '#ffc107',
                    position: 'top-end',
                    toast: true,
                    timer: 5000,
                    timerProgressBar: true,
                    showConfirmButton: false,
                    customClass: {
                        popup: 'text-end'
                    }
                });
            });

            // Listen for save success
            Livewire.on('save-success', (data) => {
                const d = Array.isArray(data) ? data[0] : data;
                Swal.fire({
                    icon: 'success',
                    title: d.title || 'نجح',
                    text: d.text,
                    confirmButtonText: '{{ __('View Plan') }}',
                    confirmButtonColor: '#28a745',
                    showCancelButton: true,
                    cancelButtonText: 'إنشاء خطة جديدة'
                }).then((result) => {
                    if (result.isConfirmed) {
                        window.location.href = '/installments/plans/' + d.planId;
                    } else {
                        // Reset form
                        @this.call('$refresh');
                        $('#accHeadId').val(null).trigger('change');
                    }
                });
            });
        });

        // Function to show existing plans
        function showExistingPlans() {
            const plans = @json($existingPlans);

            let html = '<div class="table-responsive text-end"><table class="table table-sm">';
            html += '<thead><tr><th>رقم الخطة</th><th>المبلغ</th><th>الحالة</th><th>التاريخ</th><th>إجراءات</th></tr></thead>';
            html += '<tbody>';

            plans.forEach(plan => {
                const date = new Date(plan.created_at).toLocaleDateString('ar-EG');
                const status = plan.status === 'active' ? 'نشطة' :
                    plan.status === 'completed' ? 'مكتملة' :
                    plan.status === 'cancelled' ? 'ملغاة' : plan.status;
                const statusClass = plan.status === 'active' ? 'badge bg-success' :
                    plan.status === 'completed' ? 'badge bg-primary' :
                    'badge bg-secondary';

                html += `<tr>
            <td>#${plan.id}</td>
            <td>${parseFloat(plan.total_amount).toFixed(2)} ريال</td>
            <td><span class="${statusClass}">${status}</span></td>
            <td>${date}</td>
            <td>
                <a href="/installments/plans/${plan.id}" class="btn btn-sm btn-info">
                    <i class="fas fa-eye"></i> {{ __('View') }}
                </a>
            </td>
        </tr>`;
            });

            html += '</tbody></table></div>';

            Swal.fire({
                title: 'الخطط الموجودة',
                html: html,
                width: '700px',
                confirmButtonText: 'إغلاق',
                customClass: {
                    popup: 'text-end'
                }
            });
        }
    </script>
@endpush
