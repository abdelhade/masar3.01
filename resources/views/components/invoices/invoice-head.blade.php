<style>
    .card-title {
        padding-inline-start: 80px;
    }
</style>
<div class="row">
    <div class="card-header d-flex justify-content-between align-items-center flex-wrap">

        <div class="d-flex align-items-center">
            <h3 class="card-title fw-bold fs-2 m-0 ms-3">
                {{ $titles[$type] }}
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

            <div class="rounded-circle {{ $colorClass }}" style="width: 50px; height: 50px; min-width: 50px;">
            </div>

            @if ($branches->count() > 1)
                <div class="ms-3" style="min-width: 150px;">
                    <label class="form-label" style="font-size: 1em;">{{ __('Ø§Ù„ÙØ±Ø¹') }}</label>
                    <select wire:model.live="branch_id" class="form-control form-control-sm"
                        style="font-size: 0.85em; height: 2em; padding: 2px 6px;">
                        @foreach ($branches as $branch)
                            <option value="{{ $branch->id }}">{{ $branch->name }}</option>
                        @endforeach
                    </select>
                </div>
            @endif
        </div>


        {{-- ØªØ­Ø¯ÙŠØ« Ø¹Ø±Ø¶ Ø§Ù„Ø±ØµÙŠØ¯ Ù…Ø¹ Ø¥Ø¶Ø§ÙØ© Ù…Ø¹Ù„ÙˆÙ…Ø§Øª Ø§Ù„Ù…Ø¨Ù„Øº Ø§Ù„Ù…Ø¯ÙÙˆØ¹ --}}
        @if ($type != 21) {{-- Ø§Ù„Ø±ØµÙŠØ¯ Ù„Ø§ ÙŠÙ†Ø·Ø¨Ù‚ Ø¹Ù„Ù‰ Ø§Ù„ØªØ­ÙˆÙŠÙ„Ø§Øª --}}
            @if ($showBalance)
                <div class="mt-2 text-end">
                    <div class="row">
                        <div class="col-6">
                            <label>Ø§Ù„Ø±ØµÙŠØ¯ Ø§Ù„Ø­Ø§Ù„ÙŠ: </label>
                            <span class="fw-bold text-primary">{{ number_format($currentBalance) }}</span>
                        </div>
                        <div class="col-6">
                            <label>Ø§Ù„Ø±ØµÙŠØ¯ Ø¨Ø¹Ø¯ Ø§Ù„ÙØ§ØªÙˆØ±Ø©: </label>
                            <span class="fw-bold {{ $balanceAfterInvoice < 0 ? 'text-danger' : 'text-success' }}">
                                {{ number_format($balanceAfterInvoice) }}
                            </span>
                        </div>
                    </div>

                    @if ($received_from_client > 0 && $received_from_client != $total_after_additional)
                        <div class="row mt-1">
                            <div class="col-12">
                                <label>Ø§Ù„Ù…Ø¨Ù„Øº Ø§Ù„Ù…Ø³ØªØ­Ù‚: </label>
                                <span
                                    class="fw-bold {{ $total_after_additional - $received_from_client < 0 ? 'text-success' : 'text-danger' }}">
                                    {{ number_format($total_after_additional - $received_from_client) }}
                                </span>
                            </div>
                        </div>
                    @endif
                </div>
            @endif
        @endif {{-- Ø§Ù„Ø±ØµÙŠØ¯ Ù„Ø§ ÙŠÙ†Ø·Ø¨Ù‚ Ø¹Ù„Ù‰ Ø§Ù„ØªØ­ÙˆÙŠÙ„Ø§Øª --}}
    </div>

    {{-- Ø¨ÙŠØ§Ù†Ø§Øª Ø±Ø£Ø³ Ø§Ù„ÙØ§ØªÙˆØ±Ø© --}}
    <div class="card-body">
        <div class="row">
            <input type="hidden" wire:model="type">

            {{-- Ø§Ù„Ø­Ø³Ø§Ø¨ Ø§Ù„Ù…ØªØºÙŠØ± acc1 --}}
            <div class="col-lg-2" wire:key="acc1-{{ $branch_id }}">
                <div class="d-flex align-items-end gap-2">
                    <div class="flex-grow-1">
                        <livewire:app::searchable-select :model="'Modules\\Accounts\\Models\\AccHead'" :label="$acc1Role" :labelField="'aname'"
                            :placeholder="'Ø§Ø¨Ø­Ø« Ø¹Ù† ' . $acc1Role . '...'" :wireModel="'acc1_id'" :selectedId="$acc1_id" :where="$this->getAcc1WhereConditions()" :searchFields="['code', 'aname']"
                            :allowCreate="false" :key="'acc1-search-' . $type . '-' . $branch_id" />
                        @error('acc1_id')
                            <span class="text-danger small"><strong>{{ $message }}</strong></span>
                        @enderror
                    </div>

                    @if ($type != 21 && setting('invoice_show_add_clients_suppliers'))
                        @php
                            $accountType = 'client';
                            if (in_array($type, [11, 13, 15, 17])) {
                                $accountType = 'supplier';
                            }
                        @endphp
                        <livewire:accounts::account-creator :type="$accountType" :button-class="'btn btn-sm btn-success'" :button-text="$accountType === 'client' ? 'Ø¥Ø¶Ø§ÙØ© Ø¹Ù…ÙŠÙ„' : 'Ø¥Ø¶Ø§ÙØ© Ù…ÙˆØ±Ø¯'"
                            :key="'account-creator-' . $type . '-' . $branch_id" />
                    @endif
                </div>
            </div>

            {{-- Ø§Ù„Ù…Ø®Ø²Ù† acc2 --}}
            <div class="col-lg-2" wire:key="acc2-{{ $branch_id }}">
                <label class="form-label" style="font-size: 1em;">{{ $acc2Role }}</label>
                <select wire:model.live="acc2_id"
                    class="form-control form-control-sm font-family-cairo fw-bold font-14 @error('acc2_id') is-invalid @enderror"
                    style="font-size: 0.85em; height: 2em; padding: 2px 6px;">
                    <option value="">Ø§Ø®ØªØ± {{ $acc2Role }}</option>
                    @foreach ($acc2List as $acc)
                        <option value="{{ $acc->id }}">{{ $acc->aname }}</option>
                    @endforeach
                </select>
                @error('acc2_id')
                    <span class="invalid-feedback"><strong>{{ $message }}</strong></span>
                @enderror
            </div>

            {{-- Ø§Ù„Ù…ÙˆØ¸Ù --}}
            <div class="col-lg-2" wire:key="emp-{{ $branch_id }}">
                <label for="emp_id" class="form-label" style="font-size: 1em;">{{ __('Ø§Ù„Ù…ÙˆØ¸Ù') }}</label>
                <select wire:model="emp_id"
                    class="form-control form-control-sm font-family-cairo fw-bold font-14 @error('emp_id') is-invalid @enderror"
                    style="font-size: 0.85em; height: 2em; padding: 2px 6px;">
                    <option value="">Ø§Ø®ØªØ± Ø§Ù„Ù…ÙˆØ¸Ù</option>
                    @foreach ($employees as $employee)
                        <option value="{{ $employee->id }}">{{ $employee->aname }}</option>
                    @endforeach
                </select>
                @error('emp_id')
                    <span class="invalid-feedback"><strong>{{ $message }}</strong></span>
                @enderror
            </div>

            @if ($type != 21) {{-- Ø¥Ø¶Ø§ÙØ© Ø§Ù„Ù…Ù†Ø¯ÙˆØ¨ Ù„Ø§ ÙŠÙ†Ø·Ø¨Ù‚ Ø¹Ù„Ù‰ Ø§Ù„ØªØ­ÙˆÙŠÙ„Ø§Øª --}}
                <div class="col-lg-2" wire:key="delivery-{{ $branch_id }}">
                    <label for="delivery_id" class="form-label" style="font-size: 1em;">{{ __('Ø§Ù„Ù…Ù†Ø¯ÙˆØ¨') }}</label>
                    <select wire:model="delivery_id"
                        class="form-control form-control-sm font-family-cairo fw-bold font-14 @error('delivery_id') is-invalid @enderror"
                        style="font-size: 0.85em; height: 2em; padding: 2px 6px;">
                        <option value="">Ø§Ø®ØªØ± Ø§Ù„Ù…Ù†Ø¯ÙˆØ¨</option>
                        @foreach ($deliverys as $delivery)
                            <option value="{{ $delivery->id }}">{{ $delivery->aname }}</option>
                        @endforeach
                    </select>
                    @error('delivery_id')
                        <span class="invalid-feedback"><strong>{{ $message }}</strong></span>
                    @enderror
                </div>
            @endif {{-- Ø¥Ø¶Ø§ÙØ© Ø§Ù„Ù…Ù†Ø¯ÙˆØ¨ Ù„Ø§ ÙŠÙ†Ø·Ø¨Ù‚ Ø¹Ù„Ù‰ Ø§Ù„ØªØ­ÙˆÙŠÙ„Ø§Øª --}}

            {{-- Ø§Ù„ØªØ§Ø±ÙŠØ® --}}
            <div class="col-lg-1">
                <label for="pro_date" class="form-label" style="font-size: 1em;">{{ __('Ø§Ù„ØªØ§Ø±ÙŠØ®') }}</label>
                <input type="date" wire:model="pro_date"
                    class="form-control form-control-sm font-family-cairo fw-bold font-14 @error('pro_date') is-invalid @enderror"
                    style="font-size: 0.85em; height: 2em; padding: 2px 6px;"
                    @if (setting('invoice_prevent_date_edit')) readonly @endif>
                @error('pro_date')
                    <span class="invalid-feedback"><strong>{{ $message }}</strong></span>
                @enderror
            </div>

            @if (setting('invoice_use_due_date'))
                {{-- ØªØ§Ø±ÙŠØ® Ø§Ù„Ø§Ø³ØªØ­Ù‚Ø§Ù‚ --}}
                @if ($type != 21)
                    {{-- ØªØ§Ø±ÙŠØ® Ø§Ù„Ø§Ø³ØªØ­Ù‚Ø§Ù‚ Ù„Ø§ ÙŠÙ†Ø·Ø¨Ù‚ Ø¹Ù„Ù‰ Ø§Ù„ØªØ­ÙˆÙŠÙ„Ø§Øª --}}
                    <div class="col-lg-1">
                        <label for="accural_date" class="form-label"
                            style="font-size: 1em;">{{ __('ØªØ§Ø±ÙŠØ® Ø§Ù„Ø§Ø³ØªØ­Ù‚Ø§Ù‚') }}</label>
                        <input type="date" wire:model="accural_date"
                            class="form-control form-control-sm font-family-cairo fw-bold font-14 @error('accural_date') is-invalid @enderror"
                            style="font-size: 0.85em; height: 2em; padding: 2px 6px;">
                        @error('accural_date')
                            <span class="invalid-feedback"><strong>{{ $message }}</strong></span>
                        @enderror
                    </div>
                @endif {{-- ØªØ§Ø±ÙŠØ® Ø§Ù„Ø§Ø³ØªØ­Ù‚Ø§Ù‚ Ù„Ø§ ÙŠÙ†Ø·Ø¨Ù‚ Ø¹Ù„Ù‰ Ø§Ù„ØªØ­ÙˆÙŠÙ„Ø§Øª --}}
            @endif

            {{-- Ø±Ù‚Ù… Ø§Ù„ÙØ§ØªÙˆØ±Ø© (pro_id) Ø«Ø§Ø¨Øª --}}
            <div class="col-lg-1">
                <label for="pro_id" class="form-label" style="font-size: 1em;">{{ __('Ø±Ù‚Ù… Ø§Ù„ÙØ§ØªÙˆØ±Ø©') }}</label>
                <input type="number" wire:model="pro_id"
                    class="form-control form-control-sm font-family-cairo fw-bold font-14 @error('pro_id') is-invalid @enderror"
                    readonly style="font-size: 0.85em; height: 2em; padding: 2px 6px;">
                @error('pro_id')
                    <span class="invalid-feedback"><strong>{{ $message }}</strong></span>
                @enderror
            </div>

            {{-- S.N Ø£Ùˆ Serial Number --}}
            @if ($type != 21)
                {{-- S.N Ù„Ø§ ÙŠÙ†Ø·Ø¨Ù‚ Ø¹Ù„Ù‰ Ø§Ù„ØªØ­ÙˆÙŠÙ„Ø§Øª --}}
                <div class="col-lg-1">
                    <label for="serial_number" class="form-label" style="font-size: 1em;">{{ __('S.N') }}</label>
                    <input type="text" wire:model="serial_number"
                        class="form-control form-control-sm font-family-cairo fw-bold font-14 @error('serial_number') is-invalid @enderror"
                        style="font-size: 0.85em; height: 2em; padding: 2px 6px;">
                    @error('serial_number')
                        <span class="invalid-feedback"><strong>{{ $message }}</strong></span>
                    @enderror
                </div>
            @endif {{-- S.N Ù„Ø§ ÙŠÙ†Ø·Ø¨Ù‚ Ø¹Ù„Ù‰ Ø§Ù„ØªØ­ÙˆÙŠÙ„Ø§Øª --}}
        </div>
    </div>
</div>

<script>
    // Initialize TomSelect only once
    document.addEventListener('DOMContentLoaded', () => {
        const select = document.getElementById('acc1-select');
        if (select && !select.tomselect) {
            new TomSelect(select, {
                plugins: {
                    dropdown_input: {
                        class: 'font-family-cairo fw-bold font-14'
                    },
                    remove_button: {
                        title: 'Ø¥Ø²Ø§Ù„Ø© Ø§Ù„Ù…Ø­Ø¯Ø¯'
                    }
                },
                placeholder: 'Ø§Ø®ØªØ±',
                onChange: (value) => {
                    console.log('TomSelect changed:', value);
                    Livewire.dispatch('input', {
                        name: 'acc1_id',
                        value: value
                    });
                }
            });
        }
    });

    // Handle branch change event
    Livewire.on('branch-changed-completed', (event) => {
        const select = document.getElementById('acc1-select');
        if (select) {
            const instance = select.tomselect;
            if (instance) {
                // Clear old options
                instance.clearOptions();
                instance.clear();

                // Add new options
                event.acc1List.forEach(option => {
                    instance.addOption({
                        value: option.value,
                        text: option.text
                    });
                });

                // Set the selected value
                const newValue = event.acc1_id;
                if (newValue) {
                    instance.setValue(newValue, true);
                } else {
                    instance.clear(true);
                }

                // Update balance in the UI
                const balanceElement = document.querySelector('.text-primary');
                if (balanceElement) {
                    balanceElement.textContent = new Intl.NumberFormat().format(event.currentBalance);
                }
            }
        }
    });
</script>

