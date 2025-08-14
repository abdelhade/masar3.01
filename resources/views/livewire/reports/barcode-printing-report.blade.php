<div class="barcode-printing-report">
    <style>
        .barcode-printing-report {
            font-family: 'Cairo', 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            direction: rtl;
            padding: 20px;
            background: white;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
        }

        .header {
            background: linear-gradient(45deg, #2c3e50, #3498db);
            color: white;
            padding: 20px;
            border-radius: 15px 15px 0 0;
            margin-bottom: 20px;
        }

        .header h2 {
            margin: 0;
            font-size: 24px;
        }

        .controls {
            display: flex;
            gap: 20px;
            align-items: center;
            flex-wrap: wrap;
            padding: 20px;
            background: #f8f9fa;
            border-bottom: 2px solid #e9ecef;
        }

        .btn {
            padding: 10px 20px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-weight: 600;
            transition: all 0.3s ease;
            font-size: 14px;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }

        .btn-primary {
            background: #3498db;
            color: white;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
        }

        .table-responsive {
            overflow-x: auto;
            margin-bottom: 20px;
        }

        .items-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 12px;
            min-width: 600px;
        }

        .items-table th {
            background: linear-gradient(45deg, #34495e, #2c3e50);
            color: white;
            padding: 12px 8px;
            text-align: center;
            font-weight: 600;
            border: 1px solid #2c3e50;
            white-space: nowrap;
        }

        .items-table td {
            padding: 8px;
            text-align: center;
            border: 1px solid #ddd;
            background: white;
        }

        .items-table tr:nth-child(even) td {
            background: #f8f9fa;
        }

        .items-table tr:hover td {
            background: #e3f2fd !important;
        }

        .barcode-count-input {
            width: 80px;
            padding: 4px 6px;
            border: 1px solid #ddd;
            border-radius: 4px;
            text-align: center;
            font-weight: 600;
            background: #fff3cd;
        }

        .barcode-count-input:focus {
            border-color: #3498db;
            background: #fff;
            outline: none;
        }

        .alert {
            padding: 12px 20px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-weight: 500;
        }

        .alert-success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }

        .alert-danger {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }

        /* Barcode Print Section - Hidden from screen, only for print */
        .barcode-print-section {
            display: none !important;
        }

        .barcodes-container {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
            padding: 20px;
            justify-items: center;
        }

        .barcode-item {
            display: flex;
            flex-direction: column;
            align-items: center;
            text-align: center;
            padding: 10px;
            border: 2px dashed #ddd;
            border-radius: 8px;
            background: white;
            page-break-inside: avoid;
            break-inside: avoid;
            width: 180px;
            min-height: 120px;
        }

        .barcode-item img {
            max-width: 160px;
            height: auto;
            margin-bottom: 8px;
        }

        .barcode-item p {
            margin: 2px 0;
            font-size: 11px;
            font-weight: 600;
            line-height: 1.2;
            word-wrap: break-word;
        }

        @media print {

            .barcodes-container {
                display: grid;
                grid-template-columns: repeat(auto-fit, minmax(50mm, 1fr));
                gap: 2mm;
                padding: 5mm;
            }

            .barcode-item {
                page-break-inside: avoid;
                break-inside: avoid;
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
                color-adjust: exact !important;
            }

            .barcode-item img {
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
                color-adjust: exact !important;
                image-rendering: -webkit-optimize-contrast;
                image-rendering: crisp-edges;
            }
        }

        /* تحسين العرض على الشاشة أيضاً */
        @media screen {
            .barcode-print-section {
                margin-top: 20px;
                padding: 20px;
                border: 2px dashed #3498db;
                border-radius: 8px;
                background: #f8f9fa;
            }

            .barcodes-container {
                display: grid;
                grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
                gap: 15px;
                justify-items: center;
            }

            .barcode-item {
                border: 2px solid #ddd;
                background: white;
                box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
            }
        }

        /* Mobile Responsive */
        @media (max-width: 768px) {
            .barcode-printing-report {
                padding: 10px;
            }

            .header {
                padding: 15px;
                margin-bottom: 15px;
            }

            .header h2 {
                font-size: 18px;
            }

            .controls {
                padding: 15px;
                gap: 10px;
            }

            .btn {
                font-size: 12px;
                padding: 8px 15px;
            }

            .items-table {
                font-size: 10px;
            }

            .items-table th,
            .items-table td {
                padding: 6px 4px;
            }

            .barcode-count-input {
                width: 60px;
                font-size: 12px;
            }

            .barcodes-container {
                grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
                gap: 10px;
                padding: 15px;
            }

            .barcode-item {
                width: 140px;
                min-height: 100px;
                padding: 8px;
            }

            .barcode-item img {
                max-width: 130px;
            }

            .barcode-item p {
                font-size: 10px;
            }
        }

        /* Print Styles */
        @media print {
            * {
                -webkit-print-color-adjust: exact !important;
                color-adjust: exact !important;
                print-color-adjust: exact !important;
            }

            body {
                margin: 0;
                padding: 0;
            }

            .barcode-printing-report {
                box-shadow: none;
                border-radius: 0;
                padding: 0;
            }

            .barcode-print-section {
                display: block !important;
            }

            .header,
            .controls,
            .table-responsive,
            .alert {
                display: none !important;
            }

            .barcodes-container {
                display: grid;
                grid-template-columns: repeat(3, 1fr);
                gap: 10mm;
                padding: 10mm;
                margin: 0;
            }

            .barcode-item {
                page-break-inside: avoid;
                break-inside: avoid;
                border: 1px solid #000;
                padding: 5mm;
                margin: 0;
                width: auto;
                min-height: auto;
                display: flex;
                flex-direction: column;
                align-items: center;
                justify-content: center;
            }

            .barcode-item img {
                max-width: 50mm;
                height: auto;
                margin-bottom: 2mm;
            }

            .barcode-item p {
                font-size: 8pt;
                margin: 1mm 0;
                color: #000;
            }

            /* Force page breaks when needed */
            .barcode-item:nth-child(9n) {
                page-break-after: always;
            }
        }

        /* Print for small labels */
        @media print and (max-width: 210mm) {
            .barcodes-container {
                grid-template-columns: repeat(2, 1fr);
            }

            .barcode-item:nth-child(6n) {
                page-break-after: always;
            }

            .barcode-item:nth-child(9n) {
                page-break-after: auto;
            }
        }

        /* Remove show-barcodes class functionality since barcodes are print-only */

        /* Loading state */
        .loading-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(255, 255, 255, 0.9);
            display: flex;
            justify-content: center;
            align-items: center;
            z-index: 9999;
            display: none;
        }

        .loading-overlay.show {
            display: flex;
        }

        .spinner {
            border: 4px solid #f3f3f3;
            border-top: 4px solid #3498db;
            border-radius: 50%;
            width: 50px;
            height: 50px;
            animation: spin 1s linear infinite;
        }

        @keyframes spin {
            0% {
                transform: rotate(0deg);
            }

            100% {
                transform: rotate(360deg);
            }
        }
    </style>

    <!-- Loading Overlay -->
    <div class="loading-overlay" wire:loading.class="show">
        <div class="spinner"></div>
    </div>

    <!-- Header -->
    <div class="header">
        <h2>🖨️ تقرير طباعة الباركودات</h2>
    </div>

    <!-- Alert Messages -->
    @if (session()->has('message'))
        <div class="alert alert-success">
            {{ session('message') }}
        </div>
    @endif

    @if (session()->has('error'))
        <div class="alert alert-danger">
            {{ session('error') }}
        </div>
    @endif

    <!-- Controls -->
    <div class="controls">
        <button wire:click="generateBarcodes" class="btn btn-primary" wire:loading.attr="disabled">
            <span wire:loading.remove>🖨️ تأكيد وطباعة</span>
            <span wire:loading>⏳ جاري التوليد...</span>
        </button>

        @if (!empty($barcodes))
            <button wire:click="clearBarcodes" class="btn" style="background: #e74c3c; color: white;">
                🗑️ مسح الباركودات
            </button>
            <div style="margin-top: 10px; padding: 10px; background: #e8f5e8; border-radius: 5px; font-size: 14px;">
                ✅ تم إنشاء {{ count($barcodes) }} باركود جاهز للطباعة
            </div>
        @endif
    </div>

    <!-- Items Table -->
    <div class="table-responsive">
        <table class="items-table">
            <thead>
                <tr>
                    <th width="40">م</th>
                    <th width="120">كود الصنف</th>
                    <th width="200">اسم الصنف</th>
                    <th width="60">الوحدة</th>
                    <th width="70">الكمية المشتراة</th>
                    <th width="100">عدد الباركودات للطباعة</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($items as $index => $item)
                    <tr>
                        <td>{{ $index + 1 }}</td>
                        <td>{{ $item->item->code ?? 'غير محدد' }}</td>
                        <td>{{ $item->item->name ?? 'غير محدد' }}</td>
                        <td>{{ $item->unit->name ?? 'قطعة' }}</td>
                        <td>{{ number_format($item->qty_in, 2) }}</td>
                        <td>
                            <input type="number" class="barcode-count-input"
                                wire:model.live="barcodeCounts.{{ $item->id }}" step="1" min="0"
                                value="{{ $item->qty_in }}">
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="text-center">لا توجد أصناف في الفاتورة</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Barcode Print Section -->
    @if (!empty($barcodes))
        <div class="barcode-print-section">
            <div class="barcodes-container">
                @foreach ($barcodes as $barcode)
                    <div class="barcode-item"
                        style="width: {{ $barcode['paper_width'] ?? 50 }}mm;
                        height: {{ $barcode['paper_height'] ?? 30 }}mm;
                        padding: {{ $barcode['margin_top'] ?? 2 }}mm {{ $barcode['margin_right'] ?? 2 }}mm {{ $barcode['margin_bottom'] ?? 2 }}mm {{ $barcode['margin_left'] ?? 2 }}mm;
                        text-align: {{ $barcode['text_align'] ?? 'center' }};
                        {{ $barcode['invert_colors'] ?? false ? 'background: black; color: white;' : 'background: white; color: black;' }}
                        border: 1px solid #ddd;
                        border-radius: 4px;
                        display: flex;
                        flex-direction: column;
                        justify-content: center;
                        align-items: center;
                        page-break-inside: avoid;">

                        @if ($barcode['show_company_name'] ?? false)
                            <div
                                style="font-size: {{ $barcode['font_size_company'] ?? 10 }}pt; font-weight: bold; margin-bottom: 1mm;">
                                {{ $barcode['company_name'] ?? 'اسم الشركة' }}
                            </div>
                        @endif

                        @if ($barcode['show_item_name'] ?? true)
                            <div
                                style="font-size: {{ $barcode['font_size_item'] ?? 8 }}pt; margin-bottom: 1mm; font-weight: 600;">
                                {{ $barcode['item_name'] }}
                            </div>
                        @endif

                        @if (($barcode['show_barcode_image'] ?? true) && !empty($barcode['barcode_image']))
                            <div style="margin: 1mm 0;">
                                <img src="data:image/png;base64,{{ $barcode['barcode_image'] }}"
                                    style="max-width: {{ $barcode['barcode_width'] ?? 40 }}mm;
                                       height: {{ $barcode['barcode_height'] ?? 15 }}mm;
                                       image-rendering: crisp-edges;
                                       -webkit-print-color-adjust: exact;"
                                    alt="Barcode">
                            </div>
                        @endif

                        @if ($barcode['show_item_code'] ?? true)
                            <div
                                style="font-size: {{ $barcode['font_size_item'] ?? 8 }}pt; margin: 1mm 0; font-family: monospace;">
                                {{ $barcode['item_code'] }}
                            </div>
                        @endif

                        @if ($barcode['show_price_before_discount'] ?? false)
                            <div
                                style="font-size: {{ $barcode['font_size_price'] ?? 8 }}pt; text-decoration: line-through; color: #999; margin: 0.5mm 0;">
                                {{ number_format($barcode['price_before_discount'] ?? 0, 2) }} ج.م
                            </div>
                        @endif

                        @if ($barcode['show_price_after_discount'] ?? false)
                            <div
                                style="font-size: {{ $barcode['font_size_price'] ?? 8 }}pt; font-weight: bold; color: #e74c3c; margin: 0.5mm 0;">
                                {{ number_format($barcode['price_after_discount'] ?? 0, 2) }} ج.م
                            </div>
                        @endif
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                // Handle input focus
                document.addEventListener('focusin', function(e) {
                    if (e.target.classList.contains('barcode-count-input')) {
                        e.target.select();
                    }
                });

                // Auto-print after generation
                let printTimeout;
                window.addEventListener('barcodesGenerated', function() {
                    clearTimeout(printTimeout);
                    printTimeout = setTimeout(() => {
                        window.print();
                    }, 500);
                });

                // Handle keyboard shortcuts
                document.addEventListener('keydown', function(e) {
                    if (e.ctrlKey && e.key === 'p' && document.querySelector('.barcode-print-section')) {
                        e.preventDefault();
                        setTimeout(() => window.print(), 100);
                    }
                });
            });
        </script>
    @endpush
</div>
