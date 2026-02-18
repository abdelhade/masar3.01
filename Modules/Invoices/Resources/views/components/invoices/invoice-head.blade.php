@php
    $titles = [
        10 => 'فاتورة مبيعات',
        11 => 'فاتورة مشتريات',
        12 => 'مرتجع مبيعات',
        13 => 'مرتجع مشتريات',
        14 => 'أمر بيع',
        15 => 'أمر شراء',
        16 => 'عرض سعر للعميل',
        17 => 'عرض سعر من المورد',
        18 => 'فاتورة تالف',
        19 => 'أمر صرف',
        20 => 'أمر إضافة',
        21 => 'تحويل مخزني',
        22 => 'أمر حجز',
        24 => 'فاتورة خدمة',
        25 => 'طلب شراء',
        26 => 'اتفاقية تسعير',
    ];
@endphp

<div class="card border-0 shadow-sm mb-3">
    <div class="card-header" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white;">
        <div class="row align-items-center">
            <div class="col-md-6">
                <h5 class="mb-0 fw-bold">
                    <i class="fas fa-file-invoice me-2"></i>
                    {{ $titles[$type] ?? 'فاتورة' }}
                </h5>
            </div>

            <div class="col-md-6 text-end">
                @if ($type != 21 && $showBalance)


                    <small class="me-3">
                        <strong>{{ __('Current Balance:') }}</strong>
                        <span id="current-balance-header" class="badge bg-light text-dark">0.00</span>
                    </small>

                    <small>
                        <strong>{{ __('After Invoice:') }}</strong>
                        <span id="balance-after-header" class="badge bg-light text-dark">0.00</span>
                    </small>

                    <small class="me-3">
                        @if (setting('enable_installment_from_invoice') && $type == 10)
                            <button type="button" class="btn btn-md btn-info"
                                style="font-size: 0.8rem; padding: 0.25rem 0.5rem;">
                                <i class="las la-calendar-check"></i> {{ __('Installment') }}
                            </button>
                        @endif
                    </small>
                    <small class="me-3">
                        <button type="button" class="btn btn-success btn-sm" onclick="window.InvoiceApp.submitForm()">
                            <i class="fas fa-save me-2"></i>
                            {{ __('حفظ الفاتورة') }}
                        </button>
                    </small>

                    <small class="me-3">
                        <button type="button" class="btn btn-primary btn-sm" onclick="window.InvoiceApp.printInvoice()" id="print-invoice-btn">
                            <i class="fas fa-print me-2"></i>
                            {{ __('طباعة') }}
                        </button>
                    </small>

                    <small class="me-3">
                        <a href="{{ url()->previous() }}" class="btn btn-danger btn-sm ">
                            <i class="fas fa-arrow-right me-2"></i>
                            {{ __('رجوع') }}
                        </a>
                    </small>
                @endif
            </div>
        </div>
    </div>

    <div class="card-body p-3" style="background: #f8f9fa;">
        <div class="row g-2">
            <input type="hidden" name="type" value="{{ $type }}">

            {{-- العميل/المورد - With Select2 Search --}}
            <div class="col-md-1">
                <label class="form-label mb-1 fw-semibold" style="font-size: 0.85rem;">
                    {{ $acc1Role }}
                    <span class="text-danger">*</span>
                </label>
                <select id="acc1-id" class="form-select form-select-sm" style="width: 100%;">
                    <option value="">{{ __('ابحث عن') }} {{ $acc1Role }}...</option>
                    @foreach ($acc1Options as $option)
                        <option value="{{ $option->id }}">{{ $option->aname }}</option>
                    @endforeach
                </select>
            </div>

            {{-- المخزن --}}
            <div class="col-md-1">
                <label class="form-label mb-1 fw-semibold" style="font-size: 0.85rem;">{{ $acc2Role }}</label>
                <select id="acc2-id" class="form-select form-select-sm" {{ !$canEditStore ? 'disabled' : '' }}>
                    <option value="">{{ __('اختر') }} {{ $acc2Role }}</option>
                    @foreach ($acc2List as $acc)
                        <option value="{{ $acc->id }}">{{ $acc->aname }}</option>
                    @endforeach
                </select>
            </div>

            {{-- الموظف --}}
            <div class="col-md-1">
                <label class="form-label mb-1 fw-semibold" style="font-size: 0.85rem;">{{ __('الموظف') }}</label>
                <select id="emp-id" class="form-select form-select-sm">
                    <option value="">{{ __('اختر الموظف') }}</option>
                    @foreach ($employees as $employee)
                        <option value="{{ $employee->id }}">{{ $employee->aname }}</option>
                    @endforeach
                </select>
            </div>

            @if ($type != 21)
                {{-- المندوب --}}
                <div class="col-md-1">
                    <label class="form-label mb-1 fw-semibold" style="font-size: 0.85rem;">{{ __('المندوب') }}</label>
                    <select id="delivery-id" class="form-select form-select-sm">
                        <option value="">{{ __('اختر المندوب') }}</option>
                        @foreach ($deliverys as $delivery)
                            <option value="{{ $delivery->id }}">{{ $delivery->aname }}</option>
                        @endforeach
                    </select>
                </div>
            @endif
            <div class="col-md-1">
                <label class="form-label mb-1 fw-semibold"
                    style="font-size: 0.85rem;">{{ __('Invoice Pattern') }}</label>

                <select id="invoice-template" class="form-select form-select-sm">
                    <option value="">{{ __('اختر النمط...') }}</option>
                    @php
                        $templates = DB::table('invoice_templates')->get();
                    @endphp
                    @foreach ($templates as $template)
                        <option value="{{ $template->id }}" data-columns="{{ $template->visible_columns }}"
                            {{ $template->is_active ? 'selected' : '' }}>
                            {{ $template->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            {{-- الفئة السعرية (للمبيعات فقط) --}}
            @if (in_array($type, [10, 12, 14, 16, 19, 22]))
                <div class="col-md-1">
                    <label class="form-label mb-1 fw-semibold" style="font-size: 0.85rem;">
                        {{ __('Price List') }}
                        <span class="text-danger">*</span>
                    </label>
                    <select id="price-list-id" class="form-select form-select-sm">
                        <option value="">{{ __('اختر الفئة السعرية') }}</option>
                        @foreach ($priceLists ?? [] as $priceList)
                            <option value="{{ $priceList->id }}" {{ $loop->first ? 'selected' : '' }}>
                                {{ $priceList->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
            @endif
            {{-- التاريخ --}}
            <div class="col-md-1">
                <label class="form-label mb-1 fw-semibold" style="font-size: 0.85rem;">{{ __('التاريخ') }}</label>
                <input type="date" id="pro-date" class="form-control form-control-sm" value="{{ date('Y-m-d') }}"
                    {{ !setting('allow_edit_transaction_date', true) ? 'readonly' : '' }}>
            </div>

            {{-- رقم الفاتورة --}}
            <div class="col-md-1">
                <label class="form-label mb-1 fw-semibold" style="font-size: 0.85rem;">{{ __('رقم الفاتورة') }}</label>
                <input type="text" id="pro-id" class="form-control form-control-sm" readonly placeholder="تلقائي">
            </div>

            @if ($type != 21)
                {{-- S.N --}}
                <div class="col-md-1">
                    <label class="form-label mb-1 fw-semibold" style="font-size: 0.85rem;">{{ __('S.N') }}</label>
                    <input type="text" id="serial-number" class="form-control form-control-sm">
                </div>
            @endif

            <x-branches::branch-select :branches="$branches" />

            {{-- ملاحظات --}}
            <div class="col-md-2">
                <label class="form-label mb-1 fw-semibold" style="font-size: 0.85rem;">{{ __('ملاحظات') }}</label>
                <input id="notes" class="form-control form-control-sm" rows="2"
                    placeholder="أدخل ملاحظات إضافية..."></input>
            </div>
        </div>
    </div>
</div>
