@extends('admin.dashboard')

@section('sidebar')
    @include('components.sidebar.expenses')
@endsection

@section('content')
    <div class="container-fluid">
        <!-- Title -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h4 class="mb-1">
                    <i class="fas fa-plus-circle text-primary me-2"></i>
                    {{ __('New Expense Record') }}
                </h4>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item"><a
                                href="{{ route('expenses.dashboard') }}">{{ __('Expenses Management') }}</a></li>
                        <li class="breadcrumb-item active">{{ __('New Expense Record') }}</li>
                    </ol>
                </nav>
            </div>
            <a href="{{ route('expenses.dashboard') }}" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-right me-1"></i>
                {{ __('Back') }}
            </a>
        </div>

        @if (session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="fas fa-check-circle me-2"></i>
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        @if ($errors->any())
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <ul class="mb-0">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        <div class="row">
            <div class="col-lg-8">
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-transparent border-0">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-file-invoice-dollar text-primary me-2"></i>
                            {{ __('Expense Details') }}
                        </h5>
                    </div>
                    <div class="card-body">
                        <form action="{{ route('expenses.store') }}" method="POST">
                            @csrf

                            <div class="row">
                                <!-- Expense Account -->
                                <div class="col-md-6 mb-3">
                                    <label for="expense_account_id" class="form-label fw-medium">
                                        <i class="fas fa-tag text-muted me-1"></i>
                                        {{ __('Expense Item') }} <span class="text-danger">*</span>
                                    </label>
                                    <select name="expense_account_id" id="expense_account_id"
                                        class="form-select expense-select2 @error('expense_account_id') is-invalid @enderror"
                                        required>
                                        <option value="">{{ __('Search or select expense item...') }}</option>
                                        @foreach ($expenseAccounts as $account)
                                            <option value="{{ $account->id }}"
                                                {{ old('expense_account_id') == $account->id ? 'selected' : '' }}>
                                                {{ $account->code }} - {{ $account->aname }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('expense_account_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <!-- Payment Account -->
                                <div class="col-md-6 mb-3">
                                    <label for="payment_account_id" class="form-label fw-medium">
                                        <i class="fas fa-wallet text-muted me-1"></i>
                                        {{ __('Payment From (Cash/Banks)') }} <span class="text-danger">*</span>
                                    </label>
                                    <select name="payment_account_id" id="payment_account_id"
                                        class="form-select expense-select2 @error('payment_account_id') is-invalid @enderror"
                                        required>
                                        <option value="">{{ __('Search or select payment account...') }}</option>
                                        <optgroup label="{{ __('Cash Registers') }}">
                                            @foreach ($paymentAccounts->filter(fn($a) => str_starts_with($a->code, '11')) as $account)
                                                <option value="{{ $account->id }}"
                                                    {{ old('payment_account_id') == $account->id ? 'selected' : '' }}>
                                                    {{ $account->code }} - {{ $account->aname }}
                                                </option>
                                            @endforeach
                                        </optgroup>
                                        <optgroup label="{{ __('Banks') }}">
                                            @foreach ($paymentAccounts->filter(fn($a) => str_starts_with($a->code, '12')) as $account)
                                                <option value="{{ $account->id }}"
                                                    {{ old('payment_account_id') == $account->id ? 'selected' : '' }}>
                                                    {{ $account->code }} - {{ $account->aname }}
                                                </option>
                                            @endforeach
                                        </optgroup>
                                    </select>
                                    @error('payment_account_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <!-- Amount -->
                                <div class="col-md-6 mb-3">
                                    <label for="amount" class="form-label fw-medium">
                                        <i class="fas fa-money-bill-wave text-muted me-1"></i>
                                        {{ __('Amount') }} <span class="text-danger">*</span>
                                    </label>
                                    <div class="input-group">
                                        <input type="number" name="amount" id="amount"
                                            class="form-control @error('amount') is-invalid @enderror"
                                            value="{{ old('amount') }}" step="0.01" min="0.01" required
                                            placeholder="0.00">
                                        <span class="input-group-text">ر.س</span>
                                    </div>
                                    @error('amount')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                </div>

                                <!-- Date -->
                                <div class="col-md-6 mb-3">
                                    <label for="expense_date" class="form-label fw-medium">
                                        <i class="fas fa-calendar-alt text-muted me-1"></i>
                                        {{ __('Date') }} <span class="text-danger">*</span>
                                    </label>
                                    <input type="date" name="expense_date" id="expense_date"
                                        class="form-control @error('expense_date') is-invalid @enderror"
                                        value="{{ old('expense_date', date('Y-m-d')) }}" required>
                                    @error('expense_date')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <!-- Cost Center -->
                                <div class="col-md-6 mb-3">
                                    <label for="cost_center_id" class="form-label fw-medium">
                                        <i class="fas fa-sitemap text-muted me-1"></i>
                                        {{ __('Cost Center') }}
                                    </label>
                                    <select name="cost_center_id" id="cost_center_id"
                                        class="form-select expense-select2 @error('cost_center_id') is-invalid @enderror">
                                        <option value="">{{ __('No Cost Center') }}</option>
                                        @foreach ($costCenters as $center)
                                            <option value="{{ $center->id }}"
                                                {{ old('cost_center_id') == $center->id ? 'selected' : '' }}>
                                                {{ $center->cname }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('cost_center_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <!-- Description -->
                                <div class="col-12 mb-3">
                                    <label for="description" class="form-label fw-medium">
                                        <i class="fas fa-align-left text-muted me-1"></i>
                                        {{ __('Description / Notes') }}
                                    </label>
                                    <textarea name="description" id="description" rows="3"
                                        class="form-control @error('description') is-invalid @enderror"
                                        placeholder="{{ __('Enter detailed expense description...') }}">{{ old('description') }}</textarea>
                                    @error('description')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <hr class="my-4">

                            <div class="d-flex justify-content-between">
                                <button type="reset" class="btn btn-outline-secondary">
                                    <i class="fas fa-undo me-1"></i>
                                    {{ __('Reset') }}
                                </button>
                                <button type="submit" class="btn btn-primary px-4">
                                    <i class="fas fa-save me-1"></i>
                                    {{ __('Save Expense') }}
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Right Side - Help Information -->
            <div class="col-lg-4">
                <!-- Instructions -->
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-info bg-opacity-10 border-0">
                        <h6 class="card-title mb-0 text-info">
                            <i class="fas fa-info-circle me-2"></i>
                            {{ __('Instructions') }}
                        </h6>
                    </div>
                    <div class="card-body">
                        <ul class="list-unstyled mb-0 small">
                            <li class="mb-2">
                                <i class="fas fa-check-circle text-success me-2"></i>
                                {{ __('Select appropriate expense item') }}
                            </li>
                            <li class="mb-2">
                                <i class="fas fa-check-circle text-success me-2"></i>
                                {{ __('Select payment account (Cash or Bank)') }}
                            </li>
                            <li class="mb-2">
                                <i class="fas fa-check-circle text-success me-2"></i>
                                {{ __('Enter amount accurately') }}
                            </li>
                            <li class="mb-2">
                                <i class="fas fa-check-circle text-success me-2"></i>
                                {{ __('You can specify cost center for tracking') }}
                            </li>
                            <li>
                                <i class="fas fa-check-circle text-success me-2"></i>
                                {{ __('Add clear description for the expense') }}
                            </li>
                        </ul>
                    </div>
                </div>

                <!-- Quick Stats -->
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-warning bg-opacity-10 border-0">
                        <h6 class="card-title mb-0 text-warning">
                            <i class="fas fa-chart-bar me-2"></i>
                            {{ __('Quick Stats') }}
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-muted small">{{ __('Expense Items Count:') }}</span>
                            <span class="fw-bold">{{ $expenseAccounts->count() }}</span>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-muted small">{{ __('Cost Centers:') }}</span>
                            <span class="fw-bold">{{ $costCenters->count() }}</span>
                        </div>
                        <div class="d-flex justify-content-between">
                            <span class="text-muted small">{{ __('Payment Accounts:') }}</span>
                            <span class="fw-bold">{{ $paymentAccounts->count() }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            (function() {
                function initSelect2() {
                    // Check if jQuery and Select2 are available
                    if (typeof jQuery === 'undefined' || typeof jQuery.fn.select2 === 'undefined') {
                        setTimeout(initSelect2, 100);
                        return;
                    }

                    // Initialize Select2 for expense accounts
                    jQuery('#expense_account_id').select2({
                        placeholder: '{{ __('Search or select expense item...') }}',
                        allowClear: true,
                        width: '100%',
                        language: {
                            noResults: function() {
                                return "{{ __('No results found') }}";
                            },
                            searching: function() {
                                return "{{ __('Searching...') }}";
                            }
                        }
                    });

                    // Initialize Select2 for payment accounts
                    jQuery('#payment_account_id').select2({
                        placeholder: '{{ __('Search or select payment account...') }}',
                        allowClear: true,
                        width: '100%',
                        language: {
                            noResults: function() {
                                return "{{ __('No results found') }}";
                            },
                            searching: function() {
                                return "{{ __('Searching...') }}";
                            }
                        }
                    });

                    // Initialize Select2 for cost centers
                    jQuery('#cost_center_id').select2({
                        placeholder: '{{ __('Search or select cost center...') }}',
                        allowClear: true,
                        width: '100%',
                        language: {
                            noResults: function() {
                                return "{{ __('No results found') }}";
                            },
                            searching: function() {
                                return "{{ __('Searching...') }}";
                            }
                        }
                    });
                }

                // Initialize when page is ready
                if (document.readyState === 'loading') {
                    document.addEventListener('DOMContentLoaded', initSelect2);
                } else {
                    initSelect2();
                }
            })();
        </script>
    @endpush
@endsection
