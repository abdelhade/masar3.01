@extends('admin.dashboard')
@section('content')

    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @include('components.breadcrumb', [
        'title' => __('الخصومات'),
        'items' => [
            ['label' => __('الرئيسيه'), 'url' => route('admin.dashboard')],
            ['label' => __('الخصومات'), 'url' => route('discounts.index')],
            ['label' => __('إنشاء خصم')],
        ],
    ])

    <div class="container-fluid px-0">
        <section class="content" style="width:100%">
            <form action="{{ route('discounts.store') }}" method="post">
                @csrf

                @php
                    $titles = [
                        30 => 'خصم مسموح به',
                        31 => 'خصم مكتسب',
                    ];
                @endphp

                <div class="card bg-white w-100" style="max-width: 100%;">
                    <div class="card-header">
                        <h3 class="card-title fw-bold fs-2">
                            {{ $titles[$type] }}
                        </h3>
                    </div>

                    <div class="card-body">
                        <div class="row">
                            <input type="hidden" name="type" value="{{ $type }}">

                            @if ($type == 30)
                                <input type="hidden" name="acc2" value="{{ $acc2Fixed->id }}">
                                <div class="col-lg-4">
                                    <label>الحساب المدين (acc1 - العملاء)</label>
                                    <select name="acc1" id="acc1" class="form-control" required onchange="updateBalance()">
                                        @foreach ($clientsAccounts as $acc)
                                            <option value="{{ $acc->id }}" data-balance="{{ $acc->balance }}"
                                                {{ $loop->first ? 'selected' : '' }}>
                                                {{ $acc->aname }} (الرصيد: {{ number_format($acc->balance) }})
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="col-lg-2">
                                    <div class="mt-2">
                                        <label>الرصيد الحالي: </label>
                                        <span id="current-balance" class="fw-bold text-primary">0</span>
                                    </div>
                                    <div class="mt-2">
                                        <label>الرصيد بعد الخصم: </label>
                                        <span id="balance-after-discount" class="fw-bold text-success">0</span>
                                    </div>
                                </div>

                            @elseif ($type == 31)
                                <input type="hidden" name="acc1" value="{{ $acc1Fixed->id }}">
                                <div class="col-lg-4">
                                    <label>الحساب الدائن (acc2 - الموردين)</label>
                                    <select name="acc2" id="acc2" class="form-control" required onchange="updateBalance()">
                                        @foreach ($suppliers as $acc)
                                            <option value="{{ $acc->id }}" data-balance="{{ $acc->balance }}"
                                                {{ $loop->first ? 'selected' : '' }}>
                                                {{ $acc->aname }} (الرصيد: {{ number_format($acc->balance, 2) }})
                                            </option>
                                        @endforeach
                                    </select>
                                    <div class="mt-2">
                                        <label>الرصيد الحالي: </label>
                                        <span id="current-balance" class="fw-bold text-primary">0</span>
                                    </div>
                                    <div class="mt-2">
                                        <label>الرصيد بعد الخصم: </label>
                                        <span id="balance-after-discount" class="fw-bold text-success">0</span>
                                    </div>
                                </div>
                            @endif

                            <div class="col-lg-2">
                                <div class="form-group">
                                    <label for="pro_date">{{ __('التاريخ') }}</label>
                                    <input type="date" name="pro_date"
                                        value="{{ old('pro_date', \Carbon\Carbon::now()->format('Y-m-d')) }}"
                                        class="form-control @error('pro_date') is-invalid @enderror">
                                    @error('pro_date')
                                        <span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-lg-2">
                                <div class="form-group">
                                    <label for="pro_id">{{ __('رقم السند') }}</label>
                                    <input type="text" name="pro_id" inputmode="numeric" pattern="\d*"
                                        class="form-control @error('pro_id') is-invalid @enderror"
                                        value="{{ old('pro_id', $nextProId) }}" readonly
                                        oninput="this.value = this.value.replace(/[^0-9]/g, '')">
                                    @error('pro_id')
                                        <span class="invalid-feedback"><strong>{{ $message }}</strong></span>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-lg-2">
                                <div class="form-group">
                                    <label for="pro_value">{{ __('قيمة الخصم ') }}</label>
                                    <input type="number" name="pro_value" id="pro_value" step="0.01" min="0.01"
                                        class="form-control @error('pro_value') is-invalid @enderror"
                                        oninput="updateBalance()">
                                    @error('pro_value')
                                        <span class="invalid-feedback"><strong>{{ $message }}</strong></span>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-lg-12">
                                <div class="form-group">
                                    <label for="info">{{ __('ملاحظات') }}</label>
                                    <textarea name="info" class="form-control @error('info') is-invalid @enderror"></textarea>
                                    @error('info')
                                        <span class="invalid-feedback"><strong>{{ $message }}</strong></span>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-sm-10 mt-3">
                                <button type="submit" class="btn btn-primary">تأكيد</button>
                                <a href="{{ url()->previous() }}" class="btn btn-danger">إلغاء</a>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </section>
    </div>

    <script>
        function updateBalance() {
            const select = document.querySelector('#acc1') || document.querySelector('#acc2');
            const proValue = parseFloat(document.querySelector('#pro_value').value) || 0;
            const selectedOption = select.options[select.selectedIndex];
            const currentBalance = parseFloat(selectedOption.getAttribute('data-balance')) || 0;

            document.querySelector('#current-balance').textContent = currentBalance.toFixed(2);
            const balanceAfterDiscount = currentBalance - proValue;
            document.querySelector('#balance-after-discount').textContent = balanceAfterDiscount.toFixed(2);

            if (balanceAfterDiscount < 0) {
                document.querySelector('#balance-after-discount').classList.add('text-danger');
            } else {
                document.querySelector('#balance-after-discount').classList.remove('text-danger');
            }
        }

        document.addEventListener('DOMContentLoaded', updateBalance);
    </script>
@endsection
