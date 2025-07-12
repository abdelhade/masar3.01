<div class="row">
    {{-- العنوان واختيار نوع السعر للفاتورة --}}
    <div class="card-header">
        <h3 class="card-title fw-bold fs-2">
            {{ $titles[$type] }}
        </h3>
    </div>
    {{-- بيانات رأس الفاتورة --}}
    <div class="card-body">
        <div class="row">
            <input type="hidden" wire:model="type">

            {{-- الحساب المتغير acc1 --}}
            <div class="col-lg-2">
                <label class="form-label" style="font-size: 1em;">{{ $acc1Role }} (acc1)</label>
                {{--
                <select wire:model="acc1_id"
                    class="form-control form-control-sm scnd @error('acc1_id') is-invalid @enderror"
                    style="font-size: 0.85em; height: 2em; padding: 2px 6px;">
                    @foreach ($acc1List as $acc)
                        <option value="{{ $acc->id }}">{{ $acc->aname }}</option>
                    @endforeach
                </select> --}}

                <x-tom-select :options="collect($acc1List)
                    ->map(fn($acc1List) => ['value' => $acc1List->id, 'text' => $acc1List->aname])
                    ->toArray()" wireModel="acc1_id" :search="true" :tomOptions="[
                    'plugins' => [
                        'dropdown_input' => ['class' => 'font-family-cairo fw-bold font-14'],
                        'remove_button' => ['title' => 'إزالة المحدد'],
                    ],
                ]" />
                @error('acc1_id')
                    <span class="invalid-feedback"><strong>{{ $message }}</strong></span>
                @enderror
            </div>

            {{-- المخزن ثابت acc2 --}}
            <div class="col-lg-2">
                <label class="form-label" style="font-size: 1em;">{{ $acc2Role }} (المخزن)</label>
                <select wire:model="acc2_id" class="form-control form-control-sm @error('acc2_id') is-invalid @enderror"
                    style="font-size: 0.85em; height: 2em; padding: 2px 6px;">
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
                <select wire:model="emp_id" class="form-control form-control-sm @error('emp_id') is-invalid @enderror"
                    style="font-size: 0.85em; height: 2em; padding: 2px 6px;">
                    @foreach ($employees as $employee)
                        <option value="{{ $employee->id }}">{{ $employee->aname }}</option>
                    @endforeach
                </select>
                @error('emp_id')
                    <span class="emp_id-feedback"><strong>{{ $message }}</strong></span>
                @enderror
            </div>

            {{-- التاريخ --}}
            <div class="col-lg-2">
                <label for="pro_date" class="form-label" style="font-size: 1em;">{{ __('التاريخ') }}</label>
                <input type="date" wire:model="pro_date"
                    class="form-control form-control-sm @error('pro_date') is-invalid @enderror"
                    style="font-size: 0.85em; height: 2em; padding: 2px 6px;">
                @error('pro_date')
                    <span class="invalid-feedback"><strong>{{ $message }}</strong></span>
                @enderror
            </div>

            {{-- تاريخ الاستحقاق --}}
            <div class="col-lg-2">
                <label for="accural_date" class="form-label"
                    style="font-size: 1em;"">{{ __('تاريخ الاستحقاق') }}</label>
                <input type="date" wire:model="accural_date"
                    class="form-control form-control-sm @error('accural_date') is-invalid @enderror"
                    style="font-size: 0.85em; height: 2em; padding: 2px 6px;">
                @error('accural_date')
                    <span class="invalid-feedback"><strong>{{ $message }}</strong></span>
                @enderror
            </div>

            {{-- رقم الفاتورة (pro_id) ثابت --}}
            <div class="col-lg-1 ">
                <label for="pro_id" class="form-label" style="font-size: 1em;">{{ __('رقم الفاتورة') }}</label>
                <input type="number" wire:model="pro_id"
                    class="form-control form-control-sm @error('pro_id') is-invalid @enderror" readonly
                    style="font-size: 0.85em; height: 2em; padding: 2px 6px;">
                @error('pro_id')
                    <span class="invalid-feedback"><strong>{{ $message }}</strong></span>
                @enderror
            </div>

            {{-- S.N أو Serial Number --}}
            <div class="col-lg-1">
                <label for="serial_number" class="form-label" style="font-size: 1em;">{{ __('S.N') }}</label>
                <input type="text" wire:model="serial_number"
                    class="form-control form-control-sm @error('serial_number') is-invalid @enderror"
                    style="font-size: 0.85em; height: 2em; padding: 2px 6px;">
                @error('serial_number')
                    <span class="invalid-feedback"><strong>{{ $message }}</strong></span>
                @enderror
            </div>

        </div>
    </div>
</div>
