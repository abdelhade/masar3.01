<div class="row">
    <div class="card-header d-flex justify-content-between align-items-center flex-wrap">

        <div class="d-flex align-items-center">
            <h3 class="card-title fw-bold fs-2 m-0 me-3">
                {{ $titles[$type] }}
            </h3>
            @php
                $colorClass = '';
                if (in_array($type, [10, 14, 16, 22])) {
                    $colorClass = 'bg-primary';
                } elseif (in_array($type, [11, 15, 17])) {
                    $colorClass = 'bg-danger';
                } elseif (in_array($type, [12, 13, 18, 19, 20, 21])) {
                    $colorClass = 'bg-warning';
                }
            @endphp

            <div class="rounded-circle {{ $colorClass }}" style="width: 50px; height: 50px; min-width: 50px;">
            </div>
        </div>


        {{-- تحديث عرض الرصيد مع إضافة معلومات المبلغ المدفوع --}}
        @if ($type != 21) {{-- الرصيد لا ينطبق على التحويلات --}}
        @if ($showBalance)
            <div class="mt-2 text-end">
                <div class="row">
                    <div class="col-6">
                        <label>الرصيد الحالي: </label>
                        <span class="fw-bold text-primary">{{ number_format($currentBalance) }}</span>
                    </div>
                    <div class="col-6">
                        <label>الرصيد بعد الفاتورة: </label>
                        <span class="fw-bold {{ $balanceAfterInvoice < 0 ? 'text-danger' : 'text-success' }}">
                            {{ number_format($balanceAfterInvoice) }}
                        </span>
                    </div>
                </div>

                @if ($received_from_client > 0 && $received_from_client != $total_after_additional)
                    <div class="row mt-1">
                        <div class="col-12">
                            <label>المبلغ المستحق: </label>
                            <span
                                class="fw-bold {{ $total_after_additional - $received_from_client < 0 ? 'text-success' : 'text-danger' }}">
                                {{ number_format($total_after_additional - $received_from_client) }}
                            </span>
                        </div>
                    </div>
                @endif
            </div>
        @endif
        @endif {{-- الرصيد لا ينطبق على التحويلات --}}
    </div>

    {{-- بيانات رأس الفاتورة --}}
    <div class="card-body">
        <div class="row">
            <input type="hidden" wire:model="type">

            {{-- الحساب المتغير acc1 --}}
            <div class="col-lg-2">
                <label class="form-label" style="font-size: 1em;">{{ $acc1Role }}</label>
                <div class="input-group">
                    {{-- Tom Select Input --}}
                    <div class="flex-grow-1">
                        <x-tom-select 
                            :options="collect($acc1List)
                                ->map(fn($item) => ['value' => $item->id, 'text' => $item->aname])
                                ->toArray()" 
                            wireModel="acc1_id" 
                            :value="$acc1_id" 
                            :search="true"
                            :tomOptions="[
                                'plugins' => [
                                    'dropdown_input' => ['class' => 'font-family-cairo fw-bold font-14'],
                                    'remove_button' => ['title' => 'إزالة المحدد'],
                                ],
                                'placeholder' => 'اختر',
                            ]" 
                            class="form-control form-control-sm scnd"
                            style="font-size: 0.85em; height: 2em; padding: 2px 6px;" 
                            placeholder="اختر"
                        />
                    </div>

                    {{-- Livewire Button --}}
                    @if ($type != 21) {{-- إضافة العميل أو المورد لا ينطبق على التحويلات --}}
                    @php
                        $accountType = 'client'; // افتراضي للعملاء
                        if (in_array($type, [11, 13, 15, 17])) {
                            $accountType = 'supplier'; // للموردين
                        }
                    @endphp
                    <livewire:accounts::account-creator :type="$accountType" :button-class="'btn btn-sm btn-success'" :button-text="$accountType === 'client' ? 'إضافة عميل' : 'إضافة مورد'" />
                    @endif {{-- إضافة العميل أو المورد لا ينطبق على التحويلات --}}
                </div>
                @error('acc1_id')
                    <span class="text-danger small"><strong>{{ $message }}</strong></span>
                @enderror
            </div>

            {{-- المخزن acc2 --}}
            <div class="col-lg-2">
                <label class="form-label" style="font-size: 1em;">{{ $acc2Role }}</label>
                <select wire:model.live="acc2_id"
                    class="form-control form-control-sm font-family-cairo fw-bold font-14 @error('acc2_id') is-invalid @enderror"
                    style="font-size: 0.85em; height: 2em; padding: 2px 6px;">
                    <option value="">اختر</option>
                    @foreach ($acc2List as $acc)
                        <option value="{{ $acc->id }}">{{ $acc->aname }}</option>
                    @endforeach
                </select>
                @error('acc2_id')
                    <span class="invalid-feedback"><strong>{{ $message }}</strong></span>
                @enderror
            </div>

            {{-- الموظف --}}
            <div class="col-lg-2">
                <label for="emp_id" class="form-label" style="font-size: 1em;">{{ __('الموظف') }}</label>
                <select wire:model="emp_id" class="form-control form-control-sm font-family-cairo fw-bold font-14 @error('emp_id') is-invalid @enderror"
                    style="font-size: 0.85em; height: 2em; padding: 2px 6px;">
                    <option value="">اختر</option>
                    @foreach ($employees as $employee)
                        <option value="{{ $employee->id }}">{{ $employee->aname }}</option>
                    @endforeach
                </select>
                @error('emp_id')
                    <span class="emp_id-feedback"><strong>{{ $message }}</strong></span>
                @enderror
            </div>

            @if ($type != 21) {{-- إضافة المندوب لا ينطبق على التحويلات --}}
            <div class="col-lg-2">
                <label for="delivery_id" class="form-label" style="font-size: 1em;">{{ __('المندوب') }}</label>
                <select wire:model="delivery_id"
                    class="form-control form-control-sm font-family-cairo fw-bold font-14 @error('delivery_id') is-invalid @enderror"
                    style="font-size: 0.85em; height: 2em; padding: 2px 6px;">
                    <option value="">اختر</option>
                    @foreach ($deliverys as $delivery)
                        <option value="{{ $delivery->id }}">{{ $delivery->aname }}</option>
                    @endforeach
                </select>
                @error('delivery_id')
                    <span class="emp_id-feedback"><strong>{{ $message }}</strong></span>
                @enderror
            </div>
            @endif {{-- إضافة المندوب لا ينطبق على التحويلات --}}
            {{-- التاريخ --}}
            <div class="col-lg-1">
                <label for="pro_date" class="form-label" style="font-size: 1em;">{{ __('التاريخ') }}</label>
                <input type="date" wire:model="pro_date"
                    class="form-control form-control-sm font-family-cairo fw-bold font-14 @error('pro_date') is-invalid @enderror"
                    style="font-size: 0.85em; height: 2em; padding: 2px 6px;">
                @error('pro_date')
                    <span class="invalid-feedback"><strong>{{ $message }}</strong></span>
                @enderror
            </div>

            {{-- تاريخ الاستحقاق --}}
            @if ($type != 21) {{-- تاريخ الاستحقاق لا ينطبق على التحويلات --}}
            <div class="col-lg-1">
                <label for="accural_date" class="form-label"
                    style="font-size: 1em;"">{{ __('تاريخ الاستحقاق') }}</label>
                <input type="date" wire:model="accural_date"
                    class="form-control form-control-sm font-family-cairo fw-bold font-14 @error('accural_date') is-invalid @enderror"
                    style="font-size: 0.85em; height: 2em; padding: 2px 6px;">
                @error('accural_date')
                    <span class="invalid-feedback"><strong>{{ $message }}</strong></span>
                @enderror
            </div>
            @endif {{-- تاريخ الاستحقاق لا ينطبق على التحويلات --}}

            {{-- رقم الفاتورة (pro_id) ثابت --}}
            <div class="col-lg-1 ">
                <label for="pro_id" class="form-label" style="font-size: 1em;">{{ __('رقم الفاتورة') }}</label>
                <input type="number" wire:model="pro_id"
                    class="form-control form-control-sm font-family-cairo fw-bold font-14 @error('pro_id') is-invalid @enderror" readonly
                    style="font-size: 0.85em; height: 2em; padding: 2px 6px;">
                @error('pro_id')
                    <span class="invalid-feedback"><strong>{{ $message }}</strong></span>
                @enderror
            </div>

            {{-- S.N أو Serial Number --}}
            @if ($type != 21) {{-- S.N لا ينطبق على التحويلات --}}
            <div class="col-lg-1">
                <label for="serial_number" class="form-label" style="font-size: 1em;">{{ __('S.N') }}</label>
                <input type="text" wire:model="serial_number"
                    class="form-control form-control-sm font-family-cairo fw-bold font-14 @error('serial_number') is-invalid @enderror"
                    style="font-size: 0.85em; height: 2em; padding: 2px 6px;">
                @error('serial_number')
                    <span class="invalid-feedback"><strong>{{ $message }}</strong></span>
                @enderror
            </div>
            @endif {{-- S.N لا ينطبق على التحويلات --}}
        </div>
    </div>
</div>
<script>
    document.addEventListener('livewire:updated', () => {
        const select = document.getElementById('acc1-select');
        if (select) {
            const instance = window.getTomSelectInstance(select.id);
            if (instance) {
                const currentValue = @this.get('acc1_id');
                if (currentValue) {
                    instance.setValue(currentValue, true);
                }
            }
        }
    });
</script>
