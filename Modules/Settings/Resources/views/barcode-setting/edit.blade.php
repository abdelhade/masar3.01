@extends('admin.dashboard')
@section('content')
    <div class="container-fluid py-4">
        <div class="card shadow-sm">
            <div class="card-header text-white d-flex justify-content-between align-items-center">
                <h3 class="mb-0">⚙️ إعدادات طباعة الباركود</h3>
                <button type="submit" form="settings-form" class="btn btn-success">
                    <i class="bi bi-save me-2"></i>حفظ التغييرات
                </button>
            </div>
            <div class="card-body">
                @if (session('success'))
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        {{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif

                @if ($errors->any())
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <ul class="mb-0">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif

                <div class="row">
                    <!-- Settings Form -->
                    <div class="col-lg-8">
                        <form id="settings-form" action="{{ route('barcode.print.settings.update') }}" method="POST">
                            @csrf
                            @method('PUT')

                            <!-- Nav Tabs -->
                            <ul class="nav nav-tabs mb-4" id="settingsTab" role="tablist">
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link active" id="general-tab" data-bs-toggle="tab"
                                        data-bs-target="#general" type="button" role="tab" aria-controls="general"
                                        aria-selected="true">الإعدادات العامة</button>
                                </li>
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link" id="appearance-tab" data-bs-toggle="tab"
                                        data-bs-target="#appearance" type="button" role="tab"
                                        aria-controls="appearance" aria-selected="false">المظهر والتنسيق</button>
                                </li>
                            </ul>

                            <!-- Tab Content -->
                            <div class="tab-content" id="settingsTabContent">
                                <!-- General Settings -->
                                <div class="tab-pane fade show active" id="general" role="tabpanel"
                                    aria-labelledby="general-tab">
                                    <div class="card mb-4">
                                        <div class="card-header bg-light">
                                            <h5 class="mb-0"><i class="bi bi-building me-2"></i>معلومات الشركة</h5>
                                        </div>
                                        <div class="card-body">
                                            <div class="mb-3">
                                                <label class="form-label">اسم الشركة</label>
                                                <input type="text" name="company_name"
                                                    value="{{ old('company_name', $settings->company_name ?? 'اسم الشركة') }}"
                                                    class="form-control" placeholder="أدخل اسم الشركة" id="company_name">
                                                @error('company_name')
                                                    <span class="text-danger">{{ $message }}</span>
                                                @enderror
                                            </div>
                                            <div class="form-check mb-3">
                                                <input type="hidden" name="show_company_name" value="0">
                                                <input type="checkbox" name="show_company_name" value="1"
                                                    class="form-check-input"
                                                    {{ old('show_company_name', $settings->show_company_name ?? true) ? 'checked' : '' }}
                                                    id="show_company_name">
                                                <label class="form-check-label">عرض اسم الشركة على الباركود</label>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="card mb-4">
                                        <div class="card-header bg-light">
                                            <h5 class="mb-0"><i class="bi bi-list-check me-2"></i>محتويات الباركود</h5>
                                        </div>
                                        <div class="card-body">
                                            <div class="row">
                                                <div class="col-md-6">
                                                    <div class="form-check mb-3">
                                                        <input type="hidden" name="show_item_name" value="0">
                                                        <input type="checkbox" name="show_item_name" value="1"
                                                            class="form-check-input"
                                                            {{ old('show_item_name', $settings->show_item_name ?? true) ? 'checked' : '' }}
                                                            id="show_item_name">
                                                        <label class="form-check-label">عرض اسم الصنف</label>
                                                    </div>
                                                    <div class="form-check mb-3">
                                                        <input type="hidden" name="show_item_code" value="0">
                                                        <input type="checkbox" name="show_item_code" value="1"
                                                            class="form-check-input"
                                                            {{ old('show_item_code', $settings->show_item_code ?? true) ? 'checked' : '' }}
                                                            id="show_item_code">
                                                        <label class="form-check-label">عرض كود الصنف</label>
                                                    </div>
                                                    <div class="form-check mb-3">
                                                        <input type="hidden" name="show_barcode_image" value="0">
                                                        <input type="checkbox" name="show_barcode_image" value="1"
                                                            class="form-check-input"
                                                            {{ old('show_barcode_image', $settings->show_barcode_image ?? true) ? 'checked' : '' }}
                                                            id="show_barcode_image">
                                                        <label class="form-check-label">عرض صورة الباركود</label>
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="form-check mb-3">
                                                        <input type="hidden" name="show_price_before_discount"
                                                            value="0">
                                                        <input type="checkbox" name="show_price_before_discount"
                                                            value="1" class="form-check-input"
                                                            {{ old('show_price_before_discount', $settings->show_price_before_discount ?? false) ? 'checked' : '' }}
                                                            id="show_price_before_discount">
                                                        <label class="form-check-label">عرض السعر قبل الخصم</label>
                                                    </div>
                                                    <div class="form-check mb-3">
                                                        <input type="hidden" name="show_price_after_discount"
                                                            value="0">
                                                        <input type="checkbox" name="show_price_after_discount"
                                                            value="1" class="form-check-input"
                                                            {{ old('show_price_after_discount', $settings->show_price_after_discount ?? true) ? 'checked' : '' }}
                                                            id="show_price_after_discount">
                                                        <label class="form-check-label">عرض السعر بعد الخصم</label>
                                                    </div>
                                                    <div class="form-check mb-3">
                                                        <input type="hidden" name="is_active" value="0">
                                                        <input type="checkbox" name="is_active" value="1"
                                                            class="form-check-input"
                                                            {{ old('is_active', $settings->is_active ?? true) ? 'checked' : '' }}
                                                            id="is_active">
                                                        <label class="form-check-label">تفعيل الإعداد</label>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="card">
                                        <div class="card-header bg-light">
                                            <h5 class="mb-0"><i class="bi bi-rulers me-2"></i>حجم الورقة</h5>
                                        </div>
                                        <div class="card-body">
                                            <div class="row">
                                                <div class="col-md-6 mb-3">
                                                    <label class="form-label">عرض الورقة (مم)</label>
                                                    <input type="number" name="paper_width"
                                                        value="{{ old('paper_width', $settings->paper_width ?? 25) }}"
                                                        class="form-control" min="10" max="100"
                                                        step="1" id="paper_width">
                                                    @error('paper_width')
                                                        <span class="text-danger">{{ $message }}</span>
                                                    @enderror
                                                </div>
                                                <div class="col-md-6 mb-3">
                                                    <label class="form-label">ارتفاع الورقة (مم)</label>
                                                    <input type="number" name="paper_height"
                                                        value="{{ old('paper_height', $settings->paper_height ?? 38) }}"
                                                        class="form-control" min="10" max="100"
                                                        step="1" id="paper_height">
                                                    @error('paper_height')
                                                        <span class="text-danger">{{ $message }}</span>
                                                    @enderror
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Appearance Settings -->
                                <div class="tab-pane fade" id="appearance" role="tabpanel"
                                    aria-labelledby="appearance-tab">
                                    <div class="card mb-4">
                                        <div class="card-header bg-light">
                                            <h5 class="mb-0"><i class="bi bi-boxes me-2"></i>الهوامش</h5>
                                        </div>
                                        <div class="card-body">
                                            <div class="row">
                                                <div class="col-md-6 mb-3">
                                                    <label class="form-label">الهامش العلوي (مم)</label>
                                                    <input type="number" name="margin_top"
                                                        value="{{ old('margin_top', $settings->margin_top ?? 2) }}"
                                                        class="form-control" min="0" max="10"
                                                        step="0.5" id="margin_top">
                                                    @error('margin_top')
                                                        <span class="text-danger">{{ $message }}</span>
                                                    @enderror
                                                </div>
                                                <div class="col-md-6 mb-3">
                                                    <label class="form-label">الهامش السفلي (مم)</label>
                                                    <input type="number" name="margin_bottom"
                                                        value="{{ old('margin_bottom', $settings->margin_bottom ?? 2) }}"
                                                        class="form-control" min="0" max="10"
                                                        step="0.5" id="margin_bottom">
                                                    @error('margin_bottom')
                                                        <span class="text-danger">{{ $message }}</span>
                                                    @enderror
                                                </div>
                                                <div class="col-md-6 mb-3">
                                                    <label class="form-label">الهامش الأيسر (مم)</label>
                                                    <input type="number" name="margin_left"
                                                        value="{{ old('margin_left', $settings->margin_left ?? 2) }}"
                                                        class="form-control" min="0" max="10"
                                                        step="0.5" id="margin_left">
                                                    @error('margin_left')
                                                        <span class="text-danger">{{ $message }}</span>
                                                    @enderror
                                                </div>
                                                <div class="col-md-6 mb-3">
                                                    <label class="form-label">الهامش الأيمن (مم)</label>
                                                    <input type="number" name="margin_right"
                                                        value="{{ old('margin_right', $settings->margin_right ?? 2) }}"
                                                        class="form-control" min="0" max="10"
                                                        step="0.5" id="margin_right">
                                                    @error('margin_right')
                                                        <span class="text-danger">{{ $message }}</span>
                                                    @enderror
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="card mb-4">
                                        <div class="card-header bg-light">
                                            <h5 class="mb-0"><i class="bi bi-fonts me-2"></i>أحجام الخطوط</h5>
                                        </div>
                                        <div class="card-body">
                                            <div class="row">
                                                <div class="col-md-4 mb-3">
                                                    <label class="form-label">حجم خط اسم الشركة (pt)</label>
                                                    <input type="number" name="font_size_company"
                                                        value="{{ old('font_size_company', $settings->font_size_company ?? 10) }}"
                                                        class="form-control" min="6" max="20"
                                                        step="1" id="font_size_company">
                                                    @error('font_size_company')
                                                        <span class="text-danger">{{ $message }}</span>
                                                    @enderror
                                                </div>
                                                <div class="col-md-4 mb-3">
                                                    <label class="form-label">حجم خط اسم الصنف (pt)</label>
                                                    <input type="number" name="font_size_item"
                                                        value="{{ old('font_size_item', $settings->font_size_item ?? 8) }}"
                                                        class="form-control" min="6" max="16"
                                                        step="1" id="font_size_item">
                                                    @error('font_size_item')
                                                        <span class="text-danger">{{ $message }}</span>
                                                    @enderror
                                                </div>
                                                <div class="col-md-4 mb-3">
                                                    <label class="form-label">حجم خط السعر (pt)</label>
                                                    <input type="number" name="font_size_price"
                                                        value="{{ old('font_size_price', $settings->font_size_price ?? 9) }}"
                                                        class="form-control" min="6" max="16"
                                                        step="1" id="font_size_price">
                                                    @error('font_size_price')
                                                        <span class="text-danger">{{ $message }}</span>
                                                    @enderror
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="card">
                                        <div class="card-header bg-light">
                                            <h5 class="mb-0"><i class="bi bi-upc-scan me-2"></i>الباركود</h5>
                                        </div>
                                        <div class="card-body">
                                            <div class="row">
                                                <div class="col-md-6 mb-3">
                                                    <label class="form-label">عرض الباركود (مم)</label>
                                                    <input type="number" name="barcode_width"
                                                        value="{{ old('barcode_width', $settings->barcode_width ?? 50) }}"
                                                        class="form-control" min="20" max="80"
                                                        step="5" id="barcode_width">
                                                    @error('barcode_width')
                                                        <span class="text-danger">{{ $message }}</span>
                                                    @enderror
                                                </div>
                                                <div class="col-md-6 mb-3">
                                                    <label class="form-label">ارتفاع الباركود (مم)</label>
                                                    <input type="number" name="barcode_height"
                                                        value="{{ old('barcode_height', $settings->barcode_height ?? 15) }}"
                                                        class="form-control" min="5" max="30"
                                                        step="1" id="barcode_height">
                                                    @error('barcode_height')
                                                        <span class="text-danger">{{ $message }}</span>
                                                    @enderror
                                                </div>
                                                <div class="col-md-6 mb-3">
                                                    <label class="form-label">محاذاة النص</label>
                                                    <select name="text_align" class="form-control" id="text_align">
                                                        <option value="center"
                                                            {{ old('text_align', $settings->text_align ?? 'center') == 'center' ? 'selected' : '' }}>
                                                            وسط</option>
                                                        <option value="left"
                                                            {{ old('text_align', $settings->text_align ?? 'center') == 'left' ? 'selected' : '' }}>
                                                            يسار</option>
                                                        <option value="right"
                                                            {{ old('text_align', $settings->text_align ?? 'center') == 'right' ? 'selected' : '' }}>
                                                            يمين</option>
                                                    </select>
                                                    @error('text_align')
                                                        <span class="text-danger">{{ $message }}</span>
                                                    @enderror
                                                </div>
                                                <div class="col-md-6 mb-3">
                                                    <div class="form-check">
                                                        <input type="hidden" name="invert_colors" value="0">
                                                        <input type="checkbox" name="invert_colors" value="1"
                                                            class="form-check-input"
                                                            {{ old('invert_colors', $settings->invert_colors ?? false) ? 'checked' : '' }}
                                                            id="invert_colors">
                                                        <label class="form-check-label">عكس الألوان (خلفية سوداء، نص
                                                            أبيض)</label>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>

                    <!-- Preview Panel -->
                    <div class="col-lg-4">
                        <div class="card sticky-top" style="top: 20px;">
                            <div class="card-header bg-light">
                                <h5 class="mb-0 text-center"><i class="bi bi-eye me-2"></i>معاينة الباركود</h5>
                            </div>
                            <div class="card-body text-center">
                                <div class="barcode-preview"
                                    style="width: {{ $settings->paper_width ?? 25 }}mm;
                                        height: {{ $settings->paper_height ?? 38 }}mm;
                                        padding-top: {{ $settings->margin_top ?? 2 }}mm;
                                        padding-bottom: {{ $settings->margin_bottom ?? 2 }}mm;
                                        padding-left: {{ $settings->margin_left ?? 2 }}mm;
                                        padding-right: {{ $settings->margin_right ?? 2 }}mm;
                                        text-align: {{ $settings->text_align ?? 'center' }};
                                        {{ $settings->invert_colors ? 'background: black; color: white;' : 'background: white; color: black;' }};
                                        border: 2px dashed #3498db;
                                        border-radius: 8px;
                                        margin: 15px auto;
                                        display: flex;
                                        flex-direction: column;
                                        justify-content: center;
                                        align-items: center;">

                                    <div class="preview-company-name"
                                        style="font-size: {{ $settings->font_size_company ?? 10 }}pt; font-weight: bold; margin-bottom: 3px; display: {{ $settings->show_company_name ?? true ? 'block' : 'none' }};">
                                        {{ $settings->company_name ?? 'اسم الشركة' }}
                                    </div>

                                    <div class="preview-item-name"
                                        style="font-size: {{ $settings->font_size_item ?? 8 }}pt; margin-bottom: 2px; display: {{ $settings->show_item_name ?? true ? 'block' : 'none' }};">
                                        صنف تجريبي
                                    </div>

                                    <div class="preview-barcode"
                                        style="display: {{ $settings->show_barcode_image ?? true ? 'block' : 'none' }};">
                                        <canvas id="barcode"></canvas>
                                    </div>

                                    <div class="preview-item-code"
                                        style="font-size: {{ $settings->font_size_item ?? 8 }}pt; margin: 2px 0; display: {{ $settings->show_item_code ?? true ? 'block' : 'none' }};">
                                        ITEM-001
                                    </div>

                                    <div class="preview-price-before"
                                        style="font-size: {{ $settings->font_size_price ?? 9 }}pt; text-decoration: line-through; color: #999; display: {{ $settings->show_price_before_discount ?? false ? 'block' : 'none' }};">
                                        100.00 ج.م
                                    </div>

                                    <div class="preview-price-after"
                                        style="font-size: {{ $settings->font_size_price ?? 9 }}pt; font-weight: bold; color: #e74c3c; display: {{ $settings->show_price_after_discount ?? true ? 'block' : 'none' }};">
                                        85.00 ج.م
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
        <!-- JsBarcode Library -->
        <script src="https://cdn.jsdelivr.net/npm/jsbarcode@3.11.6/dist/JsBarcode.all.min.js"></script>

        <script>
            // Initialize JsBarcode
            function updateBarcode() {
                const barcodeWidth = parseFloat(document.getElementById('barcode_width').value) || 50;
                const barcodeHeight = parseFloat(document.getElementById('barcode_height').value) || 15;
                JsBarcode("#barcode", "ITEM-001", {
                    format: "CODE128",
                    width: barcodeWidth / 25, // Adjust width relative to mm
                    height: barcodeHeight * 2, // Adjust height relative to mm
                    displayValue: false,
                    background: document.getElementById('invert_colors').checked ? '#000000' : '#ffffff',
                    lineColor: document.getElementById('invert_colors').checked ? '#ffffff' : '#000000',
                    margin: 0
                });
            }

            // Initial barcode render
            updateBarcode();

            // Live Preview Update
            document.querySelectorAll('#settings-form input, #settings-form select').forEach(input => {
                input.addEventListener('input', () => {
                    const preview = document.querySelector('.barcode-preview');
                    const companyName = document.getElementById('company_name').value || 'اسم الشركة';
                    const paperWidth = document.getElementById('paper_width').value || 25;
                    const paperHeight = document.getElementById('paper_height').value || 38;
                    const marginTop = document.getElementById('margin_top').value || 2;
                    const marginBottom = document.getElementById('margin_bottom').value || 2;
                    const marginLeft = document.getElementById('margin_left').value || 2;
                    const marginRight = document.getElementById('margin_right').value || 2;
                    const fontSizeCompany = document.getElementById('font_size_company').value || 10;
                    const fontSizeItem = document.getElementById('font_size_item').value || 8;
                    const fontSizePrice = document.getElementById('font_size_price').value || 9;
                    const textAlign = document.getElementById('text_align').value || 'center';
                    const invertColors = document.getElementById('invert_colors').checked;

                    // Update preview styles
                    preview.style.width = `${paperWidth}mm`;
                    preview.style.height = `${paperHeight}mm`;
                    preview.style.paddingTop = `${marginTop}mm`;
                    preview.style.paddingBottom = `${marginBottom}mm`;
                    preview.style.paddingLeft = `${marginLeft}mm`;
                    preview.style.paddingRight = `${marginRight}mm`;
                    preview.style.textAlign = textAlign;
                    preview.style.background = invertColors ? 'black' : 'white';
                    preview.style.color = invertColors ? 'white' : 'black';

                    // Update text elements
                    document.querySelector('.preview-company-name').style.fontSize = `${fontSizeCompany}pt`;
                    document.querySelector('.preview-company-name').style.display = document.getElementById(
                        'show_company_name').checked ? 'block' : 'none';
                    document.querySelector('.preview-company-name').textContent = companyName;

                    document.querySelector('.preview-item-name').style.fontSize = `${fontSizeItem}pt`;
                    document.querySelector('.preview-item-name').style.display = document.getElementById(
                        'show_item_name').checked ? 'block' : 'none';

                    document.querySelector('.preview-barcode').style.display = document.getElementById(
                        'show_barcode_image').checked ? 'block' : 'none';

                    document.querySelector('.preview-item-code').style.fontSize = `${fontSizeItem}pt`;
                    document.querySelector('.preview-item-code').style.display = document.getElementById(
                        'show_item_code').checked ? 'block' : 'none';

                    document.querySelector('.preview-price-before').style.fontSize = `${fontSizePrice}pt`;
                    document.querySelector('.preview-price-before').style.display = document.getElementById(
                        'show_price_before_discount').checked ? 'block' : 'none';

                    document.querySelector('.preview-price-after').style.fontSize = `${fontSizePrice}pt`;
                    document.querySelector('.preview-price-after').style.display = document.getElementById(
                        'show_price_after_discount').checked ? 'block' : 'none';

                    // Update barcode
                    updateBarcode();
                });
            });
        </script>
    @endpush
    @push('styles')
        <style>
            .barcode-settings-manager {
                font-family: 'Cairo', sans-serif;
                direction: rtl;
            }

            .barcode-preview {
                min-height: 120px;
                box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
            }

            .form-check-input:checked {
                background-color: #3498db;
                border-color: #3498db;
            }

            .form-control:focus,
            .form-select:focus {
                border-color: #3498db;
                box-shadow: 0 0 5px rgba(52, 152, 219, 0.5);
            }

            .nav-tabs .nav-link {
                border-radius: 8px 8px 0 0;
                font-weight: 600;
            }

            .nav-tabs .nav-link.active {
                background-color: #3498db;
                color: white;
                border-color: #3498db;
            }

            .card {
                border-radius: 10px;
                overflow: hidden;
            }

            .card-header {
                border-bottom: 1px solid #e9ecef;
            }

            .text-danger {
                font-size: 0.875rem;
                margin-top: 0.25rem;
            }
        </style>
    @endpush
@endsection
