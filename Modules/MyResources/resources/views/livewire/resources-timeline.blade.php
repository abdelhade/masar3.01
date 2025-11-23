<div>
    <div class="row mb-3">
        <div class="col-md-3">
            <input type="date" wire:model.live="startDate" class="form-control">
        </div>
        <div class="col-md-3">
            <input type="date" wire:model.live="endDate" class="form-control">
        </div>
        <div class="col-md-3">
            <select wire:model.live="categoryFilter" class="form-control">
                <option value="">كل التصنيفات</option>
                @foreach($categories as $category)
                    <option value="{{ $category->id }}">{{ $category->name_ar }}</option>
                @endforeach
            </select>
        </div>
    </div>

    <div class="alert alert-info">
        عرض الجدولة الزمنية للموارد (Timeline View) - يمكن تطويره لاحقاً باستخدام مكتبات JavaScript
    </div>

    <div class="table-responsive">
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>المورد</th>
                    <th>المشروع</th>
                    <th>تاريخ البداية</th>
                    <th>تاريخ النهاية</th>
                    <th>الحالة</th>
                </tr>
            </thead>
            <tbody>
                @foreach($assignments as $assignment)
                    <tr>
                        <td>{{ $assignment->resource->name }}</td>
                        <td>{{ $assignment->project->name }}</td>
                        <td>{{ $assignment->start_date->format('Y-m-d') }}</td>
                        <td>{{ $assignment->end_date?->format('Y-m-d') ?? '-' }}</td>
                        <td>
                            <span class="badge bg-{{ $assignment->status->color() }}">
                                {{ $assignment->status->label() }}
                            </span>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>

