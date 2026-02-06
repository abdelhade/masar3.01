<table class="table table-bordered">
    <thead>
        <tr>
            <th>{{ __('general.item') }}</th>
            <th>{{ __('general.default_quantity') }}</th>
            <th>{{ __('general.action') }}</th>
        </tr>
    </thead>
    <tbody id="items-table">
        @if(old('items'))
            @foreach(old('items') as $i => $item)
                <tr>
                    <td>
                        <select name="items[{{ $i }}][work_item_id]" class="form-select" required>
                            <option value="">{{ __('general.choose_item') }}</option>
                            @foreach($workItems as $workItem)
                                <option value="{{ $workItem->id }}"
                                    {{ $item['work_item_id'] == $workItem->id ? 'selected' : '' }}>
                                    {{ $workItem->name }}
                                </option>
                            @endforeach
                        </select>
                    </td>
                    <td>
                        <input type="number" name="items[{{ $i }}][default_quantity]"
                               class="form-control"
                               value="{{ $item['default_quantity'] ?? 1 }}" min="1">
                    </td>
                    <td>
                        <button type="button" class="btn btn-danger btn-sm remove-row">{{ __('general.delete') }}</button>
                    </td>
                </tr>
            @endforeach
        @endif
    </tbody>
</table>

<button type="button" id="add-row" class="btn btn-primary btn-sm">+ {{ __('general.add_item') }}</button>

<script>
document.addEventListener('DOMContentLoaded', function () {
    let rowIndex = {{ old('items') ? count(old('items')) : 0 }};

    document.getElementById('add-row').addEventListener('click', function () {
        let table = document.getElementById('items-table');
        let newRow = document.createElement('tr');
        newRow.innerHTML = `
            <td>
                <select name="items[${rowIndex}][work_item_id]" class="form-select" required>
                    <option value="">{{ __('general.choose_item') }}</option>
                    @foreach($workItems as $workItem)
                        <option value="{{ $workItem->id }}">{{ $workItem->name }}</option>
                    @endforeach
                </select>
            </td>
            <td>
                <input type="number" name="items[${rowIndex}][default_quantity]"
                       class="form-control" value="1" min="1">
            </td>
            <td>
                <button type="button" class="btn btn-danger btn-sm remove-row">{{ __('general.delete') }}</button>
            </td>
        `;
        table.appendChild(newRow);
        rowIndex++;
    });

    document.addEventListener('click', function (e) {
        if (e.target.classList.contains('remove-row')) {
            e.target.closest('tr').remove();
        }
    });
});
</script>
