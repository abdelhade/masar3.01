<!-- Modal for Adding New Client -->
<div wire:ignore.self class="modal fade" id="addClientModal" tabindex="-1" aria-labelledby="addClientModalLabel"
    aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addClientModalLabel">
                    <i class="fas fa-user-plus me-2"></i>
                    إضافة {{ $modalClientTypeLabel ?? 'عميل' }} جديد
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                @if (session()->has('error'))
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        {{ session('error') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif

                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label class="form-label">اسم العميل <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" wire:model="newClient.cname"
                            placeholder="أدخل الاسم">
                        @error('newClient.cname')
                            <small class="text-danger">{{ $message }}</small>
                        @enderror
                    </div>

                    <div class="col-md-4 mb-3">
                        <label class="form-label">البريد الإلكتروني</label>
                        <input type="email" class="form-control" wire:model="newClient.email"
                            placeholder="example@email.com">
                        @error('newClient.email')
                            <small class="text-danger">{{ $message }}</small>
                        @enderror
                    </div>

                    <div class="col-md-4 mb-3">
                        <label class="form-label">الهاتف 1 <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" wire:model="newClient.phone"
                            placeholder="رقم الهاتف الأساسي">
                        @error('newClient.phone')
                            <small class="text-danger">{{ $message }}</small>
                        @enderror
                    </div>

                    <div class="col-md-4 mb-3">
                        <label class="form-label">الهاتف 2</label>
                        <input type="text" class="form-control" wire:model="newClient.phone2"
                            placeholder="رقم هاتف ثانوي">
                        @error('newClient.phone2')
                            <small class="text-danger">{{ $message }}</small>
                        @enderror
                    </div>

                    <div class="col-md-4 mb-3">
                        <label class="form-label">الشركة</label>
                        <input type="text" class="form-control" wire:model="newClient.company"
                            placeholder="اسم الشركة">
                        @error('newClient.company')
                            <small class="text-danger">{{ $message }}</small>
                        @enderror
                    </div>

                    <div class="col-md-4 mb-3">
                        <label class="form-label">الوظيفة</label>
                        <input type="text" class="form-control" wire:model="newClient.job"
                            placeholder="المسمى الوظيفي">
                        @error('newClient.job')
                            <small class="text-danger">{{ $message }}</small>
                        @enderror
                    </div>

                    <div class="col-md-6 mb-3">
                        <label class="form-label">العنوان 1</label>
                        <input type="text" class="form-control" wire:model="newClient.address"
                            placeholder="العنوان الأساسي">
                        @error('newClient.address')
                            <small class="text-danger">{{ $message }}</small>
                        @enderror
                    </div>

                    <div class="col-md-6 mb-3">
                        <label class="form-label">العنوان 2</label>
                        <input type="text" class="form-control" wire:model="newClient.address2"
                            placeholder="عنوان ثانوي">
                        @error('newClient.address2')
                            <small class="text-danger">{{ $message }}</small>
                        @enderror
                    </div>

                    <div class="col-md-3 mb-3">
                        <label class="form-label">الرقم القومي</label>
                        <input type="text" class="form-control" wire:model="newClient.national_id"
                            placeholder="الرقم القومي">
                        @error('newClient.national_id')
                            <small class="text-danger">{{ $message }}</small>
                        @enderror
                    </div>

                    <div class="col-md-3 mb-3">
                        <label class="form-label">تاريخ الميلاد</label>
                        <input type="date" class="form-control" wire:model="newClient.date_of_birth">
                        @error('newClient.date_of_birth')
                            <small class="text-danger">{{ $message }}</small>
                        @enderror
                    </div>

                    <div class="col-md-3 mb-3">
                        <label class="form-label">النوع <span class="text-danger">*</span></label>
                        <select class="form-select" wire:model.blur="newClient.gender" wire:key="newClient.gender">
                            <option value="">-- اختر النوع --</option> {{-- إضافة هذا للـ UX والـ default --}}
                            <option value="male">ذكر</option>
                            <option value="female">أنثى</option>
                        </select>
                        @error('newClient.gender')
                            <small class="text-danger">{{ $message }}</small>
                        @enderror
                    </div>

                    <div class="col-md-3 mb-3">
                        <label class="form-label">تصنيف العميل</label>
                        <select class="form-select" wire:model.blur="newClient.client_category_id"
                            wire:key="newClient.client_category_id">
                            <option value="">اختر التصنيف</option>
                            @foreach ($clientCategories as $category)
                                <option value="{{ $category['id'] }}">{{ $category['name'] }}</option>
                            @endforeach
                        </select>
                        @error('newClient.client_category_id')
                            <small class="text-danger">{{ $message }}</small>
                        @enderror
                    </div>

                    <div class="col-md-4 mb-3">
                        <label class="form-label">شخص للتواصل</label>
                        <input type="text" class="form-control" wire:model="newClient.contact_person"
                            placeholder="اسم المسؤول">
                        @error('newClient.contact_person')
                            <small class="text-danger">{{ $message }}</small>
                        @enderror
                    </div>

                    <div class="col-md-4 mb-3">
                        <label class="form-label">هاتف التواصل</label>
                        <input type="text" class="form-control" wire:model="newClient.contact_phone"
                            placeholder="رقم المسؤول">
                        @error('newClient.contact_phone')
                            <small class="text-danger">{{ $message }}</small>
                        @enderror
                    </div>

                    <div class="col-md-4 mb-3">
                        <label class="form-label">صلة القرابة</label>
                        <input type="text" class="form-control" wire:model="newClient.contact_relation"
                            placeholder="العلاقة">
                        @error('newClient.contact_relation')
                            <small class="text-danger">{{ $message }}</small>
                        @enderror
                    </div>

                    <div class="col-12 mb-3">
                        <label class="form-label">ملاحظات</label>
                        <textarea class="form-control" wire:model="newClient.info" rows="2" placeholder="أي معلومات إضافية"></textarea>
                        @error('newClient.info')
                            <small class="text-danger">{{ $message }}</small>
                        @enderror
                    </div>

                    <!-- Status -->
                    <div class="col-12 mb-3">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" wire:model="newClient.is_active"
                                id="clientIsActive" checked>
                            <label class="form-check-label" for="clientIsActive">
                                <i class="fas fa-toggle-on me-1"></i> نشط
                            </label>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="fas fa-times me-1"></i> إلغاء
                </button>
                <button type="button" class="btn btn-primary" wire:click="saveNewClient"
                    wire:loading.attr="disabled">
                    <span wire:loading.remove wire:target="saveNewClient">
                        <i class="fas la-save me-1"></i> حفظ
                    </span>
                    <span wire:loading wire:target="saveNewClient">
                        <span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span>
                        جاري الحفظ...
                    </span>
                </button>
            </div>
        </div>
    </div>
</div>

@push('scripts')
    <script>
        document.addEventListener('livewire:init', () => {
            Livewire.on('openClientModal', (data) => {
                const modal = new bootstrap.Modal(document.getElementById('addClientModal'));
                modal.show();
            });

            Livewire.on('closeClientModal', () => {
                const modal = bootstrap.Modal.getInstance(document.getElementById('addClientModal'));
                if (modal) {
                    modal.hide();
                }
            });
        });
    </script>
@endpush
