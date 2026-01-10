<div class="row">
    <div class="col-md-6">
        <div class="form-group">
            <label for="name">{{ __('Template Name') }} <span class="text-danger">*</span></label>
            <input type="text" name="name" id="name" class="form-control @error('name') is-invalid @enderror"
                value="{{ old('name', $template->name ?? '') }}" required>
            @error('name')
                <span class="invalid-feedback">{{ $message }}</span>
            @enderror
        </div>
    </div>


    <div class="col-md-6">
        <div class="form-group">
            <label for="code">{{ __('Template Code') }} <span class="text-danger">*</span></label>
            <input type="text" name="code" id="code" class="form-control @error('code') is-invalid @enderror"
                value="{{ old('code', $template->code ?? '') }}" required>
            @error('code')
                <span class="invalid-feedback">{{ $message }}</span>
            @enderror
        </div>
    </div>
</div>


<div class="form-group">
    <label for="description">{{ __('Description') }}</label>
    <textarea name="description" id="description" rows="2" class="form-control">{{ old('description', $template->description ?? '') }}</textarea>
</div>


<div class="row">
    <div class="col-md-6">
        <div class="form-group">
            <label for="sort_order">{{ __('Display Order') }}</label>
            <input type="number" name="sort_order" id="sort_order" class="form-control"
                value="{{ old('sort_order', $template->sort_order ?? 0) }}">
        </div>
    </div>


    <div class="col-md-6">
        <div class="form-group">
            <div class="custom-control custom-switch mt-4">
                <input type="checkbox" name="is_active" value="1" class="custom-control-input" id="is_active"
                    {{ old('is_active', $template->is_active ?? true) ? 'checked' : '' }}>
                <label class="custom-control-label" for="is_active">{{ __('Template Active') }}</label>
            </div>
        </div>
    </div>
</div>


<hr>


<div class="form-group">
    <label>{{ __('Invoice Types') }} <span class="text-danger">*</span></label>
    <div class="row">
        @foreach ($invoiceTypes as $typeId => $typeName)
            <div class="col-md-4">
                <div class="custom-control custom-checkbox">
                    <input type="checkbox" name="invoice_types[]" value="{{ $typeId }}"
                        class="custom-control-input invoice-type-checkbox" id="type_{{ $typeId }}"
                        {{ in_array($typeId,old('invoice_types',collect(optional($template)->invoiceTypes ?? [])->pluck('invoice_type')->toArray()))? 'checked': '' }}>
                    <label class="custom-control-label" for="type_{{ $typeId }}">
                        {{ $typeName }} ({{ $typeId }})
                    </label>


                    <div class="ml-4 default-option" id="default_{{ $typeId }}" style="display: none;">
                        <div class="custom-control custom-switch">
                            <input type="checkbox" name="is_default[]" value="{{ $typeId }}"
                                class="custom-control-input" id="default_switch_{{ $typeId }}"
                                {{ collect(optional($template)->invoiceTypes ?? [])->where('invoice_type', $typeId)->first()?->is_default? 'checked': '' }}>
                            <label class="custom-control-label" for="default_switch_{{ $typeId }}">
                                <small class="text-muted">{{ __('Default for this type') }}</small>
                            </label>
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>
</div>


<hr>


<div class="form-group">
    <label>{{ __('Visible Columns and their Order') }} <span class="text-danger">*</span></label>
    <p class="text-muted">{{ __('Drag columns to reorder, and set the width of each column') }}</p>


    <div id="columns-container" class="border rounded p-3 bg-light">
        <div id="sortable-columns" class="list-group">
            @php
                $orderedColumns = old(
                    'column_order',
                    optional($template)->column_order ?? array_keys($availableColumns),
                );
                $selectedColumns = old('visible_columns', optional($template)->visible_columns ?? []);
                $columnWidths = old('column_widths', optional($template)->column_widths ?? []);
            @endphp


            @foreach ($orderedColumns as $columnKey)
                @if (isset($availableColumns[$columnKey]))
                    <div class="list-group-item column-item" data-column="{{ $columnKey }}">
                        <div class="row align-items-center">
                            <div class="col-1 text-center">
                                <i class="fas fa-grip-vertical text-muted" style="cursor: move;"></i>
                            </div>


                            <div class="col-3">
                                <div class="custom-control custom-checkbox">
                                    <input type="checkbox" name="visible_columns[]" value="{{ $columnKey }}"
                                        class="custom-control-input column-checkbox" id="column_{{ $columnKey }}"
                                        {{ in_array($columnKey, $selectedColumns) ? 'checked' : '' }}>
                                    <label class="custom-control-label" for="column_{{ $columnKey }}">
                                        {{ $availableColumns[$columnKey] }}
                                    </label>
                                </div>
                            </div>


                            <div class="col-6">
                                <div class="form-group mb-0">
                                    <label class="small">{{ __('Column Width (%)') }}</label>
                                    <input type="range" name="column_widths[{{ $columnKey }}]"
                                        class="custom-range column-width-slider" min="5" max="30"
                                        step="1" value="{{ $columnWidths[$columnKey] ?? 10 }}"
                                        data-column="{{ $columnKey }}">
                                </div>
                            </div>


                            <div class="col-2 text-center">
                                <span class="badge badge-primary width-display" id="width_{{ $columnKey }}">
                                    {{ $columnWidths[$columnKey] ?? 10 }}%
                                </span>
                            </div>
                        </div>


                        <input type="hidden" name="column_order[]" value="{{ $columnKey }}"
                            class="column-order-input">
                    </div>
                @endif
            @endforeach


            @foreach ($availableColumns as $columnKey => $columnName)
                @if (!in_array($columnKey, $orderedColumns))
                    <div class="list-group-item column-item" data-column="{{ $columnKey }}">
                        <div class="row align-items-center">
                            <div class="col-1 text-center">
                                <i class="fas fa-grip-vertical text-muted" style="cursor: move;"></i>
                            </div>


                            <div class="col-3">
                                <div class="custom-control custom-checkbox">
                                    <input type="checkbox" name="visible_columns[]" value="{{ $columnKey }}"
                                        class="custom-control-input column-checkbox" id="column_{{ $columnKey }}">
                                    <label class="custom-control-label" for="column_{{ $columnKey }}">
                                        {{ $columnName }}
                                    </label>
                                </div>
                            </div>


                            <div class="col-6">
                                <div class="form-group mb-0">
                                    <label class="small">{{ __('Column Width (%)') }}</label>
                                    <input type="range" name="column_widths[{{ $columnKey }}]"
                                        class="custom-range column-width-slider" min="5" max="30"
                                        step="1" value="10" data-column="{{ $columnKey }}">
                                </div>
                            </div>


                            <div class="col-2 text-center">
                                <span class="badge badge-primary width-display"
                                    id="width_{{ $columnKey }}">10%</span>
                            </div>
                        </div>


                        <input type="hidden" name="column_order[]" value="{{ $columnKey }}"
                            class="column-order-input">
                    </div>
                @endif
            @endforeach
        </div>
    </div>


    @error('visible_columns')
        <span class="text-danger">{{ $message }}</span>
    @enderror
</div>


@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"></script>
    <script>
        // Wait for jQuery to be loaded before executing
        (function() {
            function initInvoiceTemplateForm() {
                // Check if jQuery is loaded
                if (typeof jQuery === 'undefined' || typeof $ === 'undefined') {
                    // Retry after a short delay
                    setTimeout(initInvoiceTemplateForm, 100);
                    return;
                }
                
                // Use jQuery safely
                jQuery(document).ready(function($) {
            // تفعيل السحب والإفلات
            var sortable = new Sortable(document.getElementById('sortable-columns'), {
                animation: 150,
                handle: '.fa-grip-vertical',
                onEnd: function() {
                    updateColumnOrder();
                }
            });


            // تحديث ترتيب الأعمدة
            function updateColumnOrder() {
                $('#sortable-columns .column-item').each(function(index) {
                    $(this).find('.column-order-input').val($(this).data('column'));
                });
            }


            // تحديث عرض العمود عند تحريك السلايدر
            $('.column-width-slider').on('input', function() {
                var columnKey = $(this).data('column');
                var width = $(this).val();
                $('#width_' + columnKey).text(width + '%');
            });


            // إظهار/إخفاء خيار الافتراضي لأنواع الفواتير
            $('.invoice-type-checkbox').each(function() {
                const typeId = $(this).val();
                if ($(this).is(':checked')) {
                    $('#default_' + typeId).show();
                }
            });


            $('.invoice-type-checkbox').on('change', function() {
                const typeId = $(this).val();
                if ($(this).is(':checked')) {
                    $('#default_' + typeId).slideDown();
                } else {
                    $('#default_' + typeId).slideUp();
                    $('#default_switch_' + typeId).prop('checked', false);
                }
            });
                });
            }
            
            // Start initialization
            initInvoiceTemplateForm();
        })();
    </script>
@endpush
