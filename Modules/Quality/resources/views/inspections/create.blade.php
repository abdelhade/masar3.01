@extends('admin.dashboard')

@section('sidebar')
    @include('components.sidebar.quality')
@endsection

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-12">
            <h2><i class="fas fa-plus-circle me-2"></i>{{ __('New Quality Inspection') }}</h2>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('quality.dashboard') }}">{{ __('Quality') }}</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('quality.inspections.index') }}">{{ __('Inspections') }}</a></li>
                    <li class="breadcrumb-item active">{{ __('New') }}</li>
                </ol>
            </nav>
        </div>
    </div>

    <form action="{{ route('quality.inspections.store') }}" method="POST" enctype="multipart/form-data">
        @csrf
        <div class="row">
            <div class="col-lg-8">
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0">{{ __('Inspection Details') }}</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label required">{{ __("Item") }}</label>
                                <select name="item_id" class="form-select @error('item_id') is-invalid @enderror" required>
                                    <option value="">{{ __('Select Item') }}</option>
                                    @foreach($items as $item)
                                        <option value="{{ $item->id }}">{{ $item->name }}</option>
                                    @endforeach
                                </select>
                                @error('item_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>

                            <div class="col-md-6 mb-3">
                                <label class="form-label required">{{ __('Inspection Type') }}</label>
                                <select name="inspection_type" class="form-select @error('inspection_type') is-invalid @enderror" required>
                                    <option value="">{{ __('Select Type') }}</option>
                                    <option value="receiving">{{ __('Receiving Inspection') }}</option>
                                    <option value="in_process">{{ __('In-Process Inspection') }}</option>
                                    <option value="final">{{ __('Final Inspection') }}</option>
                                    <option value="random">{{ __('Random Inspection') }}</option>
                                    <option value="customer_complaint">{{ __('Customer Complaint Inspection') }}</option>
                                </select>
                                @error('inspection_type')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>

                            <div class="col-md-6 mb-3">
                                <label class="form-label">{{ __('Supplier') }}</label>
                                <select name="supplier_id" class="form-select">
                                    <option value="">{{ __('Select Supplier (Optional)') }}</option>
                                    @foreach($suppliers as $supplier)
                                        <option value="{{ $supplier->id }}">{{ $supplier->aname }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label class="form-label required">{{ __('Inspection Date') }}</label>
                                <input type="date" name="inspection_date" 
                                       class="form-control @error('inspection_date') is-invalid @enderror" 
                                       value="{{ date('Y-m-d') }}" required>
                                @error('inspection_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>

                            <div class="col-md-4 mb-3">
                                <label class="form-label required">{{ __('Inspected Quantity') }}</label>
                                <input type="number" step="0.001" name="quantity_inspected" 
                                       class="form-control @error('quantity_inspected') is-invalid @enderror" required>
                                @error('quantity_inspected')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>

                            <div class="col-md-4 mb-3">
                                <label class="form-label required">{{ __('Passed Quantity') }}</label>
                                <input type="number" step="0.001" name="pass_quantity" 
                                       class="form-control @error('pass_quantity') is-invalid @enderror" required>
                                @error('pass_quantity')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>

                            <div class="col-md-4 mb-3">
                                <label class="form-label required">{{ __('Failed Quantity') }}</label>
                                <input type="number" step="0.001" name="fail_quantity" 
                                       class="form-control @error('fail_quantity') is-invalid @enderror" required>
                                @error('fail_quantity')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>

                            <div class="col-12 mb-3">
                                <label class="form-label">{{ __('Defects Found') }}</label>
                                <textarea name="defects_found" rows="3" class="form-control" 
                                          placeholder="{{ __('List defects found...') }}"></textarea>
                            </div>

                            <div class="col-12 mb-3">
                                <label class="form-label">{{ __('Inspector Notes') }}</label>
                                <textarea name="inspector_notes" rows="3" class="form-control" 
                                          placeholder="{{ __('Additional notes...') }}"></textarea>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0">{{ __('Result') }} {{ __('and Action') }}</h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label class="form-label required">{{ __('Inspection Result') }}</label>
                            <select name="result" class="form-select @error('result') is-invalid @enderror" required>
                                <option value="pass">{{ __('Pass') }}</option>
                                <option value="fail">{{ __('Fail') }}</option>
                                <option value="conditional">{{ __('Conditional') }}</option>
                            </select>
                            @error('result')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label required">{{ __('Action Taken') }}</label>
                            <select name="action_taken" class="form-select @error('action_taken') is-invalid @enderror" required>
                                <option value="accepted">{{ __('Accepted') }}</option>
                                <option value="rejected">{{ __('Rejected') }}</option>
                                <option value="rework">{{ __('Rework') }}</option>
                                <option value="conditional_accept">{{ __('Conditional Accept') }}</option>
                                <option value="pending_review">{{ __('Pending Review') }}</option>
                            </select>
                            @error('action_taken')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label">{{ __('Batch Number') }}</label>
                            <input type="text" name="batch_number" class="form-control" 
                                   placeholder="{{ __('Optional') }}">
                        </div>

                        <div class="mb-3">
                            <label class="form-label">{{ __('Attachments') }}</label>
                            <input type="file" name="attachments[]" class="form-control" multiple>
                            <small class="text-muted">{{ __('Photos, reports, certificates') }}</small>
                        </div>
                    </div>
                </div>

                <div class="d-grid gap-2">
                    <button type="submit" class="btn btn-success btn-lg">
                        <i class="fas fa-save me-2"></i>{{ __('Save Inspection') }}
                    </button>
                    <a href="{{ route('quality.inspections.index') }}" class="btn btn-secondary">
                        <i class="fas fa-times me-2"></i>{{ __("Cancel") }}
                    </a>
                </div>
            </div>
        </div>
    </form>
</div>
@endsection

