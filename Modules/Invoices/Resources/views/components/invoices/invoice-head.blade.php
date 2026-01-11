@push('styles')
    <style>
        .card-title {
            padding-inline-start: 80px;
        }
    </style>
@endpush

@php
    $titles = [
        10 => 'Sales Invoice',
        11 => 'Purchase Invoice',
        12 => 'Sales Return',
        13 => 'Purchase Return',
        14 => 'Sales Order',
        15 => 'Purchase Order',
        16 => 'Quotation to Customer',
        17 => 'Quotation from Supplier',
        18 => 'Damaged Goods Invoice',
        19 => 'Dispatch Order',
        20 => 'Addition Order',
        21 => 'Store-to-Store Transfer',
        22 => 'Booking Order',
        24 => 'Service Invoice',
        25 => 'Requisition',
        26 => 'Pricing Agreement',
    ];
@endphp


<div class="row card border border-secondary border-3">
    <div class="card-header d-flex justify-content-between align-items-center flex-wrap">
        <div class="d-flex align-items-center">
            <h3 class="card-title fw-bold fs-2 m-0 ms-3">
                {{ __($titles[$type]) }}
            </h3>
            @php
                $colorClass = '';
                if (in_array($type, [10, 14, 16, 22])) {
                    $colorClass = 'bg-primary';
                } elseif (in_array($type, [11, 15, 17, 24, 25])) {
                    $colorClass = 'bg-danger';
                } elseif (in_array($type, [12, 13, 18, 19, 20, 21])) {
                    $colorClass = 'bg-warning';
                }
            @endphp


            <div class="rounded-circle {{ $colorClass }}" style="width: 25px; height: 25px; min-width: 25px;">
            </div>


            @if ($branches->count() > 1)
                <div class="ms-3" style="min-width: 150px;">
                    <label class="form-label" style="font-size: 1em;">{{ __('Branch') }}</label>
                    <select wire:model.live="branch_id" class="form-control form-control-sm"
                        style="font-size: 0.85em; height: 2em; padding: 2px 6px;">
                        @foreach ($branches as $branch)
                            <option value="{{ $branch->id }}">{{ $branch->name }}</option>
                        @endforeach
                    </select>
                </div>
            @endif
        </div>

        @if (isMultiCurrencyEnabled())
            <div class="col-lg-3">
                <x-settings::currency-converter-mini :inline="false" sourceField="#pro_value" :showAmount="true"
                    :showResult="true" {{-- تمرير المتغيرات المحدثة من الدالة PHP --}} :selectedCurrency="$currency_id" :exchangeRate="$currency_rate" {{-- إضافة wire:key يجبر Livewire على إعادة رسم الكومبوننت عند تغير العملة أو السعر --}}
                    wire:key="currency-converter-{{ $currency_id }}-{{ $currency_rate }}" {{-- ربط التغيير العكسي (لو المستخدم غير العملة يدوياً) --}}
                    wire:model.live="currency_id" />
            </div>
        @else
            <input type="hidden" wire:model="currency_id" value="1">
            <input type="hidden" wire:model="currency_rate" value="1">
        @endif


        {{-- تحديث عرض الرصيد مع إضافة معلومات المبلغ المدفوع --}}
        @if ($type != 21)
            @if ($showBalance)
                <div class="mt-1">
                    <div class="row" style="min-width: 400px">
                        <div class="col-6">
                            <label>{{ __('Current Balance: ') }}</label>
                            <span class="fw-bold text-primary"
                                x-text="window.formatNumberFixed(currentBalance)">{{ number_format($currentBalance) }}</span>
                        </div>
                        <div class="col-6">
                            <label>{{ __('Balance After Invoice: ') }}</label>
                            <span class="fw-bold" :class="calculatedBalanceAfter < 0 ? 'text-danger' : 'text-success'"
                                x-text="window.formatNumberFixed(calculatedBalanceAfter)">
                                {{ number_format($balanceAfterInvoice) }}
                            </span>
                        </div>
                    </div>



                </div>
            @endif
        @endif
    </div>

    <div class="card-body ">
        <div class="row">
            <input type="hidden" wire:model="type">

            {{-- الحساب المتغير acc1 --}}
            <div class="col-lg-2" wire:key="acc1-{{ $branch_id }}">
                <div class="flex-grow-1">
                    @if ($type != 21 && setting('invoice_show_add_clients_suppliers'))
                        @php
                            $accountType = 'client';
                            if (in_array($type, [11, 13, 15, 17])) {
                                $accountType = 'supplier';
                            }
                        @endphp

                        {{-- ✅ Label فوق الحقل --}}
                        <label class="form-label">{{ $acc1Role }}</label>

                        {{-- ✅ Async Select مع الزر ملزوق (استخدام options بدلاً من endpoint) --}}
                        <div class="input-group">
                            <div class="flex-grow-1">
                                <livewire:async-select name="acc1_id" wire:model.live="acc1_id" :options="$acc1Options"
                                    placeholder="{{ __('Search for ') . $acc1Role . __('...') }}" ui="bootstrap"
                                    :key="'acc1-async-add-' . $type . '-' . $branch_id . '-' . count($acc1Options)"
                                    x-on:change="if ($wire && typeof $wire.updateCurrencyFromAccount === 'function') $wire.updateCurrencyFromAccount($event.target.value)" />
                            </div>

                            @canany(['create ' . $titles[$type], 'create invoices'])
                                <livewire:accounts::account-creator :type="$accountType" :button-class="'btn btn-success'" :button-text="'+'"
                                    :key="'account-creator-' . $type . '-' . $branch_id" />
                            @endcanany
                        </div>
                    @else
                        {{-- ✅ بدون زر إضافة (استخدام options بدلاً من endpoint) --}}
                        <label class="form-label">{{ $acc1Role }}</label>
                        <livewire:async-select name="acc1_id" wire:model.live="acc1_id" :options="$acc1Options"
                            placeholder="{{ __('Search for ') . $acc1Role . __('...') }}" ui="bootstrap"
                            :key="'acc1-async-' . $type . '-' . $branch_id . '-' . count($acc1Options)"
                            x-on:change="if (typeof updateCurrencyFromAccount === 'function') updateCurrencyFromAccount($event.target.value)" />
                    @endif

                    @error('acc1_id')
                        <span class="text-danger small d-block mt-1"><strong>{{ $message }}</strong></span>
                    @enderror
                </div>
            </div>

            {{-- ✅ Currency Display Fields (Multi-Currency) --}}




            {{-- المخزن acc2 --}}
            <div class="col-lg-2" wire:key="acc2-{{ $branch_id }}">
                <label class="form-label" style="font-size: 1em;">{{ $acc2Role }}</label>
                <select wire:model.live="acc2_id"
                    class="form-control form-control-sm font-hold fw-bold font-14 @error('acc2_id') is-invalid @enderror"
                    style="font-size: 0.85em; height: 2em; padding: 2px 6px;"
                    @cannot('edit ' . $titles[$type]) disabled @endcannot <option value="">{{ __('Select ') }}
                    {{ $acc2Role }}</option>
                    @foreach ($acc2List as $acc)
                        <option value="{{ $acc->id }}">{{ $acc->aname }}</option>
                    @endforeach
                </select>
                @error('acc2_id')
                    <span class="invalid-feedback"><strong>{{ $message }}</strong></span>
                @enderror
            </div>


            {{-- الموظف --}}
            <div class="col-lg-2" wire:key="emp-{{ $branch_id }}">
                <label for="emp_id" class="form-label" style="font-size: 1em;">{{ __('Employee') }}</label>
                <select wire:model="emp_id"
                    class="form-control form-control-sm font-hold fw-bold font-14 @error('emp_id') is-invalid @enderror"
                    style="font-size: 0.85em; height: 2em; padding: 2px 6px;"
                    @cannot('edit ' . $titles[$type]) disabled @endcannot <option
                    value="">{{ __('Select Employee') }}</option>
                    @foreach ($employees as $employee)
                        <option value="{{ $employee->id }}">{{ $employee->aname }}</option>
                    @endforeach
                </select>
                @error('emp_id')
                    <span class="invalid-feedback"><strong>{{ $message }}</strong></span>
                @enderror
            </div>


            @if ($type != 21)
                <div class="col-lg-2" wire:key="delivery-{{ $branch_id }}">
                    <label for="delivery_id" class="form-label" style="font-size: 1em;">{{ __('Delegate') }}</label>
                    <select wire:model="delivery_id"
                        class="form-control form-control-sm font-hold fw-bold font-14 @error('delivery_id') is-invalid @enderror"
                        style="font-size: 0.85em; height: 2em; padding: 2px 6px;"
                        @cannot('edit ' . __($titles[$type])) disabled @endcannot>
                        <option value="">{{ __('Select Delegate') }}</option>
                        @foreach ($deliverys as $delivery)
                            <option value="{{ $delivery->id }}">{{ $delivery->aname }}</option>
                        @endforeach
                    </select>
                    @error('delivery_id')
                        <span class="invalid-feedback"><strong>{{ $message }}</strong></span>
                    @enderror
                </div>
            @endif


            {{-- التاريخ --}}
            <div class="col-lg-1">
                <label for="pro_date" class="form-label" style="font-size: 1em;">{{ __('Date') }}</label>
                <input type="date" wire:model="pro_date"
                    class="form-control form-control-sm font-hold fw-bold font-14 @error('pro_date') is-invalid @enderror"
                    style="font-size: 0.85em; height: 2em; padding: 2px 6px;"
                    @if (setting('invoice_prevent_date_edit') ||
                            !auth()->user()->can('edit ' . $titles[$type])) readonly @endif
                    @error('pro_date')
                    <span class="invalid-feedback"><strong>{{ $message }}</strong></span>
                @enderror
                    </div>


                @if (setting('invoice_use_due_date'))
                    @if ($type != 21)
                        <div class="col-lg-1">
                            <label for="accural_date" class="form-label"
                                style="font-size: 1em;">{{ __('Due Date') }}</label>
                            <input type="date" wire:model="accural_date"
                                class="form-control form-control-sm font-hold fw-bold font-14 @error('accural_date') is-invalid @enderror"
                                style="font-size: 0.85em; height: 2em; padding: 2px 6px;"
                                @cannot('edit ' . $titles[$type]) readonly @endcannot
                                @error('accural_date')
                            <span class="invalid-feedback"><strong>{{ $message }}</strong></span>
                        @enderror
                                </div>
                    @endif
                @endif


                <div class="col-lg-1">
                    <label for="pro_id" class="form-label"
                        style="font-size: 1em;">{{ __('Invoice Number') }}</label>
                    <input type="number" wire:model="pro_id"
                        class="form-control form-control-sm font-hold fw-bold font-14 @error('pro_id') is-invalid @enderror"
                        readonly style="font-size: 0.85em; height: 2em; padding: 2px 6px;">
                    @error('pro_id')
                        <span class="invalid-feedback"><strong>{{ $message }}</strong></span>
                    @enderror
                </div>


                @if ($type != 21)
                    <div class="col-lg-1">
                        <label for="serial_number" class="form-label"
                            style="font-size: 1em;">{{ __('S.N') }}</label>
                        <input type="text" wire:model="serial_number"
                            class="form-control form-control-sm font-hold fw-bold font-14 @error('serial_number') is-invalid @enderror"
                            style="font-size: 0.85em; height: 2em; padding: 2px 6px;"
                            @cannot('edit ' . $titles[$type]) readonly @endcannot
                            @error('serial_number')
                        <span class="invalid-feedback"><strong>{{ $message }}</strong></span>
                    @enderror
                            </div>
                @endif
            </div>
        </div>
    </div>
