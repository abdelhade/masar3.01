@extends('admin.dashboard')

@section('content')
    {{-- Breadcrumb --}}
    @include('components.breadcrumb', [
        'title' => __('إضافة عقد إيجار جديد'),
        'items' => [
            ['label' => __('الرئيسية'), 'url' => route('admin.dashboard')],
            ['label' => __('العقود'), 'url' => route('rentals.leases.index')],
            ['label' => __('إضافة عقد')],
        ],
    ])

    <div class="container-fluid px-4">
        <div class="card shadow-sm">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="fas fa-file-contract me-2"></i>
                    إضافة عقد إيجار جديد
                </h5>
            </div>
            <div class="card-body">
                <form action="{{ route('rentals.leases.store') }}" method="POST">
                    @csrf

                    <div class="row">
                        {{-- الوحدة --}}
                        <div class="col-md-6 mb-3">
                            <label class="form-label">الوحدة <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-building"></i></span>
                                <select name="unit_id" class="form-select @error('unit_id') is-invalid @enderror" required>
                                    <option value="">-- اختر الوحدة --</option>
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

                        {{-- العميل --}}
                        <div class="col-md-6 mb-3">
                            <label class="form-label">العميل <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-user"></i></span>

                                <select name="client_id" class="form-select @error('client_id') is-invalid @enderror"
                                    required>
                                    <option value="">-- اختر العميل --</option>
                                    @foreach ($clients as $client)
                                        <option value="{{ $client->id }}" @selected(old('client_id') == $client->id)>
                                            {{ $client->aname }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            @error('client_id')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- تاريخ البداية --}}
                        <div class="col-md-6 mb-3">
                            <label class="form-label">تاريخ البداية <span class="text-danger">*</span></label>
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

                        {{-- تاريخ النهاية --}}
                        <div class="col-md-6 mb-3">
                            <label class="form-label">تاريخ النهاية <span class="text-danger">*</span></label>
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

                        {{-- قيمة الإيجار --}}
                        <div class="col-md-6 mb-3">
                            <label class="form-label">قيمة الإيجار <span class="text-danger">*</span></label>
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

                        {{-- طريقة الدفع --}}
                        <div class="col-md-6 mb-3">
                            <label class="form-label">الصندوق <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-user"></i></span>
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

                        {{-- الحالة --}}
                        <div class="col-md-6 mb-3">
                            <label class="form-label">الحالة <span class="text-danger">*</span></label>
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

                        {{-- الملاحظات --}}
                        <div class="col-12 mb-3">
                            <label class="form-label">ملاحظات</label>
                            <textarea name="notes" class="form-control @error('notes') is-invalid @enderror" rows="4">{{ old('notes') }}</textarea>
                            @error('notes')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="card-footer text-end bg-transparent border-top pt-3 pe-0">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-2"></i>حفظ العقد
                        </button>
                        <a href="{{ route('rentals.leases.index') }}" class="btn btn-danger">
                            <i class="fas fa-times me-2"></i>إلغاء
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
