@extends('admin.dashboard')
@section('content')

    @include('components.breadcrumb', [
        'title' => __('الخصومات'),
        'items' => [
            ['label' => __('الرئيسيه'), 'url' => route('admin.dashboard')],
            ['label' => __('الخصومات'), 'url' => route('discounts.index')],
            ['label' => __('تعديل خصم')],
        ],
    ])

    <div class="content-wrapper">
        <section class="content">
            <form action="{{ route('discounts.update', $discount->id) }}" method="post">
                @csrf
                @method('PUT')

                <div class="card bg-white col-md-12 container">
                    <div class="card-header">
                        <h3 class="card-title fw-bold fs-2">{{ $titles[$type] }}</h3>
                    </div>

                    <div class="card-body  ">
                        <div class="row">
                            <input type="hidden" name="type" value="{{ $type }}">

                            @if ($type == 30)
                                <input type="hidden" name="acc2" value="{{ $acc2Fixed->id }}">
                                <div class="col-lg-4">
                                    <label>الحساب المدين (acc1 - العملاء)</label>
                                    <select name="acc1" class="form-control" required>
                                        @foreach ($clientsAccounts as $acc)
                                            <option value="{{ $acc->id }}" @selected($discount->acc1 == $acc->id)>
                                                {{ $acc->aname }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            @elseif ($type == 31)
                                <input type="hidden" name="acc1" value="{{ $acc1Fixed->id }}">
                                <div class="col-lg-4">
                                    <label>الحساب الدائن (acc2 - الموردين)</label>
                                    <select name="acc2" class="form-control" required>
                                        @foreach ($suppliers as $acc)
                                            <option value="{{ $acc->id }}" @selected($discount->acc2 == $acc->id)>
                                                {{ $acc->aname }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            @endif

                            <div class="col-lg-4">
                                <label>التاريخ</label>
                                <input type="date" name="pro_date" class="form-control"
                                    value="{{ $discount->pro_date }}">
                            </div>

                            <div class="col-lg-4">
                                <label>رقم السند</label>
                                <input type="number" name="pro_id" class="form-control" value="{{ $discount->pro_id }}"
                                    readonly>
                            </div>

                            <div class="col-lg-4">
                                <div class="form-group">
                                    <label for="pro_value">{{ __('قيمة الخصم ') }}</label>
                                    <input type="number" name="pro_value" id="pro_value" step="0.01" min="0.01"
                                        class="form-control @error('pro_value') is-invalid @enderror"
                                        value="{{ $discount->pro_value }}">
                                    @error('pro_value')
                                        <span class="invalid-feedback"><strong>{{ $message }}</strong></span>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-lg-8">
                                <label>ملاحظات</label>
                                <textarea name="info" class="form-control">{{ $discount->info }}</textarea>
                            </div>
                        </div>
                    </div>

                    <div class="row mt-3 mb-3">
                        <div class="col-sm-10">
                            <button type="submit" class="btn btn-primary">تحديث</button>
                            <a href="{{ route('discounts.index') }}" class="btn btn-danger">إلغاء</a>
                        </div>
                    </div>
                </div>
            </form>
        </section>
    </div>

@endsection
