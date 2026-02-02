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
    $colorClass = '';
    if (in_array($type, [10, 14, 16, 22])) {
        $colorClass = 'bg-primary';
    } elseif (in_array($type, [11, 15, 17, 24, 25])) {
        $colorClass = 'bg-danger';
    } elseif (in_array($type, [12, 13, 18, 19, 20, 21])) {
        $colorClass = 'bg-warning';
    }
@endphp

<div class="row invoice-head-card card mb-2">
    <div class="card-body py-2">
        <input type="hidden" wire:model="type">

        {{-- السطر الأول: عنوان الفاتورة + المورد + المخزن + نمط الفاتورة --}}
        <div class="row g-2 align-items-end mb-2">
            <div class="col-auto d-flex align-items-center gap-2">
                <span class="rounded-circle {{ $colorClass }}" style="width: 10px; height: 10px; min-width: 10px; flex-shrink: 0;"></span>
                <h3 class="card-title fw-bold m-0" style="font-size: 1.15rem;">{{ __($titles[$type]) }}</h3>
            </div>

            @if ($branches->count() > 1)
                <div class="col-auto" style="min-width: 130px;">
                    <label class="form-label small mb-0">{{ __('Branch') }}</label>
                    <select wire:model.live="branch_id" class="form-control form-control-sm">
                        @foreach ($branches as $branch)
                            <option value="{{ $branch->id }}">{{ $branch->name }}</option>
                        @endforeach
                    </select>
                </div>
            @endif

            {{-- المورد / العميل acc1 — 2/12 من العرض --}}
            <div class="col-2" wire:key="acc1-{{ $branch_id }}">
                <label class="form-label small mb-0">{{ $acc1Role }}</label>
                @if ($type != 21 && setting('invoice_show_add_clients_suppliers'))
                    @php
                        $accountType = in_array($type, [11, 13, 15, 17]) ? 'supplier' : 'client';
                    @endphp
                    <div class="input-group input-group-sm">
                        <div class="flex-grow-1">
                            <livewire:async-select name="acc1_id" wire:model.live="acc1_id" :options="$acc1Options"
                                placeholder="{{ __('Search for ') . $acc1Role . __('...') }}" ui="bootstrap"
                                :key="'acc1-async-add-' . $type . '-' . $branch_id . '-' . count($acc1Options)" />
                        </div>
                        @canany(['create ' . $titles[$type], 'create invoices'])
                            <livewire:accounts::account-creator :type="$accountType" :button-class="'btn btn-success btn-sm'" :button-text="'+'"
                                :key="'account-creator-' . $type . '-' . $branch_id" />
                        @endcanany
                    </div>
                @else
                    <livewire:async-select name="acc1_id" wire:model.live="acc1_id" :options="$acc1Options"
                        placeholder="{{ __('Search for ') . $acc1Role . __('...') }}" ui="bootstrap"
                        :key="'acc1-async-' . $type . '-' . $branch_id . '-' . count($acc1Options)" />
                @endif
                @error('acc1_id')<span class="text-danger small"><strong>{{ $message }}</strong></span>@enderror
            </div>

            {{-- المخزن acc2 --}}
            <div class="col-auto" wire:key="acc2-{{ $branch_id }}" style="min-width: 140px;">
                <label class="form-label small mb-0">{{ $acc2Role }}</label>
                <select wire:model.live="acc2_id"
                    class="form-control form-control-sm @error('acc2_id') is-invalid @enderror"
                    @cannot('edit ' . $titles[$type]) disabled @endcannot>
                    <option value="">{{ __('Select ') }}{{ $acc2Role }}</option>
                    @foreach ($acc2List as $acc)
                        <option value="{{ $acc->id }}">{{ $acc->aname }}</option>
                    @endforeach
                </select>
                @error('acc2_id')<span class="invalid-feedback d-block"><strong>{{ $message }}</strong></span>@enderror
            </div>

            {{-- نمط الفاتورة: قالب الفاتورة و/أو نوع السعر --}}
            @if (setting('invoice_use_templates') && ($availableTemplates ?? collect())->isNotEmpty())
                <div class="col-auto" style="min-width: 120px;">
                    <label class="form-label small mb-0">{{ __('Invoice Template') }}</label>
                    <select wire:model.live="selectedTemplateId" id="selectedTemplate"
                        class="form-control form-control-sm @error('selectedTemplateId') is-invalid @enderror">
                        @foreach ($availableTemplates as $template)
                            <option value="{{ $template->id }}">{{ $template->name }}</option>
                        @endforeach
                    </select>
                    @error('selectedTemplateId')<span class="invalid-feedback d-block"><strong>{{ $message }}</strong></span>@enderror
                </div>
            @endif
            @if (setting('invoice_select_price_type') && in_array($type, [10, 12, 14, 16, 22]))
                <div class="col-auto" style="min-width: 120px;">
                    <label class="form-label small mb-0">{{ __('Price Type') }}</label>
                    <select wire:model.live="selectedPriceType"
                        class="form-control form-control-sm @error('selectedPriceType') is-invalid @enderror">
                        @foreach ($priceTypes ?? [] as $id => $name)
                            <option value="{{ $id }}">{{ $name }}</option>
                        @endforeach
                    </select>
                    @error('selectedPriceType')<span class="invalid-feedback d-block"><strong>{{ $message }}</strong></span>@enderror
                </div>
            @endif

            @if (isMultiCurrencyEnabled())
                <div class="col-auto">
                    <x-settings::currency-converter-mini :inline="false" sourceField="#pro_value" :showAmount="true"
                        :showResult="true" :selectedCurrency="$currency_id" :exchangeRate="$currency_rate"
                        wire:key="currency-converter-{{ $currency_id }}-{{ $currency_rate }}"
                        wire:model.live="currency_id" />
                </div>
            @else
                <input type="hidden" wire:model="currency_id" value="1">
                <input type="hidden" wire:model="currency_rate" value="1">
            @endif

            @if ($type != 21 && $showBalance ?? false)
                <div class="col-auto">
                    <div class="small">
                        <span>{{ __('Current Balance:') }}</span>
                        <span class="fw-bold text-primary" id="invoice-balance-current">{{ number_format($currentBalance ?? 0) }}</span>
                        <span class="ms-1">{{ __('After:') }}</span>
                        <span id="invoice-balance-after" class="fw-bold {{ ($balanceAfterInvoice ?? 0) < 0 ? 'text-danger' : 'text-success' }}">{{ number_format($balanceAfterInvoice ?? 0) }}</span>
                    </div>
                </div>
            @endif
        </div>

        {{-- السطر الثاني: باقي الحقول وينتهي بالبحث بالباركود --}}
        <div class="row g-2 align-items-end">
            {{-- الموظف --}}
            <div class="col-auto" wire:key="emp-{{ $branch_id }}" style="min-width: 120px;">
                <label class="form-label small mb-0">{{ __('Employee') }}</label>
                <select wire:model="emp_id" class="form-control form-control-sm @error('emp_id') is-invalid @enderror"
                    @cannot('edit ' . $titles[$type]) disabled @endcannot>
                    <option value="">{{ __('Select Employee') }}</option>
                    @foreach ($employees as $employee)
                        <option value="{{ $employee->id }}">{{ $employee->aname }}</option>
                    @endforeach
                </select>
                @error('emp_id')<span class="invalid-feedback d-block"><strong>{{ $message }}</strong></span>@enderror
            </div>

            @if ($type != 21)
                <div class="col-auto" wire:key="delivery-{{ $branch_id }}" style="min-width: 120px;">
                    <label class="form-label small mb-0">{{ __('Delegate') }}</label>
                    <select wire:model="delivery_id" class="form-control form-control-sm @error('delivery_id') is-invalid @enderror"
                        @cannot('edit ' . $titles[$type]) disabled @endcannot>
                        <option value="">{{ __('Select Delegate') }}</option>
                        @foreach ($deliverys as $delivery)
                            <option value="{{ $delivery->id }}">{{ $delivery->aname }}</option>
                        @endforeach
                    </select>
                    @error('delivery_id')<span class="invalid-feedback d-block"><strong>{{ $message }}</strong></span>@enderror
                </div>
            @endif

            {{-- التاريخ --}}
            <div class="col-auto" style="min-width: 115px;">
                <label class="form-label small mb-0">{{ __('Date') }}</label>
                <input type="date" wire:model="pro_date"
                    class="form-control form-control-sm @error('pro_date') is-invalid @enderror"
                    @if (setting('invoice_prevent_date_edit') || !auth()->user()->can('edit ' . $titles[$type])) readonly @endif>
                @error('pro_date')<span class="invalid-feedback d-block"><strong>{{ $message }}</strong></span>@enderror
            </div>

            @if (in_array($type, [15, 17]))
                <div class="col-auto" style="min-width: 115px;">
                    <label class="form-label small mb-0">{{ __('Expected delivery date') }}</label>
                    <input type="date" wire:model="expected_delivery_date"
                        class="form-control form-control-sm @error('expected_delivery_date') is-invalid @enderror"
                        @cannot('edit ' . $titles[$type]) readonly @endcannot>
                    @error('expected_delivery_date')<span class="invalid-feedback d-block"><strong>{{ $message }}</strong></span>@enderror
                </div>
            @endif

            @if (setting('invoice_use_due_date') && $type != 21)
                <div class="col-auto" style="min-width: 115px;">
                    <label class="form-label small mb-0">{{ __('Due Date') }}</label>
                    <input type="date" wire:model="accural_date"
                        class="form-control form-control-sm @error('accural_date') is-invalid @enderror"
                        @cannot('edit ' . $titles[$type]) readonly @endcannot>
                    @error('accural_date')<span class="invalid-feedback d-block"><strong>{{ $message }}</strong></span>@enderror
                </div>
            @endif

            <div class="col-auto" style="min-width: 80px;">
                <label class="form-label small mb-0">{{ __('Invoice Number') }}</label>
                <input type="number" wire:model="pro_id" class="form-control form-control-sm @error('pro_id') is-invalid @enderror" readonly>
                @error('pro_id')<span class="invalid-feedback d-block"><strong>{{ $message }}</strong></span>@enderror
            </div>

            @if ($type != 21)
                <div class="col-auto" style="min-width: 80px;">
                    <label class="form-label small mb-0">{{ __('S.N') }}</label>
                    <input type="text" wire:model="serial_number"
                        class="form-control form-control-sm @error('serial_number') is-invalid @enderror"
                        @cannot('edit ' . $titles[$type]) readonly @endcannot>
                    @error('serial_number')<span class="invalid-feedback d-block"><strong>{{ $message }}</strong></span>@enderror
                </div>
            @endif

            @if ($type == 14 && isset($statues))
                <div class="col-auto" style="min-width: 100px;">
                    <label class="form-label small mb-0">{{ __('Invoice Status') }}</label>
                    <select wire:model="status" id="status" class="form-control form-control-sm @error('status') is-invalid @enderror">
                        @foreach ($statues as $statusCase)
                            <option value="{{ $statusCase->value }}">{{ $statusCase->translate() }}</option>
                        @endforeach
                    </select>
                    @error('status')<span class="invalid-feedback d-block"><strong>{{ $message }}</strong></span>@enderror
                </div>
            @endif

            {{-- البحث بالباركود في نهاية السطر --}}
            <div class="col ms-auto" style="min-width: 160px;">
                @stack('invoice_head_barcode')
            </div>
        </div>
    </div>
</div>
