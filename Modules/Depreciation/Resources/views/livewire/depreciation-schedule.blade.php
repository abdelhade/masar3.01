<div>
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <h2 class="text-primary">
                    <i class="fas fa-calendar-alt me-2"></i>
                    {{ __('جدولة إهلاك الأصول') }}
                </h2>
                <div class="text-muted">
                    <i class="fas fa-info-circle me-2"></i>
                    {{ __('عرض جدولة الإهلاك المتوقعة للأصول') }}
                </div>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="card mb-4">
        <div class="card-body">
            <div class="row">
                <div class="col-md-3">
                    <label class="form-label">{{ __('البحث') }}</label>
                    <input wire:model.live.debounce.300ms="search" type="text" class="form-control" 
                           placeholder="{{ __('البحث باسم الأصل...') }}">
                </div>
                <div class="col-md-3">
                    <label class="form-label">{{ __('الفرع') }}</label>
                    <select wire:model.live="selectedBranch" class="form-select">
                        <option value="">{{ __('جميع الفروع') }}</option>
                        @foreach($branches as $branch)
                            <option value="{{ $branch->id }}">{{ $branch->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">{{ __('حالة الإهلاك') }}</label>
                    <select wire:model.live="filterStatus" class="form-select">
                        <option value="">{{ __('جميع الحالات') }}</option>
                        <option value="not_depreciated">{{ __('غير مهلك') }}</option>
                        <option value="partially_depreciated">{{ __('مهلك جزئياً') }}</option>
                        <option value="fully_depreciated">{{ __('مهلك بالكامل') }}</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">{{ __('الإجراءات') }}</label>
                    <div class="d-flex gap-2">
                        <button wire:click="$set('showBulkActions', !$showBulkActions)" 
                                class="btn btn-outline-primary btn-sm">
                            <i class="fas fa-tasks me-1"></i>
                            {{ __('إجراءات مجمعة') }}
                        </button>
                    </div>
                </div>
            </div>
            
            <!-- Bulk Actions -->
            @if($showBulkActions && !empty($selectedAssets))
                <div class="row mt-3">
                    <div class="col-12">
                        <div class="d-flex justify-content-between align-items-center p-3 bg-light rounded">
                            <span>{{ __('تم اختيار') }} {{ count($selectedAssets) }} {{ __('أصل') }}</span>
                            <div class="d-flex gap-2">
                                <select wire:model="bulkAction" class="form-select form-select-sm" style="width: auto;">
                                    <option value="">{{ __('اختر الإجراء') }}</option>
                                    <option value="calculate">{{ __('حساب الإهلاك الشهري') }}</option>
                                    <option value="reset">{{ __('إعادة تعيين الإهلاك') }}</option>
                                </select>
                                <button wire:click="bulkProcess" class="btn btn-primary btn-sm">
                                    <i class="fas fa-play me-1"></i>
                                    {{ __('تنفيذ') }}
                                </button>
                                <button wire:click="$set('selectedAssets', []); $set('selectAll', false); $set('showBulkActions', false)" 
                                        class="btn btn-outline-secondary btn-sm">
                                    {{ __('إلغاء') }}
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>

    <!-- Assets Table -->
    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-striped table-hover">
                    <thead class="table-primary">
                        <tr>
                            <th width="50">
                                <input type="checkbox" wire:model="selectAll" class="form-check-input">
                            </th>
                            <th>{{ __('اسم الأصل') }}</th>
                            <th>{{ __('الفرع') }}</th>
                            <th>{{ __('طريقة الإهلاك') }}</th>
                            <th>{{ __('العمر الإنتاجي') }}</th>
                            <th>{{ __('تكلفة الشراء') }}</th>
                            <th>{{ __('الإهلاك المتراكم') }}</th>
                            <th>{{ __('القيمة الدفترية') }}</th>
                            <th>{{ __('حالة الإهلاك') }}</th>
                            <th>{{ __('الإجراءات') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($assets as $asset)
                            <tr>
                                <td>
                                    <input type="checkbox" wire:model="selectedAssets" value="{{ $asset->id }}" class="form-check-input">
                                </td>
                                <td>
                                    <div class="d-flex flex-column">
                                        <strong>{{ $asset->asset_name ?: $asset->accHead->aname }}</strong>
                                        <small class="text-muted">{{ $asset->accHead->code }}</small>
                                    </div>
                                </td>
                                <td>{{ $asset->accHead->branch->name ?? '-' }}</td>
                                <td>
                                    @switch($asset->depreciation_method)
                                        @case('straight_line')
                                            <span class="badge bg-info">{{ __('القسط الثابت') }}</span>
                                            @break
                                        @case('double_declining')
                                            <span class="badge bg-warning">{{ __('الرصيد المتناقص المضاعف') }}</span>
                                            @break
                                        @case('sum_of_years')
                                            <span class="badge bg-success">{{ __('مجموع السنوات') }}</span>
                                            @break
                                        @default
                                            <span class="badge bg-secondary">{{ __('غير محدد') }}</span>
                                    @endswitch
                                </td>
                                <td>{{ $asset->useful_life_years }} {{ __('سنوات') }}</td>
                                <td>{{ number_format($asset->purchase_cost, 2) }}</td>
                                <td>
                                    <div class="d-flex flex-column">
                                        <strong class="text-warning">{{ number_format($asset->accumulated_depreciation, 2) }}</strong>
                                        <small class="text-muted">{{ number_format($asset->getDepreciationPercentage(), 1) }}%</small>
                                    </div>
                                </td>
                                <td>
                                    <strong class="text-primary">{{ number_format($asset->getNetBookValue(), 2) }}</strong>
                                </td>
                                <td>
                                    @if($asset->isFullyDepreciated())
                                        <span class="badge bg-success">{{ __('مهلك بالكامل') }}</span>
                                    @elseif($asset->accumulated_depreciation > 0)
                                        <span class="badge bg-warning">{{ __('مهلك جزئياً') }}</span>
                                    @else
                                        <span class="badge bg-secondary">{{ __('غير مهلك') }}</span>
                                    @endif
                                </td>
                                <td>
                                    <div class="btn-group" dir="ltr">
                                        <button wire:click="viewSchedule({{ $asset->id }})" 
                                                class="btn btn-sm btn-outline-primary" 
                                                title="{{ __('عرض الجدولة') }}">
                                            <i class="fas fa-calendar-alt"></i>
                                        </button>
                                        <button wire:click="generateScheduleForAsset({{ $asset->id }})" 
                                                class="btn btn-sm btn-outline-info" 
                                                title="{{ __('إنشاء جدولة') }}">
                                            <i class="fas fa-calculator"></i>
                                        </button>
                                        <button wire:click="exportSchedule({{ $asset->id }})" 
                                                class="btn btn-sm btn-outline-success" 
                                                title="{{ __('تصدير') }}">
                                            <i class="fas fa-download"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="10" class="text-center py-4">
                                    <div class="text-muted">
                                        <i class="fas fa-calendar fa-3x mb-3"></i>
                                        <p>{{ __('لا توجد أصول متاحة للجدولة') }}</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div class="d-flex justify-content-center mt-3">
                {{ $assets->links() }}
            </div>
        </div>
    </div>

    <!-- Schedule Modal -->
    @if($showModal && $selectedAsset)
        <div class="modal fade show d-block" tabindex="-1" style="background-color: rgba(0,0,0,0.5);">
            <div class="modal-dialog modal-xl">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">
                            {{ __('جدولة إهلاك الأصل') }}: {{ $selectedAsset->asset_name ?: $selectedAsset->accHead->aname }}
                        </h5>
                        <button type="button" class="btn-close" wire:click="closeModal"></button>
                    </div>
                    <div class="modal-body">
                        <!-- Asset Summary -->
                        <div class="row mb-4">
                            <div class="col-md-3">
                                <div class="card bg-primary text-white">
                                    <div class="card-body">
                                        <h6>{{ __('تكلفة الشراء') }}</h6>
                                        <h4>{{ number_format($selectedAsset->purchase_cost, 2) }}</h4>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="card bg-success text-white">
                                    <div class="card-body">
                                        <h6>{{ __('قيمة الخردة') }}</h6>
                                        <h4>{{ number_format($selectedAsset->salvage_value ?? 0, 2) }}</h4>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="card bg-warning text-white">
                                    <div class="card-body">
                                        <h6>{{ __('المبلغ القابل للإهلاك') }}</h6>
                                        <h4>{{ number_format($selectedAsset->purchase_cost - ($selectedAsset->salvage_value ?? 0), 2) }}</h4>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="card bg-info text-white">
                                    <div class="card-body">
                                        <h6>{{ __('العمر الإنتاجي') }}</h6>
                                        <h4>{{ $selectedAsset->useful_life_years }} {{ __('سنوات') }}</h4>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Schedule Table -->
                        <div class="table-responsive">
                            <table class="table table-striped table-hover table-sm">
                                <thead class="table-dark">
                                    <tr>
                                        <th>{{ __('السنة') }}</th>
                                        <th>{{ __('من تاريخ') }}</th>
                                        <th>{{ __('إلى تاريخ') }}</th>
                                        <th>{{ __('القيمة الدفترية في البداية') }}</th>
                                        <th>{{ __('إهلاك السنة') }}</th>
                                        <th>{{ __('الإهلاك المتراكم') }}</th>
                                        <th>{{ __('القيمة الدفترية في النهاية') }}</th>
                                        <th>{{ __('النسبة %') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($scheduleData as $row)
                                        <tr>
                                            <td><strong>{{ $row['year'] }}</strong></td>
                                            <td>{{ $row['start_date'] }}</td>
                                            <td>{{ $row['end_date'] }}</td>
                                            <td>{{ number_format($row['beginning_book_value'], 2) }}</td>
                                            <td class="text-danger">{{ number_format($row['annual_depreciation'], 2) }}</td>
                                            <td class="text-warning">{{ number_format($row['accumulated_depreciation'], 2) }}</td>
                                            <td class="text-success">{{ number_format($row['ending_book_value'], 2) }}</td>
                                            <td>
                                                <div class="progress" style="height: 15px; min-width: 60px;">
                                                    <div class="progress-bar" style="width: {{ $row['percentage'] }}%">
                                                        {{ number_format($row['percentage'], 1) }}%
                                                    </div>
                                                </div>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="8" class="text-center text-muted">
                                                {{ __('لا يمكن حساب الجدولة - تأكد من إدخال البيانات المطلوبة') }}
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                                @if(!empty($scheduleData))
                                    <tfoot class="table-secondary">
                                        <tr>
                                            <th colspan="4">{{ __('المجموع') }}</th>
                                            <th>{{ number_format(collect($scheduleData)->sum('annual_depreciation'), 2) }}</th>
                                            <th>{{ number_format(collect($scheduleData)->last()['accumulated_depreciation'] ?? 0, 2) }}</th>
                                            <th>{{ number_format(collect($scheduleData)->last()['ending_book_value'] ?? 0, 2) }}</th>
                                            <th>100%</th>
                                        </tr>
                                    </tfoot>
                                @endif
                            </table>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" wire:click="closeModal">
                            {{ __('إغلاق') }}
                        </button>
                        <button type="button" class="btn btn-success" wire:click="exportSchedule({{ $selectedAsset->id }})">
                            <i class="fas fa-download me-2"></i>
                            {{ __('تصدير إلى CSV') }}
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>

@push('styles')
<style>
    .progress {
        background-color: #e9ecef;
    }
    
    .progress-bar {
        background-color: #007bff;
        color: white;
        font-size: 11px;
        line-height: 15px;
    }
    
    .modal-xl {
        max-width: 95%;
    }
    
    .table th, .table td {
        vertical-align: middle;
    }
    
    .card {
        border: none;
        box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
    }
    
    .badge {
        font-size: 0.75em;
    }
    
    .btn-group .btn {
        margin-right: 2px;
    }
    
    .form-check-input:checked {
        background-color: #007bff;
        border-color: #007bff;
    }
</style>
@endpush

@push('scripts')
<script>
    document.addEventListener('livewire:initialized', () => {
        Livewire.on('alert', (event) => {
            if (event.type === 'success') {
                // You can replace this with your preferred notification system
                alert(event.message);
            } else if (event.type === 'error') {
                alert('خطأ: ' + event.message);
            } else if (event.type === 'warning') {
                alert('تحذير: ' + event.message);
            }
        });
    });
</script>
@endpush