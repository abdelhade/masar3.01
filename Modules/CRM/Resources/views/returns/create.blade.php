@extends('admin.dashboard')

@section('sidebar')
    @include('components.sidebar.crm')
@endsection

@section('content')
    @include('components.breadcrumb', [
        'title' => __('Returns'),
        'items' => [
            ['label' => __('Dashboard'), 'url' => route('admin.dashboard')],
            ['label' => __('Returns'), 'url' => route('returns.index')],
            ['label' => __('Create')],
        ],
    ])
@if ($errors->any())
    <div class="alert alert-danger">
        <ul class="mb-0">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

    <div class="row">
        <div class="col-lg-12">
            <div class="card">
                <div class="card-header">
                    <h2>{{ __('Add New Return') }}</h2>
                </div>
                <div class="card-body">
                    <form action="{{ route('returns.store') }}" method="POST" id="returnForm">
                        @csrf

                        <div class="row">
                            <!-- Client -->
                            <div class="col-md-6 mb-3">
                                <x-dynamic-search name="client_id" :label="__('Client')" column="cname"
                                    model="App\Models\Client" :placeholder="__('Search for client...')" :required="true" :class="'form-select'" />
                            </div>

                            <!-- Return Date -->
                            <div class="mb-3 col-lg-3">
                                <label for="return_date" class="form-label">{{ __('Return Date') }}</label>
                                <input type="date" name="return_date" id="return_date" class="form-control"
                                    value="{{ old('return_date', date('Y-m-d')) }}" required>
                                @error('return_date')
                                    <small class="text-danger">{{ $message }}</small>
                                @enderror
                            </div>

                            <!-- Return Type -->
                            <div class="mb-3 col-lg-3">
                                <label for="return_type" class="form-label">{{ __('Return Type') }}</label>
                                <select name="return_type" id="return_type" class="form-control" required>
                                    <option value="refund" {{ old('return_type') == 'refund' ? 'selected' : '' }}>
                                        {{ __('Refund') }}</option>
                                    <option value="exchange" {{ old('return_type') == 'exchange' ? 'selected' : '' }}>
                                        {{ __('Exchange') }}</option>
                                    <option value="credit_note"
                                        {{ old('return_type') == 'credit_note' ? 'selected' : '' }}>
                                        {{ __('Credit Note') }}</option>
                                </select>
                                @error('return_type')
                                    <small class="text-danger">{{ $message }}</small>
                                @enderror
                            </div>

                            <!-- Original Invoice Number -->
                            <div class="mb-3 col-lg-6">
                                <label for="original_invoice_number"
                                    class="form-label">{{ __('Original Invoice Number') }}</label>
                                <input type="text" name="original_invoice_number" id="original_invoice_number"
                                    class="form-control" value="{{ old('original_invoice_number') }}">
                                @error('original_invoice_number')
                                    <small class="text-danger">{{ $message }}</small>
                                @enderror
                            </div>

                            <!-- Original Invoice Date -->
                            <div class="mb-3 col-lg-6">
                                <label for="original_invoice_date"
                                    class="form-label">{{ __('Original Invoice Date') }}</label>
                                <input type="date" name="original_invoice_date" id="original_invoice_date"
                                    class="form-control" value="{{ old('original_invoice_date') }}">
                                @error('original_invoice_date')
                                    <small class="text-danger">{{ $message }}</small>
                                @enderror
                            </div>

                            <!-- Reason -->
                            <div class="mb-3 col-lg-12">
                                <label for="reason" class="form-label">{{ __('Reason') }}</label>
                                <textarea name="reason" id="reason" class="form-control" rows="2">{{ old('reason') }}</textarea>
                                @error('reason')
                                    <small class="text-danger">{{ $message }}</small>
                                @enderror
                            </div>

                            <!-- Notes -->
                            <div class="mb-3 col-lg-9">
                                <label for="notes" class="form-label">{{ __('Notes') }}</label>
                                <textarea name="notes" id="notes" class="form-control" rows="2">{{ old('notes') }}</textarea>
                                @error('notes')
                                    <small class="text-danger">{{ $message }}</small>
                                @enderror
                            </div>

                            <div class="mb-3 col-lg-3">
                                <x-branches::branch-select :branches="$branches" />
                            </div>
                        </div>

                        <!-- Items Section -->
                        <div class="card mt-3">
                            <div class="card-header">
                                <h5 class="mb-0">{{ __('Return Items') }}</h5>
                            </div>
                            <div class="card-body">
                                <button type="button" class="btn btn-sm btn-success mb-3" id="addItemBtn">
                                    <i class="fas fa-plus"></i> {{ __('Add Item') }}
                                </button>
                                <div class="table-responsive">
                                    <table class="table table-bordered" id="itemsTable">
                                        <thead>
                                            <tr>
                                                <th>{{ __('Item') }}</th>
                                                <th>{{ __('Quantity') }}</th>
                                                <th>{{ __('Unit Price') }}</th>
                                                <th>{{ __('Total') }}</th>
                                                <th>{{ __('Condition') }}</th>
                                                <th>{{ __('Actions') }}</th>
                                            </tr>
                                        </thead>
                                        <tbody id="itemsBody">
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>

                        <!-- Save Buttons -->
                        <div class="d-flex justify-content-start mt-4">
                            <button type="submit" class="btn btn-main me-2">
                                <i class="las la-save"></i> {{ __('Save') }}
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
        let itemIndex = 0;
        const items = @json($items);

        document.getElementById('addItemBtn').addEventListener('click', function() {
            const tbody = document.getElementById('itemsBody');
            const row = document.createElement('tr');
            row.innerHTML = `
                <td>
                    <select name="items[${itemIndex}][item_id]" class="form-control" required>
                        <option value="">{{ __('Select Item') }}</option>
                        ${items.map(item => `<option value="${item.id}">${item.name}</option>`).join('')}
                    </select>
                </td>
                <td><input type="number" name="items[${itemIndex}][quantity]" class="form-control" min="1" required></td>
                <td><input type="number" name="items[${itemIndex}][unit_price]" class="form-control" step="0.01" min="0" required></td>
                <td><input type="number" class="form-control total-price" readonly></td>
                <td><input type="text" name="items[${itemIndex}][item_condition]" class="form-control"></td>
                <td><button type="button" class="btn btn-danger btn-sm removeItemBtn"><i class="las la-trash"></i></button></td>
            `;
            tbody.appendChild(row);
            itemIndex++;

            // Calculate total on quantity/price change
            const qtyInput = row.querySelector('input[name*="[quantity]"]');
            const priceInput = row.querySelector('input[name*="[unit_price]"]');
            const totalInput = row.querySelector('.total-price');

            function calculateTotal() {
                const qty = parseFloat(qtyInput.value) || 0;
                const price = parseFloat(priceInput.value) || 0;
                totalInput.value = (qty * price).toFixed(2);
            }

            qtyInput.addEventListener('input', calculateTotal);
            priceInput.addEventListener('input', calculateTotal);

            // Remove row
            row.querySelector('.removeItemBtn').addEventListener('click', function() {
                row.remove();
            });
        });

        // Add first row automatically
        document.getElementById('addItemBtn').click();
    </script>
@endsection
