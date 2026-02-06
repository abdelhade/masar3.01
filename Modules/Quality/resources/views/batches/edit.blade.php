@extends('admin.dashboard')

@section('sidebar')
    @include('components.sidebar.quality')
@endsection

@section('content')
    <div class="container-fluid">
        <div class="row mb-4">
            <div class="col-12">
                <h2><i class="fas fa-edit me-2"></i>{{ __("Edit Batch") }}</h2>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('quality.dashboard') }}">{{ __("Quality") }}</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('quality.batches.index') }}">{{ __("Batch Tracking") }}</a></li>
                        <li class="breadcrumb-item active">{{ __("Edit") }}</li>
                    </ol>
                </nav>
            </div>
        </div>

        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">{{ __("Batch Details") }}</h5>
                    </div>
                    <div class="card-body">
                        <form action="{{ route('quality.batches.update', $batch) }}" method="POST">
                            @csrf
                            @method('PUT')
                            <div class="row">
                                    <!-- رقم الدفعة -->
                                    <div class="col-md-6 mb-3">
                                        <label for="batch_number" class="form-label">{{ __("Batch Number") }}</label>
                                        <input type="text" class="form-control" id="batch_number" name="batch_number"
                                            value="{{ $batch->batch_number }}" readonly>
                                    </div>

                                    <!-- {{ __("Item") }} -->
                                    <div class="col-md-6 mb-3">
                                        <label for="item_id" class="form-label">{{ __("Item") }}</label>
                                        <input type="text" class="form-control" value="{{ $batch->item->name ?? '---' }}"
                                            readonly>
                                    </div>

                                    <!-- تاريخ الإنتاج -->
                                    <div class="col-md-6 mb-3">
                                        <label for="production_date" class="form-label">{{ __("Production Date") }} <span
                                                class="text-danger">*</span></label>
                                        <input type="date"
                                            class="form-control @error('production_date') is-invalid @enderror"
                                            id="production_date" name="production_date"
                                            value="{{ old('production_date', $batch->production_date?->format('Y-m-d')) }}"
                                            required>
                                        @error('production_date')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <!-- تاريخ الصلاحية -->
                                    <div class="col-md-6 mb-3">
                                        <label for="expiry_date" class="form-label">{{ __("Expiry Date") }}</label>
                                        <input type="date"
                                            class="form-control @error('expiry_date') is-invalid @enderror" id="expiry_date"
                                            name="expiry_date"
                                            value="{{ old('expiry_date', $batch->expiry_date?->format('Y-m-d')) }}">
                                        @error('expiry_date')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <!-- {{ __("Quantity") }} المتبقية -->
                                    <div class="col-md-6 mb-3">
                                        <label for="remaining_quantity" class="form-label">{{ __("Remaining Quantity") }} <span
                                                class="text-danger">*</span></label>
                                        <input type="number" step="0.001"
                                            class="form-control @error('remaining_quantity') is-invalid @enderror"
                                            id="remaining_quantity" name="remaining_quantity"
                                            value="{{ old('remaining_quantity', $batch->remaining_quantity) }}"
                                            min="0" required>
                                        @error('remaining_quantity')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <!-- حالة {{ __("Quality") }} -->
                                    <div class="col-md-6 mb-3">
                                        <label for="quality_status" class="form-label">{{ __("Quality Status") }} <span
                                                class="text-danger">*</span></label>
                                        <select class="form-select @error('quality_status') is-invalid @enderror"
                                            id="quality_status" name="quality_status" required>
                                            <option value="">-- {{ __("Select Status") }} --</option>
                                            <option value="passed"
                                                {{ old('quality_status', $batch->quality_status) == 'passed' ? 'selected' : '' }}>
                                                {{ __("Passed") }}</option>
                                            <option value="failed"
                                                {{ old('quality_status', $batch->quality_status) == 'failed' ? 'selected' : '' }}>
                                                {{ __("Failed") }}</option>
                                            <option value="conditional"
                                                {{ old('quality_status', $batch->quality_status) == 'conditional' ? 'selected' : '' }}>
                                                {{ __("Conditional") }}</option>
                                            <option value="quarantine"
                                                {{ old('quality_status', $batch->quality_status) == 'quarantine' ? 'selected' : '' }}>
                                                {{ __("Quarantine") }}</option>
                                        </select>
                                        @error('quality_status')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <!-- الحالة -->
                                    <div class="col-md-6 mb-3">
                                        <label for="status" class="form-label">{{ __("Status") }} <span
                                                class="text-danger">*</span></label>
                                        <select class="form-select @error('status') is-invalid @enderror" id="status"
                                            name="status" required>
                                            <option value="active"
                                                {{ old('status', $batch->status) == 'active' ? 'selected' : '' }}>{{ __("Active") }}
                                            </option>
                                            <option value="consumed"
                                                {{ old('status', $batch->status) == 'consumed' ? 'selected' : '' }}>{{ __("Consumed") }}
                                            </option>
                                            <option value="expired"
                                                {{ old('status', $batch->status) == 'expired' ? 'selected' : '' }}>{{ __("Expired") }}</option>
                                            <option value="rejected"
                                                {{ old('status', $batch->status) == 'rejected' ? 'selected' : '' }}>{{ __("Rejected") }}
                                            </option>
                                        </select>
                                        @error('status')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <!-- المورد -->
                                    <div class="col-md-6 mb-3">
                                        <label for="supplier_id" class="form-label">{{ __("Supplier") }}</label>
                                        <select class="form-select @error('supplier_id') is-invalid @enderror"
                                            id="supplier_id" name="supplier_id">
                                            <option value="">-- {{ __("Select Supplier (Optional)") }} --</option>
                                            @foreach ($suppliers as $supplier)
                                                <option value="{{ $supplier->id }}"
                                                    {{ old('supplier_id', $batch->supplier_id) == $supplier->id ? 'selected' : '' }}>
                                                    {{ $supplier->aname }}
                                                </option>
                                            @endforeach
                                        </select>
                                        @error('supplier_id')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <!-- المستودع -->
                                    <div class="col-md-6 mb-3">
                                        <label for="warehouse_id" class="form-label">{{ __("Warehouse") }}</label>
                                        <select class="form-select @error('warehouse_id') is-invalid @enderror"
                                            id="warehouse_id" name="warehouse_id">
                                            <option value="">-- {{ __("Select Warehouse") }} --</option>
                                            @foreach ($warehouses as $warehouse)
                                                <option value="{{ $warehouse->id }}"
                                                    {{ old('warehouse_id', $batch->warehouse_id) == $warehouse->id ? 'selected' : '' }}>
                                                    {{ $warehouse->aname }}
                                                </option>
                                            @endforeach
                                        </select>
                                        @error('warehouse_id')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <!-- الموقع -->
                                    <div class="col-md-12 mb-3">
                                        <label for="location" class="form-label">{{ __("Storage Location") }}</label>
                                        <input type="text" class="form-control @error('location') is-invalid @enderror"
                                            id="location" name="location"
                                            value="{{ old('location', $batch->location) }}"
                                            placeholder="{{ __("Example: Shelf A - Level 2 - Position 5") }}">
                                        @error('location')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <!-- ملاحظات -->
                                    <div class="col-md-12 mb-3">
                                        <label for="notes" class="form-label">{{ __("Notes") }}</label>
                                        <textarea class="form-control @error('notes') is-invalid @enderror" id="notes" name="notes" rows="3">{{ old('notes', $batch->notes) }}</textarea>
                                        @error('notes')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                            </div>
                            
                            <div class="d-flex justify-content-end gap-2 mt-4">
                                <a href="{{ route('quality.batches.show', $batch) }}" class="btn btn-secondary">
                                    <i class="fas fa-times me-2"></i>{{ __("Cancel") }}
                                </a>
                                <button type="submit" class="btn btn-success">
                                    <i class="fas fa-save me-2"></i>{{ __("Save Changes") }}
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const productionDate = document.getElementById('production_date');
            const expiryDate = document.getElementById('expiry_date');

            // Ensure expiry date is after production date
            productionDate.addEventListener('change', function() {
                expiryDate.min = this.value;
                if (expiryDate.value && expiryDate.value < this.value) {
                    expiryDate.value = '';
                }
            });

            // Set initial min value if production date exists
            if (productionDate.value) {
                expiryDate.min = productionDate.value;
            }
        });
    </script>
@endsection
