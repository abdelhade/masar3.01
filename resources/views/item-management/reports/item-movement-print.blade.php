<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ø·Ø¨Ø§Ø¹Ø© ØªÙ‚Ø±ÙŠØ± Ø­Ø±ÙƒØ© ØµÙ†Ù - MASAR</title>
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@300;400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        @php
            include_once app_path('Helpers/FormatHelper.php');
        @endphp
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Cairo', sans-serif;
            direction: rtl;
            text-align: right;
            font-size: 12px;
            font-weight: bold;
            line-height: 1.2;
            color: #000;
            background: #f8f9fa;
        }

        .print-container {
            width: 100%;
            max-width: 210mm; /* A4 width */
            margin: 0 auto;
        }

        /* Print Controls - Improved Design */
        .print-controls {
            position: fixed;
            top: 90px; /* Below header */
            right: 20px;
            z-index: 2000;
            background: white;
            border-radius: 12px;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.15);
            min-width: 350px;
            max-width: 420px;
            transition: all 0.3s ease;
            border: 1px solid #e9ecef;
            max-height: calc(100vh - 120px); /* Prevent overflow */
            overflow-y: auto;
        }

        .print-controls.collapsed {
            width: 60px;
            min-width: 60px;
            max-width: 60px;
            overflow: hidden;
            border-radius: 50%;
            height: 60px;
        }

        .print-controls.collapsed .controls-header {
            border-radius: 50%;
            padding: 0;
            min-height: 60px;
            justify-content: center;
        }

        .print-controls.collapsed .controls-header h3 {
            display: none;
        }

        .print-controls.collapsed .toggle-btn {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            box-shadow: 0 4px 15px rgba(102, 126, 234, 0.3);
        }

        .print-controls.collapsed .toggle-btn i {
            font-size: 20px;
            color: white;
        }

        .controls-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 20px 25px;
            border-radius: 12px 12px 0 0;
            display: flex;
            justify-content: space-between;
            align-items: center;
            cursor: pointer;
            position: sticky;
            top: 0;
            z-index: 10;
            min-height: 70px;
        }

        .controls-header h3 {
            font-size: 20px;
            font-weight: bold;
            margin: 0;
            text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.4);
            line-height: 1.3;
            flex: 1;
            text-align: center;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 12px;
            padding: 0 10px;
            direction: rtl;
            unicode-bidi: bidi-override;
        }

        .controls-header h3 i {
            font-size: 18px;
            color: rgba(255, 255, 255, 0.95);
            flex-shrink: 0;
            margin-left: 8px;
        }

        .controls-header h3 span {
            font-weight: 800;
            letter-spacing: 1px;
            text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.5);
            white-space: nowrap;
            overflow: visible;
            text-overflow: clip;
            max-width: none;
            display: inline-block;
            font-family: 'Cairo', sans-serif;
            text-rendering: optimizeLegibility;
            -webkit-font-smoothing: antialiased;
            -moz-osx-font-smoothing: grayscale;
        }

        .toggle-btn {
            background: rgba(255, 255, 255, 0.2);
            border: none;
            color: white;
            width: 30px;
            height: 30px;
            border-radius: 50%;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.3s ease;
        }

        .toggle-btn:hover {
            background: rgba(255, 255, 255, 0.3);
            transform: scale(1.1);
        }

        .controls-content {
            padding: 20px;
            transition: all 0.3s ease;
        }

        .collapsed .controls-content {
            display: none;
        }

        .column-filters {
            margin-bottom: 20px;
        }

        .column-filters h4 {
            font-size: 14px;
            font-weight: bold;
            margin-bottom: 12px;
            color: #495057;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .column-filters h4 i {
            color: #667eea;
        }

        .column-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 8px;
            max-height: 300px;
            overflow-y: auto;
            padding: 10px;
            background: #f8f9fa;
            border-radius: 8px;
            border: 1px solid #e9ecef;
        }

        .column-grid::-webkit-scrollbar {
            width: 6px;
        }

        .column-grid::-webkit-scrollbar-track {
            background: #f1f1f1;
            border-radius: 3px;
        }

        .column-grid::-webkit-scrollbar-thumb {
            background: #c1c1c1;
            border-radius: 3px;
        }

        .column-grid::-webkit-scrollbar-thumb:hover {
            background: #a8a8a8;
        }

        .column-item {
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 11px;
            padding: 6px 8px;
            background: white;
            border-radius: 6px;
            border: 1px solid #e9ecef;
            transition: all 0.2s ease;
        }

        .column-item:hover {
            background: #f8f9fa;
            border-color: #667eea;
        }

        .column-item input[type="checkbox"] {
            width: 14px;
            height: 14px;
            accent-color: #667eea;
        }

        .column-item label {
            cursor: pointer;
            font-size: 11px;
            font-weight: 500;
            color: #495057;
            flex: 1;
        }

        .print-actions {
            display: flex;
            gap: 12px;
            justify-content: center;
            flex-wrap: wrap;
        }

        .print-btn, .select-all-btn {
            border: none;
            padding: 12px 20px;
            font-size: 13px;
            border-radius: 8px;
            cursor: pointer;
            transition: all 0.3s ease;
            font-family: 'Cairo', sans-serif;
            font-weight: bold;
            display: flex;
            align-items: center;
            gap: 8px;
            min-width: 120px;
            justify-content: center;
        }

        .print-btn {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }

        .print-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(102, 126, 234, 0.4);
        }

        .select-all-btn {
            background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
            color: white;
        }

        .select-all-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(40, 167, 69, 0.4);
        }

        /* Fixed Header */
        .print-header {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            background: white;
            border-bottom: 2px solid #000;
            padding: 10px 15px;
            z-index: 1000;
            height: 60px;
        }

        .header-content {
            display: flex;
            justify-content: space-between;
            align-items: center;
            height: 100%;
        }

        .company-info {
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .company-logo {
            width: 40px;
            height: 40px;
            background: #2c3e50;
            border-radius: 5px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: bold;
            font-size: 14px;
        }

        .company-details h1 {
            font-size: 16px;
            font-weight: bold;
            margin-bottom: 2px;
            color: #2c3e50;
        }

        .company-details p {
            font-size: 10px;
            color: #666;
            margin: 0;
        }

        .report-info {
            text-align: center;
        }

        .report-title {
            font-size: 14px;
            font-weight: bold;
            margin-bottom: 3px;
            color: #2c3e50;
        }

        .report-date {
            font-size: 10px;
            color: #666;
        }

        .page-info {
            text-align: left;
            font-size: 10px;
            color: #666;
        }

        /* Fixed Footer */
        .print-footer {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            background: white;
            border-top: 1px solid #000;
            padding: 8px 15px;
            z-index: 1000;
            height: 40px;
        }

        .footer-content {
            display: flex;
            justify-content: space-between;
            align-items: center;
            height: 100%;
            font-size: 9px;
            color: #666;
        }

        /* Main Content */
        .print-content {
            margin-top: 70px; /* Header height + padding */
            margin-bottom: 50px; /* Footer height + padding */
            padding: 20px;
            margin-right: 5px; /* Fixed space for controls - never changes */
            transition: none; /* Remove transition to prevent movement */
        }

        .print-content.full-width {
            margin-right: 440px; /* Keep the same margin even when collapsed */
        }

        /* Filters Section */
        .filters-section {
            margin-bottom: 20px;
            padding: 15px;
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            border-radius: 10px;
            border: 1px solid #dee2e6;
            font-size: 11px;
        }

        .filters-title {
            font-weight: bold;
            margin-bottom: 8px;
            color: #495057;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .filters-title i {
            color: #667eea;
        }

        .filter-item {
            display: inline-block;
            margin-left: 15px;
            color: #666;
            background: white;
            padding: 4px 8px;
            border-radius: 4px;
            border: 1px solid #dee2e6;
        }

        /* Table Styles */
        .movements-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
            font-size: 9px;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        }

        .movements-table th,
        .movements-table td {
            border: 1px solid #dee2e6;
            padding: 6px 8px;
            text-align: center;
            vertical-align: middle;
        }

        .movements-table th {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            font-weight: bold;
            font-size: 10px;
            text-shadow: 1px 1px 2px rgba(0, 0, 0, 0.2);
        }

        .movements-table tbody tr:nth-child(even) {
            background: #f8f9fa;
        }

        .movements-table tbody tr:hover {
            background: #e3f2fd;
        }

        /* Column visibility classes */
        .col-hidden {
            display: none !important;
        }

        /* Totals Section */
        .totals-section {
            margin-top: 20px;
            padding: 15px;
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            border: 1px solid #dee2e6;
            border-radius: 10px;
        }

        .totals-grid {
            display: flex;
            justify-content: space-around;
            flex-wrap: wrap;
            gap: 20px;
        }

        .total-item {
            text-align: center;
            background: white;
            padding: 12px 20px;
            border-radius: 8px;
            border: 1px solid #dee2e6;
            min-width: 120px;
        }

        .total-label {
            font-size: 10px;
            color: #495057;
            margin-bottom: 4px;
            font-weight: bold;
        }

        .total-value {
            font-size: 14px;
            font-weight: bold;
            color: #2c3e50;
        }

        /* Movement type badges */
        .badge-in {
            background: #d4edda;
            color: #155724;
            padding: 2px 6px;
            border-radius: 4px;
            font-size: 8px;
            font-weight: bold;
        }

        .badge-out {
            background: #f8d7da;
            color: #721c24;
            padding: 2px 6px;
            border-radius: 4px;
            font-size: 8px;
            font-weight: bold;
        }

        /* Page Break */
        .page-break {
            page-break-before: always;
        }

        /* Print Specific - Dynamic Orientation */
        @media print {
            .print-controls {
                display: none !important;
            }

            .print-content {
                margin-right: 0 !important;
            }

            body {
                background: white !important;
            }

            /* Dynamic page orientation based on column count */
            .print-container.landscape {
                @page {
                    size: A4 landscape;
                    margin: 8mm;
                }
            }

            .print-container.portrait {
                @page {
                    size: A4 portrait;
                    margin: 10mm;
                }
            }

            body {
                font-size: 10px;
            }

            .print-header {
                position: fixed;
                top: 0;
                left: 0;
                right: 0;
                height: 60px;
            }

            .print-footer {
                position: fixed;
                bottom: 0;
                left: 0;
                right: 0;
                height: 40px;
            }

            .print-content {
                margin-top: 70px;
                margin-bottom: 50px;
            }

            .movements-table {
                font-size: 8px;
            }

            .movements-table th {
                font-size: 9px;
            }

            .movements-table th,
            .movements-table td {
                padding: 4px 6px;
            }

            /* Landscape specific adjustments */
            .print-container.landscape .movements-table {
                font-size: 7px;
            }

            .print-container.landscape .movements-table th {
                font-size: 8px;
            }

            .print-container.landscape .movements-table th,
            .print-container.landscape .movements-table td {
                padding: 3px 4px;
            }
        }

        /* No Print Elements */
        .no-print {
            display: none !important;
        }

        /* Responsive Design */
        @media (max-width: 1200px) {
            .print-controls {
                right: 10px;
                left: 10px;
                max-width: none;
            }
            
            .print-content {
                margin-right: 20px;
            }
        }

        /* Print orientation indicator */
        .orientation-indicator {
            position: fixed;
            top: 700px; /* Below the print controls panel */
            right: 20px;
            background: rgba(102, 126, 234, 0.9);
            color: white;
            padding: 8px 12px;
            border-radius: 6px;
            font-size: 11px;
            font-weight: bold;
            z-index: 1500;
            display: none;
            max-width: 420px;
            text-align: center;
        }

        .orientation-indicator.show {
            display: block;
        }
    </style>
</head>
<body>
    <!-- Print Controls - Improved Design -->
    <div class="print-controls" id="printControls">
        <div class="controls-header" onclick="toggleControls()">
            <h3>
                <i class="fas fa-cog"></i>
                <span>Ø¥Ø¹Ø¯Ø§Ø¯Ø§Øª Ø§Ù„Ø·Ø¨Ø§Ø¹Ø©</span>
            </h3>
            <button class="toggle-btn" id="toggleBtn">
                <i class="fas fa-chevron-left" id="toggleIcon"></i>
            </button>
        </div>
        
        <div class="controls-content">
            <div class="column-filters">
                <h4><i class="fas fa-columns"></i> Ø§Ø®ØªØ± Ø§Ù„Ø£Ø¹Ù…Ø¯Ø© Ø§Ù„Ù…Ø±Ø§Ø¯ Ø·Ø¨Ø§Ø¹ØªÙ‡Ø§:</h4>
                <div class="column-grid">
                    <div class="column-item">
                        <input type="checkbox" id="col-date" checked>
                        <label for="col-date">Ø§Ù„ØªØ§Ø±ÙŠØ®</label>
                    </div>
                    <div class="column-item">
                        <input type="checkbox" id="col-reference" checked>
                        <label for="col-reference">Ù…ØµØ¯Ø± Ø§Ù„Ø¹Ù…Ù„ÙŠØ©</label>
                    </div>
                    <div class="column-item">
                        <input type="checkbox" id="col-movement-type" checked>
                        <label for="col-movement-type">Ù†ÙˆØ¹ Ø§Ù„Ø­Ø±ÙƒØ©</label>
                    </div>
                    <div class="column-item">
                        <input type="checkbox" id="col-warehouse" checked>
                        <label for="col-warehouse">Ø§Ù„Ù…Ø®Ø²Ù†</label>
                    </div>
                    <div class="column-item">
                        <input type="checkbox" id="col-unit" checked>
                        <label for="col-unit">Ø§Ù„ÙˆØ­Ø¯Ø©</label>
                    </div>
                    <div class="column-item">
                        <input type="checkbox" id="col-balance-before" checked>
                        <label for="col-balance-before">Ø§Ù„Ø±ØµÙŠØ¯ Ù‚Ø¨Ù„ Ø§Ù„Ø­Ø±ÙƒØ©</label>
                    </div>
                    <div class="column-item">
                        <input type="checkbox" id="col-quantity" checked>
                        <label for="col-quantity">Ø§Ù„ÙƒÙ…ÙŠØ©</label>
                    </div>
                    <div class="column-item">
                        <input type="checkbox" id="col-balance-after" checked>
                        <label for="col-balance-after">Ø§Ù„Ø±ØµÙŠØ¯ Ø¨Ø¹Ø¯ Ø§Ù„Ø­Ø±ÙƒØ©</label>
                    </div>
                </div>
            </div>
            
            <div class="print-actions">
                <button type="button" class="select-all-btn" onclick="toggleAllColumns()">
                    <i class="fas fa-check-double"></i>
                    ØªØ­Ø¯ÙŠØ¯ Ø§Ù„ÙƒÙ„
                </button>
                <button type="button" class="print-btn" onclick="printReport()">
                    <i class="fas fa-print"></i>
                    Ø·Ø¨Ø§Ø¹Ø© Ø§Ù„ØªÙ‚Ø±ÙŠØ±
                </button>
            </div>
        </div>
    </div>

    <!-- Orientation Indicator -->
    <div class="orientation-indicator" id="orientationIndicator">
        <i class="fas fa-info-circle"></i>
        <span id="orientationText">Ø³ÙŠØªÙ… Ø§Ù„Ø·Ø¨Ø§Ø¹Ø© Ø¨Ø§Ù„Ø¹Ø±Ø¶</span>
    </div>

    <div class="print-container" id="printContainer">
        <!-- Fixed Header -->
        <div class="print-header">
            <div class="header-content">
                <div class="company-info">
                    <div class="company-logo">M</div>
                    <div class="company-details">
                        <h1>MASAR</h1>
                        <p>Ù†Ø¸Ø§Ù… Ø¥Ø¯Ø§Ø±Ø© Ø§Ù„Ù…Ø®Ø²ÙˆÙ† ÙˆØ§Ù„Ù…Ø¨ÙŠØ¹Ø§Øª</p>
                    </div>
                </div>
                <div class="report-info">
                    <div class="report-title">ØªÙ‚Ø±ÙŠØ± Ø­Ø±ÙƒØ© ØµÙ†Ù</div>
                    <div class="report-date">{{ now()->format('Y-m-d H:i:s') }}</div>
                </div>
                <div class="page-info">
                    ØµÙØ­Ø© <span class="page-number">1</span>
                </div>
            </div>
        </div>

        <!-- Main Content -->
        <div class="print-content" id="printContent">
            <!-- Filters Section -->
            <div class="filters-section">
                <div class="filters-title">
                    <i class="fas fa-filter"></i>
                    Ø§Ù„ÙÙ„Ø§ØªØ± Ø§Ù„Ù…Ø·Ø¨Ù‚Ø©:
                </div>
                @if ($itemId)
                    @php
                        $item = \App\Models\Item::find($itemId);
                    @endphp
                    <span class="filter-item">Ø§Ù„ØµÙ†Ù: {{ $item ? $item->name : 'ØºÙŠØ± Ù…Ø­Ø¯Ø¯' }}</span>
                @endif
                @if ($warehouseId !== 'all')
                    @php
                        $warehouse = \Modules\Accounts\Models\AccHead::find($warehouseId);
                    @endphp
                    <span class="filter-item">Ø§Ù„Ù…Ø®Ø²Ù†: {{ $warehouse ? $warehouse->aname : 'ØºÙŠØ± Ù…Ø­Ø¯Ø¯' }}</span>
                @else
                    <span class="filter-item">Ø§Ù„Ù…Ø®Ø²Ù†: Ø¬Ù…ÙŠØ¹ Ø§Ù„Ù…Ø®Ø§Ø²Ù†</span>
                @endif
                <span class="filter-item">Ù…Ù† ØªØ§Ø±ÙŠØ®: {{ $fromDate }}</span>
                <span class="filter-item">Ø¥Ù„Ù‰ ØªØ§Ø±ÙŠØ®: {{ $toDate }}</span>
            </div>

            <!-- Movements Table -->
            <table class="movements-table" id="movementsTable">
                <thead>
                    <tr>
                        <th class="col-date">Ø§Ù„ØªØ§Ø±ÙŠØ®</th>
                        <th class="col-reference">Ù…ØµØ¯Ø± Ø§Ù„Ø¹Ù…Ù„ÙŠØ©</th>
                        <th class="col-movement-type">Ù†ÙˆØ¹ Ø§Ù„Ø­Ø±ÙƒØ©</th>
                        <th class="col-warehouse">Ø§Ù„Ù…Ø®Ø²Ù†</th>
                        <th class="col-unit">Ø§Ù„ÙˆØ­Ø¯Ø©</th>
                        <th class="col-balance-before">Ø§Ù„Ø±ØµÙŠØ¯ Ù‚Ø¨Ù„ Ø§Ù„Ø­Ø±ÙƒØ©</th>
                        <th class="col-quantity">Ø§Ù„ÙƒÙ…ÙŠØ©</th>
                        <th class="col-balance-after">Ø§Ù„Ø±ØµÙŠØ¯ Ø¨Ø¹Ø¯ Ø§Ù„Ø­Ø±ÙƒØ©</th>
                    </tr>
                </thead>
                <tbody>
                    @php
                        // Get movements data
                        $movementsQuery = \App\Models\OperationItems::where('item_id', $itemId)
                            ->when($warehouseId !== 'all', function ($q) use ($warehouseId) {
                                $q->where('detail_store', $warehouseId);
                            })
                            ->when($fromDate, function ($q) use ($fromDate) {
                                $q->whereDate('created_at', '>=', $fromDate);
                            })
                            ->when($toDate, function ($q) use ($toDate) {
                                $q->whereDate('created_at', '<=', $toDate);
                            })
                            ->orderBy('created_at', 'asc');

                        $allMovements = $movementsQuery->get();

                        // Calculate initial balance
                        if ($warehouseId === 'all' || empty($warehouseId)) {
                            $balanceBefore = \App\Models\OperationItems::where('item_id', $itemId)
                                ->where('created_at', '<', $fromDate)
                                ->sum('qty_in') -
                                \App\Models\OperationItems::where('item_id', $itemId)
                                ->where('created_at', '<', $fromDate)
                                ->sum('qty_out');
                        } else {
                            $balanceBefore = \App\Models\OperationItems::where('item_id', $itemId)
                                ->where('detail_store', $warehouseId)
                                ->where('created_at', '<', $fromDate)
                                ->sum('qty_in') -
                                \App\Models\OperationItems::where('item_id', $itemId)
                                ->where('detail_store', $warehouseId)
                                ->where('created_at', '<', $fromDate)
                                ->sum('qty_out');
                        }

                        $balanceAfter = 0;
                        $totalIn = 0;
                        $totalOut = 0;
                    @endphp

                    @foreach ($allMovements as $movement)
                        @php
                            // Get reference name
                            $referenceName = '';
                            $baseId = $movement->pro_tybe;
                            $translations = [
                                '10' => 'ÙØ§ØªÙˆØ±Ø© Ù…Ø¨ÙŠØ¹Ø§Øª',
                                '11' => 'ÙØ§ØªÙˆØ±Ø© Ù…Ø´ØªØ±ÙŠØ§Øª',
                                '12' => 'Ù…Ø±Ø¯ÙˆØ¯ Ù…Ø¨ÙŠØ¹Ø§Øª',
                                '13' => 'Ù…Ø±Ø¯ÙˆØ¯ Ù…Ø´ØªØ±ÙŠØ§Øª',
                                '14' => 'Ø§Ù…Ø± Ø¨ÙŠØ¹',
                                '15' => 'Ø§Ù…Ø± Ø´Ø±Ø§Ø¡',
                                '16' => 'Ø¹Ø±Ø¶ Ø³Ø¹Ø± Ù„Ø¹Ù…ÙŠÙ„',
                                '17' => 'Ø¹Ø±Ø¶ Ø³Ø¹Ø± Ù…Ù† Ù…ÙˆØ±Ø¯',
                                '18' => 'ÙØ§ØªÙˆØ±Ø© ØªÙˆØ§Ù„Ù',
                                '19' => 'Ø§Ù…Ø± ØµØ±Ù',
                                '20' => 'Ø§Ù…Ø± Ø§Ø¶Ø§ÙØ©',
                                '21' => 'ØªØ­ÙˆÙŠÙ„ Ù…Ù† Ù…Ø®Ø²Ù† Ù„Ù…Ø®Ø²Ù†',
                                '22' => 'Ø§Ù…Ø± Ø­Ø¬Ø²',
                                '23' => 'ØªØ­ÙˆÙŠÙ„ Ø¨ÙŠÙ† ÙØ±ÙˆØ¹',
                                '35' => 'Ø³Ù†Ø¯ Ø¥ØªÙ„Ø§Ù Ù…Ø®Ø²ÙˆÙ†',
                                '56' => 'Ù†Ù…ÙˆØ°Ø¬ ØªØµÙ†ÙŠØ¹',
                                '57' => 'Ø§Ù…Ø± ØªØ´ØºÙŠÙ„',
                                '58' => 'ØªØµÙ†ÙŠØ¹ Ù…Ø¹ÙŠØ§Ø±ÙŠ',
                                '59' => 'ØªØµÙ†ÙŠØ¹ Ø­Ø±',
                                '60' => 'ØªØ³Ø¬ÙŠÙ„ Ø§Ù„Ø§Ø±ØµØ¯Ù‡ Ø§Ù„Ø§ÙØªØªØ§Ø­ÙŠÙ‡ Ù„Ù„Ù…Ø®Ø§Ø²Ù†',
                            ];
                            $referenceName = $translations[$baseId] ?? 'N/A';

                            // Calculate balance after
                            if ($movement->qty_in != 0) {
                                $balanceAfter = $balanceBefore + $movement->qty_in;
                                $totalIn += $movement->qty_in;
                            } elseif ($movement->qty_out != 0) {
                                $balanceAfter = $balanceBefore - $movement->qty_out;
                                $totalOut += $movement->qty_out;
                            }

                            // Get warehouse name
                            $warehouse = \Modules\Accounts\Models\AccHead::find($movement->detail_store);
                            $warehouseName = $warehouse ? $warehouse->aname : 'N/A';

                            // Get unit name
                            $item = \App\Models\Item::find($itemId);
                            $unitName = $item && $item->units->first() ? $item->units->first()->name : 'N/A';
                        @endphp

                        <tr>
                            <td class="col-date">{{ $movement->created_at->format('Y-m-d') }}</td>
                            <td class="col-reference">{{ $movement->pro_id }}#_{{ $referenceName }}</td>
                            <td class="col-movement-type">
                                <span class="{{ $movement->qty_in != 0 ? 'badge-in' : 'badge-out' }}">
                                    {{ $movement->qty_in != 0 ? 'in' : 'out' }}
                                </span>
                            </td>
                            <td class="col-warehouse">{{ $warehouseName }}</td>
                            <td class="col-unit">{{ $unitName }}</td>
                            <td class="col-balance-before">{{ $balanceBefore }}</td>
                            <td class="col-quantity">{{ $movement->qty_in != 0 ? $movement->qty_in : $movement->qty_out }}</td>
                            <td class="col-balance-after">{{ $balanceAfter }}</td>
                        </tr>
                        @php
                            $balanceBefore = $balanceAfter;
                        @endphp
                    @endforeach
                </tbody>
            </table>

            <!-- Totals Section -->
            <div class="totals-section">
                <div class="totals-grid">
                    <div class="total-item">
                        <div class="total-label">Ø¥Ø¬Ù…Ø§Ù„ÙŠ Ø§Ù„ÙƒÙ…ÙŠØ§Øª Ø§Ù„ÙˆØ§Ø±Ø¯Ø©</div>
                        <div class="total-value">{{ $totalIn }}</div>
                    </div>
                    <div class="total-item">
                        <div class="total-label">Ø¥Ø¬Ù…Ø§Ù„ÙŠ Ø§Ù„ÙƒÙ…ÙŠØ§Øª Ø§Ù„ØµØ§Ø¯Ø±Ø©</div>
                        <div class="total-value">{{ $totalOut }}</div>
                    </div>
                    <div class="total-item">
                        <div class="total-label">Ø§Ù„Ø±ØµÙŠØ¯ Ø§Ù„Ù†Ù‡Ø§Ø¦ÙŠ</div>
                        <div class="total-value">{{ $balanceAfter }}</div>
                    </div>
                    <div class="total-item">
                        <div class="total-label">Ø¹Ø¯Ø¯ Ø§Ù„Ø­Ø±ÙƒØ§Øª</div>
                        <div class="total-value">{{ $allMovements->count() }}</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Fixed Footer -->
        <div class="print-footer">
            <div class="footer-content">
                <div class="footer-left">
                    <div>ØªÙ… Ø¥Ù†Ø´Ø§Ø¡ Ù‡Ø°Ø§ Ø§Ù„ØªÙ‚Ø±ÙŠØ± Ø¨ÙˆØ§Ø³Ø·Ø© Ù†Ø¸Ø§Ù… MASAR</div>
                    <div>ØªØ§Ø±ÙŠØ® Ø§Ù„Ø·Ø¨Ø§Ø¹Ø©: {{ now()->format('Y-m-d H:i:s') }}</div>
                </div>
                <div class="footer-right">
                    <div>Ø¥Ø¬Ù…Ø§Ù„ÙŠ Ø§Ù„Ø³Ø¬Ù„Ø§Øª: {{ $allMovements->count() }}</div>
                    <div>ØµÙØ­Ø© 1 Ù…Ù† 1</div>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Toggle controls panel
        function toggleControls() {
            const controls = document.getElementById('printControls');
            const toggleIcon = document.getElementById('toggleIcon');
            const indicator = document.getElementById('orientationIndicator');
            
            controls.classList.toggle('collapsed');
            
            if (controls.classList.contains('collapsed')) {
                toggleIcon.className = 'fas fa-cog';
                // Adjust indicator position for collapsed state
                if (indicator.classList.contains('show')) {
                    indicator.style.right = '90px';
                }
            } else {
                toggleIcon.className = 'fas fa-chevron-left';
                // Adjust indicator position for expanded state
                if (indicator.classList.contains('show')) {
                    indicator.style.right = '20px';
                }
            }
        }

        // Calculate visible columns and determine orientation
        function calculateOrientation() {
            const visibleColumns = document.querySelectorAll('.column-item input[type="checkbox"]:checked').length;
            const container = document.getElementById('printContainer');
            const indicator = document.getElementById('orientationIndicator');
            const orientationText = document.getElementById('orientationText');
            const controls = document.getElementById('printControls');
            
            // If more than 6 columns are visible, use landscape
            if (visibleColumns > 6) {
                container.classList.remove('portrait');
                container.classList.add('landscape');
                indicator.classList.add('show');
                orientationText.textContent = 'Ø³ÙŠØªÙ… Ø§Ù„Ø·Ø¨Ø§Ø¹Ø© Ø¨Ø§Ù„Ø¹Ø±Ø¶ (Landscape)';
                
                // Adjust indicator position based on controls state
                if (controls.classList.contains('collapsed')) {
                    indicator.style.right = '90px'; // Align with collapsed controls
                } else {
                    indicator.style.right = '20px'; // Align with expanded controls
                }
            } else {
                container.classList.remove('landscape');
                container.classList.add('portrait');
                indicator.classList.remove('show');
            }
        }

        // Column visibility management
        function updateColumnVisibility() {
            const checkboxes = document.querySelectorAll('.column-item input[type="checkbox"]');
            
            checkboxes.forEach(checkbox => {
                const columnClass = checkbox.id.replace('col-', 'col-');
                const columns = document.querySelectorAll('.' + columnClass);
                
                if (checkbox.checked) {
                    columns.forEach(col => col.classList.remove('col-hidden'));
                } else {
                    columns.forEach(col => col.classList.add('col-hidden'));
                }
            });
            
            // Recalculate orientation after column changes
            calculateOrientation();
        }

        // Toggle all columns
        function toggleAllColumns() {
            const checkboxes = document.querySelectorAll('.column-item input[type="checkbox"]');
            const allChecked = Array.from(checkboxes).every(cb => cb.checked);
            
            checkboxes.forEach(checkbox => {
                checkbox.checked = !allChecked;
            });
            
            updateColumnVisibility();
        }

        // Print function
        function printReport() {
            window.print();
        }

        // Initialize
        document.addEventListener('DOMContentLoaded', function() {
            // Add event listeners to checkboxes
            const checkboxes = document.querySelectorAll('.column-item input[type="checkbox"]');
            checkboxes.forEach(checkbox => {
                checkbox.addEventListener('change', updateColumnVisibility);
            });
            
            // Initial visibility update and orientation calculation
            updateColumnVisibility();
            calculateOrientation();
        });
    </script>
</body>
</html>

