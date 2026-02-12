<div class="footer-section">
    <div class="row g-3">
        {{-- Right Side - Totals --}}
        <div class="col-md-8">
            <div class="row g-2">
                {{-- Row 1 --}}
                <div class="col-md-3">
                    <div class="footer-label">{{ __('الإجمالي الفرعي') }}</div>
                    <div class="footer-value" id="subtotal-display">0</div>
                </div>

                <div class="col-md-3">
                    <div class="footer-label">{{ __('الخصم') }} %</div>
                    <div class="input-group input-group-sm">
                        <input type="number" id="discount-percentage" class="form-control text-center" 
                               placeholder="%" step="0.01" value="0">
                    </div>
                </div>

                <div class="col-md-3">
                    <div class="footer-label">{{ __('قيمة الخصم') }}</div>
                    <div class="input-group input-group-sm">
                        <input type="number" id="discount-value" class="form-control text-center" 
                               step="0.01" value="0">
                    </div>
                </div>

                <div class="col-md-3">
                    <div class="footer-label">{{ __('الإضافي') }} %</div>
                    <div class="input-group input-group-sm">
                        <input type="number" id="additional-percentage" class="form-control text-center" 
                               placeholder="%" step="0.01" value="0">
                    </div>
                </div>

                {{-- Row 2 --}}
                <div class="col-md-3">
                    <div class="footer-label">{{ __('قيمة الإضافي') }}</div>
                    <div class="input-group input-group-sm">
                        <input type="number" id="additional-value" class="form-control text-center" 
                               step="0.01" value="0">
                    </div>
                </div>

                <div class="col-md-3">
                    <div class="footer-label">{{ __('الضريبة') }} ({{ $vatPercentage }}%)</div>
                    <div class="footer-value" id="vat-value-display">0</div>
                </div>

                @if($withholdingTaxPercentage > 0)
                <div class="col-md-3">
                    <div class="footer-label">{{ __('خصم المنبع') }} ({{ $withholdingTaxPercentage }}%)</div>
                    <div class="footer-value" id="withholding-tax-value-display">0</div>
                </div>
                @endif

                <div class="col-md-3">
                    <div class="footer-label">{{ __('الإجمالي') }}</div>
                    <div class="footer-value total" id="total-display">0</div>
                </div>

                {{-- Row 3 --}}
                <div class="col-md-3">
                    <div class="footer-label">{{ __('المدفوع') }}</div>
                    <div class="input-group input-group-sm">
                        <input type="number" id="received-from-client" class="form-control text-center" 
                               step="0.01" value="0">
                    </div>
                </div>

                <div class="col-md-3">
                    <div class="footer-label">{{ __('المتبقي') }}</div>
                    <div class="footer-value" id="remaining-display" style="background: #fff3cd; color: #856404;">0</div>
                </div>
            </div>
        </div>

        {{-- Left Side - Actions --}}
        <div class="col-md-4">
            <div class="d-flex flex-column gap-2 h-100 justify-content-center">
                <button type="button" class="btn btn-save-invoice w-100" onclick="InvoiceApp.saveInvoice(); return false;">
                    <i class="fas fa-save me-2"></i>
                    {{ __('حفظ الفاتورة') }}
                </button>
                
                <button type="button" class="btn btn-outline-primary btn-sm w-100" onclick="window.print()">
                    <i class="fas fa-print me-2"></i>
                    {{ __('طباعة') }}
                </button>
                
                <a href="{{ url()->previous() }}" class="btn btn-outline-secondary btn-sm w-100">
                    <i class="fas fa-arrow-right me-2"></i>
                    {{ __('رجوع') }}
                </a>
            </div>
        </div>
    </div>

    {{-- Balance Section (if enabled) --}}
    @if($showBalance)
    <div class="row mt-3">
        <div class="col-12">
            <div class="alert alert-info py-2 mb-0 d-flex justify-content-between align-items-center">
                <span>
                    <strong>{{ __('الرصيد الحالي:') }}</strong>
                    <span id="current-balance-display" class="badge bg-primary">0.00</span>
                </span>
                <span>
                    <strong>{{ __('الرصيد بعد الفاتورة:') }}</strong>
                    <span id="balance-after-display" class="badge bg-success">0.00</span>
                </span>
            </div>
        </div>
    </div>
    @endif
</div>
