<!-- Header -->
<div class="row mb-4">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center">
            <h2 class="text-primary">
                <i class="fas fa-calculator me-2"></i>
                {{ __('إدارة إهلاك الأصول') }}
            </h2>
            <button wire:click="create" class="btn btn-primary">
                <i class="fas fa-plus me-2"></i>
                {{ __('إضافة أصل جديد') }}
            </button>
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
                           placeholder="{{ __('البحث بالاسم...') }}">
                </div>
                <div class="col-md-3">
                    <label class="form-label">{{ __('الفرع') }}</label>
                    <select wire:model.live="filterBranch" class="form-select">
                        <option value="">{{ __('جميع الفروع') }}</option>
                        @foreach($branches as $branch)
                            <option value="{{ $branch->id }}">{{ $branch->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">{{ __('طريقة الإهلاك') }}</label>
                    <select wire:model.live="filterMethod" class="form-select">
                        <option value="">{{ __('جميع الطرق') }}</option>
                        <option value="straight_line">{{ __('القسط الثابت') }}</option>
                        <option value="double_declining">{{ __('الرصيد المتناقص المضاعف') }}</option>
                        <option value="sum_of_years">{{ __('مجموع السنوات') }}</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">{{ __('الحالة') }}</label>
                    <select wire:model.live="filterStatus" class="form-select">
                        <option value="">{{ __('جميع الحالات') }}</option>
                        <option value="1">{{ __('نشط') }}</option>
                        <option value="0">{{ __('غير نشط') }}</option>
                    </select>
                </div>
            </div>
        </div>
    </div>

    <!-- Table -->
    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-striped table-hover">
                    <thead class="table-primary">
                        <tr>
                            <th>{{ __('الاسم') }}</th>
                            <th>{{ __('تاريخ الشراء') }}</th>
                            <th>{{ __('التكلفة') }}</th>
                            <th>{{ __('العمر الإنتاجي') }}</th>
                            <th>{{ __('الإهلاك السنوي') }}</th>
                            <th>{{ __('الإهلاك المتراكم') }}</th>
                            <th>{{ __('القيمة الدفترية') }}</th>
                            <th>{{ __('نسبة الإهلاك') }}</th>
                            <th>{{ __('الحالة') }}</th>
                            <th>{{ __('الإجراءات') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($depreciationItems as $item)
                            <tr>
                                <td>
                                    <div class="d-flex flex-column">
                                        <strong>{{ $item->name }}</strong>
                                        @if($item->assetAccount)
                                            <small class="text-muted">
                                                {{ $item->assetAccount->code }} - {{ $item->assetAccount->aname }}
                                            </small>
                                        @endif
                                    </div>
                                </td>
                                <td>{{ $item->purchase_date->format('Y-m-d') }}</td>
                                <td>{{ number_format($item->cost, 2) }}</td>
                                <td>{{ $item->useful_life }} {{ __('سنوات') }}</td>
                                <td>{{ number_format($item->annual_depreciation, 2) }}</td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <span>{{ number_format($item->accumulated_depreciation, 2) }}</span>
                                        <button wire:click="calculateDepreciation({{ $item->id }})" 
                                                class="btn btn-sm btn-outline-info ms-2" 
                                                title="{{ __('إعادة حساب') }}">
                                            <i class="fas fa-sync-alt"></i>
                                        </button>
                                    </div>
                                </td>
                                <td>{{ number_format($item->getNetBookValue(), 2) }}</td>
                                <td>
                                    <div class="progress" style="height: 20px;">
                                        <div class="progress-bar" 
                                             style="width: {{ $item->getDepreciationPercentage() }}%"
                                             role="progressbar">
                                            {{ number_format($item->getDepreciationPercentage(), 1) }}%
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    @if($item->is_active)
                                        <span class="badge bg-success">{{ __('نشط') }}</span>
                                    @else
                                        <span class="badge bg-secondary">{{ __('غير نشط') }}</span>
                                    @endif
                                </td>
                                <td>
                                    <div class="btn-group" dir="ltr">
                                        <button wire:click="edit({{ $item->id }})" 
                                                class="btn btn-sm btn-outline-primary" 
                                                title="{{ __('تعديل') }}">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <button wire:click="delete({{ $item->id }})" 
                                                class="btn btn-sm btn-outline-danger"
                                                onclick="return confirm('{{ __('هل أنت متأكد من الحذف؟') }}')"
                                                title="{{ __('حذف') }}">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="10" class="text-center py-4">
                                    <div class="text-muted">
                                        <i class="fas fa-calculator fa-3x mb-3"></i>
                                        <p>{{ __('لا توجد أصول للإهلاك') }}</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div class="d-flex justify-content-center mt-3">
                {{ $depreciationItems->links() }}
            </div>
        </div>
    </div>

    <!-- Modal -->
    @if($showModal)
        <div class="modal fade show d-block" tabindex="-1" style="background-color: rgba(0,0,0,0.5);">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">
                            @if($editMode)
                                {{ __('تعديل بيانات الإهلاك') }}
                            @else
                                {{ __('إضافة أصل جديد للإهلاك') }}
                            @endif
                        </h5>
                        <button type="button" class="btn-close" wire:click="closeModal"></button>
                    </div>
                    <div class="modal-body">
                        <form wire:submit.prevent="save">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label">{{ __('اسم الأصل') }} *</label>
                                        <input wire:model="name" type="text" class="form-control @error('name') is-invalid @enderror">
                                        @error('name')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label">{{ __('تاريخ الشراء') }} *</label>
                                        <input wire:model="purchase_date" type="date" class="form-control @error('purchase_date') is-invalid @enderror">
                                        @error('purchase_date')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label">{{ __('التكلفة') }} *</label>
                                        <input wire:model="cost" type="number" step="0.01" class="form-control @error('cost') is-invalid @enderror">
                                        @error('cost')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label">{{ __('قيمة الخردة') }}</label>
                                        <input wire:model="salvage_value" type="number" step="0.01" class="form-control @error('salvage_value') is-invalid @enderror">
                                        @error('salvage_value')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label">{{ __('العمر الإنتاجي (بالسنوات)') }} *</label>
                                        <input wire:model="useful_life" type="number" min="1" class="form-control @error('useful_life') is-invalid @enderror">
                                        @error('useful_life')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label">{{ __('طريقة الإهلاك') }} *</label>
                                        <select wire:model="depreciation_method" class="form-select @error('depreciation_method') is-invalid @enderror">
                                            <option value="straight_line">{{ __('القسط الثابت') }}</option>
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
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label">{{ __('حساب الأصل') }}</label>
                                        <select wire:model="asset_account_id" class="form-select @error('asset_account_id') is-invalid @enderror">
                                            <option value="">{{ __('اختر حساب الأصل') }}</option>
                                            @foreach($assetAccounts as $account)
                                                <option value="{{ $account->id }}">{{ $account->code }} - {{ $account->aname }}</option>
                                            @endforeach
                                        </select>
                                        @error('asset_account_id')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label">{{ __('الفرع') }} *</label>
                                        <select wire:model="branch_id" class="form-select @error('branch_id') is-invalid @enderror">
                                            <option value="">{{ __('اختر الفرع') }}</option>
                                            @foreach($branches as $branch)
                                                <option value="{{ $branch->id }}">{{ $branch->name }}</option>
                                            @endforeach
                                        </select>
                                        @error('branch_id')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-12">
                                    <div class="mb-3">
                                        <label class="form-label">{{ __('ملاحظات') }}</label>
                                        <textarea wire:model="notes" class="form-control @error('notes') is-invalid @enderror" rows="3"></textarea>
                                        @error('notes')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-12">
                                    <div class="form-check">
                                        <input wire:model="is_active" type="checkbox" class="form-check-input" id="is_active">
                                        <label class="form-check-label" for="is_active">
                                            {{ __('نشط') }}
                                        </label>
                                    </div>
                                </div>
                            </div>

                            @if($cost && $useful_life && $salvage_value !== null)
                                <div class="alert alert-info mt-3">
                                    <strong>{{ __('الإهلاك السنوي المحسوب:') }}</strong>
                                    {{ number_format(($cost - $salvage_value) / $useful_life, 2) }}
                                </div>
                            @endif
                        </form>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" wire:click="closeModal">
                            {{ __('إلغاء') }}
                        </button>
                        <button type="button" class="btn btn-primary" wire:click="save">
                            @if($editMode)
                                {{ __('تحديث') }}
                            @else
                                {{ __('حفظ') }}
                            @endif
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif

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