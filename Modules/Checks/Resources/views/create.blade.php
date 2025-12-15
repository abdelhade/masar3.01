@extends('admin.dashboard')

@section('sidebar')
    @include('components.sidebar.checks')
@endsection

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card shadow-sm">
                <!-- Header -->
                <div class="card-header bg-gradient-{{ $pageType === 'incoming' ? 'success' : 'danger' }} text-white py-4">
                    <div class="d-flex align-items-center">
                        <div class="icon-shape bg-white text-{{ $pageType === 'incoming' ? 'success' : 'danger' }} rounded-circle p-3 me-3">
                            <i class="fas fa-{{ $pageType === 'incoming' ? 'plus-circle' : 'minus-circle' }} fa-2x"></i>
                        </div>
                        <div>
                            <h2 class="mb-1 fw-bold header-title">{{ $pageTitle }}</h2>
                            <p class="mb-0 text-white-75 header-subtitle">
                                {{ $pageType === 'incoming' ? 'استلام شيك من عميل' : 'إصدار شيك لمورد' }}
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Form -->
                <form method="POST" action="{{ route('checks.store') }}">
                    @csrf
                    <input type="hidden" name="type" value="{{ $pageType }}">
                    <input type="hidden" name="branch_id" value="{{ auth()->user()->branch_id ?? 1 }}">

                    <div class="card-body">
                        @if ($errors->any())
                            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                <div class="d-flex align-items-center">
                                    <i class="fas fa-exclamation-circle me-2"></i>
                                    <div class="flex-grow-1">
                                        <strong>يرجى تصحيح الأخطاء التالية:</strong>
                                        <ul class="mb-0 mt-2">
                                            @foreach ($errors->all() as $error)
                                                <li>{{ $error }}</li>
                                            @endforeach
                                        </ul>
                                    </div>
                                </div>
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>
                        @endif

                        <!-- Section 1: المعلومات الأساسية -->
                        <div class="form-section mb-4">
                            <div class="section-header mb-3">
                                <h5 class="section-title">
                                    <i class="fas fa-info-circle text-{{ $pageType === 'incoming' ? 'success' : 'danger' }} me-2"></i>
                                    المعلومات الأساسية
                                </h5>
                                <hr class="section-divider">
                            </div>
                            <div class="row g-3">
                                <div class="col-md-4 col-lg-3">
                                    <label class="form-label fw-bold">
                                        <i class="fas fa-calendar-alt text-muted me-1"></i>
                                        التاريخ <span class="text-danger">*</span>
                                    </label>
                                    <input type="date" name="pro_date" id="pro_date" 
                                           class="form-control @error('pro_date') is-invalid @enderror"
                                           value="{{ old('pro_date', date('Y-m-d')) }}" required autofocus>
                                    @error('pro_date')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-8 col-lg-6">
                                    <label class="form-label fw-bold">
                                        <i class="fas fa-user-circle text-muted me-1"></i>
                                        اسم الحساب <span class="text-danger">*</span>
                                    </label>
                                    <select name="acc1_id" id="acc_id" 
                                            class="form-select js-tom-select @error('acc1_id') is-invalid @enderror" required>
                                        <option value="">ابحث عن الحساب...</option>
                                        @if(isset($groupedAccounts) && count($groupedAccounts) > 0)
                                            @foreach($groupedAccounts as $groupName => $accounts)
                                                <optgroup label="{{ $groupName }}">
                                                    @foreach($accounts as $account)
                                                        <option value="{{ $account->id }}" 
                                                                data-balance="{{ $account->balance ?? 0 }}"
                                                                {{ old('acc1_id') == $account->id ? 'selected' : '' }}>
                                                            [{{ $account->code }}] {{ $account->aname }} - {{ number_format($account->balance ?? 0, 2) }} ر.س
                                                        </option>
                                                    @endforeach
                                                </optgroup>
                                            @endforeach
                                        @endif
                                    </select>
                                    @error('acc1_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-6 col-lg-3">
                                    <label class="form-label fw-bold">
                                        <i class="fas fa-coins text-muted me-1"></i>
                                        المبلغ <span class="text-danger">*</span>
                                    </label>
                                    <div class="input-group">
                                        <input type="number" step="0.01" name="amount" id="amount" 
                                               class="form-control @error('amount') is-invalid @enderror"
                                               value="{{ old('amount') }}" required>
                                        <span class="input-group-text">ر.س</span>
                                    </div>
                                    @error('amount')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <!-- Account Balance Display -->
                            <div class="row g-3 mt-2">
                                <div class="col-md-4 col-lg-3">
                                    <label class="form-label fw-bold text-muted">
                                        <i class="fas fa-wallet text-info me-1"></i>
                                        الرصيد قبل
                                    </label>
                                    <div class="input-group">
                                        <input type="number" step="0.01" name="acc2_before" id="acc2_before" 
                                               class="form-control balance-display" 
                                               value="{{ old('acc2_before') }}" placeholder="0.00" readonly>
                                        <span class="input-group-text bg-light">ر.س</span>
                                    </div>
                                </div>

                                <div class="col-md-4 col-lg-3">
                                    <label class="form-label fw-bold text-muted">
                                        <i class="fas fa-arrow-{{ $pageType === 'incoming' ? 'up' : 'down' }} text-{{ $pageType === 'incoming' ? 'success' : 'danger' }} me-1"></i>
                                        الرصيد بعد
                                    </label>
                                    <div class="input-group">
                                        <input type="number" step="0.01" name="acc2_after" id="acc2_after" 
                                               class="form-control balance-display balance-after" 
                                               value="{{ old('acc2_after') }}" placeholder="0.00" readonly>
                                        <span class="input-group-text bg-light">ر.س</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Section 2: معلومات الشيك -->
                        <div class="form-section mb-4">
                            <div class="section-header mb-3">
                                <h5 class="section-title">
                                    <i class="fas fa-file-invoice text-{{ $pageType === 'incoming' ? 'success' : 'danger' }} me-2"></i>
                                    معلومات الشيك
                                </h5>
                                <hr class="section-divider">
                            </div>
                            <div class="row g-3">
                                <div class="col-md-4 col-lg-3">
                                    <label class="form-label fw-bold">
                                        <i class="fas fa-hashtag text-muted me-1"></i>
                                        رقم الشيك <span class="text-danger">*</span>
                                    </label>
                                    <input type="text" name="check_number" id="check_number" 
                                           class="form-control @error('check_number') is-invalid @enderror"
                                           value="{{ old('check_number') }}" required>
                                    @error('check_number')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-4 col-lg-3">
                                    <label class="form-label fw-bold">
                                        <i class="fas fa-university text-muted me-1"></i>
                                        اسم البنك <span class="text-danger">*</span>
                                    </label>
                                    <input type="text" name="bank_name" id="bank_name" 
                                           class="form-control @error('bank_name') is-invalid @enderror"
                                           value="{{ old('bank_name') }}" required>
                                    @error('bank_name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-4 col-lg-3">
                                    <label class="form-label fw-bold">
                                        <i class="fas fa-credit-card text-muted me-1"></i>
                                        رقم الحساب البنكي
                                    </label>
                                    <input type="text" name="account_number" id="account_number" 
                                           class="form-control @error('account_number') is-invalid @enderror"
                                           value="{{ old('account_number') }}">
                                    @error('account_number')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-4 col-lg-3">
                                    <label class="form-label fw-bold">
                                        <i class="fas fa-user-tie text-muted me-1"></i>
                                        اسم صاحب الورقة الأصلية <span class="text-danger">*</span>
                                    </label>
                                    <input type="text" name="account_holder_name" id="account_holder_name" 
                                           class="form-control @error('account_holder_name') is-invalid @enderror"
                                           value="{{ old('account_holder_name') }}" required>
                                    @error('account_holder_name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-4 col-lg-3">
                                    <label class="form-label fw-bold">
                                        <i class="fas fa-calendar-check text-muted me-1"></i>
                                        تاريخ تحرير الشيك <span class="text-danger">*</span>
                                    </label>
                                    <input type="date" name="issue_date" id="issue_date" 
                                           class="form-control @error('issue_date') is-invalid @enderror"
                                           value="{{ old('issue_date', date('Y-m-d')) }}" required>
                                    @error('issue_date')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-4 col-lg-3">
                                    <label class="form-label fw-bold">
                                        <i class="fas fa-calendar-times text-muted me-1"></i>
                                        تاريخ الاستحقاق <span class="text-danger">*</span>
                                    </label>
                                    <input type="date" name="due_date" id="due_date" 
                                           class="form-control @error('due_date') is-invalid @enderror"
                                           value="{{ old('due_date') }}" required>
                                    @error('due_date')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-4 col-lg-3">
                                    <label class="form-label fw-bold">
                                        <i class="fas fa-briefcase text-muted me-1"></i>
                                        حافظة الأوراق المالية <span class="text-danger">*</span>
                                    </label>
                                    <select name="portfolio_id" id="portfolio_id" 
                                            class="form-select @error('portfolio_id') is-invalid @enderror" required>
                                        @php
                                            $portfolioCode = $pageType === 'incoming' ? '1105' : '2103';
                                            $portfolios = \Modules\Accounts\Models\AccHead::where('is_basic', 0)
                                                ->where('isdeleted', 0)
                                                ->where('code', 'like', $portfolioCode . '%')
                                                ->select('id', 'aname', 'code', 'balance')
                                                ->get();
                                        @endphp
                                        @foreach($portfolios as $portfolio)
                                            <option value="{{ $portfolio->id }}" {{ old('portfolio_id', $portfolios->first()->id ?? '') == $portfolio->id ? 'selected' : '' }}>
                                                {{ $portfolio->aname }} - {{ $portfolio->code }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('portfolio_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-4 col-lg-3">
                                    <label class="form-label fw-bold">
                                        <i class="fas fa-{{ $pageType === 'incoming' ? 'hand-holding-usd' : 'hand-holding' }} text-muted me-1"></i>
                                        اسم {{ $pageType === 'incoming' ? 'المستفيد' : 'الدافع' }}
                                    </label>
                                    <input type="text" name="{{ $pageType === 'incoming' ? 'payee_name' : 'payer_name' }}"
                                           id="beneficiary_name" 
                                           class="form-control @error($pageType === 'incoming' ? 'payee_name' : 'payer_name') is-invalid @enderror"
                                           value="{{ old($pageType === 'incoming' ? 'payee_name' : 'payer_name') }}">
                                    @error($pageType === 'incoming' ? 'payee_name' : 'payer_name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <!-- Section 3: معلومات إضافية -->
                        <div class="form-section mb-4">
                            <div class="section-header mb-3">
                                <h5 class="section-title">
                                    <i class="fas fa-info text-{{ $pageType === 'incoming' ? 'success' : 'danger' }} me-2"></i>
                                    معلومات إضافية
                                </h5>
                                <hr class="section-divider">
                            </div>
                            <div class="row g-3">
                                <div class="col-md-6 col-lg-4">
                                    <label class="form-label fw-bold">
                                        <i class="fas fa-barcode text-muted me-1"></i>
                                        رقم المرجع
                                    </label>
                                    <input type="text" name="reference_number" 
                                           class="form-control @error('reference_number') is-invalid @enderror"
                                           value="{{ old('reference_number') }}">
                                    @error('reference_number')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-6 col-lg-4">
                                    <label class="form-label fw-bold">
                                        <i class="fas fa-toggle-on text-muted me-1"></i>
                                        الحالة
                                    </label>
                                    <select name="status" class="form-select @error('status') is-invalid @enderror">
                                        <option value="pending" {{ old('status', 'pending') == 'pending' ? 'selected' : '' }}>معلق</option>
                                        <option value="cleared" {{ old('status') == 'cleared' ? 'selected' : '' }}>مصفى</option>
                                        <option value="bounced" {{ old('status') == 'bounced' ? 'selected' : '' }}>مرتد</option>
                                        <option value="cancelled" {{ old('status') == 'cancelled' ? 'selected' : '' }}>ملغى</option>
                                    </select>
                                    @error('status')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-12">
                                    <label class="form-label fw-bold">
                                        <i class="fas fa-sticky-note text-muted me-1"></i>
                                        ملاحظات
                                    </label>
                                    <textarea name="notes" class="form-control @error('notes') is-invalid @enderror" 
                                              rows="3" placeholder="أضف أي ملاحظات إضافية...">{{ old('notes') }}</textarea>
                                    @error('notes')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Footer with buttons -->
                    <div class="card-footer bg-light border-top">
                        <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
                            <a href="{{ route('checks.' . $pageType) }}" class="btn btn-secondary btn-lg">
                                <i class="fas fa-arrow-right me-2"></i> رجوع
                            </a>
                            <button type="submit" id="submitBtn" class="btn btn-{{ $pageType === 'incoming' ? 'success' : 'danger' }} btn-lg px-5">
                                <span class="btn-text">
                                    <i class="fas fa-save me-2"></i> حفظ الورقة
                                </span>
                                <span class="btn-loading d-none">
                                    <span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>
                                    جاري الحفظ...
                                </span>
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" rel="stylesheet" />
<style>
/* Header Styles */
.bg-gradient-success {
    background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);
}

.bg-gradient-danger {
    background: linear-gradient(135deg, #eb3349 0%, #f45c43 100%);
}

.icon-shape {
    width: 60px;
    height: 60px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
}

.header-title {
    font-family: 'Cairo', 'Segoe UI', Tahoma, sans-serif;
    font-size: 2rem;
    font-weight: 800;
}

.header-subtitle {
    font-family: 'Cairo', 'Segoe UI', Tahoma, sans-serif;
    font-size: 1rem;
    opacity: 0.9;
}

.text-white-75 {
    color: rgba(255, 255, 255, 0.75) !important;
}

/* Form Sections */
.form-section {
    background: #f8f9fa;
    border-radius: 8px;
    padding: 1.5rem;
    border: 1px solid #e9ecef;
    transition: all 0.3s ease;
}

.form-section:hover {
    box-shadow: 0 2px 8px rgba(0,0,0,0.05);
}

.section-header {
    margin-bottom: 1rem;
}

.section-title {
    font-size: 1.1rem;
    font-weight: 700;
    color: #495057;
    margin-bottom: 0.5rem;
    display: flex;
    align-items: center;
}

.section-divider {
    margin: 0;
    border-top: 2px solid #dee2e6;
    opacity: 0.5;
}

/* Form Controls */
.form-control, .form-select {
    padding: 0.5rem 0.75rem !important;
    font-size: 0.875rem !important;
    height: auto !important;
    border: 1px solid #ced4da;
    transition: all 0.2s ease;
}

.form-control:focus, .form-select:focus {
    border-color: #80bdff;
    box-shadow: 0 0 0 0.2rem rgba(0,123,255,0.25);
}

.form-control.is-invalid, .form-select.is-invalid {
    border-color: #dc3545;
}

.form-label {
    font-size: 0.875rem !important;
    margin-bottom: 0.5rem !important;
    color: #495057;
    font-weight: 600;
}

.form-label i {
    font-size: 0.8rem;
}

/* Balance Display */
.balance-display {
    background-color: #f8f9fa;
    font-weight: 600;
    color: #495057;
}

.balance-after {
    color: #28a745;
    font-size: 1rem;
}

.input-group-text {
    background-color: #e9ecef;
    border-color: #ced4da;
    font-weight: 500;
}

/* Select2/TomSelect Styles */
.select2-container, .ts-wrapper {
    width: 100% !important;
}

.select2-selection, .ts-control {
    min-height: 38px !important;
    padding: 2px 6px !important;
    font-size: 0.875rem !important;
    border: 1px solid #ced4da !important;
}

.select2-selection:focus, .ts-control.focus {
    border-color: #80bdff !important;
    box-shadow: 0 0 0 0.2rem rgba(0,123,255,0.25) !important;
}

/* OptGroup Styles for Tom Select */
.ts-dropdown .optgroup-header {
    font-weight: 700 !important;
    background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%) !important;
    padding: 8px 12px !important;
    color: #495057 !important;
    border-bottom: 2px solid #dee2e6 !important;
    font-size: 0.85rem !important;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.ts-dropdown .optgroup {
    border-bottom: 1px solid #e9ecef;
    margin-bottom: 4px;
}

.ts-dropdown .optgroup:last-child {
    border-bottom: none;
    margin-bottom: 0;
}

.ts-dropdown .optgroup .option {
    padding-right: 20px !important;
}

/* Alerts */
.alert {
    border-radius: 8px;
    border: none;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.alert-danger {
    background-color: #f8d7da;
    color: #721c24;
}

/* Buttons */
.btn-lg {
    padding: 0.75rem 1.5rem;
    font-size: 1rem;
    font-weight: 600;
    border-radius: 6px;
    transition: all 0.3s ease;
}

.btn-lg:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(0,0,0,0.15);
}

.btn-lg:active {
    transform: translateY(0);
}

/* Loading State */
.btn-loading {
    display: inline-flex;
    align-items: center;
}

.spinner-border-sm {
    width: 1rem;
    height: 1rem;
}

/* Card Footer */
.card-footer {
    background-color: #f8f9fa !important;
    border-top: 2px solid #e9ecef !important;
    padding: 1.25rem 1.5rem;
}

/* Responsive */
@media (max-width: 768px) {
    .form-section {
        padding: 1rem;
    }
    
    .section-title {
        font-size: 1rem;
    }
    
    .header-title {
        font-size: 1.5rem;
    }
    
    .btn-lg {
        padding: 0.5rem 1rem;
        font-size: 0.9rem;
    }
}

/* Icons */
.form-label i {
    width: 16px;
    text-align: center;
}

/* Small text */
small.text-muted {
    font-size: 0.75rem !important;
}

/* Focus states */
.form-control:focus,
.form-select:focus,
input[type="date"]:focus {
    outline: none;
    border-color: #80bdff;
    box-shadow: 0 0 0 0.2rem rgba(0,123,255,0.25);
}
</style>
@endpush

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Initialize Tom Select for account selection (local search)
    const accountSelect = document.getElementById('acc_id');
    let tomSelectInstance = null;
    
    function initTomSelect() {
        if (accountSelect && window.TomSelect) {
            tomSelectInstance = new TomSelect(accountSelect, {
                create: false,
                searchField: ['text'],
                sortField: null, // حفظ الترتيب الأصلي للمجموعات
                dropdownInput: true,
                placeholder: 'ابحث عن الحساب...',
                lockOptgroupOrder: true,
                plugins: ['remove_button'],
                render: {
                    no_results: function() {
                        return '<div class="no-results p-2 text-center">لا توجد نتائج</div>';
                    },
                    optgroup_header: function(data, escape) {
                        return '<div class="optgroup-header">' + escape(data.label) + '</div>';
                    }
                }
            });

            // Set z-index for dropdown
            tomSelectInstance.on('dropdown_open', function() {
                const dropdown = accountSelect.parentElement.querySelector('.ts-dropdown');
                if (dropdown) {
                    dropdown.style.zIndex = '99999';
                }
            });

            // Update account balances when account is selected
            tomSelectInstance.on('change', function(value) {
                updateAccountBalances(value);
            });
        } else if (!window.TomSelect) {
            // Retry if Tom Select not loaded yet
            setTimeout(initTomSelect, 100);
        }
    }
    
    initTomSelect();

    // Form submission with loading state
    const form = document.querySelector('form');
    if (form) {
        form.addEventListener('submit', function() {
            const submitBtn = document.getElementById('submitBtn');
            if (submitBtn) {
                const btnText = submitBtn.querySelector('.btn-text');
                const btnLoading = submitBtn.querySelector('.btn-loading');
                submitBtn.disabled = true;
                if (btnText) btnText.classList.add('d-none');
                if (btnLoading) btnLoading.classList.remove('d-none');
            }
        });
    }

    // Auto-fill dates
    const proDateInput = document.getElementById('pro_date');
    const issueDateInput = document.getElementById('issue_date');
    const dueDateInput = document.getElementById('due_date');

    if (proDateInput) {
        proDateInput.addEventListener('change', function() {
            if (issueDateInput && !issueDateInput.value) {
                issueDateInput.value = this.value;
            }
        });
    }

    // Validate due date with better UX
    if (dueDateInput) {
        dueDateInput.addEventListener('change', function() {
            const issueDate = new Date(issueDateInput.value);
            const dueDate = new Date(this.value);

            if (dueDate < issueDate) {
                this.classList.add('is-invalid');
                const existingError = this.parentElement.querySelector('.invalid-feedback');
                if (!existingError) {
                    const errorMsg = document.createElement('div');
                    errorMsg.className = 'invalid-feedback';
                    errorMsg.textContent = 'تاريخ الاستحقاق يجب أن يكون بعد تاريخ تحرير الشيك';
                    this.parentElement.appendChild(errorMsg);
                }
                this.value = '';
                
                // Remove error after 3 seconds
                setTimeout(() => {
                    this.classList.remove('is-invalid');
                    const feedback = this.parentElement.querySelector('.invalid-feedback');
                    if (feedback) feedback.remove();
                }, 3000);
            } else {
                this.classList.remove('is-invalid');
                const feedback = this.parentElement.querySelector('.invalid-feedback');
                if (feedback) feedback.remove();
            }
        });
    }

    // Validate issue date
    if (issueDateInput) {
        issueDateInput.addEventListener('change', function() {
            if (dueDateInput && dueDateInput.value) {
                const issueDate = new Date(this.value);
                const dueDate = new Date(dueDateInput.value);
                if (dueDate < issueDate) {
                    dueDateInput.dispatchEvent(new Event('change'));
                }
            }
        });
    }

    // Function to update account balances
    function updateAccountBalances(accountId) {
        const acc2Before = document.getElementById('acc2_before');
        const acc2After = document.getElementById('acc2_after');
        
        if (!accountId) {
            if (acc2Before) acc2Before.value = '';
            if (acc2After) acc2After.value = '';
            return;
        }

        // Get balance from option data attribute
        const selectedOption = document.querySelector('#acc_id option[value="' + accountId + '"]');
        const balance = selectedOption ? parseFloat(selectedOption.dataset.balance) || 0 : 0;
        if (acc2Before) acc2Before.value = balance.toFixed(2);
        calculateAccountAfter();
    }

    // Calculate account after based on amount and type
    function calculateAccountAfter() {
        const acc2Before = document.getElementById('acc2_before');
        const acc2After = document.getElementById('acc2_after');
        const amountInput = document.getElementById('amount');
        
        const balanceBefore = parseFloat(acc2Before?.value) || 0;
        const amount = parseFloat(amountInput?.value) || 0;
        const checkType = '{{ $pageType }}'; // 'incoming' or 'outgoing'

        let balanceAfter = balanceBefore;
        if (checkType === 'incoming') {
            // ورقة قبض: الرصيد يزيد
            balanceAfter = balanceBefore + amount;
        } else {
            // ورقة دفع: الرصيد ينقص
            balanceAfter = balanceBefore - amount;
        }

        if (acc2After) {
            acc2After.value = balanceAfter.toFixed(2);
            // Add visual feedback
            acc2After.style.animation = 'none';
            setTimeout(() => {
                acc2After.style.animation = 'pulse 0.5s ease';
            }, 10);
        }
    }

    // Update balances when amount changes
    const amountInput = document.getElementById('amount');
    if (amountInput) {
        amountInput.addEventListener('input', function() {
            if (this.value && parseFloat(this.value) > 0) {
                calculateAccountAfter();
            } else {
                const acc2Before = document.getElementById('acc2_before');
                const acc2After = document.getElementById('acc2_after');
                if (acc2After) acc2After.value = acc2Before?.value || '0.00';
            }
        });

        // Format amount on blur
        amountInput.addEventListener('blur', function() {
            const value = parseFloat(this.value);
            if (value && !isNaN(value)) {
                this.value = value.toFixed(2);
            }
        });
    }

    // Update balances when account changes (for regular select fallback)
    if (accountSelect) {
        accountSelect.addEventListener('change', function() {
            updateAccountBalances(this.value);
        });
    }

    // Real-time validation feedback
    document.querySelectorAll('input[required], select[required]').forEach(function(el) {
        el.addEventListener('blur', function() {
            if (this.value) {
                this.classList.remove('is-invalid');
                this.classList.add('is-valid');
            } else {
                this.classList.remove('is-valid');
                this.classList.add('is-invalid');
            }
        });
    });

    // Add pulse animation for balance updates
    if (!document.getElementById('balance-animation-style')) {
        const style = document.createElement('style');
        style.id = 'balance-animation-style';
        style.textContent = `
            @keyframes pulse {
                0% { transform: scale(1); }
                50% { transform: scale(1.02); }
                100% { transform: scale(1); }
            }
        `;
        document.head.appendChild(style);
    }
});
</script>
@endpush
