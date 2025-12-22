<div>
    <!-- Success Message -->
    @if (session()->has('message'))
        <div class="alert alert-success">{{ session('message') }}</div>
    @endif

    <!-- Plan Summary -->
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h4 class="card-title mb-0">{{ __('Plan Summary') }}</h4>
            <div>
                @can('edit Installment Plans')
                    <a href="{{ route('installments.plans.edit', $plan->id) }}" class="btn btn-warning btn-sm">
                        <i class="fas fa-edit"></i> {{ __('Edit Plan') }}
                    </a>
                @endcan
                
                @can('delete Installment Plans')
                    <button wire:click="deletePlan" wire:confirm="{{ __('Are you sure you want to delete this plan?') }} {{ __('All installments and journal entries will be deleted') }}" class="btn btn-danger btn-sm">
                        <i class="fas fa-trash"></i> {{ __('Delete Plan') }}
                    </button>
                @endcan
            </div>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-4"><strong>{{ __('Client') }}:</strong> {{ $plan->account->aname ?? 'N/A' }}
                    ({{ $plan->account->code ?? '' }})</div>
                <div class="col-md-4"><strong>{{ __('Current Balance') }}:</strong>
                    {{ number_format($plan->amount_to_be_installed - $plan->payments->sum('amount_paid'), 2) }}</div>
                <div class="col-md-4"><strong>{{ __('Status') }}:</strong> <span
                        class="badge bg-primary">{{ $plan->status }}</span></div>
            </div>
        </div>
    </div>

    <!-- Installments Table -->
    <div class="card">
        <div class="card-header">
            <h4 class="card-title">{{ __('View All Installments') }}</h4>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered mb-0">
                    <thead class="table-light text-center align-middle">
                        <tr>
                            <th>{{ __('Installment Number') }}</th>
                            <th>{{ __('Due Date') }}</th>
                            <th>{{ __('Installment Amount') }}</th>
                            <th>{{ __('Amount Paid') }}</th>
                            <th>{{ __('Payment Date') }}</th>
                            <th>{{ __('Status') }}</th>
                            <th>{{ __('Actions') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($plan->payments as $payment)
                            <tr class="text-center">
                                <td>{{ $payment->installment_number }}</td>
                                <td>{{ $payment->due_date }}</td>
                                <td>{{ number_format($payment->amount_due, 2) }}</td>
                                <td>{{ number_format($payment->amount_paid, 2) }}</td>
                                <td>{{ $payment->payment_date ?? '-' }}</td>
                                <td>
                                    @if ($payment->status == 'paid')
                                        <span class="badge bg-success">{{ __('Paid') }}</span>
                                    @elseif($payment->status == 'pending' && $payment->due_date < now())
                                        <span class="badge bg-danger">{{ __('Overdue') }}</span>
                                    @else
                                        <span class="badge bg-warning">{{ __('Pending') }}</span>
                                    @endif
                                </td>
                                <td>
                                    @if ($payment->status != 'paid')
                                        @can('edit Installment Plans')
                                            <button class="btn btn-success btn-sm"
                                                wire:click="openPaymentModal({{ $payment->id }})">
                                                <i class="fas fa-check"></i> {{ __('Record Payment') }}
                                            </button>
                                        @endcan
                                        
                                        @can('delete Installment Plans')
                                            <button class="btn btn-danger btn-sm"
                                                wire:click="deletePayment({{ $payment->id }})"
                                                wire:confirm="{{ __('Are you sure you want to delete this installment?') }}">
                                                <i class="fas fa-trash"></i> {{ __('Delete') }}
                                            </button>
                                        @endcan
                                    @else
                                        <span class="badge bg-success me-2">
                                            <i class="fas fa-check-circle"></i> {{ __('Paid') }}
                                        </span>
                                        
                                        @can('edit Installment Plans')
                                            <button class="btn btn-warning btn-sm"
                                                wire:click="cancelPayment({{ $payment->id }})"
                                                wire:confirm="{{ __('Are you sure you want to cancel this payment?') }} {{ __('The associated journal entry will be deleted') }}">
                                                <i class="fas fa-undo"></i> {{ __('Cancel') }}
                                            </button>
                                        @endcan
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot class="table-light">
                        <tr class="text-center fw-bold">
                            <td colspan="2">{{ __('Total') }}</td>
                            <td>{{ number_format($plan->payments->sum('amount_due'), 2) }}</td>
                            <td>{{ number_format($plan->payments->sum('amount_paid'), 2) }}</td>
                            <td colspan="3"></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>

    <!-- ============== Payment Recording Modal ============== -->
    <div wire:ignore.self class="modal fade" id="paymentModal" tabindex="-1" aria-labelledby="paymentModalLabel"
        aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="paymentModalLabel">{{ __('Record New Payment') }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form wire:submit.prevent="recordPayment">
                    <div class="modal-body">
                        <!-- Payment Amount -->
                        <div class="mb-3">
                            <label for="paymentAmount" class="form-label">{{ __('Payment Amount') }}</label>
                            <input type="number" step="0.01"
                                class="form-control @error('paymentAmount') is-invalid @enderror" id="paymentAmount"
                                wire:model="paymentAmount">
                            @error('paymentAmount')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <!-- Payment Date -->
                        <div class="mb-3">
                            <label for="paymentDate" class="form-label">{{ __('Payment Date') }}</label>
                            <input type="date" class="form-control @error('paymentDate') is-invalid @enderror"
                                id="paymentDate" wire:model="paymentDate">
                            @error('paymentDate')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <!-- Notes -->
                        <div class="mb-3">
                            <label for="notes" class="form-label">{{ __('Notes') }}</label>
                            <textarea class="form-control" id="notes" wire:model="notes" rows="3"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary"
                            data-bs-dismiss="modal">{{ __('Close') }}</button>
                        <button type="submit" class="btn btn-primary">{{ __('Save Payment') }}</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

@push('scripts')
    <script>
        document.addEventListener('livewire:initialized', () => {
            const modalElement = document.getElementById('paymentModal');
            const modal = new bootstrap.Modal(modalElement);

            @this.on('open-modal', (event) => {
                modal.show();
            });

            @this.on('close-modal', (event) => {
                modal.hide();
            });

            // Listen for payment success
            Livewire.on('payment-success', (data) => {
                const d = Array.isArray(data) ? data[0] : data;
                Swal.fire({
                    icon: 'success',
                    title: d.title || 'نجح',
                    text: d.text,
                    confirmButtonText: 'حسناً',
                    confirmButtonColor: '#28a745',
                    customClass: {
                        popup: 'text-end'
                    }
                });
            });

            // Listen for payment error
            Livewire.on('payment-error', (data) => {
                const d = Array.isArray(data) ? data[0] : data;
                Swal.fire({
                    icon: 'error',
                    title: d.title || 'خطأ',
                    text: d.text,
                    confirmButtonText: 'حسناً',
                    confirmButtonColor: '#d33',
                    customClass: {
                        popup: 'text-end'
                    }
                });
            });
        });
    </script>
@endpush
