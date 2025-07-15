<?php

use Livewire\Volt\Component;
use App\Models\Project;
use Livewire\WithPagination;
use Illuminate\Support\Str;

new class extends Component {
    use WithPagination;

    public function getProjectsProperty()
    {
        return Project::with(['createdBy', 'updatedBy'])
            ->latest()
            ->paginate(10);
    }

    public function delete(Project $project)
    {
        $project->delete();
        session()->flash('success', 'تم حذف المشروع بنجاح');
    }

    public function getStatusBadgeClass($status)
    {
        return match ($status) {
            'pending' => 'bg-warning',
            'in_progress' => 'bg-info',
            'completed' => 'bg-success',
            'cancelled' => 'bg-danger',
            default => 'bg-secondary',
        };
    }

    public function getStatusText($status)
    {
        return match ($status) {
            'pending' => 'قيد الانتظار',
            'in_progress' => 'قيد التنفيذ',
            'completed' => 'مكتمل',
            'cancelled' => 'ملغي',
            default => 'غير معروف',
        };
    }
}; ?>

<div class="p-6">
    <div class="flex justify-between items-center mb-6">
        <h2 class="text-2xl font-bold text-gray-800">المشاريع</h2>
        @can('إنشاء مشروع')
        <a href="{{ route('projects.create') }}" class="btn btn-primary mb-2">
            <i class="las la-plus"></i> إضافة مشروع جديد
        </a>
        @endcan

    </div>

    @if (session()->has('success'))
        <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 3000)"
            class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"
                x-on:click="show = false"></button>
        </div>
    @endif

    <div class="card">
        <div class="card-body">
            <div class="table-responsive" style="overflow-x: auto;">
                <table class="table table-striped mb-0" style="min-width: 1200px;">
                    <thead class="table-light text-center align-middle">

                        <tr>

                            <th class="font-family-cairo fw-bold">#</th>
                            <th class="font-family-cairo fw-bold">اسم المشروع</th>
                            <th class="font-family-cairo fw-bold">الوصف</th>
                            <th class="font-family-cairo fw-bold">تاريخ البدء</th>
                            <th class="font-family-cairo fw-bold">تاريخ الانتهاء المتوقع</th>
                            <th class="font-family-cairo fw-bold">تاريخ الانتهاء الفعلي</th>
                            <th class="font-family-cairo fw-bold">الحالة</th>
                            <th class="font-family-cairo fw-bold">أنشئ بواسطة</th>
                            <th class="font-family-cairo fw-bold">تم التحديث بواسطة</th>
                            @can('إجراء عمليات علي المشروع')
                            <th class="font-family-cairo fw-bold">العمليات</th>
                            @endcan

                        </tr>
                    </thead>
                    <tbody>
                        @forelse($this->projects as $project)
                            <tr wire:key="{{ $project->id }}">
                                <td class="font-family-cairo text-center fw-bold">{{ $loop->iteration }}</td>
                                <td class="font-family-cairo text-center fw-bold">{{ $project->name }}</td>
                                <td class="font-family-cairo text-center fw-bold">
                                    {{ Str::limit($project->description, 50) }}</td>
                                <td class="font-family-cairo text-center fw-bold">
                                    {{ $project->start_date->format('Y-m-d') }}</td>
                                <td class="font-family-cairo text-center fw-bold">
                                    {{ $project->end_date->format('Y-m-d') }}</td>
                                <td class="font-family-cairo text-center fw-bold">
                                    {{ $project->actual_end_date?->format('Y-m-d') ?? '-' }}</td>
                                <td class="font-family-cairo text-center fw-bold">
                                    <span class="badge {{ $this->getStatusBadgeClass($project->status) }}">
                                        {{ $this->getStatusText($project->status) }}
                                    </span>
                                </td>

                                <td class="font-family-cairo fw-bold">{{ $project->createdBy->name }}</td>
                                <td class="font-family-cairo fw-bold">{{ $project->updatedBy->name }}</td>
                                @can('إجراء عمليات علي المشروع')
                                <td>
                                    @can('تعديل مشروع')
                                    <a href="{{ route('projects.edit', $project) }}" class="btn btn-success btn-sm">
                                        <i class="las la-edit fa-lg"></i>
                                    </a>
                                    @endcan
                                    @can('حذف مشروع')
                                    <button type="button" class="btn btn-danger btn-sm"
                                        wire:click="delete({{ $project->id }})"
                                        onclick="confirm('هل أنت متأكد من حذف هذا المشروع؟') || event.stopImmediatePropagation()">
                                        <i class="las la-trash fa-lg"></i>
                                    </button>
                                    @endcan

                                </td>
                                @endcan

                            </tr>
                        @empty
                            <tr>
                                <td colspan="10" class="text-center">
                                    <div class="alert alert-info py-3 mb-0"
                                        style="font-size: 1.2rem; font-weight: 500;">
                                        <i class="las la-info-circle me-2"></i>
                                        لا توجد بيانات
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="mt-4">
        {{ $this->projects->links() }}
    </div>
</div>
