{{-- resources/views/livewire/components/account-creator.blade.php --}}
<div>
    <!-- زر فتح المودال -->
    <button class="btn btn-primary btn-lg" type="button" class="{{ $buttonClass }}" wire:click="openModal">
        <i class="fas fa-plus"></i> {{ $buttonText }}
    </button>

    <!-- المودال -->
    @if ($showModal)
        <div class="modal fade show" style="display: block; background-color: rgba(0,0,0,0.5);" tabindex="-1">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">
                            <i class="fas fa-user-plus"></i> {{ $buttonText }}
                        </h5>
                        <button type="button" class="btn-close" wire:click="closeModal"></button>
                    </div>

                    <div class="modal-body">
                        @if (session()->has('error'))
                            <div class="alert alert-danger">
                                {{ session('error') }}
                            </div>
                        @endif

                        <form wire:submit.prevent="saveAccount">
                            <div class="row">
                                <!-- الحقول الأساسية -->
                                <div class="col-md-4 mb-3">
                                    <label class="form-label">
                                        الكود <span class="text-danger">*</span>
                                    </label>
                                    <input type="text" class="form-control @error('code') is-invalid @enderror"
                                        wire:model="code" readonly>
                                    @error('code')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-4 mb-3">
                                    <label class="form-label">
                                        الاسم <span class="text-danger">*</span>
                                    </label>
                                    <input type="text" class="form-control @error('aname') is-invalid @enderror"
                                        wire:model="aname" required>
                                    @error('aname')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-4 mb-3">
                                    <label class="form-label">
                                        يتبع لـ <span class="text-danger">*</span>
                                    </label>
                                    <select class="form-select @error('parent_id') is-invalid @enderror"
                                        wire:model="parent_id" required>
                                        <option value="">-- اختر الحساب الأب --</option>
                                        @foreach ($parentAccounts as $parent)
                                            <option value="{{ $parent->id }}">
                                                {{ $parent->code }} - {{ $parent->aname }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('parent_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">التليفون</label>
                                    <input type="text" class="form-control @error('phone') is-invalid @enderror"
                                        wire:model="phone" placeholder="رقم التليفون">
                                    @error('phone')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label class="form-label">العنوان</label>
                                    <input type="text" class="form-control @error('address') is-invalid @enderror"
                                        wire:model="address" placeholder="العنوان">
                                    @error('address')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            @if (in_array($accountType, ['client', 'supplier']))
                                <!-- حقول ZATCA للعملاء والموردين -->
                                <div class="row">
                                    <div class="col-md-4 mb-3">
                                        <label class="form-label">الاسم التجاري (ZATCA)</label>
                                        <input type="text"
                                            class="form-control @error('zatca_name') is-invalid @enderror"
                                            wire:model="zatca_name" placeholder="الاسم التجاري">
                                        @error('zatca_name')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="col-md-4 mb-3">
                                        <label class="form-label">الرقم الضريبي (VAT)</label>
                                        <input type="text"
                                            class="form-control @error('vat_number') is-invalid @enderror"
                                            wire:model="vat_number" placeholder="الرقم الضريبي">
                                        @error('vat_number')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="col-md-4 mb-3">
                                        <label class="form-label">رقم الهوية</label>
                                        <input type="text"
                                            class="form-control @error('national_id') is-invalid @enderror"
                                            wire:model="national_id" placeholder="رقم الهوية">
                                        @error('national_id')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-4 mb-3">
                                        <label class="form-label">العنوان الوطني (ZATCA)</label>
                                        <input type="text"
                                            class="form-control @error('zatca_address') is-invalid @enderror"
                                            wire:model="zatca_address" placeholder="العنوان الوطني">
                                        @error('zatca_address')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="col-md-4 mb-3">
                                        <label class="form-label">نوع العميل</label>
                                        <select class="form-select @error('company_type') is-invalid @enderror"
                                            wire:model="company_type">
                                            <option value="">-- اختر النوع --</option>
                                            <option value="شركة">شركة</option>
                                            <option value="فردي">فردي</option>
                                        </select>
                                        @error('company_type')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="col-md-4 mb-3">
                                        <label class="form-label">الجنسية</label>
                                        <input type="text"
                                            class="form-control @error('nationality') is-invalid @enderror"
                                            wire:model="nationality" placeholder="الجنسية">
                                        @error('nationality')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>

                                @if ($accountType === 'client')
                                    <!-- حقل حد الائتمان للعملاء فقط -->
                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label">
                                                <i class="fas fa-money-bill-wave"></i> حد الائتمان المسموح
                                            </label>
                                            <input type="number" step="0.001"
                                                class="form-control @error('debit_limit') is-invalid @enderror"
                                                wire:model="debit_limit" placeholder="0.000">
                                            <small class="text-muted">اترك الحقل فارغاً لعدم وضع حد</small>
                                            @error('debit_limit')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                @endif
                            @endif

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">
                                        الفرع <span class="text-danger">*</span>
                                    </label>
                                    <select class="form-select @error('branch_id') is-invalid @enderror"
                                        wire:model="branch_id" required>
                                        @foreach ($branches as $branch)
                                            <option value="{{ $branch->id }}">{{ $branch->name }}</option>
                                        @endforeach
                                    </select>
                                    @error('branch_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </form>
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" wire:click="closeModal">
                            <i class="fas fa-times"></i> إلغاء
                        </button>
                        <button type="button" class="btn btn-primary" wire:click="saveAccount">
                            <i class="fas fa-save"></i> حفظ
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif
    <script>
        // منع إغلاق المودال عند الضغط خارجها
        document.addEventListener('livewire:initialized', () => {
            Livewire.on('account-created', (event) => {
                // يمكنك إضافة أي منطق إضافي هنا عند إنشاء الحساب
                console.log('تم إنشاء حساب جديد:', event);
            });
        });
    </script>
</div>
