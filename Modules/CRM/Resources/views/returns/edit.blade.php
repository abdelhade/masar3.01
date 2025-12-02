@extends('admin.dashboard')

@section('sidebar')
    @include('components.sidebar.crm')
@endsection

@section('content')
    {{-- عرض الأخطاء في الأعلى --}}
    @if ($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    @include('components.breadcrumb', [
        'title' => __('Edit Return'),
        'items' => [
            ['label' => __('Dashboard'), 'url' => route('admin.dashboard')],
            ['label' => __('Returns'), 'url' => route('returns.index')],
            ['label' => $return->return_number, 'url' => route('returns.show', $return->id)],
            ['label' => __('Edit')],
        ],
    ])

    <div class="row">
        <div class="col-lg-12">
            <div class="card">
                <div class="card-header">
                    <h2>{{ __('Edit Return') }} : {{ $return->return_number }}</h2>
                </div>
                <div class="card-body">
                    <form action="{{ route('returns.update', $return->id) }}" method="POST" id="returnForm">
                        @csrf
                        @method('PUT')

                        <div class="row">
                            <!-- Client -->
                            <div class="col-md-6 mb-3">
                                <label for="client_id" class="form-label">{{ __('Client') }} <span
                                        class="text-danger">*</span></label>
                                <select name="client_id" id="client_id" class="form-control" required>
                                    <option value="">{{ __('Select Client') }}</option>
                                    @foreach ($clients as $client)
                                        <option value="{{ $client->id }}"
                                            {{ old('client_id', $return->client_id) == $client->id ? 'selected' : '' }}>
                                            {{ $client->cname }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('client_id')
                                    <small class="text-danger">{{ $message }}</small>
                                @enderror
                            </div>

                            <!-- Return Date -->
                            <div class="mb-3 col-lg-3">
                                <label for="return_date" class="form-label">{{ __('Return Date') }} <span
                                        class="text-danger">*</span></label>
                                <input type="date" name="return_date" id="return_date" class="form-control"
                                    value="{{ old('return_date', $return->return_date->format('Y-m-d')) }}" required>
                                @error('return_date')
                                    <small class="text-danger">{{ $message }}</small>
                                @enderror
                            </div>

                            <!-- Return Type -->
                            <div class="mb-3 col-lg-3">
                                <label for="return_type" class="form-label">{{ __('Return Type') }} <span
                                        class="text-danger">*</span></label>
                                <select name="return_type" id="return_type" class="form-control" required>
                                    @foreach (['refund', 'exchange', 'credit_note'] as $type)
                                        <option value="{{ $type }}"
                                            {{ old('return_type', $return->return_type) == $type ? 'selected' : '' }}>
                                            {{ __(ucfirst(str_replace('_', ' ', $type))) }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('return_type')
                                    <small class="text-danger">{{ $message }}</small>
                                @enderror
                            </div>

                            <!-- Original Invoice Number -->
                            <div class="mb-3 col-lg-6">
                                <label for="original_invoice_number"
                                    class="form-label">{{ __('Original Invoice Number') }}</label>
                                <input type="text" name="original_invoice_number" class="form-control"
                                    value="{{ old('original_invoice_number', $return->original_invoice_number) }}">
                            </div>

                            <!-- Original Invoice Date -->
                            <div class="mb-3 col-lg-6">
                                <label for="original_invoice_date"
                                    class="form-label">{{ __('Original Invoice Date') }}</label>
                                <input type="date" name="original_invoice_date" class="form-control"
                                    value="{{ old('original_invoice_date', $return->original_invoice_date?->format('Y-m-d')) }}">
                            </div>

                            <!-- Reason -->
                            <div class="mb-3 col-lg-12">
                                <label for="reason" class="form-label">{{ __('Reason') }}</label>
                                <textarea name="reason" class="form-control" rows="2">{{ old('reason', $return->reason) }}</textarea>
                            </div>

                            <!-- Notes -->
                            <div class="mb-3 col-lg-9">
                                <label for="notes" class="form-label">{{ __('Notes') }}</label>
                                <textarea name="notes" class="form-control" rows="2">{{ old('notes', $return->notes) }}</textarea>
                            </div>

                            <!-- Branch (Fixed Select) -->
                            <div class="mb-3 col-lg-3">
                                <label for="branch_id" class="form-label">{{ __('Branch') }} <span
                                        class="text-danger">*</span></label>
                                <select name="branch_id" id="branch_id" class="form-control" required>
                                    <option value="">{{ __('Select Branch') }}</option>
                                    @foreach (\Modules\Branches\Models\Branch::all() as $branch)
                                        <option value="{{ $branch->id }}"
                                            {{ old('branch_id', $return->branch_id) == $branch->id ? 'selected' : '' }}>
                                            {{ $branch->name ?? ($branch->title ?? 'Branch ' . $branch->id) }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('branch_id')
                                    <small class="text-danger">{{ $message }}</small>
                                @enderror
                            </div>
                        </div>

                        <!-- Items Section -->
                        <div class="card mt-3">
                            <div class="card-header ">
                                <h5 class="mb-0">{{ __('Return Items') }}</h5>
                            </div>
                            <div class="card-body">
                                <button type="button" class="btn btn-sm btn-success mb-3" id="addItemBtn">
                                    <i class="fas fa-plus"></i> {{ __('Add Item') }}
                                </button>
                                <div class="table-responsive">
                                    <table class="table table-bordered">
                                        <thead>
                                            <tr>
                                                <th>{{ __('Item') }} <span class="text-danger">*</span></th>
                                                <th>{{ __('Quantity') }} <span class="text-danger">*</span></th>
                                                <th>{{ __('Unit Price') }} <span class="text-danger">*</span></th>
                                                <th>{{ __('Total') }}</th>
                                                <th>{{ __('Condition') }}</th>
                                                <th>{{ __('Actions') }}</th>
                                            </tr>
                                        </thead>
                                        <tbody id="itemsBody">

                                            @php
                                                $currentItems = old('items', $return->items->toArray());
                                            @endphp

                                            @foreach ($currentItems as $index => $item)
                                                <tr>
                                                    <td>
                                                        <select name="items[{{ $index }}][item_id]"
                                                            class="form-control" required>
                                                            <option value="">{{ __('Select Item') }}</option>
                                                            @foreach ($items as $product)
                                                                <option value="{{ $product->id }}"
                                                                    {{ old("items.{$index}.item_id") == $product->id || (isset($item['item_id']) && $item['item_id'] == $product->id) ? 'selected' : '' }}>
                                                                    {{ $product->name }}
                                                                </option>
                                                            @endforeach
                                                        </select>

                                                    </td>

                                                    <td>
                                                        <input type="number" name="items[{{ $index }}][quantity]"
                                                            class="form-control qty-input" step="any" min="0.01"
                                                            value="{{ $item['quantity'] }}" required>
                                                    </td>
                                                    <td>
                                                        <input type="number"
                                                            name="items[{{ $index }}][unit_price]"
                                                            class="form-control price-input" step="0.01"
                                                            min="0" value="{{ $item['unit_price'] }}" required>
                                                    </td>
                                                    <td>
                                                        <input type="number" class="form-control total-input" readonly
                                                            value="{{ number_format($item['quantity'] * $item['unit_price'], 2, '.', '') }}">
                                                    </td>
                                                    <td>
                                                        <input type="text"
                                                            name="items[{{ $index }}][item_condition]"
                                                            class="form-control"
                                                            value="{{ $item['item_condition'] ?? '' }}">
                                                    </td>
                                                    <td>
                                                        <button type="button"
                                                            class="btn btn-danger btn-sm removeItemBtn">
                                                            <i class="las la-trash"></i>
                                                        </button>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>

                        <div class="d-flex justify-content-start mt-4">
                            <button type="submit" class="btn btn-main me-2">
                                <i class="las la-save"></i> {{ __('Update') }}
                            </button>
                            <a href="{{ route('returns.index') }}" class="btn btn-danger">
                                <i class="las la-times"></i> {{ __('Cancel') }}
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        let itemIndex = {{ count($currentItems) }};
        // Items data for JS dropdowns
        const products = @json($items);

        // Function to add new row
        function addRow() {
            const tbody = document.getElementById('itemsBody');
            const row = document.createElement('tr');

            let options = '<option value="">{{ __('Select Item') }}</option>';
            products.forEach(p => {
                options += `<option value="${p.id}">${p.name}</option>`;
            });

            row.innerHTML = `
                <td>
                    <select name="items[${itemIndex}][item_id]" class="form-control" required>
                        ${options}
                    </select>
                </td>
                <td><input type="number" name="items[${itemIndex}][quantity]" class="form-control qty-input" step="any" min="0.01" required></td>
                <td><input type="number" name="items[${itemIndex}][unit_price]" class="form-control price-input" step="0.01" min="0" required></td>
                <td><input type="number" class="form-control total-input" readonly></td>
                <td><input type="text" name="items[${itemIndex}][item_condition]" class="form-control"></td>
                <td><button type="button" class="btn btn-danger btn-sm removeItemBtn"><i class="las la-trash"></i></button></td>
            `;
            tbody.appendChild(row);
            itemIndex++;

            attachEvents(row);
        }

        // Attach calculation events to row inputs
        function attachEvents(row) {
            const qtyInput = row.querySelector('.qty-input');
            const priceInput = row.querySelector('.price-input');
            const totalInput = row.querySelector('.total-input');
            const removeBtn = row.querySelector('.removeItemBtn');

            function calculate() {
                const qty = parseFloat(qtyInput.value) || 0;
                const price = parseFloat(priceInput.value) || 0;
                totalInput.value = (qty * price).toFixed(2);
            }

            qtyInput.addEventListener('input', calculate);
            priceInput.addEventListener('input', calculate);
            removeBtn.addEventListener('click', function() {
                row.remove();
            });
        }

        // Initialize existing rows
        document.querySelectorAll('#itemsBody tr').forEach(row => {
            attachEvents(row);
        });

        document.getElementById('addItemBtn').addEventListener('click', addRow);
    </script>
@endsection
