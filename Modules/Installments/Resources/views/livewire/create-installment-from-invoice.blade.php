<div wire:id="installment-modal-component">
    <!-- Modal -->
    <div class="modal fade" id="installmentModal" tabindex="-1" aria-labelledby="installmentModalLabel" aria-hidden="true"
        wire:ignore.self>
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="installmentModalLabel">
                        <i class="las la-calendar-check"></i> {{ __('Create Installment Plan') }}
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form wire:submit.prevent="save">
                    <div class="modal-body">
                        <div class="row">
                            <!-- Client Selection - Read Only -->
                            <div class="col-md-6 mb-3">
                                <label for="accHeadId" class="form-label">{{ __('Client (Account)') }} <span
                                        class="text-danger">*</span></label>
                                <select wire:model="accHeadId" id="accHeadId"
                                    class="form-select bg-light @error('accHeadId') is-invalid @enderror"
                                    style="pointer-events: none;">
                                    <option value="">{{ __('Select Client') }}</option>
                                    @foreach ($clients as $client)
                                        <option value="{{ $client->id }}">{{ $client->code }} - {{ $client->aname }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('accHeadId')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <small
                                    class="text-muted">{{ __('Client is automatically selected from invoice') }}</small>
                            </div>

                            <!-- Account Balance Display -->
                            @if ($accHeadId)
                                <div class="col-md-6 mb-3">
                                    <div
                                        class="alert {{ $showBalanceWarning ? 'alert-danger' : 'alert-info' }} mb-0 p-2">
                                        <div class="d-flex justify-content-between align-items-center mb-1">
                                            <small class="fw-bold">{{ __('Total Balance') }}:</small>
                                            <small class="fw-bold">{{ number_format($accountBalance, 2) }}
                                                {{ __('EGP') }}</small>
                                        </div>
                                        @if (count($existingPlans) > 0)
                                            <div class="d-flex justify-content-between align-items-center mb-1">
                                                <small class="fw-bold">{{ __('Existing Plans') }}
                                                    ({{ count($existingPlans) }}):</small>
                                                <small class="fw-bold text-warning">-
                                                    {{ number_format($totalInstallmentPlans, 2) }}
                                                    {{ __('EGP') }}</small>
                                            </div>
                                        @endif
                                        <div class="d-flex justify-content-between align-items-center">
                                            <small class="fw-bold">{{ __('Available for Installments') }}:</small>
                                            <small
                                                class="fw-bold {{ $availableBalance > 0 ? 'text-success' : 'text-danger' }}">
                                                {{ number_format($availableBalance, 2) }} {{ __('EGP') }}
                                            </small>
                                        </div>
                                    </div>
                                </div>
                            @else
                                <div class="col-md-6 mb-3">
                                    <div class="alert alert-warning mb-0 p-2">
                                        <small class="fw-bold">
                                            <i class="las la-exclamation-triangle"></i>
                                            {{ __('Please select a client in the invoice first') }}
                                        </small>
                                    </div>
                                </div>
                            @endif
                        </div>

                        <div class="row">
                            <!-- Total Amount -->
                            <div class="col-md-3 mb-3">
                                <label for="totalAmount" class="form-label">{{ __('Total Amount (Final)') }} <span
                                        class="text-danger">*</span></label>
                                <input type="number" step="0.01" wire:model.live.debounce.300ms="totalAmount"
                                    id="totalAmount" class="form-control @error('totalAmount') is-invalid @enderror"
                                    placeholder="{{ __('Final total after discount/additional') }}">
                                @error('totalAmount')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <small
                                    class="text-muted">{{ __('Invoice total after discount and additional charges') }}</small>
                            </div>

                            <!-- Down Payment -->
                            <div class="col-md-3 mb-3">
                                <label for="downPayment" class="form-label">{{ __('Down Payment') }}</label>
                                <input type="number" step="0.01" wire:model.live.debounce.500ms="downPayment"
                                    id="downPayment" class="form-control @error('downPayment') is-invalid @enderror">
                                @error('downPayment')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Amount to be Installed -->
                            <div class="col-md-3 mb-3">
                                <label class="form-label">{{ __('Amount to Install') }}</label>
                                <input type="text" value="{{ number_format($amountToBeInstalled, 2) }}"
                                    class="form-control bg-light" readonly>
                            </div>

                            <!-- Number of Installments -->
                            <div class="col-md-3 mb-3">
                                <label for="numberOfInstallments" class="form-label">{{ __('Number of Installments') }}
                                    <span class="text-danger">*</span></label>
                                <input type="number" wire:model.live.debounce.500ms="numberOfInstallments"
                                    id="numberOfInstallments"
                                    class="form-control @error('numberOfInstallments') is-invalid @enderror">
                                @error('numberOfInstallments')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="row">
                            <!-- Installment Amount -->
                            <div class="col-md-3 mb-3">
                                <label class="form-label">{{ __('Installment Amount') }}</label>
                                <input type="text" value="{{ number_format($installmentAmount, 2) }}"
                                    class="form-control bg-light" readonly>
                            </div>

                            <!-- Start Date -->
                            <div class="col-md-3 mb-3">
                                <label for="startDate" class="form-label">{{ __('Start Date') }} <span
                                        class="text-danger">*</span></label>
                                <input type="date" wire:model="startDate" id="startDate"
                                    class="form-control @error('startDate') is-invalid @enderror">
                                @error('startDate')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Interval Type -->
                            <div class="col-md-3 mb-3">
                                <label class="form-label">{{ __('Interval Type') }} <span
                                        class="text-danger">*</span></label>
                                <select wire:model="intervalType"
                                    class="form-select @error('intervalType') is-invalid @enderror">
                                    <option value="monthly">{{ __('Monthly') }}</option>
                                    <option value="daily">{{ __('Daily') }}</option>
                                </select>
                                @error('intervalType')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                            <i class="las la-times"></i> {{ __('Cancel') }}
                        </button>
                        <button type="submit" class="btn btn-primary" wire:loading.attr="disabled">
                            <span wire:loading.remove>
                                <i class="las la-save"></i> {{ __('Save Installment Plan') }}
                            </span>
                            <span wire:loading>
                                <i class="las la-spinner la-spin"></i> {{ __('Saving...') }}
                            </span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

@push('scripts')
    <script>
        // Define modal instance globally so it can be accessed by Livewire events
        let installmentModalInstance = null;
        let modalComponentId = null;

        (function() {
            function initModal() {
                const modalElement = document.getElementById('installmentModal');
                if (modalElement) {
                    installmentModalInstance = new bootstrap.Modal(modalElement, {
                        backdrop: 'static',
                        keyboard: true
                    });

                    // Get the Livewire component ID
                    const componentWrapper = document.getElementById('installment-modal-component');
                    if (componentWrapper) {
                        modalComponentId = componentWrapper.getAttribute('wire:id');
                    }
                }
            }

            function handleOpenModal(event) {
                const data = event.detail;

                // Try to find the modal's Livewire component
                let livewireComponent = null;

                if (modalComponentId) {
                    livewireComponent = Livewire.find(modalComponentId);
                }

                if (!livewireComponent) {
                    // Fallback: find by component name
                    const allComponents = Livewire.all();
                    livewireComponent = allComponents.find(c => c.__name ===
                        'installments::create-installment-from-invoice');
                }

                if (!livewireComponent) {
                    return;
                }

                // Update the component data only (modal is already open)
                livewireComponent.call('updateFromInvoice', data.invoiceTotal, data.clientAccountId).then(() => {
                    console.log('✅ Data updated successfully');
                }).catch(err => {
                    return;
                });
            }

            // Initialize on DOM ready
            if (document.readyState === 'loading') {
                document.addEventListener('DOMContentLoaded', function() {
                    setTimeout(initModal, 100);
                    window.addEventListener('open-installment-modal', handleOpenModal);
                });
            } else {
                setTimeout(initModal, 100);
                window.addEventListener('open-installment-modal', handleOpenModal);
            }
        })();

        document.addEventListener('livewire:init', function() {
            // Listen for update event from invoice (when opening modal) - LEGACY
            Livewire.on('update-installment-data', (data) => {

                const d = Array.isArray(data) ? data[0] : data;

                if (!d.clientAccountId || d.clientAccountId === 'null' || d.clientAccountId === null) {

                    Swal.fire({
                        icon: 'warning',
                        title: 'تحذير',
                        text: 'يرجى اختيار العميل في الفاتورة أولاً',
                        confirmButtonText: 'حسناً'
                    });

                    return;
                }
                // Update the component first
                @this.call('updateFromInvoice', d.invoiceTotal, d.clientAccountId).then(() => {
                    // Then open the modal after data is updated
                    setTimeout(() => {
                        if (installmentModalInstance) {
                            installmentModalInstance.show();
                        }
                    }, 100);
                }).catch(err => {
                    console.error('❌ Error calling updateFromInvoice:', err);
                });
            });

            // Listen for client change in invoice (auto-update modal data)
            Livewire.on('client-changed-in-invoice', (data) => {

                const d = Array.isArray(data) ? data[0] : data;

                // Update the component data without opening modal
                @this.call('updateFromInvoice', d.invoiceTotal, d.clientAccountId);
            });

            // Listen for show warning event
            Livewire.on('show-warning', (data) => {
                const d = Array.isArray(data) ? data[0] : data;

                Swal.fire({
                    icon: 'warning',
                    title: d.title || 'تحذير',
                    text: d.text,
                    confirmButtonText: 'حسناً'
                });
            });
        });

        document.addEventListener('DOMContentLoaded', function() {
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

            // Listen for installment created success
            Livewire.on('installment-created', (data) => {
                console.log('✅ installment-created event received');
                const d = Array.isArray(data) ? data[0] : data;

                // Close modal using Bootstrap directly
                const modalEl = document.getElementById('installmentModal');
                if (modalEl) {
                    const modalInstance = bootstrap.Modal.getInstance(modalEl);
                    if (modalInstance) {
                        modalInstance.hide();
                        console.log('✅ Modal closed');
                    } else {
                        // Fallback: hide using jQuery-like approach
                        modalEl.classList.remove('show');
                        modalEl.style.display = 'none';
                        document.body.classList.remove('modal-open');
                        const backdrop = document.querySelector('.modal-backdrop');
                        if (backdrop) backdrop.remove();
                        console.log('✅ Modal closed (fallback)');
                    }
                }

                // Show success alert
                Swal.fire({
                    icon: 'success',
                    title: 'تم الحفظ بنجاح',
                    text: 'تم إنشاء خطة التقسيط بنجاح',
                    confirmButtonText: 'حسناً',
                    confirmButtonColor: '#28a745'
                });
            });

            // Listen for close modal event
            Livewire.on('close-installment-modal', () => {
                if (installmentModalInstance) {
                    installmentModalInstance.hide();
                }
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

            // Handle modal hidden event to reset instance
            const modalElement = document.getElementById('installmentModal');
            if (modalElement) {
                modalElement.addEventListener('hidden.bs.modal', function() {
                    console.log('Modal hidden');
                });
            }
        });
    </script>
@endpush
