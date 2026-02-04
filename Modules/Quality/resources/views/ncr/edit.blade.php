@extends('admin.dashboard')

@section('sidebar')
    @include('components.sidebar.quality')
@endsection

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-12">
            <h2><i class="fas fa-edit me-2"></i>{{ __("Edit Report") }}: {{ $ncr->ncr_number }}</h2>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('quality.dashboard') }}">{{ __("Quality") }}</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('quality.ncr.index') }}">{{ __("NCR") }}</a></li>
                    <li class="breadcrumb-item active">{{ __("Edit") }}</li>
                </ol>
            </nav>
        </div>
    </div>

    <form action="{{ url('/quality/ncr/' . $ncr->id) }}" method="POST" enctype="multipart/form-data">
        @csrf
        @method('PUT')
        <div class="row">
            <div class="col-lg-8">
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0">{{ __("Non-Conformance Information") }}</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label required">{{ __("Item") }}</label>
                                <select name="item_id" class="form-select @error('item_id') is-invalid @enderror" required>
                                    <option value="">{{ __("Select Item") }}</option>
                                    @foreach($items as $item)
                                        <option value="{{ $item->id }}" {{ old('item_id', $ncr->item_id) == $item->id ? 'selected' : '' }}>{{ $item->name }}</option>
                                    @endforeach
                                </select>
                                @error('item_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>

                            <div class="col-md-6 mb-3">
                                <label class="form-label">{{ __("Batch Number") }}</label>
                                <input type="text" name="batch_number" class="form-control" 
                                       value="{{ old('batch_number', $ncr->batch_number) }}" placeholder="{{ __("Optional") }}">
                            </div>

                            <div class="col-md-6 mb-3">
                                <label class="form-label required">{{ __("Detection Date") }}</label>
                                <input type="date" name="detected_date" 
                                       class="form-control @error('detected_date') is-invalid @enderror" 
                                       value="{{ old('detected_date', $ncr->detected_date?->format('Y-m-d')) }}" required>
                                @error('detected_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>

                            <div class="col-md-6 mb-3">
                                <label class="form-label required">{{ __("Affected Quantity") }}</label>
                                <input type="number" step="0.001" name="affected_quantity" 
                                       class="form-control @error('affected_quantity') is-invalid @enderror" 
                                       value="{{ old('affected_quantity', $ncr->affected_quantity) }}" required>
                                @error('affected_quantity')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>

                            <div class="col-md-6 mb-3">
                                <label class="form-label required">{{ __("Source") }}</label>
                                <select name="source" class="form-select @error('source') is-invalid @enderror" required>
                                    <option value="">{{ __("Select Source") }}</option>
                                    <option value="receiving_inspection" {{ old('source', $ncr->source) == 'receiving_inspection' ? 'selected' : '' }}>{{ __("Receiving Inspection") }}</option>
                                    <option value="in_process" {{ old('source', $ncr->source) == 'in_process' ? 'selected' : '' }}>{{ __("In-Process Inspection") }}</option>
                                    <option value="final_inspection" {{ old('source', $ncr->source) == 'final_inspection' ? 'selected' : '' }}>{{ __("Final Inspection") }}</option>
                                    <option value="customer_complaint" {{ old('source', $ncr->source) == 'customer_complaint' ? 'selected' : '' }}>{{ __("Customer Complaint Inspection") }}</option>
                                    <option value="internal_audit" {{ old('source', $ncr->source) == 'internal_audit' ? 'selected' : '' }}>{{ __("Internal") }}</option>
                                    <option value="supplier_notification" {{ old('source', $ncr->source) == 'supplier_notification' ? 'selected' : '' }}>{{ __("Supplier Notification") }}</option>
                                </select>
                                @error('source')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>

                            <div class="col-12 mb-3">
                                <label class="form-label required">{{ __("Problem Description") }}</label>
                                <textarea name="problem_description" rows="4" 
                                          class="form-control @error('problem_description') is-invalid @enderror" 
                                          placeholder="{{ __("Explain the problem in detail...") }}" required>{{ old('problem_description', $ncr->problem_description) }}</textarea>
                                @error('problem_description')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>

                            <div class="col-12 mb-3">
                                <label class="form-label">{{ __("Immediate Action") }}</label>
                                <textarea name="immediate_action" rows="3" class="form-control" 
                                          placeholder="{{ __("What immediate action was taken?") }}">{{ old('immediate_action', $ncr->immediate_action) }}</textarea>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0">{{ __("Classification and Priority") }}</h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label class="form-label required">{{ __("Severity") }}</label>
                            <select name="severity" class="form-select @error('severity') is-invalid @enderror" required>
                                <option value="minor" {{ old('severity', $ncr->severity) == 'minor' ? 'selected' : '' }}>{{ __("Minor") }}</option>
                                <option value="major" {{ old('severity', $ncr->severity) == 'major' ? 'selected' : '' }}>{{ __("Major") }}</option>
                                <option value="critical" {{ old('severity', $ncr->severity) == 'critical' ? 'selected' : '' }}>{{ __("Critical") }}</option>
                            </select>
                            @error('severity')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label">{{ __("Estimated Cost") }}</label>
                            <input type="number" step="0.01" name="estimated_cost" 
                                   class="form-control" value="{{ old('estimated_cost', $ncr->estimated_cost) }}">
                        </div>

                        <div class="mb-3">
                            <label class="form-label">{{ __("Disposition") }}</label>
                            <select name="disposition" class="form-select">
                                <option value="">{{ __("To Be Determined") }}</option>
                                <option value="rework" {{ old('disposition', $ncr->disposition) == 'rework' ? 'selected' : '' }}>{{ __("Rework") }}</option>
                                <option value="scrap" {{ old('disposition', $ncr->disposition) == 'scrap' ? 'selected' : '' }}>{{ __("Scrap") }}</option>
                                <option value="return_to_supplier" {{ old('disposition', $ncr->disposition) == 'return_to_supplier' ? 'selected' : '' }}>{{ __("Return to Supplier") }}</option>
                                <option value="use_as_is" {{ old('disposition', $ncr->disposition) == 'use_as_is' ? 'selected' : '' }}>{{ __("Use As Is") }}</option>
                                <option value="repair" {{ old('disposition', $ncr->disposition) == 'repair' ? 'selected' : '' }}>{{ __("Repair") }}</option>
                                <option value="downgrade" {{ old('disposition', $ncr->disposition) == 'downgrade' ? 'selected' : '' }}>{{ __("Downgrade") }}</option>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">{{ __("Assigned To") }}</label>
                            <select name="assigned_to" class="form-select">
                                <option value="">{{ __("Select Responsible") }}</option>
                                @foreach($users as $user)
                                    <option value="{{ $user->id }}" {{ old('assigned_to', $ncr->assigned_to) == $user->id ? 'selected' : '' }}>{{ $user->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">{{ __("Target Closure Date") }}</label>
                            <input type="date" name="target_closure_date" 
                                   class="form-control" value="{{ old('target_closure_date', $ncr->target_closure_date?->format('Y-m-d')) }}">
                        </div>

                        <div class="mb-3">
                            <label class="form-label">{{ __("Attachments") }}</label>
                            <input type="file" name="attachments[]" class="form-control" multiple>
                            <small class="text-muted">{{ __("Photos, reports, certificates") }}</small>
                        </div>
                    </div>
                </div>

                <div class="d-grid gap-2">
                    <button type="submit" class="btn btn-warning btn-lg">
                        <i class="fas fa-save me-2"></i>{{ __("Update Report") }}
                    </button>
                    <a href="{{ route('quality.ncr.index') }}" class="btn btn-secondary">
                        <i class="fas fa-times me-2"></i>{{ __("Cancel") }}
                    </a>
                </div>
            </div>
        </div>
    </form>
</div>
@endsection