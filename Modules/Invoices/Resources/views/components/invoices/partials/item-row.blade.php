@php
    $index = $index ?? 0;
    $item = $item ?? null;
    $price = $price ?? 0;
    $qty = $qty ?? 1;
@endphp

<tr class="align-middle invoice-row-new" data-index="{{ $index }}">
    <td class="text-center" style="width: 30px; font-weight: bold; background: #f8f9fa;">
        {{ $index + 1 }}
    </td>

    <td style="width: 18%;">
        <input type="hidden" name="items[{{ $index }}][item_id]" value="{{ $item->id }}">
        <div style="font-weight: 900; font-size: 1.1rem; color: #000;">
            {{ $item->name }}
        </div>
    </td>

    <td style="width: 10%;">
        {{ $item->code ?? '-' }}
    </td>

    <td style="width: 10%;">
        <select name="items[{{ $index }}][unit_id]" class="form-control form-control-sm">
            @foreach($units as $unit)
                <option value="{{ $unit->id }}" {{ $loop->first ? 'selected' : '' }}>{{ $unit->name }}</option>
            @endforeach
        </select>
    </td>

    <td style="width: 10%;">
        <input type="number" step="0.001" name="items[{{ $index }}][quantity]" 
               value="{{ $qty }}" class="form-control form-control-sm text-center quantity-input" 
               oninput="calculateRow(this)">
    </td>

    <td style="width: 10%;">
        <input type="number" step="0.01" name="items[{{ $index }}][price]" 
               value="{{ $price }}" class="form-control form-control-sm text-center price-input"
               oninput="calculateRow(this)">
    </td>

    <td style="width: 10%;" class="row-total">
        {{ number_format($price * $qty, 2) }}
    </td>

    <td class="text-center">
        <button type="button" class="btn btn-sm btn-danger" onclick="this.closest('tr').remove(); calculateInvoiceTotal();">
            <i class="las la-trash"></i>
        </button>
    </td>
</tr>
