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
                <div class="card-header bg-gradient-danger text-white py-4">
                    <div class="d-flex align-items-center justify-content-between">
                        <div class="d-flex align-items-center">
                            <div class="icon-shape bg-white text-danger rounded-circle p-3 me-3">
                                <i class="fas fa-ban fa-2x"></i>
                            </div>
                            <div>
                                <h2 class="mb-1 fw-bold header-title">{{ $pageTitle }}</h2>
                                <p class="mb-0 text-white-75 header-subtitle">
                                    إنشاء قيد محاسبي عكسي لإلغاء الورقة
                                </p>
                            </div>
                        </div>
                        <a href="{{ route($check->type === 'incoming' ? 'checks.incoming' : 'checks.outgoing') }}" class="btn btn-light">
                            <i class="fas fa-arrow-right me-2"></i> العودة
                        </a>
                    </div>
                </div>

                <!-- Form -->
                <form method="POST" action="{{ route('checks.cancel-reversal', $check) }}">
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

                        <!-- تحذير -->
                        <div class="alert alert-warning">
                            <h5 class="alert-heading">
                                <i class="fas fa-exclamation-triangle me-2"></i> تحذير
                            </h5>
                            <p class="mb-0">
                                سيتم إلغاء هذه الورقة وإنشاء قيد محاسبي عكسي للقيد الأصلي. هذا الإجراء لا يمكن التراجع عنه.
                            </p>
                        </div>

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
                                                    <span class="badge bg-{{ $check->status_color }}">{{ $check->status }}</span>
                                                </p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="alert alert-info">
                                    <h6 class="fw-bold mb-2">
                                        <i class="fas fa-info-circle me-2"></i> القيد المحاسبي العكسي:
                                    </h6>
                                    @if($check->type === 'incoming')
                                        <p class="mb-1">
                                            <strong>من:</strong> حافظة أوراق القبض (دائن)
                                        </p>
                                        <p class="mb-0">
                                            <strong>إلى:</strong> الحساب المقابل (مدين)
                                        </p>
                                    @else
                                        <p class="mb-1">
                                            <strong>من:</strong> الحساب المقابل (دائن)
                                        </p>
                                        <p class="mb-0">
                                            <strong>إلى:</strong> حافظة أوراق الدفع (مدين)
                                        </p>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card-footer bg-light">
                        <div class="d-flex justify-content-between">
                            <a href="{{ route($check->type === 'incoming' ? 'checks.incoming' : 'checks.outgoing') }}" class="btn btn-secondary">
                                <i class="fas fa-times me-2"></i> إلغاء
                            </a>
                            <button type="submit" class="btn btn-danger" onclick="return confirm('هل أنت متأكد من إلغاء هذه الورقة بقيد عكسي؟ لا يمكن التراجع عن هذا الإجراء.')">
                                <i class="fas fa-ban me-2"></i> إلغاء بقيد عكسي
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

