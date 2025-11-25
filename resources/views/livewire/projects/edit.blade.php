<?php

use Livewire\Volt\Component;
use App\Models\Project;
use Illuminate\Support\Facades\Auth;

new class extends Component {
    public Project $project;
    public $name;
    public $description;
    public $start_date;
    public $end_date;
    public $actual_end_date;
    public $status;

    public function mount(Project $project)
    {
        $this->project = $project;
        $this->name = $project->name;
        $this->description = $project->description;
        $this->start_date = $project->start_date ? $project->start_date->format('Y-m-d') : null;
        $this->end_date = $project->end_date ? $project->end_date->format('Y-m-d') : null;
        $this->actual_end_date = $project->actual_end_date ? $project->actual_end_date->format('Y-m-d') : null;
        $this->status = $project->status;
    }

    public function save()
    {
        $validated = $this->validate([
            'name' => 'required|min:3|max:255|unique:projects,name,' . $this->project->id,
            'description' => 'required|min:10',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
            'actual_end_date' => 'nullable|date|after_or_equal:start_date',
            'status' => 'required|in:pending,in_progress,completed,cancelled',
        ]);

        // Convert empty string to null for actual_end_date
        if (empty($validated['actual_end_date'])) {
            $validated['actual_end_date'] = null;
        }

        $validated['updated_by'] = Auth::id();

        $this->project->update($validated);

        session()->flash('success', 'تم تحديث المشروع بنجاح');
        return redirect()->route('projects.index');
    }
}; ?>

<div class="p-6">
    <div class="card">
        <div class="card-header">
            <h3 class="card-title font-family-cairo fw-bold">تعديل المشروع</h3>
        </div>
        <div class="card-body">
            <form wire:submit="save">
                <div class="row">
                    <div class="col-md-3 mb-3">
                        <label for="name" class="form-label font-family-cairo fw-bold">اسم المشروع</label>
                        <input type="text" class="form-control @error('name') is-invalid @enderror" wire:model="name"
                            id="name" placeholder="أدخل اسم المشروع">
                        @error('name')
                            <div class="invalid-feedback font-family-cairo">
                                {{ $message }}
                            </div>
                        @enderror
                    </div>

                    <div class="col-md-2 mb-3">
                        <label for="start_date" class="form-label font-family-cairo fw-bold">تاريخ البدء</label>
                        <input type="date" class="form-control @error('start_date') is-invalid @enderror"
                            wire:model="start_date" id="start_date">
                        @error('start_date')
                            <div class="invalid-feedback font-family-cairo">
                                {{ $message }}
                            </div>
                        @enderror
                    </div>

                    <div class="col-md-2 mb-3">
                        <label for="end_date" class="form-label font-family-cairo fw-bold">تاريخ الانتهاء المتوقع</label>
                        <input type="date" class="form-control @error('end_date') is-invalid @enderror"
                            wire:model="end_date" id="end_date">
                        @error('end_date')
                            <div class="invalid-feedback font-family-cairo">
                                {{ $message }}
                            </div>
                        @enderror
                    </div>

                    <div class="col-md-2 mb-3">
                        <label for="actual_end_date" class="form-label font-family-cairo fw-bold">تاريخ الانتهاء الفعلي</label>
                        <input type="date" class="form-control @error('actual_end_date') is-invalid @enderror"
                            wire:model="actual_end_date" id="actual_end_date">
                        @error('actual_end_date')
                            <div class="invalid-feedback font-family-cairo">
                                {{ $message }}
                            </div>
                        @enderror
                    </div>

                    <div class="col-md-2 mb-3">
                        <label for="status" class="form-label font-family-cairo fw-bold">حالة المشروع</label>
                        <select class="form-select font-family-cairo fw-bold @error('status') is-invalid @enderror" wire:model="status"
                            id="status">
                            <option value="pending">قيد الانتظار</option>
                            <option value="in_progress">قيد التنفيذ</option>
                            <option value="completed">مكتمل</option>
                            <option value="cancelled">ملغي</option>
                        </select>
                        @error('status')
                            <div class="invalid-feedback font-family-cairo">
                                {{ $message }}
                            </div>
                        @enderror
                    </div>

                    <div class="col-md-12 mb-3">
                        <label for="description" class="form-label font-family-cairo fw-bold">وصف المشروع</label>
                        <textarea class="form-control @error('description') is-invalid @enderror" wire:model="description" id="description"
                            rows="4" placeholder="أدخل وصف المشروع"></textarea>
                        @error('description')
                            <div class="invalid-feedback font-family-cairo">
                                {{ $message }}
                            </div>
                        @enderror
                    </div>
                </div>

                <div class="card-footer">
                    <div class="d-flex justify-content-end gap-2">
                        <a href="{{ route('projects.index') }}" class="btn btn-secondary">
                            <i class="las la-times"></i> إلغاء
                        </a>
                        <button type="submit" class="btn btn-main">
                            <i class="las la-save"></i> حفظ التغييرات
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
