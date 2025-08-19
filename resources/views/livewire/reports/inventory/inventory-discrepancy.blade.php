<div>

    {{-- Filters --}}
    <div class="card mb-4">
        <div class="card-header">
            <h5 class="card-title mb-0"><i class="ri-filter-3-line"></i> فلاتر التقرير</h5>
        </div>
        <div class="card-body">
            <div class="row align-items-end">

                <div class="col-md-4">
                    <label class="form-label">المخزن</label>
                    <select wire:model.live="selectedWarehouse" class="form-select">
                        <option value="">اختر المخزن</option>
                        @foreach ($warehouses as $warehouse)
                            <option value="{{ $warehouse->id }}">{{ $warehouse->aname }}</option>
                        @endforeach
                    </select>
                    @error('selectedWarehouse')
                        <span class="text-danger small">{{ $message }}</span>
                    @enderror
                </div>

                <div class="col-md-4">
                    <label class="form-label">حساب تسوية الجرد</label>
                    <select wire:model="selectedPartner" class="form-select">
                        @foreach ($partners as $partner)
                            <option value="{{ $partner->id }}">{{ $partner->aname }}</option>
                        @endforeach
                    </select>
                    @error('selectedPartner')
                        <span class="text-danger small">{{ $message }}</span>
                    @enderror
                </div>

                <div class="col-md-4">
                    <div class="d-flex gap-2">
                        <button type="button" class="btn btn-primary w-100" wire:click="applyInventoryAdjustments"
                            wire:loading.attr="disabled">
                            <span wire:loading.remove wire:target="applyInventoryAdjustments"><i
                                    class="ri-refresh-line"></i> تحديث الأرصدة وتطبيق التسوية</span>
                            <span wire:loading wire:target="applyInventoryAdjustments">جاري التطبيق...</span>
                        </button>
                        <button type="button" class="btn btn-success" onclick="window.print()"> طباعه</button>
                    </div>
                </div>

            </div>
        </div>
    </div>

    {{-- إحصائيات سريعة --}}
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card bg-light">
                <div class="card-body text-center">
                    <h6 class="card-title">إجمالي الأصناف</h6>
                    <h4 class="text-primary">{{ $totalItems }}</h4>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-light">
                <div class="card-body text-center">
                    <h6 class="card-title">أصناف بها زيادة</h6>
                    <h4 class="text-info">{{ $itemsWithOverage }}</h4>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-light">
                <div class="card-body text-center">
                    <h6 class="card-title">أصناف بها نقص</h6>
                    <h4 class="text-danger">{{ $itemsWithShortage }}</h4>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-light">
                <div class="card-body text-center">
                    <h6 class="card-title">أصناف مطابقة</h6>
                    <h4 class="text-success">{{ $itemsMatching }}</h4>
                </div>
            </div>
        </div>
    </div>

    {{-- Alert Container --}}
    <div id="alertContainer"></div>

    {{-- إشعار حساب الفروقات --}}
    @if (!$inventoryDifferenceAccount)
        <div class="alert alert-warning">
            <i class="ri-alert-line"></i>
            تحذير: حساب فروقات الجرد غير محدد في الإعدادات العامة. يرجى تحديد قيمة للمفتاح
            <code>show_inventory_difference_account</code> في جدول PublicSettings.
        </div>
    @endif

    {{-- Data Table --}}
    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-hover">
                    <thead class="table-light">
                        <tr class="text-center">
                            <th>#</th>
                            <th>اسم الصنف</th>
                            <th>التكلفة</th>
                            <th>الرصيد الدفتري</th>
                            <th>الكمية الفعلية (الجرد)</th>
                            <th>الفرق (الكمية)</th>
                            <th>نوع الفرق</th>
                            <th>قيمة الفرق</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($inventoryData as $index => $data)
                            <tr
                                class="{{ $data['discrepancy'] < 0 ? 'table-danger' : ($data['discrepancy'] > 0 ? 'table-info' : '') }}">
                                <td class="text-center">{{ $index + 1 }}</td>
                                {{-- CORRECTED --}}
                                <td class="text-center">{{ $data['item_name'] }}</td>
                                <td class="text-center">{{ number_format($data['item_cost']) }}</td>
                                <td class="text-center">
                                    {{ number_format($data['system_quantity']) }}
                                    {{-- CORRECTED --}}
                                    <br><small>{{ $data['main_unit_name'] }}</small>
                                </td>
                                <td style="width: 150px;">
                                    {{-- CORRECTED --}}
                                    <input type="number"
                                        wire:model.live.debounce.500ms="quantities.{{ $data['item_id'] }}"
                                        class="form-control form-control-sm text-center" step="any">
                                </td>
                                <td class="text-center fw-bold">
                                    <span
                                        class="{{ $data['discrepancy'] < 0 ? 'text-danger' : ($data['discrepancy'] > 0 ? 'text-primary' : 'text-success') }}">
                                        {{ $data['discrepancy'] > 0 ? '+' : '' }}{{ number_format($data['discrepancy']) }}
                                    </span>
                                </td>
                                <td class="text-center">
                                    <span
                                        class="badge {{ $data['discrepancy'] < 0 ? 'bg-danger' : ($data['discrepancy'] > 0 ? 'bg-info' : 'bg-success') }}">
                                        {{ $data['discrepancy_type'] }}
                                    </span>
                                </td>
                                <td class="text-center fw-bold">
                                    <span
                                        class="{{ $data['discrepancy_value'] < 0 ? 'text-danger' : ($data['discrepancy_value'] > 0 ? 'text-primary' : 'text-success') }}">
                                        {{ $data['discrepancy_value'] > 0 ? '+' : '' }}{{ number_format($data['discrepancy_value']) }}
                                    </span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center py-4">
                                    @if (!$selectedWarehouse)
                                        الرجاء اختيار مخزن لعرض الأصناف
                                    @else
                                        لا توجد أصناف لعرضها
                                    @endif
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- ملخص القيم المالية المحسن --}}
            @if (count($inventoryData) > 0)
                @php
                    $summary = collect($inventoryData);
                    $totalIncreaseValue = $summary->where('discrepancy_value', '>', 0)->sum('discrepancy_value');
                    $totalDecreaseValue = abs($summary->where('discrepancy_value', '<', 0)->sum('discrepancy_value'));
                    $netDifference = $summary->sum('discrepancy_value');
                @endphp

                <div class="row mt-4">
                    <div class="col-md-3">
                        <div class="card bg-info text-white">
                            <div class="card-body text-center">
                                <i class="ri-arrow-up-line fs-2"></i>
                                <h6 class="mt-2">إجمالي قيمة الزيادات</h6>
                                <h4>{{ number_format($totalIncreaseValue) }}</h4>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card bg-danger text-white">
                            <div class="card-body text-center">
                                <i class="ri-arrow-down-line fs-2"></i>
                                <h6 class="mt-2">إجمالي قيمة النقص</h6>
                                <h4>{{ number_format($totalDecreaseValue) }}</h4>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card {{ $netDifference >= 0 ? 'bg-success' : 'bg-warning' }} text-white">
                            <div class="card-body text-center">
                                <i class="ri-calculator-line fs-2"></i>
                                <h6 class="mt-2">صافي الفرق</h6>
                                <h4>{{ number_format($netDifference) }}</h4>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card bg-secondary text-white">
                            <div class="card-body text-center">
                                <i class="ri-account-circle-line fs-2"></i>
                                <h6 class="mt-2">حساب الفروقات</h6>
                                <small>{{ $inventoryDifferenceAccount ? 'محدد' : 'غير محدد' }}</small>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- تفاصيل إضافية للفروقات --}}
                @if ($hasUnsavedChanges)
                    <div class="alert alert-warning mt-3">
                        <i class="ri-information-line"></i>
                        يوجد تغييرات غير محفوظة. انقر على "تحديث الأرصدة وتطبيق التسوية" لحفظ التغييرات.
                    </div>
                @endif

                {{-- جدول ملخص بالأصناف التي بها فروقات فقط --}}
                @php
                    $itemsWithDiscrepancies = collect($inventoryData)->where('discrepancy', '!=', 0);
                @endphp

                @if ($itemsWithDiscrepancies->count() > 0)
                    <div class="card mt-4">
                        <div class="card-header">
                            <h6 class="card-title mb-0">
                                <i class="ri-alert-line text-warning"></i>
                                الأصناف التي تحتاج تسوية ({{ $itemsWithDiscrepancies->count() }} صنف)
                            </h6>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-sm">
                                    <thead>
                                        <tr class="text-center">
                                            <th>الصنف</th>
                                            <th>الكمية الدفترية</th>
                                            <th>الكمية الفعلية</th>
                                            <th>الفرق</th>
                                            <th>القيمة</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($itemsWithDiscrepancies as $item)
                                            <tr class="text-center">
                                                {{-- CORRECTED --}}
                                                <td>{{ $item['item_name'] }}</td>
                                                <td>{{ number_format($item['system_quantity']) }}</td>
                                                <td>{{ number_format($item['actual_quantity']) }}</td>
                                                <td
                                                    class="{{ $item['discrepancy'] > 0 ? 'text-success' : 'text-danger' }} fw-bold">
                                                    {{ $item['discrepancy'] > 0 ? '+' : '' }}{{ number_format($item['discrepancy']) }}
                                                </td>
                                                <td
                                                    class="{{ $item['discrepancy_value'] > 0 ? 'text-success' : 'text-danger' }} fw-bold">
                                                    {{ $item['discrepancy_value'] > 0 ? '+' : '' }}{{ number_format($item['discrepancy_value']) }}
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                @endif

            @endif
        </div>
    </div>
</div>

@push('scripts')
    <script>
        document.addEventListener('livewire:initialized', () => {
            Livewire.on('show-alert', (event) => {
                const eventData = event[0];
                const alertContainer = document.getElementById('alertContainer');
                const alertClass = eventData.type === 'success' ? 'alert-success' :
                    eventData.type === 'error' ? 'alert-danger' :
                    eventData.type === 'info' ? 'alert-info' : 'alert-warning';

                alertContainer.innerHTML = `
                        <div class="alert ${alertClass} alert-dismissible fade show" role="alert">
                            <i class="ri-${eventData.type === 'success' ? 'check' : eventData.type === 'error' ? 'close' : 'information'}-line"></i>
                            ${eventData.message}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    `;

                // إخفاء التنبيه تلقائياً بعد 5 ثوان
                setTimeout(() => {
                    const alert = alertContainer.querySelector('.alert');
                    if (alert) {
                        alert.remove();
                    }
                }, 5000);
            });

            // تحسين تجربة المستخدم عند إدخال الكميات
            document.addEventListener('input', function(e) {
                if (e.target.type === 'number' && e.target.getAttribute('wire:model.live.debounce.500ms')) {
                    e.target.style.backgroundColor = '#fff3cd'; // تمييز الحقول المعدلة
                    setTimeout(() => {
                        e.target.style.backgroundColor = '';
                    }, 1000);
                }
            });
        });

        // دالة لطباعة تقرير مخصص
        function printInventoryReport() {
            const printContent = `
                    <style>
                        body { font-family: Arial, sans-serif; direction: rtl; }
                        table { width: 100%; border-collapse: collapse; margin: 20px 0; }
                        th, td { border: 1px solid #ddd; padding: 8px; text-align: center; }
                        th { background-color: #f5f5f5; }
                        .header { text-align: center; margin-bottom: 30px; }
                        .summary { display: flex; justify-content: space-around; margin: 20px 0; }
                        .summary-item { text-align: center; }
                        @media print { .no-print { display: none; } }
                    </style>
                    <div class="header">
                        <h2>تقرير جرد المخزون</h2>
                        <p>التاريخ: ${new Date().toLocaleDateString('ar-EG')}</p>
                        <p>المخزن: ${document.querySelector('[wire\\:model\\.live="selectedWarehouse"] option:checked')?.textContent || 'غير محدد'}</p>
                    </div>
                    ${document.querySelector('.table-responsive').innerHTML}
                `;

            const printWindow = window.open('', '_blank');
            printWindow.document.write(printContent);
            printWindow.document.close();
            printWindow.print();
        }
    </script>
@endpush

@push('styles')
    <style>
        .loading-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(255, 255, 255, .8);
            display: flex;
            justify-content: center;
            align-items: center;
            z-index: 9999;
        }

        .table th,
        .table td {
            vertical-align: middle;
        }

        .card {
            box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
            border: 1px solid rgba(0, 0, 0, 0.125);
            transition: all 0.3s ease;
        }

        .card:hover {
            box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
        }

        /* تحسين مظهر الجدول */
        .table-hover tbody tr:hover {
            background-color: rgba(0, 123, 255, 0.1);
        }

        /* تحسين حقول الإدخال */
        input[type="number"]:focus {
            border-color: #80bdff;
            box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25);
        }

        /* تمييز الصفوف بناءً على نوع الفرق */
        .table-danger {
            background-color: rgba(220, 53, 69, 0.1) !important;
        }

        .table-info {
            background-color: rgba(13, 202, 240, 0.1) !important;
        }

        /* تحسين البطاقات الإحصائية */
        .card .card-body i {
            opacity: 0.8;
        }

        /* تحسين التنبيهات */
        .alert {
            border-left: 4px solid;
            border-radius: 0.375rem;
        }

        .alert-warning {
            border-left-color: #ffc107;
        }

        .alert-success {
            border-left-color: #198754;
        }

        .alert-danger {
            border-left-color: #dc3545;
        }

        .alert-info {
            border-left-color: #0dcaf0;
        }

        @media print {

            .btn,
            .loading-overlay,
            #alertContainer,
            .no-print {
                display: none !important;
            }

            .card {
                border: none !important;
                box-shadow: none !important;
            }

            .table {
                font-size: 12px;
            }
        }

        /* تحسينات للجوال */
        @media (max-width: 768px) {
            .table-responsive {
                font-size: 12px;
            }

            .card-body {
                padding: 1rem 0.5rem;
            }

            .col-md-3 {
                margin-bottom: 1rem;
            }
        }
    </style>
@endpush
</div>
