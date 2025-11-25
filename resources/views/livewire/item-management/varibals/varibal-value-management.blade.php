<div>
    <!-- Header Section -->
    <div class="row mb-4">
        <div class="col-md-6">
            <h4 class="mb-0">{{ $varibal->name ?? 'غير محدد' }}</h4>
        </div>
        <div class="col-md-6 text-end">
            @can('create varibalsValues')
                <button wire:click="create" class="btn btn-main">
                    <i class="fas fa-plus"></i> {{ __('إضافه') }}
                </button>
            @endcan
        </div>
    </div>

    <!-- Search Section -->
    <div class="row mb-3">
        <div class="col-md-6">
            <div class="input-group">
                <input type="text" wire:model.live="search" class="form-control"
                    placeholder="{{ __('البحث في  ' . $varibal->name . '...') }}">
                <span class="input-group-text">
                    <i class="fas fa-search"></i>
                </span>
            </div>
        </div>
    </div>

    <!-- Flash Messages -->
    @if (session()->has('message'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('message') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <!-- Form Modal -->
    @if ($showForm)
        <div class="modal show d-block" tabindex="-1" style="background-color: rgba(0,0,0,0.5);">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">
                            {{ $editingId ? __('تعديل القيمة') : __('إضافة قيمة جديدة') }}
                        </h5>
                        <button type="button" wire:click="cancel" class="btn-close"></button>
                    </div>
                    <div class="modal-body">
                        <form wire:submit.prevent="save">
                            <div class="mb-3">
                                <label for="value" class="form-label">{{ __('القيمة') }} <span
                                        class="text-danger">*</span></label>
                                <input type="text" wire:model="value"
                                    class="form-control @error('value') is-invalid @enderror" id="value"
                                    placeholder="{{ __('أدخل القيمة') }}">
                                @error('value')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </form>
                    </div>
                    <div class="modal-footer">
                        <button type="button" wire:click="cancel" class="btn btn-secondary">
                            {{ __('إلغاء') }}
                        </button>
                        <button type="button" wire:click="save" class="btn btn-main">
                            {{ $editingId ? __('تحديث') : __('حفظ') }}
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <!-- Data Table -->
    <div class="card">
        <div class="card-body">
            @if ($varibalValues->count() > 0)
                <div class="table-responsive">
                    <table class="table table-striped table-hover">
                        <thead class="table-dark">
                            <tr>
                                <th class="text-white">{{ __($varibal->name) }}</th>
                                @canany(['edit varibalsValues', 'delete varibalsValues'])
                                    <th class="text-white">{{ __('الإجراءات') }}</th>
                                @endcanany
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($varibalValues as $varibalValue)
                                <tr>
                                    <td>{{ $varibalValue->value }}</td>
                                    @canany(['edit varibalsValues', 'delete varibalsValues'])
                                        <td>
                                            <div class="btn-group" role="group">
                                                @can('edit varibalsValues')
                                                    <button wire:click="edit({{ $varibalValue->id }})"
                                                        class="btn btn-sm btn-outline-primary" title="{{ __('تعديل') }}">
                                                        <i class="fas fa-edit"></i>
                                                    </button>
                                                @endcan
                                                @can('delete varibalsValues')
                                                    <button wire:click="delete({{ $varibalValue->id }})"
                                                        class="btn btn-sm btn-outline-danger" title="{{ __('حذف') }}"
                                                        onclick="return confirm('{{ __('هل أنت متأكد من حذف هذه القيمة؟') }}')">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                @endcan
                                            </div>
                                        </td>
                                    @endcanany
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div class="d-flex justify-content-center mt-3">
                    {{ $varibalValues->links() }}
                </div>
            @else
                <div class="text-center py-5">
                    <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                    <h5 class="text-muted">{{ __('لا توجد قيم') }}</h5>
                    <p class="text-muted">{{ __('لم يتم إضافة أي قيم لهذا المتغير بعد') }}</p>
                    @if (!$search)
                        <button wire:click="create" class="btn btn-main">
                            <i class="fas fa-plus"></i> {{ __('إضافة أول قيمة') }}
                        </button>
                    @endif
                </div>
            @endif
        </div>
    </div>

    <!-- Loading Indicator -->
    <div wire:loading class="position-fixed top-50 start-50 translate-middle">
        <div class="spinner-border text-primary" role="status">
            <span class="visually-hidden">{{ __('جاري التحميل...') }}</span>
        </div>
    </div>
</div>
