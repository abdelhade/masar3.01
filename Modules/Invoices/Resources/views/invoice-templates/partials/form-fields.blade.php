{{-- المعلومات الأساسية --}}
<div class="row">
    <div class="col-md-6">
        <div class="form-group">
            <label for="name">اسم النموذج <span class="text-danger">*</span></label>
            <input type="text" name="name" id="name" class="form-control @error('name') is-invalid @enderror"
                value="{{ old('name', $template->name ?? '') }}" required>
            @error('name')
                <span class="invalid-feedback">{{ $message }}</span>
            @enderror
        </div>
    </div>

    <div class="col-md-6">
        <div class="form-group">
            <label for="code">كود النموذج <span class="text-danger">*</span></label>
            <input type="text" name="code" id="code" class="form-control @error('code') is-invalid @enderror"
                value="{{ old('code', $template->code ?? '') }}" required>
            @error('code')
                <span class="invalid-feedback">{{ $message }}</span>
            @enderror
            <small class="text-muted">مثال: sales_full, purchase_standard</small>
        </div>
    </div>
</div>

<div class="form-group">
    <label for="description">الوصف</label>
    <textarea name="description" id="description" rows="2"
        class="form-control @error('description') is-invalid @enderror">{{ old('description', $template->description ?? '') }}</textarea>
    @error('description')
        <span class="invalid-feedback">{{ $message }}</span>
    @enderror
</div>

<div class="row">
    <div class="col-md-6">
        <div class="form-group">
            <label for="sort_order">ترتيب العرض</label>
            <input type="number" name="sort_order" id="sort_order" class="form-control"
                value="{{ old('sort_order', $template->sort_order ?? 0) }}">
        </div>
    </div>

    <div class="col-md-6">
        <div class="form-group">
            <div class="custom-control custom-switch mt-4">
                <input type="checkbox" name="is_active" value="1" class="custom-control-input" id="is_active"
                    {{ old('is_active', $template->is_active ?? true) ? 'checked' : '' }}>
                <label class="custom-control-label" for="is_active">
                    النموذج نشط
                </label>
            </div>
        </div>
    </div>
</div>

<hr>

{{-- اختيار أنواع الفواتير --}}
<div class="form-group">
    <label>أنواع الفواتير <span class="text-danger">*</span></label>
    <div class="row">
        @foreach ($invoiceTypes as $typeId => $typeName)
            <div class="col-md-4">
                <div class="custom-control custom-checkbox">
                    <input type="checkbox" name="invoice_types[]" value="{{ $typeId }}"
                        class="custom-control-input invoice-type-checkbox" id="type_{{ $typeId }}"
                        {{ in_array(
                            $typeId,
                            old(
                                'invoice_types',
                                collect(optional($template)->invoiceTypes ?? [])->pluck('invoice_type')->toArray(),
                            ),
                        )
                            ? 'checked'
                            : '' }}>
                    <label class="custom-control-label" for="type_{{ $typeId }}">
                        {{ $typeName }} ({{ $typeId }})
                    </label>

                    <div class="ml-4 default-option" id="default_{{ $typeId }}" style="display: none;">
                        <div class="custom-control custom-switch">
                            <input type="checkbox" name="is_default[]" value="{{ $typeId }}"
                                class="custom-control-input" id="default_switch_{{ $typeId }}"
                                {{ collect(optional($template)->invoiceTypes ?? [])->where('invoice_type', $typeId)->first()?->is_default? 'checked': '' }}>
                            <label class="custom-control-label" for="default_switch_{{ $typeId }}">
                                <small class="text-muted">افتراضي لهذا النوع</small>
                            </label>
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>
    @error('invoice_types')
        <span class="text-danger">{{ $message }}</span>
    @enderror
</div>

<hr>

{{-- اختيار الأعمدة المرئية --}}
<div class="form-group">
    <label>الأعمدة المرئية <span class="text-danger">*</span></label>
    <p class="text-muted">اختر الأعمدة التي ستظهر في جدول الفاتورة</p>

    <div class="row">
        @foreach ($availableColumns as $columnKey => $columnName)
            <div class="col-md-3">
                <div class="custom-control custom-checkbox">
                    <input type="checkbox" name="visible_columns[]" value="{{ $columnKey }}"
                        class="custom-control-input" id="column_{{ $columnKey }}"
                        {{ in_array($columnKey, old('visible_columns', $template->visible_columns ?? [])) ? 'checked' : '' }}>
                    <label class="custom-control-label" for="column_{{ $columnKey }}">
                        {{ $columnName }}
                    </label>
                </div>
            </div>
        @endforeach
    </div>
    @error('visible_columns')
        <span class="text-danger">{{ $message }}</span>
    @enderror
</div>
