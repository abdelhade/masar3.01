@extends('admin.dashboard')

@section('sidebar')
    @include('components.sidebar.expenses')
@endsection

@section('content')
<div class="container-fluid">
    <!-- العنوان -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-1">
                <i class="fas fa-plus-circle text-primary me-2"></i>
                تسجيل مصروف جديد
            </h4>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('expenses.dashboard') }}">إدارة المصروفات</a></li>
                    <li class="breadcrumb-item active">تسجيل مصروف جديد</li>
                </ol>
            </nav>
        </div>
        <a href="{{ route('expenses.dashboard') }}" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-right me-1"></i>
            العودة
        </a>
    </div>

    @if(session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <i class="fas fa-check-circle me-2"></i>
        {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    @endif

    @if($errors->any())
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <ul class="mb-0">
            @foreach($errors->all() as $error)
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
                        بيانات المصروف
                    </h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('expenses.store') }}" method="POST">
                        @csrf

                        <div class="row">
                            <!-- حساب المصروف -->
                            <div class="col-md-6 mb-3">
                                <label for="expense_account_id" class="form-label fw-medium">
                                    <i class="fas fa-tag text-muted me-1"></i>
                                    بند المصروف <span class="text-danger">*</span>
                                </label>
                                <select name="expense_account_id" id="expense_account_id" class="form-select expense-select2 @error('expense_account_id') is-invalid @enderror" required>
                                    <option value="">ابحث أو اختر بند المصروف...</option>
                                    @foreach($expenseAccounts as $account)
                                    <option value="{{ $account->id }}" {{ old('expense_account_id') == $account->id ? 'selected' : '' }}>
                                        {{ $account->code }} - {{ $account->aname }}
                                    </option>
                                    @endforeach
                                </select>
                                @error('expense_account_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- حساب الدفع -->
                            <div class="col-md-6 mb-3">
                                <label for="payment_account_id" class="form-label fw-medium">
                                    <i class="fas fa-wallet text-muted me-1"></i>
                                    الدفع من (النقدية والبنوك) <span class="text-danger">*</span>
                                </label>
                                <select name="payment_account_id" id="payment_account_id" class="form-select expense-select2 @error('payment_account_id') is-invalid @enderror" required>
                                    <option value="">ابحث أو اختر حساب الدفع...</option>
                                    <optgroup label="الصناديق">
                                        @foreach($paymentAccounts->filter(fn($a) => str_starts_with($a->code, '11')) as $account)
                                        <option value="{{ $account->id }}" {{ old('payment_account_id') == $account->id ? 'selected' : '' }}>
                                            {{ $account->code }} - {{ $account->aname }}
                                        </option>
                                        @endforeach
                                    </optgroup>
                                    <optgroup label="البنوك">
                                        @foreach($paymentAccounts->filter(fn($a) => str_starts_with($a->code, '12')) as $account)
                                        <option value="{{ $account->id }}" {{ old('payment_account_id') == $account->id ? 'selected' : '' }}>
                                            {{ $account->code }} - {{ $account->aname }}
                                        </option>
                                        @endforeach
                                    </optgroup>
                                </select>
                                @error('payment_account_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- المبلغ -->
                            <div class="col-md-6 mb-3">
                                <label for="amount" class="form-label fw-medium">
                                    <i class="fas fa-money-bill-wave text-muted me-1"></i>
                                    المبلغ <span class="text-danger">*</span>
                                </label>
                                <div class="input-group">
                                    <input type="number" name="amount" id="amount" 
                                           class="form-control @error('amount') is-invalid @enderror" 
                                           value="{{ old('amount') }}" 
                                           step="0.01" min="0.01" required
                                           placeholder="0.00">
                                    <span class="input-group-text">ر.س</span>
                                </div>
                                @error('amount')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- التاريخ -->
                            <div class="col-md-6 mb-3">
                                <label for="expense_date" class="form-label fw-medium">
                                    <i class="fas fa-calendar-alt text-muted me-1"></i>
                                    التاريخ <span class="text-danger">*</span>
                                </label>
                                <input type="date" name="expense_date" id="expense_date" 
                                       class="form-control @error('expense_date') is-invalid @enderror" 
                                       value="{{ old('expense_date', date('Y-m-d')) }}" required>
                                @error('expense_date')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- مركز التكلفة -->
                            <div class="col-md-6 mb-3">
                                <label for="cost_center_id" class="form-label fw-medium">
                                    <i class="fas fa-sitemap text-muted me-1"></i>
                                    مركز التكلفة
                                </label>
                                <select name="cost_center_id" id="cost_center_id" class="form-select expense-select2 @error('cost_center_id') is-invalid @enderror">
                                    <option value="">بدون مركز تكلفة</option>
                                    @foreach($costCenters as $center)
                                    <option value="{{ $center->id }}" {{ old('cost_center_id') == $center->id ? 'selected' : '' }}>
                                        {{ $center->cname }}
                                    </option>
                                    @endforeach
                                </select>
                                @error('cost_center_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- الوصف -->
                            <div class="col-12 mb-3">
                                <label for="description" class="form-label fw-medium">
                                    <i class="fas fa-align-left text-muted me-1"></i>
                                    الوصف / البيان
                                </label>
                                <textarea name="description" id="description" rows="3" 
                                          class="form-control @error('description') is-invalid @enderror" 
                                          placeholder="أدخل وصفاً تفصيلياً للمصروف...">{{ old('description') }}</textarea>
                                @error('description')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <hr class="my-4">

                        <div class="d-flex justify-content-between">
                            <button type="reset" class="btn btn-outline-secondary">
                                <i class="fas fa-undo me-1"></i>
                                إعادة تعيين
                            </button>
                            <button type="submit" class="btn btn-primary px-4">
                                <i class="fas fa-save me-1"></i>
                                حفظ المصروف
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- الجانب الأيمن - معلومات مساعدة -->
        <div class="col-lg-4">
            <!-- تعليمات -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-info bg-opacity-10 border-0">
                    <h6 class="card-title mb-0 text-info">
                        <i class="fas fa-info-circle me-2"></i>
                        تعليمات
                    </h6>
                </div>
                <div class="card-body">
                    <ul class="list-unstyled mb-0 small">
                        <li class="mb-2">
                            <i class="fas fa-check-circle text-success me-2"></i>
                            اختر بند المصروف المناسب
                        </li>
                        <li class="mb-2">
                            <i class="fas fa-check-circle text-success me-2"></i>
                            حدد حساب الدفع (صندوق أو بنك)
                        </li>
                        <li class="mb-2">
                            <i class="fas fa-check-circle text-success me-2"></i>
                            أدخل المبلغ بدقة
                        </li>
                        <li class="mb-2">
                            <i class="fas fa-check-circle text-success me-2"></i>
                            يمكنك تحديد مركز التكلفة للتتبع
                        </li>
                        <li>
                            <i class="fas fa-check-circle text-success me-2"></i>
                            أضف وصفاً واضحاً للمصروف
                        </li>
                    </ul>
                </div>
            </div>

            <!-- إحصائيات سريعة -->
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-warning bg-opacity-10 border-0">
                    <h6 class="card-title mb-0 text-warning">
                        <i class="fas fa-chart-bar me-2"></i>
                        إحصائيات سريعة
                    </h6>
                </div>
                <div class="card-body">
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted small">عدد بنود المصروفات:</span>
                        <span class="fw-bold">{{ $expenseAccounts->count() }}</span>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted small">مراكز التكلفة:</span>
                        <span class="fw-bold">{{ $costCenters->count() }}</span>
                    </div>
                    <div class="d-flex justify-content-between">
                        <span class="text-muted small">حسابات الدفع:</span>
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
        // التحقق من توفر jQuery و Select2
        if (typeof jQuery === 'undefined' || typeof jQuery.fn.select2 === 'undefined') {
            setTimeout(initSelect2, 100);
            return;
        }

        // تهيئة Select2 لقائمة حسابات المصروفات
        jQuery('#expense_account_id').select2({
            placeholder: 'ابحث أو اختر بند المصروف...',
            allowClear: true,
            width: '100%',
            language: {
                noResults: function() {
                    return "لا توجد نتائج";
                },
                searching: function() {
                    return "جاري البحث...";
                }
            }
        });

        // تهيئة Select2 لقائمة حسابات الدفع (النقدية والبنوك)
        jQuery('#payment_account_id').select2({
            placeholder: 'ابحث أو اختر حساب الدفع...',
            allowClear: true,
            width: '100%',
            language: {
                noResults: function() {
                    return "لا توجد نتائج";
                },
                searching: function() {
                    return "جاري البحث...";
                }
            }
        });

        // تهيئة Select2 لقائمة مراكز التكلفة
        jQuery('#cost_center_id').select2({
            placeholder: 'ابحث أو اختر مركز التكلفة...',
            allowClear: true,
            width: '100%',
            language: {
                noResults: function() {
                    return "لا توجد نتائج";
                },
                searching: function() {
                    return "جاري البحث...";
                }
            }
        });
    }

    // البدء في التهيئة عندما تكون الصفحة جاهزة
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initSelect2);
    } else {
        initSelect2();
    }
})();
</script>
@endpush
@endsection

