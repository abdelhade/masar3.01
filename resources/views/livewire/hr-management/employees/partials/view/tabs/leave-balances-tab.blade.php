{{-- Leave Balances Information Tab --}}
<div x-show="activeViewTab === 'leaveBalances'" 
     x-transition:enter="transition ease-out duration-200"
     x-transition:enter-start="opacity-0"
     x-transition:enter-end="opacity-100"
     x-cloak
>
    <div class="row g-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm animate-on-scroll">
                <div class="card-header bg-gradient-primary text-white py-2">
                    <h6 class="card-title mb-0 fw-bold font-hold">
                        <i class="fas fa-calendar-check me-2"></i>{{ __('رصيد الإجازات') }}
                    </h6>
                </div>
                <div class="card-body">
                    @if ($viewEmployee && $viewEmployee->leaveBalances->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-bordered table-striped table-hover mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th class="text-center fw-bold">{{ __('نوع الإجازة') }}</th>
                                        <th class="text-center fw-bold">{{ __('السنة') }}</th>
                                        <th class="text-center fw-bold">{{ __('الرصيد الافتتاحي') }}</th>
                                        <th class="text-center fw-bold">{{ __('المتراكمة') }}</th>
                                        <th class="text-center fw-bold">{{ __('المستخدمة') }}</th>
                                        <th class="text-center fw-bold">{{ __('المعلقة') }}</th>
                                        <th class="text-center fw-bold">{{ __('المحولة') }}</th>
                                        <th class="text-center fw-bold">{{ __('المتبقي') }}</th>
                                        <th class="text-center fw-bold">{{ __('ملاحظات') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($viewEmployee->leaveBalances as $balance)
                                        @php
                                            $remainingDays = $balance->opening_balance_days + 
                                                           $balance->accrued_days + 
                                                           $balance->carried_over_days - 
                                                           $balance->used_days - 
                                                           $balance->pending_days;
                                        @endphp
                                        <tr>
                                            <td class="align-middle">
                                                <span class="fw-bold text-success">
                                                    <i class="fas fa-calendar-alt me-1"></i>{{ e($balance->leaveType->name ?? __('غير محدد')) }}
                                                </span>
                                            </td>
                                            <td class="align-middle text-center">
                                                <span class="badge bg-info">{{ $balance->year }}</span>
                                            </td>
                                            <td class="align-middle text-center">{{ number_format($balance->opening_balance_days, 1) }}</td>
                                            <td class="align-middle text-center text-success">{{ number_format($balance->accrued_days, 1) }}</td>
                                            <td class="align-middle text-center text-danger">{{ number_format($balance->used_days, 1) }}</td>
                                            <td class="align-middle text-center text-warning">{{ number_format($balance->pending_days, 1) }}</td>
                                            <td class="align-middle text-center text-info">{{ number_format($balance->carried_over_days, 1) }}</td>
                                            <td class="align-middle text-center">
                                                <span class="badge fw-bold {{ $remainingDays >= 0 ? 'bg-success' : 'bg-danger' }} fs-6">
                                                    {{ number_format($remainingDays, 1) }}
                                                </span>
                                            </td>
                                            <td class="align-middle">
                                                <small class="text-muted">{{ e($balance->notes ?? __('لا توجد ملاحظات')) }}</small>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="alert alert-info text-center mb-0">
                            <i class="fas fa-info-circle me-2"></i>
                            {{ __('لا توجد أرصدة إجازات محددة لهذا الموظف.') }}
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

