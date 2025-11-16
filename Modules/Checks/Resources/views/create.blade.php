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
                                {{ $pageType === 'incoming' ? 'Ø§Ø³ØªÙ„Ø§Ù… Ø´ÙŠÙƒ Ù…Ù† Ø¹Ù…ÙŠÙ„' : 'Ø¥ØµØ¯Ø§Ø± Ø´ÙŠÙƒ Ù„Ù…ÙˆØ±Ø¯' }}
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
                            <div class="alert alert-danger">
                                <ul class="mb-0">
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        <div class="row g-3">
                            <div class="col-md-4">
                                <label class="form-label fw-bold">Ø§Ù„ØªØ§Ø±ÙŠØ® <span class="text-danger">*</span></label>
                                <input type="date" name="pro_date" id="pro_date" class="form-control"
                                       value="{{ old('pro_date', date('Y-m-d')) }}" required autofocus>
                            </div>

                            <div class="col-md-8">
                                <label class="form-label fw-bold">Ø§Ø³Ù… Ø§Ù„Ø­Ø³Ø§Ø¨ <span class="text-danger">*</span></label>
                                <select name="acc1_id" id="acc_id" class="form-control select2" required>
                                    <option value="">Ø§Ø¨Ø­Ø« Ø¹Ù† Ø§Ù„Ø­Ø³Ø§Ø¨...</option>
                                    @php
                                        $allAccounts = \Modules\Accounts\Models\AccHead::where('isdeleted', 0)
                                            ->where('is_basic', 0)
                                            ->select('id', 'aname', 'code', 'balance')
                                            ->orderBy('code')
                                            ->get();
                                    @endphp
                                    @foreach($allAccounts as $account)
                                        <option value="{{ $account->id }}" {{ old('acc_id') == $account->id ? 'selected' : '' }}>
                                            [{{ $account->code }}] {{ $account->aname }} - {{ number_format($account->balance, 2) }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-4">
                                <label class="form-label fw-bold">Ø§Ù„Ù…Ø¨Ù„Øº <span class="text-danger">*</span></label>
                                <input type="number" step="0.01" name="amount" id="amount" class="form-control"
                                       value="{{ old('amount') }}" required>
                            </div>

                            <div class="col-md-4">
                                <label class="form-label fw-bold">ØªØ§Ø±ÙŠØ® ØªØ­Ø±ÙŠØ± Ø§Ù„Ø´ÙŠÙƒ <span class="text-danger">*</span></label>
                                <input type="date" name="issue_date" id="issue_date" class="form-control"
                                       value="{{ old('issue_date', date('Y-m-d')) }}" required>
                            </div>

                            <div class="col-md-4">
                                <label class="form-label fw-bold">ØªØ§Ø±ÙŠØ® Ø§Ù„Ø§Ø³ØªØ­Ù‚Ø§Ù‚ <span class="text-danger">*</span></label>
                                <input type="date" name="due_date" id="due_date" class="form-control"
                                       value="{{ old('due_date') }}" required>
                            </div>

                            <div class="col-md-4">
                                <label class="form-label fw-bold">Ø§Ø³Ù… Ø§Ù„Ù…Ø³ØªÙÙŠØ¯</label>
                                <input type="text" name="{{ $pageType === 'incoming' ? 'payee_name' : 'payer_name' }}"
                                       id="beneficiary_name" class="form-control"
                                       value="{{ old($pageType === 'incoming' ? 'payee_name' : 'payer_name') }}">
                            </div>

                            <div class="col-md-4">
                                <label class="form-label fw-bold">Ø§Ø³Ù… Ø§Ù„Ø¨Ù†Ùƒ <span class="text-danger">*</span></label>
                                <input type="text" name="bank_name" id="bank_name" class="form-control"
                                       value="{{ old('bank_name') }}" required>
                            </div>

                            <div class="col-md-4">
                                <label class="form-label fw-bold">Ø§Ø³Ù… ØµØ§Ø­Ø¨ Ø§Ù„ÙˆØ±Ù‚Ø© Ø§Ù„Ø£ØµÙ„ÙŠ <span class="text-danger">*</span></label>
                                <input type="text" name="account_holder_name" id="account_holder_name" class="form-control"
                                       value="{{ old('account_holder_name') }}" required>
                            </div>

                            <div class="col-md-4">
                                <label class="form-label fw-bold">Ø­Ø§ÙØ¸Ø© Ø§Ù„Ø£ÙˆØ±Ø§Ù‚ Ø§Ù„Ù…Ø§Ù„ÙŠØ© <span class="text-danger">*</span></label>
                                <select name="portfolio_id" id="portfolio_id" class="form-select" required>
                                    @php
                                        $portfolioCode = $pageType === 'incoming' ? '1105' : '2103';
                                        $portfolios = \Modules\Accounts\Models\AccHead::where('isdeleted', 0)
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
                            </div>

                            <div class="col-md-4">
                                <label class="form-label fw-bold">Ø±Ù‚Ù… Ø§Ù„Ø´ÙŠÙƒ <span class="text-danger">*</span></label>
                                <input type="text" name="check_number" id="check_number" class="form-control"
                                       value="{{ old('check_number') }}" required>
                            </div>

                            <div class="col-md-4">
                                <label class="form-label fw-bold">Ø±Ù‚Ù… Ø§Ù„Ø­Ø³Ø§Ø¨ Ø§Ù„Ø¨Ù†ÙƒÙŠ</label>
                                <input type="text" name="account_number" id="account_number" class="form-control"
                                       value="{{ old('account_number') }}">
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-bold">Ø±Ù‚Ù… Ø§Ù„Ù…Ø±Ø¬Ø¹</label>
                                <input type="text" name="reference_number" class="form-control"
                                       value="{{ old('reference_number') }}">
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-bold">Ø§Ù„Ø­Ø§Ù„Ø©</label>
                                <select name="status" class="form-select">
                                    <option value="pending" selected>Ù…Ø¹Ù„Ù‚</option>
                                    <option value="cleared">Ù…ØµÙÙ‰</option>
                                    <option value="bounced">Ù…Ø±ØªØ¯</option>
                                    <option value="cancelled">Ù…Ù„ØºÙ‰</option>
                                </select>
                            </div>

                            <div class="col-12">
                                <label class="form-label fw-bold">Ù…Ù„Ø§Ø­Ø¸Ø§Øª</label>
                                <textarea name="notes" class="form-control" rows="2">{{ old('notes') }}</textarea>
                            </div>
                        </div>
                    </div>

                    <!-- Footer with buttons -->
                    <div class="card-footer bg-light">
                        <div class="d-flex justify-content-between">
                            <a href="{{ route('checks.' . $pageType) }}" class="btn btn-secondary btn-lg">
                                <i class="fas fa-arrow-right"></i> Ø±Ø¬ÙˆØ¹
                            </a>
                            <button type="submit" class="btn btn-{{ $pageType === 'incoming' ? 'success' : 'danger' }} btn-lg px-5">
                                <i class="fas fa-save"></i> Ø­ÙØ¸ Ø§Ù„ÙˆØ±Ù‚Ø©
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

.select2-container {
    width: 100% !important;
}

.select2-selection {
    min-height: 32px !important;
    padding: 2px 6px !important;
    font-size: 0.875rem !important;
}

.form-control, .form-select {
    padding: 0.375rem 0.75rem !important;
    font-size: 0.875rem !important;
    height: auto !important;
}

.form-label {
    font-size: 0.875rem !important;
    margin-bottom: 0.25rem !important;
}

small.text-muted {
    font-size: 0.75rem !important;
}
</style>
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
$(document).ready(function() {
    // Initialize Select2 for searchable account selection
    $('#acc_id').select2({
        theme: 'bootstrap-5',
        placeholder: 'Ø§Ø¨Ø­Ø« Ø¹Ù† Ø§Ù„Ø­Ø³Ø§Ø¨...',
        allowClear: true,
        language: {
            noResults: function() {
                return "Ù„Ø§ ØªÙˆØ¬Ø¯ Ù†ØªØ§Ø¦Ø¬";
            },
            searching: function() {
                return "Ø¬Ø§Ø±ÙŠ Ø§Ù„Ø¨Ø­Ø«...";
            }
        }
    });

    // Auto-fill dates
    $('#pro_date').on('change', function() {
        if (!$('#issue_date').val()) {
            $('#issue_date').val($(this).val());
        }
    });

    // Validate due date
    $('#due_date').on('change', function() {
        const issueDate = new Date($('#issue_date').val());
        const dueDate = new Date($(this).val());

        if (dueDate < issueDate) {
            alert('ØªØ§Ø±ÙŠØ® Ø§Ù„Ø§Ø³ØªØ­Ù‚Ø§Ù‚ ÙŠØ¬Ø¨ Ø£Ù† ÙŠÙƒÙˆÙ† Ø¨Ø¹Ø¯ ØªØ§Ø±ÙŠØ® ØªØ­Ø±ÙŠØ± Ø§Ù„Ø´ÙŠÙƒ');
            $(this).val('');
        }
    });
});
</script>
@endpush

