<?php

use Livewire\Volt\Component;

new class extends Component {
    public array $rows = [
        [
            'type' => '',
            'options' => [''],
            'name' => '',
            'value' => '',
            'placeholder' => '',
        ],
    ];

    public function addRow()
    {
        $this->rows[] = [
            'type' => '',
            'options' => [''],
            'name' => '',
            'value' => '',
            'placeholder' => '',
        ];
    }

    public function removeRow($index)
    {
        unset($this->rows[$index]);
        $this->rows = array_values($this->rows);
    }

    public function addOption($rowIndex)
    {
        $this->rows[$rowIndex]['options'][] = '';
    }

    public function removeOption($rowIndex, $optionIndex)
    {
        unset($this->rows[$rowIndex]['options'][$optionIndex]);
        $this->rows[$rowIndex]['options'] = array_values($this->rows[$rowIndex]['options']);
    }
};
?>

<div style="font-family: 'Cairo', sans-serif; direction: rtl;">
    <table class="table table-bordered">
        <thead>
            <tr>
                <th>{{ __('type') }}</th>
                <th>{{ __('options') }}</th>
                <th>{{ __('name') }}</th>
                <th>{{ __('value') }}</th>
                <th>{{ __('placeholder') }}</th>
                <th>{{ __('action') }}</th>
            </tr>
        </thead>
        <tbody>
            @foreach($rows as $i => $row)
                <tr>
                    {{-- Type --}}
                    <td>
                        @php
                            $types = ['text', 'number', 'date', 'time', 'email', 'password', 'select'];
                        @endphp
                        <select name="type[]"
                            class="form-control input_type form-control-sm">
                            <option value="">{{ __('اختر النوع') }}</option>
                            @foreach($types as $type)
                                <option value="{{ $type }}">{{ __($type) }}</option>
                            @endforeach
                        </select>
                    </td>
                    <td>
                        @php
                            $options = ['accounts', 'clients', 'employees', 'projects', 'products', 'services', 'suppliers', 'vendors'];
                        @endphp
                        <select name="option[]"
                            class="form-control form-control-sm mb-2">
                            <option value="">{{ __('اختر خيار') }}</option>
                            @foreach($options as $opt)
                                <option value="{{ $opt }}">{{ $opt }}</option>
                            @endforeach
                        </select>
                    </td>

                    {{-- Name --}}
                    <td>
                        <input placeholder="{{ __('name') }}" type="text" wire:model="rows.{{ $i }}.name"
                            name="name[]" class="form-control form-control-sm">
                    </td>

                    {{-- Value --}}
                    <td>
                        <input placeholder="{{ __('value') }}" type="text" wire:model="rows.{{ $i }}.value"
                            name="value[]" class="form-control form-control-sm">
                    </td>

                    {{-- Placeholder --}}
                    <td>
                        <input placeholder="{{ __('placeholder') }}" type="text" name="placeholder[]" class="form-control form-control-sm">
                    </td>
                    <td>
                        <input placeholder="{{ __('label') }}" type="text" name="label[]" class="form-control form-control-sm">
                    </td>


                    {{-- Actions --}}
                    <td>
                        <button type="button" class="btn btn-danger" wire:click="removeRow({{ $i }})">x</button>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="d-flex justify-content-end">
        <button type="button" class="btn btn-success" wire:click="addRow">+</button>
    </div>
</div>