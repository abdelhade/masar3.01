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
                <div class="card-header bg-gradient-primary text-white py-4">
                    <div class="d-flex align-items-center justify-content-between">
                        <div class="d-flex align-items-center">
                            <div class="icon-shape bg-white text-primary rounded-circle p-3 me-3">
                                <i class="fas fa-exchange-alt fa-2x"></i>
                            </div>
                            <div>
                                <h2 class="mb-1 fw-bold header-title">{{ $pageTitle }}</h2>
                                <p class="mb-0 text-white-75 header-subtitle">
                                    إنشاء قيد محاسبي لتظهير الورقة
                                </p>
                            </div>
                        </div>
                        <a href="{{ route($check->type === 'incoming' ? 'checks.incoming' : 'checks.outgoing') }}" class="btn btn-light">
                            <i class="fas fa-arrow-right me-2"></i> العودة
                        </a>
                    </div>
                </div>

                <!-- Form -->
                <form method="POST" action="{{ route('checks.clear', $check) }}">
                    @csrf
                    <input type="hidden" name="branch_id" value="{{ auth()->user()->branch_id ?? 1 }}">

                    <div class="card-body">
                        @if (session('error'))
                            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                <i class="fas fa-exclamation-circle me-2"></i> {{ session('error') }}
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        @endif

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

                        <!-- معلومات الورقة -->
                        <div class="row mb-4">
                            <div class="col-12">
                                <h5 class="mb-3 text-primary">
                                    <i class="fas fa-info-circle me-2"></i> معلومات الورقة
                                </h5>
                            </div>
                            <div class="col-md-6">
                                <div class="card bg-light">
                                    <div class="card-body">
                                        <div class="row g-3">
                                            <div class="col-6">
                                                <label class="form-label fw-bold text-muted">رقم الورقة:</label>
                                                <p class="mb-0 fw-bold">{{ $check->check_number }}</p>
                                            </div>
                                            <div class="col-6">
                                                <label class="form-label fw-bold text-muted">البنك:</label>
                                                <p class="mb-0">{{ $check->bank_name }}</p>
                                            </div>
                                            <div class="col-6">
                                                <label class="form-label fw-bold text-muted">المبلغ:</label>
                                                <p class="mb-0 fw-bold text-primary fs-5">{{ number_format($check->amount, 2) }} ر.س</p>
                                            </div>
                                            <div class="col-6">
                                                <label class="form-label fw-bold text-muted">تاريخ الاستحقاق:</label>
                                                <p class="mb-0">{{ $check->due_date->format('Y-m-d') }}</p>
                                            </div>
                                            <div class="col-6">
                                                <label class="form-label fw-bold text-muted">صاحب الحساب:</label>
                                                <p class="mb-0">{{ $check->account_holder_name }}</p>
                                            </div>
                                            <div class="col-6">
                                                <label class="form-label fw-bold text-muted">الحالة:</label>
                                                <p class="mb-0">
                                                    <span class="badge bg-warning">معلق</span>
                                                </p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="alert alert-info">
                                    <h6 class="fw-bold mb-2">
                                        <i class="fas fa-info-circle me-2"></i> القيد المحاسبي:
                                    </h6>
                                    <p class="mb-1">
                                        <strong>من:</strong> حافظة أوراق القبض (مدين)
                                    </p>
                                    <p class="mb-0">
                                        <strong>إلى:</strong> حساب البنك (دائن)
                                    </p>
                                </div>
                            </div>
                        </div>

                        <!-- بيانات التظهير -->
                        <div class="row">
                            <div class="col-12">
                                <h5 class="mb-3 text-primary">
                                    <i class="fas fa-edit me-2"></i> بيانات التظهير
                                </h5>
                            </div>

                            <!-- الحساب -->
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">الحساب <span class="text-danger">*</span></label>
                                <select name="bank_account_id" id="bank_account_id" class="form-select js-tom-select" required>
                                    <option value="">اختر الحساب</option>
                                    @foreach($accounts as $account)
                                        <option value="{{ $account->id }}" {{ old('bank_account_id') == $account->id ? 'selected' : '' }}>
                                            {{ $account->aname }} - {{ $account->code }} (رصيد: {{ number_format($account->balance ?? 0, 2) }})
                                        </option>
                                    @endforeach
                                </select>
                                @error('bank_account_id')
                                    <div class="text-danger small mt-1">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- تاريخ التظهير -->
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">تاريخ التظهير <span class="text-danger">*</span></label>
                                <input type="date" name="collection_date" id="collection_date" 
                                       class="form-control" 
                                       value="{{ old('collection_date', date('Y-m-d')) }}" 
                                       required>
                                @error('collection_date')
                                    <div class="text-danger small mt-1">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <div class="card-footer bg-light">
                        <div class="d-flex justify-content-between">
                            <a href="{{ route($check->type === 'incoming' ? 'checks.incoming' : 'checks.outgoing') }}" class="btn btn-secondary">
                                <i class="fas fa-times me-2"></i> إلغاء
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-exchange-alt me-2"></i> تظهير الورقة وإنشاء القيد
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    // Initialize Tom Select for searchable select
    function initTomSelect() {
        const selectElement = document.getElementById('bank_account_id');
        if (selectElement && window.TomSelect && !selectElement.tomselect) {
            const tomSelect = new TomSelect(selectElement, {
                create: false,
                searchField: ['text'],
                sortField: {field: 'text', direction: 'asc'},
                dropdownInput: true,
                placeholder: 'ابحث واختر الحساب...',
                maxOptions: 1000,
                allowEmptyOption: true,
            });
            
            // Set z-index for dropdown
            tomSelect.on('dropdown_open', function() {
                const dropdown = selectElement.parentElement.querySelector('.ts-dropdown');
                if (dropdown) {
                    dropdown.style.zIndex = '99999';
                }
            });
        }
    }
    
    // Initialize when document is ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initTomSelect);
    } else {
        initTomSelect();
    }
});
</script>
@endpush

