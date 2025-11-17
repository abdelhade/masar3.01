<style>
    .card-title {
        padding-inline-start: 80px;
    }
</style>


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


<div class="row">
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


            <div class="rounded-circle {{ $colorClass }}" style="width: 50px; height: 50px; min-width: 50px;">
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


        {{-- تحديث عرض الرصيد مع إضافة معلومات المبلغ المدفوع --}}
        @if ($type != 21)
            @if ($showBalance)
                <div class="mt-2 text-end">
                    <div class="row">
                        <div class="col-6">
                            <label>{{ __('Current Balance: ') }}</label>
                            <span class="fw-bold text-primary">{{ number_format($currentBalance) }}</span>
                        </div>
                        <div class="col-6">
                            <label>{{ __('Balance After Invoice: ') }}</label>
                            <span class="fw-bold {{ $balanceAfterInvoice < 0 ? 'text-danger' : 'text-success' }}">
                                {{ number_format($balanceAfterInvoice) }}
                            </span>
                        </div>
                    </div>


                    @if ($received_from_client > 0 && $received_from_client != $total_after_additional)
                        <div class="row mt-1">
                            <div class="col-12">
                                <label>{{ __('Amount Due: ') }}</label>
                                <span
                                    class="fw-bold {{ $total_after_additional - $received_from_client < 0 ? 'text-success' : 'text-danger' }}">
                                    {{ number_format($total_after_additional - $received_from_client) }}
                                </span>
                            </div>
                        </div>
                    @endif
                </div>
            @endif
        @endif
    </div>


    <div class="card-body">
        <div class="row">
            <input type="hidden" wire:model="type">


            {{-- الحساب المتغير acc1 --}}
            <div class="col-lg-2" wire:key="acc1-{{ $branch_id }}">
                <div class="d-flex align-items-end gap-2">
                    <div class="flex-grow-1">
                        <livewire:app::searchable-select :model="'Modules\\Accounts\\Models\\AccHead'" :label="$acc1Role" :labelField="'aname'"
                            :placeholder="__('Search for ') . $acc1Role . __('...')" :wireModel="'acc1_id'" :selectedId="$acc1_id" :where="$this->getAcc1WhereConditions()" :searchFields="['code', 'aname']"

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
                        {{-- ✅ التحكم في إضافة عميل/مورد --}}
                        @canany(['create ' . $titles[$type], 'create invoices'])
                            <livewire:accounts::account-creator :type="$accountType" :button-class="'btn btn-sm btn-success'" :button-text="$accountType === 'client' ? __('Add Client') : __('Add Supplier')"
                                :key="'account-creator-' . $type . '-' . $branch_id" />
                        @endcanany
                    @endif
                </div>
            </div>


            {{-- المخزن acc2 --}}
            <div class="col-lg-2" wire:key="acc2-{{ $branch_id }}">
                <label class="form-label" style="font-size: 1em;">{{ $acc2Role }}</label>
                <select wire:model.live="acc2_id"
                    class="form-control form-control-sm font-family-cairo fw-bold font-14 @error('acc2_id') is-invalid @enderror"
                    style="font-size: 0.85em; height: 2em; padding: 2px 6px;"
                    @cannot('edit ' . $titles[$type]) disabled @endcannot
                    <option value="">{{ __('Select ') }} {{ $acc2Role }}</option>
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
                    class="form-control form-control-sm font-family-cairo fw-bold font-14 @error('emp_id') is-invalid @enderror"
                    style="font-size: 0.85em; height: 2em; padding: 2px 6px;"
                    @cannot('edit ' . $titles[$type]) disabled @endcannot
                    <option value="">{{ __('Select Employee') }}</option>
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
                        class="form-control form-control-sm font-family-cairo fw-bold font-14 @error('delivery_id') is-invalid @enderror"
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
                    class="form-control form-control-sm font-family-cairo fw-bold font-14 @error('pro_date') is-invalid @enderror"
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
                            class="form-control form-control-sm font-family-cairo fw-bold font-14 @error('accural_date') is-invalid @enderror"
                            style="font-size: 0.85em; height: 2em; padding: 2px 6px;"
                            @cannot('edit ' . $titles[$type]) readonly @endcannot
                        @error('accural_date')
                            <span class="invalid-feedback"><strong>{{ $message }}</strong></span>
                        @enderror
                    </div>
                @endif
            @endif


            <div class="col-lg-1">
                <label for="pro_id" class="form-label" style="font-size: 1em;">{{ __('Invoice Number') }}</label>
                <input type="number" wire:model="pro_id"
                    class="form-control form-control-sm font-family-cairo fw-bold font-14 @error('pro_id') is-invalid @enderror"
                    readonly style="font-size: 0.85em; height: 2em; padding: 2px 6px;">
                @error('pro_id')
                    <span class="invalid-feedback"><strong>{{ $message }}</strong></span>
                @enderror
            </div>


            @if ($type != 21)
                <div class="col-lg-1">
                    <label for="serial_number" class="form-label" style="font-size: 1em;">{{ __('S.N') }}</label>
                    <input type="text" wire:model="serial_number"
                        class="form-control form-control-sm font-family-cairo fw-bold font-14 @error('serial_number') is-invalid @enderror"
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
                        title: "{{ __('Remove Selected') }}"
                    }
                },
                placeholder: "{{ __('Select') }}",
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
                instance.clearOptions();
                instance.clear();


                event.acc1List.forEach(option => {
                    instance.addOption({
                        value: option.value,
                        text: option.text
                    });
                });


                const newValue = event.acc1_id;
                if (newValue) {
                    instance.setValue(newValue, true);
                } else {
                    instance.clear(true);
                }


                const balanceElement = document.querySelector('.text-primary');
                if (balanceElement) {
                    balanceElement.textContent = new Intl.NumberFormat().format(event.currentBalance);
                }
            }
        }
    });
</script>
