@extends('admin.dashboard')

@section('sidebar')
    @include('components.sidebar.rentals')
@endsection

@section('content')
    {{-- Breadcrumb --}}
    @include('components.breadcrumb', [
        'title' => __('Add New Lease'),
        'items' => [
            ['label' => __('Dashboard'), 'url' => route('admin.dashboard')],
            ['label' => __('Leases'), 'url' => route('rentals.leases.index')],
            ['label' => __('Add Lease')],
        ],
    ])

    <div class="container-fluid px-4">
        @can('create Leases')
            <div class="card shadow-sm">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-file-contract me-2"></i>
                        {{ __('Add New Lease') }}
                    </h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('rentals.leases.store') }}" method="POST">
                        @csrf

                        <div class="row">
                            {{-- Unit --}}
                            <div class="col-md-6 mb-3">
                                <label class="form-label">{{ __('Unit') }} <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-building"></i></span>
                                    <select name="unit_id" class="form-select @error('unit_id') is-invalid @enderror" required>
                                        <option value="">-- {{ __('Select Unit') }} --</option>
                                        @foreach ($units as $id => $name)
                                            <option value="{{ $id }}" @selected(old('unit_id') == $id)>
                                                {{ $name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                @error('unit_id')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>

                            {{-- Client --}}
                            <div class="col-md-6 mb-3">
                                <x-dynamic-search name="client_id" label="{{ __('Client') }}" column="aname"
                                    model="Modules\Accounts\Models\AccHead" placeholder="{{ __('Search for client...') }}" :required="true"
                                    :class="'form-select'" :filters="['acc_type' => 1, 'is_basic' => 0, 'isdeleted' => 0]" />
                            </div>

                            {{-- Start Date --}}
                            <div class="col-md-6 mb-3">
                                <label class="form-label">{{ __('Start Date') }} <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-calendar-alt"></i></span>
                                    <input type="date" name="start_date"
                                        class="form-control @error('start_date') is-invalid @enderror"
                                        value="{{ old('start_date') }}" required>
                                </div>
                                @error('start_date')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>

                            {{-- End Date --}}
                            <div class="col-md-6 mb-3">
                                <label class="form-label">{{ __('End Date') }} <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-calendar-check"></i></span>
                                    <input type="date" name="end_date"
                                        class="form-control @error('end_date') is-invalid @enderror"
                                        value="{{ old('end_date') }}" required>
                                </div>
                                @error('end_date')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>

                            {{-- Rent Amount --}}
                            <div class="col-md-6 mb-3">
                                <label class="form-label">{{ __('Rent Amount') }} <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-money-bill-wave"></i></span>
                                    <input type="number" step="0.01" name="rent_amount"
                                        class="form-control @error('rent_amount') is-invalid @enderror"
                                        value="{{ old('rent_amount') }}" required>
                                </div>
                                @error('rent_amount')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>

                            {{-- Account --}}
                            <div class="col-md-6 mb-3">
                                <label class="form-label">{{ __('Account') }} <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-cash-register"></i></span>
                                    <select name="acc_id" class="form-select @error('acc_id') is-invalid @enderror" required>
                                        @foreach ($paymantAccount as $account)
                                            <option value="{{ $account->id }}" @selected(old('acc_id') == $account->id)>
                                                {{ $account->aname }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                @error('acc_id')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>

                            {{-- Status --}}
                            <div class="col-md-6 mb-3">
                                <label class="form-label">{{ __('Status') }} <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-toggle-on"></i></span>
                                    <select name="status" class="form-select @error('status') is-invalid @enderror" required>
                                        @foreach (\Modules\Rentals\Enums\LeaseStatus::cases() as $status)
                                            <option value="{{ $status->value }}" @selected(old('status', $lease->status ?? \Modules\Rentals\Enums\LeaseStatus::PENDING->value) == $status->value)>
                                                {{ $status->label() }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                @error('status')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>

                            {{-- Notes --}}
                            <div class="col-12 mb-3">
                                <label class="form-label">{{ __('Notes') }}</label>
                                <textarea name="notes" class="form-control @error('notes') is-invalid @enderror" rows="4">{{ old('notes') }}</textarea>
                                @error('notes')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="card-footer text-end bg-transparent border-top pt-3 pe-0">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-2"></i>{{ __('Save Lease') }}
                            </button>
                            <a href="{{ route('rentals.leases.index') }}" class="btn btn-secondary">
                                <i class="fas fa-times me-2"></i>{{ __('Cancel') }}
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        @endcan
    </div>
@endsection
