<div>
    <div class="row mt-4">
        @if (!$condition_id)
            <div class="col-lg-6">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title"> (Submittals)</h5>
                    </div>
                    <div class="card-body">
                        <form wire:submit.prevent="{{ $submittal_id ? 'updateSubmittal' : 'storeSubmittal' }}">
                            <h5>{{ $submittal_id ? 'تعديل بيانات التقديم' : 'إضافة تقديم جديد' }}</h5>
                            <div class="row">
                                <div class="mb-3 col-lg-5">
                                    <label class="form-label" for="submittal-name">الاسم</label>
                                    <input type="text" class="form-control" id="submittal-name"
                                        wire:model.defer="submittal_name" placeholder="أدخل اسم التقديم">
                                    @error('submittal_name')
                                        <small class="text-danger">{{ $message }}</small>
                                    @enderror
                                </div>
                                <div class="mb-3 col-lg-5">
                                    <label class="form-label" for="submittal-score">السكور</label>
                                    <input type="number" class="form-control" id="submittal-score"
                                        wire:model.defer="submittal_score" placeholder="أدخل السكور">
                                    @error('submittal_score')
                                        <small class="text-danger">{{ $message }}</small>
                                    @enderror
                                </div>
                                <div class="mb-3 col-lg-2 d-flex align-items-end">
                                    <button type="submit" class="btn btn-primary me-2">
                                        <i class="las la-save"></i> حفظ
                                    </button>
                                    @if ($submittal_id)
                                        <button type="button" wire:click="cancel" class="btn btn-danger">
                                            <i class="las la-times"></i> إلغاء
                                        </button>
                                    @endif
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        @endif

        @if (!$submittal_id)
            <div class="col-lg-6">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title"> (Conditions)</h5>
                    </div>
                    <div class="card-body">
                        <form wire:submit.prevent="{{ $condition_id ? 'updateCondition' : 'storeCondition' }}">
                            <h5>{{ $condition_id ? 'تعديل بيانات شرط العمل' : 'إضافة شرط عمل جديد' }}</h5>
                            <div class="row">
                                <div class="mb-3 col-lg-3">
                                    <label class="form-label" for="condition-name">الاسم</label>
                                    <input type="text" class="form-control" id="condition-name"
                                        wire:model.defer="condition_name" placeholder="أدخل اسم الشرط">
                                    @error('condition_name')
                                        <small class="text-danger">{{ $message }}</small>
                                    @enderror
                                </div>
                                <div class="mb-3 col-lg-3">
                                    <label class="form-label" for="condition-score">السكور الافتراضي</label>
                                    <input type="number" class="form-control" id="condition-score"
                                        wire:model.defer="condition_score" placeholder="أدخل السكور">
                                    @error('condition_score')
                                        <small class="text-danger">{{ $message }}</small>
                                    @enderror
                                </div>

                                <div class="col-lg-6">
                                    <label class="form-label">الخيارات (اختياري)</label>
                                    @foreach ($options as $index => $option)
                                        <div class="input-group mb-2">
                                            <input type="text" wire:model.defer="options.{{ $index }}.name"
                                                class="form-control" placeholder="اسم الخيار">
                                            <input type="number" wire:model.defer="options.{{ $index }}.score"
                                                class="form-control" placeholder="السكور">
                                            <button type="button" wire:click="removeOption({{ $index }})"
                                                class="btn btn-danger btn-sm">
                                                <i class="las la-trash"></i>
                                            </button>
                                        </div>
                                        @error("options.{$index}.name")
                                            <small class="text-danger d-block">{{ $message }}</small>
                                        @enderror
                                        @error("options.{$index}.score")
                                            <small class="text-danger d-block">{{ $message }}</small>
                                        @enderror
                                    @endforeach
                                    <button wire:click.prevent="addOption" type="button"
                                        class="btn btn-secondary btn-sm mt-2">إضافة خيار آخر</button>
                                </div>
                                <div class="col-lg-4 d-flex align-items-end">
                                    <button type="submit" class="btn btn-primary me-2">
                                        <i class="las la-save"></i> حفظ
                                    </button>
                                    @if ($condition_id)
                                        <button type="button" wire:click="cancel" class="btn btn-danger">
                                            <i class="las la-times"></i> إلغاء
                                        </button>
                                    @endif
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        @endif
    </div>

    <div class="row mt-4">
        <div class="col-lg-6">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title">قائمة التقديمات (Submittals)</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>الاسم</th>
                                    <th>السكور</th>
                                    <th>تحكم</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($submittals as $submittal)
                                    <tr>
                                        <td>{{ $loop->iteration }}</td>
                                        <td>{{ $submittal->name }}</td>
                                        <td>{{ $submittal->score }}</td>
                                        <td>
                                            <button wire:click="editSubmittal({{ $submittal->id }})"
                                                class="btn btn-sm btn-success">
                                                <i class="fa fa-edit"></i>
                                            </button>
                                            <button wire:click="destroySubmittal({{ $submittal->id }})"
                                                wire:confirm="هل أنت متأكد من الحذف؟" class="btn btn-sm btn-danger"> <i
                                                    class="fa fa-trash"></i>
                                            </button>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="text-center">لا توجد بيانات</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-6">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title">قائمة شروط العمل (Work Conditions)</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>الاسم</th>
                                    <th>السكور</th>
                                    <th>تحكم</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($conditions as $condition)
                                    <tr>
                                        <td>{{ $loop->iteration }}</td>
                                        <td>{{ $condition->name }}</td>
                                        <td>{{ $condition->score }}</td>
                                        <td>
                                            <button wire:click="editCondition({{ $condition->id }})"
                                                class="btn btn-sm btn-success"> <i class="fa fa-edit"></i>
                                            </button>
                                            <button wire:click="destroyCondition({{ $condition->id }})"
                                                wire:confirm="هل أنت متأكد من الحذف؟" class="btn btn-sm btn-danger"><i
                                                    class="fa fa-trash"></i></button>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="12" class="text-center">لا توجد بيانات</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
