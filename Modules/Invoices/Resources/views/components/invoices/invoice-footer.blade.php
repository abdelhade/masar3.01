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
                        <input type="number" id="discount-percentage" class="form-control text-center" placeholder="%"
                            step="0.01" value="0">
                    </div>
                </div>

                <div class="col-md-3">
                    <div class="footer-label">{{ __('قيمة الخصم') }}</div>
                    <div class="input-group input-group-sm">
                        <input type="number" id="discount-value" class="form-control text-center" step="0.01"
                            value="0">
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
                        <input type="number" id="additional-value" class="form-control text-center" step="0.01"
                            value="0">
                    </div>
                </div>

                <div class="col-md-3">
                    <div class="footer-label">{{ __('الضريبة') }} ({{ $vatPercentage }}%)</div>
                    <div class="footer-value" id="vat-value-display">0</div>
                </div>

                @if ($withholdingTaxPercentage > 0)
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
                        <input type="number" id="received-from-client" class="form-control text-center" step="0.01"
                            value="0">
                    </div>
                </div>

                <div class="col-md-3">
                    <div class="footer-label">{{ __('المتبقي') }}</div>
                    <div class="footer-value" id="remaining-display" style="background: #fff3cd; color: #856404;">0
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
