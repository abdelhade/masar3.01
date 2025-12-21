<div>
<div>
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <h2 class="text-primary">
                    <i class="fas fa-calculator me-2"></i>
                    {{ __('إدارة إهلاك الأصول') }}
                </h2>
                <div class="d-flex align-items-center gap-3">
                    <a href="{{ route('depreciation.schedule') }}" class="btn btn-primary">
                        <i class="fas fa-calendar-alt me-2"></i>
                        {{ __('جدولة الإهلاك') }}
                    </a>
                    <div class="text-muted">
                        <i class="fas fa-info-circle me-2"></i>
                        {{ __('اختر حساب أصل من القائمة لتطبيق الإهلاك عليه') }}
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="card mb-4">
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <label class="form-label">{{ __('البحث') }}</label>
                    <input wire:model.live.debounce.300ms="search" type="text" class="form-control" 
                           placeholder="{{ __('البحث باسم الحساب...') }}">
                </div>
                <div class="col-md-6">
                    <label class="form-label">{{ __('الفرع') }}</label>
                    <select wire:model.live="filterBranch" class="form-select">
                        <option value="">{{ __('جميع الفروع') }}</option>
                        @foreach($branches as $branch)
                            <option value="{{ $branch->id }}">{{ $branch->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            
            <!-- Bulk Actions -->
            @if(!empty($selectedAssets))
                <div class="row mt-3">
                    <div class="col-12">
                        <div class="d-flex justify-content-between align-items-center p-3 bg-light rounded">
                            <span>{{ __('تم اختيار') }} {{ count($selectedAssets) }} {{ __('أصل') }}</span>
                            <div>
                                <button wire:click="bulkDepreciation" class="btn btn-primary btn-sm me-2">
                                    <i class="fas fa-calculator me-1"></i>
                                    {{ __('معالجة مجمعة') }}
                                </button>
                                <button wire:click="$set('selectedAssets', []); $set('selectAll', false)" class="btn btn-outline-secondary btn-sm">
                                    {{ __('إلغاء التحديد') }}
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>

    <!-- Table -->
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
                            <th>{{ __('حساب الأصل') }}</th>
                            <th>{{ __('تاريخ الشراء') }}</th>
                            <th>{{ __('التكلفة') }}</th>
                            <th>{{ __('الإهلاك السنوي') }}</th>
                            <th>{{ __('الإهلاك المتراكم') }}</th>
                            <th>{{ __('القيمة الدفترية') }}</th>
                            <th>{{ __('الحالة') }}</th>
                            <th>{{ __('الإجراءات') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($accountAssets as $asset)
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
                                <td>
                                    <span class="badge bg-info">{{ $asset->accHead->aname }}</span>
                                </td>
                                <td>
                                    {{ $asset->purchase_date ? $asset->purchase_date->format('Y-m-d') : '-' }}
                                </td>
                                <td>{{ number_format($asset->purchase_cost, 2) }}</td>
                                <td>{{ number_format($asset->annual_depreciation, 2) }}</td>
                                <td>{{ number_format($asset->accumulated_depreciation, 2) }}</td>
                                <td>
                                    <strong class="text-primary">{{ number_format($asset->getNetBookValue(), 2) }}</strong>
                                </td>
                                <td>
                                    @if($asset->is_active)
                                        <span class="badge bg-success">{{ __('نشط') }}</span>
                                    @else
                                        <span class="badge bg-secondary">{{ __('غير نشط') }}</span>
                                    @endif
                                </td>
                                <td>
                                    <div class="btn-group" dir="ltr">
                                        <button wire:click="editAsset({{ $asset->id }})" 
                                                class="btn btn-sm btn-outline-primary" 
                                                title="{{ __('تعديل') }}">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <button wire:click="selectAccountForDepreciation({{ $asset->accHead->id }})" 
                                                class="btn btn-sm btn-outline-success" 
                                                title="{{ __('إهلاك إضافي') }}">
                                            <i class="fas fa-calculator"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="10" class="text-center py-4">
                                    <div class="text-muted">
                                        <i class="fas fa-building fa-3x mb-3"></i>
                                        <p>{{ __('لا توجد أصول مسجلة') }}</p>
                                        @if($assetAccounts->count() > 0)
                                            <p class="small">{{ __('يمكنك إضافة أصل جديد من الحسابات أدناه') }}</p>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Available Asset Accounts Section -->
            @if($assetAccounts->count() > 0)
                <div class="mt-4">
                    <h5 class="text-muted mb-3">
                        <i class="fas fa-plus-circle me-2"></i>
                        {{ __('حسابات أصول متاحة للإضافة') }}
                    </h5>
                    <div class="row">
                        @foreach($assetAccounts as $account)
                            <div class="col-md-4 mb-2">
                                <div class="card border-dashed border-primary">
                                    <div class="card-body p-3">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <div>
                                                <strong>{{ $account->aname }}</strong>
                                                <br><small class="text-muted">{{ $account->code }}</small>
                                            </div>
                                            <button wire:click="createAssetRecord({{ $account->id }})" 
                                                    class="btn btn-sm btn-outline-primary">
                                                <i class="fas fa-plus"></i> إضافة
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            <!-- Pagination -->
            <div class="d-flex justify-content-center mt-3">
                {{ $accountAssets->links() }}
            </div>
        </div>
    </div>

    <!-- Modal -->
    @if($showModal)
        <div class="modal fade show d-block" tabindex="-1" style="background-color: rgba(0,0,0,0.5);">
            <div class="modal-dialog modal-lg">
                <div class="modal-content position-relative">
                    <!-- Loading Overlay during save (hidden by default to avoid flash) -->
                    <div wire:loading.delay wire:target="processDepreciation"
                         wire:loading.class.remove="d-none" wire:loading.class="d-flex"
                         class="position-absolute top-0 start-0 w-100 h-100 d-none align-items-center justify-content-center"
                         style="background: rgba(255,255,255,0.6); z-index: 10;">
                        <div class="d-flex align-items-center">
                            <div class="spinner-border text-primary me-2" role="status" aria-hidden="true"></div>
                            <strong>{{ __('جاري الحفظ...') }}</strong>
                        </div>
                    </div>
                    <div class="modal-header">
                        <h5 class="modal-title">
                            {{ __('إهلاك الأصل') }}: {{ $selectedAccount?->aname }}
                        </h5>
                        <button type="button" class="btn-close" wire:click="closeModal"></button>
                    </div>
                    <div class="modal-body">
                        @if ($errors->any())
                            <div class="alert alert-danger">
                                <div class="d-flex align-items-center mb-2">
                                    <i class="fas fa-exclamation-triangle me-2"></i>
                                    <strong>{{ __('الرجاء تصحيح الأخطاء التالية:') }}</strong>
                                </div>
                                <ul class="mb-0">
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif
                        @error('general')
                            <div class="alert alert-danger">
                                <i class="fas fa-times-circle me-2"></i>
                                {{ $message }}
                            </div>
                        @enderror
                        <form wire:submit.prevent="processDepreciation">
                            <div class="mb-4">
                                <h6 class="text-primary border-bottom pb-2">{{ __('بيانات الإهلاك') }}</h6>
                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="mb-3">
                                            <label class="form-label">{{ __('تاريخ الشراء') }}</label>
                                            <input wire:model="purchase_date" type="date" 
                                                   class="form-control @error('purchase_date') is-invalid @enderror">
                                            @error('purchase_date')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="mb-3">
                                            <label class="form-label">{{ __('تاريخ أول إهلاك') }}</label>
                                            <input wire:model="depreciation_date" type="date" 
                                                   class="form-control @error('depreciation_date') is-invalid @enderror">
                                            @error('depreciation_date')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                    </div>
                                </div>
                                    <div class="col-md-4">
                                        <div class="mb-3">
                                            <label class="form-label">{{ __('تكلفة الشراء (من حساب الأصل)') }}</label>
                                            <input wire:model="purchase_cost" type="number" step="0.01" 
                                                   class="form-control @error('purchase_cost') is-invalid @enderror" readonly
                                                   placeholder="{{ __('تُجلب تلقائياً من رصيد الحساب') }}">
                                            @error('purchase_cost')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                            <small class="text-muted">{{ __('القيمة مأخوذة من رصيد حساب الأصل الحالي') }}</small>
                                        </div>
                                        </div>
                                    </div>

                                <div class="row">
                                    <div class="col-md-3">
                                        <div class="mb-3">
                                            <label class="form-label">{{ __('قيمة الخردة') }}</label>
                                            <input wire:model="salvage_value" type="number" step="0.01" 
                                                   class="form-control @error('salvage_value') is-invalid @enderror">
                                            @error('salvage_value')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                    <div class="mb-3">
                                            <label class="form-label">{{ __('القيمة القابلة للإهلاك') }}</label>
                                            <input type="text" class="form-control" value="{{ number_format(max(($purchase_cost ?: 0) - ($salvage_value ?: 0), 0), 2) }}" readonly>
                                    </div>
                                </div>
                                    <div class="col-md-3">
                                    <div class="mb-3">
                                        <label class="form-label">{{ __('طريقة الإهلاك') }}</label>
                                        <select wire:model="depreciation_method" class="form-select @error('depreciation_method') is-invalid @enderror">
                                            <option value="straight_line">{{ __('القسط الثابت') }}</option>
                                                <option value="declining_balance">{{ __('الرصيد المتناقص') }}</option>
                                            <option value="double_declining">{{ __('الرصيد المتناقص المضاعف') }}</option>
                                            <option value="sum_of_years">{{ __('مجموع السنوات') }}</option>
                                        </select>
                                        @error('depreciation_method')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                </div>
                                </div>
                            </div>

                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="mb-3">
                                            <label class="form-label">{{ __('عدد سنوات الإهلاك') }}</label>
                                            <input wire:model="useful_life_years" type="number" min="1" 
                                                   class="form-control @error('useful_life_years') is-invalid @enderror">
                                            @error('useful_life_years')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="mb-3">
                                <h6 class="text-primary border-bottom pb-2">{{ __('جدول الإهلاك المتوقع') }}</h6>
                                <div class="table-responsive">
                                    <table class="table table-striped table-hover table-sm">
                                        <thead class="table-secondary">
                                            <tr>
                                                <th>{{ __('السنة') }}</th>
                                                <th>{{ __('من تاريخ') }}</th>
                                                <th>{{ __('إلى تاريخ') }}</th>
                                                <th>{{ __('القيمة الدفترية في البداية') }}</th>
                                                <th>{{ __('إهلاك السنة') }}</th>
                                                <th>{{ __('الإهلاك المتراكم') }}</th>
                                                <th>{{ __('القيمة الدفترية في النهاية') }}</th>
                                                <th>{{ __('معادلة الإهلاك') }}</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @forelse($schedulePreview as $row)
                                                <tr>
                                                    <td><strong>{{ $row['year'] }}</strong></td>
                                                    <td>{{ $row['start_date'] }}</td>
                                                    <td>{{ $row['end_date'] }}</td>
                                                    <td>{{ number_format($row['beginning_book_value'], 2) }}</td>
                                                    <td class="text-danger">{{ number_format($row['annual_depreciation'], 2) }}</td>
                                                    <td class="text-warning">{{ number_format($row['accumulated_depreciation'], 2) }}</td>
                                                    <td class="text-success">{{ number_format($row['ending_book_value'], 2) }}</td>
                                                    <td>
                                                        @switch($depreciation_method)
                                                            @case('straight_line')
                                                                {{ __('القيمة القابلة للإهلاك') }} / {{ $useful_life_years ?: '-' }}
                                                                @break
                                                            @case('declining_balance')
                                                                {{ __('القيمة الدفترية') }} × (1/{{ $useful_life_years ?: '-' }})
                                                                @break
                                                            @case('double_declining')
                                                                {{ __('القيمة الدفترية') }} × (2/{{ $useful_life_years ?: '-' }})
                                                                @break
                                                            @case('sum_of_years')
                                                                {{ __('القيمة القابلة للإهلاك') }} × {{ __('السنوات المتبقية') }} / {{ __('مجموع السنوات') }}
                                                                @break
                                                            @default
                                                                -
                                                        @endswitch
                                                    </td>
                                        </tr>
                                            @empty
                                        <tr>
                                                    <td colspan="8" class="text-center text-muted">{{ __('لا توجد بيانات كافية لحساب الجدولة') }}</td>
                                        </tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>
                            </div>

                            <div class="alert alert-secondary">
                                <i class="fas fa-info-circle me-2"></i>
                                {{ __('هذه مرحلة جدولة فقط - لا يتم إنشاء قيود من هذه الشاشة.') }}
                                </div>
                        </form>
                    </div>
                    <div class="modal-footer">
                        <a href="{{ route('depreciation.schedule') }}" class="btn btn-outline-primary">
                            <i class="fas fa-list me-2"></i>
                            {{ __('الانتقال إلى شاشة الجدولة') }}
                        </a>
                        <button type="button" class="btn btn-outline-secondary" wire:click="generatePreview" wire:loading.attr="disabled" wire:target="generatePreview">
                            <span wire:loading.delay.inline wire:target="generatePreview" class="me-2">
                                <span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
                            </span>
                            <i class="fas fa-sync-alt me-2"></i>
                            {{ __('تحديث الجدولة') }}
                        </button>
                        <button type="button" class="btn btn-secondary" wire:click="closeModal" wire:loading.attr="disabled" wire:target="processDepreciation">
                            {{ __('إلغاء') }}
                        </button>
                        <button type="button" class="btn btn-success" wire:click="processDepreciation" wire:loading.attr="disabled" wire:target="processDepreciation">
                            <span wire:loading.delay.inline wire:target="processDepreciation" class="me-2">
                                <span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
                            </span>
                            <i class="fas fa-save me-2"></i>
                            {{ __('حفظ') }}
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>

@push('styles')
<style>
    /* Dark Brown Text Color - Apply to all text */
    div,
    h1, h2, h3, h4, h5, h6,
    p, span, a, label, small, strong,
    th, td, li,
    .text-primary, .text-success, .text-warning, .text-info, 
    .text-danger, .text-muted, .text-secondary, .text-dark, .text-white,
    .card-body, .card-header, .card-title, .modal-title, .modal-body,
    .form-label, .btn, .badge,
    input, select, textarea {
        color: #5D4037 !important; /* Dark brown color */
    }
    
    /* Keep button text readable but maintain dark brown */
    .btn.btn-primary, .btn.btn-success, .btn.btn-warning, 
    .btn.btn-info, .btn.btn-danger, .btn.btn-secondary {
        color: #5D4037 !important;
    }
    
    /* Keep badges readable */
    .badge {
        color: #5D4037 !important;
    }
    
    /* Links should also be dark brown */
    a {
        color: #5D4037 !important;
    }
    
    a:hover {
        color: #3E2723 !important; /* Darker brown on hover */
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
            }
        });
    });
</script>
@endpush